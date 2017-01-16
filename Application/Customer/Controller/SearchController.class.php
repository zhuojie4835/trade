<?php
namespace Customer\Controller;


class SearchController extends BaseController {
	private $_userinfo = array();
	
	public function __construct() {
    	parent::__construct();
    	if(!$this->isLogin()) {
    		$this->redirect('Index/login');
    	}
    	$redis = getRedis();
    	$userinfo_in_redis = $redis->hgetall("user:".session('uid'));
    	$this->_userinfo = $userinfo_in_redis;
    }
	
	public function index() {
		$this->active = 4;
		$this->display();
	}

	#个人信息
	public function userinfo() {
		$this->_userinfo['user_type_text'] = D('Common/Customer')->_user_type_val[$this->_userinfo['user_type']];
		$this->assign('userinfo',$this->_userinfo);
		$this->display();
	}

	#资金流水
	public function follow() {
		$list = D('Common/Follow')->where(array('customer_id'=>$this->_userinfo['uid']))->order(array('id'=>'desc'))->select();
		$follow_type_val = D('Common/Follow')->_follow_type_val;

		foreach($list as $k=>$v) {
			$list[$k]['date'] = date('Y/m/d',$v['create_time']);
			$list[$k]['time'] = date('H:i:s',$v['create_time']);
			$list[$k]['follow_type_text'] = $follow_type_val[$v['follow_type']];
		}

		$this->active = 4;
		$this->assign('list',$list);
		$this->display();
	}

	#银行卡信息
	public function bank() {
		$this->active = 4;
		$this->display();
	}
	
	#认购记录
	public function sublist() {
		$list = D('Common/ProductOrder')->where(array('customer_id'=>$this->_userinfo['uid']))->order(array('id'=>'desc'))->select();
		
		$this->assign('list',$list);
		$this->display();
	}

	#收货地址
	public function addess() {
		$this->display();
	}

	#成交记录
	public function deals() {
		$list = M('deals','trade_')->where(array('customer_id'=>$this->_userinfo['uid']))->order(array('id'=>'desc'))->select();
		int_to_string($list,array('deals_type'=>array(1=>'挂单买入',2=>'挂单卖出',3=>'应价买入',4=>'应价卖出')));
		$this->assign('list',$list);
		
		$this->display();
	}

	#挂单记录
	public function gd() {
		$list = array();
		$redis = getRedis();
		$gids = $redis->zrevrange('gid_by_person:'.$this->_userinfo['uid'],0,100000);
		$gd_status = array(1=>'等待成交',2=>'部分成交',3=>'全部成交',4=>'已撤销',5=>'系统撤销');
		foreach($gids as $v) {
			$item = $redis->hgetall('gd_record:'.$v);
			$item['gd_status_text'] = $gd_status[$item['gd_status']];
			$item['direct_text'] = ($item['direct'] == 's') ? '卖' : '买';
			$list[] = $item;
		}
		
		$this->assign('list',$list);
		$this->display();
	}

	#提货记录
	public function delivery() {
		$this->display();
	}

	#添加地址
	public function newaddress() {
		$this->display();
	}

	#返利记录
	public function fanli() {
		$this->display();
	}
}