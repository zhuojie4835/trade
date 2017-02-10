<?php
namespace Admin\Controller;

/**
 * 客户管理控制器
 * @author zhuojie
 */
class CustomerController extends AdminController {
	#列表
	public function index() {
		$map = array();
		I('login_name') && $map['login_name'] = I('login_name');
		I('operator_number') && $map['operator_number'] = I('operator_number');
		I('agent_number') && $map['agent_number'] = I('agent_number');
		I('name') && $map['name'] = array('like','%'.I('name').'%');

		$model = D('Common/Customer');		
        $list   = $this->lists($model,$map);
        $redis = getRedis();
		foreach($list as $k=>$v) {
			$list[$k]['user_type_text'] = $model->_user_type_val[$list[$k]['user_type']];
			$list[$k]['is_balance_text'] = $model->_balance_val[$list[$k]['is_balance']];
			$list[$k]['funds_status_text'] = $model->_funds_status_val[$list[$k]['funds_status']];
			$online = $redis->get('expire_'.$v['id']) ? 1 : 2;
			$list[$k]['online'] = $model->_online_val[$online];
		}
		
		$operators = D('Common/Operator')->field('operator_number,company_name')->select();
		$this->meta_title = '客户列表';
		$this->assign('list', $list);
		$this->assign('operators', $operators);
		$this->display();
	}

	#查看
	public function view($id) {
		!$id && $this->error('参数错误');
		$info = D('Common/Customer')->find($id);
		$redis = getRedis();
		$asset = $redis->hgetall('user:' . $info['id']);
		$position = array();
		if($position_keys = $redis->keys('position:'.$info['id'].':*')) {
			$product_status_val = D('Common/Product')->_status_val;
			$total_market_value = $total_profit = 0;
			foreach($position_keys as $v) {
				$item = $redis->hgetall($v);
				$item['now_price'] = $redis->hget('product_trade:'.$item['pid'],'now_price');
				$item['profit'] = getFloat(($item['now_price']-$item['average_price'])*$item['volume']);
				$item['status_text'] = $product_status_val[$item['status']];
				$total_market_value += getFloat($item['now_price']*$item['volume']);
				$total_profit += $item['profit'];
				$position[] = $item;
			}
			$this->total_market_value = $total_market_value;
			$this->total_profit = $total_profit;
		}
		$follow_model = D('Common/Follow');		
        $list = $this->lists($follow_model,array('customer_id'=>$id));
		$follow_type_val = $follow_model->_follow_type_val;
		foreach($list as $k=>$v) {
			$list[$k]['follow_type_text'] = $follow_type_val[$v['follow_type']];
		}
		
		$deals_model = D('Common/Deals');
		$deals_list = $deals_model->where(array('customer_id'=>$id))->order('id desc')->limit(100)->select();
		$deals_type_val = $deals_model->_deals_type_val;
		foreach($deals_list as $k=>$v) {
			$deals_list[$k]['deals_type_text'] = $deals_type_val[$v['deals_type']];
		}
		
		$status_val = D('Common/Customer')->_status_val;
		$this->assign('status',$status_val);
		$this->assign('info',$info);
		$this->assign('asset',$asset);
		$this->assign('position',$position);
		$this->assign('follow',$list);
		$this->assign('deals',$deals_list);
		$this->is_view = 1;
		$this->meta_title = '查看客户';
		$this->display('add');
	}

	#绑定代理编号
	public function bind() {
		$id = I('post.id');
		$bind_agent_number = I('post.bind_agent_number');
		$customer = D('Common/Customer')->find($id);
		$agent = D('Common/Agent')->where(array('agent_number'=>$bind_agent_number))->find();
		if(!$customer) {
			$this->ajaxReturn(array('status'=>0,'msg'=>'用户不存在'));
		}
		if(!$agent) {
			$this->ajaxReturn(array('status'=>0,'msg'=>'代理不存在'));
		}
		
		$redis = getRedis();
		if(I('post.act') == 'bind') {
			if($customer['user_type'] != 1) {
				$this->ajaxReturn(array('status'=>0,'msg'=>'用户类型错误'));
			}
			if($customer['bind_agent_number']) {
				$this->ajaxReturn(array('status'=>0,'msg'=>'不能重复绑定'));
			}
			if($agent['bind_customer_id']) {
				$this->ajaxReturn(array('status'=>0,'msg'=>'不能重复绑定'));
			}
			D('Common/Customer')->where(array('id'=>$customer['id']))->save(array('user_type'=>$agent['agent_type']+1,'bind_agent_number'=>$bind_agent_number));
			D('Common/Agent')->where(array('id'=>$agent['id']))->save(array('bind_customer_id'=>$customer['id']));
			$redis->hset('user:'.$customer['id'],'bind_agent_number',$bind_agent_number);
		} elseif(I('post.act') == 'cancel') {
			D('Common/Customer')->where(array('id'=>$customer['id']))->save(array('user_type'=>1,'bind_agent_number'=>0));
			D('Common/Agent')->where(array('id'=>$agent['id']))->save(array('bind_customer_id'=>0));
			$redis->hdel('user:'.$customer['id'],'bind_agent_number');
		}
		$this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'));
	}
	
	#设置用户状态
	public function setstatus() {
		$id = I('post.id',0);
		$status = I('post.status');
		$customer = D('Common/Customer')->find($id);
		if(!$customer) {
			$this->ajaxReturn(array('status'=>0,'msg'=>'用户不存在'));
		}
		
		$redis = getRedis();
		D('Common/Customer')->where(array('id'=>$customer['id']))->save(array('status'=>$status));
		$redis->hset('user:'.$customer['id'],'status',$status);
		
		$this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'));
	}
}