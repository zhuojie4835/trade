<?php
namespace Customer\Controller;


class TransferController extends BaseController {
	public function index() {
		// $redis = getRedis();
		// $keys = $redis->keys('product:*');
		
		// $_status_val = D('Common/Product')->_status_val;
		// $list = array();
		// foreach($keys as $k=>$v) {
			// $data = $redis->hgetall($v);
			// $data = unserialize($data['product_info']);
			// $data['status_text'] = $_status_val[$data['status']];
			// $list[] = $data;
		// }
		
		// $this->assign('active',1);
		// $this->assign('list',$list);
		$this->display();
	}

}