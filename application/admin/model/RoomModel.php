<?php
namespace app\admin\model;

use think\Model;

class RoomModel extends Model
{
    // 确定链接表名
    protected $name = 'room';

    /**
     * 获取所有房间列表
     */
    public function getAllRooms(){
        return objToArray(
            $this->select()
        );
    }

    /**
     * 根据房间id获取房间信息
     * @param $room_id
     */
    public function getOneByRoomid($room_id){
        return objToArray(
            $this->where('id', $room_id)->find()
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
