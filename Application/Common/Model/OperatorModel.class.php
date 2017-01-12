<?php
namespace Common\Model;

class OperatorModel extends TradeModel {
	public $_status_val = array(1=>'启用',2=>'禁用');
	
	protected $_validate = array(
		//4 新增
		array('operator_number','checkOperator','运营中心必须',1,'callback',4),
		array('operator_number','/^1\d{5}$/','运营中心格式错误',1,'',4),
		array('name','require','联络人姓名必须',1,'',4),
		array('mobile','require','联络人手机号码必须',1,'',4),
		array('mobile','/^1[34578]\d{9}$/','联络人手机号码格式错误',1,'',4),
		array('company_name','require','公司名称必须',1,'',4),
		//5修改
		array('name','require','联络人姓名必须',1,'',5),
		array('mobile','require','联络人手机号码必须',1,'',5),
		array('mobile','/^1[34578]\d{9}$/','联络人手机号码格式错误',1,'',5),
		array('company_name','require','公司名称必须',1,'',5),
	);

	protected function checkOperator($operator) {
		if(!$operator) {
			throw new \Exception('运营中心必须');
		}
		if(D('Common/Operator')->where(array('operator_number'=>$operator))->find()) {
			throw new \Exception('运营中心已存在');
		}
	}
}