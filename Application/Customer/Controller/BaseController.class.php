<?php
namespace Customer\Controller;

use Think\Controller;
use Predis\Autoloader;  
use Predis\Client; 

class BaseController extends Controller {
	public function isLogin() {
		return session('userinfo');
	}
	
	public function getRedis() {
		Autoloader::register();
		return new Client();
	}

	#重新均价
	protected function generatePosition($pid,$uid,$volume,$price,$scene='subscribe') {
		$redis = getRedis();
		$position_key = 'position:'.$uid.':'.$pid;
		// dump_log($position_key);dump_log($volume);
		if((!$position = $redis->hgetall($position_key)) && $volume>0) {//持仓不能为负
			$product_trade_info = $redis->hgetall('product_trade:'.$pid);
			$position_info = array(
				'pid'=>$pid,
				'short_name'=>$product_trade_info['short_name'],
				'product_number'=>$product_trade_info['product_number'],
				'status'=>$product_trade_info['status'],
				'volume'=>$volume,
				'can_sell'=>$volume,
				'average_price'=>getFloat($price),
				'status'=>$product_trade_info['status'],
			);
			$redis->hmset($position_key,$position_info);
		} else {
			if($scene == 'subscribe') {//认购
				$redis->hincrby($position_key,'can_sell',$volume);
				$redis->hincrby($position_key,'volume',$volume);
				//新成本价=持仓成本价*持仓数+新买入数量*新买入价格)/(已持仓数量+新买入数量)
				$new_average_price = ($position['volume']*$position['average_price']+$volume*$price)/($position['volume']+$volume);
			} elseif($scene == 'yj_in_gd') {//应价买入挂单方
				$redis->hincrby($position_key,'volume',-$volume);
				//新成本价=平仓价-(平仓价-旧平均价)*平仓前数量/(平仓前数量-已平仓数量)
				if($position['volume']-$volume>0) {
					$new_average_price = $price-($price-$position['average_price'])*$position['volume']/($position['volume']-$volume);
				} else {
					$new_average_price = 0;//全部卖出
				}
			} elseif($scene == 'yj_in_yj') {//应价买入应价方
				$redis->hincrby($position_key,'volume',$volume);
				$redis->hincrby($position_key,'can_sell',$volume);
				//新成本价=持仓成本价*持仓数+新买入数量*新买入价格)/(已持仓数量+新买入数量)
				$new_average_price = ($position['volume']*$position['average_price']+$volume*$price)/($position['volume']+$volume);
				// dump_log($new_average_price);
			} elseif($scene == 'yj_out_gd') {//应价卖出挂单方
				$redis->hincrby($position_key,'can_sell',$volume);
				$redis->hincrby($position_key,'volume',$volume);
				//新成本价=持仓成本价*持仓数+新买入数量*新买入价格)/(已持仓数量+新买入数量)
				$new_average_price = ($position['volume']*$position['average_price']+$volume*$price)/($position['volume']+$volume);
			} elseif($scene == 'yj_out_yj') {//应价卖出应价方
				$redis->hincrby($position_key,'can_sell',-$volume);
				$redis->hincrby($position_key,'volume',-$volume);
				//新成本价=平仓价-(平仓价-旧平均价)*平仓前数量/(平仓前数量-已平仓数量)
				if($position['volume']-$volume>0) {
					$new_average_price = $price-($price-$position['average_price'])*$position['volume']/($position['volume']-$volume);
				} else {
					$new_average_price = 0;//全部卖出
				}
			}

			$redis->hset($position_key,'average_price',getFloat($new_average_price));
		}
	}
}