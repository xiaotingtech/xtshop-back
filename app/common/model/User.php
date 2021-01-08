<?php
/**
 * Created by xtshop
 * Class User
 * Description:用户表
 * @package app\common\model
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-25 15:26
 */
namespace app\common\model;

use think\Model;
class User extends Model
{
    protected $autoWriteTimestamp = true;

    /**
     * @return \think\model\relation\BelongsToMany
     * Description: 关联角色
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-25 15:27
     */
    public function roles()
    {
        return $this->belongsToMany('Role', 'role_user', 'role_id', 'user_id');
    }
}