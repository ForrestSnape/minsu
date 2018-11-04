<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;

class UserModel extends Model
{
    // 确定链接表名
    protected $name = 'user';

    /**
     * 根据用户id获取用户信息
     * @param $user_id
     */
    public function getOneByUserid($user_id)
    {
        return objToArray(
            $this->where('id', $user_id)->find()
        );
    }

    /**
     * 根据用户账号获取用户信息
     * @param $username
     */
    public function getOneByUsername($username)
    {
        return objToArray(
            $this->where('username', $username)->find()
        );
    }

    
}
