<?php
namespace Customer\Controller;
use Think\Controller;

class HotclassController extends Controller {
	/*
	热门课程列表
	 */
	public function index() {
		$project_model = M('Project','t_','mysqli://root:root@192.168.30.83/zaixian#utf8');
		$grade_model = M('ProjectGrade','t_','mysqli://root:root@192.168.30.83/zaixian#utf8');
		$class_model = M('ClassType','t_','mysqli://root:root@192.168.30.83/zaixian#utf8');

		$map = array();
		$map['is_release'] = 'Y';
		$all_project = $project_model->where(array('sale_status'=>'Y'))->select();
		$all_grade = $grade_model->where(array('sale_status'=>'Y'))->select();
		$all_class = $class_model->where($map)->select();

		var_dump($all_grade);
	}

	/*
	课程详情
	 */
	public function detail() {

	}
}