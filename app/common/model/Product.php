<?php
/**
 * Created by xtshop
 * Class Product
 * Description:商品model
 * @package app\common\model
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-27 11:06
 */
namespace app\common\model;

use think\Model;
class Product extends Model
{
    protected $autoWriteTimestamp = true;

    /**
     * @return \think\model\relation\HasOne
     * Description:商品详情
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 11:09
     */
    public function detail()
    {
        return $this->hasOne('ProductDetail', 'product_id', 'id')
            ->bind(['detail_id','detail_title','detail_desc','detail_html','detail_mobile_html']);
    }

    /**
     * @return \think\model\relation\HasMany
     * Description:SKU商品
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-27 10:44
     */
    public function skuStockList()
    {
        return $this->hasMany('SkuProduct', 'product_id', 'id');
    }

    /**
     * @return \think\model\relation\HasMany
     * Description:商品参数
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-27 14:30
     */
    public function productAttributeValueList()
    {
        return $this->hasMany('ProductAttribute', 'product_id', 'id');
    }
}