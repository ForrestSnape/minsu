<?php
namespace app\admin\controller;

use app\admin\model\OrderModel;
use app\admin\model\RoomModel;
use app\admin\model\PlatformModel;




class Analysis extends Base
{
    /**
     * 房间主页
     */
    public function room(){
        $room_mdl = new RoomModel();
        //房间信息
        $rooms = $room_mdl->getAllRooms();
        //当前年月份
        $y_str = date('Y');
        $y_m_str = date('Y-m');
        $this->assign([
            'rooms' => $rooms,
            'y_str' => $y_str,
            'y_m_str' => $y_m_str
        ]);
        return $this->fetch();
    }

    /**
     * 平台主页
     */
    public function platform(){
        $platform_mdl = new PlatformModel();
        //房间信息
        $platforms = $platform_mdl->getAllPlatforms();
        //当前年月份
        $y_str = date('Y');
        $y_m_str = date('Y-m');
        $this->assign([
            'platforms' => $platforms,
            'y_str' => $y_str,
            'y_m_str' => $y_m_str
        ]);
        return $this->fetch();
    }

    /**
     * ajax查询指定月份指定房间的订单信息 柱状图显示
     */
    public function ajax_getMonthOrderBarData(){
        $order_mdl = new OrderModel();
        $data = [];
        $month_where = [];
        //房间id
        $room_id = input('param.room_id');
        if($room_id > 0){
            $month_where['room_id'] = ['eq', $room_id];
        }
        //平台id
        $platform_id = input('param.platform_id');
        if($platform_id > 0){
            $month_where['platform_id'] = ['eq', $platform_id];
        }
        //获取指定月起止时间戳
        $begin_end = getTimestampEveryMonth(2018, input('param.month'));
        //查询当月订单
        $month_where['begin'] = ['between', [$begin_end['begin'], $begin_end['end']]];
        $month_order = 'begin asc';
        $orders = $order_mdl->getOrdersByWhere($month_where, $month_order);
        //组合柱状图数组
        //x轴坐标
        $order_num = count($orders);
        for($i = 1; $i <= $order_num; $i++){$data['x_data'][] = $i;}
        //订单天数 $y_days 订单金额 $y_total_price 利润金额 $y_profit_price
        foreach($orders as $k => $v){
            $data['y_date'][] = date('d', $v['begin']) . '.' . date('d', $v['end']);
            $data['y_days'][] = $v['days'];
            $data['y_total_price'][] = $v['total_price'];
            $data['y_profit_price'][] = $v['profit_price'];
        }
        return json_encode($data);
    }

    /**
     * ajax查询指定月份指定房间的平台信息 饼状图显示
     */
    public function ajax_getMonthPlatformPieData(){
        $order_mdl = new OrderModel();
        $platform_mdl = new PlatformModel();
        $room_mdl = new RoomModel();
        $data = [];
        $month_where = [];
        //房间id
        $room_id = input('param.room_id');
        if($room_id > 0){
            $month_where['room_id'] = ['eq', $room_id];
        }
        //平台id
        $platform_id = input('param.platform_id');
        if($platform_id > 0){
            $month_where['platform_id'] = ['eq', $platform_id];
        }
        //获取指定月起止时间戳
        $begin_end = getTimestampEveryMonth(2018, input('param.month'));
        //查询当月订单
        $month_where['begin'] = ['between', [$begin_end['begin'], $begin_end['end']]];
        $month_order = 'begin asc';
        $orders = $order_mdl->getOrdersByWhere($month_where, $month_order);
        
        if($room_id > 0){
            $platforms = $platform_mdl->getAllPlatforms();
            //组合饼状图数组
            $total = [];
            foreach($platforms as $k => $v){
                $total[$k]['platform_id'] = $v['id'];
                $total[$k]['platform_name'] = $v['name'];
                foreach($orders as $k1 => $v1){
                    if($v1['platform_id'] == $v['id']){$total[$k]['orders'][] = $v1;}
                }
            }
            foreach($total as $k => $v){
                $data['platforms'][] = $v['platform_name'];
                $orders_num = isset($v['orders']) ? count($v['orders']) : 0;
                $data['series'][] = [
                    'value' => $orders_num,
                    'name' => $v['platform_name']
                ];
            }
        }elseif($platform_id > 0){
            $rooms = $room_mdl->getAllRooms();
            //组合饼状图数组
            $total = [];
            foreach($rooms as $k => $v){
                $total[$k]['room_id'] = $v['id'];
                $total[$k]['room_name'] = $v['name'];
                foreach($orders as $k1 => $v1){
                    if($v1['room_id'] == $v['id']){$total[$k]['orders'][] = $v1;}
                }
            }
            foreach($total as $k => $v){
                $data['rooms'][] = $v['room_name'];
                $orders_num = isset($v['orders']) ? count($v['orders']) : 0;
                $data['series'][] = [
                    'value' => $orders_num,
                    'name' => $v['room_name']
                ];
            }
        }
        
        return json_encode($data);
    }

    /**
     * ajax查询指定年份指定房间的订单信息 折线图显示
     */
    public function ajax_getYearOrderLineData(){
        $order_mdl = new OrderModel();
        $data = [];
        $month_where = [];
        //房间id
        $room_id = input('param.room_id');
        if($room_id > 0){
            $month_where['room_id'] = ['eq', $room_id];
        }
        //平台id
        $platform_id = input('param.platform_id');
        if($platform_id > 0){
            $month_where['platform_id'] = ['eq', $platform_id];
        }
        //指定年份
        $year = input('param.year');
        for($i = 1; $i <= 12; $i++){
            $begin_end = getTimestampEveryMonth(2018, $i);
            $month_where['begin'] = ['between', [$begin_end['begin'], $begin_end['end']]];
            $month_order = 'begin asc';
            $orders[$i] = $order_mdl->getOrdersByWhere($month_where, $month_order);
        }
        
        //组合折线图数组
        //x轴坐标
        for($i = 1; $i <= 12; $i++){$data['x_data'][] = $i . '月';}
        //订单天数 $y_days 订单金额 $y_total_price 利润金额 $y_profit_price
        foreach($orders as $k => $v){
            $data['y_orders_num'][] = count($v);
            $y_total_days = 0;
            $y_total_price = 0;
            $y_profit_price = 0;
            foreach($v as $k1 => $v1){
                $y_total_days += $v1['days'];
                $y_total_price += $v1['total_price'];
                $y_profit_price += $v1['profit_price'];
            }
            $data['y_total_days'][] = $y_total_days;
            $data['y_total_price'][] = $y_total_price;
            $data['y_profit_price'][] = $y_profit_price;
        }
        return json_encode($data);
    }

    /**
     * ajax查询指定年份指定房间的平台信息 饼状图显示
     */
    public function ajax_getYearPlatformPieData(){
        $order_mdl = new OrderModel();
        $platform_mdl = new PlatformModel();
        $room_mdl = new RoomModel();
        $data = [];
        $month_where = [];
        //房间id
        $room_id = input('param.room_id');
        if($room_id > 0){
            $month_where['room_id'] = ['eq', $room_id];
        }
        //平台id
        $platform_id = input('param.platform_id');
        if($platform_id > 0){
            $month_where['platform_id'] = ['eq', $platform_id];
        }
        //指定年份
        $year = input('param.year');
        $begin_end['begin'] = mktime(0, 0, 0, 1, 1, $year);
        $begin_end['end'] = mktime(23, 59, 59, 12, 31, $year);
        //查询当月订单
        $month_where['begin'] = ['between', [$begin_end['begin'], $begin_end['end']]];
        $month_order = 'begin asc';
        $orders = $order_mdl->getOrdersByWhere($month_where, $month_order);

        if($room_id > 0){
            $platforms = $platform_mdl->getAllPlatforms();
            //组合饼状图数组
            $total = [];
            foreach($platforms as $k => $v){
                $total[$k]['platform_id'] = $v['id'];
                $total[$k]['platform_name'] = $v['name'];
                foreach($orders as $k1 => $v1){
                    if($v1['platform_id'] == $v['id']){$total[$k]['orders'][] = $v1;}
                }
            }
            foreach($total as $k => $v){
                $data['platforms'][] = $v['platform_name'];
                $orders_num = isset($v['orders']) ? count($v['orders']) : 0;
                $data['series'][] = [
                    'value' => $orders_num,
                    'name' => $v['platform_name']
                ];
            }
        }elseif($platform_id > 0){
            $rooms = $room_mdl->getAllRooms();
            //组合饼状图数组
            $total = [];
            foreach($rooms as $k => $v){
                $total[$k]['room_id'] = $v['id'];
                $total[$k]['room_name'] = $v['name'];
                foreach($orders as $k1 => $v1){
                    if($v1['room_id'] == $v['id']){$total[$k]['orders'][] = $v1;}
                }
            }
            foreach($total as $k => $v){
                $data['rooms'][] = $v['room_name'];
                $orders_num = isset($v['orders']) ? count($v['orders']) : 0;
                $data['series'][] = [
                    'value' => $orders_num,
                    'name' => $v['room_name']
                ];
            }
        }
        
        return json_encode($data);
    }
}
