<?php
/**
 * Created by xtshop
 * Class UserMoneyLogRepository
 * Description:流水表数据处理层
 * @package app\common\repository
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-07-02 16:39
 */
namespace app\common\repository;

use app\common\model\UserMoneyLog;
class UserMoneyLogRepository extends BaseRepository
{
    //user_money_log的model
    protected $userMoneyLog;
    public function __construct()
    {
        $this->userMoneyLog=new UserMoneyLog();
    }

    /**
     * @param $logData
     * @return bool
     * Description:保存数据
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-02 16:41
     */
    public function saveData($logData){
        if(!$this->userMoneyLog->save($logData)){
            return false;
        }
        return true;
    }
}