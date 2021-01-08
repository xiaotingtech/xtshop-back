<?php
/**
 * Created by xtshop
 * Class ProductInfo
 * Description:商品信息监听类
 * @package app\common\listener
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-07-02 17:29
 */
namespace app\common\listener;

use app\common\model\UserProductBrowse;
class ProductInfo extends Base
{
    public $userProductBrowse;

    public function __construct()
    {
        parent::__construct();
        $this->userProductBrowse = new UserProductBrowse();
    }

    public function handle($dataInfo=[])
    {
        if(empty($dataInfo['user'])){
           return;
        }
        if(empty($dataInfo['product'])){
            return;
        }
        $userInfo=$dataInfo['user'];
        $productInfo=$dataInfo['product'];
        //记录商品足迹
        $saveData=[
            'uid'=>$userInfo['id'],
            'product_id'=>$productInfo['id']
        ];
        if($logData=$this->userProductBrowse->field('id,status')->where($saveData)->find()){
            if($logData['status']==0) {
                $this->userProductBrowse->where('id', $logData['id'])
                    ->update(['status' => 1,'update_time'=>time()]);
            }
        }
        $this->userProductBrowse->save($saveData);
    }
}