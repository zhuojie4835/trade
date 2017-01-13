<?php
namespace Admin\Controller;

/**
 * 资金流水管理控制器
 * @author zhuojie
 */
class FollowController extends AdminController {
	#列表
	public function index() {
		$map = array('id'=>array('gt',0));
		I('customer_mobile') && $map['customer_mobile'] = I('customer_mobile');
		
		$model = D('Common/Follow');		
        $list   = $this->lists($model,$map);
		foreach($list as $k=>$v) {
			$list[$k]['follow_type_text'] = $model->_follow_type_val[$list[$k]['follow_type']];
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