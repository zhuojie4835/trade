<?php

namespace Cli\Controller;

use Think\Controller;

class IndexController extends Controller {
	#清空数据，方便测试
	public function clearData() {
		$redis = getRedis();
		$redis->flushdb();
		M('')->execute('TRUNCATE TABLE trade_recharge_admin');
		M('')->execute('TRUNCATE TABLE trade_product_order');
		M('')->execute('TRUNCATE TABLE trade_position');
		M('')->execute('TRUNCATE TABLE trade_follow');
		M('')->execute('TRUNCATE TABLE trade_deals');
		M('')->execute('TRUNCATE TABLE trade_customer');
	}

	#更新持仓
	public function updatePosition($uid='*') {
		$redis = getRedis();
		$model = D('Common/Position');
		$position_keys = $redis->keys('position:'.$uid.':*');
		
		foreach ($position_keys as $k=>$v) {
			$key_arr = explode(':', $v);
			$uid = $key_arr[1];
			$pid = $key_arr[2];
			$map = array('customer_id'=>$uid,'pid'=>$pid);
			$product_trade = $redis->hgetall('product_trade:'.$pid);
			$item = $redis->hgetall($v);
			
			if(!$position = $model->where($map)->find()) {
				$userinfo = $redis->hgetall('user:'.$uid);
				$item['customer_mobile'] = $userinfo['mobile'];
				$item['customer_name'] = $userinfo['name'];
				$item['customer_id'] = $userinfo['uid'];
				$item['agent_number'] = $userinfo['agent_number'];
				$item['last_time'] = time();
				$item['now_price'] = (float)$product_trade['now_price'];

				$model->add($item);
			} else {
				$map['id'] = $position['id'];
				$data = array(
					'now_price'=>(float)$product_trade['now_price'],
					'volume'=>$item['volume'],
					'average_price'=>$item['average_price'],
					'can_sell'=>$item['can_sell'],
					'last_time'=>time(),
					'status'=>$product_trade['status']
				);

				$model->where($map)->save($data);
			}
		}
	}

	public function test() {
		dump_log(time());
	}
}