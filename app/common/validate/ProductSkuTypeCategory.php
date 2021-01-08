<?php
/**
 * Created by xtshop
 * Class User
 * Description:商品SKU的分类的验证器
 * @package app\common\validate
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-09-18 10:42
 */
namespace app\common\validate;

use think\Validate;
class ProductSkuTypeCategory extends Validate
{
	protected $rule = [
        'name'=>'require',
	];
    protected $message  =   [
        'name.require' => '类型名称必须',
    ];
}
