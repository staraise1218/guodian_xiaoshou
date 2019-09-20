<?php

namespace app\api\controller;

use think\Db;

class Find extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'post';

		parent::__construct();
	}

	// 发现首页列表
	public function index(){
		$user_id = I('user_id');
		$page = I('page', 1);
        
        $list = M('Article')
        	->where('cat_id', 8)
        	->where('is_open', 1)
        	->order('article_id desc')
        	->page($page)
        	->limit(16)
        	->field('article_id, title, thumb_app thumb, like_num')
        	->select();
       	
       	if(is_array($list)){
        	foreach ($list as &$item) {
        		$item['isliked'] = $this->isLike($user_id, $item['article_id']);
        	}
        }

        $result['list']= $list;
        response_success($result);
	}

	public function getListByCat(){
		$user_id = I('user_id');
		$cat_id = I('cat_id');
		$page = I('page', 1);

		$list = M('Article')
        	->where('cat_id', $cat_id)
        	->where('is_open', 1)
        	->order('article_id desc')
        	->page($page)
        	->limit(16)
        	->field('article_id, title, thumb, like_num')
        	->select();

        if(is_array($list)){
        	foreach ($list as &$item) {
        		$item['isliked'] = $this->isLike($user_id, $item['article_id']);
        	}
        }
        
        response_success($list);
	}

	public function detail(){
		$user_id = I('user_id');
		$article_id = input('article_id');

		$info = Db::name('article')
			->where('article_id', $article_id)
			->where('is_open', 1)
			->field('title, thumb, content')
			->find();
		$info &&  $info['isliked'] = $this->isLike($user_id, $article_id);

		if($info) $info['content'] = html_entity_decode($info['content']);

		response_success($info);
	}

	// 点赞
	public function clickLike(){
		$user_id = I('user_id');
		$article_id = I('article_id');

		$isLiked = $this->isLike($user_id, $article_id);
		if($isLiked) response_success('', '已点赞');

		Db::name('like')->insert(array(
			'user_id'=>$user_id,
			'table_name' => 'article',
			'table_id' => $article_id,
		));
		Db::name('article')->where('article_id', $article_id)->setInc('like_num', 1);
		response_success('', '点赞成功');
	}

	private function isLike($user_id, $article_id){
		$count = Db::name('like')
			->where('user_id', $user_id)
			->where('table_name', 'article')
			->where('table_id', $article_id)
			->count();

		return ($count ? 1 : 0);
	}
}