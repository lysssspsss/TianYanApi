<?php
namespace app\index\controller;
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
            $status = Time::timediff(strtotime($lecture['starttime']), time(), $lecture['mins']);
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
           /* $publish_url=C('workerman_publish_url');
            $data['popular'] = $lecdata['clicknum'];
            $data['lecture_id'] = $lecture['id'];
            ToolsController::publish_msg(0,$lecture['id'],$publish_url,json_encode($data));*/
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

    public function send_message()
    {

    }

}