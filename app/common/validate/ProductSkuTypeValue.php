<?php
/**
 * Created by xtshop
 * Class User
 * Description:商品SKU值验证器
 * @package app\common\validate
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-08-28 10:40
 */
namespace app\common\validate;

use think\Validate;
class ProductSkuTypeValue extends Validate
{
	protected $rule = [
        'name'=>'require',
        'sku_type_id'=>'require',
	];
    protected $message  =   [
        'name.require' => 'SKU值必须',
        'sku_type_id.require'=>'SKU类型必须',
    ];
    //设置场景
    protected $scene = [
        'add' => ['name','sku_type_id'],
        'edit' => ['name'],
    ];
}
