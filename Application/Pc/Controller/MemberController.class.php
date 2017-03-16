<?php
namespace Pc\Controller;
use Think\Controller;

class MemberController extends Controller {
	/*
	登录
	 */
	public function login() {
		
		
		$this->meta_title = '登录';
		$this->display();
		
	}

	/*
	注册
	 */
	public function register() {
		$this->meta_title = '注册';
		$this->display();
	}
}