<?php
namespace app\admin\model;

use think\Model;

class RoomImageModel extends Model
{
    // 确定链接表名
    protected $name = 'room_image';

    /**
     * 根据房间id获取图片
     */
    public function getImagesByRoomid($room_id, $limit = ''){
        return objToArray(
            $this->where('room_id', $room_id)->limit($limit)->select()
        );
    }

}
