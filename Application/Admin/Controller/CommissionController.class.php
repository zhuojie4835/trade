<?php
namespace Admin\Controller;

/**
 * 交易分成管理控制器
 * @author zhuojie
 */
class CommissionController extends AdminController {
	public function customer() {
		$map = array('id'=>array('gt',0));
		I('login_name') && $map['customer_mobile'] = I('login_name');
		I('operator_number') && $map['operator_number'] = I('operator_number');
		I('agent_number') && $map['agent_number'] = I('agent_number');
		I('name') && $map['name'] = array('like','%'.I('name').'%');

		$model = D('Common/Commission1');	
        $list   = $this->lists($model,$map);
		$redis = getRedis();
		$customer_model = D('Common/Customer');
		foreach($list as $k=>$v) {
			$list[$k]['user_type_text'] = $customer_model->_user_type_val[$list[$k]['user_type']];
			// $list[$k]['agent_type_text'] = $model->_agent_type_val[$v['agent_type']];
			// $list[$k]['operator_number_text'] = $all_operator[$v['operator_number']];
		}

		$this->meta_title = '客户分成列表';
		$this->assign('list', $list);
		$operators = D('Common/Operator')->field('operator_number,company_name')->select();
		$this->assign('operators', $operators);
		$this->display();
	}

	public function agent() {

		$this->meta_title = '代理分成列表';
		$this->assign('list', $list);
		$this->display();
	}

	public function jys() {

		$this->meta_title = '交易所分成列表';
		$this->assign('list', $list);
		$this->display();
	}
}