<?php
/**
 * Created by xtshop
 * Class Order
 * Description:订单主表
 * @package app\common\model
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-27 14:19
 */
namespace app\common\model;

use think\Model;
class Order extends Model
{
    protected $autoWriteTimestamp = true;

    /**
     * @return \think\model\relation\HasOne
     * Description:绑定用户信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-26 11:32
     */
    public function user()
    {
        return $this->hasOne('User', 'id', 'uid')
            ->bind(['nickname'=>'username']);
    }

    /**
     * @return \think\model\relation\HasOne
     * Description:快递信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-27 11:01
     */
    public function delivery()
    {
        return $this->hasOne('OrderDelivery', 'order_id', 'id')
            ->bind(['delivery_company','delivery_sn','delivery_time'=>'create_time']);
    }

    /**
     * @return \think\model\relation\HasMany
     * Description:子订单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-26 15:57
     */
    public function productOrder()
    {
        return $this->hasMany('ProductOrder', 'order_id', 'id');
    }

    /**
     * @return \think\model\relation\HasMany
     * Description:操作日志
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-27 11:37
     */
    public function manageLog()
    {
        return $this->hasMany('OrderManageLog', 'order_id', 'id');
    }
}