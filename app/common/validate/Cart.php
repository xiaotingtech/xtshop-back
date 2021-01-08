<?php
/**
 * Created by xtshop
 * Class Cart
 * Description:购物车验证类
 * @package app\common\validate
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-20 15:34
 */
namespace app\common\validate;
use think\Validate;

class Cart extends Validate
{
    protected $rule = [
        'uid' => 'require|number',
        'product_id' => 'require|number',
        'cate_id'=>'require|number',
        'sku_product_id' => 'require|number',
        'img' => 'require',
        'product_name' => 'require',
        'product_price' => 'require|float',
        'product_stock' => 'require|number',
        'product_num'=>'require|number',
        'sku_property'=>'require',
        'property_value'=>'require',
    ];
    protected $field = [
        'uid'=>'用户ID',
        'product_id' => '商品ID',
        'sku_product_id' => '子商品ID',
        'img' => '商品预览图',
        'product_name' => '商品标题',
        'product_price' => '商品价格',
        'product_stock' => '商品库存',
        'product_num'=>'商品数量',
        'sku_property'=>'商品规格ID',
        'property_value'=>'商品规格值',
    ];
    protected $message  =   [
        'uid.require' => '用户ID必须',
        'uid.number' => '用户ID只能是数字',
        'product_id.require' => '商品ID必须',
        'product_id.number' => '商品ID只能是数字',
        'sku_product_id.require' => '子商品ID必须',
        'sku_product_id.number' => '子商品ID只能是数字',
        'cate_id.require' => '商品分类必须',
        'cate_id.number' => '商品分类只能是数字',
        'img.require' => '商品预览图必须',
        'product_name.require' => '商品名称必须',
        'product_price.require' => '商品价格必须',
        'product_price.float' => '商品价格只能是数字',
        'product_stock.require' => '商品库存数必须',
        'product_num.require' => '商品购买数必须',
        'sku_property.require' => '商品规格必须',
        'property_value.require' => '商品规格值必须',
    ];
    //设置场景
    protected $scene = [
        'save' => ['uid','product_id','sku_product_id','cate_id','img',
            'product_name','product_price','product_stock','product_num',
            'sku_property','property_value'],
    ];
}