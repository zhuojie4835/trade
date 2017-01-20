<?php
namespace Customer\Controller;


class IndexController extends BaseController {
	#首页
	public function index() {
		$redis = getRedis();
		$keys = $redis->keys('product:*');
		
		$_status_val = D('Common/Product')->_status_val;
		$list = array();
		foreach($keys as $k=>$v) {
			$data = $redis->hgetall($v);
			$data = unserialize($data['product_info']);
			$data['status_text'] = $_status_val[$data['status']];
			$list[] = $data;
		}
		
		$this->assign('active',1);
		$this->assign('list',$list);
		$this->display();
	}

	#产品详情
	public function product($id) {
		$redis = getRedis();
		if(!$id || (!$info = $redis->hgetall('product:' . $id))) {
			$this->error('参数错误');
		}
		
		$info = unserialize($info['product_info']);
		$product_trade_info = getProductTradeInfo($id);
		$info['left_number'] = $product_trade_info['left_number'];
		// var_dump($info[status]);die;
		$this->assign('info',$info);
		$this->display();
	}

	#认购页面
	public function subscribe($id) {
		$redis = getRedis();
		if(!$id || (!$info = $redis->hgetall('product:' . $id))) {
			$this->error('参数错误');
		}

		$info = unserialize($info['product_info']);
		if($info['status'] != 2) {
			$this->redirect('index');
		}

		$this->assign('info',$info);
		$this->display();
	}
	
	#交易期
	public function transaction($id) {
		$redis = getRedis();
		if(!$id || (!$info = $redis->hgetall('product:' . $id))) {
			$this->error('参数错误');
		}
		
		$info = unserialize($info['product_info']);
		if($info['status'] != 3) {
			$this->redirect('index');
		}
		$is_login = session('uid') ? 1 : 0;
		$this->assign('is_login',$is_login);
		$this->display();
	}

	#注册
	public function register() {
		if($this->isLogin()) {
			$this->redirect('user/center');
		}
		if(IS_POST) {
			$model = D('Common/Customer');
			$post = I('post.');
			try {
				if(!$model->create($post,4)) {
					throw new \Exception($model->getError());
				}
				$agent = D('Common/Agent')->field('agent_number,operator_number,agent_member_number,parent_number,agent_type')->where(array('agent_number'=>$post['agent_number']))->find();
				$agent2 = 0;//所属高级代理
				if($agent['agent_type'] == 1) {
					$agent2 = 0;//会员没有高级代理
				} elseif ($agent['agent_type'] == 2) {
					$agent2 = $post['agent_number'];
				} elseif ($agent['agent_type'] == 3) {
					$agent2 = $agent['parent_number'];
				}
				
				$model->agent2 = $agent2;
				$model->parent1 = I('post.parent1');
				$model->parent2 = I('post.parent2');
				$model->create_time = time();
				$model->password = $model::generatePassword($post['password']);
				$model->operator_number = $agent['operator_number'];
				$model->agent_member_number = $agent['agent_member_number'];
				$model->ip = get_client_ip();
				$uid = $model->add();
				$redis = getRedis();
				$customer_in_redis = array(
					'uid'=>$uid,
					'mobile'=>$post['login_name'],
					'name'=>$post['name'],
					'id_card_number'=>$post['id_card_number'],
					'agent_number'=>$post['agent_number'],
					'user_type'=>1,
					'operator_number'=>$agent['operator_number'],
					'agent_member_number'=>$agent['agent_member_number'],
					'register_time'=>time(),
					'free_money'=>50000,
					'freeze_money'=>0,
					'agent2'=>$agent2,
					'parent1'=>I('post.parent1'),
					'parent2'=>I('post.parent2'),
				);
				$follow_info = array(
					"follow_number"=>generateFollowNumber('R'),//注册奖励
					"customer_id"=>$uid,
					"customer_mobile"=>$post['login_name'],
					"customer_name"=>$post['name'],
					"follow_type"=>5,
					"bussiness_desciption"=>'注册奖励',
					"money"=>50000,
					"new_money"=>50000,
					'freeze_money'=>0,
					"create_time"=>time(),
				);
				
				$redis->lpush('follow',json_encode($follow_info));//流水
				$redis->hmset('user:'.$uid,$customer_in_redis);//redis注册
				$this->ajaxReturn(array('status'=>1,'msg'=>'注册成功'));
			} catch (\Exception $e) {
				$this->ajaxReturn(array('status'=>0,'msg'=>$e->getMessage()));
			}
		}
		
		$this->display();
	}
	
	#注册成功页面
	public function registerOk() {
		if($this->isLogin()) {
			$this->redirect('user/center');
		}
		$this->display();
	}
	
	#登录
	public function login() {
		if($this->isLogin()) {
			$this->redirect('user/center');
		}
		if(IS_POST) {
			$model = D('Common/Customer');
			$post = I('post.');
			try {
				if(!$model->create($post,5)) {
					throw new \Exception($model->getError());
				}
				$userinfo = $model->where(array('login_name'=>$post['login_name']))->find();
				$model->where(array('login_name'=>$userinfo['login_name']))->save(array('login_time'=>time()));//更新登录时间
				$redis = getRedis();
				session('uid',$userinfo['id']);
				$redis->setex('expire_'.$userinfo['id'],C('LOGIN_TIMEOUT'),session_id());
				$this->ajaxReturn(array('status'=>1,'msg'=>'登录成功'));
			} catch (\Exception $e) {
				$this->ajaxReturn(array('status'=>0,'msg'=>$e->getMessage()));
			}
		}
		$this->display();
	}
	
	#退出
	public function logout() {
		$redis = getRedis();
		$uid = session('uid');
		$redis->del('expire_'.$uid);
		session('uid',null);
   		$this->redirect('index/login');
	}
	
	#忘记密码
	public function getPwd() {
		if($this->isLogin()) {
			$this->redirect('user/center');
		}
		if(IS_POST) {
			$model = D('Common/Customer');
			$post = I('post.');
			try {
				if(!$model->create($post,6)) {
					throw new \Exception($model->getError());
				}
				$new_password = $model::generatePassword($model->password);
				$model->where(array('login_name'=>$model->login_name))->save(array('password'=>$new_password));
				
				$this->ajaxReturn(array('status'=>1,'msg'=>'找回密码成功','mobile'=>$model->login_name));
			} catch (\Exception $e) {
				$this->ajaxReturn(array('status'=>0,'msg'=>$e->getMessage()));
			}
		}
		$this->display();
	}
	
	#找回密码成功页面
	public function getPwdOk() {
		if($this->isLogin()) {
			$this->redirect('user/center');
		}
		$this->display();
	}

	#更新行情
	public function updateQuota() {
		$pid = I('post.id',0);
		
		$redis = getRedis();
		if(!$product_trade = $redis->hgetall('product_trade:'.$pid)) {
			$this->ajaxReturn(array('status'=>0,'msg'=>'获取数据失败'));
		}
		
		$gd_in_quota = $this->getQuota($now_price,'in',$pid);
		$gd_out_quota = $this->getQuota($now_price,'out',$pid);
		
		$this->ajaxReturn(array('status'=>1,'product_info'=>(array)$product_trade,'gd_in_quota'=>(array)$gd_in_quota,'gd_out_quota'=>(array)$gd_out_quota));
	}

	#获取行情信息
	protected function getQuota($price,$type='in',$pid,$number=5) {
		$redis = getRedis();
		$now_price = $redis->hget('product_trade:'.$pid,'now_price');

		$all_price = $redis->smembers('gd_'.$type.'_price:'.$pid);
		$quto_price = array();
		foreach($all_price as $v) {
			$quto_price[$v] = abs($v-$now_price);
		}

		asort($quto_price);
		$quto_price = array_keys($quto_price);
		$quota = array();
		foreach($quto_price as $v) {
			$quota[] = $redis->hgetall('gd_'.$type.'_price_detail:'.$pid.':'.$v);
		}
		
		$result = array_slice($quota,0,$number);
		
		$sort = array(  
            'direction' => 'SORT_ASC', 
            'field'     => 'price',
        );  
        $result =  arr_sort($sort,$result);
		
		return $result;
	}
	
	#登录验证
	public function checkLogin() {
		if(IS_AJAX && $this->isLogin()) {
			return $this->ajaxReturn(array('status'=>1,'msg'=>'已登录'));
		}
		
		return $this->ajaxReturn(array('status'=>0,'msg'=>'未登录'));
	}
}