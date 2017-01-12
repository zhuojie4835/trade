<?php
namespace Customer\Controller;


class NoticeController extends BaseController {
	public function index() {

		$this->assign('active',3);
		$this->display();
	}

	public function detail() {

		$this->assign('active',3);
		$this->display();
	}
}