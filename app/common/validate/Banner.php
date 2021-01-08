<?php
/**
 * Created by xtshop
 * Class Banner
 * Description:轮播图验证器
 * @package app\common\validate
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-26 15:35
 */
namespace app\common\validate;

use think\Validate;
class Banner extends Validate
{
    protected $rule = [
        'title'=>'require|min:1',
        'target_type'=>'require|gt:0',
        'url' => 'require|min:1',
        'pic' => 'require|min:1',
    ];
    protected $field = [
        'attachment_id' => '附件ID',
        'title'=>'标题',
        'target_type'=>'跳转目标类型',
        'url' => 'banner链接',
        'pic' => '远程路径',
    ];
    protected $message = [
        'title.require' => '标题不能为空',
        'title.min' => '标题不能为空',
        'target_type.require' => '跳转目标类型必须选择',
        'target_type.gt' => '跳转目标类型必须选择',
        'pic.require' => '图片不能为空',
        'pic.min' => '图片不能为空',
        'url.require' => '链接不能为空',
        'url.min' => '链接不能为空',
    ];
    //设置场景
    protected $scene = [
        'save' => ['target_type','title','pic'],
    ];
}