<?php
namespace Common\Model;

class ProductOrderModel extends TradeModel {
	public $_status_val = array(1=>'展示期',2=>'认购期',3=>'交易期');

	public $_industry_val = array(1=>'艺术品');

	public $_issue_type_val = array(1=>'线上',2=>'线下');
	
}