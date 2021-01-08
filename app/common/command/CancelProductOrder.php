<?php
/**
 * Class cancel_order
 * @package app\common\command
 * Description:取消订单脚本
 * User: sunnier
 * Email: xiaoyao_xiao@126.com
 * Date: 2019-08-07
 * Time: 13:00
 */
namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
class CancelProductOrder  extends Command
{
	protected function configure()
	{
		$this->setName('cancel_product_order')
			->addArgument('time',Argument::OPTIONAL,'时间')
			->setDescription('取消多少小时内的未支付订单');
	}

	/**
	 * @param Input $input
	 * @param Output $output
	 * @return int|void|null
	 */
	protected function execute(Input $input, Output $output)
	{
        $time =$input->getArgument('time');
        if(empty($time)){
            $time=1;
        }
        $result=app('app\common\repository\ProductOrderRepository')->cancelOrder($time);
        if($result['status']!=1) {
            $output->writeln(date('Y-m-d H:i:s') . "执行取消订单出错！原因是" . $result['msg']);
        }
        $output->writeln(date('Y-m-d H:i:s') . "执行取消订单成功！");
	}
}