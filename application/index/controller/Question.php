<?php
namespace app\index\controller;
//use think\Input;



class Question extends Base
{
    /**
     * 写入回复内容
     * @param $msg_id 信息ID
     * @param $reply_type 类型
     * @param $reply_content 内容
     * @param string $replay_length 时间长度
     */
    public function reply_content($msg_id, $reply_type, $reply_content, $replay_length = ''){
        $contentD = db('Ask_content');
        $info = $contentD
            ->where(array('msg_id' => $msg_id))
            ->field('id,teacher_id,course_id')
            ->find();

        if($info){
            $arr = array(
                'answer_type' => $reply_type,
                'audio_length'=> $replay_length,
                'answer' => $reply_content
            );
            $contentD->where(array('id' => $info['id']))->update($arr);

            // 模板推送消息
            $this->send_mould($info['teacher_id'], $_SERVER['HTTP_HOST'].'/'.__APP__."/Home/Lecture/classroom?id=".$info['course_id'] , 0);
        }
    }


    /**
     * 模板推送
     * @param $teacher_id 老师ID
     * @param $url 跳转链接
     * @param $type 类型 0：班主任推送；1：老师推送
     * @param $question 问题
     * @param $describe 描述
     * @return bool
     */
    public function send_mould($teacher_id , $url, $type){
        $memberD = db('member');

        $info = $memberD
            ->where('id ='. $teacher_id)
            ->field('nickname,name,openid')
            ->find();

        if($info){
            $nickname = $info['name'] ?:$info['nickname'];
            // type 1:老师推送；0： 班主任推送
            if($type){
                $openid = $info['openid'];
                $str = $nickname.'老师 您好！有学员向您提问';
                $describe = "快进谈论组解答吧！";
            } else{
                $_info = $memberD
                    ->where('id = 294')
                    ->field('nickname,name,openid')
                    ->find();

                $openid = $_info['openid'];
                $str = $_info['name'] ?:$_info['nickaname'].' 您好！有学员向'.$nickname.'老师提问！';
                $describe = "老师已经回复，快去后台审核吧！";
            }

            $wechat = new WeChat();

            $data = array(
                'first'    => array('value' => urldecode($str), 'color' => '#743A3A'),
                'keyword1' => array('value' => urldecode("讨论组"), 'color' => '#743A3A'),
                'keyword2' => array('value' => date('Y-m-d H:i:s',time()), 'color' => '#743A3A'),
                'keyword3' => array('value' => urldecode($describe), 'color' => '#743A3A'),
                'keyword4' => array('value' => date('Y-m-d H:i:s',time()), 'color' => '#743A3A')
            );
            $wechat->doSendTempleteMsg($openid, C("template_code.ask_progress"), $url, $data, '#7B68EE');

        }else{
            //LogController::Error_mediaid_log("模板推送：找不到用户Id为 $teacher_id 的信息");

        }
        return true;
    }
}