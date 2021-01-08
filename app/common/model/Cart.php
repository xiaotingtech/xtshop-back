<?php
/**
 * Created by xtshop
 * Class Cart
 * Description:购物车模型类
 * @package app\common\model
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-20 10:45
 */
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Cart extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = true;
}