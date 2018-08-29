<?php
namespace app\index\controller;
use app\tools\controller\Tools;
use think\Request;
use think\Db;
use think\Config;


class Lecture extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    private $log_path = APP_PATH.'log/Lecture.log';//日志路径

    /**
     * 添加课程或专栏：显示封面图片列表
     */
    public function get_cover_list()
    {
        $data = [];
        for($i=1;$i<21;$i++){
            $data[$i-1] = SERVER_URL . "/public/images/cover/cover" . $i . ".jpg";
        }
        $this->return_json(OK,$data);
    }

    /**
     * 获取专栏信息
     */
    public function channel_view()
    {
        $channel_id = input('get.channel_id');
        $result = $this->validate(['channel_id' => $channel_id],['channel_id'  => 'require|number']);
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $data = db('channel')->find($channel_id);
        if(empty($data)){
            $this->return_json(E_OP_FAIL,'没有找到对应专栏');
        }
        $this->return_json(OK,$data);
    }

    /**
     * 添加或编辑专栏/频道
     */
    public function channel_add_edit(){
        $member = $this->user;
        $channel_id = input('post.channel_id');//频道ID
        $money = input('post.money');//固定收费
        $expire = input('post.expire');//收费后期限（单位月）
        $year_money = input('post.year_money');//按时收费
        //$roomid = input('post.liveroom_id');//房间ID
        $name = input('post.name');//专栏标题
        $channel_type = input('post.channel_type');//专栏类型：pay_channel 或 open_channel
        $description = input('post.description');//专栏介绍
        $cover_url = input('post.cover_url');//专栏封面
        //$permanent = input('post.permanent');//
        $priority = input('post.priority');
        $price_list = '';
        $is_pay_only_channel = 0;
        $permanent = 0;
        //数据验证
        $result = $this->validate(
            [
                'channel_id' => $channel_id,
                'name' => $name,
                'expire' => $expire,
                'year_money' => $year_money,
                //'roomid' => $roomid,
                'channel_type' => $channel_type,
                'cover_url' => $cover_url,
                'money' => $money,
                'priority' => $priority,
            ],
            [
                'channel_id'  => 'number',
                'name'  => 'require',
                'expire' =>  'number',
                'year_money' =>  'number',
                //'roomid' =>  'require|number',
                'channel_type' =>  'require|in:open_channel,pay_channel',
                'cover_url' =>  'require',
                'money' =>  'number',
                'priority' =>  'number',
            ]
        );
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $roomid = db('home')->field('id')->where(['memberid'=>$member['id']])->find();
        if(empty($roomid)){
            $this->return_json(E_OP_FAIL,'请先完善个人信息');
        }
        if(!empty($money) && empty($expire) && empty($year_money)){//固定收费
            $permanent = 1;
            $is_pay_only_channel = 1;
        }elseif(empty($money) && !empty($expire) && !empty($year_money)){
            $tmp[0]['expire'] = (int)$expire;
            $tmp[0]['money'] = (int)$year_money;
            $price_list = json_encode($tmp);
            $is_pay_only_channel = 0;
        }else{
            $this->return_json(E_ARGS,'参数错误:请检查费用');
        }

        $reseller_enabled = $resell_percent = 0;

        $data = array(
            'memberid' => $member['id'],
            'roomid' => $roomid['id'],//房间ID
            'create_time' => date("Y-m-d H:i:s"),
            'name' => $name,//专栏名称
            'type' => $channel_type,//pay_channel 或 open_channel
            'description' => $description,//专栏介绍
            //'cover_url' => SERVER_URL . "/public/images/cover/cover" . rand(1, 20) . ".jpg",//封面图片
            'cover_url' => $cover_url,//封面图片
            'money' => $money,//收费金额
            'price_list' => $price_list,//固定+单节收费 的 金额列表 json格式的金额列表
            'priority' => empty($priority)?1:$priority,//优先级
            'is_pay_only_channel' => $is_pay_only_channel,//只付费频道(1)，可付费课程或频道(0)
            'permanent' => $permanent,//固定收费1 或按时收费0
            'reseller_enabled' => $reseller_enabled,//是否开启分销
            'resell_percent' => $resell_percent,//分销比例
        );

        if(!empty($channel_id)){
            $id = db('channel')->where(['id'=>$channel_id])->update($data);
        }else{
            $id = db('channel')->insertGetId($data);
        }
        if($id){
            $res['channel_id'] = $id;
            if(!empty($channel_id)){
                $data['id'] = $id = $channel_id;
                $res = $data;
            }
            wlog($this->log_path,"channel_add_edit 频道/专栏保存成功id为：：".$id."\n");
            $this->return_json(OK,$res);
        }else{
            wlog($this->log_path,"channel_add_edit 频道/专栏保存数据失败：".$id."\n");
            $this->return_json(E_OP_FAIL,'插入数据失败');
        }
    }



    /**
     * 添加课程
     */
    public function lecture_add()
    {
        wlog($this->log_path,"add_lecture 进入保存课程方法");
        $name = input('post.name');//课程标题
        $starttime = input('post.starttime');//开始时间
        $type = input('post.type');//课程类型普通课程，加密课程，付费课程（open_lecture,password_lecture,pay_lecture）
        $pass = input('post.pass');//课程密码
        $cost = input('post.cost');//课程费用
        $mode = input('post.mode');//课程模式：picture图文模式，video视频模式，ppt模式
        $channel_id = input('post.channel_id');
        $reseller_enabled = input('post.reseller_enabled')?input('post.reseller_enabled'):0;
        $resell_percent = input('post.resell_percent')?input('post.resell_percent'):0;
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
                'mode' =>  'require|in:picture,vedio,ppt',
                'channel_id' =>  'require|number',
            ]
        );
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

        if(empty($livehome)){
            $this->return_json(E_OP_FAIL,'请先在 我的-编辑资料 完善个人信息');
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
            'sub_title'=>'',
            'startdate'=>'',
            'intro'=>'',
            'text_content'=>'',
            'audio_content'=>'',
            'qrcode'=>'',
            'attachid'=>'',
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
        $exist_courses = db("course")->where(['name'=>$data['name']])->select();
        //判断该课程是否已建，已建的不再新建

        //开启事务
        Db::startTrans();
        //try {
            if (!$exist_courses) {
                $cid = Db::name('course')->insertGetId($data);
            } else {
                $this->return_json(E_OP_FAIL, '已存在同名课程！');
            }
            if ($cid) {
                wlog($this->log_path, "add_lecture 课程保存成功id为：" . $cid);
                $res['code'] = 0;
                $data['lecture_id'] = $cid;

                //插入场景
                $expend = array(
                    'type' => 'sub_lecture',
                    'memberid' => $this->user['id'],
                    'eventid' => $cid
                );
                $expendid = Db::name("expend")->insertGetId($expend);
                if(!$expendid){
                    Db::rollback();
                    $this->return_json(E_OP_FAIL, '插入expend失败');
                }
                //设置二维码
                $a = $this->setqrcode($cid, $expendid);
                if(empty($a[0]) || empty($a[1])){
                    wlog($this->log_path, "add_lecture 设置二维码失败");
                    Db::rollback();
                    $this->return_json(E_OP_FAIL, '设置二维码失败');
                }
                $invitedata['inviteid'] = $this->user['id'];
                $invitedata['beinviteid'] = $this->user['id'];
                $invitedata['invitetype'] = "讲师";
                $invitedata['is_teacher'] = 1;
                $invitedata['courseid'] = $cid;
                $invitedata['addtime'] = date("Y-m-d H:i:s");
                $icount = Db::name('invete')->insertGetId($invitedata);
                if ($icount) {
                    wlog($this->log_path, "add_lecture 插入课程id为：" . $cid . "的讲师信息ID为：" . $icount);
                } else {
                    Db::rollback();
                    wlog($this->log_path, "add_lecture 插入课程id为：" . $cid . "的讲师信息失败");
                    $this->return_json(E_OP_FAIL, '插入课程的讲师信息失败');
                }
                if ($this->user['id'] != 294) {
                    $invite['inviteid'] = $this->user['id'];
                    $invite['beinviteid'] = 294;
                    $invite['invitetype'] = "主持人";
                    $invite['courseid'] = $cid;
                    $invite['addtime'] = date("Y-m-d H:i:s");
                    $h_count = Db::name('invete')->insertGetId($invite);
                    if ($h_count) {
                        wlog($this->log_path, "add_lecture 插入课程id为：" . $cid . "的主持人信息ID为：" . $h_count);
                    } else {
                        Db::rollback();
                        wlog($this->log_path, "add_lecture 插入课程id为：" . $cid . "的主持人信息失败");
                        $this->return_json(E_OP_FAIL, '插入课程的主持人信息失败');
                    }
                }
                if($mode == 'video'){//视频类型的课程
                    $zhibo_url = $this->get_stream_url($data['starttime'],$cid);
                    $videoinfo = [
                        'addtime' => date("Y-m-d H:i:s") . "." . rand(000000, 999999),
                        'video_cover' => $data['coverimg'],
                        'lecture_id' => $cid,
                        'sender_id' => $this->user['id'],
                        'sender_nickname' => $this->user['name'] ? $this->user['name'] : $this->user['nickname'],
                        'sender_headimg' => ($this->user['headimg'] == $this->user['img']) ? $this->user['headimg'] : $this->user['img'],
                        'sender_title' => $invitedata['invitetype'],
                        'push_url' => $zhibo_url['push_url'],
                        'video' => $zhibo_url['pull_url'],
                    ];
                    $vid = Db::name('video')->insertGetId($videoinfo);
                    if ($vid) {
                        wlog($this->log_path, "add_lecture 插入video信息成功！;id:".$vid);
                    } else {
                        Db::rollback();
                        wlog($this->log_path, "add_lecture 插入课程id为：" . $cid . "的video信息失败");
                        $this->return_json(E_OP_FAIL, '插入课程的主持人信息失败');
                    }
                }

            } else {
                Db::rollback();
                wlog($this->log_path, "add_lecture 课程保存失败！");
                //$res['code'] = 1;
                $this->return_json(E_OP_FAIL,'创建新课程失败');
            }
        Db::commit(); // 提交事务
        wlog($this->log_path, "add_lecture 创建新课程ID为" . $cid . "开始推送消息提醒！");
        $this->push_lecture_notify("lectureadd", $cid); //添加新课程后推送给已经购买专栏的学员
        /*}catch (\Exception $e) {
            wlog($this->log_path, "add_lecture 创建新课程失败 已回滚");
            Db::rollback();
            $this->return_json(E_OP_FAIL,'创建新课程失败 已回滚');
        }*/
        $this->return_json(OK,$data);
    }

    /**
     * 编辑课程
     */
    public function lecture_edit()
    {
        $cid = input('post.lecture_id');//课程id
        $name = input('post.name');//课程标题
        $starttime = input('post.starttime');//开始时间
        $type = input('post.type');//课程类型普通课程，加密课程，付费课程（open_lecture,password_lecture,pay_lecture）
        $pass = input('post.pass');//课程密码
        $cost = input('post.cost');//课程费用
        $coverimg = input('post.coverimg');//课程封面
        $intro = input('post.intro');//课程介绍
        $priority = (int)input('post.priority');//课程优先级
        $mode = input('post.mode');//课程模式：picture图文模式，vedio视频模式，ppt模式
        $reseller_enabled = input('post.reseller_enabled')?input('post.reseller_enabled'):0;
        $resell_percent = input('post.resell_percent')?input('post.resell_percent'):0;
        $tag = input('post.tag');
        $labels = input('post.labels');
        //数据验证
        $result = $this->validate(
            [
                'name' => $name,
                'cid' => $cid,
                'starttime' => $starttime,
                'type' => $type,
                'pass' => $pass,
                'cost' => $cost,
                'mode' => $mode,
                //'coverimg' => $coverimg,
                'priority' => $priority,
            ],
            [
                'name'  => 'require',
                'cid'  => 'require|number',
                'starttime' =>  'require|date',
                'type' =>  'require|in:open_lecture,password_lecture,pay_lecture',
                'pass' =>  'alphaNum',
                'cost' =>  'number',
                'mode' =>  'require|in:picture,vedio,ppt',
                //'coverimg' =>  'url',
                'priority' =>  'require|number',
            ]
        );
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

        $data = array(
            'name' => $name,
            'starttime' => date('Y-m-d H:i', strtotime($starttime)),
            'type' =>$type,
            'mode' => $mode,
            'pass' => $pass,
            'cost' => $cost ? $cost : 0,
            'coverimg' => $coverimg,
            'intro' => $intro,
            'priority' => $priority,
        );
        $is = db('course')->where(['id'=>$cid])->update($data);
        if(empty($is)){
            wlog($this->log_path, "add_lecture 课程保存失败！");
            $this->return_json(E_OP_FAIL,'保存课程失败');
        }
        $data['id'] = $cid;
        $this->return_json(OK,$data);
    }

    /**
     * 获取课程信息
     */
    public function get_lecture_info()
    {
        $lecture_id = input('get.lecture_id');
        $result = $this->validate(['lecture_id' => $lecture_id,],['lecture_id'  => 'require|number',]);
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }
        $data = db('course')->find($lecture_id);
        if(empty($data)){
            $this->return_json(E_OP_FAIL,'没有找到对应课程');
        }
        $this->return_json(OK,$data);
    }

    /**
     * 获取讲师信息
     */
    public function get_jiangshi()
    {

    }


    //设置课程二维码
    public function setqrcode($id, $expendid, $qrpath='', $source='course')
    {
        if ($id) {
            //创建二维码
            /*$member = $this->user;
            $mid = $member['id'];*/
            $wechatfun = Factory::create_obj('wechat');
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
                    //"qrcode_addtime" => date("Y-m-d H:i")
                );
                $a[0] = Db::name($source)->where("id=" . $id)->update($update_data);
                $a[1] = Db::name("expend")->where("id=" . $expendid)->update($update_data);
                return $a;
            } else {
                $res = $wechatfun->getQRCode($q_content, $qrpath, 1, 2592000);
                if ($res == 0) {
                    $wechatfun->getQRCode($q_content, $qrpath, 1, 2592000);
                }
            }
        }
    }


    /**
     * 推送课程给预约人员
     * type:lectureadd 时推送给购买了专栏的学员
     */
    public function push_lecture_notify($type = '',$lectureid = 0)
    {
        $wechat = Factory::create_obj('wechat');
        if ((!empty($type))&&($type == 'lectureadd')) {
            $lecture = db('course')->field('name,starttime,channel_id')->find($lectureid);
            $channel_id = $lecture['channel_id'];
            if ($channel_id > 0) {
                $pay_list = db('channelpay')->where("channelid=" . $channel_id . " and status='finish'")->select();
                $url = "http://tianyan199.com/index.php/Home/Lecture/index?id=$lectureid";
                foreach ($pay_list as $k => $v) {
                    $member = db('member')->find($v['memberid']);
                    //LogController::W_H_Log("推送给".$member['name']."消息提醒！！");
                    wlog($this->log_path, "push_lecture_notify 推送给" . $member['name'] . "消息提醒！！");
                    //推送图文消息给用户
                    $data = array(
                        'userName' => array('value' => urlencode($member['name'] ? $member['name'] : $member['nickname']), 'color' => "#743A3A"),
                        'courseName' => array('value' => urlencode($lecture['name']), 'color' => '#173177'),
                        'date' => array('value' => urlencode($lecture['starttime']), 'color' => '#173177'),
                        'remark' => array('value' => urlencode('\n点击查看详情！'), 'color' => '#173177'),
                    );
                    if(is_numeric($member['openid'])){
                        wlog($this->log_path, "push_lecture_notify 用户" . $member['name'] . "为非微信注册用户，无法推送！");
                    }else{
                        $wechat->doSendTempleteMsg($member['openid'], Config::get('template_code.sub_publish'), $url, $data, $topcolor = '#7B68EE');
                    }
                }
            }
        }else{
            $lecture_id = $_POST['lecture_id'];
            if ($lecture_id) {
                $sub_list = db('subscribe')->where("cid=" . $lecture_id)->select();
                if ($sub_list) {
                    $url = "http://tianyan199.com/index.php/Home/Lecture/index?id=$lecture_id";
                    $lecture = db('course')->find($lecture_id);
                    foreach ($sub_list as $k => $v) {
                        $member = db('member')->find($v['mid']);
                        //  $member = M('member')->getField("name,nickname,openid")->find($v['mid']);
                        //推送图文消息给用户
                        $data = array(
                            'userName' => array('value' => urlencode($member['name'] ? $member['name'] : $member['nickname']), 'color' => "#743A3A"),
                            'courseName' => array('value' => urlencode($lecture['name']), 'color' => '#173177'),
                            'date' => array('value' => urlencode($lecture['starttime']), 'color' => '#173177'),
                            'remark' => array('value' => urlencode('\n点击查看详情！'), 'color' => '#173177'),
                        );
                        $wechat->doSendTempleteMsg($member['openid'], Config::get('template_code.sub_publish'), $url, $data, $topcolor = '#7B68EE');
                    }
                }
                $res['code'] = 'success';
            } else {
                $res['code'] = 'fail';
            }
            $this->return_json(OK,$res);
        }
    }


    /**
     * 获取阿里云推流地址与拉流地址
     * @param string $starttime
     * @param int $cid
     * @return mixed
     */
    public function get_stream_url($starttime = '',$cid = 0)
    {
        if(empty($starttime)){
            $starttime = date('Ymd');
        }
        $yxqtime = strtotime($starttime)+86400;//推/拉流地址有效期
        //$rand = time().rand(100,999);
        $rand = 0;
        $StreamName = LIVE_STREAMNAME_LEFT.$cid.rand(100,999);
        $strpush = '/'.LIVE_APPNAME.'/'.$StreamName.'-'.$yxqtime.'-'.$rand.'-0-'.LIVE_AUTH_KEY;
        $strflv =  '/'.LIVE_APPNAME.'/'.$StreamName.'.flv-'.$yxqtime.'-'.$rand.'-0-'.LIVE_AUTH_KEY;
        $md5 = md5($strpush);
        $auth_key = $yxqtime.'-'.$rand.'-0-'.$md5;
        $data['push_url'] = LIVE_URL.LIVE_APPNAME.'/'.$StreamName.'?vhost='.LIVE_VHOST.'&auth_key='.$auth_key;
        $data['pull_url'] = 'rtmp://'.LIVE_VHOST.'/'.LIVE_APPNAME.'/'.$StreamName.'?auth_key='.$auth_key;
        $flvurl = 'http://'.LIVE_VHOST.'/'.LIVE_APPNAME.'/'.$StreamName.'.flv?auth_key='.$yxqtime.'-'.$rand.'-0-'.md5($strflv);
        wlog($this->log_path, "get_stream_url flv拉流地址为： $flvurl");
        //$this->return_json(OK,$data);exit;
        return $data;
    }

}
