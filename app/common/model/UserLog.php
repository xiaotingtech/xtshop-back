<?php
/**
 * Created by xtshop
 * Class UserLog
 * Description:用户日志
 * @package app\common\model
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-25 15:28
 */
namespace app\common\model;

use think\Model;
class UserLog extends Model
{
    protected $autoWriteTimestamp = true;

    /**
     * @return \think\model\relation\BelongsTo
     * Description:所属用户
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-25 15:27
     */
    public function user()
    {
        return $this->belongsTo('User','uid','id')->field('id,username,phone');
    }
}