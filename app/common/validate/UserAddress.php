<?php
/**
 * Created by xtshop
 * Class Cart
 * Description:用户地址验证类
 * @package app\common\validate
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-20 17:50
 */
namespace app\common\validate;
use think\Validate;

class UserAddress extends Validate
{
    protected $rule = [
        'uid' => 'require|number',
        'phone' => 'require',
        'username' => 'require',
        'address' => 'require',
        'address_district' => 'require',
        'address_door'=>'require',
    ];
    protected $field = [
        'uid'=>'用户ID',
        'phone' => '电话',
        'username' => '用户名',
        'address' => '地址',
        'address_district' => '街道',
        'address_door'=>'门牌号',
    ];
    protected $message  =   [
        'uid.require' => '用户ID必须',
        'uid.number' => '用户ID只能是数字',
        'phone.require' => '电话必须',
        'address.require' => '地址必须',
        'address_district.require' => '街道必须',
        'address_door.require' => '门牌号必须',
    ];
    //设置场景
    protected $scene = [
        'save' => ['uid','phone','username','address','address_district','address_door'],
    ];
}