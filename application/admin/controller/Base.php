<?php
namespace app\admin\controller;


use think\Controller;
use app\admin\model\UserModel;

class Base extends Controller
{
    public function _initialize()
    {
        $user_id = session('?user_id') ? session('user_id') : 0;
        if(!$user_id) $user_id = cookie('user_id');

        //验证登录
        if(!$user_id){
            $this->redirect(url('login/login'));
        }

        //获取用户信息
        $userModel = new UserModel();
        $userInfo = $userModel->getOneByUserid($user_id);
        $this->assign(['userInfo' => $userInfo]);
    }

}
