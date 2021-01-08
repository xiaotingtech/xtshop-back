<?php
/**
 * Created by xtshop
 * Class UserMoneyLogRepository
 * Description:经验表数据处理
 * @package app\common\repository
 * Author:sunnier
 * Email:xiaoyao_xiao@126.com
 * 2020-07-02 22:39
 */
namespace app\common\repository;

use app\common\model\UserScoreLog;
class UserScoreLogRepository extends BaseRepository
{
    //user_score_log的model
    protected $userScoreLog;
    public function __construct()
    {
        $this->userScoreLog=new UserScoreLog();
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
        if(!$this->userScoreLog->save($logData)){
            return false;
        }
        return true;
    }
}