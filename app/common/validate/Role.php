<?php
/**
 * Created by xtshop
 * Class Role
 * Description:角色验证器
 * @package app\common\validate
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-25 17:06
 */
namespace app\common\validate;
use think\Validate;

class Role extends Validate
{
	protected $rule = [
		'name' => 'require',
	];
	protected $message = [
		'name.require' => '名称必须',
	];
	//设置场景
	protected $scene = [
		'save' => ['name'],
	];
}
