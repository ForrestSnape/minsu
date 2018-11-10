<?php
namespace app\admin\model;

use think\Model;

class OrderModel extends Model
{
    // 确定链接表名
    protected $name = 'order';

    /**
     * 与room表一对一关联 一个订单只有一个房间
     */
    public function room(){
        return $this->belongsTo('RoomModel', 'room_id', 'id');
    }

    /**
     * 与platform表一对一关联 一个订单只有一个平台
     */
    public function platform(){
        return $this->belongsTo('PlatformModel', 'platform_id', 'id');
    }

    /**
     * 与customer表一对一关联 一个订单只有一个客人
     */
    public function customer(){
        return $this->belongsTo('CustomerModel', 'customer_id', 'id');
    }

    /**
     * 与comment表一对一关联 一个订单只有一个点评
     */
    public function comment(){
        return $this->hasOne('CommentModel', 'order_id', 'id');
    }
    
    /**
     * 根据订单id获取订单所有信息
     * @param $order_id
     */
    public function getOneByOrderid($order_id){
        return $this->where('id', $order_id)->with(['room', 'platform', 'customer', 'comment'])->find();
    }

    /**
     * 按条件分页查询所有订单
     * @param $where 条件
     * @param $pageNum 每页条数
     */
    public function getOrdersByWhereWithPaginate($where = [], $pageNum=10){
        return $this->where($where)
            ->with(['room', 'platform', 'customer', 'comment'])
            ->order('begin desc')
            ->paginate($pageNum);
    }

    /**
     * 插入一条记录 成功返回记录id 失败返回false
     * @param $data 
     */
    public function insertOneGetId($data){
        return $this->insertGetId($data);
    }

    /**
     * 根据条件获取订单信息
     * @param $where 查询条件
     * @param $order 排序
     */
    public function getOrdersByWhere($where=[], $order='', $limit=''){
        return $this->where($where)
            ->with(['room', 'platform', 'customer', 'comment'])
            ->order($order)
            ->limit($limit)
            ->select();
    }

    /**
     * 查询房间指定累计订单数据
     * @param $room_id 房间id
     */
    public function getAllStatisticsByRoomid($room_id){
        $orders = objToArray($this->where('room_id', $room_id)->select());
        $num = count($orders);//总订单数
        $days = 0;//总出租天数
        $total_price = 0;//总订单金额
        $profit_price = 0;//总利润
        foreach($orders as $k => $v){
            $days += $v['days'];
            $total_price += $v['total_price'];
            $profit_price += $v['profit_price'];
        }
        return [
            'num' => $num,
            'days' => $days,
            'total_price' => $total_price,
            'profit_price' => $profit_price
        ];
    }

    /**
     * 查询平台指定累计订单数据
     * @param $platform_id 平台id
     */
    public function getAllStatisticsByPlatformid($platform_id){
        $orders = objToArray($this->where('platform_id', $platform_id)->select());
        $num = count($orders);//总订单数
        $days = 0;//总出租天数
        $total_price = 0;//总订单金额
        $profit_price = 0;//总利润
        foreach($orders as $k => $v){
            $days += $v['days'];
            $total_price += $v['total_price'];
            $profit_price += $v['profit_price'];
        }
        return [
            'num' => $num,
            'days' => $days,
            'total_price' => $total_price,
            'profit_price' => $profit_price
        ];
    }

    /**
     * 查询指定房间某天的状态
     */
    public function getStatusInfoByRoomidTimestamp($room_id, $timestamp){
        //今日开始的订单
        $b_order = $this->where('room_id', $room_id)->where('begin', $timestamp)->find();
        //今日结束的订单
        $e_order = $this->where('room_id', $room_id)->where('end', $timestamp)->find();
        //包含今日的订单
        $c_order = $this->where('room_id', $room_id)->where('begin', '<', $timestamp)->where('end', '>', $timestamp)->find();
        return [
            'b_order' => $b_order,
            'e_order' => $e_order,
            'c_order' => $c_order
        ];
    }

    /**
     * 根据id删除一条订单
     * @param $order_id 
     */
    public function deleteOne($order_id){
        return $this->where('id', $order_id)->delete();
    }

    /**
     * 根据id查询当前订单的状态 已完成/入住中/待入住
     * @param $order_id
     */
    public function getUsestatusByOrderid($order_id){
        $begin = $this->where('id', $order_id)->value('begin');
        $end = $this->where('id', $order_id)->value('end');
        $status = 0;
        //订单开始结束时间保存的都是当日凌晨数据
        //begin与今日比较 如果begin大于今日 则是待入住 因为begin end存的都是8点的时间戳 所以跟今日8点比较
            //如果相同 说明今天入住 如果当前时间过了14点 则是入住中 如果未过14点 则是待入住
            //如果begin小于今日 end与今日比较 
                //如果end大于今日 则是入住中
                //如果end等于今日 如果当前时间未过12点 则是入住中 如果过了12点 则是已完成
                //如果end小于今日 则是已完成
        $curDay_8 = strtotime(date('Y-m-d 08:00:00', time()));//今日8点时间戳
        $curDay_12 = strtotime(date('Y-m-d 12:00:00', time()));//今日12点时间戳
        $curDay_14 = strtotime(date('Y-m-d 14:00:00', time()));//今日14点时间戳

        if($begin > $curDay_8){
            $status = 1;
        }
        if($begin == $curDay_8){
            $status = time() >= $curDay_14 ? 2 : 1;
        }
        if($begin < $curDay_8){
            if($end > $curDay_8){
                $status = 2;
            }
            if($end == $curDay_8){
                $status = time() >= $curDay_12 ? 3 : 2;
            }
            if($end < $curDay_8){
                $status = 3;
            }
        }
        return $status;
    }

    /**
     * 查询指定房间指定平台指定时间段内的利润
     * @param $room_id 房间ID
     * @param $platform_id 平台ID
     * @param $begin 开始时间
     * @param $end 结束时间
     */
    public function getProfitByRoomIdAndPlatformIdInTime($room_id, $platform_id, $begin, $end){
        $condition = [
            'room_id' => $room_id,
            'platform_id' => $platform_id,
            'begin' => ['>=', $begin],
            'end' => ['<=', $end]
        ];
        $profit = $this->where($condition)->sum('profit_price');
        return number_format($profit, 2);
    }
}
