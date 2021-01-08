<?php
/**
 * Created by xtshop
 * Class Cart
 * Description:地址模型
 * @package app\common\model
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-20 17:45
 */
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
class UserAddress extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = true;
}