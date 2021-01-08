<?php
/**
 * Created by xtshop
 * Class OrderManageRecord
 * Description:订单操作日志监听
 * @package app\common\listener
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-10-27 9:57
 */
namespace app\common\listener;

class OrderManageRecord extends Base
{
    public $orderManageRepository;

    public function __construct()
    {
        parent::__construct();

        $this->orderManageRepository=app('app\common\repository\OrderManageLogRepository');
    }

    public function handle($dataInfo=[])
    {
        //记录日志
        $this->orderManageRepository->saveLog($dataInfo);
    }
}