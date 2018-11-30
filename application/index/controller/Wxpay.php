<?php
namespace app\index\controller;
use Think\Exception;
use app\tools\controller\Tools;
use Think\Log;
use think\Request;
use think\Db;
use think\Config;


class Wxpay extends Base
{
    private $log_path = APP_PATH.'log/Wxpay_android.log';//日志路径
    /**
     * 初始化  安卓微信支付接口
     */
    public function __construct()
    {
        parent::__construct();
    }

    //微信APP支付 一定要先仔细阅读微信的官方文档，统一下单接口
    // 调取微信APP支付必须先开通商户后台的微信APP支付
    // 注意：开通微信APP支付会发邮件到你们邮箱，下面的商户号和appid还有key密钥，都必须是开通后的。说这些主要是提醒，有些商户是多个商户后台的，所以就有多个商户id和商户appid和商户key，必须匹配，不然获取不到预支付id，也就是prepay_id的。
    // header("Content-type: text/xml");   // 支付出问题时，方便查看xml格式数据，放开可以查看传送的xml字符串    xml要用 echo输出 不要var_dump()
    //echo weChatPay('订单号','价格');  //直接输出json给前台APP

    //入口函数
    public function wechat_pay(){

        wlog($this->log_path,"jsApiCall 进入支付方法");
        $lecture_id = input('post.lecture_id');
        $channel_id =input('post.channel_id');
        $book_id =input('post.book_id');
        $channel_expire = input('post.expire');
        $fee = input('post.fee');
        $target = input('post.js_memberid');
        $product = input('post.product'); // pay_onlinebook,pay_lecture 支付课程 reward 打赏讲师  pay_channel支付频道 pay_onlinebook支付在线听书 pay_reciter 最美保险声音评选  余额充值recharge
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
            ],
            [
                'lecture_id'  => 'number' ,
                'channel_id'  => 'number' ,
                'book_id'  => 'number' ,
                'expire'  => 'number' ,
                'fee'  => 'require|number' ,
                'target'  => 'require|number' ,
                'product'  => 'require|in:pay_onlinebook,pay_lecture,reward,pay_channel,pay_onlinebook,pay_reciter,recharge' ,
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
        if(empty($channel_expire)){
            $channel_expire = 12;
        }
        $book = $channel = $lecture = [];
        if (!empty($channel_id)){
            $channel = db('channel')->find($channel_id);
            if(empty($channel)){
                wlog($this->log_path,"没有此专栏".$channel_id);
                $this->return_json(E_OP_FAIL,'没有此专栏');
            }
            $is = db('channelpay')->field('expire,status')->where(['memberid'=>$target,'channelid'=>$channel_id])->find();
            if(!empty($is)){
                if($is['status']=='finish' && time()<(int)strtotime($is['expire'])){
                    wlog($this->log_path,"专栏已购买，无需重复购买".$channel_id);
                    $this->return_json(E_OP_FAIL,'专栏已购买，无需重复购买');
                }
            }
        }
        if (!empty($lecture_id)){
            $lecture = db('course')->find($lecture_id);
            if(empty($lecture)){
                wlog($this->log_path,"没有此课程".$lecture_id);
                $this->return_json(E_OP_FAIL,'没有此课程');
            }
            $is = db('coursepay')->field('id,status')->where(['memberid'=>$target,'courseid'=>$lecture_id])->find();
            if(!empty($is)){
                if($is['status']=='finish'){
                    wlog($this->log_path,"课程已购买，无需重复购买".$lecture_id);
                    $this->return_json(E_OP_FAIL,'课程已购买，无需重复购买');
                }
            }
        }
        /*$bookid = input('post.bookid');
        if (!empty($bookid)){
            $book = db('onlinebooks')->find($bookid);
        }*/
        if (!empty($book_id)){
            $book = db('onlinebooks')->field('id,name')->find($book_id);
            if(empty($book)){
                wlog($this->log_path,"没有此书籍".$book_id);
                $this->return_json(E_OP_FAIL,'没有此书籍');
            }
            $is = db('onlinebookpay')->field('id,status')->where(['memberid'=>$this->user['id'],'bookid'=>$book_id])->find();
            if(!empty($is) && $product=='pay_onlinebook'){
                if($is['status']=='finish'){
                    wlog($this->log_path,"书籍已购买，无需重复购买");
                    $this->return_json(E_OP_FAIL,'书籍已购买，无需重复购买');
                }
            }
        }
        $reciterid = input('post.reciterid');
        if (!empty($reciterid)){
            $reciter = db('reciter')->find($reciterid);
        }

        //$member = $this->user;
        /*if($member){
            $openId = $member['openid'];
        }*/
        if ($product!='pay_onlinebook'&&$product!='pay_reciter'&&$product!='pay_wuhan'&&$product!='pay_register'&&$product!='pay_zlhd'){
            $targetmember = db('member')->find($target);
        }else{ //当支付类型为pay_onlinebook时，支付的目标用户为系统，则$targetmember['id'] = 0
            $targetmember['id'] = 0;
        }
        //$pay_amount = $fee/100.00;//费用
        //$add_time = date("Y-m-d H:i:s").".".rand(000000,999999);//时间
        $out_trade_no = $product.date("YmdHis").rand(100000,999999);//订单号


        $json = array();
        //生成预支付交易单的必选参数:
        $newPara = array();
        //应用ID
        $newPara["appid"] = WECHATPAY_APPID;
        //商户号
        $newPara["mch_id"] = WECHATPAY_MCHID;
        //设备号
        //$newPara["device_info"] = WECHATPAY_DEVICE_INFO;
        //随机字符串,这里推荐使用函数生成
        $newPara["nonce_str"] = $this->createNoncestr();
        //商品描述
        $newPara["body"] = "天雁APP支付";
        //商户订单号,这里是商户自己的内部的订单号
        $newPara["out_trade_no"] = $out_trade_no;
        //总金额
        //$newPara["total_fee"] = $price*100;
        $newPara["total_fee"] = (int)$fee;
        if($newPara["total_fee"]<=0){
            wlog($this->log_path,"金额需大于零：".$newPara["total_fee"]);
            $this->return_json(E_OP_FAIL,'金额需大于零');
        }
        //终端IP
        $newPara["spbill_create_ip"] = $_SERVER["REMOTE_ADDR"];
        //$newPara["spbill_create_ip"] = '183.238.1.246';
        //通知地址，注意，这里的url里面不要加参数
        $newPara["notify_url"] = SERVER_URL.'/api.php/index/Wxpaynotify/notify';//"支付成功后的回调地址";
        //交易类型
        $newPara["trade_type"] = "APP";

        $key = WECHATPAY_KEY;//"密钥：在商户后台个人安全中心设置";
        //第一次签名
        $newPara["sign"] = $this->appgetSign($newPara,$key);

        //把数组转化成xml格式
        $xmlData = $this->arrayToXml($newPara);

        $get_data = $this->sendPrePayCurl($xmlData);
        //var_dump($get_data);exit;
        /*$get_data['return_code'] = "SUCCESS";
        $get_data['result_code'] = "SUCCESS";*/
        if(empty($get_data['data'])){
            $json['success'] = 0;
            $json['error'] = '支付失败(第三方返回内容为空)';
            wlog($this->log_path,"支付失败(第三方返回内容为空)：");
            $this->return_json(OK,$json);
        }
        //返回的结果进行判断。
        if($get_data['data']['return_code'] == "SUCCESS" && $get_data['data']['result_code'] == "SUCCESS"){
            //根据微信支付返回的结果进行二次签名
            //二次签名所需的随机字符串
            $newPara["nonce_str"] = $this->createNoncestr();
            //二次签名所需的时间戳
            $newPara['timeStamp'] = time()."";
            //二次签名剩余参数的补充
            $secondSignArray = array(
                "appid"=>$newPara['appid'],
                "noncestr"=>$newPara['nonce_str'],
                "package"=>"Sign=WXPay",
                "prepayid"=>$get_data['data']['prepay_id'],
                "partnerid"=>$newPara['mch_id'],
                "timestamp"=>$newPara['timeStamp'],
            );
            $json['success'] = 1;
            $json['ordersn'] = $newPara["out_trade_no"]; //订单号
            $json['order_arr'] = $secondSignArray;  //返给前台APP的预支付订单信息
            $json['order_arr']['sign'] = $this->appgetSign($secondSignArray,$key);  //预支付订单签名
            $json['data'] = "预支付完成";
            //预支付完成,在下方进行自己内部的业务逻辑
            /*****************************/
            $a = $this->js_api_call($lecture_id,$channel_id,$book_id,$channel_expire,$fee,$target,$product,$channel,$lecture,$book,$out_trade_no);
            if(!$a){
                $json['success'] = 0;
                $json['error'] = '支付失败(数据录入FAIL)，请等候退款';
                wlog($this->log_path,"支付失败(数据录入FAIL)，请等候退款");
                $this->return_json(OK,$json);
            }
            $this->return_json(OK,$json);
            //return json_encode($json);
        } else{
            $json['success'] = 0;
            $json['error'] = $get_data['data']['return_msg'];
            wlog($this->log_path,"支付失败:".json_encode($json,JSON_UNESCAPED_UNICODE));
            $this->return_json(OK,$json);
            //return json_encode($json);
        }
    }

    public function js_api_call($lecture_id,$channel_id,$book_id,$channel_expire,$fee,$target,$product,$channel,$lecture,$book,$out_trade_no)
    {
        //$this->return_json(OK,['msg'=>'支付成功']);
        //LogController::W_P_Log("进入支付方法!");
        $member = $this->user;
        if($member){
            $openId = $member['openid'];
        }
        if ($product!='pay_onlinebook'&&$product!='pay_reciter'&&$product!='pay_wuhan'&&$product!='pay_register'&&$product!='pay_zlhd'){
            $targetmember = db('member')->find($target);
        }else{ //当支付类型为pay_onlinebook时，支付的目标用户为系统，则$targetmember['id'] = 0
            $targetmember['id'] = 0;
        }
        $pay_amount = $fee/100.00;
        $add_time = date("Y-m-d H:i:s").".".rand(000000,999999);

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

        //$out_trade_no = $product.date("YmdHis").rand(000000,999999);
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
                    $tmembername = $targetmember['name']?$targetmember['name']:$targetmember['nickname'];
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
                $out_trade_no =  $product.date("YmdHis").rand(0000,9999);
                break;
            case 'pay_register':
                $orderData['body'] = ($membername."支付了天雁论坛会员购买".$pay_amount."元");
                $orderData['attach'] = ($membername."支付了天雁论坛会员购买".$pay_amount."元");
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
        $aaaa = db('orders')->insert($orderData); //保存订单数据
        wlog($this->log_path,$product."保存订单数据orders：".(int)$aaaa);
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
                wlog($this->log_path,$product."保存消息数据msg：".(int)$count);
                $data['message_id'] = $count;
                $res['data'] = $data;
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
                $pppp = db('coursepay')->insert($paydata);
                wlog($this->log_path,$product."保存课程支付数据coursepay：".(int)$pppp);
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
                    wlog($this->log_path,$product."收益表加入分销推广记录earns：".(int)$e);
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
                $wwww = db('channelpay')->insertGetId($channeldata);
                wlog($this->log_path,$product."保存专栏支付数据channelpay：".(int)$wwww);
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
//                LogController::W_P_Log("数据为 写入:".json_encode($bookdata));
                        db('onlinebookpay')->insert($bookdata);
                    }
                }

                break;
            case 'pay_onlinebook':
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
                    $cba = db('channelpay')->insert($channeldata);
                    wlog($this->log_path,$product."保存专栏支付数据channelpay：".(int)$cba);
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
        wlog($this->log_path,$product."收益表添加记录earns：".(int)$a);
        //$res['code'] = 0;
        //$this->ajaxReturn($res,'JSON');
        //$a = $this->NotifyProcess($out_trade_no,$fee);
        if($a){
            //$this->return_json(OK,['msg'=>'支付完成','out_trade_no'=>$out_trade_no,'fee'=>$fee]);
            return true;
        }else{
            return false;
        }

    }

    /**
     * 回调函数
     * @param string $out_trade_no
     * @param string $total_fee
     * @param string $return_code
     * @return bool
     */
    public function notify($out_trade_no = '',$total_fee = '',$return_code = 'SUCCESS')
    {
        if(empty($out_trade_no) || empty($total_fee)){
            $out_trade_no = input('post.out_trade_no');
            $total_fee = input('post.fee');
            $return_code = input('post.return_code');
        }

        $result = $this->validate(
            [
                'fee' => $total_fee,
                'out_trade_no' => $out_trade_no,
            ],
            [
                'fee'  => 'require|number' ,
                'out_trade_no'  => 'require|alphaNum' ,
            ]);
        if($result !== true){
            wlog($this->log_path,"参数验证失败,订单号：$out_trade_no,费用：$total_fee,状态:$return_code");
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
                    return true;
                }
            }
            $data['total_fee'] = $total_fee;
            $data['status'] = "finish";

            //var_dump($total_fee,$data['total_fee']);exit;
            //更新订单状态
            db('orders')->where("out_trade_no='".$out_trade_no."'")->update($data);
            $data['total_fee'] = ($total_fee/100.00);
            //更新用户收益
            $order = db("orders")->where("out_trade_no='".$out_trade_no."'")->find();
            if ($order['getmember']!=0){
                $getmember = db("member")->find($order['getmember']);
                $mdata['sumearn'] = $getmember['sumearn'] + ($data['total_fee']);
                db('member')->where("id=".$getmember['id'])->setField("sumearn",$mdata['sumearn']);
            }

            //更新收益表
            db('earns')->where("out_trade_no='".$out_trade_no."'")->setField("status",'finish');
            $earns = db('earns')->where("out_trade_no='".$out_trade_no."'")->find();
            //\Common\Controller\LogController::W_P_Log("earns id is:".$earns['id']);
            wlog($this->log_path,"earns id is:". $earns['id']);
            //更新课程表
            $type = $earns['type'];
            if ($earns['lectureid']){
                $lecture = db('course')->find($earns['lectureid']);
                $sum = $lecture['sumearns'] + ($data['total_fee']);
                $lecdata['sumearns'] = $sum;
                db('course')->where("id=".$earns['lectureid'])->update($lecdata);

                //更新消息表
                $msg = db('msg')->where("out_trade_no='".$out_trade_no."' and lecture_id=".$earns['lectureid'])->order("message_id desc")->find();
                if ($msg){
                    db('msg')->where("message_id=".$msg['message_id'])->setField("isshow","show");
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
            }
            if ($type == 'pay_onlinebook'){ //更新
                $data['status'] = "finish";
                //更新订单状态
                db('onlinebookpay')->where("out_trade_no='".$out_trade_no."'")->update($data);
                $bookid = $earns['bookid'];
                $book = db('onlinebooks')->find($bookid);
                $book_sum = $book['sumearns'] + $data['total_fee'];
                db('onlinebooks')->where("id=".$bookid)->setField("sumearns",$book_sum);
            }
            if ($type == 'pay_reciter'){ //更新保险公益杯支付表
                $data['status'] = "finish";
                //更新订单状态
                db('reciterpay')->where("out_trade_no='".$out_trade_no."'")->update($data);
            }
            $this->result(OK,['msg'=>'success']);
            //return true;
        }else{
            $this->result(E_OP_FAIL,['msg'=>'error']);
            //return false;
        }
    }

//将数组转换为xml格式
    private function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";
            }
            else
            {
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }

        }
        $xml.="</xml>";
        return $xml;
    }


    //发送请求
    private function sendPrePayCurl($xml,$second=30)
    {
        //$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, WECHATPAY_URL);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            //设置header
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            //要求结果为字符串且输出到屏幕上
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //post提交方式
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            //运行curl
            $data = curl_exec($ch);
            curl_close($ch);
            $data_xml_arr = $this->XMLDataParse($data);//解析成数组
            if ($data_xml_arr) {
                $ret=['status'=>true,'data'=>$data_xml_arr];
            } else {
                $error = curl_errno($ch);
                wlog($this->log_path, "curl出错，错误码:$error");
                wlog($this->log_path, 'http://curl.haxx.se/libcurl/c/libcurl-errors.html 错误原因查询');
                curl_close($ch);
                $ret=['status'=>false,'msg'=>"curl出错，错误码:$error"];
            }
            return $ret;
        }catch (\Exception $exception){
            $ret=['status'=>false,'msg'=>$exception->getMessage()];
            return $ret;
        }
    }

    //xml格式数据解析函数
    private function XMLDataParse($data){
        $xml = simplexml_load_string($data,NULL,LIBXML_NOCDATA);
        $array=json_decode(json_encode($xml),true);
        return $array;
    }

    /**
     * 生成随机字符串
     * @param int $length
     * @return string
     */
    private function createNoncestr( $length = 32 )
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * 格式化参数格式化成url参数  生成签名sign
     */
    private function appgetSign($Obj,$appwxpay_key)
    {
        foreach ($Obj as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        //echo $Parameters['nonce_str'];
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';

        //签名步骤二：在string后加入KEY
        if($appwxpay_key){
            $String = $String."&key=".$appwxpay_key;
        }
        //echo "【string2】".$String."</br>";

        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";

        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    //按字典序排序参数
    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
            /*if($k == 'nonce_str'){
                $buff .= $k . "=" . $v . "&amp";
            }else{
                $buff .= $k . "=" . $v . "&";
            }*/
            //$buff .= strtolower($k) . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }


}