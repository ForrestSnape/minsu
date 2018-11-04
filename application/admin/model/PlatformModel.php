<?php
namespace app\admin\model;

use think\Model;

class PlatformModel extends Model
{
    // 确定链接表名
    protected $name = 'platform';

    /**
     * 获取所有平台列表
     */
    public function getAllPlatforms(){
        return objToArray(
            $this->select()
        );
    }

    /**
     * 根据平台id获取平台信息
     * @param $platform_id
     */
    public function getOneByPlatformid($platform_id){
        return objToArray(
            $this->where('id', $platform_id)->find()
        );
    }

    /**
     * 插入一条记录 成功返回记录id 失败返回false
     * @param $data 
     */
    public function insertOneGetId($data){
        return $this->insertGetId($data);
    }

}
