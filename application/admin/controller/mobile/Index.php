<?php
namespace app\admin\controller\mobile;

use app\admin\model\RoomModel;
use app\admin\model\OrderModel;


class Index extends Base
{
    public function index(){
        //今日房间动态
        $today_timestamp = strtotime(date('Y-m-d 08:00:00', time()));
        $rooms_status_today = $this->getRoomsStatusInfoByDay($today_timestamp);
        //明日房间动态
        $yesterday_timestamp = strtotime(date('Y-m-d 08:00:00', time()+24*60*60));
        $rooms_status_yesterday = $this->getRoomsStatusInfoByDay($yesterday_timestamp);
        
        $this->assign([
            'rooms_status_today' => $rooms_status_today,
            'rooms_status_yesterday' => $rooms_status_yesterday
        ]);

        return $this->fetch();
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
}