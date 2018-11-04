<?php
namespace app\admin\model;

use think\Model;

class OrderDatePriceModel extends Model
{
    // 确定链接表名
    protected $name = 'order_date_price';

    /**
     * 批量插入记录
     * @param $data
     */
    public function insertMany($data){
        return $this->insertAll($data);
    }

    /**
     * 根据订单id获取订单所有信息
     * @param $order_id
     */
    public function getInfoByOrderid($order_id){
        return $this->where('order_id', $order_id)->select();
    }


}
