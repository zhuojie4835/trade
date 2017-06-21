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
		for($gid=$last_handle_gid;$gid<=$last_gid;$gid++) {
			$gid_key = 'gd_record:'.$gid;
			$gd_info = $redis->hgetall($gid_key);
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
			if ($scene == 'gd_out_cancel') {//挂单卖出撤销
				$redis->hincrby($position_key,'can_sell',$volume);
				$new_average_price = $position['average_price'];
				
				$redis->hset($position_key,'average_price',getFloat($new_average_price));
				$redis->hset($position_key,'status',$product_trade_info['status']);
			}
		}
	}

	#日结生成行情
	public function candlestick() {
		dump_log('candlestick start...');
		$redis = getRedis();
		if($pids = D('Common/Product')->field('id')->where(array('status'=>3))->select()) {
			$next_cid = $redis->incrby('next_cid',1);
			foreach ($pids as $k=>$v) {
				$product_trade = $redis->hgetall('product_trade:'.$v['id']);
				if($product_trade['status'] == 3) {
					$data = array(
						'pid'=>$v['id'],
						'product_number'=>$product_trade['product_number'],
						'short_name'=>$product_trade['short_name'],
						'low_price'=>$product_trade['low_price'],
						'high_price'=>$product_trade['high_price'],
						'open_price'=>$product_trade['open_price'],
						'close_price'=>$product_trade['now_price'],
						'volume'=>$product_trade['volume'],
						'amount'=>$product_trade['amount'],
						'status'=>$product_trade['status'],
						'time'=>time()
					);
					
					$redis->hmset('candlestick:'.$next_cid,$data);
					$redis->zadd('candlestick_set:'.$v['id'],date('Ymd',time()),$next_cid);
				}
			}
		}

		dump_log('candlestick end');
	}

	#开盘前重置行情
	public function reset() {
		dump_log('reset start...');
		$redis = getRedis();
		if($pids = D('Common/Product')->field('id')->where(array('status'=>3))->select()) {
			foreach ($pids as $k=>$v) {
				$product_trade = $redis->hgetall('product_trade:'.$v['id']);
				if($product_trade['status'] == 3) {
					$price_limit = 0.1;
					$min_price = getFloat((1-$price_limit)*$product_trade['now_price']);
					$max_price = getFloat((1+$price_limit)*$product_trade['now_price']);
					$data = array(
						'low_price'=>'--',
						'high_price'=>'--',
						'open_price'=>'--',
						'close_price'=>$product_trade['now_price'],
						'min_price'=>$min_price,
						'max_price'=>$max_price,
						'volume'=>0,
						'amount'=>0
					);
					
					$redis->hmset('product_trade:'.$v['id'],$data);
				}
			}
		}

		dump_log('reset end');
	}

	public function fixclassnu() {
		echo 222;
	}


	public function test4() {
		$mcd_model = M('MemberClassnuData','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		$mc_model = M('MemberClassnu','t_');
		$onc_model = M('OldNewClass','t_');
		// $mcd_model = M('MemberClassnuData','t_');
		// $mc_model = M('MemberClassnu','t_');
		// $onc_model = M('OldNewClass','t_');
		
		$where = 'id in(117,121,37)';
		$old_classnuids = $mc_model->where($where)->getField('id',true);
		$log_name = '/nsql_'.date('m_d_H_i');
		// error_log('mch:data = '.M()->getLastSql()."\r\n",3,APP_PATH.'/sql.log');
		// var_dump($old_classnuids);die;
		if($old_classnuids) {
			foreach ($old_classnuids as $old_classnuid) {
				$all_mcd = $mcd_model->where(array('classnuid'=>$old_classnuid))->select();//查询所有老班号下的学员
				error_log('查询所有老班号下的学员 '.count($all_mcd)."\r\n",3,realpath(APP_PATH).$log_name.'.log');
				$i = 0;
				$data = array();
				foreach($all_mcd as $k=>$v) {
					$new_name = $onc_model->where(array('old_id'=>$v['classnuid']))->select();//查询新班号的名称
					error_log('查询新班号信息 '.json_encode($new_name)."\r\n",3,realpath(APP_PATH).$log_name.'.log');
					if($new_name) {
						foreach ($new_name as $k1=> $v1) {
							//根据班号名称判断新班号是否存在以及数据是否重复
							if(($new_classnuid = $v1['new_id']) && !$mcd_model->where(array('classnuid'=>$new_classnuid,'userid'=>$v['userid']))->find()) {
								
								$i++;
								$item = $v;
								unset($item['id']);
								$item['crttime'] = time();
								$item['classnuid'] = $new_classnuid;
								// var_dump($item);die;
								$mcd_model->add($item);
								error_log('userid='.$v['userid'].'&phone='.$v['username']."操作成功 \r\n",3,realpath(APP_PATH).$log_name.'.log');
							}
						}
					}
				}
			}
		}

		var_dump($i);
	}

	public function test6() {
		$mcd_model = M('MemberClassnuData','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/test#utf8');
		$mcd_online_model = M('MemberClassnuData','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		$mc_model = M('MemberClassnu','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/test#utf8');
		$onc_model = M('OldNewClass','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/test#utf8');
		$xy_online_model = M('Xueyuan','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		// $mcd_model = M('MemberClassnuData','t_');
		// $mc_model = M('MemberClassnu','t_');
		// $onc_model = M('OldNewClass','t_');
		
		$where = 'id>21791';
		$new_mcd_data = $mcd_online_model->where($where)->group('userid')->order('userid asc')->select();//查询上线后生成的班内学员表数据，根据userid分组
		error_log(date('m/d H:i:s').' 所有用户数量 '.count($new_mcd_data).' 用户详情 '.json_encode($new_mcd_data)."\r\n",3,APP_PATH.'/sql_new_data_import.log');
		foreach ($new_mcd_data as $k=>$v) {
			$id = $v['id'];
			error_log(date('m/d H:i:s').' userid='.$v['userid']." start \r\n",3,APP_PATH.'/sql_new_data_import.log');
			if(preg_match('/^1[34578]\d{9}$/',$v['username'])) {
				$xy_arr = $xy_online_model->where(array('xy_phone'=>$v['username']))->select();//查询userid对应的学员表的班型信息
				if($xy_arr) {
					foreach ($xy_arr as $key=>$value) {
						$classnuids = $mc_model->where(array('classtypeid'=>$value['ctid']))->select();//根据班型查询到班号信息
						if($classnuids) {
							foreach ($classnuids as $k2=>$v2) {
								if(!$mcd_model->where(array('username'=>$v['username'],'classnuid'=>$v2['id']))->find()) {
									$item = $v;
									$item['crttime'] = time();
									$item['classnuid'] = $v2['id'];
									unset($item['id']);
									// var_dump($item);die;
									// $mcd_model->add($item);//插入到班内学员
									error_log(date('m/d H:i:s').' userid='.$v['userid'].'phone='.$v['username'].'插入班号'.$v2['id']."成功 ".json_decode($item)."\r\n",3,APP_PATH.'/sql_new_data_import.log');
								}

							}
						} else {
							error_log(date('m/d H:i:s').' notice，班型没有对应的班号：'.$value['ctid']."\r\n",3,APP_PATH.'/sql_new_data_import.log');
						}
					}

				} else {
					error_log(date('m/d H:i:s').' error，学员表没有信息：'.$v['username']."\r\n",3,APP_PATH.'/sql_new_data_import.log');
				}
			} else {
				error_log(date('m/d H:i:s').' error，不是手机号码：'.$v['username']."\r\n",3,APP_PATH.'/sql_new_data_import.log');
			}
			
		}

		
	}

	public function test7() {
		$mcd_model = M('MemberClassnuData','t_');
		$mc_model = M('MemberClassnu','t_');

		// $mcd_model = M('MemberClassnuData','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		// $mc_model = M('MemberClassnu','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		
		$sql = "SELECT id,`name` FROM t_member_classnu GROUP BY `name` HAVING count(1)>1";
		$all_repeat_classnu = $mc_model->query($sql);//所有重复班号
		
		foreach ($all_repeat_classnu as $k=>$v) {
			$classnuids = $mc_model->where(array('name'=>$v['name']))->getField('id',true);//班号名称相同的所有班号id
			$baoliu_classnuid = $v['id'];//保留的班号id
			
			foreach ($classnuids as $classnuid) {
				if($classnuid != $baoliu_classnuid) {
					error_log('classnuid='.$baoliu_classnuid."开始\r\n",3,realpath(APP_PATH).'/asql_'.date('m_d_H_i').'log');
					$mcd_model->where(array('classnuid'=>$classnuid))->save(array('classnuid'=>$baoliu_classnuid));
					error_log('classnuid='.$baoliu_classnuid."结束\r\n",3,realpath(APP_PATH).'/asql_'.date('m_d_H_i').'log');
				}
				
			}
			// var_dump($baoliu_classnuid);die;
		}
		
	}
}