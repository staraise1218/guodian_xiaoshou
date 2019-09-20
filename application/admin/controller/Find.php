<?php

namespace app\admin\controller;

use think\Page;
use app\admin\logic\ArticleCatLogic;

class Find extends Base {
    
    // 发现列表
    public function index(){
        $res = $list = array();
        $p = empty($_REQUEST['p']) ? 1 : $_REQUEST['p'];
        $size = empty($_REQUEST['size']) ? 20 : $_REQUEST['size'];
        $cat_id = I('cat_id', 0);
        $keywords = trim(I('keywords'));

        // 获取发现分类
        $cats = M('article_cat')->where('parent_id', 10)->order('sort_order asc, cat_id asc')->select();
        
        if($cat_id){
            $where['cat_id'] = $cat_id;
        } else {
            $cats_ids = array_column($cats, 'cat_id');
            $cats_ids[] = 10;
            $where['cat_id'] = array('in', $cats_ids);
        }
        
        $keywords && $where['title'] = array('like', "%$keywords%");

        $list = M('Article')->where($where)->order('article_id desc')->page("$p, $size")->select();
        $count = M('Article')->where($where)->count();// 查询满足要求的总记录数
        $pager = new Page($count,$size);// 实例化分页类 传入总记录数和每页显示的记录数
        //$page = $pager->show();//分页显示输出
        if($list){
            foreach ($list as &$val){
                array_walk($cats, function ($v, $k) use (&$val){
                    if($v['cat_id'] == $val['cat_id']) $val['category'] = $v['cat_name'];
                });
                
                $val['add_time'] = date('Y-m-d H:i:s',$val['add_time']);                
            }
        }

        $this->assign('cats',$cats);
        $this->assign('cat_id',$cat_id);
        $this->assign('keywords',$keywords);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('pager',$pager);// 赋值分页输出        
        return $this->fetch();
    }
    
    public function article(){
        $ArticleCat = new ArticleCatLogic();
 		$act = I('GET.act','add');
        
        $info = array();
        $info['publish_time'] = time()+3600*24;
        if(I('GET.article_id')){
           $article_id = I('GET.article_id');
           $info = M('article')->where('article_id='.$article_id)->find();
        }
         // 获取发现分类
        $cats = M('article_cat')->where('parent_id', 10)->order('sort_order asc, cat_id asc')->select();

        $this->assign('cats',$cats);
        $this->assign('act',$act);
        $this->assign('info',$info);
        return $this->fetch();
    }
    
    public function aticleHandle()
    {
        $data = I('post.');
        $data['publish_time'] = strtotime($data['publish_time']);
        //$referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Article/articleList');
        
        $result = $this->validate($data, 'Article.'.$data['act'], [], true);
        if ($result !== true) {
            $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => $result]);
        }
        
        if ($data['act'] == 'add') {
            $data['click'] = mt_rand(1000,1300);
        	$data['add_time'] = time(); 
            $r = M('article')->add($data);
        } elseif ($data['act'] == 'edit') {
            $r = M('article')->where('article_id='.$data['article_id'])->save($data);
        } elseif ($data['act'] == 'del') {
        	$r = M('article')->where('article_id='.$data['article_id'])->delete(); 	
        }
        
        if (!$r) {
            $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
        }
            
        $this->ajaxReturn(['status' => 1, 'msg' => '操作成功']);
    }
    
}