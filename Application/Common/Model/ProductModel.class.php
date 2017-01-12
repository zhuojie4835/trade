<?php
namespace Common\Model;

class ProductModel extends TradeModel {
	public $_status_val = array(1=>'展示期',2=>'认购期',3=>'交易期');

	public $_industry_val = array(1=>'艺术品');

	public $_issue_type_val = array(1=>'线上',2=>'线下');
	
	protected $_validate = array(
		//4 新增
		array('status','require','产品状态必须',1,'',4),
		array('number','checkNumber','商品编号必须',1,'callback',4),
		array('product_name','require','商品名称必须',1,'',4),
		array('short_name','require','商品简称必须',1,'',4),
		array('issue_price','require','发行价格必须',1,'',4),
		array('issue_number','require','发行数量必须',1,'',4),
		array('sub_end_time','require','认购结束时间必须',1,'',4),
		//5 修改
		array('status','require','产品状态必须',1,'',5),
		array('product_name','require','商品名称必须',1,'',5),
		array('short_name','require','商品简称必须',1,'',5),
		array('issue_price','require','发行价格必须',1,'',5),
		array('issue_number','require','发行数量必须',1,'',5),
		array('sub_end_time','require','认购结束时间必须',1,'',5),
	);

	protected function checkNumber($number) {
		if(!$number) {
			throw new \Exception('商品编号必须');
		}
		if(D('Common/Product')->where(array('number'=>$number))->find()) {
			throw new \Exception('商品编号已存在');
		}
	}
}