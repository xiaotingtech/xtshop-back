<?php
/**
 * Created by xtshop
 * Class User
 * Description:商品SKU类型验证器
 * @package app\common\validate
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-08-28 10:40
 */
namespace app\common\validate;

use think\Validate;
class ProductSkuType extends Validate
{
	protected $rule = [
        'name'=>'require',
	];
    protected $message  =   [
        'name.require' => 'SKU名称必须',
    ];
}
