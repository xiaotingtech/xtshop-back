<?php
/**
 * Created by xtshop
 * Class SubjectRepository
 * Description:专题管理仓库类
 * @package app\common\repository
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-28 12:04
 */
namespace app\common\repository;

use app\common\model\Subject;
class SubjectRepository extends BaseRepository
{
    //Subject验证器
    protected $subjectValidate;
    //Subject的model
    protected $subjectModel;

    public function __construct()
    {
        $this->subjectValidate = sunnier_validate('app\common\validate\Subject');
        $this->subjectModel = new Subject();
    }

    /**
     * @param $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-25 11:05
     */
    public function info($id){
        return $this->subjectModel->where('id',$id)->find();
    }
    /**
     * @param $data
     * @return array
     * Description:保存数据
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-25 10:53
     */
    public function save($data){
        if (!$this->subjectValidate->scene('save')->check($data)) {
            return ['status'=>0, 'msg'=>$this->subjectValidate->getError()];
        }
        if(!empty($data['icon'])){
            $data['icon']=filter_pics_url($data['icon']);
        }
        //验证完成根据是否有ID保存数据
        if(!empty($data['id'])){
            if(isset($data['update_time'])){
                unset($data['update_time']);
            }
            if(isset($data['create_time'])){
                unset($data['create_time']);
            }
            if(!$this->subjectModel->update($data)){
                return [
                    'status'=>0,
                    'msg'=>'保存失败!',
                ];
            }
        }else{
            if(!$this->subjectModel->save($data)){
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

    /**
     * @param $where
     * @param int $page
     * @param int $listRow
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取商品专题列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-18 18:20
     */
    public function getList($where,$page=1,$listRow=10){
        $count=$this->subjectModel->where($where)
            ->count();
        if($count>0) {
            $list = $this->subjectModel
                ->field('id,name,pid,status,show_navigation,product_num,sort_num,level')
                ->where($where)
                ->page($page, $listRow)->select();
            if(!$list->isEmpty()) {
                $listData=$list->toArray();
                foreach ($listData as $oneKey=>$oneLevelVal){
                    $oneLevelVal['level']=$oneLevelVal['level']-1;
                    $listData[$oneKey]=$oneLevelVal;
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
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取商品专题
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-11 11:52
     */
    public function getTreeList($where){
        $count=$this->subjectModel->where($where)->count();
        if($count>0) {
            $list = $this->subjectModel
                ->field('id,name,pid as parentId,sort_num')
                ->where($where)
                ->select();
            if(!$list->isEmpty()) {
                $listData=$list->toArray();
                //先将第一级拿出来
                $oneLevelData=[];
                foreach ($listData as $oneKey=>$oneLevelVal){
                    if($oneLevelVal['parentId']==0){
                        $oneLevelData[]=$oneLevelVal;
                        unset($listData[$oneKey]);
                    }
                }
                //遍历第一级查询对应的第二级放到一级下面
                foreach ($oneLevelData as $twoKey=>$twoLevelData){
                    $twoChildren=[];
                    foreach ($listData as $ltKey=>$ltVal){
                        if($ltVal['parentId']==$twoLevelData['id']){
                            $twoChildren[]=$ltVal;
                        }
                    }
                    $twoLevelData['children']=$twoChildren;
                    $oneLevelData[$twoKey]=$twoLevelData;
                }
                return [
                    'status' => 1,
                    'msg' => '获取成功！',
                    'data' => $oneLevelData,
                ];
            }else{
                return [
                    'status'=>1,
                    'msg'=>'没有数据了！',
                    'data'=>[],
                ];
            }
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[],
            ];
        }
    }

    /**
     * @param $where
     * @param string $sort
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:接口对应的所有数据列表返回
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 9:29
     */
    public function getAllList($where='',$sort='sort_num ASC'){
        $count=$this->subjectModel->where($where)
            ->where('show_navigation',1)->count();
        if($count>0) {
            $list = $this->subjectModel
                ->field('id,name,icon,pid')
                ->where($where)
                ->where('show_navigation',1)
                ->order($sort)
                ->select();
            if(!$list->isEmpty()) {
                $listData=$list->toArray();
                foreach ($listData as $lk=>$lv){
                    if(!empty($lv['icon'])){
                        $listData[$lk]['icon']=get_real_url($lv['icon']);
                    }
                }
                return [
                    'status' => 1,
                    'msg' => '获取成功！',
                    'data' => $listData,
                ];
            }else{
                return [
                    'status'=>1,
                    'msg'=>'没有数据了！',
                    'data'=>[],
                ];
            }
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[],
            ];
        }
    }

    /**
     * @param string $where
     * @param string $sort
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取活动菜单列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-20 17:00
     */
    public function getRecommendList($where='',$sort='sort_num DESC,id DESC'){
        $count=$this->subjectModel->where($where)
            ->where('pid','=',0)
            ->where('show_home_recommend',1)->count();
        if($count>0) {
            $list = $this->subjectModel
                ->field('id,name,icon,pid')
                ->where($where)
                ->where('pid','=',0)
                ->where('show_home_recommend',1)
                ->order($sort)
                ->limit(5)
                ->select();
            if(!$list->isEmpty()) {
                $listData=$list->toArray();
                foreach ($listData as $lk=>$lv){
                    if(!empty($lv['icon'])){
                        $listData[$lk]['icon']=get_real_url($lv['icon']);
                    }
                }
                return [
                    'status' => 1,
                    'msg' => '获取成功！',
                    'data' => $listData,
                ];
            }else{
                return [
                    'status'=>1,
                    'msg'=>'没有数据了！',
                    'data'=>[],
                ];
            }
        }else{
            return [
                'status'=>1,
                'msg'=>'没有数据了！',
                'data'=>[],
            ];
        }
    }
}