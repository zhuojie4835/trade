<?php
namespace Admin\Controller;


class SettingsController extends AdminController {
	public function add() {
		$model = D('Common/Settings');
		$_type_val = $model->_type_val;
		$_status_val = $model->_status_val;
		
		if(IS_POST) {
			$redis = getRedis();
			$post = I('post.');
			try {
				if(!$model->create($post,4)) {
					throw new \Exception($model->getError());
				}
				$post['last_time'] = time();
				$post['admin_id'] = UID;
				$post['status'] = 1;
				
				$model->add($post);
				$redis->hset('settings',$post['key'],$post['value']);
				$this->success('操作成功');
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}
		$this->meta_title = '添加配置';
		$this->assign('type_val',$_type_val);
		$this->assign('status_val',$_status_val);
		$this->display();
	}
	
	public function edit($id) {
		!$id && $this->error('参数错误');
		$model = D('Common/Settings');

		if(!$info = $model->find($id)) {
			$this->error('参数错误');
		}
		$_type_val = $model->_type_val;
		$_status_val = $model->_status_val;
		
		if(IS_POST) {
			$redis = getRedis();
			$post = I('post.');
			try {
				if(!$model->create($post,5)) {
					throw new \Exception($model->getError());
				}
				$post['last_time'] = time();
				
				$model->save($post);
				$redis->hset('settings',$post['key'],$post['value']);
				$post['status'] == 2 && $redis->hdel('settings',$post['key']);
				$this->success('操作成功');
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}
		
		$this->meta_title = '修改配置';
		$this->is_view = 1;
		$this->assign('type_val',$_type_val);
		$this->assign('info',$info);
		$this->assign('status_val',$_status_val);
		$this->display('add');
	}
	
	public function del($id) {
		!$id && $this->error('参数错误');
		$model = D('Common/Settings');

		$model->delete($id);
		$this->success('操作成功');
	}
	
	public function common() {
		$map = array('type'=>1);
		$model = D('Common/Settings');		
        $list   = $this->lists($model,$map);
		int_to_string($list,array('status'=>$model->_status_val));
		
		$this->meta_title = '通用设置';
		$this->assign('list',$list);
		$this->display();
	}
	
	public function trade() {
		$map = array('type'=>2);
		I('key') && $map['key'] = array('like','%'.I('key').'%');
		$model = D('Common/Settings');		
        $list   = $this->lists($model,$map);
		int_to_string($list,array('status'=>$model->_status_val));
		
		$this->meta_title = '交易设置';
		$this->assign('list',$list);
		$this->display();
	}

	#假期设置
	public function notrade() {
		$model = D('Common/Notrade');		
        $list   = $this->lists($model,$map);
		int_to_string($list,array('status'=>$model->_status_val));

		$this->meta_title = '假期设置';
		$this->assign('list',$list);
		$this->display();
	}

	#添加假期
	public function addnotrade() {
		$model = D('Common/Notrade');
		$_status_val = $model->_status_val;

		if(IS_POST) {
			$redis = getRedis();
			$post = I('post.');
			try {
				if(!$model->create($post,4)) {
					throw new \Exception($model->getError());
				}
				$post['last_time'] = time();
				$post['admin_id'] = UID;
				$post['status'] = 1;
				$post['datetime'] = $_POST['datetime'];
				
				$model->add($post);
				$redis->sadd('notrade',date('Ymd',$post['datetime']));
				$this->success('操作成功',U('notrade'));
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}
		$this->meta_title = '添加假期';
		$this->assign('status_val',$_status_val);
		$this->display();
	}

	#禁用假期
	public function stop($id=null) {
		!$id && $this->error('参数错误');
		$model = D('Common/Notrade');
		if(!$info = $model->find($id)) {
			$this->error('数据不存在');
		}
		$redis = getRedis();
		if($info['status'] == 1) {
			$save_data = array('status'=>2);
			$redis->srem('notrade',date('Ymd',$info['datetime']));
		} else {
			$save_data = array('status'=>1);
			$redis->sadd('notrade',date('Ymd',$info['datetime']));
		}
		$model->where(array('id'=>$id))->save($save_data);
		$this->success('操作成功');
	}
}
