<?php
/**
 * Created by xtshop
 * Class User
 * Description:专题验证器
 * @package app\common\validate
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-28 12:00
 */
namespace app\common\validate;

use think\Validate;
class Subject extends Validate
{
	protected $rule = [
        'name'=>'require',
	];
    protected $message  =   [
        'name.require' => '专题名称必须',
    ];
}
