<?php

namespace Cli\Controller;

use Think\Controller;

class IndexController extends Controller {
	#清空数据，方便测试
	public function clearData() {
		$redis = getRedis();
		$redis->flushdb();
		M('customer','trade_')->where('1')->delete();
		M('follow','trade_')->where('1')->delete();
		M('ProductOrder','trade_')->where('1')->delete();
		M('RechargeAdmin','trade_')->where('1')->delete();
		M('deals','trade_')->where('1')->delete();
	}
}