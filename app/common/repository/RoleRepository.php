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

use app\common\model\Role;
use app\common\model\Node;
use app\common\model\Access;
class RoleRepository extends BaseRepository
{
    //role的model
    protected $roleModel;

    public function __construct()
    {
        $this->roleModel = new Role();
    }

    /**
     * @param $where
     * @param $page
     * @param $listRow
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取角色列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-10 9:30
     */
    public function getList($where,$page,$listRow){
        $count=$this->roleModel->where($where)->count();
        if($count>0) {
            $list = $this->roleModel->field('id,name,remark,status')
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
     * Description:不分页的角色列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-10 15:09
     */
    public function getListSelect($where){
        $count=$this->roleModel->where($where)->count();
        if($count>0) {
            $list = $this->roleModel->field('id,name,remark,status')
                ->where($where)
                ->select();
            if(!$list->isEmpty()) {
                return [
                    'status' => 1,
                    'msg' => '获取成功！',
                    'data' => $list,
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
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:获取角色菜单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-10 14:11
     */
    public function getMenuList($where){
        $accessModel=new Access();
        $nodeModel=new Node();
        $accessData=$accessModel->field('node_id')->where($where)->select();
        if(!$accessData->isEmpty()) {
            $nodeIds=[];
            foreach ($accessData as $node){
                $nodeIds[]=$node['node_id'];
            }
            $list = $nodeModel->field('id,name,title,pid as parentId,icon,hidden,sort,level')
                ->where('id','IN',$nodeIds)
                ->where('status',1)
                ->select();
            if(!$list->isEmpty()){
                return [
                    'status'=>1,
                    'msg'=>'获取成功！',
                    'data'=>$list->toArray(),
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