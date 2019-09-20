<?php

namespace app\admin\controller;

use app\admin\logic\GoodsLogic;
use think\AjaxPage;
use think\Page;
use think\Db;

class GoodsLog extends Base {

    /**
     *  商品列表
     */
    public function index(){      
        $GoodsLogic = new GoodsLogic();        
        $brandList = $GoodsLogic->getSortBrands();
        $categoryList = $GoodsLogic->getSortCategory();

        $this->assign('categoryList',$categoryList);
        $this->assign('brandList',$brandList);
        return $this->fetch();
    }
    
    /**
     *  商品列表
     */
    public function ajaxList(){            

        $count = M('Goods_log')->count();
        $Page  = new AjaxPage($count,20);

        $show = $Page->show();
        $goodsList = M('Goods_log')->alias('gl')
            ->join('admin a', 'a.admin_id=gl.admin_id', 'left')
            ->join('admin a2', 'a2.admin_id=gl.approver_id', 'left')
            ->field('gl.*, a.realname admin_realname, a2.realname approver_realname')
            ->order('id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();

        $catList = D('goods_category')->select();
        $catList = convert_arr_key($catList, 'id');
        $this->assign('catList',$catList);
        $this->assign('goodsList',$goodsList);
        $this->assign('page',$show);// 赋值分页输出
        return $this->fetch();
    }
    

    /**
     * 添加修改商品
     */
    public function detail()
    {
        $id = I('id');

        $GoodsLogic = new GoodsLogic();
        $Goods = new \app\admin\model\Goods();

        // 获取记录信息
        $goodsLogInfo = M('goods_log')->where('id', $id)->find();

        $goodsInfo = M('Goods')->where('goods_id', $goodsLogInfo['goods_id'])->find();

        $goodsInfo = array_merge($goodsInfo, $goodsLogInfo);

        $level_cat = $GoodsLogic->find_parent_cat($goodsInfo['cat_id']); // 获取分类默认选中的下拉框
        $level_cat2 = $GoodsLogic->find_parent_cat($goodsInfo['extend_cat_id']); // 获取分类默认选中的下拉框
        $cat_list = M('goods_category')->where("parent_id = 0")->select(); // 已经改成联动菜单
        $brandList = M('brand')->field('id, name')->select();
        $goodsType = M("GoodsType")->select();

        $this->assign('level_cat', $level_cat);
        $this->assign('level_cat2', $level_cat2);
        $this->assign('cat_list', $cat_list);
        $this->assign('brandList', $brandList);
        if ($goodsInfo['brand_id']){
            $seriesList = M("Series")->where('brand_id ='.$goodsInfo['brand_id'])->field('id,name')->select();
            $this->assign('seriesList', $seriesList);
        }
        $this->assign('goods_log_id', $id);
        $this->assign('goodsType', $goodsType);
        $this->assign('goodsInfo', $goodsInfo);  // 商品详情
        $goodsImages = M("GoodsImages")->where('goods_id =' . I('GET.id', 0))->select();
        $this->assign('goodsImages', $goodsImages);  // 商品相册
        return $this->fetch();
    }

    // 审核
    public function approve(){
        $goods_log_id = I('goods_log_id');
        $status = I('status');

        $goodsLog = Db::name('goods_log')->where('id', $goods_log_id)->find();

        // 判断审核者和编辑者是否是同一人
        $myadmin_id = session('admin_id');
        if($myadmin_id == $goodsLog['admin_id']) {
            $return_arr = array(
               'status' => 0,
               'msg'   => '审核者和操作者不能是同一人',
               'data'  => '',
           );
           $this->ajaxReturn($return_arr);
        }

        // 当状态为 作废 或者 该记录是新增的时候直接更改记录状态
        if($status == 2 || $GoodsLog['action'] == 1){
            Db::name('goods_log')->where('id', $goods_log_id)->update(array('status'=>$status, 'approve_time'=>time()));
            $return_arr = array(
               'status' => 1,
               'msg'   => '操作成功',
               'data'  => array('url'=>U('Admin/GoodsLog/index', array('goods_id', $goodsLog['goods_id']))),
           );
           $this->ajaxReturn($return_arr);
        }

        // 当审核通过时
        $goodsModel = new \app\admin\model\Goods();
        unset($goodsLog['id']);
        // 将记录更新到主表
        $goodsModel->data($goodsLog, true)->isUpdate(true)->allowField(true)->save(); 
        // 修改
        Db::name('goods_log')->where('id', $goods_log_id)->update(array('status'=>$status, 'approve_time'=>time()));

        $return_arr = array(
           'status' => 1,
           'msg'   => '操作成功',
           'data'  => array('url'=>U('Admin/GoodsLog/index', array('goods_id', $goodsLog['goods_id']))),
       );
       $this->ajaxReturn($return_arr);
    }

}