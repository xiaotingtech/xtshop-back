<?php
/**
 * Created by xtshop
 * Class User
 * Description:经销商表
 * @package app\common\model
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-25 15:26
 */
namespace app\common\model;

use think\Model;
class Coupon extends Model
{
    protected $autoWriteTimestamp = true;

    /**
     * @return \think\model\relation\BelongsTo
     * Description:关联用户
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-04 17:31
     */
    public function user()
    {
        return $this->belongsTo('User', 'uid', 'id');
    }

    /**
     * @return \think\model\relation\BelongsTo
     * Description:关联的经销商
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-04 17:45
     */
    public function agency()
    {
        return $this->belongsTo('Agency', 'agency_id', 'id')->bind(['username']);
    }
}