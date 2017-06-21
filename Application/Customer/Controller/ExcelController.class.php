<?php
namespace Customer\Controller;
use Think\Controller;

class ExcelController extends Controller {
	
	public function upload() {
		if(IS_POST) {
			$upload = new \Think\Upload();
		    $upload->maxSize   =     3145728 ;
		    $upload->exts      =     array('xls','xlsx');
		    $upload->rootPath  =     realpath(APP_PATH.'../').'/Uploads/excel/'; 
		    $upload->subName   =     '';  
		    if(!is_dir($upload->rootPath)) {
		    	@mkdir($upload->rootPath,777,true);
		    }
		    $upload->savePath  =     '';
		    $info   =   $upload->upload();
		    if(!$info) {
		        echo '<script>alert("'.$upload->getError().'");</script>';exit();
		    } else {
		    	header("Content-type:text/html;charset=utf-8");
		    	$file_name = $upload->rootPath.$info['file']['savepath'].$info['file']['savename'];
		    	$ym = $_GET['date'] ? $_GET['date'].'-' : '';
		    	!$ym && ($ym = date('Y-m-'));
		    	$ext = pathinfo($file_name,PATHINFO_EXTENSION);
		    	vendor("PHPExcel.PHPExcel");
		    	if($ext == 'xlsx') {
		    		$type = 'Excel2007';
				    $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
				    $objPHPExcel = $objReader->load($file_name,'utf-8');
				} elseif($ext == 'xls') {
					$type = 'Excel5';
				    $objReader = \PHPExcel_IOFactory::createReader('Excel5');
				    $objPHPExcel = $objReader->load($file_name,'utf-8');
				} else {
					die('不支持的文件格式');
				}
				
				$sheetCount = $objPHPExcel->getSheetCount();//获取sheet总数
				$data = array();
				$d = 25569;
				$t = 3600*8;
				for($i=2;$i<$sheetCount;$i++) {
				// for($i=2;$i<3;$i++) {
					$sheet = $objPHPExcel->getSheet($i);
		        	$highestColumn = $sheet->getHighestColumn();//取得总列数
		        	
					for($x=0;$x<3;$x++) {
						if($x == 0) {
							for($d=1;$d<32;$d++) {
								$item = array();
								$item['department'] = $sheet->getCell("B4")->getValue();//部门
								$item['name'] = $sheet->getCell("J4")->getValue();//姓名
								$item['date'] = substr($sheet->getCell("A".(12+$d))->getValue(),0,2);//日期
								$item['time1'] = $sheet->getCell("B".(12+$d))->getValue();
								$item['time2'] = $sheet->getCell("D".(12+$d))->getValue();
								$item['time3'] = $sheet->getCell("G".(12+$d))->getValue();
								$item['time4'] = $sheet->getCell("I".(12+$d))->getValue();
								$item['time5'] = $sheet->getCell("K".(12+$d))->getValue();
								$item['time6'] = $sheet->getCell("M".(12+$d))->getValue();
								if($item['time1'] || $item['time2'] || $item['time3'] || $item['time4'] || $item['time5'] || $item['time6']) {
									$time_data = array($item['time1'],$item['time2'],$item['time3'],$item['time4'],$item['time5'],$item['time6']);
									
									foreach($time_data as $k=>$v) {
										if($v) {
											$item['time'.($k+1)] = \PHPExcel_Shared_Date::ExcelToPHP($v)-$t;
											$item['time'.($k+1)] = date('H:i',$item['time'.($k+1)]);
										} else {
											unset($item['time'.($k+1)]);
										}
									}
									
									$data[] = $item;
								}
								
							}
						} elseif($x == 1) {
							for($d=1;$d<32;$d++) {
								$item = array();
								$item['department'] = $sheet->getCell("Q4")->getValue();//部门
								$item['name'] = $sheet->getCell("Y4")->getValue();//姓名
								$item['date'] = substr($sheet->getCell("P".(12+$d))->getValue(),0,2);//日期
								$item['time1'] = $sheet->getCell("Q".(12+$d))->getValue();
								$item['time2'] = $sheet->getCell("S".(12+$d))->getValue();
								$item['time3'] = $sheet->getCell("V".(12+$d))->getValue();
								$item['time4'] = $sheet->getCell("X".(12+$d))->getValue();
								$item['time5'] = $sheet->getCell("Z".(12+$d))->getValue();
								$item['time6'] = $sheet->getCell("AB".(12+$d))->getValue();
								if($item['time1'] || $item['time2'] || $item['time3'] || $item['time4'] || $item['time5'] || $item['time6']) {
									$time_data = array($item['time1'],$item['time2'],$item['time3'],$item['time4'],$item['time5'],$item['time6']);
									foreach($time_data as $k=>$v) {
										if($v) {
											$item['time'.($k+1)] = \PHPExcel_Shared_Date::ExcelToPHP($v)-$t;
											$item['time'.($k+1)] = date('H:i',$item['time'.($k+1)]);
										} else {
											unset($item['time'.($k+1)]);
										}
									}
									$data[] = $item;
								}
							}
						} elseif ($x == 2) {
							for($d=1;$d<32;$d++) {
								$item = array();
								$item['department'] = $sheet->getCell("AF4")->getValue();//部门
								$item['name'] = $sheet->getCell("AN4")->getValue();//姓名
								$item['date'] = substr($sheet->getCell("AE".(12+$d))->getValue(),0,2);//日期
								$item['time1'] = $sheet->getCell("AF".(12+$d))->getValue();
								$item['time2'] = $sheet->getCell("AH".(12+$d))->getValue();
								$item['time3'] = $sheet->getCell("AK".(12+$d))->getValue();
								$item['time4'] = $sheet->getCell("AM".(12+$d))->getValue();
								$item['time5'] = $sheet->getCell("AO".(12+$d))->getValue();
								$item['time6'] = $sheet->getCell("AQ".(12+$d))->getValue();
								if($item['time1'] || $item['time2'] || $item['time3'] || $item['time4'] || $item['time5'] || $item['time6']) {
									$time_data = array($item['time1'],$item['time2'],$item['time3'],$item['time4'],$item['time5'],$item['time6']);
									foreach($time_data as $k=>$v) {
										if($v) {
											$item['time'.($k+1)] = \PHPExcel_Shared_Date::ExcelToPHP($v)-$t;
											$item['time'.($k+1)] = date('H:i',$item['time'.($k+1)]);
										} else {
											unset($item['time'.($k+1)]);
										}
									}
									$data[] = $item;
								}
							}
						}
					}
				}
				// var_dump($data);die;
				$phpexcel = new \PHPExcel();  
				$filename="用户考勤信息表".date('YmdHis');  
				
		        $phpexcel->getActiveSheet()->setTitle($filename);  
		        $phpexcel->getActiveSheet()  
		              ->setCellValue('A1','部门')  
		              ->setCellValue('B1','姓名') 
		              ->setCellValue('C1','考勤号码')  
		              ->setCellValue('D1','日期时间')  
		              ->setCellValue('E1','记录状态')  
		              ->setCellValue('F1','机器号')  
		              ->setCellValue('G1','编号')  
		              ->setCellValue('H1','工种代码')  
		              ->setCellValue('I1','对比方式')
		              ->setCellValue('J1','卡号');  
		        $i = 2;  
		        foreach($data as $k=>$val) {
		        	if($val['time1']) {
		        		$phpexcel->getActiveSheet()   
		                     ->setCellValue('A'.$i, $val['department'])  
		                     ->setCellValue('B'.$i, $val['name'])  
		                     ->setCellValue('C'.$i, '')  
		                     ->setCellValue('D'.$i, $ym.$val['date'].' '.$val['time1'])  
		                     ->setCellValue('E'.$i, '')  
		                     ->setCellValue('F'.$i, '')    
		                     ->setCellValue('G'.$i, '')    
		                     ->setCellValue('H'.$i, '')       
		                     ->setCellValue('I'.$i, '')
		                     ->setCellValue('J'.$i, '');
		                $i++;
		        	}
		        	if($val['time2']) {
		        		$phpexcel->getActiveSheet()   
		                     ->setCellValue('A'.$i, $val['department'])  
		                     ->setCellValue('B'.$i, $val['name'])  
		                     ->setCellValue('C'.$i, '')  
		                     ->setCellValue('D'.$i, $ym.$val['date'].' '.$val['time2'])  
		                     ->setCellValue('E'.$i, '')  
		                     ->setCellValue('F'.$i, '')    
		                     ->setCellValue('G'.$i, '')    
		                     ->setCellValue('H'.$i, '')       
		                     ->setCellValue('I'.$i, '')
		                     ->setCellValue('J'.$i, '');
		                $i++;
		        	}
		        	if($val['time3']) {
		        		$phpexcel->getActiveSheet()   
		                     ->setCellValue('A'.$i, $val['department'])  
		                     ->setCellValue('B'.$i, $val['name'])  
		                     ->setCellValue('C'.$i, '')  
		                     ->setCellValue('D'.$i, $ym.$val['date'].' '.$val['time3'])  
		                     ->setCellValue('E'.$i, '')  
		                     ->setCellValue('F'.$i, '')    
		                     ->setCellValue('G'.$i, '')    
		                     ->setCellValue('H'.$i, '')       
		                     ->setCellValue('I'.$i, '')
		                     ->setCellValue('J'.$i, '');
		                     $i++;
		        	}
		        	if($val['time4']) {
		        		$phpexcel->getActiveSheet()   
		                     ->setCellValue('A'.$i, $val['department'])  
		                     ->setCellValue('B'.$i, $val['name'])  
		                     ->setCellValue('C'.$i, '')  
		                     ->setCellValue('D'.$i, $ym.$val['date'].' '.$val['time4'])  
		                     ->setCellValue('E'.$i, '')  
		                     ->setCellValue('F'.$i, '')    
		                     ->setCellValue('G'.$i, '')    
		                     ->setCellValue('H'.$i, '')       
		                     ->setCellValue('I'.$i, '')
		                     ->setCellValue('J'.$i, '');
		                $i++;
		        	}
		        	if($val['time5']) {
		        		$phpexcel->getActiveSheet()   
		                     ->setCellValue('A'.$i, $val['department'])  
		                     ->setCellValue('B'.$i, $val['name'])  
		                     ->setCellValue('C'.$i, '')  
		                     ->setCellValue('D'.$i, $ym.$val['date'].' '.$val['time5'])  
		                     ->setCellValue('E'.$i, '')  
		                     ->setCellValue('F'.$i, '')    
		                     ->setCellValue('G'.$i, '')    
		                     ->setCellValue('H'.$i, '')       
		                     ->setCellValue('I'.$i, '')
		                     ->setCellValue('J'.$i, '');
		                $i++;
		        	}
		        	if($val['time6']) {
		        		$phpexcel->getActiveSheet()   
		                     ->setCellValue('A'.$i, $val['department'])  
		                     ->setCellValue('B'.$i, $val['name'])  
		                     ->setCellValue('C'.$i, '')  
		                     ->setCellValue('D'.$i, $ym.$val['date'].' '.$val['time6'])  
		                     ->setCellValue('E'.$i, '')  
		                     ->setCellValue('F'.$i, '')    
		                     ->setCellValue('G'.$i, '')    
		                     ->setCellValue('H'.$i, '')       
		                     ->setCellValue('I'.$i, '')
		                     ->setCellValue('J'.$i, '');
		                $i++;
		        	}
		        }  
		        
		        $obj_Writer = \PHPExcel_IOFactory::createWriter($phpexcel,'Excel5');
		        //设置header  
		        header("Content-Type: charset=utf-8");
		        header("Content-Type: application/force-download");   
		        header("Content-Type: application/octet-stream");   
		        header("Content-Type: application/download");   
		        header('Content-Disposition:inline;filename="'.$filename.'.xls"');   
		        header("Content-Transfer-Encoding: binary");   
		        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");   
		        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");   
		        header("Pragma: no-cache");
		        $obj_Writer->save('php://output');//输出
		        die();
		    }
		}

	    $this->display('index');
	}

	public function upload1() {
    	/*header("Content-type:text/html;charset=utf-8");
    	$file_name = APP_PATH.'../Uploads/excel/test.xlsx';
    	
    	$ext = pathinfo($file_name,PATHINFO_EXTENSION);
    	vendor("PHPExcel.PHPExcel");
    	if($ext == 'xlsx') {
    		$type = 'Excel2007';
		    $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
		    $objPHPExcel = $objReader->load($file_name,'utf-8');
		} elseif($ext == 'xls') {
			$type = 'Excel5';
		    $objReader = \PHPExcel_IOFactory::createReader('Excel5');
		    $objPHPExcel = $objReader->load($file_name,'utf-8');
		} else {
			die('不支持的文件格式');
		}
		
		$sheetCount = $objPHPExcel->getSheetCount();//获取sheet总数
		$data = array();
		// var_dump(M('Univ','t_')->where(array('id'=>17))->getField('name'));die;
		for($i=0;$i<3;$i++) {
			$sheet = $objPHPExcel->getSheet($i);
			if($i == 0) {
				for($d=3;$d<49;$d++) {
					$item = array();
					$item['prjid'] = 110;//项目id
					$item['univid'] = (int)$sheet->getCell("A".$d)->getValue();//学校id
					$item['majorid'] = (int)$sheet->getCell("B".$d)->getValue();//专业id
					$item['pgradeid'] = (int)$sheet->getCell("C".$d)->getValue();//级别id
					$item['name'] = $sheet->getCell("D".$d)->getValue();//班型名称
					$item['remark'] = $sheet->getCell("E".$d)->getValue();//备注
					$item['basic_price'] = $sheet->getCell("F".$d)->getValue();//原价
					$item['sale_price'] = $sheet->getCell("H".$d)->getValue();//优惠后金额
					$item['subsidy_price'] = $sheet->getCell("G".$d)->getValue();//优惠
					$item['crt_time'] = time();
					$item['crt_adminid'] = 0;
					$item['introduce'] = 0;
					$item['ct_max'] = 0;
					$item['is_sale'] = 'Y';
					$item['dis_max_price'] = 0;
					$school_name = M('Univ','t_')->where(array('id'=>$item['univid']))->getField('name');//学校名称
					$major_name = M('Major','t_')->where(array('id'=>$item['majorid']))->getField('name');//专业名称
					$item['name'] = $school_name.'|'.$major_name.'--'.$item['name'];
					$data[] = $item;
				}
			} elseif($i == 1) {
				for($d=3;$d<65;$d++) {
					$item = array();
					$item['prjid'] = 110;//项目id
					$item['univid'] = (int)$sheet->getCell("A".$d)->getValue();//学校id
					$item['majorid'] = (int)$sheet->getCell("B".$d)->getValue();//专业id
					$item['pgradeid'] = (int)$sheet->getCell("C".$d)->getValue();//级别id
					$item['name'] = $sheet->getCell("D".$d)->getValue();//班型名称
					$item['remark'] = $sheet->getCell("E".$d)->getValue();//备注
					$item['basic_price'] = $sheet->getCell("F".$d)->getValue();//原价
					$item['sale_price'] = $sheet->getCell("H".$d)->getValue();//优惠后金额
					$item['subsidy_price'] = $sheet->getCell("G".$d)->getValue();//优惠
					$item['crt_time'] = time();
					$item['crt_adminid'] = 0;
					$item['introduce'] = 0;
					$item['ct_max'] = 0;
					$item['is_sale'] = 'Y';
					$item['dis_max_price'] = 0;
					$school_name = M('Univ','t_')->where(array('id'=>$item['univid']))->getField('name');//学校名称
					$major_name = M('Major','t_')->where(array('id'=>$item['majorid']))->getField('name');//专业名称
					$item['name'] = $school_name.'|'.$major_name.'--'.$item['name'];
					$data[] = $item;
				}
			} elseif ($i == 2) {
				for($d=3;$d<29;$d++) {
					$item = array();
					$item['prjid'] = 110;//项目id
					$item['univid'] = (int)$sheet->getCell("A".$d)->getValue();//学校id
					$item['majorid'] = (int)$sheet->getCell("B".$d)->getValue();//专业id
					$item['pgradeid'] = (int)$sheet->getCell("C".$d)->getValue();//级别id
					$item['name'] = $sheet->getCell("D".$d)->getValue();//班型名称
					$item['remark'] = $sheet->getCell("E".$d)->getValue();//备注
					$item['basic_price'] = $sheet->getCell("F".$d)->getValue();//原价
					$item['sale_price'] = $sheet->getCell("H".$d)->getValue();//优惠后金额
					$item['subsidy_price'] = $sheet->getCell("G".$d)->getValue();//优惠
					$item['crt_time'] = time();
					$item['crt_adminid'] = 0;
					$item['introduce'] = 0;
					$item['ct_max'] = 0;
					$item['is_sale'] = 'Y';
					$item['dis_max_price'] = 0;
					$school_name = M('Univ','t_')->where(array('id'=>$item['univid']))->getField('name');//学校名称
					$major_name = M('Major','t_')->where(array('id'=>$item['majorid']))->getField('name');//专业名称
					$item['name'] = $school_name.'|'.$major_name.'--'.$item['name'];
					$data[] = $item;
				}
			}
			
		}*/
		// var_dump($data[132]);die;
		// M('ClassType','t_')->addAll($data);
	}

	public function shilong() {
       /* $this->member_classnu_db=M('member_classnu','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');//线上数据表
        $this->grade_subject_db=M('grade_subject','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');//线上数据表
        $this->class_qbank_db=M('classtype_qbank','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');//线下新建数据表
        // var_dump($this->class_qbank_db);die;
        $this->member_classnu_data_db=M('member_classnu_data','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');//线上数据表
        
        $this->subject_db=M('questions_bank','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
        
        $list=$this->member_classnu_db->field("id,gradeid")->where('istiku!=0')->select();
        
        foreach($list as $k=>$v)
        {
            $list[$k]['pb']=$this->grade_subject_db->field("subjectid")->where(array('gradeid'=>$v['gradeid']))->select();
            
            foreach($list[$k]['pb'] as $a=>$b)
            {
                $condition['subjectid']=$b['subjectid'];
                $condition['classtypeid']=$v['id'];
                $tcount=$this->class_qbank_db->where($condition)->count();
                if($tcount=="0")
                {
                   $condition['creatime']=time();
                   $this->class_qbank_db->add($condition);
                }
            }
        }   */
	}

	public function class() {
		/*$model = M('ClassType','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_sale_new#utf8');
		$list = $model->select();
		$template = '欢迎报读replace的学习，学习平台网址i.sxmaps.com，帐号：您的手机号码，密码：手机号码后6位。有任何问题请致电0755-32910773。';
		foreach($list as $k=>$v) {
			$name = $v['name'];
			if(strpos($v['name'], '--') !== false && strpos($v['name'], '|') !== false) {
				$name = explode('|',$v['name']);
				$name = end($name);
				
			}
			
			$success_msg = str_replace('replace', $name, $template);
			$model->where(array('id'=>$v['id']))->save(array('success_msg'=>$success_msg));*/
			// var_dump($success_msg);
		// }
	}

	public function test() {
		date_default_timezone_set('Asia/shanghai');
		$start_time = mktime(0,0,0,8,10,2014);
		$end_time = mktime(0,0,0,5,10,2017);

		// $xy_model = M('Xueyuan','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		// $ms_model = M('MemberClassnuData','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		// $m_model = M('Member','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		$xy_model = M('Xueyuan','t_');
		$ms_model = M('MemberClassnuData','t_');
		$m_model = M('Member','t_');

		// $sql = 'SELECT username,count(username) number,m.crt_time,m.from source from t_member_classnu_data mcd LEFT JOIN t_member m on mcd.userid=m.id WHERE m.crt_time>1493740800 GROUP BY userid having(count(username)>=1)';
		// $sql = 'SELECT username,count(username) number,m.crt_time,m.from source from t_member_classnu_data mcd LEFT JOIN t_member m on mcd.userid=m.id WHERE m.crt_time>'.$start_time.' and m.crt_time<'.$end_time.' GROUP BY userid having(count(username)>=1)';
		// $sql = 'SELECT phone,crt_time from  t_member   WHERE crt_time>'.$start_time.' and crt_time<'.$end_time;
		$sql = 'SELECT m.phone,m.nickname,x.xy_name,m.crt_time,x.xy_no,mcd.id as classnuid from  t_member m LEFT JOIN t_xueyuan x '. 
			'ON m.phone=x.xy_phone LEFT JOIN t_member_classnu_data mcd ON m.phone=mcd.username '. 
			'WHERE x.xy_no is not null and mcd.classnuid is null and crt_time>'.$start_time.' and crt_time<'.$end_time.' ORDER BY m.crt_time DESC';
		$data = $xy_model->query($sql);
		// var_dump($sql);die;
		// $data = array(array('phone'=>1234,'crt_time'=>date('Y-m-d H:i:s'),'xy_no'=>1111));
		
		vendor("PHPExcel.PHPExcel");
        $phpexcel = new \PHPExcel();  
		$filename = "学员资料".date('YmdHis'); 

		$phpexcel->getActiveSheet()->setTitle($filename);  
        $phpexcel->getActiveSheet()  
              ->setCellValue('A1','手机')  
              ->setCellValue('B1','姓名') 
              ->setCellValue('C1','学员姓名') 
              ->setCellValue('D1','创建时间') 
              ->setCellValue('E1','报名班号');  
        
        foreach($data as $i=>$val) {
        	$phpexcel->getActiveSheet()   
                ->setCellValue('A'.($i+2), $val['phone'])  
                ->setCellValue('B'.($i+2), $val['nickname'])  
                ->setCellValue('C'.($i+2), $val['xy_name'])  
                ->setCellValue('D'.($i+2), date('Y-m-d H:i:s',$val['crt_time']))  
                ->setCellValue('E'.($i+2), $val['xy_no']);
        }
        $obj_Writer = \PHPExcel_IOFactory::createWriter($phpexcel,'Excel5');

        //设置header  
        header("Content-Type: application/force-download");   
        header("Content-Type: application/octet-stream");   
        header("Content-Type: application/download");   
        header('Content-Disposition:inline;filename="'.$filename.'.xls"');   
        header("Content-Transfer-Encoding: binary");   
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");   
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");   
        header("Pragma: no-cache");   
        $obj_Writer->save('php://output');//输出 
        exit(); 
		/*foreach($list as $k=>$v) {
			$item = array();
			$list1 = $ms_model->where(array('username'=>$v['phone']))->select();
			if($list1) {
				$item['phone'] = $v['phone'];
				$item['status'] = '正常';
				$item['desc'] = '';
				$item['crt_time'] = date('Y-m-d H:i:s',$v['crt_time']);
				foreach($list1 as $k1=>$v1) {
					$yichang_id = '';
					if(!$m_model->where(array('id'=>$v1['userid'],'phone'=>$v1['username']))->find()) {
						$yichang_id .= $v1['userid'];
						$item['status'] = '异常';
					}
					if($yichang_id) {
						$item['desc'] .= $yichang_id.' ';
					}
				}
				$data[] = $item;
			}
		}

		$this->data = $data;
		$this->count = count($data);
		$this->display();*/
	}

	public function test2() {
		// $mcd_model = M('MemberClassnuData','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		// $mc_model = M('MemberClassnu','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		// $xud_model = M('XyUploadData','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		// $mcd_model = M('MemberClassnuData','t_');
		// $mc_model = M('MemberClassnu','t_');
		// $xud_model = M('XyUploadData','t_');
		// $userids = $xud_model->field('userid')->where(array('classnuid'=>'','majorid'=>''))->select();
		// var_dump($userids);die;
		// foreach($userids as $v) {
		// 	$classnuid = $majorid = array();
		// 	$classnuid = $mcd_model->where(array('userid'=>$v['userid']))->getField('classnuid',true);
		// 	!$classnuid && $classnuid = array();
	 //    	if($classnuid) {
	 //    		$majorid = $mc_model->where(array('id'=>array('in',$classnuid)))->getField('majorid',true);
	 //    	}

	 //    	$xud_model->where(array('userid'=>$v['userid']))->save(array('classnuid'=>json_encode($classnuid),'majorid'=>json_encode($majorid)));
		// }
		
		// ExcelCustomers();
		
		
		// $mc_model = M('MemberClassnu','t_');
		// $mc_model = M('MemberClassnu','t_');
		// var_dump($mc_model->find(1198));die;
		// $r = $mc_model->where(array('name'=>'【广告专】《广告媒体分析》精讲'))->find();
		// var_dump($r);die;
		if(IS_POST) {
			$upload = new \Think\Upload();
		    $upload->maxSize   =     3145728 ;
		    $upload->exts      =     array('xls','xlsx');
		    $upload->rootPath  =     realpath(APP_PATH.'../').'/Uploads/excel/'; 
		    $upload->subName   =     '';  
		    if(!is_dir($upload->rootPath)) {
		    	@mkdir($upload->rootPath,777,true);
		    }
		    $upload->savePath  =     '';
		    $info   =   $upload->upload();
		    if(!$info) {
		        echo '<script>alert("'.$upload->getError().'");</script>';exit();
		    } else {
		    	$file_name = $upload->rootPath.$info['file']['savepath'].$info['file']['savename'];
		    	$ext = pathinfo($file_name,PATHINFO_EXTENSION);
		    	vendor("PHPExcel.PHPExcel");
		    	if($ext == 'xlsx') {
		    		$type = 'Excel2007';
				    $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
				    $objPHPExcel = $objReader->load($file_name,'utf-8');
				} elseif($ext == 'xls') {
					$type = 'Excel5';
				    $objReader = \PHPExcel_IOFactory::createReader('Excel5');
				    $objPHPExcel = $objReader->load($file_name,'utf-8');
				} else {
					die('不支持的文件格式');
				}
				
				$data = array();
				$sheet = $objPHPExcel->getActiveSheet();
				$highestColumn = $sheet->getHighestRow();//取得总列数
				
				$onc_model = M('OldNewClass','t_');
				$mcd_model = M('MemberClassnuData','t_');
				// $mc_model = M('MemberClassnu','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
				$mc_model = M('MemberClassnu','t_');
				$data_1 = array();
				// var_dump($mc_model->find(1198));die;
				for($i=2;$i<$highestColumn+1;$i++) {
					$item['old_id'] = (int)$sheet->getCell("A".$i)->getValue();//老班号id
					// $item['new_name'] = trim($sheet->getCell("C".$i)->getValue());//新班号名称
					$item['new_name'] = $sheet->getCell("C".$i)->getValue();//新班号名称
					$item['new_id'] = 0;
					if(!$new_classnu = $mc_model->where(array('name'=>$item['new_name']))->find()) {
						$data_1[] = $item['new_name'];
						error_log(date('Y/m/d H:i:s').' notice，新班号名称不存在：'.$item['new_name']."\r\n",3,APP_PATH.'/classnu_check.log');
					}
					
					$item['new_id'] = (int) $new_classnu['id'];
					if(!$onc_model->where($item)->find()) {
						// $onc_model->add($item);
					}
					
				}
				// var_dump($data_1);
		    	exit();		       
		    }
		}

	    $this->display('index');
	}

	public function test3() {
		/*$mcd_model = M('MemberClassnuData','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		$mc_model = M('MemberClassnu','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');*/
		$mcd_model = M('MemberClassnuData','t_');
		$mc_model = M('MemberClassnu','t_');
		$onc_model = M('OldNewClass','t_');
		// $id = 18;
		// $all_mcd = $mcd_model->where(array('classnuid'=>18))->select();
		$all_mcd = $mcd_model->select();
		// var_dump($all_mcd);die;
		$i = 0;
		$data = array();
		foreach($all_mcd as $k=>$v) {
			// $sql = "SELECT mc.id FROM t_member_classnu mc,t_member_classnu_data mcd WHERE mcd.classnuid=mc.old_id and mc.`name` in (SELECT new_name FROM t_old_new_class onc,t_member_classnu mc WHERE onc.new_name=mc.`name` and onc.old_id=".$v['classnuid'].")";
			// if($v['classnuid']==18) {var_dump($sql);die;}
			$sql = "SELECT id FROM t_member_classnu mc WHERE mc.`name` in (SELECT new_name FROM t_old_new_class onc WHERE  onc.old_id=".$v['classnuid'].")";
			// $sql = "SELECT id FROM t_member_classnu mc WHERE mc.`name` in (SELECT onc.new_name FROM t_old_new_class onc,t_member_classnu mc WHERE mc.id=onc.old_id and  onc.old_id=".$v['classnuid'].")";
			
			// $test = $new_classnu = M('')->query($sql);
			// var_dump($test);die;
			if($new_classnu = M('')->query($sql)) {
				// var_dump($new_classnu,$v,$sql);die;
				foreach ($new_classnu as $k1=> $v1) {
					if($v1['id'] && !$mcd_model->where(array('classnuid'=>$v1['id'],'userid'=>$v['userid']))->find()) {
						$item = $v;
						unset($item['id']);
						$item['crt_time'] = time();
						$item['classnuid'] = $v1['id'];
						// $data[] = $item;
						$i++;
						$data[] = $v['userid'];
						if($v['userid']==58) {
							// echo 44;die;
						}
						// $mcd_model->add($item);
					}
				}
			}

		}
		var_dump($i);
	}

	public function test4() {
		$mcd_model = M('MemberClassnuData','t_');
		$mc_model = M('MemberClassnu','t_');
		$onc_model = M('OldNewClass','t_');
		
		$where = 'id < 153';
		$old_classnuids = $mc_model->where($where)->getField('id',true);
		// error_log('mch:data = '.M()->getLastSql()."\r\n",3,APP_PATH.'/sql.log');
		// var_dump(realpath(APP_PATH).'/sql.log');die;
		if($old_classnuids) {
			foreach ($old_classnuids as $old_classnuid) {
				$all_mcd = $mcd_model->where(array('classnuid'=>$old_classnuid))->select();
				error_log('mch:data = '.M()->getLastSql()."\r\n",3,realpath(APP_PATH).'/sql.log');
				$i = 0;
				$data = array();
				foreach($all_mcd as $k=>$v) {
					$new_name = $onc_model->where(array('old_id'=>$v['classnuid']))->select();
					error_log('mch:data = '.M()->getLastSql()."\r\n",3,realpath(APP_PATH).'/sql.log');
					if($new_name) {
						foreach ($new_name as $k1=> $v1) {
							if(($new_classnuid = $mc_model->where(array('name'=>$v1['new_name']))->getField('id')) && !$mcd_model->where(array('classnuid'=>$new_classnuid,'userid'=>$v['userid']))->find()) {
								
								$i++;
								$item = $v;
								unset($item['id']);
								$item['crt_time'] = time();
								$item['classnuid'] = $new_classnuid;
								// $mcd_model->add($item);
								error_log('mch:data = '.M()->getLastSql()."\r\n",3,realpath(APP_PATH).'/sql.log');
							}
						}
					}
				}
			}
		}

		var_dump($i);
	}

	public function test5() {
		// $mcd_model = M('MemberClassnuData','t_');
		// $mc_model = M('MemberClassnu','t_');
		// $onc_model = M('OldNewClass','t_');
		// $new_name = M()->query('SELECT new_name FROM t_old_new_class GROUP BY new_name');

		// foreach ($new_name as $key=>$value) {
		// 	$item = array();
		// 	$item['baoming_prjid'] = 0;
		// 	$item['baoming_ctid'] = 0;
		// 	$item['baoming_majorid'] = 0;
		// 	$item['majorid'] = 0;
		// 	$item['classtypeid'] = 0;
		// 	$item['baoming_prj_ct_major'] = 0;
		// 	$item['prj_ct_major_id'] = 0;
		// 	$item['name'] = $value['new_name'];
		// 	$mc_model->add($item);
		// }
		// var_dump($new_name);
	}

	public function test6() {
		// $mcd_model = M('MemberClassnuData','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/test#utf8');
		// $mcd_online_model = M('MemberClassnuData','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		// $mc_model = M('MemberClassnu','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/test#utf8');
		// $onc_model = M('OldNewClass','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/test#utf8');
		// $xy_online_model = M('Xueyuan','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		// $mcd_model = M('MemberClassnuData','t_');
		// $mc_model = M('MemberClassnu','t_');
		// $onc_model = M('OldNewClass','t_');
		
		// $where = 'id>21791';
		// $new_mcd_data = $mcd_online_model->where($where)->group('userid')->order('userid asc')->select();//查询上线后生成的班内学员表数据，根据userid分组
		// error_log(date('m/d H:i:s').' 所有用户数量 '.count($new_mcd_data).' 最后处理用户详情 '.json_encode(end($new_mcd_data))."\r\n",3,APP_PATH.'/sql_new_data_import.log');die;
		// foreach ($new_mcd_data as $k=>$v) {
		// 	$id = $v['id'];
		// 	error_log(date('m/d H:i:s').' userid='.$v['userid']." start \r\n",3,APP_PATH.'/sql_new_data_import.log');
		// 	if(preg_match('/^1[34578]\d{9}$/',$v['username'])) {
		// 		$xy_arr = $xy_online_model->where(array('xy_phone'=>$v['username']))->select();//查询userid对应的学员表的班型信息
		// 		if($xy_arr) {
		// 			foreach ($xy_arr as $key=>$value) {
		// 				$classnuids = $mc_model->where(array('classtypeid'=>$value['ctid']))->select();//根据班型查询到班号信息
		// 				if($classnuids) {
		// 					foreach ($classnuids as $k2=>$v2) {
		// 						if(!$mcd_model->where(array('username'=>$v['username'],'classnuid'=>$v2['id']))->find()) {
		// 							$item = $v;
		// 							$item['crt_time'] = time();
		// 							$item['classnuid'] = $v2['id'];
									
		// 							//$mcd_model->add($item);//插入到班内学员
		// 							error_log(date('m/d H:i:s').' userid='.$v['userid'].'phone='.$v['username'].'插入班号'.$v2['id']."成功 ".json_decode($item)."\r\n",3,APP_PATH.'/sql_new_data_import.log');
		// 						}

		// 					}
		// 				} else {
		// 					error_log(date('m/d H:i:s').' notice，班型没有对应的班号：'.$value['ctid']."\r\n",3,APP_PATH.'/sql_new_data_import.log');
		// 				}
		// 			}

		// 		} else {
		// 			error_log(date('m/d H:i:s').' error，学员表没有信息：'.$v['username']."\r\n",3,APP_PATH.'/sql_new_data_import.log');
		// 		}
		// 	} else {
		// 		error_log(date('m/d H:i:s').' error，不是手机号码：'.$v['username']."\r\n",3,APP_PATH.'/sql_new_data_import.log');
		// 	}
			
		// }

		
	}

	public function test7() {
		$mcd_model = M('MemberClassnuData','t_');
		$mc_model = M('MemberClassnu','t_');

		// $mcd_model = M('MemberClassnuData','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		// $mc_model = M('MemberClassnu','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');
		
		$sql = "SELECT id,`name` FROM t_member_classnu GROUP BY `name` HAVING count(1)>1";
		$all_repeat_classnu = $mc_model->query($sql);//所有重复班号
		// $log_name = '/nsql_'.date('m_d_H_i');
		foreach ($all_repeat_classnu as $k=>$v) {
			$classnuids = $mc_model->where(array('name'=>$v['name']))->getField('id',true);//班号名称相同的所有班号id
			$baoliu_classnuid = $v['id'];//保留的班号id
			
			foreach ($classnuids as $classnuid) {
				if($classnuid != $baoliu_classnuid) {
					error_log('classnuid='.$baoliu_classnuid."开始\r\n",3,realpath(APP_PATH).'/asql_'.date('m_d_H_i').'log');
					$mcd_model->where(array('classnuid'=>$classnuid))->save(array('classnuid'=>$baoliu_classnuid));
					error_log('classnuid='.$baoliu_classnuid."结束\r\n",3,realpath(APP_PATH).'/asql_'.date('m_d_H_i').'log');
				}
				
			}
			// var_dump($baoliu_classnuid);die;
		}
		
	}

	public function test8() {
		$mcd_model = M('ss_cdr_cdr_info','','mysqli://justtest:test123@192.168.88.200/justcall_db#utf8');

	
		var_dump($mcd_model->find());
	}

	public function ab() {
		$model = M('ab','t_');
		$order_model = M('order','t_');
		$id = 511;
		$buy = 2;
		$model->startTrans();
		try {
			// 正确写法
			$model->where(array('id'=>$id))->setDec('no',$buy);
			if($model->where(array('id'=>$id))->getField('no')<0) {
				throw new \Exception('库存不足');
			}
			$order_model->add(array('no'=>$buy,'crt_time'=>time()));
			$model->commit();
			
			// 错误写法
			/*$result = $model->find($id);
			$left = $result['no']-$buy;
			if($left>=0) {
				$model->where(array('id'=>$id))->save(array('no'=>$left));
				$order_model->add(array('no'=>$buy,'crt_time'=>time()));
			}
			$model->commit();*/
		} catch (\Exception $e ) {
			$model->rollback();
		}
	}
}