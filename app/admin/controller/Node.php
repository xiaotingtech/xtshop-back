<?php
/**
 * Created by xtshop
 * Class Node
 * Description:权限节点管理类
 * @package app\admin\controller
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-26 12:03
 */
namespace app\admin\controller;

use app\common\model\Node as NodeModel;
class Node extends AuthBase
{
    protected $nodeModel;

    public function initialize()
    {
        parent::initialize();
        $this->nodeModel=new NodeModel();
    }
    /**
     * @return string
     * @throws \Exception
     * Description:节点列表
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 12:03
     */
    public function nodeList()
    {
        $page = input('get.page',0,'int');
        $list_row = input('get.list_row',config('paginate.list_rows'),'int');
        $parentId=input('get.parentId',0,'int');
        $where='';
        if(!empty($parentId)){
            $where="pid = ".$parentId;
        }else{
            $where="pid = 0";
        }
        $result=app('app\common\repository\NodeRepository')->getList($where,$page,$list_row);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return \think\response\Json
     * Description:所有节点的树形结构
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-10 14:19
     */
    public function nodeTree()
    {
        $where='';
        $result=app('app\common\repository\NodeRepository')->getListThree($where);
        return show($result['status'],$result['msg'],$result['data']);
    }

    /**
     * @return string|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Description:添加节点
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 14:51
     */
    public function nodeAdd()
    {
    	$data = input('post.');
    	if ($data) {
    	    if(!empty($data['parentId'])){
    	        $data['pid']=$data['parentId'];
    	        $data['level']=1;
            }else{
                $data['pid']=0;
                $data['level']=0;
            }
            $validate = sunnier_validate('app\common\validate\Node');
            if (!$validate->scene('save')->check($data)) {
                return show(0, $validate->getError());
            }
            if (isset($data['id']) && is_numeric($data['id'])) {
                return $this->updateNode($data);
            }
            $res = $this->nodeModel->save($data);
            if ($res) {
                return show(1, '新增节点成功');
            }
            return show(0, '新增节点失败');
    	}else{
            return show(0, '新增节点失败');
        }
    }

    /**
     * @return \think\response\Json
     * Description:修改节点
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-09 17:37
     */
    public function nodeEdit()
    {
    	$id = input('get.id',0,'int');
    	if (!is_numeric($id)) {
    		abort(404,'没有获取ID数据！');
    	}
    	$node = $this->nodeModel->field('id,name,title,icon,pid as parentId,hidden,sort')->where(['status'=>1,'id'=>$id])->find();
    	return show(1,'获取成功！',$node);
    }

    /**
     * @param $data
     * Description:更新节点数据
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 14:51
     */
    public function updateNode($data)
    {
    	$id = $data['id'];
    	$row = $this->nodeModel->update($data);
    	if ($row) {
    		return show(1, '修改成功');
    	}
    	return show(0, '修改失败');
    }

    /**
     * @return \think\response\Json
     * Description:隐藏节点
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-09 17:26
     */
    public function hidden()
    {
    	$id = input('get.id',0,'int');
    	$status = input('get.hidden',0,'int');
    	if (!is_numeric($id)) {
            return show(0, '没有获取ID数据');
    	}
    	$row = $this->nodeModel->update(['hidden'=>$status,'id'=>$id]);
    	if ($row) {
    		return show(1, '修改成功'.$this->nodeModel->getLastSql());
    	}
    	return show(0, '隐藏失败');
    }

    /**
     * @param $id
     * @return \think\response\Json
     * Description:删除节点
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-09-09 17:49
     */
    public function nodeDel($id)
    {
        $row = $this->nodeModel->update(['status'=>0,'id'=>$id]);
        if ($row) {
            return show(1, '删除成功');
        }else{
            return show(0, '删除失败');
        }
    }
}
