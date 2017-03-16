<?php
namespace Pc\Controller;
use Think\Controller;

class HotclassController extends Controller {
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

		$all_project = map($project_model->field('id,name')->where(array('sale_status'=>'Y'))->select(),'id','name');
		$all_grade = map($grade_model->field('id,name')->where(array('sale_status'=>'Y'))->select(),'id','name');
		$all_major = map($major_model->field('id,name')->select(),'id','name');

		$count = $class_model->where($map)->count();
		$page = new \Think\Page($count,2);
		
        // $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		$page->setConfig('prev','上一页');
		$page->setConfig('next','下一页');
		$show = $page->show();
		$all_class = $class_model->where($map)->limit($page->firstRow.','.$page->listRows)->select();
		
		foreach ($all_class as $k=>$v) {
			$all_class[$k]['prjid_text'] = $all_project[$v['prjid']];
			$all_class[$k]['majorid_text'] = $all_major[$v['majorid']];;
			$all_class[$k]['pgradeid_text'] = $all_grade[$v['pgradeid']];
			$all_class[$k]['basic_price'] = sprintf('%.0f',$v['basic_price']);
			$all_class[$k]['sale_price'] = sprintf('%.0f',$v['sale_price']);
			$all_class[$k]['subsidy_price'] = sprintf('%.0f',$v['subsidy_price']);
		}
		// var_dump($all_class[0]);die;
		$this->assign('page',$show);
		$this->assign('totalpage',$page->totalPages);
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
		if(!(int)$id || !$info = M('ClassType','t_','mysqli://root:root@192.168.30.83/zaixian#utf8')->find($id)) {
			$this->error('课程不存在');
		}

		$info['basic_price'] = sprintf('%.0f',$info['basic_price']);
		$info['sale_price'] = sprintf('%.0f',$info['sale_price']);
		$info['subsidy_price'] = sprintf('%.0f',$info['subsidy_price']);
		$this->meta_title = '购买课程';
		$this->assign('info',$info);
		$this->display();
	}
}