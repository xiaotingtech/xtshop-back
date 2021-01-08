<?php
/**
 * Created by xtshop.
 * Description:购买商品的统计脚本
 * Author: sunnier
 * Email: xiaoyao_xiao@126.com
 * Date: 2020/7/21
 * Time: 20:03
 */
namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
class ProductBuyAnalysis  extends Command
{
    protected function configure()
    {
        $this->setName('product_buy_analysis')
            ->setDescription('购买商品的统计');
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return int|void|null
     */
    protected function execute(Input $input, Output $output)
    {
        $tag="product_buy_log";
        try {
            $cacheService = app('app\common\service\CacheService');
            $productRepository = app('app\common\repository\ProductRepository');
            $logNum = 0;
            $saveData = [];
            $errorNum = 0;
            $flag = false;
            $nowTime=time();
            while (true) {
                $dataResult = $cacheService->popList($tag);
                if ($dataResult['status']!=1) {
                    $output->writeln(date('Y-m-d H:i:s') . "查询数据结束！");
                    $flag = true;
                }
                $data=$dataResult['data'];
                $logNum++;
                if (!empty($data)) {
                    $data['create_time'] = $nowTime;
                    $data['update_time'] = $nowTime;
                    $saveData[] = $data;
                }
                if ($flag) {
                    if (!empty($saveData)) {
                        $result = $productRepository->saveData($saveData);
                        if ($result['status'] != 1) {
                            $output->writeln(date('Y-m-d H:i:s') . "执行出错！对应的错误信息是" . $result['msg']);
                            break;
                        } else {
                            break;
                        }
                    } else {
                        break;
                    }
                } else {
                    if ($logNum == 10) {
                        $result = $productRepository->saveData($saveData);
                        if ($result['status'] != 1) {
                            $errorNum++;
                            if ($errorNum > 20) {
                                $output->writeln(date('Y-m-d H:i:s') . "执行出错！对应的错误信息是" . $result['msg']);
                                break;
                            }
                            $logNum = 0;
                            $saveData = [];
                        } else {
                            $output->writeln($result['msg']);
                            $logNum = 0;
                            $saveData = [];
                        }
                    }
                }
            }
            $output->writeln(date('Y-m-d H:i:s') . "队列数据处理完成！");
            exit();
        }catch (\Exception $e){
            $output->writeln("执行出错！".$e->getMessage().'文件：'.$e->getFile().'行号：'.$e->getLine());
            exit();
        }
    }
}