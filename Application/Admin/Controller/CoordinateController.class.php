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
					if($addList1) {
						D('Common/Follow')->addAll($addList1);
					}
					
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
					if($addList1) {
						D('Common/ProductOrder')->addAll($addList2);
					}
				
					//同步成交
					$addList3 = array();
					$p1 = $p2 = $a1 = $a2 = $a3 = $j = 0;//直接推荐人、间接推荐人、代理、高级代理、会员、交易所分佣金额
					//各级分佣比例
					$p1_bl = $redis->hget('settings','p1') ? $redis->hget('settings','p1') : C('p1');
					$p2_bl = $redis->hget('settings','p2') ? $redis->hget('settings','p2') : C('p2');
					$a1_bl = $redis->hget('settings','a1') ? $redis->hget('settings','a1') : C('a1');
					$a2_bl = $redis->hget('settings','a2') ? $redis->hget('settings','a2') : C('a2');
					$a3_bl = $redis->hget('settings','a3') ? $redis->hget('settings','a3') : C('a3');
					$fee_percent = $redis->hget('settings','trade_fee') ? $redis->hget('settings','trade_fee') : 0.01;//交易手续费比例

					for($i=0; $i<5000; $i++) {
						if($deals = $redis->rpop('deals')) {
							$item = json_decode($deals,true);

							$userinfo = $redis->hgetall('user:'.$item['customer_id']);
							$item['agent_number'] = $userinfo['agent_number'];
							$item['user_type'] = $userinfo['user_type'];
							$item['operator_number'] = $userinfo['operator_number'];
							$item['agent_member_number'] = $userinfo['agent_member_number'];
							$addList3[] = $item;

							$save_data = array();
							$save_data['customer_id'] = $userinfo['uid'];
							$save_data['customer_name'] = $userinfo['name'];
							$save_data['customer_mobile'] = $userinfo['mobile'];
							$save_data['user_type'] = $userinfo['user_type'];
							$save_data['agent_number'] = $userinfo['agent_number'];
							$save_data['agent_member_number'] = $userinfo['agent_member_number'];
							$save_data['agent_number'] = $userinfo['agent_number'];
							$save_data['pid'] = $item['pid'];
							$save_data['product_number'] = $item['product_number'];
							$save_data['short_name'] = $item['short_name'];
							$save_data['gid'] = $item['gid'];
							$save_data['create_time'] = time();
							$save_data['fee_percent'] = $fee_percent;

							if($userinfo['parent1']) {
								$p1 = $item['trade_fee']*$p1_bl;
								$save_data['operator_number'] = $userinfo['operator_number'];
								$save_data['fee'] = $p1;
								M('commission1','trade_')->add($save_data);
							}
							if($userinfo['parent2']) {
								$p2 = $item['trade_fee']*$p2_bl-$p1;
								$save_data['fee'] = $p2;
								$save_data['operator_number'] = $userinfo['operator_number'];
								M('commission1','trade_')->add($save_data);
							}
							if($userinfo['agent_number']) {
								$a1 = $item['trade_fee']*$a1_bl-$p1-$p2;
								$save_data['fee'] = $a1;
								$save_data['ss_agent'] = $userinfo['agent_number'];
								M('commission2','trade_')->add($save_data);
							}
							if($userinfo['agent2'] && ($userinfo['agent_number'] != $userinfo['agent2'])) {
								$a2 = $item['trade_fee']*$a2_bl-$p1-$p2-$a1;
								$save_data['fee'] = $a2;
								$save_data['ss_agent'] = $userinfo['agent2'];
								M('commission2','trade_')->add($save_data);
							}
							if($userinfo['agent_member_number'] && ($userinfo['agent_number'] != $userinfo['agent_member_number'])) {
								$a3 = $item['trade_fee']*$a3_bl-$p1-$p2-$a1-$a2;
								$save_data['fee'] = $a3;
								$save_data['ss_agent'] = $userinfo['agent_member_number'];
								M('commission2','trade_')->add($save_data);
							}
							$j = $item['trade_fee']-$p1-$p2-$a1-$a2-$a3;
							$save_data['fee'] = $j;
							M('commission3','trade_')->add($save_data);
							dump_log($p1);dump_log($p2);dump_log($a1);dump_log($a2);dump_log($a3);dump_log($j);
						} else {
							break;
						}
					}
					if($addList3) {
						M('deals','trade_')->addAll($addList3);
					}
				} catch (\Exception $e) {
					dump_log($e->getMessage());
				}
				
			});
		};

		Worker::runAll();
	}
}