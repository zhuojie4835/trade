<?php 
namespace Customer\Controller;
use Think\Controller;

class AlbumController extends Controller {

	public function upload() {

		if(IS_POST) {
			$sort = I('post.sort');
			$base64 = I('post.base64');
			if(is_array($base64) && $base64) {
				foreach($base64 as $k=>$v) {
					if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $v)) {
						$pic = $v;
				        list($w, $h, $type, $attr) = getimagesize($pic);//判断文件类型
				        $support_type = array(IMAGETYPE_JPEG , IMAGETYPE_PNG , IMAGETYPE_GIF);
				        if(!in_array($type, $support_type,true)) {
				            $this->ajaxReturn(['code'=>0,'msg'=>'只能上传的格式jpg,gif,png']);
				        }
				       /* $limit = C('ZILIAO_LIMIT') ? C('ZILIAO_LIMIT') : 1024*3;
				        if(strlen($v['imgUrl'])/1370>$limit) {
							$this->ajaxReturn(['code'=>0,'msg'=>'文件大小在'.(int)($limit/1024).'M以内']);
						}*/
				        switch($type) {
				            case IMAGETYPE_JPEG :
				            	$type_text = 'jpg';
				                $image = imagecreatefromjpeg($pic);
				                break;
				            case IMAGETYPE_PNG :
				            	$type_text = 'png';
				                $image = imagecreatefrompng($pic);
				                break;
				            case IMAGETYPE_GIF :
				            	$type_text = 'gif';
				                $image = imagecreatefromgif($pic);
				                break;
				            default:
				                $this->ajaxReturn(['code'=>0,'path'=>'Load image error!']);
				        }

				        $ziliao_dir = dirname(realpath(APP_PATH)).'/Uploads/album/';
				        if(!file_exists($ziliao_dir)){
						  	mkdir($ziliao_dir,0777,true);
						} 
				        $filename = $ziliao_dir.$v['name'].'.'.$type_text;
				        $width = imagesx($image);  
		        		$height = imagesy($image);
				        $temp = imagecreatetruecolor($width, $height);  
				        imagecopyresampled($temp, $image, 0, 0, 0, 0, $width, $height, $width, $height);
				        imagejpeg($temp, $filename);//替换新图
				        $save_data = array(
				        	'sort'=>$sort[$k],
				        	'src'=>$filename,
				        	'crt_time'=>time()
				        );
				        M('Album','t_')->add($save_data);
				        /*if(M('XyUploadData')->where(array('userid'=>session('uid'),'phone'=>session('phone')))->find()) {
				        	M('XyUploadData')->where(array('userid'=>session('uid'),'phone'=>session('phone')))->save(array($v['name']=>$v['name'].'.'.$type_text));
				        } else {
				        	$name = session('userinfo')['name'] ? session('userinfo')['name'] : '';
				        	M('XyUploadData')->add(array('userid'=>session('uid'),'name'=>$name,'phone'=>session('phone'),'crt_time'=>time(),$v['name']=>$v['name'].'.'.$type_text));
				        }*/
					}
				}
				$this->ajaxReturn(array('code'=>1,'msg'=>'上传成功'));
			}
		}
		/*$info = M('XyUploadData')->where(array('userid'=>session('uid'),'phone'=>session('phone')))->find();
		$this->pre_url =  C('JH_WAP').'/Uploads/ziliao/'.session('phone').'/';
		$this->assign('info',$info);
		$this->meta_title = '资料上传';
		$this->active = 6;
		$this->display();*/
	}
}