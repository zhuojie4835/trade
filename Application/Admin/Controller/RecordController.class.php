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
		I('member_agent_number') && $map['member_agent_number'] = I('member_agent_number');
		I('agent_number') && $map['agent_number'] = I('agent_number');

		$model = D('Common/Deals');
		$list   = $this->lists($model,$map);
		// $customer_type_val = D('Common/Customer')->_user_type_val;
		$deals_type_val = D('Common/deals')->_deals_type_val;
		$xj_volume = $xj_money = $zj_volume = $zj_money = 0;
		foreach($list as $k=>$v) {
			// $list[$k]['customer_type_text'] = $customer_type_val[$v['customer_type']];
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
			
			$deals['other_id'] = implode(',',$deals['other_id']);
			$deals['other_name'] = implode(',',$deals['other_name']);
			$deals['other_mobile'] = implode(',',$deals['other_mobile']);
		}
		
		$this->meta_title = '成交详情';
		$this->assign('info',$deals);
		$this->assign('gd_detail',$gd_detail);
		$this->display();
	}

	#更新持仓记录
	public function position() {
		R('Cli/index/updatePosition');
		$map = array('id'=>array('gt',0));
		I('customer_mobile') && $map['customer_mobile'] = I('customer_mobile');
		I('product_number') && $map['product_number'] = I('product_number');

		$model = D('Common/Position');
		$product_status = D('Common/Product')->_status_val;
		$list = $this->lists($model,$map);
		foreach ($list as $k=>$v) {
			$list[$k]['status_text'] = $product_status[$v['status']];
			$list[$k]['profit'] = getFloat($v['volume']*($v['now_price']-$v['average_price']));
		}
		$this->assign('list',$list);
		$this->meta_title = '持仓记录';
		$this->display();
	}
}