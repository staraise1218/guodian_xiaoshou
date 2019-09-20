<?php

namespace app\api\controller;

use think\Db;

class Article extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'post';

		parent::__construct();
	}

	// type ： 1 探索国典 2 鉴定机制 3 消费者告知书 4 经营信息 5 隐私权策略 6 我的闲置 7 我的养护
	public function getContent(){
		$type = input('type');
		$article_id = input('article_id', 0);

		if($type == 1) $article_id = 98; // 探索国典
		if($type == 2) $article_id = 99; // 鉴定机制
		if($type == 3) $article_id = 100; // 消费者告知书
		if($type == 4) $article_id = 101; // 经营信息
		if($type == 5) $article_id = 102; // 隐私权策略
		if($type == 6) $article_id = 109; // 我的闲置
		if($type == 7) $article_id = 110; // 我的养护

		$info = Db::name('article')
			->where('article_id', $article_id)
			->where('is_open', 1)
			->field('title, content')
			->find();

		if($info) $info['content'] = html_entity_decode($info['content']);

		response_success($info);
	}

	public function detail(){
		$article_id = input('article_id', 0);

		$info = Db::name('article')
			->where('article_id', $article_id)
			->where('is_open', 1)
			->field('title, content')
			->find();

		if($info) $info['content'] = html_entity_decode($info['content']);

		response_success($info);
	}
}