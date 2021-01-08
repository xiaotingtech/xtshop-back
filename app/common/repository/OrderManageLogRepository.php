<?php
namespace app\common\repository;

use app\common\model\OrderManageLog;
class OrderManageLogRepository extends BaseRepository
{
    //OrderManageLog的model
    protected $orderManageLogModel;

    public function __construct()
    {
        $this->orderManageLogModel = new OrderManageLog();
    }

    /**
     * @param array $dataInfo
     * @return array
     * Description:记录日志:
     * type:1：修改：2：发货：3：备注：4：关闭订单 5：确认收货
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-10-27 10:00
     */
    public function saveLog($dataInfo){
        try {
            $type = $dataInfo['type'];
            $userInfo = $dataInfo['user'];
            $uid = $userInfo['id'];
            $username = $userInfo['username'];
            $saveData = [];
            switch ($type) {
                case 1:
                    //修改
                    break;
                case 2:
                    //发货
                    $saveDataArr = $dataInfo['record_content'];
                    $orderId = $saveDataArr['order_id'];
                    $saveData = [
                        'type' => $type,
                        'uid' => $uid,
                        'username'=>$username,
                        'order_id' => $orderId,
                        'record_content' => json_encode($saveDataArr),
                    ];
                    break;
                case 3:
                    //备注
                    $saveDataArr = $dataInfo['order_info'];
                    $remarkData = $dataInfo['record_content'];
                    $remark = $remarkData['remark'];
                    $saveData = [
                        'type' => $type,
                        'uid' => $uid,
                        'username'=>$username,
                        'order_id' => $saveDataArr['id'],
                        'record_content' => json_encode($saveDataArr),
                        'remark'=>$remark
                    ];
                    break;
                case 4:
                    //关闭订单
                    $saveDataArr = $dataInfo['order_info'];
                    $saveData = [
                        'type' => $type,
                        'uid' => $uid,
                        'username'=>$username,
                        'order_id' => $saveDataArr['id'],
                        'record_content' => json_encode($saveDataArr),
                    ];
                    break;
                case 5:
                    //确认收货
                    $saveDataArr = $dataInfo['order_info'];
                    $saveData = [
                        'type' => $type,
                        'uid' => $uid,
                        'username'=>$username,
                        'order_id' => $saveDataArr['id'],
                        'record_content' => json_encode($saveDataArr),
                    ];
                    break;
            }
            if (!empty($saveData)) {
                $this->orderManageLogModel->save($saveData);
            }
        }catch (\Exception $e){
            //不返回
            \think\facade\Log::error($e->getTraceAsString());
        }
    }
}