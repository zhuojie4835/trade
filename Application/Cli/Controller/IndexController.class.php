<?php

namespace Cli\Controller;

use Think\Controller;

class IndexController extends Controller {
	#清空数据，方便测试
	public function clearData() {
		$redis = getRedis();
		$redis->flushdb();
		$sql = file_get_contents(APP_PATH.'../mysql.sql');
		M('')->execute($sql);
	}
}