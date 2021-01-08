<?php
/**
 * Created by xtshop
 * Class User
 * Description:商品验证器
 * @package app\common\validate
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-25 17:06
 */
namespace app\common\validate;

use think\Validate;
class Product extends Validate
{
	protected $rule = [
		'title' => 'require|max:255',
        'price'=>'require',
        'sub_title'=>'require',
	];
    protected $message  =   [
        'title.require' => '商品名称必须填写',
        'price.require' => '商品价格必须填写',
        'sub_title.require' => '商品副标题必须填写',
    ];
}
