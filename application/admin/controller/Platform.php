<?php
namespace app\admin\controller;

use app\admin\model\PlatformModel;



class Platform extends Base
{
    /**
     * 平台列表
     */
    public function index(){
        $platform_mdl = new PlatformModel();
        $platforms = $platform_mdl->getAllPlatforms();
        $this->assign(['platforms' => $platforms]);
        return $this->fetch();
    }

    /**
     * 添加平台
     */
    public function add(){
        return $this->fetch();
    }

    /**
     * 添加房间操作
     */
    public function doAdd(){
        $platform_mdl = new PlatformModel();

        $data = input('param.');

        //获取头像
        $photo = request()->file('photo');
        //图片移动到upload/image/room目录下
        if($photo){
            $info = $photo->move(ROOT_PATH . 'public' . DS .'upload' . DS . 'image' . DS . 'platform');
            if($info){
                $photo_path = '/upload/image/platform/' . $info->getSaveName();
            }
        }
        if($photo_path) $data['photo'] = $photo_path;

        $platform_id = $platform_mdl->insertOneGetId($data);
        if($platform_id){
            return json(msg(1, '', '保存成功哦,亲'));
        }else{
            return json(msg(0, '', '保存失败啦,亲'));
        }
    }
}
