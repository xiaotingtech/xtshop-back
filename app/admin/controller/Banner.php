<?php
/**
 * Created by xtshop
 * Class Banner
 * Description:轮播控制器
 * @package app\admin\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-26 15:20
 */
namespace app\admin\controller;

use app\common\model\Banner as BannerModel;
class Banner extends AuthBase
{
    protected $bannerService;

    protected $bannerModel;

    public function initialize()
    {
        parent::initialize();
        $this->bannerModel=new BannerModel();
        $this->bannerService=app('app\common\repository\BannerRepository');
    }
    /**
     * @return mixed
     * Description:轮播图列表
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-08-13 13:43
     */
    public function bannerList(){
        $where='';
        $page=1;
        $listRow=10;
        $result =$this->bannerService->getBackList($where,$page,$listRow);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return mixed|void
     * Description:添加轮播图
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-08-13 13:43
     */
    public function bannerAdd()
    {
        $data = input('post.');
        if ($data) {
            $user=$this->getUser();
            $res = $this->bannerService->save($data,$user);
            if ($res['status']==1) {
                return show(1, '保存轮播图成功！');
            }
            return show(0,$res['msg']);
        } else {
            return show(0,'保存轮播图失败');
        }
    }

    /**
     * @param $id
     * @return \think\response\Json
     * Description:修改轮播图
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-19 17:43
     */
    public function bannerEdit($id)
    {
        if(!empty($id)) {
            $banner = $this->bannerModel->where('id', $id)->find();
            $banner['pic']=deal_pics_url($banner['pic']);
        }else{
            $banner=[];
        }
        $targetTypes=config('banner.target_types');
        $types=config('banner.type');
        return show(1,'获取成功！',[
            'banner' => $banner,
            'types'=>array_values($types),
            'target_types'=>array_values($targetTypes)
        ]);
    }

    /**
     * Description:删除
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-08-13 13:43
     */
    public function bannerDel()
    {
        $id = input('post.ids',0,'int');
        if (!is_numeric($id)) {
            return show(0, '没有获取ID数据');
        }
        if($this->bannerModel->destroy($id)){
            return show(1, '删除成功！');
        }else{
            return show(0, '删除失败！');
        }
    }
}