<?php
/**
 * Created by xtshop
 * Class Category
 * Description:商品分类管理
 * @package app\api\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-20 9:27
 */
namespace app\api\controller;

class Category extends Base
{
    protected $productCategoryRepository;

    public function initialize()
    {
        parent::initialize();

        $this->productCategoryRepository = app('app\common\repository\ProductCategoryRepository');
    }

    /**
     * @return \think\response\Json
     * Description:分类列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 9:27
     */
    public function getList()
    {
        $result = $this->productCategoryRepository->getAllList();
        return api_res($result['status'], $result['msg'], $result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:获取推荐分类列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 16:31
     */
    public function getRecommendList()
    {
        $result = $this->productCategoryRepository->getRecommendList();
        return api_res($result['status'], $result['msg'], $result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:获取活动分类列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 16:59
     */
    public function getActivityList()
    {
        $result = $this->productCategoryRepository->getActivityList();
        return api_res($result['status'], $result['msg'], $result['data']);
    }
}