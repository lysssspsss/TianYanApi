<?php
namespace app\index\controller;

use app\tools\controller\Tools;
use think\Config;

/**
 * 安卓支付接口 回调函数
 * Class Wxpaynotify
 * @package app\index\controller
 */
class Wxpaynotify
{
    private $log_path = APP_PATH.'log/Wxpay_android.log';//日志路径
    public function notify()
    {
        $xml = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
        $datas = $this->xmlToArray($xml);
        //$datas = input('post.');
        if(empty($datas)){
            wlog($this->log_path,'微信支付返回结果为空');
            exit;
        }
        if($datas['return_code'] == 'SUCCESS' && $datas['result_code'] == 'SUCCESS'){
            wlog($this->log_path,'微信支付返回结果'.json_encode($datas,JSON_UNESCAPED_UNICODE));
            if($this->checkSign($datas)) {
                $transaction_id = $datas['transaction_id'];      //微信支付订单号
                $out_trade_no   = $datas['out_trade_no'];        //商家订单号
                $total_fee   = $datas['total_fee'];        //金额
                //$this->errorLog('微信支付返回结果,微信支付订单号：'.$transaction_id.'，商家订单号：'.$out_trade_no,[]);
                wlog($this->log_path,'微信支付返回结果签名认证成功,微信支付订单号：'.$transaction_id.'，商家订单号：'.$out_trade_no);

                //当该订单状态已经更新后再次调用时则直接返回
                $cpay = db('orders')->field('status')->where(['out_trade_no'=>$out_trade_no])->find();
                if (!empty($cpay)){
                    wlog($this->log_path,'回调查询是否有该订单号'.json_encode($cpay));
                    if ($cpay['status'] == 'finish'){
                        echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                    }
                }
                $data['total_fee'] = $total_fee;
                $data['status'] = "finish";

                //var_dump($total_fee,$data['total_fee']);exit;
                //更新订单状态
                $o = db('orders')->where(['out_trade_no'=>$out_trade_no])->update($data);
                wlog($this->log_path,'回调更新订单状态orders：'.$o);
                $data['total_fee'] = ($total_fee/100.00);
                //更新用户收益
                $order = db("orders")->where(['out_trade_no'=>$out_trade_no])->find();
                if ($order['getmember']!=0){
                    $getmember = db("member")->find($order['getmember']);
                    $mdata['sumearn'] = $getmember['sumearn'] + ($data['total_fee']);
                    $s = db('member')->where("id=".$getmember['id'])->setField("sumearn",$mdata['sumearn']);
                    wlog($this->log_path,'回调更新用户收益member：'.$s);
                }

                //更新收益表
                $e = db('earns')->where(['out_trade_no'=>$out_trade_no])->setField("status",'finish');
                wlog($this->log_path,'回调更新收益表earns：'.$e);
                $earns = db('earns')->where(['out_trade_no'=>$out_trade_no])->find();
                //\Common\Controller\LogController::W_P_Log("earns id is:".$earns['id']);
                wlog($this->log_path,"回调earns id is:". $earns['id']);
                //更新课程表
                $type = $earns['type'];
                if ($earns['lectureid']){
                    $lecture = db('course')->find($earns['lectureid']);
                    $sum = $lecture['sumearns'] + ($data['total_fee']);
                    $lecdata['sumearns'] = $sum;
                    $ll = db('course')->where(['id'=>$earns['lectureid']])->update($lecdata);
                    wlog($this->log_path,"回调更新课程表1 ". $ll);
                    //"out_trade_no='".$out_trade_no."' and lecture_id=".$earns['lectureid']
                    //更新消息表
                    $msg = db('msg')->where(['out_trade_no'=>$out_trade_no,'lecture_id'=>$earns['lectureid']])->order('message_id','desc')->find();
                    if ($msg){
                        $mm = db('msg')->where(['message_id'=>$msg['message_id']])->setField("isshow","show");
                        wlog($this->log_path,"回调更新消息表". $mm);
                        $msg['isshow'] = 'show';
                        //Tools::
                        Tools::publish_msg(0,$earns['lectureid'],WORKERMAN_PUBLISH_URL,$this->tranfer($msg));
                    }
                    //\Common\Controller\LogController::W_P_Log("earns type is：".$type);
                    wlog($this->log_path,"回调earns type is：". $type);
                    if($type == 'play'){
                        $num = $lecture['playearns']+$data['total_fee'];
                        $lecdata['playearns'] = $num;
                        $sss = db('course')->where(['id'=>$earns['lectureid']])->update($lecdata);
                        wlog($this->log_path,"回调更新课程表2 ". $sss);
                    }else if ($type=='pay'){

                        //更新课程支付表
                        $paycount = db('coursepay')->where(['out_trade_no'=>$out_trade_no])->setField("status",'finish');
                        //\Common\Controller\LogController::W_P_Log("更新课程支付表：".$paycount);
                        wlog($this->log_path,"回调更新课程支付表：".$paycount);
                        $num = $lecture['payearns']+$data['total_fee'];
                        $lecdata['payearns'] = $num;
                        $ppp = db('course')->where(['id'=>$earns['lectureid']])->update($lecdata);
                        wlog($this->log_path,"回调更新课程表3 ". $ppp);
                        $lecturer = db('earns')->where(['out_trade_no'=>$out_trade_no])->where('remarks is null')->find();
                        if(!empty($lecturer)){
                            //推送获得收益模板消息给讲师
                            $member['openid'] = db('member')->where(['id'=>$lecturer['memberid']])->value('openid');
                            $member['payer_nick'] = db('member')->where(['id'=>$lecturer['paymemberid']])->value('nickname');
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
                            $member['paymember'] = db('member')->where(['id'=>$lecturer['paymemberid']])->value('openid');
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
                        //['out_trade_no'=>$out_trade_no]
                        $e = db('earns')->where(['out_trade_no'=>$out_trade_no,'remarks'=>'分销推广'])->find();
                        if ($e){
                            if($lecture['reseller_enabled']){
                                $pmember = db("member")->find($e['memberid']);
                                $sumearns  = $pmember['sumearn'] + $e['fee'];
                                $aaab=db('member')->where(['id'=>$e['memberid']])->setField("sumearn",$sumearns);
                                //\Common\Controller\LogController::W_P_Log("添加分销推广人".$pmember['nickname']."佣金：".$e['fee']);
                                wlog($this->log_path,"添加分销推广人".$pmember['nickname']."佣金：".$e['fee'].'是否成功'.$aaab);
                                //推送获得收益模板消息给分销推广人
                                $member['openid'] = db('member')->where(['id'=>$e['memberid']])->value('openid');
                                $member['paymember'] = db('member')->where(['id'=>$e['memberid']])->value('nickname');
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
                        $paychannel = db('channelpay')->where(['out_trade_no'=>$out_trade_no])->setField("status",'finish');
                        wlog($this->log_path,"更新频道支付表channelpay：".$paychannel);
                        //更新频道收益
                        $channel_earns = $channel['earns'] + $data['total_fee'];
                        $chadata['earns'] = $channel_earns;
                        //\Common\Controller\LogController::W_P_Log("支付频道收益：".$chadata['earns']);
                        wlog($this->log_path,"支付频道收益：".$chadata['earns']);
                        $update_channel = db('channel')->where(['id'=>$channel['id']])->update($chadata);
                        //\Common\Controller\LogController::W_P_Log("支付频道收益更新：".$update_channel);
                        wlog($this->log_path,"支付频道收益更新：".$update_channel);
                        $channel_info = db("earns")->where(['out_trade_no'=>$out_trade_no])->where('remarks is null')->find();
                        //推送收益消息给讲师
                        $url = "http://tianyan199.com/index.php/Home/LiveRoom/channel_detail?channel_id=".$channel_info['channelid'];
                        $member['openid'] = db('member')->where(['id'=>$channel_info['memberid']])->value('openid');
                        $member['payer_nick'] = db('member')->where(['id'=>$channel_info['paymemberid']])->value('nickname');
                        $wechat = new WeChat();
                        $template = array(
                            'first' => array('value' => urlencode($member['payer_nick'].' 购买了频道《'.$channel['name'].'》，您获得'.$channel_info['fee'].'元收益'), 'color' => '#173177'),
                            'keyword1' => array('value' => urlencode('付费频道'), 'color' => '#173177'),
                            'keyword2' => array('value' => urlencode($channel_info['addtime']), 'color' => '#173177'),
                            'remark' => array('value' => urlencode('您的努力初见成效，再接再厉哟。'), 'color' => '#000000'),
                        );
                        $wechat->doSendTempleteMsg($member['openid'],  Config::get('template_code.earns_notice'), $url, $template, $topcolor = '#7B68EE');

                        if ($channel['lecturer']&&($channel['lecturer']!=$channel['memberid'])){
                            $lecturer_openid = db('member')->where(['id'=>$channel['lecturer']])->value('openid');
                            $wechat->doSendTempleteMsg($lecturer_openid,  Config::get('template_code.earns_notice'), $url, $template, $topcolor = '#7B68EE');
                        }

                        //推送付款成功消息给付款用户
                        $member['paymember'] = db('member')->where(['id'=>$channel_info['paymemberid']])->value('openid');
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
                            $payc = db('onlinebookpay')->where(['out_trade_no'=>$out_trade_no])->update($data);
                            wlog($this->log_path,"处理送书逻辑更新onlinebookpay：".$payc);
                        }

                    }
                }
                if ($type == 'pay_zlhd'){ //更新所有关联频道状态
                    $paychannel = db('channelpay')->where(['out_trade_no'=>$out_trade_no])->setField("status",'finish');
                    wlog($this->log_path,"更新所有关联频道状态channelpay：".$paychannel);
                }
                if ($type == 'pay_onlinebook'){ //更新
                    $odata['status'] = "finish";
                    //更新订单状态
                    $aaaaa = db('onlinebookpay')->where(['out_trade_no'=>$out_trade_no])->update($odata);
                    wlog($this->log_path,"更新订单状态onlinebookpay：".$aaaaa);
                    $bookid = $earns['bookid'];
                    $book = db('onlinebooks')->find($bookid);
                    $book_sum = $book['sumearns'] + ($data['total_fee']*100);
                    $ccck = db('onlinebooks')->where(['id'=>$bookid])->setField("sumearns",$book_sum);
                    wlog($this->log_path,"更新onlinebooks：".$ccck);
                }
                if ($type == 'pay_reciter'){ //更新保险公益杯支付表
                    $data['status'] = "finish";
                    //更新订单状态
                    $cacck = db('reciterpay')->where(['out_trade_no'=>$out_trade_no])->update($data);
                    wlog($this->log_path,"更新reciterpay：".$cacck);
                }
            } else {
                //$this->errorLog('微信支付返回结果签名验证失败',$data);
                wlog($this->log_path,'微信支付返回结果签名验证失败'.json_encode($datas));
            }
        } else {
            wlog($this->log_path,'微信支付返回结果'.json_encode($datas));
        }
        $this->redis->hdel('memberEarnings',$order['paymember']);
        $this->redis->hdel('memberEarnings',$order['getmember']);
        wlog($this->log_path,'------------微信支付结束-------------');
        echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    }

    /*

        * XML转array

        * @params xml $xml : xml 数据

        * return array $data : 转义后的array数组

        */

    private function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }

    /*
     * 验证签名
     * @params array $result : 微信支付成功返回的结果数组
     * return bool $ret : 成功true，失败false
     * */
    private function checkSign(array $data)
    {
        $str = '';
        ksort($data);
        foreach ($data as $k => $v) {
            if($k != 'sign') $str .= $k.'='.$v.'&';
        }
        $temp = $str . 'key='.WECHATPAY_KEY;     //key：商户支付密钥
        $sign = strtoupper(md5($temp));
        return $sign == $data['sign'] ? true : false;
    }



    private function errorLog($msg,$ret)
    {
        file_put_contents(ROOT_PATH . 'runtime/error/wxpaynofiy.log', "[" . date('Y-m-d H:i:s') . "] ".$msg."," .json_encode($ret).PHP_EOL, FILE_APPEND);
    }

    /**
     * 数据类型转换
     * @param $data
     * @return array|string
     */
    private function tranfer($data)
    {
        $data = arr_val_tran_str($data,'APP');
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        return $data;
    }

}