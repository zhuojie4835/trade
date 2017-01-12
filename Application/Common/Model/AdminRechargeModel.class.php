<?php
namespace Common\Model;

class AdminRechargeModel extends TradeModel {
	protected $tableName = 'recharge_admin';

	public $_status_val = array(1=>'待审核',2=>'通过',3=>'驳回');
	
	protected $_validate = array(
		//4 新增
		array('customer_mobile','checkMobile','',1,'callback',4),
		array('total','checkTotal','',1,'callback',4),
	);

	protected function checkMobile($customer_mobile) {
		if(!$customer_mobile) {
			throw new \Exception('客户手机号码不能为空');
		}
		if(!$customer_mobile) {
			throw new \Exception('代理编号必须！');
		}
		if(!preg_match('/^1[34578]\d{9}$/',$customer_mobile)) {
			throw new \Exception('客户手机号码格式错误');
		}
		if(!D('Common/Customer')->where(array('login_name'=>$customer_mobile))->find()) {
			throw new \Exception('客户手机号码不存在');
		}
	}

	protected function checkTotal($total) {
		if(!$total) {
			throw new \Exception('充值金额不能为空');
		}
		if(!is_int((int)$total) || $total<=0) {
			throw new \Exception('充值金额是大于0的整数');
		}
	}
}