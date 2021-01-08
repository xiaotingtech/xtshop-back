<?php
namespace app\common\repository;

use app\common\model\Access;
use app\common\model\Node;
use app\common\model\RoleUser;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\db\exception\DbException;
use think\facade\Log;
/**
 * Created by xtshop
 * Class NodeMenu
 * Description:节点菜单类仓库
 * @package app\common\repository
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-06-25 15:28
 */
class NodeMenu
{

	protected $menu;
	protected $role;
	protected $access;

	public function __construct()
	{
		$this->menu = new Node();
		$this->role = new RoleUser();
		$this->access = new Access();
	}

    /**
     * @param $access
     * @param $is_super
     * @return array
     * Description:获取菜单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 11:05
     */
	protected function menu($access, $is_super,$role_id)
	{

		$node = [];
		if ($is_super == false) {
            $node = $this->menu
                ->field('id,title,name,level,pid as parentId,icon,hidden,sort')
                ->whereIn('id', $access)
                ->where(['status' => 1])
                ->select();
		} else {
            $node = $this->menu
                ->field('id,title,icon,name,level,pid as parentId,icon,hidden,sort')
                ->where(['status' => 1])
                ->select();
		}
		if(!$node->isEmpty()){
		    $node=$node->toArray();
        }
		return ['menus'=>array_values($node),'roles'=>$role_id];
	}

    /**
     * @param $user
     * @param bool $is_super
     * @return array
     * Description:根据用户获取菜单
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-06-26 11:05
     */
	public function get_by_user_role_menu($user, $is_super = false)
	{
		$access = [];
		if ($is_super == false) {
			if (!empty($user['id'])){
				$user_id = $user['id'];
			}else {
                Log::error('nodemenu，没有用户信息');
			}
			try {
				$role_id = $this->role->where(['user_id' => $user_id])->field('role_id')->find();
				if (!empty($role_id['role_id'])) {
					$access = $this->access->where('role_id', '=', $role_id['role_id'])
						->order('level asc')
						->column('node_id');
				}
			} catch (DataNotFoundException $e) {
				Log::error('nodemenu.'.$e->getMessage().PHP_EOL.$e->getTraceAsString());
			} catch (ModelNotFoundException $e) {
				Log::error('nodemenu.'.$e->getMessage().PHP_EOL.$e->getTraceAsString());
			} catch (DbException $e) {
				Log::error('nodemenu.'.$e->getMessage().PHP_EOL.$e->getTraceAsString());
			}
		}
		return $this->menu($access, $is_super,$role_id);
	}
}