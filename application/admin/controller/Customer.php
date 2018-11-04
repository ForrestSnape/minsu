<?php
namespace app\admin\controller;

use app\admin\model\CustomerModel;


class Customer extends Base
{
    /**
     * 客人列表
     */
    public function index(){
        echo '客人管理';
    }

    /**
     * 根据手机号查询客人信息
     */
    public function ajax_checkCustomer(){
        $customer_mdl = new CustomerModel();
        $mobile = input('param.mobile');
        $customer = $customer_mdl->getOneByMobile($mobile);
        if($customer){
            return json(msg(1, $customer, '此客人已注册了哦,点击确定保存,亲'));
        }else{
            return json(msg(0, '', '此手机号还未注册哦,亲'));
        }
    }
}
