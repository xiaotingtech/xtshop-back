<?php
/**
 * Created by xtshop.
 * Description:商品购买监听器
 * Author: sunnier
 * Email: xiaoyao_xiao@126.com
 * Date: 2020/7/21
 * Time: 19:57
 */
namespace app\common\listener;

class ProductBuy extends Base
{
    public $cacheService;

    public function __construct()
    {
        parent::__construct();
        $this->cacheService =app('app\common\service\CacheService');
    }

    public function handle($dataInfo=[])
    {
        if(empty($dataInfo['uid'])){
            return;
        }
        if(empty($dataInfo['id'])){
            return;
        }
        $saveData=[
            'uid'=>$dataInfo['uid'],
            'order_id'=>$dataInfo['id']
        ];
        $this->cacheService->pushList('product_buy_log',$saveData);
    }
}