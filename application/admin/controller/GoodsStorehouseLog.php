<?php


namespace app\admin\controller;

use think\AjaxPage;
use think\Page;
use think\Db;

class GoodsStorehouseLog extends Base {

    public function index(){

        return $this->fetch();
    }

	/**
	 * 店铺列表
	 */
	public function ajaxindex()
	{
        // 搜索条件
        $keyword = input('keyword');

        $where = array();
        if($keyword) $where['g.goods_name'] = array('like', "%$keyword%");

        

		$count = DB::name('goods_storehouse_log')->alias('gsl')
                ->join('goods g', 'g.goods_id=gsl.goods_id','left')
                ->where($where)->count();
        $AjaxPage = new AjaxPage($count, 10);
        $list = DB::name('goods_storehouse_log')->alias('gsl')
                ->join('goods g', 'g.goods_id=gsl.goods_id','left')
                ->where($where)
                ->field('gsl.id, g.goods_name, gsl.storehouse_id, new_storehouse_id, gsl.status, gsl.add_time')
                ->order("gsl.id desc")
                ->limit($AjaxPage->firstRow, $AjaxPage->listRows)
                ->select();

        $storehouselist = Db::name('storehouse')->column('id, name');


        $this->assign('list', $list);
        $this->assign('storehouselist', $storehouselist);
        $this->assign('AjaxPage', $AjaxPage);
        $this->assign('page', $AjaxPage->show());
		return $this->fetch();
	}

    // 详情
    public function detail(){
        $id = input('id');

        $info = Db::name('goods_storehouse_log')->alias('gsl')
            ->join('goods g', 'g.goods_id=gsl.goods_id','left')
            ->field('gsl.id, g.goods_name, gsl.storehouse_id, gsl.new_storehouse_id, gsl.status, gsl.add_time, gsl.admin_id, gsl.approver_id, gsl.approve_time')
            ->where('id', $id)
            ->find();

        // 获得库房信息
        if($info['storehouse_id']) $storehouse_arr[] = $info['storehouse_id'];
        if($info['new_storehouse_id']) $storehouse_arr[] = $info['new_storehouse_id'];
        $storehouselist = Db::name('storehouse')->where('id', 'in', $storehouse_arr)->column('id, name');

        // 操作员
        if($info['admin_id']) $admin_ids[] = $info['admin_id'];
        if($info['approver_id']) $admin_ids[] = $info['approver_id'];
        $admin = Db::name('admin')->where('admin_id', 'in', $admin_ids)->column('admin_id, realname');

// p($storehouselist, $info);
        $this->assign('info', $info);
        $this->assign('admin', $admin);
        $this->assign('storehouselist', $storehouselist);
        return $this->fetch();
    }


    // 审核
    public function approve(){
        $id = I('id');
        $status = I('status');

        $goodsStorehouseLog = Db::name('goods_storehouse_log')->where('id', $id)->find();

        // 判断审核者和编辑者是否是同一人
        $myadmin_id = session('admin_id');
        // if($myadmin_id == $goodsStorehouseLog['admin_id']) {
        //     $return_arr = array(
        //        'status' => 0,
        //        'msg'   => '审核者和操作者不能是同一人',
        //        'data'  => '',
        //    );
        //    $this->ajaxReturn($return_arr);
        // }

        // 当状态为 作废 直接更改记录状态
        if($status == 2){
            Db::name('goods_storehouse_log')->where('id', $id)->update(array('status'=>$status, 'approve_time'=>time()));
            $return_arr = array(
               'status' => 1,
               'msg'   => '操作成功',
               'data'  => array('back'=>true),
           );
           $this->ajaxReturn($return_arr);
        }

        // 当审核通过时
        
        // 将记录更新到主表
        Db::name('goods')->where('goods_id', $goodsStorehouseLog['goods_id'])->update(array('storehouse_id'=>$goodsStorehouseLog['new_storehouse_id'])); 
        // 修改
        Db::name('goods_storehouse_log')->where('id', $id)->update(array('status'=>$status, 'approve_time'=>time(), 'approver_id'=>$myadmin_id));

        $return_arr = array(
           'status' => 1,
           'msg'   => '操作成功',
           'data'  => array('back'=>true),
       );
       $this->ajaxReturn($return_arr);
    }

}