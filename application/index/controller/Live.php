<?php
namespace app\index\controller;
use app\tools\controller\Tools;
use think\Controller;
use think\Request;
use think\Input;
use think\Db;
use think\Session;
use think\Validate;
use app\tools\controller\Time;

class Live extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function classroom()
    {
        $lectureid = \input('post.lecture_id');
        if ($lectureid) {
            $lecture = db("course")->find($lectureid);
            $status = Tools::timediff(strtotime($lecture['starttime']), time(), $lecture['mins']);
            $lecture['starttimes'] = strtotime($lecture['starttime']);

            if ($status != '进行中') {
                $lecture['current_status'] = $status ? 'ready' : 'closed';
            } else {
                $lecture['current_status'] = 'started';
            }
            if ($lecture['current_status'] == 'closed') {
                $lecture['lectureStatus'] = 'closed';
            } else {
                $lecture['lectureStatus'] = 'approved';
            }
            //LogController::W_H_Log("status 为：".$status."current_status 为：". $lecture['current_status']);
            $lecture['intro'] = str_replace(PHP_EOL, '', $lecture['intro']);
            $result['lecture'] = $lecture;

            $member = db('member')->find($lecture['memberid']);
            $liveroom = db("home")->find($lecture['live_homeid']);
            $result['livehome'] = $liveroom;
            if ($lecture['mode']=='video' || $lecture['mode']=='vedio'){
                $vedio = db('video')->where(['lecture_id'=>$lectureid,'isshow'=>'show'])->select();
                foreach ($vedio as $k=>$v ){
                    if (eregi_new("mp4$", $v['video'])||eregi_new("m3u8$", $v['video'])){
                        $d_video['mp4'] = $v['video'];
                    } elseif(eregi_new("webm$", $v['video'])){
                        $d_video['webm'] = $v['video'];
                    }
                }
                $d_video['img'] = $vedio[0]['video_cover'];
                if (!isset($d_video['img'])){
                    $d_video['img'] = $lecture['coverimg'];
                }
                $result['dvideo'] = $d_video;
            }
        }
        $result['member'] = $member;
        $currentMember = $this->user;
        $currentMember = db('member')->find($currentMember['id']);
        $result['cmember'] = $currentMember;
        if ($lecture['channel_id']==217 && $currentMember['remarks']=='武汉峰会签到'){ //武汉峰会 会员进入
            $shareTitle = $currentMember['name']."花980元邀请您1元钱收听".$lecture['name'];
        }else{
            $shareTitle = $lecture['name'];
        }
        $result['shareTitle'] = $shareTitle;
        //判断是否已关注直播间
        $atten = db('attention')->where(['memberid'=>$currentMember['id'],'roomid'=>$liveroom['id']])->find();
        if ($atten) {
            $result['isattention'] = 1;
        } else {
            $result['isattention'] = 0;
        }
        $subscrib = db('subscribe')->where(['cid'=>$lectureid,'mid'=>$currentMember['id']])->find();
        if ($subscrib) {
            $result['issubscrib'] = 1;
        } else {
            $result['issubscrib'] = 0;
        }
        if (isset($lecture['live_homeid'])&&(!empty($lecture['live_homeid'])) && $lecture['live_homeid']!=0){
            $manager = db('home_manager')->where('(homeid='.$lecture['live_homeid'].' or homeid='.$lecture['channel_id'].') AND beinviteid='.$currentMember['id'])->find();
        }
        if (($currentMember['id'] == $lecture['memberid']) || $manager) {
            $result['isOwner'] = 1;
            $result['isSpeaker'] = 1;
            $result['canSpeak'] = 1;
        } else {
            $result['isOwner'] = 0;
        }
        $invete = db('invete')->where(['courseid'=>$lectureid,'beinviteid'=>$currentMember['id']])->find();
        if ($invete || $manager) {
           /* $this->assign("isSpeaker", 1);
            $this->assign("canSpeak", 1);*/
            $result['isSpeaker'] = 1;
            $result['canSpeak'] = 1;
        } else {
           /* $this->assign("isSpeaker", 0);
            $this->assign("canSpeak", 0);*/
            $result['isSpeaker'] = 0;
            $result['canSpeak'] = 0;
        }

        //是否禁言
        //"courseid=" . $lecture['id'] . " and memberid=" . $currentMember['id']
        $dissenmsg = db('dissenmsg')->where(['courseid'=>$lecture['id'],'memberid'=>$currentMember['id']])->find();
        if ($dissenmsg) {
            //$this->assign("blocked", 1);
            $result['blocked'] = 1;
        } else {
            //$this->assign("blocked", 0);
            $result['blocked'] = 0;
        }
        //评论数
        //"lecture_id=" . $lecture['id']
        $dis_count = db('discuss')->where(['lecture_id'=>$lecture['id']])->count();
        $result['dis_count'] = $dis_count;
        //$this->assign("dis_count", $dis_count);
        //预约人数
        $sub_count = db('subscribe')->where(["cid"=>$lecture['id']])->count();
        if (isset($lecture['basescrib'])){
            $sub_count += $lecture['basescrib'];
        }
        //$this->assign("sub_count", $sub_count);
        $result['sub_count'] = $sub_count;
        //课程相关人员
       /* $Model = new Model();
        $arr_invete = $Model->table("live_invete i ,live_member m")->where("i.beinviteid=m.id and i.courseid=" . $lectureid)->field("i.id as iid,m.id as mid,i.invitetype as title,m.headimg,m.intro,m.name,m.nickname")->select();
        $this->assign("invetelist", $arr_invete);*/

        //更新人气
        //$cmember = $_SESSION["CurrenMember"];
        $cmember = $this->user;
        if ($cmember['id'] != $lecture['memberid']) {
            $lecdata = array(
                'clicknum' => $lecture['clicknum'] + 1,
            );
            db("course")->where(["id"=>$lecture['id']])->update($lecdata);
            //LogController::W_A_Log($cmember['id']."更新了人气为".$lecdata['clicknum']);

            //推送给用户
            //推送消息给用户
            //$publish_url=C('workerman_publish_url');
            $data['popular'] = $lecdata['clicknum'];
            $data['lecture_id'] = $lecture['id'];
            Tools::publish_msg(0,$lecture['id'],WORKERMAN_PUBLISH_URL,json_encode($data));
        }

        //JSSDK 签名
        /*$this->assign("appid", WECHAT_APPID);
        $jssdk = new JsApiController(C("wechat.APPID"), C("wechat.APPSECRET"));
        $signPackage = $jssdk->GetSignPackage();
        $this->assign("signpack", $signPackage);
        if (($lecture['mode']=='video' || $lecture['mode']=='vedio')&&(!empty($lecture['audio_content']))){
            $this->display('classroom_new');
        }else{
            $this->display();
        }*/
        $this->return_json(OK,$result);
    }


    /*
     * 获取初始聊天信息
     */
    public function get_messages()
    {
        //$cmember = $this->user;
        $lecture_id = input('post.lecture_id'); //课程id
        $start_date = input('post.start_date'); //开始日期 ，格式2018-08-06 20:00（没有秒），如果不传，则返回全部
        $desired_count = input('post.desired_count');//返回聊天信息条数
        $reverse = input('post.reverse');//为1则返回显示开始日期以前的数据
        //数据验证
        $result = $this->validate(
            [
                'lecture_id'  => $lecture_id,
                'start_date' => $start_date,
                'desired_count' => $desired_count,
                'reverse' => $reverse,
            ],
            [
                'lecture_id'  => 'require|number',
                'start_date'  => 'date',
                'desired_count'  => 'require|number',
                'reverse'  => 'require|in:0,1',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $where['id'] = $lecture_id;
        $where['isshow'] = 'show';

        $sql = "lecture_id=" . $lecture_id . " and isshow='show'";
        if (!empty($start_date) && $reverse==0) {
            $sql .= " and add_time>='$start_date'";
        }elseif (!empty($start_date) && $reverse==1){
            $sql .= " and add_time<='$start_date'";
        }
        $lecture = db('course')->field('memberid,isonline,name')->find($lecture_id);
        $member = db('member')->field('id,name,headimg,img')->find($lecture['memberid']);

        if ($reverse == 0){
            if (!empty($start_date)){
                //$listmsg = MemcacheToolController::Mem_Data_process($tmsql,'course_msg',$lecture_id);
                $listmsg = db("msg")->where($sql)->limit($desired_count)->select();
            }else{
                $listmsg = array();
            }
        }elseif ($reverse==1){
            //$listmsg = MemcacheToolController::Mem_Data_process($tmsql,'course_msg',$lecture_id);
            $listmsg = db("msg")->where($sql)->limit($desired_count)->order("add_time desc")->select();
        }
        /*LogController::W_H_Log("msg 长度：".sizeof($listmsg,0));
        LogController::W_H_Log("sql is:".$sql);*/
        if (sizeof($listmsg, 0) == 0) {//没有消息时
            if ($lecture['isonline']=='no'){
                $one_content = '大家可以点击左下角的“关注直播间”，后续有新的课堂会收到通知。也可以在课堂的主页点击右上角，分享到朋友圈或者微信群让更多的人听到老师的分享。';
            }else{
                $one_content = '欢迎大家来到《'.$lecture['name'].'》的讨论室，大家可以在这讨论课程相关的内容，老师会为大家一一解答！';
            }

            //$t = MemcacheToolController::Mem_Data_process($tcsql,'get',null);
            $t = db("msg")->where(['content'=>$one_content,'lecture_id'=>$lecture_id])->find();
            if (!$t) {
                $data = array(
                    'add_time' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
                    'content' => $one_content,
                    'length' => 0,
                    'message_type' => "text",
                    'lecture_id' => $lecture_id,
                    'ppt_id' => null,
                    'ppt_url' => null,
                    'reply' => null,
                    'sender_headimg' => ($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
                    'sender_id' => $member['id'],
                    'sender_nickname' => $member['name'],
                    'sender_title' => "讲师",
                    'server_id' => null,
                );
                $count = db('msg')->insertGetId($data);
                //操作msg表后，失效缓存
                /*MemcacheToolController::Mem_Data_process("live_course_msg_".$lecture_id,'put',null);
                MemcacheToolController::Mem_Data_process(md5($tcsql),'put',null);*/
                $data['message_id'] = $count;
                $listmsg[0] = $data;
            }
        }
        $res['data'] = $listmsg;
        $res['mark'] = $start_date;
        $this->return_json(OK,$res);
    }


    //发送文本消息
    public function send_text_message()
    {
        $member = $this->user;

        $liveroommemberid = input('post.aid');
        $lecture_id = input('post.lecture_id');
        $message = input('post.message');
        $reply_message_id = input('post.reply_message_id');

        //数据验证
        $result = $this->validate(
            [
                'liveroommemberid'  => $liveroommemberid,
                'lecture_id' => $lecture_id,
                'message' => $message,
                'reply_message_id' => $reply_message_id,
            ],
            [
                'liveroommemberid'  => 'require|number',
                'lecture_id'  => 'require|number',
                'message'  => 'require',
                'reply_message_id'  => 'number',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }

        $reply = '';
        $liveroom = db('home')->field('id')->where(['memberid' => $liveroommemberid])->find();
        if(empty($liveroom)){
            $this->return_json(E_OP_FAIL,'消息发送失败');
        }
        //$lecture = db('course')->find($lecture_id);
        $invete = db('invete')->field('invitetype')->where(['courseid'=>$lecture_id,'beinviteid'=>$member['id']])->find();//测试时注释
        if (!empty($invete)) {
            $title = $invete['invitetype'];
        } else {
            $title = "听众";
        }

        $message_type = "text";
        if (!empty($reply_message_id)) {
            $reply_msg = db('msg')->find($reply_message_id);
            $reply = $reply_msg['sender_nickname'] . ":" . $reply_msg['content'];
            $message_type = "reply_text";
        }
        wlog(APP_PATH.'log/text_message.log','message is:'.$message);
        $data = array(
            'add_time' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
            'content' => $message,
            'length' => 0,
            'message_type' => $message_type,
            'lecture_id' => $lecture_id,
            'ppt_url' => null,
            'reply' => $reply,
            'homeid' => $liveroom['id'],
            'sender_headimg' => ($member['headimg'] == $member['headimg']) ? $member['headimg'] : $member['img'],
            'sender_id' => $member['id'],
            'sender_nickname' => $member['name'] ? $member['name'] : $member['nickname'],
            'sender_title' => $title,
            'server_id' => null,
        );
        $count = db('msg')->insertGetId($data);//测试时注释
        //$count = 1;
        //失效缓存
        //MemcacheToolController::Mem_Data_process("live_course_msg_".$lecture_id,'put',null);
        /*if ($count) {
            $data['message_id'] = $count;
            $qestionC = new QuestionController();
            // 问题回复--写入问题表里面
            $qestionC->reply_content($reply_msg['message_id'],1,$message);
        }*/
        if ($count) {
            Tools::publish_msg(0,$lecture_id,WORKERMAN_PUBLISH_URL,$this->tranfer($data));
            $this->return_json(OK,$data);
        }else{
            wlog(APP_PATH.'log/text_message.log','插入消息数据失败');
            $this->return_json(E_OP_FAIL,'消息发送失败');
        }
    }


    /**
     * 数据类型转换
     * @param $data
     * @return array|string
     */
    public function tranfer($data)
    {
        $data = arr_val_tran_str($data);
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        return $data;
    }


    //发送文件相关的消息：包括语音，图片，视频
    function send_file_message()
    {
        $member = $this->user;
        $liveroommemberid = input('post.aid');
        $lecture_id = input('post.lecture_id');
        $reply_message_id = input('post.reply_message_id');
        $path = input('post.path');
        $type = input('post.type');
        $length = input('post.audio_length');
        $server_id = input('post.media_id');


        //数据验证
        $result = $this->validate(
            [
                'liveroommemberid'  => $liveroommemberid,
                'lecture_id' => $lecture_id,
                'length' => $length,
                'server_id' => $server_id,
                'reply_message_id' => $reply_message_id,
                'path' => $path,
                'type' => $type,
            ],
            [
                'liveroommemberid'  => 'require|number',
                'lecture_id'  => 'require|number',
                'length' =>  'number',
                'server_id' =>  'number',
                'reply_message_id'  => 'number',
                'path' => 'require',
                'type' => 'require|in:audio,video,picture,music,iframe',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }


        $liveroom = db('home')->field('id')->where("memberid=" . $liveroommemberid)->find();
        //$lecture = M('course')->find($lecture_id);

        $invete = db('invete')->field('invitetype')->where("courseid=$lecture_id and beinviteid=" . $member['id'])->find();
        if ($invete) {
            $title = $invete['invitetype'];
        } else {
            $title = "听众";
        }
        $message_type = $type;
        $reply = '';
        if (!empty($reply_message_id)) {
            $reply_msg = db('msg')->find($reply_message_id);
            $reply = $reply_msg['sender_nickname'] . ":" . $reply_msg['content'];
            $message_type = "reply_audi";
        }
        if (empty($length)){
            $length = 45;
        }
        $data = array(
            'add_time' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
            //'content' => C('OSS.remotepath').$content,
            'content' => $path,
            //'content'=>"http://tianyan199.com".$content,
            'length' => $length,
            'message_type' => $message_type,
            'lecture_id' => $lecture_id,
            //'ppt_id' => null,
            'meta'=>'{"wave_points": [179, 1066, 1121, 870, 1451, 1130, 1232, 1537, 1218, 1319, 1254, 1313, 1027, 1127, 1246, 1426, 1736, 1529, 1507, 1069, 925, 1198, 1033, 980, 1297, 1434, 978, 1367, 1037, 988, 1223, 1262, 904, 1267, 981, 975, 1418, 1556, 1112, 1185, 1426, 1301, 1255, 1376, 1297]}',
            'ppt_url' => null,
            'reply' => $reply,
            'homeid' => $liveroom['id'],
            'sender_headimg' => ($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
            'sender_id' => $member['id'],
            'sender_nickname' => $member['name'] ? $member['name'] : $member['nickname'],
            'sender_title' => $title,
            'server_id' => $server_id,
        );
        $count = db('msg')->insertGetId($data);
        if ($count) {
            Tools::publish_msg(0,$lecture_id,WORKERMAN_PUBLISH_URL,$this->tranfer($data));
            $this->return_json(OK,$data);
        }else{
            wlog(APP_PATH.'log/text_message.log','插入消息数据失败');
            $this->return_json(E_OP_FAIL,'消息发送失败');
        }
    }



    //发送语音消息
    function send_voice_message()
    {
        $member = $this->user;
        $liveroommemberid = input('post.aid');
        $lecture_id = input('post.lecture_id');
        $reply_message_id = input('post.reply_message_id');
        $path = input('post.path');
        $length = input('post.audio_length');
        $server_id = input('post.media_id');


        //数据验证
        $result = $this->validate(
            [
                'liveroommemberid'  => $liveroommemberid,
                'lecture_id' => $lecture_id,
                'length' => $length,
                'server_id' => $server_id,
                'reply_message_id' => $reply_message_id,
                'path' => $path,
            ],
            [
                'liveroommemberid'  => 'require|number',
                'lecture_id'  => 'require|number',
                'length' =>  'number',
                'server_id' =>  'number',
                'reply_message_id'  => 'number',
                'path' => 'require',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }


        $liveroom = db('home')->field('id')->where("memberid=" . $liveroommemberid)->find();
        //$lecture = M('course')->find($lecture_id);

        $invete = db('invete')->field('invitetype')->where("courseid=$lecture_id and beinviteid=" . $member['id'])->find();
        if ($invete) {
            $title = $invete['invitetype'];
        } else {
            $title = "听众";
        }

        $message_type = "audio";
        $reply = '';
        if (!empty($reply_message_id)) {
            $reply_msg = db('msg')->find($reply_message_id);
            $reply = $reply_msg['sender_nickname'] . ":" . $reply_msg['content'];
            $message_type = "reply_audi";
        }

        //$WeChat = new WeChatController();
        /*try {
            $content = $WeChat->getMedia($server_id);
            $size = filesize(".".$content);
            if (!$size || $size<1000){
                $WeChat->clear_token();
                $content = $WeChat->getMedia($server_id);
            }
        } catch (Exception $e) {
            LogController::W_A_Log("下载音频失败！mediaid is :" . $server_id);
            LogController::W_A_Log("$e->getTraceAsString()");
        }*/
       /* try {
            $update_res = Tools::UploadFile_OSS(substr($content, 1), "." . $content);
            if ($update_res == 1){ //上传未成功
                $mcontent = C('media_domain') . $content;
            }else{
                $mcontent = C('OSS.remotepath') . $content;

            }
        } catch (Exception $e) {
            LogController::W_A_Log("音频文件上传OSS失败！" . $content);
            $mcontent = C('media_domain') . $content;
            LogController::W_A_Log("$e->getMessage()");
        }*/
        //$mcontent = C('media_domain') . $content;
        if (empty($length)){
            $length = 45;
        }
        $data = array(
            'add_time' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
            //'content' => C('OSS.remotepath').$content,
            'content' => $path,
            //'content'=>"http://tianyan199.com".$content,
            'length' => $length,
            'message_type' => $message_type,
            'lecture_id' => $lecture_id,
            //'ppt_id' => null,
            'meta'=>'{"wave_points": [179, 1066, 1121, 870, 1451, 1130, 1232, 1537, 1218, 1319, 1254, 1313, 1027, 1127, 1246, 1426, 1736, 1529, 1507, 1069, 925, 1198, 1033, 980, 1297, 1434, 978, 1367, 1037, 988, 1223, 1262, 904, 1267, 981, 975, 1418, 1556, 1112, 1185, 1426, 1301, 1255, 1376, 1297]}',
            'ppt_url' => null,
            'reply' => $reply,
            'homeid' => $liveroom['id'],
            'sender_headimg' => ($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
            'sender_id' => $member['id'],
            'sender_nickname' => $member['name'] ? $member['name'] : $member['nickname'],
            'sender_title' => $title,
            'server_id' => $server_id,
        );
        $count = db('msg')->insertGetId($data);
            //失效缓存
            //MemcacheToolController::Mem_Data_process("live_course_msg_".$lecture_id,'put',null);

           /* LogController::W_A_Log("插入音频MSG失败！");
            LogController::W_A_Log($e->getMessage());
            LogController::W_A_Log($e->getTraceAsString());*/
        if ($count) {
            Tools::publish_msg(0,$lecture_id,WORKERMAN_PUBLISH_URL,$this->tranfer($data));
            $this->return_json(OK,$data);
        }else{
            wlog(APP_PATH.'log/text_message.log','插入消息数据失败');
            $this->return_json(E_OP_FAIL,'消息发送失败');
        }

       /* if ($count) {
            $data['message_id'] = $count;
            $qestionC = new QuestionController();
            // 问题回复--写入问题表里面
            $qestionC->reply_content($reply_msg['message_id'],2,$mcontent,$length);
        }
        $res['code'] = 0;
        $res['data'] = $data;
        $this->ajaxReturn($res, 'JSON');*/
    }

    //发送图片
    function send_picture_message()
    {
        $lecture_id = input('post.lecture_id');
        $path = input('post.path');
        //$reply_message_id = input('post.reply_message_id');
        //$length = input('post.audio_length');
        //$server_id = input('post.media_id');
        //$media_id = $_POST['media_id'];
        //$lecture_id = $_POST['lecture_id'];
        $member = $this->user;

        //数据验证
        $result = $this->validate(
            [
                'lecture_id' => $lecture_id,
                'path' => $path,
            ],
            [
                'lecture_id'  => 'require|number',
                'path' => 'require',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }


        $liveroom = db('home')->where("memberid=" . $member['id'])->find();
        $invete = db('invete')->where("courseid=$lecture_id and beinviteid=" . $member['id'])->find();
        if ($invete) {
            $title = $invete['invitetype'];
        } else {
            $title = "听众";
        }

        if (!empty($path)) { //获取图片
          /*  $wechat = new WeChatController();
            $content = $wechat->downlodimg($media_id);
            $size = filesize("." . $content);
            LogController::W_A_Log("图片大小为$size");
            UploadRemoteController::uploadFile(substr($content, 1), "." . $content);*/
            $data = array(
                'add_time' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
                'content' => $path,
                'length' => 0,
                'message_type' => "picture",
                'lecture_id' => $lecture_id,
                //'ppt_id' => null,
                'ppt_url' => null,
                'reply' => null,
                'homeid' => $liveroom['id'],
                'sender_headimg' => ($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
                'sender_id' => $member['id'],
                'sender_nickname' => $member['name'] ? $member['name'] : $member['nickname'],
                'sender_title' => $title,
                //'server_id' => $media_id,
            );
            $count = db('msg')->insertGetId($data);

            if ($count) {
                Tools::publish_msg(0,$lecture_id,WORKERMAN_PUBLISH_URL,$this->tranfer($data));
                $this->return_json(OK,$data);
            }else{
                wlog(APP_PATH.'log/text_message.log','插入消息数据失败');
                $this->return_json(E_OP_FAIL,'消息发送失败');
            }
            //失效缓存
            //MemcacheToolController::Mem_Data_process("live_course_msg_".$lecture_id,'put',null);
           /* if ($count) {
                $data['message_id'] = $count;
            }
            if ($size < 1000) { //当图片不足1K时说明图片未下载成功，重新下载
                LogController::W_A_Log("图片大小为$size,重新下载！");
                $content = $wechat->downlodimg($media_id);
                UploadRemoteController::uploadFile(substr($content, 1), "." . $content);
                M('msg')->where("server_id='" . $media_id . "'")->setField("content", C('OSS.remotepath') . $content);
                LogController::W_A_Log("重新下载图片大小为！" . filesize("." . $content));
                $data['content'] = C('OSS.remotepath') . $content;
            }

            $res['code'] = 0;
            $res['data'] = $data;
            $this->ajaxReturn($res, 'JSON');*/

        }
    }


    public function send_video_message(){
        $lecture_id = $_POST['lecture_id'];
        $video_id = $_POST['video_id'];
        if ($video_id) {
            $member = $_SESSION['CurrenMember'];
            $m = M('material')->find($video_id);
            if ($m['type']=='video'){
                $c['thumb_url'] = $m['cover'];
                $c['video_id'] = $m['id'];
                $c['video_url'] = $m["oss_path"];
            }else if($m['type']=='audio'){
                $c['audio_url'] = $m['path'];
                $c['thumb_url'] = $m['main'];
                $c['songname'] = $m['songname'];
                $c['audio_id'] = $m['id'];
                $c['singername'] = $m['singer'];
            }
            LogController::W_A_Log("video_url:".$m['oss_path']);
            $invete = M('invete')->where("courseid=$lecture_id and beinviteid=" . $member['id'])->find();
            if ($invete) {
                $title = $invete['invitetype'];
            } else {
                $title = "听众";
            }
            $message_type = ($m['type']=='audio')?'music':'video';
            $data = array(
                'add_time' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
                'content' => json_encode($c),
                'length' => 0,
                'message_type' => $message_type,
                'lecture_id' => $lecture_id,
                'ppt_id' => null,
                'ppt_url' => null,
                'reply' => null,
                'homeid' => null,
                'sender_headimg' => ($member['headimg'] == $member['headimg']) ? $member['headimg'] : $member['img'],
                'sender_id' => $member['id'],
                'sender_nickname' => $member['name'] ? $member['name'] : $member['nickname'],
                'sender_title' => $title,
                'server_id' => null,
            );
            $count = M('msg')->add($data);
            //失效缓存
            MemcacheToolController::Mem_Data_process("live_course_msg_".$lecture_id,'put',null);
            if ($count) {
                $data['message_id'] = $count;
            }
            $res['code'] = 0;
            $res['data'] = $data;
        }else{
            $res['code'] = 1;
            $res['msg'] = "缺少参数";

        }
        $this->ajaxReturn($res, 'JSON');
    }

    function send_iframe_message(){
        $lecture_id = $_POST['lecture_id'];
        $iframe = $_POST['iframe'];

        $member = $_SESSION['CurrenMember'];
        $lecture = M('course')->find($lecture_id);

        $invete = M('invete')->where("courseid=$lecture_id and beinviteid=" . $member['id'])->find();
        if ($invete) {
            $title = $invete['invitetype'];
        } else {
            $title = "听众";
        }
        $message_type = "iframe";

        $arr = explode('/',$iframe);
        $vid = $arr[count($arr)-1];
        $vid = str_replace(".html","",$vid);
        $content = '<iframe frameborder="0" width="640" height="498" src="http://v.qq.com/iframe/player.html?vid='.$vid.'&tiny=0&auto=0" allowfullscreen></iframe>';
        $data = array(
            'add_time' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
            'content' => $content,
            'length' => 0,
            'message_type' => $message_type,
            'lecture_id' => $lecture_id,
            'ppt_id' => null,
            'ppt_url' => null,
            'reply' => null,
            'homeid' => null,
            'sender_headimg' => ($member['headimg'] == $member['headimg']) ? $member['headimg'] : $member['img'],
            'sender_id' => $member['id'],
            'sender_nickname' => $member['name'] ? $member['name'] : $member['nickname'],
            'sender_title' => $title,
            'server_id' => null,
        );
        $count = M('msg')->add($data);
        //失效缓存
        MemcacheToolController::Mem_Data_process("live_course_msg_".$lecture_id,'put',null);
        if ($count) {
            $data['message_id'] = $count;
        }
        $res['code'] = 0;
        $res['data'] = $data;
        $this->ajaxReturn($res, 'JSON');

    }


    /**
     * 上传文件
     * @return mixed
     */
    public function uploadfile()
    {
        return parent::upload_file();
    }


    public function www()
    {
        //header( "Content-type: image/jpeg");
        $PSize = filesize(FILE_PATH.'1.png');
        $picturedata = fread(fopen(FILE_PATH.'1.png', "r"), $PSize);
        echo $picturedata;
    }

    /**
     * 获取二进制文件流并上传到OSS
     */
    public function upload_binary()
    {
        $PSize = filesize(FILE_PATH.'1.mp3');
        $picturedata = fread(fopen(FILE_PATH.'1.mp3', "r"), $PSize);
        $content = $picturedata;

        wlog(APP_PATH.'log/upload_binary.log',$content);
        //$content = file_get_contents('php://input');    // 不需要php.ini设置，内存压力小
        $name = time().mt_rand(1000,9999);
        $path = FILE_PATH.'temp/'.$name;
        $is = file_put_contents($path, $content, true);
        if ($is) {
            $this->redis->hset('file',$name,$path);
            $this->return_json(OK, ['fid'=>$name]);
        } else {
            $this->return_json(E_OP_FAIL, '操作失败！');
        }
    }

    public function upload_oss()
    {
        $name = $_POST['fid'];
        $houzui = $_POST['houzui'];
        if(empty($name)){
            $this->return_json(E_ARGS, '参数错误！');
        }
        $path = $this->redis->hget('file',$name);
        if(empty($path)){
            $this->return_json(E_ARGS, '参数错误！');
        }
        $oss_path = 'Public/Uploads/Chat/app/'.$name.$houzui;
        $is = Tools::UploadFile_OSS($oss_path,$path);
        if ($is) {
            $apath = OSS_REMOTE_PATH.'/'.$oss_path;
            //$this->redis->hset('file',$name,$apath);
            $this->return_json(OK, ['path'=>$apath]);
        } else {
            $this->return_json(E_OP_FAIL, '操作失败！');
        }
    }


    /**
     * 获取二进制文件流并上传到OSS-图片
     */
    public function upload_binary_photo()
    {
        $content = file_get_contents('php://input');    // 不需要php.ini设置，内存压力小
        //header( "Content-type: image/jpeg");
        /*$PSize = filesize(FILE_PATH.'2.png');
        $picturedata = fread(fopen(FILE_PATH.'2.png', "r"), $PSize);
        $content = $picturedata;*/
        $name = time().mt_rand(1000,9999);
        $path = FILE_PATH.'temp/'.$name;
        $handle=finfo_open(FILEINFO_MIME_TYPE);//This function opens a magic database and returns its resource.
        file_put_contents($path, $content, true);
        $fileInfo=finfo_file($handle,$path);// Return information about a file
        finfo_close($handle);
        switch ($fileInfo){
            case 'image/jpeg':$houzui = '.jpg';break;
            case 'image/png':$houzui = '.png';break;
            case 'image/bmp':$houzui = '.bmp';break;
            default:$houzui = '.jpg';
        }
        $oss_path = 'Public/Uploads/Chat/app/'.$name.$houzui;
        $is = Tools::UploadFile_OSS($oss_path,$path);
        if ($is) {
            $this->return_json(OK, ['path'=>OSS_REMOTE_PATH.'/'.$oss_path]);
        } else {
            $this->return_json(E_OP_FAIL, '操作失败！');
        }
    }


    /**
     * 获取二进制文件流并上传到OSS-视频
     */
    public function upload_binary_video()
    {
        $content = file_get_contents('php://input');    // 不需要php.ini设置，内存压力小
        $name = time().mt_rand(1000,9999).'.mp4';
        $path = FILE_PATH.'temp/'.$name;
        file_put_contents($path, $content, true);
        $oss_path = 'Public/Uploads/Chat/app/'.$name;
        $is = Tools::UploadFile_OSS($oss_path,$path);
        if ($is) {
            $this->return_json(OK, ['path'=>OSS_REMOTE_PATH.'/'.$oss_path]);
        } else {
            $this->return_json(E_OP_FAIL, '操作失败！');
        }
    }

    /**
     * 获取二进制文件流并上传到OSS-语音
     */
    public function upload_binary_voice()
    {
        $PSize = filesize(FILE_PATH.'1.mp3');
        $picturedata = fread(fopen(FILE_PATH.'1.mp3', "r"), $PSize);
        $content = $picturedata;

        //$content = file_get_contents('php://input');    // 不需要php.ini设置，内存压力小
        $name = time().mt_rand(1000,9999).'.mp3';
        $path = FILE_PATH.'temp/'.$name;
        file_put_contents($path, $content, true);
        $oss_path = 'Public/Uploads/Chat/app/'.$name;
        $is = Tools::UploadFile_OSS($oss_path,$path);
        if ($is) {
            $this->return_json(OK, ['path'=>OSS_REMOTE_PATH.'/'.$oss_path]);
        } else {
            $this->return_json(E_OP_FAIL, '操作失败！');
        }
    }
}