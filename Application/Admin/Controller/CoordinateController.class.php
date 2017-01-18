<?php
namespace Admin\Controller;

use Workerman\Autoloader;
use Workerman\Worker;
use Workerman\Lib\Timer;


class CoordinateController {
	public function coordinateRedis() {
		if(!IS_CLI){
            die("access illegal");
        }
        spl_autoload_register('\Workerman\Autoloader::loadByNamespace');
        define('MAX_REQUEST', 1000);
		Worker::$daemonize = true;
		$task = new Worker();
		$task->count = 1;
		$task->onWorkerStart = function($task)
		{
			$time_interval = 5;
			Timer::add($time_interval, function()
			{
				try {
					$redis = getRedis();
					//同步流水
					$addList1 = array();
					for($i=0; $i<5000; $i++) {
						if($flow = $redis->rpop('follow')) {
							$addList1[] = json_decode($flow,true);
						} else {
							break;
						}
					}
					D('Common/Follow')->addAll($addList1);
					
					//同步认购
					$addList2 = array();
					for($i=0; $i<1000; $i++) {
						if($subscribe = $redis->rpop('subscribe_record')) {
							$subscribe = json_decode($subscribe,true);
							$customer = D('Common/Customer')->find($subscribe['customer_id']);
							$product = D('Common/Product')->find($subscribe['pid']);
							$addList2[] = array(
								'operator_number'=>$customer['operator_number'],
								'customer_id'=>$customer['id'],
								'agent_number'=>$customer['agent_number'],
								'customer_type'=>$customer['user_type'],
								'customer_name'=>$customer['name'],
								'member_agent_number'=>$customer['agent_member_number'],
								'customer_mobile'=>$customer['login_name'],
								'product_number'=>$product['number'],
								'product_name'=>$product['product_name'],
								'pid'=>$product['id'],
								'short_name'=>$product['short_name'],
								'price'=>$subscribe['price'],
								'trade_money'=>getFloat($subscribe['price']*$subscribe['volume']),
								'volume'=>$subscribe['volume'],
								'create_time'=>$subscribe['create_time'],
							);
						} else {
							break;
						}
					}
					D('Common/ProductOrder')->addAll($addList2);
				
					//同步成交
					$addList3 = array();
					for($i=0; $i<5000; $i++) {
						if($deals = $redis->rpop('deals')) {
							$item = json_decode($deals,true);
							$userinfo = $redis->hgetall('user:'.$item['customer_id']);
							$item['agent_number'] = $userinfo['agent_number'];
							$item['user_type'] = $userinfo['user_type'];
							$item['operator_number'] = $userinfo['operator_number'];
							$item['agent_member_number'] = $userinfo['agent_member_number'];
							$addList3[] = $item;
						} else {
							break;
						}
					}
					M('deals','trade_')->addAll($addList3);
				} catch (\Exception $e) {
					dump_log($e->getMessage());
				}
				
			});
		};

		Worker::runAll();
	}
}