<?php
/**
 * Created by xtshop
 * Class ProductSkuType
 * Description:商品SKU类型表
 * @package app\common\model
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-08-27 9:59
 */
namespace app\common\model;

use think\Model;
class ProductSkuType extends Model
{
    protected $autoWriteTimestamp = true;

    /**
     * @return mixed|\think\model\relation\HasMany
     * Description:关联值
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-18 15:40
     */
    public function values()
    {
        return $this->hasMany('ProductSkuTypeValue', 'sku_type_id', 'id');
    }
}