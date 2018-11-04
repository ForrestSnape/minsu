<?php
namespace app\admin\controller;

use app\admin\model\UserModel;
use think\Controller;

class Login extends Controller
{
    // 登录页面
    public function login()
    {
        return $this->fetch();
    }

    // 登录操作
    public function doLogin()
    {
        $username = input("param.username");
        $password = input("param.password");
        $userModel = new UserModel();
        $userInfo = $userModel->getOneByUsername($username);
        if(empty($userInfo) || md5($password . config('salt')) != $userInfo['password']){
            return json(msg(0, '', '账号或密码错误哦,亲'));
        }
        session('user_id', $userInfo['id']);
        cookie('user_id', $userInfo['id']);
        return json(msg(1, url('index/index'), ''));
    }

    // 退出操作
    public function loginOut()
    {
        session('user_id', null);
        cookie('user_id', null);
        $this->redirect(url('login/login'));
    }
}
