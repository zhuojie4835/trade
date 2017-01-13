<?php
namespace Common\Model;

/**
 * 成交记录模型
 * @author zhuojie
 */
class DealsModel extends TradeModel {
	public $_deals_type_val = array(1=>'挂单买入成交',2=>'挂单卖出成交',3=>'应价买入成交',4=>'应价卖出成交');
} 

