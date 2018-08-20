<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Input;
use think\Db;
use think\Session;
use think\Validate;
use app\tools\controller\Time;
use app\tools\controller\Tools;


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
            //dump($lecture);exit;
            //$this->assign("lecture", $lecture);
            $result['lecture'] = $lecture;

            $member = db('member')->find($lecture['memberid']);
            //    $liveroom = db("home")->where("memberid=".$member['id'])->find();
            $liveroom = db("home")->find($lecture['live_homeid']);
            //$this->assign("livehome", $liveroom);
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
                //$this->assign('dvideo',$d_video);
            }
        }
        $result['member'] = $member;
        //$this->assign("member", $member);

        $currentMember = $this->user;
        $currentMember = db('member')->find($currentMember['id']);
        $result['cmember'] = $currentMember;
        //$this->assign("cmember", $currentMember);

        if ($lecture['channel_id']==217 && $currentMember['remarks']=='武汉峰会签到'){ //武汉峰会 会员进入
            $shareTitle = $currentMember['name']."花980元邀请您1元钱收听".$lecture['name'];
        }else{
            $shareTitle = $lecture['name'];
        }
        $result['shareTitle'] = $shareTitle;
        //$this->assign("shareTitle",$shareTitle);

        //判断是否已关注直播间
        //"memberid=" . $currentMember['id'] . " and roomid=" . $liveroom['id']
        $atten = db('attention')->where(['memberid'=>$currentMember['id'],'roomid'=>$liveroom['id']])->find();
        if ($atten) {
            //$this->assign("isattention", 1);
            $result['isattention'] = 1;
        } else {
            //$this->assign("isattention", 0);
            $result['isattention'] = 0;
        }
        //"cid=" . $lectureid . " and mid=" . $currentMember['id']
        $subscrib = db('subscribe')->where(['cid'=>$lectureid,'mid'=>$currentMember['id']])->find();
        if ($subscrib) {
            //$this->assign("issubscrib", 1);
            $result['issubscrib'] = 1;
        } else {
            //$this->assign("issubscrib", 0);
            $result['issubscrib'] = 0;
        }
        if (isset($lecture['live_homeid'])&&(!empty($lecture['live_homeid'])) && $lecture['live_homeid']!=0){
            $manager = db('home_manager')->where('(homeid='.$lecture['live_homeid'].' or homeid='.$lecture['channel_id'].') AND beinviteid='.$currentMember['id'])->find();
            // $manager = db('home_manager')->where('beinviteid='.$currentMember['id'])->find();
        }
        if (($currentMember['id'] == $lecture['memberid']) || $manager) {
            $result['isOwner'] = 1;
            $result['isSpeaker'] = 1;
            $result['canSpeak'] = 1;
            /*$this->assign("isOwner", 1);
            $this->assign("isSpeaker", 1);
            $this->assign("canSpeak", 1);*/
        } else {
            $result['isOwner'] = 0;
            //$this->assign("isOwner", 0);
        }
        //"courseid=" . $lectureid . " and beinviteid=" . $currentMember['id']
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



    //发送文本消息
    function send_text_message()
    {
        $member = $this->user;
        $liveroommemberid = $_REQUEST['aid'];
        $lecture_id = $_POST['lecture_id'];
        $message = $_POST['message'];
        $reply_message_id = $_POST['reply_message_id'];

        $liveroom = db('home')->where(['memberid' => $liveroommemberid])->find();
        $lecture = db('course')->find($lecture_id);

        //"courseid=$lecture_id and beinviteid=" . $member['id']
        $invete = M('invete')->where(['courseid'=>$lecture_id,'beinviteid'=>$member['id']])->find();
        if ($invete) {
            $title = $invete['invitetype'];
        } else {
            $title = "听众";
        }
        /*if($lecture['memberid'] == $member['id']){
            $title = '讲师';
        }else{
            $invete = M('invete')->where("beinviteid=".$member['id']." and courseid=".$lecture_id)->find();
            $title = $invete['invitetype'];
        }*/
        $message_type = "text";
        if ($reply_message_id) {
            $reply_msg = db('msg')->find($reply_message_id);
            $reply = $reply_msg['sender_nickname'] . ":" . $reply_msg['content'];
            $message_type = "reply_text";
        }
        //LogController::W_A_Log("message is:" . $message);
        wlog(APP_PATH.'log/text_message.log','message is:'.$message);
        $data = array(
            'add_time' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
            'content' => $message,
            'length' => 0,
            'message_type' => $message_type,
            'lecture_id' => $lecture_id,
            'ppt_id' => null,
            'ppt_url' => null,
            'reply' => $reply,
            'homeid' => $liveroom['id'],
            'sender_headimg' => ($member['headimg'] == $member['headimg']) ? $member['headimg'] : $member['img'],
            'sender_id' => $member['id'],
            'sender_nickname' => $member['name'] ? $member['name'] : $member['nickname'],
            'sender_title' => $title,
            'server_id' => null,
        );
        $count = db('msg')->insertGetId($data);
        //失效缓存
        //MemcacheToolController::Mem_Data_process("live_course_msg_".$lecture_id,'put',null);
        if ($count) {
            $data['message_id'] = $count;

            $qestionC = new QuestionController();
            // 问题回复--写入问题表里面
            $qestionC->reply_content($reply_msg['message_id'],1,$message);
        }
        $res['code'] = 0;
        $res['data'] = $data;
        $this->ajaxReturn($res, 'JSON');
    }

    //发送语音消息
    function send_voice_message()
    {
        $member = $_SESSION['CurrenMember'];
        //$liveroomid = $_REQUEST['aid'];
        $liveroommemberid = $_REQUEST['aid'];
        $lecture_id = $_REQUEST['lecture_id'];
        $length = $_REQUEST['audio_length'];
        $server_id = $_REQUEST['media_id'];
        $reply_message_id = $_POST['reply_message_id'];
        $liveroom = M('home')->where("memberid=" . $liveroommemberid)->find();
        $lecture = M('course')->find($lecture_id);

        $invete = M('invete')->where("courseid=$lecture_id and beinviteid=" . $member['id'])->find();
        if ($invete) {
            $title = $invete['invitetype'];
        } else {
            $title = "听众";
        }

        /* if($lecture['memberid'] == $member['id']){
             $title = '讲师';
         }else{
             $invete = M('invete')->where("beinviteid=".$member['id']." and courseid=".$lecture_id)->find();
             $title = $invete['invitetype'];
         }*/

        $message_type = "audio";
        if ($reply_message_id) {
            $reply_msg = M('msg')->find($reply_message_id);
            $reply = $reply_msg['sender_nickname'] . ":" . $reply_msg['content'];
            $message_type = "reply_audi";
        }

        $WeChat = new WeChatController();
        try {
            $content = $WeChat->getMedia($server_id);
            $size = filesize(".".$content);
            if (!$size || $size<1000){
                $WeChat->clear_token();
                $content = $WeChat->getMedia($server_id);
            }
        } catch (Exception $e) {
            LogController::W_A_Log("下载音频失败！mediaid is :" . $server_id);
            LogController::W_A_Log("$e->getTraceAsString()");
        }
        try {
            $update_res = UploadRemoteController::uploadFile(substr($content, 1), "." . $content);
            if ($update_res == 1){ //上传未成功
                $mcontent = C('media_domain') . $content;
            }else{
                $mcontent = C('OSS.remotepath') . $content;

            }
        } catch (Exception $e) {
            LogController::W_A_Log("音频文件上传OSS失败！" . $content);
            $mcontent = C('media_domain') . $content;
            LogController::W_A_Log("$e->getMessage()");
        }
        //$mcontent = C('media_domain') . $content;
        if ($length == 0){
            $length = 45;
        }
        $data = array(
            'add_time' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
            //'content' => C('OSS.remotepath').$content,
            'content' => $mcontent,
            //'content'=>"http://tianyan199.com".$content,
            'length' => $length,
            'message_type' => $message_type,
            'lecture_id' => $lecture_id,
            'ppt_id' => null,
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
        try {
            $count = M('msg')->add($data);
            //失效缓存
            MemcacheToolController::Mem_Data_process("live_course_msg_".$lecture_id,'put',null);
        } catch (Exception $e) {
            LogController::W_A_Log("插入音频MSG失败！");
            LogController::W_A_Log($e->getMessage());
            LogController::W_A_Log($e->getTraceAsString());
        }
        if ($count) {
            $data['message_id'] = $count;
            $qestionC = new QuestionController();
            // 问题回复--写入问题表里面
            $qestionC->reply_content($reply_msg['message_id'],2,$mcontent,$length);
        }
        $res['code'] = 0;
        $res['data'] = $data;
        $this->ajaxReturn($res, 'JSON');
    }

    //发送图片
    function send_picture_message()
    {
        $media_id = $_POST['media_id'];
        $lecture_id = $_POST['lecture_id'];
        $member = $_SESSION['CurrenMember'];
        $liveroom = M('home')->where("memberid=" . $member['id'])->find();
        $invete = M('invete')->where("courseid=$lecture_id and beinviteid=" . $member['id'])->find();
        if ($invete) {
            $title = $invete['invitetype'];
        } else {
            $title = "听众";
        }

        if ($media_id) { //获取图片
            $wechat = new WeChatController();
            $content = $wechat->downlodimg($media_id);
            $size = filesize("." . $content);
            LogController::W_A_Log("图片大小为$size");
            UploadRemoteController::uploadFile(substr($content, 1), "." . $content);
            $data = array(
                'add_time' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
                'content' => C('OSS.remotepath') . $content,
                'length' => 0,
                'message_type' => "picture",
                'lecture_id' => $lecture_id,
                'ppt_id' => null,
                'ppt_url' => null,
                'reply' => null,
                'homeid' => $liveroom['id'],
                'sender_headimg' => ($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
                'sender_id' => $member['id'],
                'sender_nickname' => $member['name'] ? $member['name'] : $member['nickname'],
                'sender_title' => $title,
                'server_id' => $media_id,
            );
            $count = M('msg')->add($data);
            //失效缓存
            MemcacheToolController::Mem_Data_process("live_course_msg_".$lecture_id,'put',null);
            if ($count) {
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
            $this->ajaxReturn($res, 'JSON');

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
}