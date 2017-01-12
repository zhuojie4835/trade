<?php
namespace Admin\Controller;

/**
 * 运营中心管理控制器
 * @author zhuojie
 */
class OperatorController extends AdminController {
	#运营中心列表
	public function index() {
		$map = array();
		$model = D('Common/Operator');		
        $list   = $this->lists($model,$map);
		foreach($list as $k=>$v) {
			$list[$k]['status_text'] = $model->_status_val[$v['status']];
		}
		
		$this->meta_title = '运营中心列表';
		$this->assign('list', $list);
		$this->display();
	}
	
	#新增
	public function add() {
		if(IS_POST) {
			$model = D('Common/Operator');
			try {
				$post = I('post.');
				if(!$model->create($post,4)) {
					throw new \Exception($model->getError());
				}
				$post['create_time'] = time();
				$post['admin_id'] = UID;
				$model->add($post);
				$this->success('添加成功',U('index'));
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}
		$this->meta_title = '新增运营中心';
		$this->display();
	}

	#查看
	public function view($id) {
		!$id && $this->error('参数错误');
		$info = D('Common/Operator')->find($id);
		$this->assign('info',$info);
		$this->is_view = 1;
		$this->meta_title = '查看运营中心';
		$this->display('add');
	}

	#修改
	public function edit($id=null) {
		!$id && $this->error('参数错误');
		$model = D('Common/Operator');

		if(!$info = $model->find($id)) {
			$this->error('参数错误');
		}

		if(IS_POST) {
			try {
				if(!$model->create('',5)) {
					throw new \Exception($model->getError());
				}
				$model->update_time = time();
				$model->save();
				$this->success('修改成功',U('index'));
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}

		$this->assign('info',$info);
		$this->is_edit = 1;
		$this->meta_title = '修改运营中心';
		$this->display('add');
	}
}