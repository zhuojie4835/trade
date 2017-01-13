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
}