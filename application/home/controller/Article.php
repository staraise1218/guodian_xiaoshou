<?php

namespace app\home\controller;
use think\Db;
use think\Page;
use app\common\logic\NavViewLogic;
class Article extends Base {
    
    public function index(){       
        $article_id = I('article_id/d',38);
    	$article = Db::name('article')->where("article_id", $article_id)->find();
    	$this->assign('article',$article);
        return $this->fetch();
    }
 
    /**
     * 文章内列表页
     */
    public function articleList(){
        $count = M('article')->where("cat_id  = 8","is_open = 1")->count();
        $page = new Page($count, 4);
        $article_lists = M('article')->where("cat_id  = 8","is_open = 1")->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('page',$page);// 赋值分页输出
        $this->assign('article_lists',$article_lists);// 赋值分页输出
        $this->assign('index_nav',NavViewLogic::getNav());//获取导航
        return $this->fetch();
    }
    /**
     * 文章内容页
     */
    public function detail(){
        // 如果是手机跳转到 手机模版
        if(isMobile()){
            $this->redirect(U('Mobile/Article/detail'));
        }
    	$article_id = I('article_id/d',1);
    	$article = Db::name('article')->where("article_id", $article_id)->find();
    	if($article){
    		$parent = Db::name('article_cat')->where("cat_id",$article['cat_id'])->find();
    		$this->assign('cat_name',$parent['cat_name']);
    		$this->assign('article',$article);
    	}
        $this->assign('index_nav',NavViewLogic::getNav());//获取导航
        return $this->fetch();
    } 

}