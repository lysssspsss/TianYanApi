<?php
namespace app\index\controller;


class Factory
{
    public static function create_obj($type) {
        switch($type) {
            case 'wechat':
                return new WeChat();
            case 'lecture':
                return new Lecture();
            default:
                throw new Exception('type error!');
        }
    }

}
