<?php
namespace Admin\Controller;

/**
 * 代理商管理控制器
 * @author zhuojie
 */
class AgentController extends AdminController {
	#代理商列表
	public function index() {
		$map = array();
		$model = D('Common/Agent');	
        $list   = $this->lists($model,$map);
		$all_operator = D('Common/Operator')->field('operator_number,company_name')->where(array('status'=>1))->select();
		$all_operator = map($all_operator,'operator_number','company_name');
		$redis = getRedis();
		foreach($list as $k=>$v) {
			$list[$k]['status_text'] = $model->_status_val[$v['status']];
			$list[$k]['agent_type_text'] = $model->_agent_type_val[$v['agent_type']];
			$list[$k]['operator_number_text'] = $all_operator[$v['operator_number']];
		}
		
		$this->meta_title = '代理商列表';
		$this->assign('list', $list);
		$this->display();
	}
	
	#新增
	public function add() {
		$model = D('Common/Agent');
		
		if(IS_POST) {
			try {
				$post = I('post.');
				if(!$model->create($post,4)) {
					throw new \Exception($model->getError());
				}
				$post['create_time'] = time();
				$post['admin_id'] = UID;
				if($post['agent_type'] != 1) {
					$parent = $model->where(array('agent_number'=>$post['parent_number']))->find();
					$operator = D('Common/Operator')->field('operator_number')->where(array('operator_number'=>$parent['operator_number']))->find();
					$post['operator_number'] = $operator['operator_number'];
					$post['parent_id'] = $parent['id'];
					$post['parent_number'] = $parent['agent_number'];
					$post['agent_type'] == 2 && $post['agent_member_number'] = $parent['agent_number'];
					$post['agent_type'] == 3 && $post['agent_member_number'] = $parent['parent_number'];
					$tree = $model->idTree($parent['id']);
					$post['id_tree'] = implode(',', $tree);
				} else {
					$operator = D('Common/Operator')->field('operator_number')->where(array('operator_number'=>$post['parent_number']))->find();
					$post['operator_number'] = $operator['operator_number'];
					$post['agent_member_number'] = $post['agent_number'];
				}
				$model->add($post);
				$this->success('添加成功',U('index'));
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}
		
		$this->meta_title = '新增代理商';
		$this->assign('agent_type',$model->_agent_type_val);
		$this->display();
	}
	
	#修改
	public function edit($id=null) {
		!$id && $this->error('参数错误');
		$model = D('Common/Agent');

		if(!$info = $model->find($id)) {
			$this->error('参数错误');
		}
		$info['agent_type_text'] = $model->_agent_type_val[$info['agent_type']]; 

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
		$this->meta_title = '修改代理商';
		$this->display('add');
	}
	
	#查看
	public function view($id=null) {
		$id || $this->error('错误请求');
		$model = D('Common/Agent');
		$info = $model->find($id);
		$info['agent_type_text'] = $model->_agent_type_val[$info['agent_type']]; 
		$this->assign('info',$info);
		$this->is_view = 1;
		$this->meta_title = '查看代理商';
		$this->display('add');
	}
}