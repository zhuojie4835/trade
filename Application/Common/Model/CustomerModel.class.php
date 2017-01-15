<?php
namespace Common\Model;

/**
 * 客户模型
 * @author zhuojie
 */
class CustomerModel extends TradeModel {
	protected $tableName = 'customer';
	
	public $_user_type_val = array(1=>'个人客户',2=>'会员客户',3=>'高级代理客户',4=>'代理客户');
	
	public $_marriage_val = array(1=>'未婚',2=>'已婚');
	
	public $_balance_val = array(1=>'未入金',2=>'已入金');
	
	public $_funds_status_val = array(1=>'可存可取', 2=>'可存不取', 3=>'不存可取', 4=>'不存不取');
	
	public $_status_val = array(1=>'预批',2=>'已启用',3=>'已禁用',4=>'已注销');//账号状态
	
	public $_user_from_val = array(1=>'前台注册',2=>'后台添加');//用户来源
	
	public $_online_val = array(1=>'在线',2=>'离线');//用户来源

	#验证规则
	protected $_validate = array(
		//4注册
		array('name', 'require', '真实姓名必须',1,'',4),
		array('id_card_number', 'checkID', '',1,'callback',4),
		array('login_name', 'require', '手机号码必须',1,'',4),
		array('login_name', '/^1[34578]\d{9}$/', '手机号码格式错误',1,'',4),
		array('id_card_number','','身份证码已经存在！',1,'unique',4),
		array('login_name','','手机号码已经存在！',1,'unique',4),
		array('sms_code', 'checkSmsCode', '',1,'callback',4),
		array('password', '/^\w{6,20}$/', '密码由6~20位字符组成，可以是字母、数字、下划线',1,'',4),
		array('agent_number', 'checkAgent', '',1,'callback',4),
		array('parent1', 'checkParent1', '',2,'callback',4),
		//5登录
		array('password', 'checkLogin', '',1,'callback',5),
		//6找回密码
		array('login_name', 'require', '手机号码必须',1,'',6),
		array('login_name', '/^1[34578]\d{9}$/', '手机号码格式错误',1,'',6),
		array('login_name','checkMobile','',1,'callback',6),
		array('sms_code', 'checkSmsCode', '',1,'callback',6),
		array('password', '/^\w{6,20}$/', '密码由6~20位字符组成，可以是字母、数字、下划线',1,'',6),
	);
	
	protected function checkID($id) {
		if(!$id) {
			throw new \Exception('身份证号码必填');
		}
		if(!is_idcard($id)) {
			throw new \Exception('身份证号码格式错误');
		}
	}
	
	protected function checkSmsCode($code) {
		if(!$code) {
			throw new \Exception('手机验证码必填');
		}
	}
	
	protected function checkAgent($agent) {
		if(!$agent) {
			throw new \Exception('服务商编号必填');
		}
		if(!D('Common/Agent')->where(array('agent_number'=>$agent))->find()) {
			throw new \Exception('服务商不存在');
		}
	}
	
	protected function checkLogin($password) {
		$password = I('post.password');
		$login_name = I('post.login_name');
		if(!$login_name) {
			throw new \Exception('手机号不能为空');
		}
		if(!$password) {
			throw new \Exception('密码不能为空');
		}
		if(!$customer = $this->where(array('login_name'=>$login_name))->find()) {
			throw new \Exception('手机号码或密码错误');
		}
		if(self::generatePassword($password) != $customer['password']) {
			throw new \Exception('手机号或密码错误');
		}
	}
	
	protected function checkMobile($mobile) {
		if(!$mobile) {
			throw new \Exception('手机号码必须');
		}
		if(!preg_match('/^1[34578]\d{9}$/',$mobile)) {
			throw new \Exception('手机号码格式错误');
		}
		if(!$this->where(array('login_name'=>$mobile))->find()) {
			throw new \Exception('用户不存在');
		}
	}

	protected function checkParent1($parent_login_name) {
		$parent1 = $parent2 = 0;
		if($parent = D('Common/Customer')->field('id,parent1')->where(array('login_name'=>$parent_login_name))->find()) {
			$parent1 = $parent['id'];
			$parent2 = $parent['parent1'];
		}
		$_POST['parent1'] = $parent1;
		$_POST['parent2'] = $parent2;
	}
	
	static function generatePassword($password) {
		return md5($password . C('PWD_SALT'));
	}
} 

