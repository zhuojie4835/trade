<?php
namespace Admin\Controller;

/**
 * 商品管理控制器
 * @author zhuojie
 */
class ProductController extends AdminController {
	#列表
	public function index() {
		$map = array();
		$model = D('Common/Product');		
        $list   = $this->lists($model,$map);
        $redis = getRedis();
		foreach($list as $k=>$v) {
			$product_trade = getProductTradeInfo($v['id']);
			$list[$k]['status_text'] = $model->_status_val[$v['status']];
			$list[$k]['issue_type_text'] = $model->_issue_type_val[$v['issue_type']];
			$list[$k]['industry_text'] = $model->_industry_val[$v['industry']];
			$list[$k]['subscribe_number'] = (int)($v['issue_number']-$product_trade['left_number']);
			$list[$k]['th_number'] = (int)$product_trade['th_number'];
		}
		
		$this->meta_title = '运营中心列表';
		$this->assign('list', $list);
		$this->display();
	}
	
	#新增
	public function add() {
		$model = D('Common/Product');
		if(IS_POST) {
			try {
				$post = I('post.');
				if(!$model->create('',4)) {
					throw new \Exception($model->getError());
				}
				$model->create_time = time();
				$model->sub_end_time = strtotime($model->sub_end_time);
				$insert_id = $model->add();
				$product_info = $model->find($insert_id);
				$product_in_redis = array(
					'id'=>$insert_id,
					'product_info'=>serialize($product_info)
				);
				$redis = getRedis();
				$redis->hmset('product:' . $product_info['id'],$product_in_redis);
				$this->success('添加成功',U('index'));
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}
		$this->meta_title = '新增产品';
		$this->assign('_status_val',$model->_status_val);
		$this->assign('_industry_val',$model->_industry_val);
		$this->assign('_issue_type_val',$model->_issue_type_val);
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
		$model = D('Common/Product');

		if(!$info = $model->find($id)) {
			$this->error('参数错误');
		}

		if(IS_POST) {
			try {
				if(!$model->create('',5)) {
					throw new \Exception($model->getError());
				}
				$model->update_time = time();
				$model->sub_end_time = strtotime($model->sub_end_time);
				$model->save();
				$product_info = $model->find($model->id);
				
				$product_in_redis = array(
					'id'=>$model->id,
					'product_info'=>serialize($product_info)
				);
				$redis = getRedis();
				if($redis->exists('product_trade:'.$product_info['id'])) {
					$redis->hmset('product_trade:'.$product_info['id'],array('status'=>$product_info['status']));//产品状态
				}
				$redis->hmset('product:' . $product_info['id'],$product_in_redis);
				$this->success('修改成功',U('index'));
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
		}

		$this->assign('info',$info);
		$this->is_edit = 1;
		$this->assign('_status_val',$model->_status_val);
		$this->assign('_industry_val',$model->_industry_val);
		$this->assign('_issue_type_val',$model->_issue_type_val);
		$this->meta_title = '修改商品';
		$this->display('add');
	}
}