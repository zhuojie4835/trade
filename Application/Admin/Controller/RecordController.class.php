<?php
namespace Admin\Controller;

/**
 * 客户报表控制器
 * @author zhuojie
 */
class RecordController extends AdminController {
	#认购记录
	public function subscribe() {
		$map = array('id'=>array('gt',0));
		I('customer_mobile') && $map['customer_mobile'] = I('customer_mobile');
		I('product_number') && $map['product_number'] = I('product_number');
		I('member_agent_number') && $map['member_agent_number'] = I('member_agent_number');
		I('agent_number') && $map['agent_number'] = I('agent_number');

		$model = D('Common/ProductOrder');
		$list   = $this->lists($model,$map);
		$customer_type_val = D('Common/Customer')->_user_type_val;
		$xj_volume = $xj_money = $zj_volume = $zj_money = 0;
		foreach($list as $k=>$v) {
			$list[$k]['customer_type_text'] = $customer_type_val[$v['customer_type']];
			$xj_volume += $v['volume'];
			$xj_money += $v['trade_money'];
		}
		$zj_volume = (int)$model->sum('volume');
		$zj_money = getFloat($model->sum('trade_money'));

		$this->assign('tj',array($xj_volume,getFloat($xj_money),$zj_volume,$zj_money));
		$this->assign('list',$list);
		$this->meta_title = '认购记录';
		$this->display();
	}

	#成交记录
	public function deals() {
		$map = array('id'=>array('gt',0));
		I('customer_mobile') && $map['customer_mobile'] = I('customer_mobile');
		I('product_number') && $map['product_number'] = I('product_number');
		I('agent_member_number') && $map['agent_member_number'] = I('agent_member_number');
		I('agent_number') && $map['agent_number'] = I('agent_number');

		$model = D('Common/Deals');
		$list   = $this->lists($model,$map);
		$customer_type_val = D('Common/Customer')->_user_type_val;
		$deals_type_val = D('Common/deals')->_deals_type_val;
		$xj_volume = $xj_money = $zj_volume = $zj_money = 0;
		foreach($list as $k=>$v) {
			$list[$k]['customer_type_text'] = $customer_type_val[$v['user_type']];
			$list[$k]['deals_type_text'] = $deals_type_val[$v['deals_type']];
			$xj_volume += $v['volume'];
			$xj_money += $v['trade_money'];
		}
		$zj_volume = (int)$model->sum('volume');
		$zj_money = getFloat($model->sum('trade_money'));

		$this->assign('tj',array($xj_volume,getFloat($xj_money),$zj_volume,$zj_money));
		$this->assign('list',$list);
		$this->meta_title = '成交记录';
		$this->display();
	}
	
	#查看成交详情
	public function viewdeals($id=null) {
		!$id && $this->error('wrong request');
		if(!$deals = D('Common/Deals')->find($id)) {
			$this->error('no record');
		}
		
		$redis = getRedis();
		$gd_detail = array();
		if(in_array($deals['deals_type'],array(1,2))) {//挂单
			$gd_detail[] = $redis->hgetall($deals['gid']);
		} else {//应价
			$deals['other_id'] = json_decode($deals['other_id'],true);
			$deals['other_name'] = json_decode($deals['other_name'],true);
			$deals['other_mobile'] = json_decode($deals['other_mobile'],true);
			$deals['gid'] = json_decode($deals['gid'],true);
			foreach ($deals['gid'] as $k=>$v) {
				$gd_detail[] = $redis->hgetall($v);
			}

			$deals['other_id'] = implode(',',$deals['other_id']);
			$deals['other_name'] = implode(',',$deals['other_name']);
			$deals['other_mobile'] = implode(',',$deals['other_mobile']);
		}
		
		$gd_status_val = array(1=>'等待成交',2=>'部分成交',3=>'全部成交',4=>'撤单',5=>'系统撤单');
		foreach($gd_detail as $k=>$v) {
			$gd_detail[$k]['status_text'] = $gd_status_val[$v['gd_status']];
		}

		$this->meta_title = '成交详情';
		$this->assign('info',$deals);
		$this->assign('gd_detail',$gd_detail);
		$this->display();
	}

	#持仓记录
	public function position() {
		$uid = $pid = '*';
		if(($mobile = I('customer_mobile')) && ($customer = D('Common/Customer')->where(array('login_name'=>$mobile))->find())) {
			$uid = $customer['id'];
		}
		if(($product_number = I('product_number')) && ($product = D('Common/Product')->where(array('number'=>$product_number))->find())) {
			$pid = $product['id'];
		}

		$list = array();
		if(($uid != '*') || ($pid != '*')) {
			$redis = getRedis();
			$model = D('Common/Position');
			$position_keys = $redis->keys('position:'.$uid.':'.$pid);
			$product_status_val = D('Common/Product')->_status_val;

			foreach ($position_keys as $k=>$v) {
				$key_arr = explode(':', $v);
				$uid = $key_arr[1];
				$pid = $key_arr[2];
				$product_trade = $redis->hgetall('product_trade:'.$pid);
				$customer_inredis = $redis->hgetall('user:'.$uid);
				$item = $redis->hgetall($v);
				$item['customer_mobile'] = $customer_inredis['mobile'];
				$item['customer_name'] = $customer_inredis['name'];
				$item['now_price'] = $product_trade['now_price'];
				$item['profit'] = getFloat(($item['now_price']-$item['average_price'])*$item['volume']);
				$item['status_text'] = $product_status_val[$item['status']];
				$list[] = $item;
			}
		}
		
		$this->assign('list',$list);
		$this->meta_title = '持仓记录';
		$this->display();
	}
}