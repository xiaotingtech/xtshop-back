<?php
/**
 * Created by xtshop
 * Class Node
 * Description:节点验证器
 * @package app\common\validate
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-25 17:06
 */
namespace app\common\validate;
use think\Validate;

class Node extends Validate
{
	protected $rule = [
		'name' => 'require',
		'title' => 'require',

	];
	protected $field = [
		'name' => '名称',
		'title' => '中文名称'
	];
	//设置场景
	protected $scene = [
		'save' => ['name','title'],
	];
}
