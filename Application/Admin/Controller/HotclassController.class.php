<?php
namespace Admin\Controller;

class HotclassController extends AdminController {
	/*
	热门课程列表
	 */
	public function index() {
		$project_model = M('Project','t_','mysqli://root:root@192.168.30.83/zaixian#utf8');
		$grade_model = M('ProjectGrade','t_','mysqli://root:root@192.168.30.83/zaixian#utf8');
		$class_model = M('ClassType','t_','mysqli://root:root@192.168.30.83/zaixian#utf8');
		$major_model = M('Major','t_','mysqli://root:root@192.168.30.83/zaixian#utf8');

		$map = array();
		$map['is_release'] = 'Y';
		I('prjid') && $map['prjid'] = I('prjid');
		I('majorid') && $map['majorid'] = I('majorid');
		I('pgradeid') && $map['pgradeid'] = I('pgradeid');

		$all_project = $project_model->where(array('sale_status'=>'Y'))->select();
		$all_grade = $grade_model->where(array('sale_status'=>'Y'))->select();
		$all_major = $major_model->select();

		$count = $class_model->where($map)->count();
		$page = new \Think\Page($count,10);
		$show = $page->show();
		$all_class = $class_model->where($map)->limit($page->firstRow.','.$page->listRows)->select();

		$this->assign('page',$show);
		$this->assign('project',$all_project);
		$this->assign('grade',$all_grade);
		$this->assign('class',$all_class);
		$this->assign('major',$all_major);
		$this->meta_title = '热门课程';
		$this->display();
		
	}

	/*
	课程详情
	 */
	public function detail($id) {
		if(!(int)$id || !$info = M('Major','t_','mysqli://root:root@192.168.30.83/zaixian#utf8')->find($id)) {
			$this->error
		}
		$this->display();
	}
}