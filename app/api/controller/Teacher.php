<?php
/**
 * Created by xtshop
 * Class Teacher
 * Description:导师详情
 * @package app\api\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-07-02 22:57
 */
namespace app\api\controller;

class Teacher extends Base
{
    protected $teacherRepository;

    public function initialize()
    {
        parent::initialize();

        $this->teacherRepository = app('app\common\repository\TeacherRepository');
    }

    /**
     * @return \think\response\Json
     * Description:导师详情
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 22:59
     */
    public function detail(){
        $postData=$this->postData;
        $result=$this->teacherRepository->detail($postData);
        return api_res($result['status'],$result['msg'],$result['data']);
    }
}