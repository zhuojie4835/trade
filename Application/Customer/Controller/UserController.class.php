<?php
namespace Customer\Controller;


class UserController extends BaseController {
	private $_userinfo = array();
	
	public function __construct() {
    	parent::__construct();
    	$userinfo = session('userinfo');
    	if(empty($userinfo['id'])) {
    		$this->redirect('Index/login');
    	}
    	$redis = getRedis();
    	$userinfo_in_redis = $redis->hgetall("user:" . $userinfo['id']);
    	$this->_userinfo = $userinfo_in_redis;
    }
	
	public function center() {
		$redis = getRedis();
		$position_keys = $redis->keys('position:'.$this->_userinfo['uid'].'*');
		$list = array();
		
		if(I('post.act') == 'position') {
			$position_keys = $redis->keys('position:'.$this->_userinfo['uid'].':*');
			$p_list = array();
			$total_profit = 0;
			$total_market_value = 0;
			if($position_keys) {
				foreach($position_keys as $k=>$v) {
					$item = $redis->hgetall($v);
					$product_trade = $redis->hgetall('product_trade:'.$item['pid']);
					$item['now_price'] = $product_trade['now_price'];
					$item['profit'] = ($item['now_price']-$item['average_price'])*$item['volume'];
					$item['profit'] = getFloat($item['profit']);
					$item['market_value'] = getFloat($item['now_price']*$item['volume']);
					$p_list[] = $item;
					$total_profit += $item['profit'];
					$total_market_value += $item['market_value'];
				}
			}
			
			$this->ajaxReturn(
				array(
					'status'=>1,
					'p_list'=>$p_list,
					'total_profit'=>getFloat($total_profit),
					'total_market_value'=>getFloat($total_market_value),
					'user_info'=>$this->_userinfo
				)
			);
		}
		
		$gd_today_list = array();
		$today_gids = $redis->zrevrangebyscore('gid_by_person:'.$this->_userinfo['uid'],strtotime(date('Y-m-d').' 23:59:59'),strtotime(date('Y-m-d').' 00:00:00'));
		foreach($today_gids as $k=>$v) {
			$gd_today_list[] = $redis->hgetall('gd_record:'.$v);
			
		}
		
		int_to_string($gd_today_list,array('gd_status'=>array(1=>'等待成交',2=>'部分成交',3=>'全部成交',4=>'已撤销')));
		$this->assign('active',2);
		$this->assign('list',$list);
		$this->assign('gd_today_list',$gd_today_list);
		$this->display();
	}

	#认购页面
	public function subscribe($id) {
		$redis = getRedis();
		if(!$id || (!$info = $redis->hgetall('product:'.$id))) {
			$this->error('参数错误');
		}
		$info = unserialize($info['product_info']);
		
		if(IS_POST) {
			$num = (int)I('post.num');
			if($num<=0) {
				$this->ajaxReturn(array('status'=>0,'msg'=>'认购数量需大于0！'));
			}
			
			$subscribe_money = $info['issue_price']*$num;
			if($subscribe_money>$this->_userinfo['free_money']) {
				$this->ajaxReturn(array('status'=>0,'msg'=>'余额不足！'));
			}
			$customer_money = array(
				// 'total_money'=>$this->_userinfo['total_money']-$subscribe_money,
				'free_money'=>$this->_userinfo['free_money']-$subscribe_money,
			);
			$follow_info = array(
				"follow_number"=>generateFollowNumber('S'),
			    "customer_id"=>$this->_userinfo['uid'],
			    "customer_mobile"=>$this->_userinfo['mobile'],
			    "customer_name"=>$this->_userinfo['name'],
			    "follow_type"=>2,
				"bussiness_desciption"=>'认购 '.$info['number'],
			    "money"=>-$subscribe_money,
			    "new_money"=>$this->_userinfo['free_money']-$subscribe_money,
			    'freeze_money'=>$this->_userinfo['freeze_money'],
			    "create_time"=>time(),
			);
			$subscribe_info = array(
				'pid'=>$id,
				'customer_id'=>$this->_userinfo['uid'],
				'customer_mobile'=>$this->_userinfo['mobile'],
				'customer_name'=>$this->_userinfo['name'],
				'volume'=>$num,
				'can_sell'=>$num,
				'product_number'=>$info['number'],
				'price'=>$info['issue_price'],
				'status'=>$info['status'],
				'short_name'=>$info['short_name'],
				'create_time'=>time(),
			);
			
			$redis->lpush('follow',json_encode($follow_info));//流水
			$redis->lpush('subscribe_record',json_encode($subscribe_info));//认购记录
			$redis->hmset('user:'.$this->_userinfo['uid'],$customer_money);//更新用户资金
			$redis->hincrby('product_trade:'.$id,'left_number',-$num);//更新产品交易信息
			$this->generatePosition($info['id'],$this->_userinfo['uid'],$num,$info['issue_price']);//重新计算均价
			$this->ajaxReturn(array('status'=>1,'msg'=>'认购成功！'));
		}
		
		if($info['status'] != 2) {
			$this->redirect('index/index');
		}
		
		$product_trade_info = getProductTradeInfo($id);
		$info['left_number'] = $product_trade_info['left_number'];
		
		$this->assign('user_info',$this->_userinfo);
		$this->assign('info',$info);
		$this->display();
	}
	
	#充值
	public function recharge() {
		
		$this->display();
	}

	#出金
	public function withdraw() {
		
		$this->display();
	}
}
