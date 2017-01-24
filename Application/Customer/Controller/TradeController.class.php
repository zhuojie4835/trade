<?php
namespace Customer\Controller;


class TradeController extends BaseController {
	private $price;
	private $volume;
	private $pid;
	private $direct;
	private $product;
	private $customer;
	private $trade_money;
	
	public function __construct() {
    	parent::__construct();
    	if(!$this->isLogin()) {
    		$this->redirect('Index/login');
    	}
    	$redis = getRedis();
    	$userinfo_in_redis = $redis->hgetall("user:".session('uid'));
    	$this->_userinfo = $userinfo_in_redis;
    }
	
	#挂单
	public function gd() {
		try {
			$this->gdCheck();
			$redis = getRedis();
			$gid = $redis->incrby('next_gid',1);
			$gd_info = array(
				'gid'=>$gid,
				'uid'=>$this->_userinfo['uid'],
				'mobile'=>$this->_userinfo['mobile'],
				'name'=>$this->_userinfo['name'],
				'pid'=>$this->pid,
				'product_number'=>$this->product['product_number'],
				'price'=>$this->price,
				'volume'=>$this->volume,
				'volume_p'=>$this->volume,
				'create_time'=>time(),
				'status'=>$this->product['status'],
				'gd_status'=>1,
				'direct'=>$this->direct,
				'short_name'=>$this->product['short_name'],
			);
			
			$this->direct == 's' && $gd_flag = 'out';
			$this->direct == 'b' && $gd_flag = 'in';

			$redis->hmset('gd_record:'.$gid,$gd_info);//挂单记录
			$redis->sadd('gd_'.$gd_flag.'_price:'.$this->pid,$this->price);//商品挂单买入价格集合
			$redis->zadd('gid_by_person:'.$this->_userinfo['uid'],time(),$gid);//每个客户挂单gid集合
			$redis->zadd('gid_'.$gd_flag.'_by_price:'.$this->pid.':'.$this->price,time(),$gid);//每口价格gid集合
			$gd_price_detail_key = 'gd_'.$gd_flag.'_price_detail:'.$this->pid.':'.$this->price;//更新每口价格的数量、笔数
			if(!$redis->exists($gd_price_detail_key)) {
				$gd_price_detail_info = array(
					'pid'=>$this->pid,
					'price'=>$this->price,
					'volume'=>$this->volume,
					'count'=>1,
					'status'=>$this->product['status'],
					'direct'=>$this->direct,
					'short_name'=>$this->product['short_name'],
					'product_number'=>$this->product['product_number'],
				);
				
				$redis->hmset($gd_price_detail_key,$gd_price_detail_info);
			} else {
				$redis->hincrby($gd_price_detail_key,'volume',$this->volume);
				$redis->hincrby($gd_price_detail_key,'count',1);
			}
			if($this->direct == 'b') {
				//冻结资金
				$redis->hmset('user:'.$this->_userinfo['uid'],array(
					'free_money'=>getFloat($this->_userinfo['free_money']-$this->trade_money),
					'freeze_money'=>getFloat($this->_userinfo['freeze_money']+$this->trade_money),
				));
			} else {
				//冻结持仓
				$redis->hincrby('position:'.$this->_userinfo['uid'].':'.$this->pid,'can_sell',-$this->volume);
			}
		} catch(\Exception $e) {
			$this->ajaxReturn(array('status'=>0,'msg'=>$e->getMessage()));
		}
		
		$this->ajaxReturn(array('status'=>1,'msg'=>'挂单成功'));
	}
	
	#应价
	public function yj() {
		try {
			$this->checkYj();
			$this->direct == 's' && $gd_flag = 'out';
			$this->direct == 'b' && $gd_flag = 'in';

			$redis = getRedis();
			$gd_price_key = 'gid_'.$gd_flag.'_by_price:'.$this->pid.':'.$this->price;
			$all_gid = $redis->zrange($gd_price_key,0,100000);//当前价格所有挂单记录gid
			$yj_volume = $this->volume;//此次打算应价的数量
			$consume = 0;//此次应价消耗的数量
			$yj_cost = 0;//此次应价消耗资金
			$yj_count = 0;//此次应价的笔数

			//s代表应价买入 b代表应价卖出
			if($this->direct == 's') {
				$gd_gid = $gd_id = $gd_name = $gd_mobile = array();//应价时所有挂单方gid、uid、name、mobile
				foreach($all_gid as $k=>$v) {
					$gid = 'gd_record:'.$v;
					$gd_info = $redis->hgetall($gid);
					if(in_array($gd_info['gd_status'],array(3,4,5))) {
						continue;
					}
					$yj_volume -= $gd_info['volume'];
					$consume += $gd_info['volume'];
					$gd_free_money = $redis->hget('user:'.$gd_info['uid'],'free_money');//可用资金
					$gd_freeze_money = $redis->hget('user:'.$gd_info['uid'],'freeze_money');//冻结资金
					
					if($yj_volume>=0) {
						$gd_gid[] = $gid;
						$gd_id[] = $gd_info['uid'];
						$gd_name[] = $gd_info['name'];
						$gd_mobile[] = $gd_info['mobile'];
						$cost = $gd_info['volume']*$gd_info['price'];
						$yj_cost += $cost;
						$yj_count++;
						$gd_follow_info = array(
							"follow_number"=>generateFollowNumber('G'),//G挂单成交
							"customer_id"=>$gd_info['uid'],
							"customer_mobile"=>$gd_info['mobile'],
							"customer_name"=>$gd_info['name'],
							"follow_type"=>6,
							"bussiness_desciption"=>'挂单卖出成交 '.$this->product['product_number'],
							"money"=>$cost,
							"new_money"=>$gd_free_money+$cost,
							"freeze_money"=>$gd_freeze_money,
							"create_time"=>time(),
						);
						$gd_deals_info = array(
							'deals_type'=>2,
							'customer_id'=>$gd_info['uid'],
							'customer_name'=>$gd_info['name'],
							'customer_mobile'=>$gd_info['mobile'],
							'product_number'=>$gd_info['product_number'],
							'short_name'=>$gd_info['short_name'],
							'price'=>$gd_info['price'],
							'volume'=>$gd_info['volume'],
							'pid'=>$gd_info['pid'],
							'trade_money'=>getFloat($gd_info['volume']*$gd_info['price']),
							'create_time'=>time(),
							'other_id'=>$this->_userinfo['uid'],
							'other_name'=>$this->_userinfo['name'],
							'other_mobile'=>$this->_userinfo['mobile'],
							'gid'=>$gid,
						);
						
						$redis->hmset($gid,array(
							'gd_status'=>3,//挂单状态，全部成交
							'volume'=>0,//挂单数量
							'yj_time'=>time()//应价时间
						));//修改挂单记录状态、数量
						$redis->lpush('deals',json_encode($gd_deals_info));//挂单方成交记录
						$redis->lpush('follow',json_encode($gd_follow_info));//挂单方流水
						$redis->hset('user:'.$gd_info['uid'],'free_money',getFloat($gd_free_money+$cost));//挂单方资金变化
						$this->generatePosition($this->pid,$gd_info['uid'],$gd_info['volume'],$gd_info['price'],'yj_in_gd');//挂单方库存
						$redis->zrem('gid_'.$gd_flag.'_by_price:'.$this->pid.':'.$this->price,$gd_info['gid']);//把gid从gid_out_by_price:pid:price表中删除
					} else {
						$gd_gid[] = $gid;
						$gd_id[] = $gd_info['uid'];
						$gd_name[] = $gd_info['name'];
						$gd_mobile[] = $gd_info['mobile'];
						$last_volume = $gd_info['volume']-($consume-$this->volume);//成交数量
						$cost = $last_volume*$gd_info['price'];
						$yj_cost += $cost;
						$consume_all = $consume;//本来打算的数量
						$consume = $this->volume;//实际数量
						$gd_follow_info = array(
							"follow_number"=>generateFollowNumber('G'),//G挂单成交
							"customer_id"=>$gd_info['uid'],
							"customer_mobile"=>$gd_info['mobile'],
							"customer_name"=>$gd_info['name'],
							"follow_type"=>6,
							"bussiness_desciption"=>'挂单卖出成交 '.$this->product['product_number'],
							"money"=>$cost,
							"new_money"=>$gd_free_money+$cost,
							"freeze_money"=>$gd_freeze_money,
							"create_time"=>time(),
						);
						$gd_deals_info = array(
							'deals_type'=>2,
							'customer_id'=>$gd_info['uid'],
							'customer_name'=>$gd_info['name'],
							'customer_mobile'=>$gd_info['mobile'],
							'product_number'=>$gd_info['product_number'],
							'short_name'=>$gd_info['short_name'],
							'price'=>$gd_info['price'],
							'volume'=>$last_volume,
							'pid'=>$gd_info['pid'],
							'trade_money'=>getFloat($yj_cost),
							'create_time'=>time(),
							'other_id'=>$this->_userinfo['uid'],
							'other_name'=>$this->_userinfo['name'],
							'other_mobile'=>$this->_userinfo['mobile'],
							'gid'=>$gid,
						);
						
						$redis->hmset($gid,array(
							'gd_status'=>2,//挂单状态，部分成交
							'volume'=>$consume_all-$this->volume,//挂单数量
							'yj_time'=>time()//应价时间
						));
						$redis->lpush('deals',json_encode($gd_deals_info));//挂单方成交记录
						$redis->lpush('follow',json_encode($gd_follow_info));//挂单方流水
						$redis->hset('user:'.$gd_info['uid'],'free_money',getFloat($gd_free_money+$cost));//挂单方资金变化
						$this->generatePosition($this->pid,$gd_info['uid'],$last_volume,$gd_info['price'],'yj_in_gd');//挂单方库存
						
						break;
					}	
				}

				$new_free_money = $redis->hget('user:'.$this->_userinfo['uid'],'free_money');//重新查询一次，防止同一用户挂单应价时数据错误
				$yj_follow_info = array(
					"follow_number"=>generateFollowNumber('Y'),//Y应价成交
					"customer_id"=>$this->_userinfo['uid'],
					"customer_mobile"=>$this->_userinfo['mobile'],
					"customer_name"=>$this->_userinfo['name'],
					"follow_type"=>7,
					"bussiness_desciption"=>'应价买入成交 '.$this->product['product_number'],
					"money"=>-$yj_cost,
					"new_money"=>$new_free_money-$yj_cost,
					"freeze_money"=>$this->_userinfo['freeze_money'],
					"create_time"=>time(),
				);
				$yj_deals_info = array(
					'deals_type'=>3,
					'customer_id'=>$this->_userinfo['uid'],
					'customer_name'=>$this->_userinfo['name'],
					'customer_mobile'=>$this->_userinfo['mobile'],
					'product_number'=>$gd_info['product_number'],
					'short_name'=>$gd_info['short_name'],
					'price'=>$gd_info['price'],
					'volume'=>$consume,
					'pid'=>$gd_info['pid'],
					'trade_money'=>getFloat($yj_cost),
					'create_time'=>time(),
					'other_id'=>json_encode($gd_id),
					'other_name'=>json_encode($gd_name),
					'other_mobile'=>json_encode($gd_mobile),
					'gid'=>json_encode($gd_gid),
				);
				
				$redis->lpush('deals',json_encode($yj_deals_info));
				$redis->lpush('follow',json_encode($yj_follow_info));//应价方流水
				$this->generatePosition($this->pid,$this->_userinfo['uid'],$consume,$this->price,'yj_in_yj');//应价方库存
				$redis->hset('user:'.$this->_userinfo['uid'],'free_money',getFloat($new_free_money-$yj_cost));//应价方资金变化
				$gd_price_detail_key = 'gd_'.$gd_flag.'_price_detail:'.$gd_info['pid'].':'.$gd_info['price'];
				$gd_price_volume = $redis->hget($gd_price_detail_key,'volume');
				
				if($gd_price_volume<=$this->volume) {//当前价格吃完时把当前价格从挂单价格集合中删除
					$redis->srem('gd_'.$gd_flag.'_price:'.$this->pid,$this->price);
					$redis->del($gd_price_detail_key);
				} else {
					$redis->hincrby($gd_price_detail_key,'volume',-$consume);
					$redis->hincrby($gd_price_detail_key,'count',-$yj_count);
				}
			} else {
				$gd_gid = $gd_id = $gd_name = $gd_mobile = array();//应价时所有挂单方gid、uid、name、mobile
				foreach($all_gid as $k=>$v) {
					$gid = 'gd_record:'.$v;
					$gd_info = $redis->hgetall($gid);
					if(in_array($gd_info['gd_status'],array(3,4,5))) {
						continue;
					}
					$yj_volume -= $gd_info['volume'];
					$consume += $gd_info['volume'];
					$gd_user_money = $redis->hmget('user:'.$gd_info['uid'],'freeze_money','free_money');
					
					if($yj_volume>=0) {
						$gd_gid[] = $gid;
						$gd_id[] = $gd_info['uid'];
						$gd_name[] = $gd_info['name'];
						$gd_mobile[] = $gd_info['mobile'];
						$cost = $gd_info['volume']*$gd_info['price'];
						$yj_cost += $cost;
						$yj_count++;
						$gd_follow_info = array(
							"follow_number"=>generateFollowNumber('G'),//G挂单成交
							"customer_id"=>$gd_info['uid'],
							"customer_mobile"=>$gd_info['mobile'],
							"customer_name"=>$gd_info['name'],
							"follow_type"=>3,
							"bussiness_desciption"=>'挂单买入成交 '.$this->product['product_number'],
							"money"=>-$cost,
							"new_money"=>$gd_user_money[1],
							"freeze_money"=>$gd_user_money[0]-$cost,
							"create_time"=>time(),
						);
						$gd_deals_info = array(
							'deals_type'=>1,
							'customer_id'=>$gd_info['uid'],
							'customer_name'=>$gd_info['name'],
							'customer_mobile'=>$gd_info['mobile'],
							'product_number'=>$gd_info['product_number'],
							'short_name'=>$gd_info['short_name'],
							'price'=>$gd_info['price'],
							'volume'=>$gd_info['volume'],
							'pid'=>$gd_info['pid'],
							'trade_money'=>getFloat($gd_info['volume']*$gd_info['price']),
							'create_time'=>time(),
							'other_id'=>$this->_userinfo['uid'],
							'other_name'=>$this->_userinfo['name'],
							'other_mobile'=>$this->_userinfo['mobile'],
							'gid'=>$gid,
						);
						
						$redis->hmset($gid,array(
							'gd_status'=>3,//挂单状态，全部成交
							'volume'=>0,//挂单数量
							'yj_time'=>time()//应价时间
						));
						$redis->lpush('deals',json_encode($gd_deals_info));//成交记录
						$redis->lpush('follow',json_encode($gd_follow_info));//挂单方流水
						$redis->hset('user:'.$gd_info['uid'],'freeze_money',getFloat($gd_user_money[0]-$cost));//挂单方资金变化
						$this->generatePosition($this->pid,$gd_info['uid'],$gd_info['volume'],$gd_info['price'],'yj_out_gd');//挂单方库存
						$redis->zrem('gid_'.$gd_flag.'_by_price:'.$this->pid.':'.$this->price,$gd_info['gid']);//把gid从gid_in_by_price:pid:price表中删除
					} else {
						$gd_gid[] = $gid;
						$gd_id[] = $gd_info['uid'];
						$gd_name[] = $gd_info['name'];
						$gd_mobile[] = $gd_info['mobile'];
						$last_volume = $gd_info['volume']-($consume-$this->volume);
						$cost = $last_volume*$gd_info['price'];
						$yj_cost += $cost;
						$consume_all = $consume;//本来打算的数量
						$consume = $this->volume;//实际应价的数量
						$gd_follow_info = array(
							"follow_number"=>generateFollowNumber('G'),//G挂单成交
							"customer_id"=>$gd_info['uid'],
							"customer_mobile"=>$gd_info['mobile'],
							"customer_name"=>$gd_info['name'],
							"follow_type"=>3,
							"bussiness_desciption"=>'挂单买入成交 '.$this->product['product_number'],
							"money"=>-$cost,
							"new_money"=>$gd_user_money[1],
							"freeze_money"=>$gd_user_money[0]-$cost,
							"create_time"=>time(),
						);
						$gd_deals_info = array(
							'deals_type'=>1,
							'customer_id'=>$gd_info['uid'],
							'customer_name'=>$gd_info['name'],
							'customer_mobile'=>$gd_info['mobile'],
							'product_number'=>$gd_info['product_number'],
							'short_name'=>$gd_info['short_name'],
							'price'=>$gd_info['price'],
							'volume'=>$last_volume,
							'pid'=>$gd_info['pid'],
							'trade_money'=>getFloat($cost),
							'create_time'=>time(),
							'other_id'=>$this->_userinfo['uid'],
							'other_name'=>$this->_userinfo['name'],
							'other_mobile'=>$this->_userinfo['mobile'],
							'gid'=>$gid,
						);

						$redis->hmset($gid,array(
							'gd_status'=>2,//挂单状态，部分成交
							'volume'=>$consume_all-$this->volume,//挂单数量
							'yj_time'=>time()//应价时间
						));//修改状态记录状态、数量
						$redis->lpush('deals',json_encode($gd_deals_info));//成交记录
						$redis->lpush('follow',json_encode($gd_follow_info));//挂单方流水
						$redis->hset('user:'.$gd_info['uid'],'freeze_money',getFloat($gd_user_money[0]-$cost));//挂单方资金变化
						$this->generatePosition($this->pid,$gd_info['uid'],$last_volume,$gd_info['price'],'yj_out_gd');//挂单方库存
						
						break;
					}
				}

				$new_freeze_money = $redis->hget('user:'.$this->_userinfo['uid'],'freeze_money');//重新查询一次，防止同一用户挂单应价时数据错误
				$yj_follow_info = array(
					"follow_number"=>generateFollowNumber('Y'),//Y应价成交
				    "customer_id"=>$this->_userinfo['uid'],
				    "customer_mobile"=>$this->_userinfo['mobile'],
				    "customer_name"=>$this->_userinfo['name'],
				    "follow_type"=>4,
					"bussiness_desciption"=>'应价卖出成交 '.$this->product['product_number'],
				    "money"=>$yj_cost,
				    "new_money"=>$this->_userinfo['free_money']+$yj_cost,
					"freeze_money"=>$new_freeze_money,
				    "create_time"=>time(),
				);
				$yj_deals_info = array(
					'deals_type'=>4,
					'customer_id'=>$this->_userinfo['uid'],
					'customer_name'=>$this->_userinfo['name'],
					'customer_mobile'=>$this->_userinfo['mobile'],
					'product_number'=>$gd_info['product_number'],
					'short_name'=>$gd_info['short_name'],
					'price'=>$gd_info['price'],
					'volume'=>$consume,
					'pid'=>$gd_info['pid'],
					'trade_money'=>getFloat($yj_cost),
					'create_time'=>time(),
					'other_id'=>json_encode($gd_id),
					'other_name'=>json_encode($gd_name),
					'other_mobile'=>json_encode($gd_mobile),
					'gid'=>json_encode($gd_gid),
				);

				$redis->lpush('deals',json_encode($yj_deals_info));//成交记录
				$redis->lpush('follow',json_encode($yj_follow_info));//应价方流水
				$this->generatePosition($this->pid,$this->_userinfo['uid'],$consume,$this->price,'yj_out_yj');//应价方库存
				$redis->hset('user:'.$this->_userinfo['uid'],'free_money',getFloat($this->_userinfo['free_money']+$yj_cost));//应价方资金变化
				$gd_price_detail_key = 'gd_'.$gd_flag.'_price_detail:'.$gd_info['pid'].':'.$gd_info['price'];
				$gd_price_volume = $redis->hget($gd_price_detail_key,'volume');
				
				if($gd_price_volume<=$this->volume) {//当前价格吃完时把当前价格从挂单价格集合中删除
					$redis->srem('gd_'.$gd_flag.'_price:'.$this->pid,$this->price);
					$redis->del($gd_price_detail_key);
				} else {
					$redis->hincrby($gd_price_detail_key,'volume',-$this->volume);
					$redis->hincrby($gd_price_detail_key,'count',-$yj_count);
				}
			}
			
			$product_trade_key = 'product_trade:'.$this->pid;
			$product_trade = $redis->hgetall('product_trade:'.$this->pid);
			//更新最新价格、最高价、最低价
			$redis->hset($product_trade_key,'now_price',$this->price);
			if($this->price>$product_trade['high_price']){
				$redis->hset($product_trade_key,'high_price',$this->price);
			}
			if($this->price<$product_trade['low_price']){
				$redis->hset($product_trade_key,'low_price',$this->price);
			}

			$this->ajaxReturn(array('status'=>1,'msg'=>'应价成功'));
		} catch (\Exception $e) {
			$this->ajaxReturn(array('status'=>0,'msg'=>$e->getMessage()));
		}
		
	}

	#应价验证
	protected function checkYj($data) {
		!$data && $data = I('post.');
		$data['volume'] = (int)$data['volume'];
		$data['pid'] = (int)$data['pid'];
		if(!is_numeric($data['price']) || !is_int($data['volume']) || !is_int($data['pid']) || !in_array($data['direct'],array('b','s'))) {
			throw new \Exception('参数错误');
		}
		if($data['volume']<=0) {
			throw new \Exception('参数错误');
		}
		$this->price = getFloat($data['price']);
		$this->volume = $data['volume'];
		$this->pid = $data['pid'];
		$this->direct = $data['direct'];
		
		$redis= getRedis();
		if(!$product = $redis->hgetall('product_trade:'.$this->pid)) {
			throw new \Exception('商品不存在');
		}
		
		$redis = getRedis();
		$yj_start_time_am = $redis->hget('settings','trade_am_start') ? $redis->hget('settings','trade_am_start') : '9:30';//应价上午开始时间 
		$yj_start_time_am = strtotime($yj_start_time_am);
		$yj_end_time_am = $redis->hget('settings','trade_am_end') ? $redis->hget('settings','trade_am_end') : '11:30';//应价上午截止时间 
		$yj_end_time_am = strtotime($yj_end_time_am);
		$yj_start_time_pm = $redis->hget('settings','trade_pm_start') ? $redis->hget('settings','trade_pm_start') : '13:30';//应价下午开始时间 
		$yj_start_time_pm = strtotime($yj_start_time_pm);
		$yj_end_time_pm = $redis->hget('settings','trade_pm_end') ? $redis->hget('settings','trade_pm_end') : '17:30';//应价下午截止时间 
		$yj_end_time_pm = strtotime($yj_end_time_pm);
		if(!($yj_start_time_am<time() && time()<$yj_end_time_am) && !($yj_start_time_pm<time() && time()<$yj_end_time_pm)) {
			throw new \Exception('现在不是交易时间');
		}
		
		if($this->direct == 's') {
			$this->trade_money = getFloat($this->volume*$this->price);
			if($this->trade_money>$this->_userinfo['free_money']) {
				throw new \Exception('可用资金不足');
			}
			$price_key = 'gd_out_price:'.$this->pid;
		} else {
			$can_sell = $redis->hget('position:'.$this->_userinfo['uid'].':'.$this->pid,'can_sell');
			if($can_sell<$this->volume) {
				throw new \Exception('持仓数量不足');
			}
			$price_key = 'gd_in_price:'.$this->pid;
		}
		
		if(!$redis->sismember($price_key,getFloat($this->price))) {
			throw new \Exception('挂单价位已失效');
		}
	}
	
	#挂单验证
	protected function gdCheck($data) {
		!$data && $data = I('post.');
		$data['volume'] = (int)$data['volume'];
		$data['pid'] = (int)$data['pid'];
		if(!is_numeric($data['price']) || !is_int($data['volume']) || !is_int($data['pid']) || !in_array($data['direct'],array('b','s'))) {
			throw new \Exception('参数错误');
		}
		if($data['volume']<=0) {
			throw new \Exception('参数错误');
		}
		
		$redis = getRedis();
		$gd_start_time = $redis->hget('settings','trade_am_start') ? $redis->hget('settings','trade_am_start') : '9:30';//挂单开始时间 
		$gd_start_time = strtotime($gd_start_time);
		$gd_end_time = $redis->hget('settings','trade_pm_end') ? $redis->hget('settings','trade_pm_end') : '15:00';//挂单截止时间
		$gd_end_time = strtotime($gd_end_time);
		if(time()>$gd_end_time || time()<$gd_start_time) {
			throw new \Exception('现在不是挂单时间');
		}
		
		
		$this->price = getFloat($data['price']);
		$this->volume = $data['volume'];
		$this->pid = $data['pid'];
		$this->direct = $data['direct'];
		
		$redis= getRedis();
		if(!$product = $redis->hgetall('product_trade:'.$this->pid)) {
			throw new \Exception('商品不存在');
		}

		$min_price = $product['min_price'];
		$max_price = $product['max_price'];
		if($this->price>$max_price || $this->price<$min_price) {
			throw new \Exception('挂单价格需要在涨跌幅之间');
		}

		$this->product = $product;
		if($this->direct == 'b') {
			$this->trade_money = getFloat($this->volume*$this->price);
			if($this->trade_money>$this->_userinfo['free_money']) {
				throw new \Exception('可用资金不足');
			}
		} else {
			$position = $redis->hget('position:'.$this->_userinfo['uid'].':'.$this->pid,'can_sell');
			if($position<$this->volume) {
				throw new \Exception('持仓数量不足');
			}
		}
	}

	#撤销挂单
	public function cancelGd() {
		$gid = I('post.gid',0,'int');
		$redis = getRedis();
		$gid_key = 'gd_record:'.$gid;
		if(!$gd_info = $redis->hgetall($gid_key)) {
			$this->ajaxReturn(array('status'=>0,'msg'=>'挂单不存在'));
		}
		if(!in_array($gd_info['gd_status'],array(1,2))) {
			$this->ajaxReturn(array('status'=>0,'msg'=>'挂单状态不符'));
		}

		if($gd_info['gd_status'] == 2) {
			$new_gd_status = 6;//部分撤单
		} else {
			$new_gd_status = 4;//撤单
		}
		$redis->hmset($gid_key,array('gd_status'=>$new_gd_status,'cancel_time'=>time(),'volume'=>0));//修改状态、撤单时间
		if($gd_info['direct'] == 's') {
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
		} else {
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

		$this->ajaxReturn(array('status'=>1,'msg'=>'撤单成功'));
	}
}