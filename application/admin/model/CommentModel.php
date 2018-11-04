<?php
namespace app\admin\model;

use think\Model;

class CommentModel extends Model
{
    // 确定链接表名
    protected $name = 'comment';

    /**
     * 根据房间id获取点评
     */
    public function getCommentsByRoomid($room_id, $limit = ''){
        return objToArray(
            $this->alias('c')
                ->join('room r', 'r.id=c.room_id')
                ->join('order o', 'o.id=c.order_id')
                ->join('platform p', 'p.id=o.platform_id')
                ->where('r.id', $room_id)
                ->limit($limit)
                ->order('c.comment_time desc')
                ->field('c.id as comment_id, c.order_id, c.room_id, c.star, c.comment, c.comment_time, 
                        o.platform_id, p.name as platform_name, p.photo as platform_photo')
                ->select()
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
