<?php
namespace app\admin\controller;
/**
 * Created by xtshop.
 * Description:首页
 * User: sunnier
 * Email: xiaoyao_xiao@126.com
 * Date: 2020-06-14
 * Time: 22:13
 */
use app\common\model\Product;
use app\common\model\Order;
use app\common\model\User;
use app\common\model\UserMoneyLog;
class Index extends AuthBase
{
    public function index()
    {
        $productModel=new Product();
        $productOrderModel=new Order();
        $userModel=new User();
        $userMoneyLogModel=new UserMoneyLog();
        $productNum=$productModel->count();
        $productPublishNum=$productModel->where('publish_status',1)->count();
        $productWaitNum=$productModel->where('publish_status',0)->count();
        $productNervousNum=$productModel->where('stock','<',5)->count();
        $productOrderNum=$productOrderModel->count();
        $todayStartTime=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $yesterdayStartTime=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        $monthStartTime=mktime(0,0,0,date('m'),1,date('Y'));
        $todayOrderNum=$productOrderModel
            ->where('create_time','>',$todayStartTime-1)
            ->count();
        $userNum=$userModel->count();
        $userTodayNum=$userModel->where('create_time','>',$todayStartTime-1)->count();
        $userYesterdayNum=$userModel->where('create_time','>',$yesterdayStartTime-1)
            ->where('create_time','<',$todayStartTime)
            ->count();
        $userMonthNum=$userModel->where('create_time','>',$monthStartTime-1)->count();
        $allPrice=$userMoneyLogModel->sum('price');
        $todayAllPrice=$userMoneyLogModel
            ->where('create_time','>',$todayStartTime-1)
            ->sum('price');
        return show(1,'获取成功！',[
            'product_all_num'=>$productNum,
            'product_publish_num'=>$productPublishNum,
            'product_wait_num'=>$productWaitNum,
            'product_nervous_num'=>$productNervousNum,
            'product_order_num'=>$productOrderNum,
            'today_order_num'=>$todayOrderNum,
            'today_user_num'=>$userTodayNum,
            'yesterday_user_num'=>$userYesterdayNum,
            'month_user_num'=>$userMonthNum,
            'user_num'=>$userNum,
            'all_price'=>$allPrice,
            'today_all_price'=>$todayAllPrice,
            'order_unpay_num'=>1,
            'order_finish_num'=>3,
            'order_unconfirm_num'=>11,
            'order_wait_num'=>6,
            'product_lack_num'=>8,
            'order_deal_num'=>11,
            'order_send_num'=>10,
            'order_refund_num'=>3
        ]);
    }

    /**
     * @return \think\response\Json
     * Description:获取订单信息
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-11-05 12:06
     */
    public function analysisOrder(){
        $monthStartTime=mktime(0,0,0,date('m'),1,date('Y'));
        $startTime = input('post.start_time',$monthStartTime,'int');
        $endTime = input('post.end_time',time(),'int');
        $data=[
            ['date'=>'2018-11-01', 'order_count'=> 10, 'order_amount'=> 1093],
            ['date'=>'2018-11-02', 'order_count'=> 20, 'order_amount'=> 2230],
            ['date'=>'2018-11-03', 'order_count'=> 33, 'order_amount'=> 3623],
            ['date'=>'2018-11-04', 'order_count'=> 50, 'order_amount'=> 6423],
            ['date'=>'2018-11-05', 'order_count'=> 80, 'order_amount'=> 8492],
            ['date'=>'2018-11-06', 'order_count'=> 60, 'order_amount'=> 6293],
            ['date'=>'2018-11-07', 'order_count'=> 20, 'order_amount'=> 2293],
            ['date'=>'2018-11-08', 'order_count'=> 60, 'order_amount'=> 6293],
            ['date'=>'2018-11-09', 'order_count'=> 50, 'order_amount'=> 5293],
            ['date'=>'2018-11-10', 'order_count'=> 30, 'order_amount'=> 3293],
            ['date'=>'2018-11-11', 'order_count'=> 20, 'order_amount'=> 2293],
            ['date'=>'2018-11-12', 'order_count'=> 80, 'order_amount'=> 8293],
            ['date'=>'2018-11-13', 'order_count'=> 100, 'order_amount'=> 10293],
            ['date'=>'2018-11-14', 'order_count'=> 10, 'order_amount'=> 1293],
            ['date'=>'2018-11-15', 'order_count'=> 40, 'order_amount'=> 4293]
        ];
        $resultData=[
            'columns'=>['date', 'order_count','order_amount'],
            'list'=>$data,
            'order_data'=>[
                'month_order_num'=>10000,
                'month_last_change'=>6,
                'week_order_num'=>1000,
                'week_last_change'=>8,
                'month_sale_num'=>2000,
                'month_last_sale_change'=>3,
                'week_sale_num'=>1000,
                'week_last_sale_change'=>6,
            ]
        ];
        return show(1,'获取成功',$resultData);
    }
}