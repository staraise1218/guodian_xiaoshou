<?php

namespace app\api\controller;

use think\Db;

class Git extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}

	// public function pull(){
	// 	$output = shell_exec("cd /phpstudy/www/TP; git pull 2<&1");
 //        echo "<pre>$output</pre>";
	// }

	public function testpull(){
		$output = shell_exec("cd /home/www/guodian; git pull 2<&1");
        echo "<pre>$output</pre>";
	}
}