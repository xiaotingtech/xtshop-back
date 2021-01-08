<?php
namespace app\api\controller;

class Subject extends Base
{
    protected $subjectRepository;

    public function initialize()
    {
        parent::initialize();

        $this->subjectRepository = app('app\common\repository\SubjectRepository');
    }

    /**
     * @return \think\response\Json
     * Description:获取推荐分类列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-28 15:06
     */
    public function getRecommendList()
    {
        $result = $this->subjectRepository->getRecommendList();
        return api_res($result['status'], $result['msg'], $result['data']);
    }
}