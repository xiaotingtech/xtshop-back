<?php
/**
 * Created by xtshop
 * Class ProductInfo
 * Description:商品信息处理
 * @package app\common\event
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-07-02 17:24
 */
namespace app\common\event;

use app\common\model\UserProductBrowse;
class ProductInfo extends Base
{
    public $userProductBrowse;

    public function __construct(UserProductBrowse $userProductBrowse)
    {
        parent::__construct();
        $this->userProductBrowse = $userProductBrowse;
    }
}