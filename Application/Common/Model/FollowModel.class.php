<?php
namespace Common\Model;

/**
 * 资金流水模型
 * @author zhuojie
 */
class FollowModel extends TradeModel {
	public $_follow_type_val = array(
				1=>'后台入金',
				2=>'认购',
				3=>'挂单买入成交',
				4=>'应价卖出成交',
				5=>'注册奖励',
				6=>'挂单卖出成交',
				7=>'应价买入成交'
			);

	
} 

