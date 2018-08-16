<?php
namespace app\tools\controller;
use think\Controller;
use think\Input;



class Huanxin extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function curl_json_post($data,$url = TYPE_HX_URL,$header = ['Content-Type: application/json'])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查 url为https时使用
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:23.0) Gecko/20100101 Firefox/23.0");
        curl_setopt($ch, CURLOPT_TIMEOUT,5);   //只需要设置一个秒的数量就可以
        curl_setopt($ch, CURLOPT_REFERER, "http://www.google.ca/");
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$guest_ip, 'CLIENT-IP:'.$guest_ip));
        $output = curl_exec($ch);
        if (curl_errno($ch)) {
            wlog(APP_PATH.'log/Reg_Error.log','环信注册失败:'.curl_error($ch));
        }
        curl_close($ch);
        return $output;
    }

    public static function curl_json($data_string,$url = TYPE_HX_URL,$header = ['Content-Type: application/json'])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)
        ));
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            print curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

}
