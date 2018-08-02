<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
    function back(){
        return $_SERVER['HTTP_REFERER'];
    }
	   
     /**
      * 获取客户端IP地址
      * @return string 客户端IP地址
      */
    function getClientIP() {
        global $ip;
        if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if(getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if(getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";
        return $ip;
    }


    /**
     * 对密码进行加密（不可逆）
     * @return string 加密后的密码
     */
    function _domd5($psw)
    {
        $salt = 'eWDWfj32128dwe123HHW';
        return md5($psw . $salt); //返回加salt后的散列
    }
    

    /**
      * 页面跳转
      * @param string $url
      * @return js
      */
    function headerUrl($info, $url) {
        echo "<script type='text/javascript'>alert('".$info."');location.href='{$url}';</script>";
        exit();
    }


    /**
      * js 弹窗返回
      * @param string $_info
      * @return js
      */
    function alertBack($info) {
        echo "<script>alert('".$info."');history.go(-1);</script>";
        exit();
    }


    function strs_sub($str) {
      if(mb_strlen($str) > 13) {
        $str = mb_substr($str, 0, 13);
        $str .= '...';
      }
      return $str;
    }