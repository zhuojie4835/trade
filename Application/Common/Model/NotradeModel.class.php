<?php
namespace Common\Model;

/**
 * 假期模型
 * @author zhuojie
 */
class NotradeModel extends TradeModel {
	public $_status_val = array(1=>'启用',2=>'禁用');
	
	protected $_validate = array(
		//4 新增
		array('datetime','checkDatetime','',1,'callback',4)
	);
	
	protected function checkDatetime($datetime) {
		if(!$datetime) {
			throw new \Exception('休市日期必须');
		}
		$time_int = (int)strtotime($datetime);
		if($time_int<=0) {
			throw new \Exception('格式错误');
		}
		if($this->where(array('datetime'=>$time_int))->find()) {
			throw new \Exception('休市日期已存在');
		}
		$_POST['datetime'] = $time_int;
	}
}