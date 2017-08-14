<?php 
namespace Customer\Controller;
use Think\Controller;

class Test1Controller extends Controller {
	public function index() {
		$rslog_model = M('ResourceLog','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_sale_new#utf8');
		$rs_model = M('Resource','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_sale_new#utf8');
		// $rs_model = M('Resource','t_');

		$start = strtotime('2017-07-06 23:45:0');
		$end = strtotime('2017-07-07 23:55:0');
		$fisrt_assign_ime = strtotime('2017-07-04 0:0:0');
		
		$list = $rslog_model->where('opt="chehui" and crt_time>'.$start.' and crt_time<'.$end)->getField('rsid',true);// 7-6号23:50撤回数据
		$rsid_str = implode(',',$list);
		$data = array();
		$table = '<table>';
		$rs_list = $rs_model->where('id in('.$rsid_str.')')->select();
		$rs_id_arr = array();
		$rs_id_eavluate_Y_arr = array();
		$count = 0;
		$count_evaluate_Y = 0;
		
		foreach ($rs_list as $k=>$v) 
		{

			if( $v['first_assign_time']>$fisrt_assign_ime 
				&& $v['crt_time']<strtotime('2017-07-07 0:0:0') 
				&& in_array($v['source'],array('MSG','ZX','HR')) 
				&& $v['crt_time']>strtotime('2017-07-04 0:0:0')
				&& $v['evaluate_time']==0
				) 
			{
				$count++;
				// if($v['evaluate_time']>0) {
				// 	$count_evaluate_Y++;
				// 	$rs_id_eavluate_Y_arr[] = $v['id'];
				// }
				
				// $table .= '<tr><td>';
				// $table .= json_encode($v);
				// $table .= '</td></tr>';
				$rs_id_arr[] = $v['id'];
				$return_data = $this->handle($v);
				#更新数据
				// $return_data['callman_expired_time'] = date('Y-m-d H:i:s',$return_data['callman_expired_time']);
				var_dump($return_data);
				echo '<br><br>';
				// $rs_model->where(array('id'=>$v['id']))->save($return_data);
				// dump_log($return_data);
				// if($count>20) 
				// {
				// 	die;
				// }
			}
		}
		// var_dump($rs_id_arr);
		// $table .= '</table>';

		// error_log(implode(',',$rs_id_arr),3,'rsid_log.txt');
		// error_log(implode(',',$rs_id_eavluate_Y_arr),3,'rs_id_eavluate_Y_log.txt');
		echo 'all='.$count.' 已评价='.$count_evaluate_Y;
		
		
	}

	public function handle($v) {
		$rslog_model = M('ResourceLog','t_');
		// $admin_model = M('Admin','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_sale_new#utf8');
		$admin_model = M('Admin','t_');

		#1.把is_evaluate改成N,evaluate_result改成''
		$data= array();
		// $data['id'] = $v['id'];
		$data['is_evaluate'] = 'N';
		$data['evaluate_result'] = '';
		#2.把callmanid、callgrpid改成first_callmanid、first_callgrpid
		$rs_last_log = $rslog_model->where(array('opt'=>'assign','rsid'=>$v['id']))->order('id desc')->find();
		$before_admin_name = str_replace('分配了一条数据给','',$rs_last_log['opt_txt']);
		$before_username = explode('/',$before_admin_name);
        $before_username = $before_username[0];
        $before_callmanid = $admin_model->where(array('account_263'=>$before_username,'roleid'=>154))->find();
		
		$data['callmanid'] = $before_callmanid['adminid'];
		$data['callgrpid'] = $before_callmanid['callgrpid'];

		#3.设置数据过期时间
		$first_assign_time_22 = strtotime( date("Y-m-d 22:00:00",$rs_last_log['crt_time']) );
		$data['first_assign_time'] = $rs_last_log['crt_time'];
		// var_dump(date("Y-m-d H:i:s",$rs_last_log['crt_time']),date("Y-m-d H:i:s",$first_assign_time_22 + 4*3600*24));
		// echo '<br><br>';
        $data['callman_expired_time'] = $first_assign_time_22 + 4*3600*24;
		#4.把type从2改成1
		$data['type'] = 1;
		#5.修改级别
		if(in_array($v['source'],array('HR','ZX'))) {
			$data['rating'] = 1;
		} else {
			$data['rating'] = 2;
		}

		return $data;
	}

	public function test() {
		$rs_model = M('Resource','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_sale_new#utf8');
		$start = strtotime('2017-08-9 0:0:0');
		$end = strtotime('2017-08-9 23:59:59');

		$list = $rs_model->where('evaluate_time>'.$start.' and evaluate_time<'.$end.' and evaluate_result="Y"')->select();
		$count = $rs_model->where('evaluate_time>'.$start.' and evaluate_time<'.$end.' and evaluate_result="Y"')->count();
		$id = array();
		foreach($list as $v) {
			$id[] = $v['id'];
		}

		error_log('8-10记录0809:count='.$count.' id='.implode(',',$id)."\n\n",3,'evaluate_log.txt');
		// var_dump($rs_model->where('evaluate_time>'.$start.' and evaluate_time<'.$end.' and evaluate_result="Y"')->count());
	}

	public function test1() {
		$model1 = M('SpreadWebsite','t_');
		$model2 = M('SpreadAccount','t_');

		$websites = $model1->where(array('legion_id'=>0))->select();
		foreach ($websites as $k=>$v) {
			$account = $model2->where(array('id'=>$v['spread_account_id']))->find();
			if($account['legion_id']>0) {
				$model1->where(array('spread_account_id'=>$v['spread_account_id']))->save(array('legion_id'=>$account['legion_id']));
			}
		}

		var_dump($websites);
	}

	// 分配计划加推广账户
	public function add_account() {
		$engine_model = M('AssignEngine','t_');
		$assign_resource = M('AssignEngineResource','t_');
		$account_model = M('SpreadAccount','t_');

		$all_accounts = $account_model->select();
		$all_engines = $engine_model->field('id,')->select();
		var_dump($all_engines);die;

		$sz1 = array(
			'name'=>'深圳1区'
		);
	}

	// 微信模板消息
	public function wx_push() {
		try {
			dump_log('推送开始开始','wx_push.txt');
			$access_token = get_access_token();
			$member_classnu_model = M('MemberClassnuData','t_');
			$kecheng_model = M('Zhibokecheng','t_');
			$kecheng_del_model = M('MemberDelKecheng','t_');
			$weixin_model = M('WeixinPhone','t_');
			$member = M('Member','t_');
			$wx_push_log_model = M('WxPushLog','t_');

			$where_kc = '`start`>1501758000 AND `start`<1501765200  AND type=1 AND deleted=0';
			$kecheng_infos = $kecheng_model->where($where_kc)->select();// 所有今天上课的课程信息
			
			if($kecheng_infos) {
				$classnu_ids = array_column($kecheng_infos,'banhaoid');// 今天有课的班号
				$sql = 'SELECT userid,username,classnuid,m.nickname FROM t_member_classnu_data mcd  LEFT JOIN t_member m ON m.phone=mcd.username WHERE classnuid '.
					'in('.implode(',',$classnu_ids).') '.
					'AND userid in(SELECT m.id FROM t_weixin_phone wp LEFT JOIN t_member m ON wp.phone=m.phone AND m.phone IS NOT NULL)';
				$member_classnu_infos = M()->query($sql);// 所有今天有课的学员以及他们的班号
				$member_ids = array_column($member_classnu_infos,'userid');
				$member_ids_unique = array_unique($member_ids);// 去掉重复的userid
				
				$delete_kecheng_infos = $kecheng_del_model->field('userid,username,classnuid,kcid')
										->where('userid in('.implode(',',$member_ids_unique).') and classnuid in('.implode(',',$classnu_ids).')')
										->select();// 所有学员被删除的课程

				$result_arr = array();
				$count = 0;
				foreach ($member_classnu_infos as $k=>$v) {
					$result_list = $kecheng_model->where($where_kc.' and banhaoid='.$v['classnuid'])->select();
					foreach ($result_list as $k1 =>$v1) {
					 	$item = array('userid'=>$v['userid'],'username'=>$v['username'],'classnuid'=>$v['classnuid'],'kcid'=>$v1['id']);
					 	if(!in_array($item, $delete_kecheng_infos)) {
							if($result_arr[$v['username']]) {
								$result_arr[$v['username']]['count'] += 1;
								$result_arr[$v['username']]['kecheng_str'] .= $result_arr[$v['username']]['count'].'、'.date('H:i',$v1['start'])
																				.'-'.date('H:i',$v1['end']).'   '.$v1['title'].'\n';
							} else {
								$count++;
								$result_arr[$v['username']] = array(
									'count'=>$count,
									'kecheng_str'=>'1、'.date('H:i',$v1['start']).'-'.date('H:i',$v1['end']).'   '.$v1['title'].'\n',
									'nickname'=>$v['nickname'],
									'userid'=>$v['userid'],
									'username'=>$v['username'],
									'kcid'=>$v1['id']
								);
							}
						}
					} 
				}
				
				foreach ($result_arr as $k=>$v) {
					$data = array(
			            'first'=>array('value'=>urlencode('亲爱的'.$v['nickname'].'同学，'.date('Y年m月d日').'有'.$v['count'].'节直播课，请提前做好课前准备！\n'),'color'=>"#B2B2B2"),
			            'keyword1'=>array('value'=>urlencode(date('Y年m月d日').' 星期'.mb_substr( "日一二三四五六",date("w"),1,"utf-8" ).'\n'.$v['kecheng_str']),'color'=>'#B2B2B2'),
			            'keyword2'=>array('value'=>urlencode($v['nickname']),'color'=>'#B2B2B2'),
			            'remark'=>array('value'=>urlencode(''),'color'=>'#B2B2B2'),
			        );

			        $template = array(
				        'touser'=>'oAEZhs7GFH9XgT-iNcC0PJ7Vkrjo',
				        'template_id'=>C('TMP_ID'),
				        'url'=>'http://wap.i.juhezaixian.com/index.php/Live/zb/user_id/'.$v['userid'],
				        'topcolor'=>'#7B68EE',
				        'data'=>$data
				    );

				    $json_template = json_encode($template);
				    $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
				    $result_json = request_post($url, urldecode($json_template));
				    
				    $result_arr = json_decode($result_json,true);
				    $log_data = array(
				    	'userid'=>$v['userid'],
						'username'=>$v['username'],
						'kcid'=>$v['kcid'],
						'count'=>$v['count'],
						'create_at'=>time()
				    );
				    if($result_arr['errcode'] == 0) {// 成功
				    	$log_data['status'] = 1;
				    } else {// 失败
				    	$log_data['error_code'] = $result_arr['errcode'];
				    }
				    
				    $where_log = array(
				    	'userid'=>$v['userid'],
						'username'=>$v['username'],
						'kcid'=>$v['kcid'],
						'status'=>1
				    );
				    if(!$wx_push_log_model->where($where_log)->find()) {
				    	$wx_push_log_model->add($log_data);
				    }
				    // var_dump(222);die;
				}

				dump_log('推送结束 推送人数='.count($result_arr),'wx_push.txt');
			} else {
				throw new \Exception('没有课程推送');
			}
			
		} catch (\Exception $e) {
			dump_log($e->getMessage(),'wx_push.txt');
		}
		
		
	}
}