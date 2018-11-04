<?php
namespace app\admin\model;

use think\Model;

class CustomerModel extends Model
{
    // 确定链接表名
    protected $name = 'customer';

    /**
     * 根据客人mobile获取信息
     */
    public function getOneByMobile($mobile){
        return objToArray(
            $this->where('mobile', $mobile)->find()
        );
    }
}
