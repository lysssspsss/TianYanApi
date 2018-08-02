<?php
namespace app\admin\model;

use think\Model;

class Member extends Model
{
	protected $name = 'member';
	// birthday修改器
	protected function setpwdAttr($value){
			return md5($value);
	}
}