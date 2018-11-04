<?php
namespace app\admin\controller\mobile;

use app\admin\model\RoomModel;
use app\admin\model\OrderModel;


class Order extends Base
{
    public function index(){
        $room_mdl = new RoomModel();
        $rooms = $room_mdl->getAllRooms();
        $this->assign(['rooms' => $rooms]);

        return $this->fetch();
    }

    public function ajax_getOrders(){
        $order_mdl = new OrderModel();
        $room_id = input('param.room_id');
        $where = [];
        $where['room_id'] = $room_id;
        $orders = $order_mdl->getOrdersByWhere($where, 'begin desc', 10);
        foreach($orders as $k => $v){
            $orders[$k]['begin'] = date('Y.m.d', $orders[$k]['begin']);
            $orders[$k]['end'] = date('Y.m.d', $orders[$k]['end']);
        }
        if($orders){
            return json(msg(1, $orders, ''));
        }else{
            return json(msg(0, '', '获取订单失败啦!'));
        }
    }

    
}