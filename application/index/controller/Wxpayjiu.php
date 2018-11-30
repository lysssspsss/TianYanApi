<?php
namespace app\index\controller;
use Think\Exception;
use app\tools\controller\Tools;
use Think\Log;
use think\Request;
use think\Db;
use think\Config;


class Wxpayjiu extends Base
{
    private $log_path = APP_PATH.'log/Wxpay_IOS.log';//日志路径
    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
        /*vendor('wxpay.JsApiPay');
        vendor('wxpay.log');
        vendor('wxpay.notify');
        vendor('wxpay.WxPayNativePay');*/
        import('wxpay.JsApiPay',EXTEND_PATH,EXT);
        import('wxpay.log',EXTEND_PATH,EXT);
        import('wxpay.notify',EXTEND_PATH,EXT);
        import('wxpay.WxPayNativePay',EXTEND_PATH,EXT);
    }

    //打印输出数组信息
    public function printf_info($data)
    {
        foreach($data as $key=>$value){
            echo "<font color='#00ff55;'>$key</font> : $value <br/>";
        }
    }

    //http://local.livehome.com/index.php/Home/WxJsAPI/jsApiCall?product=pay_channel&target=294&fee=19900&expire=12&channel_id=415
    //http://local.livehome.com/index.php/Home/WxJsAPI/jsApiCall?product=reward&target=294&fee=200&lecture_id=1862
    //http://local.livehome.com/index.php/Home/WxJsAPI/jsApiCall?product=pay_channel&target=294&fee=9900&expire=null&channel_id=196  支付专栏
    //http://local.livehome.com/index.php/Home/WxJsAPI/jsApiCall?product=pay_lecture&target=294&fee=500&lecture_id=1847  支付单节
    /**
     * IOS支付接口
     */
    public function js_api_call()
    {
        //$this->return_json(OK,['msg'=>'支付成功']);
        //LogController::W_P_Log("进入支付方法!");
        if($this->source != 'IOS'){
            $this->return_json(OK,['msg'=>'支付成功']);
        }
        wlog($this->log_path,"jsApiCall 进入支付方法");

        $lecture_id = input('post.lecture_id');
        $channel_id =input('post.channel_id');
        $book_id =input('post.book_id');
        $channel_expire = input('post.expire');
        $fee = input('post.fee');
        $target = input('post.js_memberid');
        $product = input('post.product'); // pay_lecture 支付课程 reward 打赏讲师  pay_channel支付频道 pay_onlinebook支付在线听书 pay_reciter 最美保险声音评选  余额充值recharge
        $phone_id =input('post.phone_id'); //IOS手机ID

        wlog($this->log_path,"接收参数:课程id：$lecture_id, 专栏id：$channel_id,fee:$fee,expire:$channel_expire,用户id:$target, 内容：$product");
        $result = $this->validate(
            [
                'lecture_id' => $lecture_id,
                'channel_id' => $channel_id,
                'book_id' => $book_id,
                'expire' => $channel_expire,
                'fee' => $fee,
                'target' => $target,
                'product' => $product,
                'phone_id' => $phone_id,
            ],
            [
                'lecture_id'  => 'number' ,
                'channel_id'  => 'number' ,
                'book_id'  => 'number' ,
                'expire'  => 'number' ,
                'fee'  => 'require|number' ,
                'target'  => 'number' ,
                'product'  => 'require|in:pay_lecture,reward,pay_channel,pay_onlinebook,pay_reciter,recharge',
                'phone_id'  => 'alphaDash',
            ]);
        if($result !== true){
            wlog($this->log_path,"参数错误");
            $this->return_json(E_ARGS,'参数错误');
        }
        if($product=='reward' && empty($lecture_id)){
            wlog($this->log_path,"缺少课程ID 1");
            $this->return_json(E_ARGS,'缺少课程ID 1');
        }
        if($product=='pay_lecture' && empty($lecture_id)){
            wlog($this->log_path,"缺少课程ID 2");
            $this->return_json(E_ARGS,'缺少课程ID 2');
        }
        if($product=='pay_channel' && empty($channel_id)){
            wlog($this->log_path,"缺少专栏ID");
            $this->return_json(E_ARGS,'缺少专栏ID');
        }
        if($product=='pay_onlinebook' && empty($book_id)){
            wlog($this->log_path,"缺少书籍ID");
            $this->return_json(E_ARGS,'缺少书籍ID');
        }
        if(empty($channel_expire)){//购买后有效期月数，默认12个月
            $channel_expire = 12;
        }
        if(empty($this->user['id'])){
            $this->user = db('member')->where(array('openid'=>$phone_id))->find();
            if(empty($this->user)){
                wlog($this->log_path,"找不到该用户 1");
                $this->return_json(E_OP_FAIL,'找不到该用户');
            }
        }
        if(empty($target) && !empty($phone_id)){
            $target = $this->user['id'];
        }elseif (empty($target) && empty($this->user['id'])){
            wlog($this->log_path,"找不到该用户 2");
            $this->return_json(E_OP_FAIL,'找不到该用户');
        }
        if (!empty($channel_id)){
            $channel = db('channel')->find($channel_id);
            if(empty($channel)){
                $this->return_json(E_OP_FAIL,'没有此专栏');
            }
            $is = db('channelpay')->field('expire,status')->where(['memberid'=>$this->user['id'],'channelid'=>$channel_id])->find();
            if(!empty($is)  && $product=='pay_channel'){
                if($is['status']=='finish' && time()<(int)strtotime($is['expire'])){
                    wlog($this->log_path,"专栏已购买，无需重复购买");
                    $this->return_json(E_OP_FAIL,'专栏已购买，无需重复购买');
                }

            }
        }
        if (!empty($lecture_id)){
            $lecture = db('course')->find($lecture_id);
            if(empty($lecture)){
                $this->return_json(E_OP_FAIL,'没有此课程');
            }
            $is = db('coursepay')->field('id,status')->where(['memberid'=>$this->user['id'],'courseid'=>$lecture_id])->find();
            if(!empty($is) && $product=='pay_lecture'){
                if($is['status']=='finish'){
                    wlog($this->log_path,"课程已购买，无需重复购买");
                    $this->return_json(E_OP_FAIL,'课程已购买，无需重复购买');
                }
            }
        }
        if (!empty($book_id)){
            $book = db('onlinebooks')->field('id,name')->find($book_id);
            if(empty($book)){
                $this->return_json(E_OP_FAIL,'没有此书籍');
            }
            $is = db('onlinebookpay')->field('id,status')->where(['memberid'=>$this->user['id'],'bookid'=>$book_id])->find();
            if(!empty($is) && $product=='pay_onlinebook'){
                if($is['status']=='finish'){
                    wlog($this->log_path,"课程已购买，无需重复购买");
                    $this->return_json(E_OP_FAIL,'课程已购买，无需重复购买');
                }
            }
        }

        $reciterid = input('post.reciterid');
        if (!empty($reciterid)){
            $reciter = db('reciter')->find($reciterid);
        }

        $member = $this->user;
        if($member){
            $openId = $member['openid'];
        }
        if ($product!='pay_onlinebook'&&$product!='pay_reciter'&&$product!='pay_wuhan'&&$product!='pay_register'&&$product!='pay_zlhd'){
            $targetmember = db('member')->find($target);
            $out_trade_no = $product.date("YmdHis").rand(100000,999999);
        }else{ //当支付类型为pay_onlinebook时，支付的目标用户为系统，则$targetmember['id'] = 0
            $targetmember['id'] = 0;
            $out_trade_no = $product.date("YmdHis").rand(1000,9999);
        }
        $pay_amount = $fee/100.00;
        $add_time = date("Y-m-d H:i:s").".".rand(000000,999999);

        if($product != 'recharge'){
            //$paymember = db('member')->field('id,sumearn,money')->find($this->user['id']);
            $money = $this->user['money'] - $pay_amount;
            if($money<0){
                $this->return_json(E_OP_FAIL,'余额不足');
            }
        }

        //初始化日志
       /* $logHandler= new \CLogFileHandler("./logs/".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);*/


        //①、获取用户openid
        /*$tools = new \JsApiPay();

        if (empty($openId) || is_numeric($openId)){
            $openId = $tools->GetOpenid();
        }*/
        //LogController::W_P_Log("下单用户openid 为：".$openId);
        //②、统一下单
        //$input = new \WxPayUnifiedOrder();
        try{
            $membername = $member['name']?$member['name']:$member['nickname'];
            $membername =  $member['paynickname']?$member['paynickname']:$membername;
        }catch (Exception $e){
            $membername = $member['nickname'];
        }


        /*switch ($product){
            case 'reward' :
                try{
                    $tmembername = $targetmember['name']?$targetmember['name']:$targetmember['nickname'];
                }catch (Exception $e){
                    $tmembername = $targetmember['nickname'];
                }
                break;
            default:break;
        }*/
        switch ($product){
            case 'reward' :
                try{
                    $tmembername = empty($targetmember['name'])?$targetmember['name']:$targetmember['nickname'];
                }catch (Exception $e){
                    $tmembername = $targetmember['nickname'];
                }
                //$input->SetBody(($membername)."打赏了 ".$tmembername." ".$pay_amount."元红包");
                //$input->SetAttach(($membername)."打赏了 ".$tmembername." ".$pay_amount."元红包");
                $orderData['body'] = ($membername)."打赏了 ".$tmembername." ".$pay_amount."元红包";
                $orderData['attach'] = ($membername)."打赏了 ".$tmembername." ".$pay_amount."元红包";
                break;
            case 'recharge':
                $orderData['body'] = ($membername)."充值了".$pay_amount."个天雁币";
                $orderData['attach'] = ($membername)."充值了".$pay_amount."个天雁币";
                break;
            case 'pay_lecture':
                $orderData['body'] =  ($membername."支付了《".$lecture['name']."》".$pay_amount."元");
                $orderData['attach'] = ($membername."支付了《".$lecture['name']."》".$pay_amount."元");
                break;
            case 'pay_channel':
                $orderData['body'] = ($membername."支付了频道《".$channel['name']."》".$pay_amount."元");
                $orderData['attach'] = ($membername."支付了频道《".$channel['name']."》".$pay_amount."元");
                break;
            case 'pay_onlinebook':
                $orderData['body'] = ($membername."支付了在线听书《".$book['name']."》".$pay_amount."元");
                $orderData['attach'] = ($membername."支付了在线听书《".$book['name']."》".$pay_amount."元");
                break;
            case 'pay_register':
                $orderData['body'] = ($membername."支付了天雁论坛会员购买".$pay_amount."元");
                $orderData['attach'] = ($membername."支付了天雁论坛会员购买".$pay_amount."元");
                break;
            case 'pay_reciter':
                $orderData['body'] = ($membername."支付了保险公益杯《".$reciter['id']."》".$pay_amount."元");
                $orderData['attach'] =($membername."支付了保险公益杯《".$reciter['id']."》".$pay_amount."元");
                break;
            case 'pay_zlhd':
                $orderData['body'] = ($membername."支付了专栏购买赠送活动".$pay_amount."元");
                $orderData['attach'] =($membername."支付了专栏购买赠送活动".$pay_amount."元");
                break;
            case 'pay_wuhan': //武汉论道
                $num1 = input('post.num1');
                $num2 = input('post.num2');

                $num11 = input('post.num11');
                $num22 = input('post.num22');

                $str14 = '；14日：';
                if ($num11>0){
                    $str14 .= "25元".$num11."餐；";
                }
                if ($num22>0){
                    $str14 .= "60元".$num22."餐";
                }
                $str15 = '；15日：';
                if ($num1>0){
                    $str15 .= "25元".$num1."餐；";
                }
                if ($num2>0){
                    $str15 .= "60元".$num2."餐";
                }

                $orderData['body'] = ($membername."支付了<武汉论道>".$pay_amount."元".$str14.$str15);
                $orderData['attach'] = ($membername."支付了<武汉论道>".$pay_amount."元".$str14.$str15);
                break;
            default:
                wlog($this->log_path,$product.":支付类型未定义,支付用户为：".$member['id'].":".$membername);
                //LogController::W_H_Log($product.":支付类型未定义,支付用户为：".$member['id'].":".$membername);
        }

        //LogController::W_P_Log("body参数为：".$input->GetBody());
        /*wlog($this->log_path,"body参数为：".$input->GetBody());
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($fee);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($product);
        $input->SetNotify_url(Config::get('WxPayConf_pub.NOTIFY_URL'));
        wlog($this->log_path,"body参数为：".$input->GetBody());*/
        //LogController::W_P_Log("支付类型为：".$_SESSION['thirdparty']);
        /*if (!empty($_SESSION['thirdparty'])){
            $input->SetTrade_type("MWEB");
            $res['thirdparty']= 1;
        }else{
            $input->SetTrade_type("JSAPI");
        }
        $input->SetOpenid($openId);*/
        /*if(input('post.Trade_type') =='NATIVE'){
            wlog($this->log_path,"调用扫码支付");
            //LogController::W_P_Log("调用扫码支付！");
            $input->SetTrade_type("NATIVE");
            $input->SetProduct_id($input->GetOut_trade_no());
            $notify = new \NativePay();
            $result = $notify->GetPayUrl($input);
            $url = $result["code_url"];
            wlog($this->log_path,"调用扫码支付!URL为：".$url);
            //LogController::W_P_Log("调用扫码支付！URL为：".$url);
            $res['url'] = $url;
        }else{
            $order = \WxPayApi::unifiedOrder($input);
            $res['mweb_url'] = $order['mweb_url'];
            wlog($this->log_path,"中间页为：".$res['mweb_url']);
            wlog($this->log_path,"统一下单支付单信息");
//            LogController::W_P_Log("订单号：".$input->getOut_trade_no());
            foreach($order as $key=>$value){
                //LogController::W_P_Log("$key:::$value");
                wlog($this->log_path,"$key:::$value");
            }
            $jsApiParameters = $tools->GetJsApiParameters($order);
            $jsApiParameters = json_decode($jsApiParameters);
            $res['params'] = $jsApiParameters;
        }*/
        //$orderData = $input->GetValues();
        $orderData['total_fee'] = $fee;
        $orderData['time_start'] = date('YmdHis',time());
        $orderData['trade_type'] = 'APPPAY';
        $orderData['goods_tag'] = $product;
        $orderData['openid'] = $this->user['openid'];

        $orderData['out_trade_no'] = $out_trade_no;
        $orderData['paymember'] = $member['id'];
        $orderData['getmember'] = $targetmember['id'];
        $orderData['status'] = "wait";
        db('orders')->insert($orderData); //保存订单数据
        wlog($this->log_path,"课程已购买，无需重复购买");
        switch ($product){
            case 'reward' :
                $data['sender_id'] = $member['id'];
                $data['lecture_id'] = $lecture_id;
                //$data['pay_amount'] = $pay_amount;
                $data['sender_headimg'] = $member['headimg'];
                $data['message_type'] = $product;
                $data['sender_nickname'] = $member['nickname'];
                $data['add_time'] = $add_time;
                $data['content'] = ($membername)."打赏了 " . ($tmembername). " ".$pay_amount . "元红包";
                $data['length'] = 0;
                //$data['ppt_id'] = null;
                $data['ppt_url'] = null;
                $data['reply'] = null;
                $data['isshow'] = 'hiden';
                //$data['out_trade_no'] = $input->GetOut_trade_no();
                $data['out_trade_no'] = $out_trade_no;
                $count = db('msg')->insertGetId($data); //保存消息数据
                $data['message_id'] = $count;
                $res['data'] = $data;
                wlog($this->log_path,"打赏msg表新增数据".$count);
                break;
            case 'recharge':
                //充值记录表添加记录
               /* $earnsDatas['memberid'] = $member['id'];
                $earnsDatas['paymemberid'] = $member['id'];
                //$earnsDatas['lectureid'] = $lecture_id;
                $earnsDatas['fee'] = $pay_amount;
                $earnsDatas['type'] = "recharge";
                $earnsDatas['out_trade_no'] = $orderData['out_trade_no'];
                $earnsDatas['status'] = 'wait';
                $earnsDatas['remarks'] = '用户充值';
                $earnsDatas['addtime'] = date("Y-m-d H:i:s");
                $e = db('recharge')->insertGetId($earnsDatas);*/
                break;
            case 'pay_lecture':
                //$fee =$pay_amount;
                $paydata = array(
                    'memberid'=>$member['id'],
                    'courseid'=>$lecture_id,
                    'fee'=>$pay_amount,
                    'status'=>'wait',
                    'addtime'=>date("Y-m-d H:i:s"),
                    'out_trade_no'=>$orderData['out_trade_no']
                );
                $bb = db('coursepay')->insert($paydata);
                wlog($this->log_path,"课程支付coursepay表新增数据".$bb);
                //处理是否加入分销推广收益
                $popular = db("popularize")->where("lecture_id=".$lecture_id." and bpid=".$member['id'])->order("id desc")->find();
                if (isset($popular)&&(!empty($popular))){
                    //收益表添加记录
                    $earnsDatas['memberid'] = $popular['pid'];
                    $earnsDatas['paymemberid'] = $member['id'];
                    $earnsDatas['lectureid'] = $lecture_id;
                    $earnsDatas['fee'] = $lecture['cost']*$lecture['resell_percent']/100;
                    $earnsDatas['type'] = "pay";
                    $earnsDatas['out_trade_no'] = $orderData['out_trade_no'];
                    $earnsDatas['status'] = 'wait';
                    $earnsDatas['remarks'] = '分销推广';
                    $earnsDatas['addtime'] = date("Y-m-d H:i:s");
                    $e = db('earns')->insertGetId($earnsDatas);//收益表添加记录
                    wlog($this->log_path,"课程支付-收益表添加记录".$e);
                    //LogController::W_P_Log("加入分销推广记录：".$e);
                    $fee = $lecture['cost']*(100-$lecture['resell_percent'])/100;
                    $pay_amount = $fee;
                }else{
                    wlog($this->log_path,"未取到推广记录信息");
                }
                break;
            case 'pay_channel':
                if(strchr($channel_expire,"w")){
                    $expire = str_replace("w","",$channel_expire)*7;
                }elseif (strchr($channel_expire,"y")){
                    $expire = str_replace("y","",$channel_expire)*365;
                }else{
                    $expire = $channel_expire*30;
                }
                $channeldata = array(
                    'memberid'=>$member['id'],
                    'channelid'=>$channel_id,
                    'fee'=>$fee,
                    'status'=>'wait',
                    'addtime'=>date("Y-m-d H:i:s"),
                    'expire' => $expire ? date("Y-m-d H:i:s",strtotime("+$expire day")) : null,
                    'out_trade_no'=>$orderData['out_trade_no']
                );
                $a = db('channelpay')->insertGetId($channeldata);
                wlog($this->log_path,"专栏支付-收益表添加记录 ".$a);
                //处理活动逻辑
                if ($channel_id == 454){
                    $onlinebooks = array(4,12,2,29,66,15,1,3,8,9);
                    foreach ($onlinebooks as $k=>$v){
                        $bookdata = array(
                            'memberid'=>$member['id'],
                            'bookid'=>$v,
                            'fee'=>0,
                            'status'=>'wait',
                            'addtime'=>date("Y-m-d H:i:s"),
                            'out_trade_no'=>$orderData['out_trade_no']
                        );
                        $ooo = db('onlinebookpay')->insert($bookdata);
                        wlog($this->log_path,"专栏支付-onlinebookpay表添加记录 ".$ooo);
                    }
                }

                break;
            case 'pay_onlinebook':
//                LogController::W_P_Log("before 写入onlinebookpay");
                $bookdata = array(
                    'memberid'=>$member['id'],
                    'bookid'=>$book_id,
                    'fee'=>$fee,
                    'status'=>'wait',
                    'addtime'=>date("Y-m-d H:i:s"),
                    'out_trade_no'=>$orderData['out_trade_no']
                );
                $aaa = db('onlinebookpay')->insert($bookdata);
                wlog($this->log_path,"pay_onlinebook-onlinebookpay添加记录 ".$aaa);
                break;
            case 'pay_reciter':
                $reciterdata = array(
                    'memberid'=>$member['id'],
                    'reciterid'=>$reciterid,
                    'fee'=>$fee,
                    'status'=>'wait',
                    'addtime'=>date("Y-m-d H:i:s"),
                    'out_trade_no'=>$orderData['out_trade_no']
                );
                $ccc = db('reciterpay')->insert($reciterdata);
                wlog($this->log_path,"pay_reciter-reciterpay添加记录 ".$ccc);
                break;
            case  'pay_zlhd':
                $channel_arr = [119,155,175,154,19,174,56,80,103,85,131,135,32];
                foreach ($channel_arr as $k => $v){
                    $channeldata = array(
                        'memberid'=>$member['id'],
                        'channelid'=>$v,
                        'fee'=>0,
                        'status'=>'wait',
                        'addtime'=>date("Y-m-d H:i:s"),
                        'expire' => date("Y-m-d H:i:s",strtotime("+365 day")),
                        'out_trade_no'=>$orderData['out_trade_no']
                    );
                    $ppp = db('channelpay')->insert($channeldata);
                    wlog($this->log_path,"pay_zlhd-channelpay添加记录 ".$ppp);
                }
                break;
            default:
                //LogController::W_H_Log($product.":支付类型未定义,支付用户为：".$member['id'].":".$membername);
                wlog($this->log_path,$product.":支付类型未定义,支付用户为：".$member['id'].":".$membername);
        }

        //收益表添加记录
        $earnsData['memberid'] = $orderData['getmember'];
        $earnsData['paymemberid'] = $orderData['paymember'];
        $earnsData['lectureid'] = $lecture_id;
        $earnsData['channelid'] = $channel_id;
        $earnsData['bookid'] = $book_id;
        $earnsData['fee'] = $pay_amount;
        switch ($product){
            case 'reward' :
                $earnsData['type'] = 'play';
                break;
            case 'pay_lecture':
                $earnsData['type'] = 'pay';
                break;
            case 'pay_channel':
                $earnsData['type'] = 'pay_channel';
                break;
            case 'recharge':
                $earnsData['type'] = 'recharge';
                break;
            case 'pay_onlinebook':
                $earnsData['type'] = 'pay_onlinebook';
                break;
            case 'pay_reciter':
                $earnsData['type'] = 'pay_reciter';
                break;
            case 'pay_wuhan':
                $earnsData['type'] = 'pay_wuhan';
                break;
            case 'pay_newregister':
                $earnsData['type'] = 'pay_newregister';
                break;
            case 'pay_zlhd':
                $earnsData['type'] = 'pay_zlhd';
                break;
        }
        $earnsData['out_trade_no'] = $orderData['out_trade_no'];
        $earnsData['status'] = 'wait';
        $earnsData['addtime'] = date("Y-m-d H:i:s");
        $a = db('earns')->insertGetId($earnsData);//收益表添加记录
        wlog($this->log_path,"last - 收益表添加记录".$a);
        //$res['code'] = 0;
        //$this->ajaxReturn($res,'JSON');
        //$a = $this->NotifyProcess($out_trade_no,$fee);
        if($product!='recharge'){
            $a = $this->NotifyProcess($out_trade_no,$fee);
        }else{
            if($a){
                $this->return_json(OK,['msg'=>'支付完成','out_trade_no'=>$out_trade_no,'fee'=>$fee]);
            }else{
                wlog($this->log_path,"支付失败".$member['id'].":".$membername);
                $this->return_json(E_OP_FAIL,'支付失败');
            }
        }
    }

    private function set_yue($memberid,$total_fee)
    {
        $getmember = db("member")->field('id,sumearn')->find($memberid);
        $sumearn = $getmember['sumearn'] + $total_fee;
        db('member')->where("id=".$getmember['id'])->setField("sumearn",$sumearn);
    }

    /**
     * 回调函数
     * @param string $out_trade_no
     * @param string $total_fee
     * @param string $return_code
     * @return bool
     */
    public function NotifyProcess($out_trade_no = '',$total_fee = '',$return_code = 'SUCCESS')
    {
        if(empty($out_trade_no) || empty($total_fee)){
            $out_trade_no = input('post.out_trade_no');
            $total_fee = input('post.fee');
            //$return_code = input('post.return_code');
        }

        $result = $this->validate(
            [
                'total_fee' => $total_fee,
                'out_trade_no' => $out_trade_no,
            ],
            [
                'total_fee'  => 'require|number' ,
                'out_trade_no'  => 'require|alphaDash' ,
            ]);
        if($result !== true){
            wlog($this->log_path,"参数验证失败,订单号：$out_trade_no,费用：$total_fee,状态:ERROR");
            $this->return_json(E_ARGS,'参数错误');
        }
        //$data, &$msg

        //处理业务逻辑
        //\Common\Controller\LogController::W_P_Log("NotifyProcess call back:" . json_encode($data));
        //wlog($this->log_path,"NotifyProcess call back:". json_encode($data));
        //$return_code = 'SUCCESS';
        //$out_trade_no = $data['out_trade_no'];
        //$attach = $data['attach'];
        //\Common\Controller\LogController::W_P_Log("++++++++++++++++return_code:".$return_code);
        wlog($this->log_path,"++++++++++++++++return_code:". $return_code);
        if ($return_code == 'SUCCESS'){

            //当该订单状态已经更新后再次调用时则直接返回
            $cpay = db('orders')->where("out_trade_no='".$out_trade_no."'")->select();
            if ($cpay){
                if ($cpay[0]['status'] == 'finish'){
                    $this->return_json(OK,['msg'=>'success']);
                }
            }
            $data['total_fee'] = $total_fee;
            $data['status'] = "finish";

            //var_dump($total_fee,$data['total_fee']);exit;
            //更新订单状态
            $lll = db('orders')->where("out_trade_no='".$out_trade_no."'")->update($data);
            wlog($this->log_path,"更新订单状态orders". (int)$lll);
            $data['total_fee'] = ($total_fee/100.00);

            //更新收益表
            $ppp = db('earns')->where("out_trade_no='".$out_trade_no."'")->setField("status",'finish');
            wlog($this->log_path,"更新收益表earns". (int)$ppp);
            $earns = db('earns')->where("out_trade_no='".$out_trade_no."'")->find();
            //\Common\Controller\LogController::W_P_Log("earns id is:".$earns['id']);
            wlog($this->log_path,"earns id is:". $earns['id']);

            $type = $earns['type'];

            //更新用户收益
            $order = db("orders")->where("out_trade_no='".$out_trade_no."'")->find();
            if ($order['getmember']!=0){
                $getmember = db('member')->field('id,sumearn,money')->find($order['getmember']);//充值时getmember==paymember
                if($type == 'recharge'){//充值更新充值用户余额
                    $mdata['money'] = $getmember['money'] + ($data['total_fee']);
                    $bbbb = db('member')->where(['id'=>$getmember['id']])->setField('money',$mdata['money']);
                    wlog($this->log_path,"更新用户余额 money：". (int)$bbbb);
                }else{//其他更新被付款用户总收益
                    $mdata['sumearn'] = $getmember['sumearn'] + ($data['total_fee']);
                    $aaaa = db('member')->where(['id'=>$getmember['id']])->setField('sumearn',$mdata['sumearn']);
                    wlog($this->log_path,"更新用户受益 sumearn：". (int)$aaaa);
                }
            }

            if($type != 'recharge'){
                $paymember = db('member')->field('id,sumearn,money')->find($order['paymember']);
                //$sumearn = $paymember['sumearn'] - ($data['total_fee']);
                $money = $paymember['money'] - ($data['total_fee']);
                /*if($sumearn<0 || $money<0){
                    $this->return_json(E_OP_FAIL,'余额不足');
                }*/
                $ccc = db('member')->where("id=".$paymember['id'])->update(['money'=>$money]);
                wlog($this->log_path,"减少购买商品的用户余额 money：". (int)$ccc);
            }
            //更新课程表
            if ($earns['lectureid']){
                $lecture = db('course')->find($earns['lectureid']);
                $sum = $lecture['sumearns'] + ($data['total_fee']);
                $lecdata['sumearns'] = $sum;
                $ddd = db('course')->where("id=".$earns['lectureid'])->update($lecdata);
                wlog($this->log_path,"更新课程表".$earns['lectureid'].' | '.(int)$ddd);
                //更新消息表
                $msg = db('msg')->where("out_trade_no='".$out_trade_no."' and lecture_id=".$earns['lectureid'])->order("message_id desc")->find();
                if ($msg){
                    db('msg')->where("message_id=".$msg['message_id'])->setField("isshow","show");
                    //wlog($this->log_path,"更新消息表".$earns['lectureid'].' | '.(int)$ddd);
                    $msg['isshow'] = 'show';
                    Tools::publish_msg(0,$earns['lectureid'],WORKERMAN_PUBLISH_URL,$this->tranfer($msg));
                }
                //\Common\Controller\LogController::W_P_Log("earns type is：".$type);
                wlog($this->log_path,"earns type is：". $type);
                if($type == 'play'){
                    $num = $lecture['playearns']+$data['total_fee'];
                    $lecdata['playearns'] = $num;
                    db('course')->where("id=".$earns['lectureid'])->update($lecdata);
                }else if ($type=='pay'){

                    //更新课程支付表
                    $paycount = db('coursepay')->where("out_trade_no='".$out_trade_no."'")->setField("status",'finish');
                    //\Common\Controller\LogController::W_P_Log("更新课程支付表：".$paycount);
                    wlog($this->log_path,"更新课程支付表：".$paycount);
                    $num = $lecture['payearns']+$data['total_fee'];
                    $lecdata['payearns'] = $num;
                    db('course')->where("id=".$earns['lectureid'])->update($lecdata);
                    $lecturer = db("earns")->where("out_trade_no='".$out_trade_no."' and remarks is null")->find();
                    if($lecturer){
                        //推送获得收益模板消息给讲师
                        $member['openid'] = db('member')->where('id='.$lecturer['memberid'])->value('openid');
                        $member['payer_nick'] = db('member')->where('id='.$lecturer['paymemberid'])->value('nickname');
                        $url = "http://tianyan199.com/index.php/Home/Lecture/index?id={$lecturer['lectureid']}";
                        $wechat = new WeChat();
                        $template = array(
                            'first' => array('value' => urlencode($member['payer_nick'].' 购买了课程 '.$lecture['name'].' ，您获得'.$lecturer['fee'].'元收益'), 'color' => '#173177'),
                            'keyword1' => array('value' => urlencode('付费课程'), 'color' => '#173177'),
                            'keyword2' => array('value' => urlencode($lecturer['addtime']), 'color' => '#173177'),
                            'remark' => array('value' => urlencode('您的努力初见成效，再接再厉哟。'), 'color' => '#000000'),
                        );
                        $wechat->doSendTempleteMsg($member['openid'],  Config::get('template_code.earns_notice'), $url, $template, $topcolor = '#7B68EE');

                        //推送付款成功消息给付款用户
                        $member['paymember'] = db('member')->where('id='.$lecturer['paymemberid'])->value('openid');
                        $template_paymember = array(
                            'first' => array('value' => urlencode('恭喜您购买课程 '.$lecture['name'].' 成功'), 'color' => '#173177'),
                            'keyword1' => array('value' => urlencode($out_trade_no), 'color' => '#173177'),
                            'keyword2' => array('value' => urlencode($lecture['name']), 'color' => '#173177'),
                            'keyword3' => array('value' => urlencode($lecturer['fee'].'元'), 'color' => '#173177'),
                            'keyword4' => array('value' => urlencode('13925227647'), 'color' => '#173177'),
                            'keyword5' => array('value' => urlencode($lecturer['addtime']), 'color' => '#173177'),
                            'remark' => array('value' => urlencode('谢谢您的光临'), 'color' => '#000000'),
                        );
                        $wechat->doSendTempleteMsg($member['paymember'],  Config::get('template_code.lecturepay_notice'), $url, $template_paymember, $topcolor = '#7B68EE');
                    }
                    $e = db("earns")->where("out_trade_no='".$out_trade_no."' and remarks='分销推广'")->find();
                    if ($e){
                        if($lecture['reseller_enabled']){
                            $pmember = db("member")->find($e['memberid']);
                            $sumearns  = $pmember['sumearn'] + $e['fee'];
                            db('member')->where("id=".$e['memberid'])->setField("sumearn",$sumearns);
                            //\Common\Controller\LogController::W_P_Log("添加分销推广人".$pmember['nickname']."佣金：".$e['fee']);
                            wlog($this->log_path,"添加分销推广人".$pmember['nickname']."佣金：".$e['fee']);
                            //推送获得收益模板消息给分销推广人
                            $member['openid'] = db('member')->where('id='.$e['memberid'])->value('openid');
                            $member['paymember'] = db('member')->where('id='.$e['paymemberid'])->value('nickname');
                            $url = "http://tianyan199.com/index.php/Home/Lecture/index?id={$e['lectureid']}";
                            $wechat = new WeChat();
                            $template = array(
                                'first' => array('value' => urlencode($member['paymember'].' 购买了课程 '.$lecture['name'].' ，您获得'.$e['fee'].'元收益'), 'color' => '#173177'),
                                'keyword1' => array('value' => urlencode('课程分销'), 'color' => '#173177'),
                                'keyword2' => array('value' => urlencode($e['addtime']), 'color' => '#173177'),
                                'remark' => array('value' => urlencode('您的努力初见成效，再接再厉哟。'), 'color' => '#000000'),
                            );
                            $wechat->doSendTempleteMsg($member['openid'],  Config::get('template_code.earns_notice'), $url, $template, $topcolor = '#7B68EE');
                        }

                    }
                }
            }
            if ($type=='pay_channel'){
                $channel = db('channel')->find($earns['channelid']);
                if($channel){
                    //更新频道支付表
                    $paychannel = db('channelpay')->where("out_trade_no='".$out_trade_no."'")->setField("status",'finish');
                    wlog($this->log_path,"更新频道支付表：". (int)$paychannel);
                    //更新频道收益
                    $channel_earns = $channel['earns'] + $data['total_fee'];
                    $chadata['earns'] = $channel_earns;
                    //\Common\Controller\LogController::W_P_Log("支付频道收益：".$chadata['earns']);
                    wlog($this->log_path,"支付频道收益：".$chadata['earns']);
                    $update_channel = db('channel')->where("id=".$channel['id'])->update($chadata);
                    //\Common\Controller\LogController::W_P_Log("支付频道收益更新：".$update_channel);
                    wlog($this->log_path,"支付频道收益更新：".$update_channel);
                    $channel_info = db("earns")->where("out_trade_no='".$out_trade_no."' and remarks is null")->find();
                    //推送收益消息给讲师
                    $url = "http://tianyan199.com/index.php/Home/LiveRoom/channel_detail?channel_id=".$channel_info['channelid'];
                    $member['openid'] = db('member')->where('id='.$channel_info['memberid'])->value('openid');
                    $member['payer_nick'] = db('member')->where('id='.$channel_info['paymemberid'])->value('nickname');
                    $wechat = new WeChat();
                    $template = array(
                        'first' => array('value' => urlencode($member['payer_nick'].' 购买了频道《'.$channel['name'].'》，您获得'.$channel_info['fee'].'元收益'), 'color' => '#173177'),
                        'keyword1' => array('value' => urlencode('付费频道'), 'color' => '#173177'),
                        'keyword2' => array('value' => urlencode($channel_info['addtime']), 'color' => '#173177'),
                        'remark' => array('value' => urlencode('您的努力初见成效，再接再厉哟。'), 'color' => '#000000'),
                    );
                    $wechat->doSendTempleteMsg($member['openid'],  Config::get('template_code.earns_notice'), $url, $template, $topcolor = '#7B68EE');

                    if ($channel['lecturer']&&($channel['lecturer']!=$channel['memberid'])){
                        $lecturer_openid = db('member')->where('id='.$channel['lecturer'])->value('openid');
                        $wechat->doSendTempleteMsg($lecturer_openid,  Config::get('template_code.earns_notice'), $url, $template, $topcolor = '#7B68EE');
                    }

                    //推送付款成功消息给付款用户
                    $member['paymember'] = db('member')->where('id='.$channel_info['paymemberid'])->value('openid');
                    $template_paymember = array(
                        'first' => array('value' => urlencode('恭喜您购买频道《'.$channel['name'].'》成功'), 'color' => '#173177'),
                        'keyword1' => array('value' => urlencode($out_trade_no), 'color' => '#173177'),
                        'keyword2' => array('value' => urlencode($channel['name']), 'color' => '#173177'),
                        'keyword3' => array('value' => urlencode($channel_info['fee'].'元'), 'color' => '#173177'),
                        'keyword4' => array('value' => urlencode('13925227647'), 'color' => '#173177'),
                        'keyword5' => array('value' => urlencode($channel_info['addtime']), 'color' => '#173177'),
                        'remark' => array('value' => urlencode('谢谢您的光临'), 'color' => '#000000'),
                    );
                    $wechat->doSendTempleteMsg($member['paymember'],  Config::get('template_code.lecturepay_notice'), $url, $template_paymember, $topcolor = '#7B68EE');

                    //处理送书逻辑
                    if ($earns['channelid'] == 454){
                        db('onlinebookpay')->where("out_trade_no='".$out_trade_no."'")->update($data);
                    }

                }
            }
            if ($type == 'pay_zlhd'){ //更新所有关联频道状态
                $paychannel = db('channelpay')->where("out_trade_no='".$out_trade_no."'")->setField("status",'finish');
                wlog($this->log_path,"更新所有关联频道状态：". (int)$paychannel);
            }
            if ($type == 'pay_onlinebook'){ //更新
                $odata['status'] = "finish";
                //更新订单状态
                $pay_onlinebook = db('onlinebookpay')->where("out_trade_no='".$out_trade_no."'")->update($odata);
                wlog($this->log_path,"更新onlinebookpay表：". (int)$pay_onlinebook);
                $book_id = $earns['bookid'];
                $book = db('onlinebooks')->find($book_id);
                $book_sum = $book['sumearns'] + ($data['total_fee']*100);//这个sumearns单位是分
                $pay_onlinebook2 = db('onlinebooks')->where("id=".$book_id)->setField("sumearns",$book_sum);
                wlog($this->log_path,"更新onlinebooks表：$book_id | ". (int)$pay_onlinebook2);
            }
            if ($type == 'pay_reciter'){ //更新保险公益杯支付表
                $data['status'] = "finish";
                //更新订单状态
                $hhhhh = db('reciterpay')->where("out_trade_no='".$out_trade_no."'")->update($data);
                wlog($this->log_path,"更新保险公益杯支付表reciterpay表：". (int)$hhhhh);
            }
            wlog($this->log_path,"+++++充值成功+++++");
            $this->get_user_redis($this->user['id']);
            $this->return_json(OK,['msg'=>'success']);
            //return true;
        }else{
            wlog($this->log_path,"+++++充值失败+++++");
            $this->return_json(E_OP_FAIL,'fail');
           // return false;
        }
    }

    public static function transfers($id){
        import('wxpay.Data',EXTEND_PATH,EXT);
        import('wxpay.wxPayApi',EXTEND_PATH,EXT);
        if ($id){//提现记录id
            $take_out = db('takeout')->find($id);
            $member = db('member')->find($take_out['memberid']);
            $amount = $take_out['num'];
            if ($amount*100<100){
                $result['code'] = 1;
                $result['msg'] = "金额最低为1元";
                return $result;
            }else{
                $data['partner_trade_no'] = $take_out['code'];
                $data['openid'] = $member['openid'];
                $data['check_name'] = "FORCE_CHECK"; //NO_CHECK：不校验真实姓名     FORCE_CHECK：强校验真实姓名
                $data['re_user_name'] = $take_out["name"];
                $data['transfersdesc'] = "用户提现";
                $data['addtime'] = date("Y-m-d H:i:s");
                $tid = db('transfers')->add($data);
                $input = new \WxPayTransfers();
                $input->SetAmount($amount*100);
                $input->SetCheck_name( $data['check_name']);
                $input->SetDesc($data['transfersdesc']);
                $input->SetOpenid($data['openid']);
                $input->SetPartner_trade_no($data['partner_trade_no']);
                $input->SetRe_user_name($data['re_user_name']);
                $input->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip
                $transfers_result = \WxPayApi::transfers($input);
                LogController::W_H_Log("微信企业付款接口返回信息");
                foreach($transfers_result as $key=>$value){
                    LogController::W_H_Log("$key:::$value");
                }
                $tdata['return_code'] = $transfers_result['return_code'];
                $tdata['return_msg'] = $transfers_result['return_msg'];
                $tdata['result_code'] = $transfers_result['result_code'];
                $tdata['err_code'] = $transfers_result['err_code'];
                $tdata['err_code_des'] = $transfers_result['err_code_des'];
                $tdata['payment_no'] = $transfers_result['payment_no'];
                $tdata['payment_time'] = $transfers_result['payment_time'];
                $tdata['updatetime'] = date("Y-m-d H:i:s");
                $tdata['id'] = $tid;
                db("transfers")->update($tdata);
                if ($tdata['return_code']=='SUCCESS'&&$tdata['result_code']=='SUCCESS'){
                    $result['code'] = 0;
                    $result['msg'] = "提现处理成功";
                }else{
                    $result['code'] = 1;
                    $result['msg'] = "提现处理失败";
                    $result['return_msg'] = $tdata['return_msg'];
                    $result['result_code'] = $tdata['result_code'];
                    $result['err_code'] = $tdata['err_code'];
                    $result['err_code_des'] = $tdata['err_code_des'];
                }
            }
        }else{
            $result['code'] = 1;
            $result['msg'] = "提现处理失败,缺少提现记录id";
        }
        return $result;
    }

    public function notify()
    {
        $notify = new \PayNotifyCallBack();
        $notify->Handle(false);
        echo 'success';
    }





    public function  log_result($file,$word)
    {
        $fp = fopen($file,"a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"执行日期：".strftime("%Y-%m-%d-%H：%M：%S",time())."\n".$word."\n\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    public function sendgroupredpack(){
        $member = $this->user;
        $openid = $member['openid'];
        $tdata = date('Y-m-d',time());
        $todayisget = db('rpslog')->where("memberid=".$member['id']." and sendtime like '".$tdata."%'")->select();
        if ((empty($todayisget)||(!isset($todayisget)))) {

        }else{

        }
    }

}