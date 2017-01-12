<?php
namespace Admin\Controller;

/**
 * 后台充值管理控制器
 * @author zhuojie
 */
class AdminRechargeController extends AdminController {
	public $customer;

	#列表
	public function index() {
		$map = array();
		$model = D('Common/AdminRecharge');	
		$customer_model = D('Common/Customer');	
        $list   = $this->lists($model,$map);
		foreach($list as $k=>$v) {
			$list[$k]['customer_type_text'] = $customer_model->_user_type_val[$v['customer_type']];
			$list[$k]['status_text'] = $model->_status_val[$v['status']];
		}
		
		$this->meta_title = '充值列表';
		$this->assign('list', $list);
		$this->display();
	}

	#新增
	public function add() {
		if(IS_POST) {
			$model = D('Common/AdminRecharge');
			try {
				if(!$model->create('',4)) {
					throw new \Exception($model->getError());
				}
				$customer = D('Common/Customer')->where(array('login_name'=>$model->customer_mobile))->find();
				$model->create_time = time();
				$model->customer_id = $customer['id'];
				$model->customer_type = $customer['user_type'];
				$model->admin_id = UID;
				$model->ip = get_client_ip();
				$model->status = 1;
				$model->customer_name = $customer['name'];
				$model->add();
				$this->success('添加成功',U('index'));
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}

		$this->display();
	}

	#审核
	public function audit($id=null,$act='') {
		(!$id || !$act) && $this->error('错误请求');

		if(!$info = D('Common/AdminRecharge')->where(array('status'=>1,'id'=>$id))->find()) {
			$this->error('错误请求');
		}
		
		if($act == 'yes') {
			D('Common/AdminRecharge')->where(array('id'=>$info['id']))->save(array('status'=>2));
			$redis = getRedis();
			$customer_in_redis = $redis->hgetall("user:".$info['customer_id']);
			// $new_total_money = $customer_in_redis['total_money']+$info['amount'];
			$new_free_money = $customer_in_redis['free_money']+$info['amount'];
			//更新资金
			$redis->hmset("user:".$info['customer_id'],array('free_money'=>$new_free_money));
			
			//流水
			$follow_arr = array(
				"follow_number"=>generateFollowNumber('T'),
			    "customer_id"=>$info['customer_id'],
			    "customer_mobile"=>$info['customer_mobile'],
			    "customer_name"=>$info['customer_name'],
			    "follow_type"=>1,
				"bussiness_desciption"=>'后台入金',
			    "money"=>$info['amount'],
			    "new_money"=>$new_free_money,
			    "create_time"=>time(),
			);
			$redis->lpush("follow",json_encode($follow_arr));
			$this->success('操作成功！');
		} elseif($act == 'no') {
			D('Common/AdminRecharge')->where(array('id'=>$info['id']))->save(array('status'=>3));
			$this->success('操作成功！');
		} else {
			$this->error('错误请求');
		}
	}
}