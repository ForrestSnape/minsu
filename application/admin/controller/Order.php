<?php
namespace app\admin\controller;

use app\admin\model\RoomModel;
use app\admin\model\PlatformModel;
use app\admin\model\OrderModel;
use app\admin\model\OrderDatePriceModel;




class Order extends Base
{
    /**
     * 订单列表
     */
    public function index(){
        $room_mdl = new RoomModel();
        $platform_mdl = new PlatformModel();
        $order_mdl = new OrderModel();

        $rooms = $room_mdl->getAllRooms();
        $platforms = $platform_mdl->getAllPlatforms();

        $query_data = input('param.');
        $where = [];
        $query_begin = 0;
        if(isset($query_data['begin']) && $query_data['begin'] && $query_data['begin'] != '开始日期'){
            $where['begin'] = ['>=', strtotime($query_data['begin'])];
            $query_begin = $query_data['begin'];
        }
        $query_end = 0;
        if(isset($query_data['end']) && $query_data['end'] && $query_data['end'] != '结束日期'){
            $where['end'] = ['<=', strtotime($query_data['end'])];
            $query_end = $query_data['end'];
        }
        $query_room_id = 0;
        if(isset($query_data['room_id']) && $query_data['room_id']){
            $where['room_id'] = ['=', $query_data['room_id']];
            $query_room_id = $query_data['room_id'];
        }
        $query_platform_id = 0;
        if(isset($query_data['platform_id']) && $query_data['platform_id']){
            $where['platform_id'] = ['=', $query_data['platform_id']];
            $query_platform_id = $query_data['platform_id'];
        }
        $orders = $order_mdl->getOrdersByWhereWithPaginate($where, 8);
        $order_use_status = config('order_use_status');
        foreach($orders as $k => $v){
            $use_status = $order_mdl->getUsestatusByOrderid($v['id']);
            $orders[$k]['use_status'] = $order_use_status[$use_status];
        }

        $this->assign([
            'query_begin' => $query_begin,
            'query_end' => $query_end,
            'query_room_id' => $query_room_id,
            'query_platform_id' => $query_platform_id
        ]);
        $this->assign([
            'rooms' => $rooms,
            'platforms' => $platforms,
            'orders' => $orders
        ]);
        return $this->fetch();
    }

    /**
     * 添加订单
     */
    public function add(){
        $room_mdl = new RoomModel();
        $platform_mdl = new PlatformModel();

        $rooms = $room_mdl->getAllRooms();
        $platforms = $platform_mdl->getAllPlatforms();

        //当前日期
        $today = date('Y-m-d', time());
        $tomorrow = date('Y-m-d', time()+24*60*60);

        $this->assign([
            'rooms' => $rooms,
            'platforms' => $platforms,
            'today' => $today,
            'tomorrow' => $tomorrow
        ]);
        return $this->fetch();
    }

    /**
     * 添加订单操作
     */
    public function doAdd(){
        $order_mdl = new OrderModel();
        $order_date_price_mdl = new OrderDatePriceModel();

        $data = input('param.');
        $order = $data['order'];
        $order_date_price = $data['order_date_price'];

        $order_id = $order_mdl->insertOneGetId($order);
        if($order_id){
            foreach($order_date_price as $k => $v){
                $order_date_price[$k]['order_id'] = $order_id;
            }
        }
        $result = $order_date_price_mdl->insertMany($order_date_price);
        if($result){
            return json(msg(1, '', '保存成功哦,亲'));
        }else{
            return json(msg(0, '', '保存失败啦,亲'));
        }
    }

    /**
     * 删除订单
     */
    public function doDelete(){
        $order_mdl = new OrderModel();

        $order_id = input('param.id');
        $result = $order_mdl->deleteOne($order_id);
        if($result){
            return json(msg(1, '', '删除成功哦,亲'));
        }else{
            return json(msg(0, '', '删除失败啦,亲'));
        }
    }

    /**
     * 查看订单详情
     */
    public function detail(){
        $order_mdl = new OrderModel();
        $order_date_price_mdl = new OrderDatePriceModel();
        $order_id = input('param.id');
        $order = $order_mdl->getOneByOrderid($order_id);
        $order_date_prices = $order_date_price_mdl->getInfoByOrderid($order_id);

        $this->assign([
            'order' => $order,
            'order_date_prices' => $order_date_prices
        ]);
        return $this->fetch();
    }

}
