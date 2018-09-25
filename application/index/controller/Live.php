<?php
namespace app\index\controller;
use app\tools\controller\Signature;
use app\tools\controller\Tools;
use think\Db;


class Live extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    private $log_path = APP_PATH.'log/Live.log';//日志路径;

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
                $channel['lecturer'] = BANZHUREN;
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
        if($lecture['memberid']!=BANZHUREN){
            $member = db('member')->field($field)->find($lecture['memberid']);
        }elseif ($lecture['memberid']==BANZHUREN && empty($channel['lecturer'])){
            $member = db('member')->field($field)->find(BANZHUREN);
        } else{
            $member = db('member')->field($field)->find($channel['lecturer']);
        }

        if(empty($member['name'])){
            $member['name'] = $member['nickname'];
        }
        $liveroom = db('home')->field('id,memberid')->find($lecture['live_homeid']);
        $result['livehome'] = $liveroom;
        $d_video['push_url'] = '';
        $d_video['pull_url'] = '';
        $d_video['img'] = '';
        if ($lecture['mode']=='video' || $lecture['mode']=='vedio'){
            $vedio = db('video')->where(['lecture_id'=>$lecture_id,'isshow'=>'show'])->select();
            //var_dump($vedio);exit;
            if(!empty($vedio)){
                foreach ($vedio as $k=>$v ){
                    if(strstr($v['video'],'rtmp')){
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
        //$d_video['pull_url'] = urlencode($d_video['pull_url']);
        $result['dvideo'] = $d_video;

        $result['js_member'] = $member;
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
            /*$atten = db('attention')->field('id')->where(['memberid'=>$currentMember['id'],'roomid'=>$lecture['channel_id'],'type'=>1])->find();
            if (!empty($atten)) {
                $result['isattention'] = 1;
            } else {
                $result['isattention'] = 0;
            }*/
            $result['isattention'] =  $this->is_attention($currentMember['id'],$lecture['channel_id']);
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
        //课程相关人员(拥有讲师的权限)
        //$Model = new Model();
        $arr_invete = db()->table("live_invete i ,live_member m")->where("i.beinviteid=m.id and i.courseid=" . $lecture_id)->field("i.id as invete_id,m.id as js_memberid,i.invitetype as title,m.headimg,m.intro,m.name,m.nickname")->select();
        //$this->assign("invetelist", $arr_invete);
        $result['invetelist'] = $arr_invete;
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

    /**
     * 关注专栏
     */
    public function attention_channel()
    {
        $lecture_id = input('post.lecture_id');
        $js_memberid = input('post.js_memberid');
        $type = input('post.type');
        if(empty($type)){
            $type = 1;
        }
        //数据验证
        $result = $this->validate(
            [
                'lecture_id'  => $lecture_id,
                'js_memberid'  => $js_memberid,
                'type'  => $type,
            ],
            [
                'lecture_id'  => 'require|number',
                'js_memberid'  => 'number',
                'type'  => 'number|in:1,2',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        if(!empty($js_memberid)){
            $cidarr = db('channel')->field('id')->where('memberid='.$js_memberid.' or lecturer='.$js_memberid)->where(['isshow'=>'show'])->select();
            //var_dump($cidarr);exit;
            $cidarr  = array_column($cidarr,'id');
        }else{
            $l  = db('course')->field('channel_id')->find($lecture_id);
            if(empty($l['channel_id'])){
                $cidarr[0] = BANZHUREN;
            }else{
                $cidarr[0] = $l['channel_id'];
            }
        }
        $a = $b = [];
        foreach($cidarr as $key => $channel_id){
            $data['memberid'] = $this->user['id'];
            $data['roomid'] = $channel_id;
            $data['type'] = 1;
            if($type==1){
                $y = db('attention')->where($data)->find();
                $a[$key] = 1;
                if(empty($y)){
                    //$this->return_json(E_OP_FAIL,'请不要重复关注');
                    $data['create_time'] = date('Y-m-d H:i:s');
                    $a[$key] = db('attention')->insertGetId($data);
                }
            }else{
                $b[$key] = db('attention')->where($data)->delete();
            }
        }
        if($type==1) {
            if (in_array(0,$a)) {
                $this->return_json(E_OP_FAIL, '关注失败');
            }
            $this->return_json(OK, ['cmemberid' => $this->user['id'], 'isattention' => 1, 'msg' => '关注成功']);
        }else{
            if(in_array(0,$b)){
                $this->return_json(E_OP_FAIL,'取消关注失败');
            }
            $this->return_json(OK,['cmemberid'=>$this->user['id'],'isattention'=>0,'msg'=>'取消关注成功']);
        }
    }

    /**
     * 主播创建签到
     */
    public function create_check_in(){
        $lecture_id = input('post.lecture_id');
        //数据验证
        $result = $this->validate(
            [
                'lecture_id'  => $lecture_id,
            ],
            [
                'lecture_id'  => 'require|number',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $is = $this->redis->hget('check_in',$lecture_id);
        if(!empty($is)){
            $this->return_json(E_OP_FAIL,'已发起签到');
        }
        $data = array(
            'add_time' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
            'content' => "老师喊你来签到啦！",
            'length' => 0,
            'message_type' => "check_in",
            'lecture_id' => $lecture_id,
            'ppt_url' => null,
            'reply' => null,
            'homeid' => null,
            'sender_headimg' => ($this->user['headimg'] == $this->user['img']) ? $this->user['headimg'] : $this->user['img'],
            'sender_id' => $this->user['id'],
            'sender_nickname' => $this->user['name'],
            'sender_title' => "讲师",
            'server_id' => null,
        );
        Db::startTrans();
        $count = db('msg')->insertGetId($data);
        if(empty($count)){
            Db::rollback();
            wlog(APP_PATH.'log/text_message.log','插入消息数据失败1');
            $this->return_json(E_OP_FAIL,'消息发送失败1');
        }
        $check_in = db('checkin')->where('lecture_id='.$lecture_id.' AND mid is null')->find();
        if(empty($check_in)){
            $cdata = array(
                'lecture_id' => $lecture_id,
                'addtime' => date("Y-m-d H:i:s") . "." . rand(000000, 999999)
            );
            $check_count = db('checkin')->insertGetId($cdata);
            if($check_count){
                $msg = db('msg')->where('message_id='.$count)->setField('remarks',$check_count);
            }else{
                Db::rollback();
                wlog(APP_PATH.'log/text_message.log','插入消息数据失败2');
                $this->return_json(E_OP_FAIL,'消息发送失败2');
            }
        }else{
            $msg = db('msg')->where('message_id='.$count)->setField('remarks',$check_in['id']);
        }
        if (empty($msg)) {
            Db::rollback();
            wlog(APP_PATH.'log/text_message.log','插入消息数据失败3');
            $this->return_json(E_OP_FAIL,'消息发送失败3');
        }
        Db::commit();
        $data['remarks'] = empty($check_in['id'])?$check_count:$check_in['id'];
        $data['message_id'] = $count;
        $this->redis->hset('check_in',$lecture_id,$data['remarks']);
        Tools::publish_msg(0,$lecture_id,WORKERMAN_PUBLISH_URL,$this->tranfer($data));
        $this->return_json(OK,$data);
    }

    /**
     * 听众签到
     */
    public function check_in(){
        $check_in_id = input('post.check_in_id');
        //数据验证
        $result = $this->validate(
            [
                'check_in_id'  => $check_in_id,
            ],
            [
                'check_in_id'  => 'require|number',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }

        $checkin = db('checkin');
        $lecture_id = $checkin->where('id='.$check_in_id)->value('lecture_id');
        //$member = $this->user;
        $mid = $checkin->where('check_in_id='.$check_in_id.' AND mid='.$this->user['id'])->find();
        if(!$mid){
            $data = array(
                'check_in_id' => $check_in_id,
                'lecture_id' => $lecture_id,
                'mid' => $this->user['id'],
                'addtime' => date("Y-m-d H:i:s") . "." . rand(000000, 999999)
            );
            $checkin->insertGetId($data);
        }
        $check = $checkin->where('check_in_id='.$check_in_id)->select();
        $res = [];
        for($i=0;$i<count($check);$i++){
            if($check[$i]['mid']==$this->user['id']){
                $res['rank'] = $i+1;
            }
        }
        $res['check_in_count'] = count($check);
        $res['msg'] = '签到成功';
        $this->return_json(OK,$res);
    }


    /**
     * 显示签到人员列表
     */
    public function show_check_in(){
        $check_in_id = input('post.check_in_id');
        $limit = input('post.limit');//页码
        $leng = input('post.leng');//每页数量
        //数据验证
        $result = $this->validate(
            [
                'check_in_id'  => $check_in_id,
                'limit'  => $limit,
                'leng'  => $leng,
            ],
            [
                'check_in_id'  => 'require|number',
                'limit'  => 'require|number|min:1',
                'leng'  => 'require|number|min:1',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        //$check_in_id = $_REQUEST['check_in_id'];
        //$limit = $_REQUEST['limit'];
        $checkin = db('checkin');
        $checkin->field('mid')->where('check_in_id='.$check_in_id);
        $members = $checkin->limit($limit-1,$leng)->select();
        $count = $checkin->where('check_in_id='.$check_in_id)->count();
        $data = [];
        foreach($members as $key=>$val){
            $member_info = db('member')->where('id='.$val['mid'])->field('id,name,nickname,headimg')->find();
            $data[$key]['memberid'] = $member_info['id'];
            $data[$key]['nickname'] = $member_info['name'] ? $member_info['name'] : $member_info['nickname'];
            $data[$key]['headimgurl'] = $member_info['headimg'];
        }
        $res['list'] = $data;
        $res['count'] = $count;
        $res['limit'] = $limit;
        $this->return_json(OK,$data);
    }

    /**
     * 撤回消息
     */
    public function revoke()
    {
        $message_id = input('post.message_id');
        //数据验证
        $result = $this->validate(
            [
                'message_id'  => $message_id,
            ],
            [
                'message_id'  => 'require|number',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $a = db('msg')->where(['message_id'=>$message_id])->update(['isshow'=>'hiden']);
        if(empty($a)){
            $this->return_json(E_OP_FAIL,'撤回失败');
        }
        $this->return_json(OK,['cmemberid'=>$this->user['id']]);
    }

    /**
     * 上墙
     */
    public function upwall()
    {
        $message_id = input('post.message_id');
        //数据验证
        $result = $this->validate(
            [
                'message_id'  => $message_id,
            ],
            [
                'message_id'  => 'require|number',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $msg = db('msg')->where(['message_id'=>$message_id,'isshow'=>'show'])->update(['message_type'=>'publish']);
        if(empty($msg)){
            $this->return_json(E_OP_FAIL,'上墙失败,找不到该消息或重复上墙');
        }
        $message = db('msg')->where(['message_id'=>$message_id])->find();
        Tools::publish_msg(0,$message['lecture_id'],WORKERMAN_PUBLISH_URL,$this->tranfer($message));
        $this->get_upwall_list($message['lecture_id']);
        //$this->return_json(OK,$msg);
    }

    /**
     * 获取上墙历史记录
     */
    public function get_upwall_list($lecture_id = '')
    {
        if(empty($lecture_id)){
            $lecture_id = input('post.lecture_id');
        }
        //数据验证
        $result = $this->validate(
            [
                'lecture_id'  => $lecture_id,
            ],
            [
                'lecture_id'  => 'require|number',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $field = 'message_id,sender_id,sender_nickname,sender_headimg,sender_title,lecture_id,message_type,add_time,content,isvipshow,isshow,reply,ppt_url,out_trade_no,remarks';
        $msg = db('msg')->field($field)->where(['lecture_id'=>$lecture_id,'message_type'=>'publish','isshow'=>'show'])->select();
        $this->return_json(OK,(array)$msg);
    }



    /**
     * 获取初始聊天信息
     */
    public function get_messages($canshu = [])
    {
        //$cmember = $this->user;
        if(empty($canshu)){
            $lecture_id = input('post.lecture_id'); //课程id
            $start_date = input('post.start_date'); //开始日期 ，格式2018-08-06 20:00（没有秒），如果不传，则返回全部
            $desired_count = input('post.desired_count');//返回聊天信息条数
            $reverse = input('post.reverse');//为1则返回显示开始日期以前的数据
            $page = input('post.page');//页码
            if(empty($reverse)){
                $reverse = 0;
            }
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
                    'page' => $page,
                ],
                [
                    'lecture_id'  => 'require|number',
                    'start_date'  => 'date',
                    'desired_count'  => 'number',
                    'reverse'  => 'in:0,1',
                    'js_memberid'  => 'require|number',
                    'type'  => 'require|in:1,2',
                    'page'  => 'number',
                ]
            );
            if($result !== true){
                $this->return_json(E_ARGS,'参数错误');
            }
        }else{
            $lecture_id = $canshu['lecture_id']; //课程id
            $start_date = $canshu['start_date']; //开始日期 ，格式2018-08-06 20:00（没有秒），如果不传，则返回全部
            $desired_count = $canshu['desired_count'];//返回聊天信息条数
            $reverse = $canshu['reverse'];//为1则返回显示开始日期以前的数据
            $page = $canshu['page'];//页码
        }


        $page = !empty($page) ? $page : 1;
        $desired_count = !empty($desired_count) ? $desired_count : 1000;
        $where['id'] = $lecture_id;
        $where['isshow'] = 'show';

        $sql = "lecture_id=" . $lecture_id . " and isshow='show' and message_type != 'set_option' and message_type != 'iframe'";
        //$sql_bak = "lecture_id=" . $lecture_id;
        $allcount = db('msg')->where($sql)->count();
        if (!empty($start_date) && $reverse==0) {
            $sql .= " and add_time>='$start_date'";
        }elseif (!empty($start_date) && $reverse==1){
            $sql .= " and add_time<='$start_date'";
        }

        //$lecture = db('course')->alias('a')->join('channel_id')->field('memberid,isonline,name')->find($lecture_id);
        /*if($type == 1){
            $sql .= " and sender_id='$js_memberid'";
        }else{
            $sql .= " and sender_id!='$js_memberid'";
        }*/
        $lecture = db('course')->field('memberid,isonline,name,channel_id,live_homeid')->find($lecture_id);
        $member = db('member')->field('id,name,headimg,img')->find($js_memberid);
        $field = 'message_id,sender_id,sender_nickname,sender_headimg,sender_title,lecture_id,message_type,add_time,content,isvipshow,isshow,reply,ppt_url,out_trade_no,remarks';
        $listmsg = db('msg')->field($field)->where($sql)->limit($page-1,$desired_count)->order("add_time desc")->select();

        /*if ($reverse == 0){
            if (!empty($start_date)){
                //$listmsg = MemcacheToolController::Mem_Data_process($tmsql,'course_msg',$lecture_id);
                $listmsg = db('msg')->field($field)->where($sql)->limit($desired_count)->select();
            }else{
                $listmsg = array();
            }
        }elseif ($reverse==1){
            //$listmsg = MemcacheToolController::Mem_Data_process($tmsql,'course_msg',$lecture_id);
            $listmsg = db('msg')->field($field)->where($sql)->limit($desired_count)->order("add_time desc")->select();
        }*/
        /*LogController::W_H_Log("msg 长度：".sizeof($listmsg,0));
        LogController::W_H_Log("sql is:".$sql);*/
        if (empty($listmsg)) { //没有消息时
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
                    //'ppt_id' => null,
                    'ppt_url' => null,
                    'reply' => null,
                    //'sender_headimg' => ($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
                    'sender_headimg' => $member['headimg'],
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
        }else{
            //$mstarr = ['text','reply_text','reply_audi'];//互动页显示的消息类型
            $js_arr = [$js_memberid,BANZHUREN];
            //$mstarr2 = ['text','audio','reply_text','reply_audi','check_in','music','picture','reward','video','publish'];//主讲页显示的消息类型
            //$lecture_listmsg_bak = $lecture_listmsg = $listmsg_bak = $arr_invete = [];
            $arr_invete = db()->table("live_invete i ,live_member m")->field("m.id as js_memberid")->where("i.beinviteid=m.id and i.courseid=" . $lecture_id)->select();
            if (!empty($arr_invete)){
                $arr_invete = array_column($arr_invete,'js_memberid');
            }
            $manager = [];
            if (!empty($lecture['live_homeid'])){
                $manager = db('home_manager')->field('beinviteid')->where('homeid='.$lecture['live_homeid'])->select();
                if(!empty($manager)){
                    $manager = array_column($manager,'beinviteid');
                }
            }
            $js_arr = array_unique(array_merge($js_arr,$manager,$arr_invete));
            //echo '<pre>';var_dump($js_arr);exit;
            $js_str = implode(',',$js_arr);
            $listmsg_sendname_list = db('msg')->field('sender_nickname')->where(['lecture_id'=>$lecture_id])->where('sender_id in ('.$js_str.')')->group('sender_nickname')->select();
            $listmsg_sendname_list = array_column($listmsg_sendname_list,'sender_nickname');
            //var_dump($listmsg_sendname_list);exit;
            if($type == 1){ //筛选内容。type为1时显示主讲页，为2时显示互动页内容
                foreach($listmsg as $key=> $value){
                    if(in_array($value['sender_id'],$js_arr)){
                        $listmsg_bak[$key] = $listmsg[$key];
                    }
                    if ($value['message_type'] == 'reward'){
                        $listmsg_bak[$key] = $listmsg[$key];
                    }
                    if ($value['message_type'] == 'reply_text' || $value['message_type'] == 'reply_audi') {
                        $replyarr = explode(':', $value['reply']);
                        if (!in_array($replyarr[0], $listmsg_sendname_list)) {
                            unset($listmsg_bak[$key]);
                        }
                    }
                }
                $listmsg = array_values($listmsg_bak);
            }else{
                foreach($listmsg as $key=> $value){
                    if(in_array($value['sender_id'],$js_arr)){
                        unset($listmsg[$key]);
                    }
                    if ($value['message_type'] == 'reward'){
                        unset($listmsg[$key]);
                    }
                    if ($value['message_type'] == 'reply_text' || $value['message_type'] == 'reply_audi') {
                        $replyarr = explode(':', $value['reply']);
                        if (!in_array($replyarr[0], $listmsg_sendname_list)) {
                            $listmsg[$key] = $value;
                        }
                    }
                }
            }
            $listmsg = array_values($listmsg);
        }
        $res['data'] = $listmsg;
        $res['mark'] = $start_date;
        $res['count'] = $allcount;
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
            $data['message_id'] = $count;
            Tools::publish_msg(0,$lecture_id,WORKERMAN_PUBLISH_URL,$this->tranfer($data));
            $this->return_json(OK,$data);
        }else{
            wlog(APP_PATH.'log/text_message.log','插入消息数据失败');
            $this->return_json(E_OP_FAIL,'消息发送失败');
        }
    }




    //发送文件相关的消息：包括语音，图片，视频
    public function send_file_message()
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
           // 'sender_headimg' => ($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
            'sender_headimg' => $member['headimg'],
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
    public function send_voice_message()
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
            // 'sender_headimg' => ($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
            'sender_headimg' => $member['headimg'],
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
            $data['message_id'] = $count;
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
    public function send_picture_message()
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
                // 'sender_headimg' => ($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
                'sender_headimg' => $member['headimg'],
                'sender_id' => $member['id'],
                'sender_nickname' => $member['name'] ? $member['name'] : $member['nickname'],
                'sender_title' => $title,
                //'server_id' => $media_id,
            );
            $count = db('msg')->insertGetId($data);
            if ($count) {
                $data['message_id'] = $count;
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
            // 'sender_headimg' => ($member['headimg'] == $member['img']) ? $member['headimg'] : $member['img'],
            'sender_headimg' => $member['headimg'],
            'sender_id' => $member['id'],
            'sender_nickname' => $member['name'] ? $member['name'] : $member['nickname'],
            'sender_title' => $title,
            'server_id' => null,
        );
        $mid = db('msg')->insertGetId($data);
        //失效缓存
        //MemcacheToolController::Mem_Data_process("live_course_msg_".$lecture_id,'put',null);
        if ($mid) {
            $data['message_id'] = $mid;
            Tools::publish_msg(0,$lecture_id,WORKERMAN_PUBLISH_URL,$this->tranfer($data));
            $this->return_json(OK,$data);
        }else{
            wlog($log_path,'插入消息数据失败');
            $this->return_json(E_OP_FAIL,'消息发送失败');
        }
    }


    /**
     * 直播完成，更新直播间拉流地址为录制的视频地址
     */
    public function save_video_url()
    {
        $lecture_id = input('get.lecture_id');
        //$str = 'tianyansxy/record/tianyansxy/ty_stream1846292/2018-09-18-10-58-46_2018-09-18-11-03-26.mp4';
        /*if(empty($lecture_id)){
            $lecture_id = input('post.lecture_id');
        }*/
        /*if(empty($endtime)){
            $endtime = date('Y-m-d H:i:s');
        }*/
        //数据验证
        $result = $this->validate(
            [
                'lecture_id' => $lecture_id,
                /*'starttime' => $starttime,
                'endtime' => $endtime,*/
            ],
            [
                'lecture_id'  => 'require|number',
               /* 'starttime' =>  'require|date',
                'endtime' =>  'date',*/
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $video = db('video')->field('video')->where(['lecture_id'=>$lecture_id])->find();
        if(empty($video['video'])){
            $this->return_json(E_OP_FAIL,'找不到播放地址');
        }
        if(!strstr($video['video'],'rtmp')){
            $this->return_json(E_OP_FAIL,'该播放地址不是rtmp地址');
        }
        $lecture = db('course')->field('id,starttime')->where(['id'=>$lecture_id])->find();
        if(empty($lecture)){
            $this->return_json(E_OP_FAIL,'找不到对应课程');
        }
        $starttime = strtotime($lecture['starttime']);//暂时用addtime,上线后用starttime
        $endtime = $starttime+86400;
        $rtmp_arr = parse_url($video['video']);
        $stearm = explode('/',$rtmp_arr['path']);
        $stearmname = end($stearm);
        //$starttime = strtotime($starttime);
        $starttime = date('Y-m-d',$starttime-86400).'T'.date('H:i:s',$starttime).'Z';
        //$endtime = strtotime($endtime);
        $endtime = date('Y-m-d',$endtime).'T'.date('H:i:s',$endtime).'Z';
        $arr = [
            'Action'=>'DescribeLiveStreamRecordIndexFiles',
            'DomainName'=>LIVE_VHOST,
            'AppName'=>LIVE_APPNAME,
            'StreamName'=>$stearmname,
            'StartTime'=>$starttime,
            'EndTime'=>$endtime,
        ];
        $url = "https://live.aliyuncs.com/?";
        $obj = new Signature($arr,$url);
        $res = $obj->callInterface();
        $str = '';
        if(empty($res['status'])){
            $this->return_json(E_OP_FAIL,$res['msg']);
        }
        $oss_arr = json_decode($res['msg'],true);

        if(empty($oss_arr['RecordIndexInfoList']['RecordIndexInfo'][0]['OssObject'])){
            $this->return_json(E_OP_FAIL, '没有找到录制的视频');
        }
        $oss_obj = $oss_arr['RecordIndexInfoList']['RecordIndexInfo'][0]['OssObject'];
        $video_url = Tools::get_oss_url_sign($oss_obj,OSS_LUZHI_TIMEOUT);
        $videoinfo = ['video' => $video_url];
        $vid = db('video')->where(['lecture_id'=>$lecture_id])->update($videoinfo);
        if ($vid) {
            wlog($this->log_path, "save_video_url 修改课程id为：" . $lecture_id . "的video字段信息成功");
        } else {
            wlog($this->log_path, "save_video_url 修改课程id为：" . $lecture_id . "的video字段信息失败");
            $this->return_json(E_OP_FAIL, '修改视频信息失败');
        }

        $this->return_json(OK,['pull_url'=>$video_url]);
        //var_dump($stearmname);exit;
        /*$url = 'https://live.aliyuncs.com/?Action=DescribeLiveStreamRecordContent&DomainName='.LIVE_VHOST.'&AppName='.LIVE_APPNAME.'&StreamName='
            .$stearmname.'&StartTime='.$starttime.'&EndTime='.$endtime.'&Format=json&Version=2016-11-01'
            .'&SignatureMethod=HMAC-SHA1&SignatureNonce='.time().mt_rand(100,999).'&SignatureVersion=1.0&AccessKeyId='.ALIYUN_ACCESS_KEY_ID.'&Timestamp='.date('Y-m-d').'T'.date('H:i:s').'Z';*/
        // 'https://live.aliyuncs.com/?Action=DescribeLiveStreamRecordContent&DomainName=live.aliyunlive.com&AppName=aliyuntest&StreamName=xxx&StartTime=xxx&EndTime=xxx&<公共请求参数>';
    }


    /**
     * 上传文件
     * @return mixed
     */
    public function uploadfile()
    {
        return parent::upload_file();
    }

}