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

	#日结时处理挂单
	public function cleargd() {
		$redis = getRedis();
		$last_handle_gid = $redis->get('last_handle_gid') ? $redis->get('last_handle_gid') : 1;
		$last_gid = $redis->get('next_gid');
		$gids = $redis->keys('gd_record:*');
		foreach ($gids as $k=> $v) {
			$gid_arr = explode(':', $v);
			$gid = end($gid_arr);
			if($gid>=$last_handle_gid && $gid<=$last_gid) {
				$gd_info = $redis->hgetall('gd_record:'.$gid);
				if(in_array($gd_info['gd_status'],array(1,2,4))) {
					if($gd_info['gd_status'] == 2) {
						$new_gd_status = 6;//部分撤单
						$redis->hmset('gd_record:'.$gid,array('gd_status'=>$new_gd_status,'cancel_time'=>time(),'volume'=>0));//修改状态、撤单时间
					} 
					if($gd_info['direct'] == 's') {
						if($gd_info['gd_status'] != 4) {
							$redis->zrem('gid_out_by_price:'.$gd_info['pid'].':'.$gd_info['price'],$gid);//删除有序集合中的gid
							$gd_detail_key = 'gd_out_price_detail:'.$gd_info['pid'].':'.$gd_info['price'];
							$gd_volume_by_price = $redis->hget($gd_detail_key,'volume');
							if($gd_info['volume']>=$gd_volume_by_price) {
								$redis->del($gd_detail_key);
								$redis->srem('gd_out_price:'.$gd_info['pid'],$gd_info['price']);
							} else {
								$redis->hincrby($gd_detail_key,'volume',-$gd_info['volume']);
								$redis->hincrby($gd_detail_key,'count',-1);
							}
							$this->generatePosition($gd_info['pid'],$gd_info['uid'],$gd_info['volume'],0,'gd_out_cancel');//修改持仓
						}

						if($gd_info['gd_status'] == 1 || $gd_info['gd_status'] == 4) {
							$redis->del('gd_record:'.$gid);
							$redis->zrem('gid_by_person:'.$gd_info['uid'],$gid);
						}
					} else {
						if($gd_info['gd_status'] != 4) {
							$redis->zrem('gid_in_by_price:'.$gd_info['pid'].':'.$gd_info['price'],$gid);//删除有序集合中的gid
							$gd_detail_key = 'gd_in_price_detail:'.$gd_info['pid'].':'.$gd_info['price'];
							$gd_volume_by_price = $redis->hget($gd_detail_key,'volume');
							if($gd_info['volume']>=$gd_volume_by_price) {
								$redis->del($gd_detail_key);
								$redis->srem('gd_in_price:'.$gd_info['pid'],$gd_info['price']);
							} else {
								$redis->hincrby($gd_detail_key,'volume',-$gd_info['volume']);
								$redis->hincrby($gd_detail_key,'count',-1);
							}
							$user_money = $redis->hmget('user:'.$gd_info['uid'],'free_money','freeze_money');
							$gd_freeze_money = getFloat($gd_info['volume']*$gd_info['price']);
							$new_free_money = getFloat($user_money[0]+$gd_freeze_money);
							$new_freeze_money = getFloat($user_money[1]-$gd_freeze_money);
							$redis->hmset('user:'.$gd_info['uid'],array('free_money'=>$new_free_money,'freeze_money'=>$new_freeze_money));
						}
						
						if($gd_info['gd_status'] == 1 || $gd_info['gd_status'] == 4) {
							$redis->del('gd_record:'.$gid);
							$redis->zrem('gid_by_person:'.$gd_info['uid'],$gid);
						}
					}
				}
			}
		}

		$redis->set('last_handle_gid',$last_gid);
	}

	public function test() {
		dump_log(time());
	}

	#重新均价
	#持仓增加 新成本价=持仓成本价*持仓数+新买入数量*新买入价格)/(已持仓数量+新买入数量)
	#持仓减少 新成本价=平仓价-(平仓价-旧平均价)*平仓前数量/(平仓前数量-已平仓数量)
	protected function generatePosition($pid,$uid,$volume,$price,$scene='subscribe') {
		$redis = getRedis();
		$position_key = 'position:'.$uid.':'.$pid;
		$product_trade_info = $redis->hgetall('product_trade:'.$pid);
		if((!$position = $redis->hgetall($position_key)) && $volume>0) {//持仓不能为负
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
				$new_average_price = ($position['volume']*$position['average_price']+$volume*$price)/($position['volume']+$volume);
				
				$redis->hset($position_key,'average_price',getFloat($new_average_price));
				$redis->hset($position_key,'status',$product_trade_info['status']);
			} elseif($scene == 'yj_in_gd') {//应价买入挂单方
				$redis->hincrby($position_key,'volume',-$volume);
				if($position['volume']-$volume>0) {
					$new_average_price = $price-($price-$position['average_price'])*$position['volume']/($position['volume']-$volume);
					$redis->hset($position_key,'average_price',getFloat($new_average_price));
				} else {
					$new_average_price = 0;//全部卖出
					$redis->del($position_key);
					D('Common/Position')->where(array('pid'=>$pid,'customer_id'=>$uid))->delete();//同时删除mysql记录
				}
			} elseif($scene == 'yj_in_yj') {//应价买入应价方
				$redis->hincrby($position_key,'volume',$volume);
				$redis->hincrby($position_key,'can_sell',$volume);
				$new_average_price = ($position['volume']*$position['average_price']+$volume*$price)/($position['volume']+$volume);
				
				$redis->hset($position_key,'average_price',getFloat($new_average_price));
				$redis->hset($position_key,'status',$product_trade_info['status']);
			} elseif($scene == 'yj_out_gd') {//应价卖出挂单方
				$redis->hincrby($position_key,'can_sell',$volume);
				$redis->hincrby($position_key,'volume',$volume);
				$new_average_price = ($position['volume']*$position['average_price']+$volume*$price)/($position['volume']+$volume);
				
				$redis->hset($position_key,'average_price',getFloat($new_average_price));
				$redis->hset($position_key,'status',$product_trade_info['status']);
			} elseif($scene == 'yj_out_yj') {//应价卖出应价方
				$redis->hincrby($position_key,'can_sell',-$volume);
				$redis->hincrby($position_key,'volume',-$volume);
				if($position['volume']-$volume>0) {
					$new_average_price = $price-($price-$position['average_price'])*$position['volume']/($position['volume']-$volume);
					
					$redis->hset($position_key,'average_price',getFloat($new_average_price));
					$redis->hset($position_key,'status',$product_trade_info['status']);
				} else {
					$new_average_price = 0;//全部卖出
					$redis->del($position_key);
					D('Common/Position')->where(array('pid'=>$pid,'customer_id'=>$uid))->delete();//同时删除mysql记录
				}
			} elseif ($scene == 'gd_out_cancel') {//挂单卖出撤销
				$redis->hincrby($position_key,'can_sell',$volume);
				$new_average_price = $position['average_price'];
				
				$redis->hset($position_key,'average_price',getFloat($new_average_price));
				$redis->hset($position_key,'status',$product_trade_info['status']);
			}
		}
	}
}