<?php
/**
 * Class Role
 * @package app\index\controller
 * Description:角色管理类
 * User: sunnier
 * Email: xiaoyao_xiao@126.com
 * Date: ${DATE}
 * Time: ${TIME}
 */
namespace app\admin\controller;

use app\common\model\Access;
use app\common\model\RoleUser;
use app\common\model\Node;
use app\common\model\Role as RoleModel;
class Role extends AuthBase
{
    protected $roleModel;

    public function initialize()
    {
        parent::initialize();
        $this->roleModel=new RoleModel();
    }
    /**
     * @return string
     * @throws \think\db\exception\DbException
     * Description:角色列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 14:51
     */
    public function roleList()
    {
        $page = input('get.page',1,'int');
        $list_row = input('get.list_row',0,'int');
        $list_row = !empty($list_row) ? $list_row : config('paginate.list_rows');
        $name=input('get.keyword','','string');
        $where='';
        if(!empty($name)){
            $where="name like '%".$name."%'";
        }
        $result=app('app\common\repository\RoleRepository')->getList($where,$page,$list_row);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:用于下拉选择的列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-10 15:08
     */
    public function roleListSelect()
    {
        $name=input('get.keyword','','string');
        $where='';
        if(!empty($name)){
            $where="name like '%".$name."%'";
        }
        $result=app('app\common\repository\RoleRepository')->getListSelect($where);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return string|void
     * @throws \Exception
     * Description:添加角色操作
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 14:50
     */
    public function roleAdd()
    {
    	$data = input('post.');
    	if ($data) {
    		$validate = sunnier_validate('app\common\validate\Role');
    		if (!$validate->scene('save')->check($data)) {
    			return show(0, $validate->getError());
    		}
            $roleModel=$this->roleModel;
    		if(empty($data['id'])) {
                $res = $roleModel->save($data);
                if ($res) {
                    return show(1, '新增角色成功');
                }else{
                    return show(0, '新增角色失败');
                }
            }else{
                $res = $roleModel->update($data);
                if ($res) {
                    return show(1, '修改角色成功');
                }else{
                    return show(0, '修改角色失败');
                }
            }
    	} else {
            return show(0, '修改角色失败');
    	}
    }

    /**
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:角色赋权
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 14:50
     */
    public function access()
    {
        $rId = input('get.id',0,'int');
        if (!is_numeric($rId)) {
            abort(404,'未获取到ID！');
        }
        $roleModel=new RoleModel();
        $nodeModel=new Node();
        $role = $roleModel->field('id,name')->where(['id'=>$rId])->find();
        $node = $nodeModel->field('id,title,pid,level,sort')->where(['status'=>1])->order('sort desc')->select()->toArray();
        $nodeIdArr=[];
        foreach ($node as $nk=>$nv){
            $nodeIdArr[]=$nv['id'];
        }
        $accessResNodeArr=[];
        $accessModel=new Access();
        $accessRes=$accessModel->field('role_id,node_id')
            ->where('role_id',$rId)
            ->where('node_id','IN',$nodeIdArr)->select();
        if(!empty($accessRes)){
            foreach ($accessRes as $accessVal){
                $accessResNodeArr[]=$accessVal['node_id'];
            }
        }
        $node = new \util\TreeUtil($node, 'id', 'pid');
        if ($node = $node->tree) {
            foreach ($node as $k => &$v) {
                if(!empty($accessResNodeArr)){
                    if(in_array($v['id'],$accessResNodeArr)){
                        $v['access']=1;
                    }else{
                        $v['access'] = 0;
                    }
                }else{
                    $v['access'] = 0;
                }
            }
        }
    	return $this->fetch('', [
            'node' => $node,
            'role' => $role
        ]);
    }

    /**
     * @throws \Exception
     * Description:保存权限
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 14:50
     */
    public function setAccess()
    {
        $data = input("post.");
        $rId = $data["roleId"];
        $accessModel=new Access();
        $accessModel->where(['role_id'=>$rId])->delete();
        $access = $data["menuIds"];
        $accessData = array();
        if (isset($access)) {
            $access=explode(',',$access);
            foreach ($access as $k => $v) {
                $tmp = explode('_', $v);
                $accessData[] = array(
                    'role_id' => $rId,
                    'node_id' => $tmp[0],
                    'level'   => $tmp[1]
                );
            }
        }
        $res = $accessModel->insertAll($accessData);
        if ($res) {
            return show(1, "修改权限成功");
        }
        return show(0, "修改权限失败");
    }

    /**
     * Description:删除角色操作
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 14:50
     */
    public function roleStatus()
    {
        $id = input('post.id',0,'int');
        if (!empty($id)) {
            if (is_numeric($id)) {
                $status = input('post.status',0,'int');
                $res = $this->roleModel->where('id',$id)->update(['status'=>$status]);
                if ($res) {
                    return show(1, '修改角色状态成功');
                }else{
                    return show(0, '修改角色状态失败');
                }
            }else{
                return show(0, '修改角色状态失败');
            }
        } else {
            return show(0, '修改角色状态失败');
        }
    }

    /**
     * @return \think\response\Json
     * Description:删除角色
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-10 9:45
     */
    public function roleDel()
    {
        $id = input('post.ids');
        if (!empty($id)) {
            if (is_array($id)) {
                $res = $this->roleModel->where('id','IN',$id)->delete();
                if ($res) {
                    return show(1, '删除角色成功');
                }else{
                    return show(0, '删除角色失败');
                }
            }else{
                $res = $this->roleModel->where('id',$id)->delete();
                if ($res) {
                    return show(1, '删除角色成功');
                }else{
                    return show(0, '删除角色失败');
                }
            }
        } else {
            return show(0, '删除角色失败');
        }
    }

    /**
     * @return \think\response\Json
     * Description:角色对应的菜单获取
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-10 14:17
     */
    public function listMenu(){
        $roleId=input('get.roleId',0,'int');
        if(!empty($roleId)){
            $where='role_id='.$roleId;
            $result=app('app\common\repository\RoleRepository')->getMenuList($where);
            return show($result['status'],$result['msg'],$result['data']);
        }else{
            return show(0, '获取失败');
        }
    }

    /**
     * @return \think\response\Json
     * Description:获取用户角色
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-10 14:55
     */
    public function member(){
        $memberId=input('get.memberId',0,'int');
        if(!empty($memberId)){
            $roleUserModel=new RoleUser();
            $roleResult=$roleUserModel->where('user_id',$memberId)->select();
            if(!$roleResult->isEmpty()) {
                $roleIds=[];
                foreach ($roleResult as $roleVal){
                    $roleIds[]=$roleVal['role_id'];
                }
                $roleModel=new RoleModel();
                $roleData=$roleModel->where('id','IN',$roleIds)->select();
                return show(1, '获取成功！',$roleData);
            }else{
                return show(1,'没有角色！');
            }
        }else{
            return show(0,'获取用户ID失败！');
        }
    }

    /**
     * @return \think\response\Json
     * Description:修改用户角色
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-10 15:37
     */
    public function alloc(){
        $memberId=input('post.adminId',0,'int');
        $roleIds=input('post.roleIds');
        if(!empty($memberId)&&!empty($roleIds)){
            $roleUserModel=new RoleUser();
            $roleIdsArr=explode(',',$roleIds);
            $insertData=[];
            foreach ($roleIdsArr as $roleId){
                $insertData[]=[
                    'role_id'=>$roleId,
                    'user_id'=>$memberId
                ];
            }
            $roleUserModel->where('user_id',$memberId)->delete();
            if($roleUserModel->insertAll($insertData)){
                return show(1,'修改成功！');
            }
            return show(0,'修改失败！');
        }else{
            if(!empty($memberId)){
                $roleUserModel=new RoleUser();
                if($roleUserModel->where('user_id',$memberId)->delete()){
                    return show(1,'修改成功！');
                }else{
                    return show(0,'修改失败！');
                }
            }
            return show(1,'修改成功！');
        }
    }
}
