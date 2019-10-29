<?php

namespace app\home\controller;
use think\Db;
use app\common\logic\NavViewLogic;
class Topic extends Base {
	/*
	 * 专题列表
	 */
	public function topicList(){
		$topicList = M('topic')->where("topic_state=2")->select();
		$this->assign('topicList',$topicList);
		return $this->fetch();
	}
	
	/*
	 * 专题详情
	 */
	public function detail(){
		$tab = I('tab');
		$tab = $tab ? $tab : 'tab-detail1';
        // 如果是手机跳转到 手机模版
        if(isMobile()){
            $this->redirect(U('Mobile/Topic/detail'));
        }
		$topic_id = I('topic_id/d',1);
		$topic = Db::name('topic')->where("topic_id", $topic_id)->find();
		

		$this->assign('index_nav',NavViewLogic::getNav());//获取导航
		$this->assign('topic',$topic);
		$this->assign('tab',$tab);
		return $this->fetch();
	}
	
	public function info(){
		$topic_id = I('topic_id/d',1);
		$topic = Db::name('topic')->where("topic_id", $topic_id)->find();
        echo htmlspecialchars_decode($topic['topic_content']);                
        exit;
	}
}