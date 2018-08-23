<?php
namespace app\index\controller;
use app\tools\controller\Tools;
use Home\Controller\WeChat;
use think\Controller;
use think\Request;
use think\Input;
use think\Db;
use think\Session;
use think\Validate;
use app\tools\controller\Message;

class Lecture extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    private $log_path = APP_PATH.'log/Lecture.log';

    /**
     * 添加专栏
     */
    public function add_channel(){
        $member = $this->user;
        $price_list = $_POST['price_list'];
        if($price_list){
            $price_list = ltrim($price_list,"{");
            $price_list = rtrim($price_list,"}");
            $arr = explode(",",$price_list);
            $tmp = array();
            foreach ($arr as $k=>$v){
                $arrt = explode(":",$v);
                $tmp[$k]['expire'] = trim($arrt[0],'"');
                $tmp[$k]['money'] = $arrt[1];
            }
            $price_list = json_encode($tmp);
        }
        $data = array(
            'memberid' => $member['id'],
            'roomid' => $_POST['liveroom_id'],
            'create_time' => date("Y-m-d H:i:s"),
            'name' => $_POST['name'],
            'type' => $_POST['channel_type'],
            'description' => $_POST['description'],
            'cover_url' => SERVER_URL . "/public/images/cover/cover" . rand(1, 20) . ".jpg",
            'permanent' => $_POST['permanent'],
            'money' => $_POST['money'],
            'price_list' => $price_list,
            'reseller_enabled' => $_POST['reseller_enabled'],
            'resell_percent' => $_POST['resell_percent'],
        );
        $id = db("channel")->insertGetId($data);
        if($id){
            $res['channel_id'] = $id;
            wlog($this->log_path,"add_channel 获得返回数据：".$id."\n");
            $this->return_json(OK,$res);
        }else{
            wlog($this->log_path,"add_channel插入数据失败：".$id."\n");
            $this->return_json(E_OP_FAIL,'插入数据失败');
        }
       /* if ($count) {
            //LogController::W_H_Log("频道保存成功id为：" . $count);
            $res['code'] = 0;
            $data['channel_id'] = $count;

        } else {
            //LogController::W_H_Log("频道保存失败！");
            $res['code'] = 1;
        }
        $res['data'] = $data;
        $this->ajaxReturn($res, 'JSON');*/
    }


    /**
     * 添加课程
     */
    public function add_lecture()
    {
        wlog($this->log_path,"add_lecture 进入保存课程方法");
        $name = input('post.name');//课程标题
        $starttime = input('post.starttime');//开始时间
        $type = input('post.type');//课程类型普通课程，加密课程，付费课程（open_lecture,password_lecture,pay_lecture）
        $pass = input('post.pass');//课程密码
        $cost = input('post.cost');//课程费用
        $mode = input('post.mode');//课程模式：picture图文模式，video视频模式，ppt模式
        $channel_id = input('post.channel_id');
        $reseller_enabled = input('post.reseller_enabled');
        $resell_percent = input('post.resell_percent');
        $tag = input('post.tag');
        $labels = input('post.labels');
        //数据验证
        $result = $this->validate(
            [
                'name' => $name,
                'starttime' => $starttime,
                'type' => $type,
                'pass' => $pass,
                'cost' => $cost,
                'mode' => $mode,
                'channel_id' => $channel_id,
            ],
            [
                'name'  => 'require',
                'starttime' =>  'require|date',
                'type' =>  'require|in:open_lecture,password_lecture,pay_lecture',
                'pass' =>  'alphaNum',
                'cost' =>  'number',
                'mode' =>  'require|in:picture,video,ppt',
                'channel_id' =>  'require|number',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }

        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        if($type == 'password_lecture'){
            if(empty($pass)){
                $this->return_json(E_ARGS,'密码为空');
            }
        }elseif ($type == 'pay_lecture'){
            if(empty($cost)){
                $this->return_json(E_ARGS,'费用为空');
            }
            $cost = round($cost,1);
        }
        $livehome = db('home')->field('id')->where(['memberid'=>$this->user['id']])->find();
        if(empty($roomid)){
            $this->return_json(E_OP_FAIL,'请先完善个人信息');
        }

        //$member = $this->user;
        //$livehome = db('home')->field('id')->where(['memberid' => $this->user['id']])->find();
        $channel = db("channel")->field("id,category")->where("id=".$channel_id)->find();
        $show_on_page = 1;
        if($channel['category'] == 'businesscollege' || $channel['category'] == 'air'){
            $show_on_page = 0;
        }

        $data = array(
            'memberid' => $this->user['id'],
            'live_homeid' => $livehome['id'],
            'addtime' => date("Y-m-d H:i:s"),
            'name' => $name,
            'starttime' => date('Y-m-d H:i', strtotime($starttime)),
            'type' =>$type,
            'mode' => $mode,
            'pass' => $pass,
            'cost' => $cost ? $cost : 0,
            'channel_id' => $channel_id,
            'coverimg' => OSS_REMOTE_PATH. "/public/images/cover1.jpg",
            'reseller_enabled' => empty($reseller_enabled)?0:$reseller_enabled,
            'resell_percent' => empty($resell_percent)?0:$resell_percent,
            'tag' => $tag,
            'labels' => $labels,
            'show_on_page'=>$show_on_page
        );
        $exist_courses = db("course")->where("name='".$data['name']."'")->select();
        //判断该课程是否已建，已建的不再新建
        if(!$exist_courses){
            $cid = db("course")->insertGetId($data);
        }else{
            $this->return_json(OK,"已存在同名课程！");
        }
        if ($cid) {
            wlog($this->log_path,"add_lecture 课程保存成功id为：" . $cid);
            wlog($this->log_path,"add_lecture 课程保存成功id为：" . db()->getLastSql());
            $res['code'] = 0;
            $data['lecture_id'] = $cid;

            //插入场景
            $expend = array(
                'type' => 'sub_lecture',
                'memberid' => $this->user['id'],
                'eventid' => $cid
            );
            $expendid = db("expend")->insertGetId($expend);
            //设置二维码
            $this->setqrcode($cid, $expendid);

            $invitedata['inviteid'] = $this->user['id'];
            $invitedata['beinviteid'] = $this->user['id'];
            $invitedata['invitetype'] = "讲师";
            $invitedata['is_teacher'] = 1;
            $invitedata['courseid'] = $cid;
            $invitedata['addtime'] = date("Y-m-d H:i:s");
            $icount = db('invete')->insertGetId($invitedata);
            if ($icount) {
                LogController::W_H_Log("插入课程id为：" . $cid . "的讲师信息ID为：" . $icount);
            } else {
                LogController::W_H_Log("插入课程id为：" . $cid . "的讲师信息失败");
            }
            if($this->user['id'] != 294){
                $invite['inviteid'] = $this->user['id'];
                $invite['beinviteid'] = 294;
                $invite['invitetype'] = "主持人";
                $invite['courseid'] = $cid;
                $invite['addtime'] = date("Y-m-d H:i:s");
                $h_count = db('invete')->insertGetId($invite);
                if($h_count){
                    LogController::W_H_Log("插入课程id为：" . $cid . "的主持人信息ID为：" . $h_count);
                } else {
                    LogController::W_H_Log("插入课程id为：" . $cid . "的主持人信息失败");
                }
            }

            LogController::W_H_Log("创建新课程ID为".$cid."开始推送消息提醒！");
            $this->api_push_lecture_notify("lectureadd",$cid); //添加新课程后推送给已经购买专栏的学员
        } else {
            LogController::W_H_Log("课程保存失败！");
            $res['code'] = 1;
        }
        $res['data'] = $data;
        $this->ajaxReturn($res, 'JSON');
    }


    //设置课程二维码
    public function setqrcode($id, $expendid, $qrpath='')
    {
        if ($id) {
            //创建二维码
            $member = $this->user;
            $mid = $member['id'];
            $wechatfun = new WeChat();
            $q_content = $expendid;
            $filename = uniqid();

            if (empty($qrpath)) {
                //LogController::W_H_Log("重新设置文件名！！！");
                wlog($this->log_path,"setqrcode 重新设置文件名！！！");
                $qrpath = FILE_PATH.'qrcode/' . $filename . '.jpg';
                $res = $wechatfun->getQRCode($q_content, $qrpath, 1, 2592000);
                if ($res == 0) {
                    $wechatfun->getQRCode($q_content, $qrpath, 1, 2592000);
                }
                Tools::UploadFile_OSS("Public/qrcode/" . $filename . ".jpg", FILE_PATH."qrcode/" . $filename . ".jpg");
                $update_data = array(
                    "qrcode" => OSS_REMOTE_PATH . "/Public/qrcode/" . $filename . ".jpg",
                    "qrcode_addtime" => date("Y-m-d H:i")
                );
                db("course")->where("id=" . $id)->update($update_data);
                db("expend")->where("id=" . $expendid)->update($update_data);
            } else {
                $res = $wechatfun->getQRCode($q_content, $qrpath, 1, 2592000);
                if ($res = 0) {
                    $wechatfun->getQRCode($q_content, $qrpath, 1, 2592000);
                }
            }
        }
    }

}
