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
    private $log_path = APP_PATH.'log/Wxpay.log';//日志路径
    /**
     * 初始化
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
    public function weChatPay($order_num,$price){

        $lecture_id = input('post.lecture_id');
        $channel_id =input('post.channel_id');
        $channel_expire = input('post.expire');
        $fee = input('post.fee');
        $target = input('post.target');
        $product = input('post.product'); // pay_lecture 支付课程 reward 打赏讲师  pay_channel支付频道 pay_onlinebook支付在线听书 pay_reciter 最美保险声音评选
        $result = $this->validate(
            [
                'lecture_id' => $lecture_id,
                'channel_id' => $channel_id,
                'expire' => $channel_expire,
                'fee' => $fee,
                'target' => $target,
                'product' => $product,
            ],
            [
                'lecture_id'  => 'number' ,
                'channel_id'  => 'number' ,
                'expire'  => 'number' ,
                'fee'  => 'require|number' ,
                'target'  => 'require|number' ,
                'product'  => 'require|in:pay_lecture,reward,pay_channel,pay_onlinebook,pay_reciter' ,
            ]);
        if($result !== true){
            $this->return_json(E_ARGS,'参数错误');
        }

        if (!empty($lecture_id)){
            $lecture = db('course')->find($lecture_id);
        }
        if (!empty($channel_id)){
            $channel = db('channel')->find($channel_id);
        }
       /* $bookid = input('post.bookid');
        if (!empty($bookid)){
            $book = db('onlinebooks')->find($bookid);
        }
        $reciterid = input('post.reciterid');
        if (!empty($reciterid)){
            $reciter = db('reciter')->find($reciterid);
        }*/

        $member = $this->user;
        if($member){
            $openId = $member['openid'];
        }
        if ($product!='pay_onlinebook'&&$product!='pay_reciter'&&$product!='pay_wuhan'&&$product!='pay_register'&&$product!='pay_zlhd'){
            $targetmember = db('member')->find($target);
        }else{ //当支付类型为pay_onlinebook时，支付的目标用户为系统，则$targetmember['id'] = 0
            $targetmember['id'] = 0;
        }
        $pay_amount = $fee/100.00;//费用
        $add_time = date("Y-m-d H:i:s").".".rand(000000,999999);//时间
        $out_trade_no = $product.date("YmdHis").rand(000000,999999);//订单号



        $json = array();
        //生成预支付交易单的必选参数:
        $newPara = array();
        //应用ID
        $newPara["appid"] = "商户appid";
        //商户号
        $newPara["mch_id"] = "商户id";
        //设备号
        $newPara["device_info"] = WECHATPAY_DEVICE_INFO;
        //随机字符串,这里推荐使用函数生成
        $newPara["nonce_str"] = $this->createNoncestr();
        //商品描述
        $newPara["body"] = "天雁APP支付";
        //商户订单号,这里是商户自己的内部的订单号
        $newPara["out_trade_no"] = $out_trade_no;
        //总金额
        //$newPara["total_fee"] = $price*100;
        $newPara["total_fee"] = $fee;
        //终端IP
        $newPara["spbill_create_ip"] = $_SERVER["REMOTE_ADDR"];
        //通知地址，注意，这里的url里面不要加参数
        $newPara["notify_url"] = "支付成功后的回调地址";
        //交易类型
        $newPara["trade_type"] = "APP";

        $key = "密钥：在商户后台个人安全中心设置";
        //第一次签名
        $newPara["sign"] = $this->appgetSign($newPara,$key);
        //把数组转化成xml格式
        $xmlData = $this->arrayToXml($newPara);
        $get_data = $this->sendPrePayCurl($xmlData);
        //返回的结果进行判断。
        if($get_data['return_code'] == "SUCCESS" && $get_data['result_code'] == "SUCCESS"){
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
                "prepayid"=>$get_data['prepay_id'],
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
            return json_encode($json);
        }
        else{
            $json['success'] = 0;
            $json['error'] = $get_data['return_msg'];
            return json_encode($json);
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
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
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
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }


}