<?php
namespace app\admin\controller;

use app\admin\model\RoomModel;
use app\admin\model\RoomImageModel;
use app\admin\model\CommentModel;
use app\admin\model\OrderModel;

class Room extends Base
{
    /**
     * 房间列表
     */
    public function index(){
        $room_mdl = new RoomModel();
        $rooms = $room_mdl->getAllRooms();
        $this->assign(['rooms' => $rooms]);
        return $this->fetch();
    }

    /**
     * 添加房间
     */
    public function add(){
        return $this->fetch();
    }

    /**
     * 添加房间操作
     */
    public function doAdd(){
        $room_mdl = new RoomModel();

        $data = input('param.');

        //获取主图
        $photo = request()->file('photo');
        //图片移动到upload/image/room目录下
        if($photo){
            $info = $photo->move(ROOT_PATH . 'public' . DS .'upload' . DS . 'image' . DS . 'room');
            if($info){
                $photo_path = '/upload/image/room/' . $info->getSaveName();
            }
        }
        if($photo_path) $data['photo'] = $photo_path;

        $room_id = $room_mdl->insertOneGetId($data);
        if($room_id){
            return json(msg(1, '', '保存成功哦,亲'));
        }else{
            return json(msg(0, '', '保存失败啦,亲'));
        }
    }

    /**
     * 批量上传房间照片
     */
    public function uploadImages(){

    }

    /**
     * 房间主页
     */
    public function detail(){
        $room_mdl = new RoomModel();
        $room_image_mdl = new RoomImageModel();
        $comment_mdl = new CommentModel();
        $order_mdl = new OrderModel();

        $room_id = input('param.id');

        $room = $room_mdl->getOneByRoomid($room_id);
        $images = $room_image_mdl->getImagesByRoomid($room_id, 5);
        $comments = $comment_mdl->getCommentsByRoomid($room_id, 5);
        $orders = $order_mdl->getOrdersByWhere(['room_id' => $room_id], 'begin desc', 5);
        $this->assign([
            'room' => $room,
            'images' => $images,
            'comments' => $comments,
            'orders' => $orders
        ]);
        return $this->fetch();
    }

    /**
     * 房间图片相册页
     */
    public function images(){
        $room_mdl = new RoomModel();
        $room_image_mdl = new RoomImageModel();

        $room_id = input('param.id');

        $images = $room_image_mdl->getImagesByRoomid($room_id);
        $this->assign(['images' => $images]);
        return $this->fetch();
    }

    /**
     * 房间点评页
     */
    public function comments(){
        $room_mdl = new RoomModel();
        $comment_mdl = new CommentModel();

        $room_id = input('param.id');

        $room = $room_mdl->getOneByRoomid($room_id);
        $comments = $comment_mdl->getCommentsByRoomid($room_id);
        $this->assign([
            'room' => $room,
            'comments' => $comments
        ]);
        return $this->fetch();
    }
}
