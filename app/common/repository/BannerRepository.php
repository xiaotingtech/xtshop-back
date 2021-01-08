<?php
namespace app\common\repository;

use app\common\model\Banner;
class BannerRepository extends BaseRepository
{
    //轮播model类
    protected $bannerModel;

    public function __construct()
    {
        $this->bannerModel=new Banner();
    }

    /**
     * @param $where
     * @param int $page
     * @param int $listRow
     * @return array
     * Description:后台获取轮播列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-19 17:29
     */
    public function getBackList($where,$page=1,$listRow=10){
        $count=$this->bannerModel->where($where)
            ->count();
        if($count>0) {
            $list = $this->bannerModel
                ->field('id,title,pic,url,target_type,sort_num')
                ->where($where)
                ->page($page, $listRow)->select();
            if(!$list->isEmpty()) {
                $listData=$list->toArray();
                foreach ($listData as $oneKey=>$oneLevelVal){
                    $listData[$oneKey]['pic']=get_real_url($oneLevelVal['pic']);
                }
                return [
                    'status' => 1,
                    'msg' => '获取成功！',
                    'data' => [
                        'list' => $listData,
                        'page' => $page,
                        'list_row' => $listRow,
                        'total' => $count,
                        'totalPage' => ceil($count / $listRow)
                    ],
                ];
            }else{
                return [
                    'status'=>1,
                    'msg'=>'没有数据了！',
                    'data'=>[
                        'list'=>[],
                        'page'=>$page,
                        'list_row'=>$listRow,
                        'total'=>$count,
                        'totalPage'=>ceil($count/$listRow)
                    ],
                ];
            }
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[
                    'list'=>[],
                    'page'=>$page,
                    'list_row'=>$listRow,
                    'total'=>$count,
                    'totalPage'=>ceil($count/$listRow)
                ],
            ];
        }
    }
    /**
     * @param $data
     * @return array
     * Description:
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-08-19 11:21
     */
    public function getList($data=[]){
        $type=1;
        //查询
        $banners=$this->bannerModel->field('id,title,target_type,url,pic')
            ->where('type',$type)->where('version',1)
            ->order('sort_num DESC')
            ->select()->toArray();
        if(!empty($banners)){
            foreach ($banners as $bk=>$bv){
                $banners[$bk]['pic']=get_real_url($bv['pic']);
            }
            return [
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>$banners,
            ];
        }else{
            return [
                'status'=>0,
                'msg'=>'没有数据！',
                'data'=>[],
            ];
        }
    }

    /**
     * @param $data
     * @return array
     * Description:轮播图保存
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: time
     */
    public function save($data){
        $validate = sunnier_validate('app\common\validate\Banner');
        if (!$validate->scene('save')->check($data)) {
            return ['status'=>0, 'msg'=>$validate->getError()];
        }
        $uid=0;
        if(!empty($user)){
            $uid=$user['id'];
        }
        $data['uid']=$uid;
        if(!empty($data['pic'])){
            $data['pic']=filter_pics_url($data['pic']);
        }
        //验证完成根据是否有ID保存数据
        if(!empty($data['id'])){
            if(isset($data['update_time'])){
                unset($data['update_time']);
            }
            if(isset($data['create_time'])){
                unset($data['create_time']);
            }
            if(!$this->bannerModel->update($data)){
                return [
                    'status'=>0,
                    'msg'=>'保存失败!',
                ];
            }
        }else{
            if(!$this->bannerModel->save($data)){
                return [
                    'status'=>0,
                    'msg'=>'保存失败!',
                ];
            }
        }
        return [
            'status'=>1,
            'msg'=>'保存成功！'
        ];
    }
}