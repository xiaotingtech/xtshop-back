<?php
/**
 * Created by xtshop
 * Class SkuProduct
 * Description:SKU的商品子表
 * @package app\common\model
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-08-27 9:11
 */
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
class SkuProduct extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = true;
}