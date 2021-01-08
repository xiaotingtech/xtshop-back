<?php
/**
 * Created by xtshop
 * Class RoleRepository
 * Description:角色的仓库类
 * @package app\common\repository
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-09-09 16:39
 */
namespace app\common\repository;

use app\common\model\Node;
class NodeRepository extends BaseRepository
{
    //node的model
    protected $nodeModel;

    public function __construct()
    {
        $this->nodeModel = new Node();
    }

    /**
     * @param $where
     * @param int $page
     * @param int $listRow
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取菜单列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-09 17:03
     */
    public function getList($where,$page=1,$listRow=10){
        $count=$this->nodeModel->where($where)->where('status',1)->count();
        if($count>0) {
            $list = $this->nodeModel
                ->field('id,name,title,pid as parentId,icon,hidden,sort,level')
                ->where('status',1)
                ->where($where)
                ->page($page, $listRow)->select();
            if(!$list->isEmpty()) {
                return [
                    'status' => 1,
                    'msg' => '获取成功！',
                    'data' => [
                        'list' => $list,
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
     * Description:节点按两级排序
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-10 14:20
     */
    public function getListThree($where){
        $count=$this->nodeModel->where($where)->where('status',1)->count();
        if($count>0) {
            $list = $this->nodeModel
                ->field('id,name,title,pid as parentId,icon,hidden,sort,level')
                ->where('status',1)
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
}