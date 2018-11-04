<?php
namespace app\admin\controller;

use app\admin\model\CommentModel;





class Comment extends Base
{
    /**
     * 添加点评
     */
    public function add(){
        $comment_mdl = new CommentModel();
        $data = input('param.');
        $data['comment_time'] = strtotime($data['comment_time']);
        $comment_id = $comment_mdl->insertOneGetId($data);
        if($comment_id){
            return json(msg(1, '', '保存成功哦,亲'));
        }else{
            return json(msg(0, '', '保存失败啦,亲'));
        }
    }

    
}
