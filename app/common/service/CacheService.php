<?php
/**
 * Class CacheService
 * @package app\common\service
 * Description:缓存服务类
 * Author: sunnier
 * Email: xiaoyao_xiao@126.com
 * Date: 2020/7/21
 * Time: 14:30
 */
namespace app\common\service;

use think\facade\Cache;
use think\App;
class CacheService extends BaseService
{
    //redis对象
    protected $redis;
    //前缀
    protected $prefix;

    public function __construct(App $app,$prefix='page_list_',$dbIndex=1)
    {
        parent::__construct($app);
        $this->redis = Cache::store('redis')->handler();
        $this->redis->select($dbIndex);
        $this->prefix=$prefix;
    }

    /**
     * @param $tagName
     * @param $data
     * @return array
     * Description:将数据插入队列
     * Author: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019/12/3
     * Time: 11:04
     */
    public function pushList($tagName,$data){
        $result=$this->redis->lpush($tagName,json_encode($data));
        if($result) {
            return [
                'status' => 1,
                'msg' => '插入成功！'
            ];
        }else{
            return [
                'status' => 1,
                'msg' => '插入失败！'
            ];
        }
    }

    /**
     * @param $tagName
     * @param $data
     * @return array
     * Description:弹出队列
     * Author: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019/12/3
     * Time: 11:04
     */
    public function popList($tagName){
        $result=$this->redis->rpop($tagName);
        if(!empty($result)) {
            return [
                'status' => 1,
                'msg' => '获取成功！',
                'data' => json_decode($result,true)
            ];
        }else{
            return [
                'status' => 0,
                'msg' => '没有数据了！',
                'data' => []
            ];
        }
    }

    /**
     * @param $tag
     * @return array
     * Description:缓存tag对应的素材数据
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-11-06 16:35
     */
    public function cacheTagData($tag){
        try {
            $materialIds = [];
            $materialTagRes = model('common/MaterialTag')->field('material_id')->where('tag_id', $tag['id'])->select();
            if (!$materialTagRes->isEmpty()) {
                foreach ($materialTagRes as $val) {
                    $materialIds[] = $val['material_id'];
                }
            }
            $searchRes = app('app\common\service\SearchService')->getMaterialByKeywordNoTag($tag['title'], $materialIds);
            if (!empty($searchRes)) {
                $materialIds = array_merge($materialIds, $searchRes);
            }
            $key='tag_materials_'.$tag['id'];
            //然后依次存到redis
            if (!empty($materialIds)) {
                $addArr=[];
                foreach ($materialIds as $k=>$id){
                    array_push($addArr,$k+1);
                    array_push($addArr,$id);
                }
                if(!empty($addArr)) {
                    $this->setSortData($key, $addArr);
                }
            }
            return [
                'status' => 1,
                'msg' => '存储完成'
            ];
        }catch (\Exception $e){
            return [
                'status' => 0,
                'msg' => '存储出错'.$e->getMessage()
            ];
        }
    }

    /**
     * @param null $key
     * @param $key
     * @param array $addArr
     * Description:添加排序条件数据
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-11-06 17:39
     */
    public function setSortData($key = null,$addArr=[])
    {
        array_unshift($addArr,$this->prefix.$key);
        call_user_func_array(array($this->redis, 'zadd'), $addArr);
    }

    /**
     * @param null $key
     * @param null $value
     * @param bool $detail
     * Description:从排序条件中删除数据
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-11-06 14:44
     */
    public function delSortData($key = null, $value = null,$detail=false)
    {
        $this->redis->zRem($this->prefix . $key, $value);
        if($detail) {
            $check = $this->redis->exists($this->prefix . $key . 'detail_' . $value);
            if ($check) {
                $this->redis->del($this->prefix . $key . 'detail_' . $value);
            }
            $check_list = $this->redis->exists($this->prefix . $key . 'list_detail_' . $value);
            if ($check_list) {
                $this->redis->del($this->prefix . $key . 'list_detail_' . $value);
            }
        }
    }

    /**
     * @param $key
     * @param $index
     * @param $data
     * Description:添加详情数据
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-11-06 14:45
     */
    public function setDataDetail($key,$index, $data)
    {
        $this->redis->hMset($this->prefix.$key . 'detail_' . $index, $data);
    }

    /**
     * @param $key
     * @param $index
     * @param $data
     * Description:添加列表显示数据
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-11-06 14:45
     */
    public function setListDetail($key,$index, $data)
    {
        $this->redis->hMset($this->prefix.$key .'list_detail_' . $index, $data);
    }

    /**
     * @param $key
     * @param int $page
     * @param int $length
     * @param string $orderBy
     * @param bool $detail
     * @return mixed
     * Description:分页获取数据
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-11-06 14:42
     */
    public function getList($key, $page = 1, $length = 10, $orderBy = 'asc',$detail=false)
    {
        $page = intval($page);
        $length = intval($length);
        $total = $this->getTotal($key);//总条数
        $total_pages = ceil($total / $length);//总页码
        $pageList['pages'] = $total_pages;//总页码
        $pageList['total'] = $total;//总条数
        $pageList['list'] = [];//数据列表
        if($total<=0){
            return $pageList;
        }
        if ($page > $total_pages) {
            $page = $total_pages;
        }
        $bPage = ($page - 1) * $length;//开始条数
        $end = ($bPage + $length) - 1;//结束条数
        if ($orderBy == 'desc') {
            $range = $this->redis->zRevRange($this->prefix . $key, $bPage, $end);//desc 从大到小
        } else {
            $range = $this->redis->zRange($this->prefix . $key, $bPage, $end);//asc 从小到大
        }
        if($detail) {
            foreach ($range as $k => $v) {
                $pageList['list'][$k] = $this->redis->hGetAll($this->prefix . 'list_detail_' . $v);
            }
        }else{
            $pageList['list']=$range;
        }
        return $pageList;
    }

    /**
     * @param $key
     * @param $index
     * @return mixed
     * Description:获取详情数据
     * User: sunnier
     * Email: xiaoyao_xiao@126.com
     * Date: 2019-11-06 14:46
     */
    public function getDataDetail($key,$index)
    {
        return $this->redis->hGetAll($this->prefix .$key. 'detail_' . $index);
    }

    /**
     * 获取排序数据总数量
     * @param $key
     * @return mixed
     */
    public function getTotal($key)
    {
        return $this->redis->zCard($this->prefix . $key);
    }

    public function __destruct()
    {
        $this->redis->close();
    }
}