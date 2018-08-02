<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
if(!file_exists('./public/install.lock')){
    if(file_exists('./install/index.php')){
        header("location:./install/index.php");
        exit;
    }else{
        header("Content-type: text/html;charset=utf-8");
        echo "安装文件不存在，请上传安装文件。如已安装过，请新建config/install.lock文件。";
        die();
    }
}

// [ 应用入口文件 ]
if (!defined('__PUBLIC__')) {
    $_public = rtrim(dirname(rtrim($_SERVER['SCRIPT_NAME'], '/')), '/');
    define('__PUBLIC__', (('/' == $_public || '\\' == $_public) ? '' : $_public).'/public');
}
// 定义应用目录
define('APP_PATH',  __DIR__.'/app/');
define('DATA_PATH',  __DIR__.'/runtime/Data/');
// 加载框架引导文件
require __DIR__ . '/think/start.php';
