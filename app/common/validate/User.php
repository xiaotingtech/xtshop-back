<?php
/**
 * Created by xtshop
 * Class User
 * Description:用户验证器
 * @package app\common\validate
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-25 17:06
 */
namespace app\common\validate;
use think\Validate;

class User extends Validate
{
	protected $rule = [
		'username' => 'require|max:25|unique:user',
        'phone'=>'require|regex:1[0-9]{10}',
		'password' => 'require|min:5',
		'code'=>'require|regex:[0-9]{4}',
	];
    protected $message  =   [
        'username.require' => '用户名必须填写',
        'username.max'     => '用户名不能超过25个字节',
        'username.unique'     => '用户名不能重复',
        'phone.require' => '手机号必须填写',
        'phone.regex'     => '请输入正确的手机号格式！',
        'password.require' => '密码必须填写',
        'password.min'     => '密码长度不能小于5个字节',
        'code.require'=>'验证码必须',
        'code.regex'=>'验证码格式不正确',
    ];
	protected $field = [
		'username' => '用户名',
		'password' => '密码',
		'code'   => '验证码',
	];
	//设置场景
	protected $scene = [
		'login' => ['username'=>'require|max:25','password'],
        'login_api' => ['phone','code'],
	];
}
