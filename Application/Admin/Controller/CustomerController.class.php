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
		$model = D('Common/Customer');		
        $list   = $this->lists($model,$map);
        $redis = getRedis();
		foreach($list as $k=>$v) {
			$list[$k]['user_type_text'] = $model->_user_type_val[$list[$k]['user_type']];
			$list[$k]['is_balance_text'] = $model->_balance_val[$list[$k]['is_balance']];
			$list[$k]['funds_status_text'] = $model->_funds_status_val[$list[$k]['funds_status']];
			$online = $redis->sismember('login_user',$v['id']) ? 1 : 2;
			$list[$k]['online'] = $model->_online_val[$online];
		}
		
		$this->meta_title = '客户列表';
		$this->assign('list', $list);
		$this->display();
	}

	#查看
	public function view($id) {
		!$id && $this->error('参数错误');
		$info = D('Common/Customer')->find($id);
		$redis = getRedis();
		$asset = $redis->hgetall('user:' . $info['id']);
		$this->assign('info',$info);
		$this->assign('asset',$asset);
		$this->is_view = 1;
		$this->meta_title = '查看客户';
		$this->display('add');
	}
}