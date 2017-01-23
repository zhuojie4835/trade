<?php
namespace Common\Model;

/**
 * 配置模型
 * @author zhuojie
 */
class SettingsModel extends TradeModel {
	public $_type_val = array(1=>'通用设置',2=>'交易设置');
	
	public $_status_val = array(1=>'启用',2=>'禁用');
	
	protected $_validate = array(
		//4 新增
		array('key','require','键值必须',1,'',4),
		array('key','','键值已存在',1,'unique',4),
		array('value','require','值必须',1,'',4),
		array('type','checkType','',1,'callback',4),
		//5 修改
		array('value','require','值必须',1,'',5),
		array('type','checkType','',1,'callback',5),
	);
	
	protected function checkType($type) {
		if($type<=0) {
			throw new \Exception('配置类型必选');
		}
		if(!array_key_exists($type,$this->_type_val)) {
			throw new \Exception('配置类型错误');
		}
	}
}