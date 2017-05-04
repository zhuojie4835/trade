<?php
namespace Customer\Controller;
use Think\Controller;

class ExcelController extends Controller {
	
	public function index($file_name,$ym='') {
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
		for($i=2;$i<$sheetCount-1;$i++) {
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
        header("Content-Type: application/force-download");   
        header("Content-Type: application/octet-stream");   
        header("Content-Type: application/download");   
        header('Content-Disposition:inline;filename="'.$filename.'.xls"');   
        header("Content-Transfer-Encoding: binary");   
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");   
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");   
        header("Pragma: no-cache");   
        $obj_Writer->save('php://output');//输出 
	}

	
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
		    	$this->index($file_name,$ym);
		    }
		}

	    $this->display('index');
	}

	public function upload1() {
    	header("Content-type:text/html;charset=utf-8");
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
			
		}
		// var_dump($data[132]);die;
		M('ClassType','t_')->addAll($data);
	}

	public function shilong() {
        $this->member_classnu_db=M('member_classnu','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_i_new#utf8');//线上数据表
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
        }   
	}

	public function class() {
		$model = M('ClassType','t_','mysql://admin:%Al&9FlPFKzSdm$V@120.24.183.111/sxmaps_sale_new#utf8');
		$list = $model->select();
		$template = '欢迎报读replace的学习，学习平台网址i.sxmaps.com，帐号：您的手机号码，密码：手机号码后6位。有任何问题请致电0755-32910773。';
		foreach($list as $k=>$v) {
			$name = $v['name'];
			if(strpos($v['name'], '--') !== false && strpos($v['name'], '|') !== false) {
				$name = explode('|',$v['name']);
				$name = end($name);
				
			}
			
			$success_msg = str_replace('replace', $name, $template);
			$model->where(array('id'=>$v['id']))->save(array('success_msg'=>$success_msg));
			// var_dump($success_msg);
		}
		
	}
}