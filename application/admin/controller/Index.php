<?php
namespace app\admin\controller;
use app\admin\model\NodeModel;
use app\admin\model\RoomModel;
use app\admin\model\OrderModel;
use app\admin\model\PlatformModel;

class Index extends Base
{
    /**
     * 框架首页
     */
    public function index()
    {
        // 获取菜单
        $node = new NodeModel();
        $menu = $node->getMenu();
        $this->assign([
            'menu' => $menu
        ]);

        return $this->fetch();
    }

    /**
     * 后台默认首页
     * @return mixed
     */
    public function init()
    {
        //房间统计数据
        $room_statistics = $this->getAllRoomsStatistics();

        //平台统计数据
        $platform_statistics = $this->getAllPlatformsStatistics();

        //当前月份利润
        $begin_end = getTimestampEveryMonth(2018, date('m'));
        $rooms_profits = $this->getProfitInTime($begin_end['begin'], $begin_end['end']);
        foreach($rooms_profits as $k => $room_profits){
            $rooms_profits[$k]['total_rent'] = $room_profits['total'] - $room_profits['rent_month'];
        }
        //当前年月份
        $m_str = date('m');
        $y_m_str = date('Y-m');

        //今日房间动态
        $today_timestamp = strtotime(date('Y-m-d 08:00:00', time()));
        $rooms_status_today = $this->getRoomsStatusInfoByDay($today_timestamp);

        //明日房间动态
        $yesterday_timestamp = strtotime(date('Y-m-d 08:00:00', time()+24*60*60));
        $rooms_status_yesterday = $this->getRoomsStatusInfoByDay($yesterday_timestamp);
        
        $this->assign([
            'room_statistics' => $room_statistics,
            'platform_statistics' => $platform_statistics,
            'rooms_profits' => $rooms_profits,
            'm_str' => $m_str,
            'y_m_str' => $y_m_str,
            'rooms_status_today' => $rooms_status_today,
            'rooms_status_yesterday' => $rooms_status_yesterday
        ]);

        return $this->fetch();
    }

    /**
     * ajax获取月度利润
     */
    public function ajax_getMonthProfit(){
        $begin_end = getTimestampEveryMonth(2018, input('param.month'));
        $rooms_profits = $this->getProfitInTime($begin_end['begin'], $begin_end['end']);
        $sum_rent = 0;//总房租
        foreach($rooms_profits as $k => $room_profits){
            $rooms_profits[$k]['total_rent'] = round($room_profits['total'] - $room_profits['rent_month'], 2);
            $sum_rent += $room_profits['rent_month'];
        }
        //计算平台合计利润
        $platform_ids = [];
        foreach($rooms_profits[0]['profits'] as $k => $profit){
            $platform_ids[] = $profit['platform_id'];
        }
        $platforms_total_profit = [];
        foreach($platform_ids as $k1 => $platform_id){
            $platforms_total_profit[$k1]['platform_id'] = $platform_id;
            $platforms_total_profit[$k1]['profit'] = 0;
            foreach($rooms_profits as $k2 => $room_profits){
                foreach($room_profits['profits'] as $k3 => $profit){
                    if($profit['platform_id'] == $platform_id){
                        $platforms_total_profit[$k1]['profit'] += $profit['profit'];
                    }
                }
            }
            $platforms_total_profit[$k1]['profit'] = round($platforms_total_profit[$k1]['profit'], 2);
        }
        $total = 0;
        foreach($platforms_total_profit as $k => $v){
            $total += $v['profit'];
        }
        $total = round($total, 2);
        $total_rent = round($total - $sum_rent, 2);
        $result = [];
        $result['rooms'] = $rooms_profits;
        $result['platforms'] = $platforms_total_profit;
        $result['total'] = $total;
        $result['total_rent'] = $total_rent;
        return json($result);
    }
    
    /**
     * 获取所有房间统计数据
     */
    private function getAllRoomsStatistics(){
        $room_mdl = new RoomModel();
        $order_mdl = new OrderModel();
        //获取所有房间
        $rooms = $room_mdl->getAllRooms();
        $room_statistics = [];
        foreach($rooms as $k =>$v){
            //查询指定房间的累计数据
            $t = $order_mdl->getAllStatisticsByRoomid($v['id']);
            $t['id'] = $v['id'];
            $t['name'] = $v['name'];
            $room_statistics[] = $t;
        }
        return $room_statistics;
    }

    /**
     * 获取所有平台统计数据
     */
    private function getAllPlatformsStatistics(){
        $platform_mdl = new PlatformModel();
        $order_mdl = new OrderModel();
        //获取所有平台
        $platforms = $platform_mdl->getAllPlatforms();
        $platform_statistics = [];
        foreach($platforms as $k => $v){
            //查询指定平台的累计数据
            $t = $order_mdl->getAllStatisticsByPlatformid($v['id']);
            $t['id'] = $v['id'];
            $t['name'] = $v['name'];
            $platform_statistics[] = $t;
        }
        return $platform_statistics;
    }

    /**
     * 获取某日房间动态
     * @param $timestamp 某日8点时间戳 因服务器地处东八区 begin/end存的也是8点时间戳
     */
    private function getRoomsStatusInfoByDay($timestamp){
        $room_mdl = new RoomModel();
        $order_mdl = new OrderModel();
        //获取所有房间
        $rooms = $room_mdl->getAllRooms();
        $room_status = [];
        
        foreach($rooms as $k =>$v){
            //查询指定房间的累计数据
            $info = $order_mdl->getStatusInfoByRoomidTimestamp($v['id'], $timestamp);
            $info['id'] = $v['id'];
            $info['name'] = $v['name'];
            $room_status[] = $info;
        }
        return $room_status;
    }

    /**
     * 获取指定时间段每个房间在每个平台的净利润
     * @param $begin 开始时间
     * @param $end 结束时间
     */
    private function getProfitInTime($begin, $end){
        $room_mdl = new RoomModel();
        $rooms = $room_mdl->getAllRooms();
        $platform_mdl = new PlatformModel();
        $platforms = $platform_mdl->getAllPlatforms();

        $order_mdl = new OrderModel();
        foreach($rooms as $k1 => $room){
            $total = 0;
            foreach($platforms as $k2 => $platform){
                $profit = $order_mdl->getProfitByRoomIdAndPlatformIdInTime($room['id'], $platform['id'], $begin, $end);
                $rooms[$k1]['profits'][] = [
                    'platform_id' => $platform['id'],
                    'platform_name' => $platform['name'],
                    'profit' => $profit
                ];
                $total += $profit;
            }
            $rooms[$k1]['total'] = round($total, 2);
        }
        return $rooms;
    }
}
