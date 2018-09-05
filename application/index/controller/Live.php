<?php
namespace app\index\controller;
use app\tools\controller\Tools;


class Live extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 直播间接口
     */
    public function classroom($lecture_id = '')
    {
        if(empty($lecture_id)){
            $lecture_id = \input('post.lecture_id');
        }
        //数据验证
        $result1 = $this->validate(
            [
                'lecture_id'  => $lecture_id,
            ],
            [
                'lecture_id'  => 'require|number',
            ]
        );
        if($result1 !== true){
            $this->return_json(E_ARGS,'参数错误');
        }

        /*
         * 获取课程信息
         */
        $lecture = db('course')->field('id,memberid,live_homeid,name,sub_title,mins,starttime,intro,clicknum,type,pass,cost,mode,coverimg,status,discuss,speak,reward_message,channel_id,is_for_vip,basescrib')->find($lecture_id);
        if(empty($lecture)){
            $this->return_json(E_OP_FAIL,'课程为空');
        }
        $channel['lecturer'] = '';
        if(!empty($lecture['channel_id'])){
            $channel = db('channel')->field('lecturer')->find($lecture['channel_id']);
            if(empty($channel['lecturer'])){
                $channel['lecturer'] = 294;
            }
        }
        $status = Tools::timediff(strtotime($lecture['starttime']), time(), $lecture['mins']);
        $lecture['starttimes'] = strtotime($lecture['starttime']);
        $lecture['countdown'] = $lecture['starttimes'] - time();//倒计时
        if($lecture['countdown'] < 0){
            $lecture['countdown'] = 0;
        }
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
        $lecture['intro'] = str_replace(PHP_EOL, '', $lecture['intro']);
        $result['lecture'] = $lecture;

        /*
         * 获取讲师信息
         */
        $field = 'id,name,nickname,sex,headimg,img,isauth,company,position,title';
        if($lecture['memberid']!=294){
            $member = db('member')->field($field)->find($lecture['memberid']);
        }elseif ($lecture['memberid']==294 && empty($channel['lecturer'])){
            $member = db('member')->field($field)->find(294);
        } else{
            $member = db('member')->field($field)->find($channel['lecturer']);
        }

        if(empty($member['name'])){
            $member['name'] = $member['nickname'];
        }
        $liveroom = db('home')->field('id,memberid')->find($lecture['live_homeid']);
        $result['livehome'] = $liveroom;
        $d_video['push_url'] = '';
        $d_video['push_url'] = '';
        $d_video['img'] = '';
        if ($lecture['mode']=='video' || $lecture['mode']=='vedio'){
            $vedio = db('video')->where(['lecture_id'=>$lecture_id,'isshow'=>'show'])->select();
            if(!empty($vedio)){
                foreach ($vedio as $k=>$v ){
                    if(strpos($v['video'],'rtmp')){
                        $d_video['push_url'] = $v['push_url'];
                        $d_video['pull_url'] = $v['video'];
                    }else{
                        if (eregi_new("mp4$", $v['video'])||eregi_new("m3u8$", $v['video'])){
                            $d_video['pull_url'] = $v['video'];
                        } elseif(eregi_new("webm$", $v['video'])){
                            $d_video['pull_url'] = $v['video'];
                        }
                    }
                }
                $d_video['img'] = $vedio[0]['video_cover'];
                if (!isset($d_video['img'])){
                    $d_video['img'] = $lecture['coverimg'];
                }
            }
        }elseif($lecture['mode']=='ppt'){//待完善

        }
        $result['dvideo'] = $d_video;

        $result['member'] = $member;
        $currentMember = $this->user;
        $result['cmember'] = $currentMember;
        if ($lecture['channel_id']==217 && $currentMember['remarks']=='武汉峰会签到'){ //武汉峰会 会员进入
            $shareTitle = $currentMember['name']."花980元邀请您1元钱收听".$lecture['name'];
        }else{
            $shareTitle = $lecture['name'];
        }
        $result['shareTitle'] = $shareTitle;
        //判断是否已关注直播间
        if(empty($lecture['channel_id'])){
            $result['isattention'] = 0;
        }else{
            $atten = db('attention')->field('id')->where(['memberid'=>$currentMember['id'],'roomid'=>$lecture['channel_id'],'type'=>1])->find();
            if (!empty($atten)) {
                $result['isattention'] = 1;
            } else {
                $result['isattention'] = 0;
            }
        }

        $subscrib = db('subscribe')->field('id')->where(['cid'=>$lecture_id,'mid'=>$currentMember['id']])->find();
        if (!empty($subscrib)) {
            $result['issubscrib'] = 1;
        } else {
            $result['issubscrib'] = 0;
        }
        if (isset($lecture['live_homeid'])&&(!empty($lecture['live_homeid'])) && $lecture['live_homeid']!=0){
            $manager = db('home_manager')->field('id')->where('(homeid='.$lecture['live_homeid'].' or homeid='.$lecture['channel_id'].') AND beinviteid='.$currentMember['id'])->find();
        }
        if (($currentMember['id'] == $lecture['memberid']) || !empty($manager)) {
            $result['isOwner'] = 1;
            $result['isSpeaker'] = 1;
            $result['canSpeak'] = 1;
        } else {
            $result['isOwner'] = 0;
        }
        $invete = db('invete')->field('id')->where(['courseid'=>$lecture_id,'beinviteid'=>$currentMember['id']])->find();
        if (!empty($invete) || !empty($manager)) {
            $result['isSpeaker'] = 1;
            $result['canSpeak'] = 1;
        } else {
            $result['isSpeaker'] = 0;
            $result['canSpeak'] = 0;
        }

        //是否禁言
        $dissenmsg = db('dissenmsg')->field('id')->where(['courseid'=>$lecture['id'],'memberid'=>$currentMember['id']])->find();
        if ($dissenmsg) {
            $result['blocked'] = 1;
        } else {
            $result['blocked'] = 0;
        }
        //评论数

        $dis_count = db('discuss')->where(['lecture_id'=>$lecture['id']])->count();
        $result['dis_count'] = $dis_count;

        //预约人数
        $sub_count = db('subscribe')->where(["cid"=>$lecture['id']])->count();
        if (isset($lecture['basescrib'])){
            $sub_count += $lecture['basescrib'];
        }

        $result['sub_count'] = $sub_count;
        //课程相关人员
       /* $Model = new Model();
        $arr_invete = $Model->table("live_invete i ,live_member m")->where("i.beinviteid=m.id and i.courseid=" . $lecture_id)->field("i.id as iid,m.id as mid,i.invitetype as title,m.headimg,m.intro,m.name,m.nickname")->select();
        $this->assign("invetelist", $arr_invete);*/

        //更新人气
        $cmember = $this->user;
        if ($cmember['id'] != $lecture['memberid']) {
            $lecdata = array(
                'clicknum' => $lecture['clicknum'] + 1,
            );
            db("course")->where(["id"=>$lecture['id']])->update($lecdata);

            //推送给用户
            //推送消息给用户
            /*$data['popular'] = $lecdata['clicknum'];
            $data['lecture_id'] = $lecture['id'];
            Tools::publish_msg(0,$lecture['id'],WORKERMAN_PUBLISH_URL,json_encode($data));*/
        }
        $this->return_json(OK,$result);
    }


    /**
     * 设置直播间
     */
    public function set_classroom()
    {
        $discussr = input('post.discuss'); //是否允许讨论
        $speak = input('post.speak'); //是否自动上墙
        $reward_message = input('post.reward_message');//是否显示打赏信息
        $lecture_id = input('post.lecture_id');
        //数据验证
        $result = $this->validate(
            [
                'lecture_id'  => $lecture_id,
                'discussr' => $discussr,
                'speak' => $speak,
                'reward_message' => $reward_message,
            ],
            [
                'lecture_id'  => 'require|number',
                'discussr'  => 'require|in:0,1',
                'speak'  => 'require|in:0,1',
                'reward_message'  => 'require|in:0,1',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $data['discussr'] = $discussr;
        $data['speak'] = $speak;
        $data['reward_message'] = $reward_message;
        db('crouse')->where(['id'=>$lecture_id])->update($data);
        $this->classroom($lecture_id);
    }


    public function guanzhu()
    {

    }


    /**
     * 获取初始聊天信息
     */
    public function get_messages()
    {
        //$cmember = $this->user;
        $lecture_id = input('post.lecture_id'); //课程id
        $start_date = input('post.start_date'); //开始日期 ，格式2018-08-06 20:00（没有秒），如果不传，则返回全部
        $desired_count = input('post.desired_count');//返回聊天信息条数
        $reverse = input('post.reverse');//为1则返回显示开始日期以前的数据

        $js_memberid = input('post.js_memberid'); //讲师用户id
        $type = input('post.type'); //类型：1为讲师的记录，2为其他用户的记录
        //数据验证
        $result = $this->validate(
            [
                'lecture_id'  => $lecture_id,
                'start_date' => $start_date,
                'desired_count' => $desired_count,
                'reverse' => $reverse,
                'js_memberid' => $js_memberid,
                'type' => $type,
            ],
            [
                'lecture_id'  => 'require|number',
                'start_date'  => 'date',
                'desired_count'  => 'require|number',
                'reverse'  => 'require|in:0,1',
                'js_memberid'  => 'require|number',
                'type'  => 'require|in:1,2',
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
        if($type == 1){
            $sql .= " and sender_id='$js_memberid'";
        }else{
            $sql .= " and sender_id!='$js_memberid'";
        }
        $lecture = db('course')->field('memberid,isonline,name')->find($lecture_id);
        $member = db('member')->field('id,name,headimg,img')->find($js_memberid);

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
            /*if(!strpos($member['headimg'],'http')){
                $member['headimg'] = $member['img'];
            }*/
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


    /**
     * 发送文本消息
     */
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
        /*if(!strpos($member['headimg'],'http')){
            $member['headimg'] = $member['img'];
        }*/
        $data = array(
            'add_time' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
            'content' => $message,
            'length' => 0,
            'message_type' => $message_type,
            'lecture_id' => $lecture_id,
            'ppt_url' => null,
            'reply' => $reply,
            'homeid' => $liveroom['id'],
            'sender_headimg' => ($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
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
                'type' => 'require|in:audio,vedio,picture,music,iframe',
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
       /* if(!strpos($member['headimg'],'http')){
            $member['headimg'] = $member['img'];
        }*/
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


    /**
     * 发送语音消息
     */
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
       /* if(!strpos($member['headimg'],'http')){
            $member['headimg'] = $member['img'];
        }*/
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
            'sender_headimg' =>  ($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
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

    /**
     * 发送图片
     */
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
            /*if(!strpos($member['headimg'],'http')){
                $member['headimg'] = $member['img'];
            }*/
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
                'sender_headimg' =>($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
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


    /**
     * 发送视频消息
     */
    public function send_video_message(){
        $lecture_id = input('post.lecture_id');
        //$path = input('post.path');
        $video_id= input('post.video_id');
        $log_path = APP_PATH.'log/text_message.log';
        $member = $this->user;

        //数据验证
        $result = $this->validate(
            [
                'lecture_id' => $lecture_id,
                'video_id' => $video_id,
            ],
            [
                'lecture_id'  => 'require|number',
                'video_id' =>  'require|number',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }

        $m = db('material')->find($video_id);
        $c = [];
        if ($m['type']=='video'){
            $c['thumb_url'] = $m['cover'];
            $c['video_id'] = $m['id'];
            $c['video_url'] = $m["OSS_path"];
        }else if($m['type']=='audio'){
            $c['audio_url'] = $m['path'];
            $c['thumb_url'] = $m['main'];
            $c['songname'] = $m['songname'];
            $c['audio_id'] = $m['id'];
            $c['singername'] = $m['singer'];
        }
        //LogController::W_A_Log("video_url:".$m['oss_path']);
        wlog($log_path,"video_url:".$m['OSS_path']);
        $invete = db('invete')->field('invitetype')->where("courseid=$lecture_id and beinviteid=" . $member['id'])->find();
        if ($invete) {
            $title = $invete['invitetype'];
        } else {
            $title = "听众";
        }
        $message_type = ($m['type']=='audio')?'music':'video';
        /*if(!strpos($member['headimg'],'http')){
            $member['headimg'] = $member['img'];
        }*/
        $data = array(
            'add_time' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
            'content' => json_encode($c),
            'length' => 0,
            'message_type' => $message_type,
            'lecture_id' => $lecture_id,
            //'ppt_id' => null,
            'ppt_url' => null,
            'reply' => null,
            'homeid' => null,
            'sender_headimg' =>($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
            'sender_id' => $member['id'],
            'sender_nickname' => $member['name'] ? $member['name'] : $member['nickname'],
            'sender_title' => $title,
            'server_id' => null,
        );
        $mid = db('msg')->insertGetId($data);
        //失效缓存
        //MemcacheToolController::Mem_Data_process("live_course_msg_".$lecture_id,'put',null);
        if ($mid) {
            Tools::publish_msg(0,$lecture_id,WORKERMAN_PUBLISH_URL,$this->tranfer($data));
            $this->return_json(OK,$data);
        }else{
            wlog($log_path,'插入消息数据失败');
            $this->return_json(E_OP_FAIL,'消息发送失败');
        }
    }




    /**
     * 上传文件
     * @return mixed
     */
    public function uploadfile()
    {
        return parent::upload_file();
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
}