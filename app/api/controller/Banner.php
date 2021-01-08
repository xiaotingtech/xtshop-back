<?php
/**
 * Created by xtshop
 * Class Banner
 * Description:轮播控制器
 * @package app\api\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-27 22:43
 */
namespace app\api\controller;

class Banner extends Base
{
    protected $bannerRepository;

    public function initialize()
    {
        parent::initialize();

        $this->bannerRepository = app('app\common\repository\BannerRepository');
    }

    /**
     * @return \think\response\Json
     * Description:轮播列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-27 22:45
     */
    public function getList(){
        $result=$this->bannerRepository->getList();
        return api_res($result['status'],$result['msg'],$result['data']);
    }
}