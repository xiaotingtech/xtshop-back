<?php
/**
 * Created by xtshop
 * Class User
 * Description:商品分类验证器
 * @package app\common\validate
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-09-11 10:40
 */
namespace app\common\validate;

use think\Validate;
class ProductCategory extends Validate
{
	protected $rule = [
        'name'=>'require',
	];
    protected $message  =   [
        'name.require' => '分类名称必须',
    ];
}
