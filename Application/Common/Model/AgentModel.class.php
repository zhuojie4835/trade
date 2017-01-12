<?php
namespace Common\Model;

class AgentModel extends TradeModel {
	public $_status_val = array(1=>'启用',2=>'禁用');
	
	public $_agent_type_val = array(1=>'会员',2=>'高级代理',3=>'代理');
	
	protected $_validate = array(
		//4 新增
		array('agent_type','require','代理类型必须',1,'',4),
		array('parent_number','checkParent','',1,'callback',4),
		array('agent_number','checkAgentNumber','代理名称必须',1,'callback',4),
		array('agent_name','require','代理名称必须',1,'',4),
		array('ratio_jiaoyi','require','交易分成必须',1,'',4),
		array('ratio_fashou','require','发售分成必须',1,'',4),
		array('ratio_tihuo','require','提货分成必须',1,'',4),
		array('name','require','联络人姓名必须',1,'',4),
		array('mobile','require','联络人手机号码必须',1,'',4),
		array('mobile','/^1[34578]\d{9}$/','联络人手机号码格式错误',1,'',4),
		//5 修改
		array('agent_name','require','代理名称必须',1,'',5),
		array('ratio_jiaoyi','require','交易分成必须',1,'',5),
		array('ratio_fashou','require','发售分成必须',1,'',5),
		array('ratio_tihuo','require','提货分成必须',1,'',5),
		array('name','require','联络人姓名必须',1,'',5),
		array('mobile','require','联络人手机号码必须',1,'',5),
		array('mobile','/^1[34578]\d{9}$/','联络人手机号码格式错误',1,'',5),
	);
	
	public function idTree($id,$first_time=1) {
		static $tree = array();
		if($first_time==1) {
			$tree = array();
		}
		$info = $this->field('id,parent_id')->find($id);
		if(empty($info)) {
			return $tree;
		}
		array_unshift($tree,$info['id']);
		if(!empty($info['parent_id'])) {
			return $this->idTree($info['parent_id'],0);
		} else {
			return $tree;
		}
	}
	
	#验证代理编号
	protected function checkAgentNumber($agent_number) {
		if(!$agent_number) {
			throw new \Exception('代理编号必须！');
		}
		if(!preg_match('/^[567]\d{5}$/', $agent_number)) {
			throw new \Exception('代理编号格式错误！');
		}
		if($_POST['agent_type'] == 1 && !preg_match('/^5\d{5}$/', $agent_number)) {//会员
			throw new \Exception('会员代理编号必须5开头！');
		}
		if($_POST['agent_type'] == 2 && !preg_match('/^6\d{5}$/', $agent_number)) {//高级代理
			throw new \Exception('高级代理编号必须6开头！');
		}
		if($_POST['agent_type'] == 3 && !preg_match('/^7\d{5}$/', $agent_number)) {//代理
			throw new \Exception('会员代理编号必须7开头！');
		}
		if($this->where(array('agent_number'=>$agent_number))->find()) {
			throw new \Exception('代理编号已存在！');
		}
	}

	#验证直属上级
	protected function checkParent($parent_number) {
		$post = I('post.');
		
		if(!$parent_number) {
			throw new \Exception('直属上级编号必须！');
		}
		if($post['agent_type'] == 1 && !D('Common/Operator')->where(array('operator_number'=>$parent_number))->find()) {
			throw new \Exception('会员直属上级不存在！');
		}
		if($post['agent_type'] == 2 && !$this->where(array('agent_number'=>$parent_number, 'agent_type'=>1))->find()) {
			throw new \Exception('高级代理直属上级必须是会员！');
		}
		if($post['agent_type'] == 3 && !$this->where(array('agent_number'=>$parent_number, 'agent_type'=>2))->find()) {
			throw new \Exception('代理直属上级必须是高级代理！');
		}
	}

	//验证个人资料
	protected function checkInfomation($account_type) {
		$post = I('post.');
		if(!$account_type) {
			throw new \Exception('账户类型必须！');
		}
		if($post['account_type'] == 2) {//账户类型为公司
			if(!$post['person_name']) {
				throw new \Exception('联络人姓名必须！');
			}
			if(!$post['company_name']) {
				throw new \Exception('公司名称必须！');
			}
			if(!$post['company_phone']) {
				throw new \Exception('公司电话必须！');
			}
		}
		if($post['account_type'] == 1) {//账户类型为个人
			if(!$post['person_name']) {
				throw new \Exception('姓名必须！');
			}
		}
	}

	//新增代理时检查分成
	protected function fcAddCheck() {
		$post = I('post.');
		$m = D('Common/YjAgent');	
		$FX_AGENT1_JY_MAX  = D('Common/YjConfig')->get('FX_AGENT1_JY_MAX');
		$FX_AGENT1_RG_MAX  = D('Common/YjConfig')->get('FX_AGENT1_RG_MAX');
		$FFX_AGENT1_TH_MAX  = D('Common/YjConfig')->get('FX_AGENT1_TH_MAX');
		$post['ratio_tihuo'] = (float) $post['ratio_tihuo'];
		$post['ratio_fashou'] = (float) $post['ratio_fashou'];
		$post['ratio_jiaoyi'] = (float) $post['ratio_jiaoyi'];
		if(I('post.type') != 1) {
			$parent = $m->getByNumber($post['parent_number'],'ratio_tihuo,ratio_fashou,ratio_jiaoyi');
			$post['parent_id'] = $parent['id'];
			$tree = $m->idTree($parent['id']);
			$post['id_tree'] = implode(',', $tree);
			
			if($post['ratio_tihuo']>$parent['ratio_tihuo']) {
				throw new \Exception('提货分成超过上级');
			}
			if($post['ratio_fashou']>$parent['ratio_fashou']) {
				throw new \Exception('发售分成超过上级');
			}
			if($post['ratio_jiaoyi']>$parent['ratio_jiaoyi']) {
				throw new \Exception('交易分成超过上级');
			}
			
		} else {
			if($post['ratio_tihuo']>$FFX_AGENT1_TH_MAX) {
				throw new \Exception('提货分成超过限制');
			}
			if($post['ratio_fashou']>$FX_AGENT1_RG_MAX) {
				throw new \Exception('发售分成超过限制');
			}
			if($post['ratio_jiaoyi']>$FX_AGENT1_JY_MAX) {
				throw new \Exception('交易分成超过限制');
			}
		}		
	}

	//修改代理时检查分成
	protected function fcEditCheck() {
		$post = I('post.');
		$m = D('Common/YjAgent');
		//判断分成是否符合条件
		if(I('post.type') != 1) {
			$parent = $m->getByNumber($post['parent_number'],'ratio_tihuo,ratio_fashou,ratio_jiaoyi');
			if($post['type'] == 2) {
				$ratio_tihuo_max = $m->field('ratio_tihuo')->where(array('parent_number'=>$post['number']))->order(array('ratio_tihuo'=>'desc'))->find();//此高级代理下所有代理中提货分成最大的
				$ratio_fashou_max = $m->field('ratio_fashou')->where(array('parent_number'=>$post['number']))->order(array('ratio_fashou'=>'desc'))->find();//此高级代理下所有代理中发售分成最大的
				$ratio_jiaoyi_max = $m->field('ratio_jiaoyi')->where(array('parent_number'=>$post['number']))->order(array('ratio_jiaoyi'=>'desc'))->find();//此高级代理下所有代理中交易分成最大的
				
				if($ratio_tihuo_max['ratio_tihuo'] > $post['ratio_tihuo']) {
					throw new \Exception('高级代理提货分成不能小于代理');
				}
				if($ratio_fashou_max['ratio_fashou'] > $post['ratio_fashou']) {
					throw new \Exception('高级代理发售分成不能小于代理');
				}
				if($ratio_jiaoyi_max['ratio_jiaoyi'] > $post['ratio_jiaoyi']) {
					throw new \Exception('高级代理交易分成不能小于代理');
				}
			}
			
			if($post['ratio_tihuo']>$parent['ratio_tihuo']) {
				throw new \Exception('提货分成超过限制');
			}
			if($post['ratio_fashou']>$parent['ratio_fashou']) {
				throw new \Exception('发售分成超过限制');
			}
			if($post['ratio_jiaoyi']>$parent['ratio_jiaoyi']) {
				throw new \Exception('交易分成超过限制');
			}					
		} else {
			$ratio_tihuo_max = $m->field('ratio_tihuo')->where(array('parent_number'=>$post['number']))->order(array('ratio_tihuo'=>'desc'))->find();//此会员下所有高级代理中提货分成最大的
			$ratio_fashou_max = $m->field('ratio_fashou')->where(array('parent_number'=>$post['number']))->order(array('ratio_fashou'=>'desc'))->find();//此会员下所有高级代理中发售分成最大的
			$ratio_jiaoyi_max = $m->field('ratio_jiaoyi')->where(array('parent_number'=>$post['number']))->order(array('ratio_jiaoyi'=>'desc'))->find();//此会员下所有高级代理中交易分成最大的
			$FX_AGENT1_JY_MAX  = D('Common/YjConfig')->get('FX_AGENT1_JY_MAX');
			$FX_AGENT1_RG_MAX  = D('Common/YjConfig')->get('FX_AGENT1_RG_MAX');
			$FFX_AGENT1_TH_MAX  = D('Common/YjConfig')->get('FX_AGENT1_TH_MAX');
			if($post['ratio_tihuo']>$FFX_AGENT1_TH_MAX) {
				throw new \Exception('提货分成超过限制');
			}
			if($ratio_tihuo_max['ratio_tihuo'] > $post['ratio_tihuo']) {
				throw new \Exception('会员提货分成不能小于高级代理');
			}
			if($post['ratio_fashou']>$FX_AGENT1_RG_MAX) {
				throw new \Exception('发售分成超过限制');
			}
			if($ratio_fashou_max['ratio_fashou'] > $post['ratio_fashou']) {
				throw new \Exception('会员发售分成不能小于高级代理');
			}
			if($post['ratio_jiaoyi']>$FX_AGENT1_JY_MAX) {
				throw new \Exception('交易分成超过限制');
			}
			if($ratio_jiaoyi_max['ratio_jiaoyi'] > $post['ratio_jiaoyi']) {
				throw new \Exception('会员交易分成不能小于高级代理');
			}
		}
	}
}