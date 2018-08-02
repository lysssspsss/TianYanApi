<?php
namespace app\index\controller;
use think\Controller;
use think\Input;
use think\Db;
use think\Request;
class Weixin extends Common{
    public $client;
    public $wc;
    public function _initialize(){
        parent::_initialize();
        //获取微信配置信息
        $this->wc = db('wx_user')->where('id',1)->find();
        $options = array(
            'token'=>$this->wc['w_token'], //填写你设定的key
            'encodingaeskey'=>$this->wc['aeskey'], //填写加密用的EncodingAESKey
            'appid'=>$this->wc['appid'], //填写高级调用功能的app id
            'appsecret'=>$this->wc['appsecret'], //填写高级调用功能的密钥
        );
    }
    public function index(){
        if($this->wc['wait_access'] == 0){
            exit($_GET["echostr"]);
        }else{
            $this->responseMsg();
        }
    }
    public function responseMsg(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (empty($postStr)){
            exit("");
        }
        libxml_disable_entity_loader(true);
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        //点击菜单拉取消息时的事件推送
        /*
         * 1、click：点击推事件
         * 用户点击click类型按钮后，微信服务器会通过消息接口推送消息类型为event的结构给开发者（参考消息接口指南）
         * 并且带上按钮中开发者填写的key值，开发者可以通过自定义的key值与用户进行交互；
         */
        if($postObj->MsgType == 'event' && $postObj->Event == 'CLICK'){
            $keyword = trim($postObj->EventKey);
        }
        if(empty($keyword)){
            exit("Input something...");
        }
        // 图文回复
        $wx_img = db('wx_img')->where("keyword like '%$keyword%'")->find();
        if($wx_img) {
            $textTpl = "<xml>
                              <ToUserName><![CDATA[%s]]></ToUserName>
                              <FromUserName><![CDATA[%s]]></FromUserName>
                              <CreateTime>%s</CreateTime>
                              <MsgType><![CDATA[%s]]></MsgType>
                              <ArticleCount><![CDATA[%s]]></ArticleCount>
                              <Articles>
                                  <item>
                                    <Title><![CDATA[%s]]></Title> 
                                    <Description><![CDATA[%s]]></Description>
                                    <PicUrl><![CDATA[%s]]></PicUrl>
                                    <Url><![CDATA[%s]]></Url>
                                  </item>                               
                              </Articles>
                         </xml>";
            if(substr($wx_img['pic'],0,4)=='http'){
                $imgUrl = $wx_img['pic'];
            }else{
                $imgUrl = 'http://'.$_SERVER['HTTP_HOST'].'/public'.$wx_img['pic'];
            }
            //$resultStr = sprintf($textTpl,$fromUsername,$toUsername,$time,'news','1',$wx_img['title'],$wx_img['desc'], $imgUrl, $wx_img['url']);
            $resultStr = sprintf($textTpl,$fromUsername,$toUsername,$time,'news','1',$imgUrl,$wx_img['desc'], $imgUrl, $wx_img['url']);
            exit($resultStr);
        }


        // 文本回复
        $wx_text = db('wx_text')->where("keyword like '%$keyword%'")->find();
        if($wx_text) {
            $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                        </xml>";
            $contentStr = $wx_text['text'];
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, 'text', $contentStr);
            exit($resultStr);
        }
        // 其他文本回复
        $textTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        <FuncFlag>0</FuncFlag>
                    </xml>";
        $contentStr = '欢迎来到CLTPHP!';
        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, 'text', $contentStr);
        exit($resultStr);
    }
}