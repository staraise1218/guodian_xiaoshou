<?php


namespace app\admin\controller;

use think\AjaxPage;
use think\Page;
use think\Db;

class Store extends Base {

    public function index(){
        //  获取省份
        $province = M('region')->where(array('parent_id'=>0,'level'=>1))->select();
        $this->assign('province', $province);
        return $this->fetch();
    }
	/**
	 * 店铺列表
	 */
	public function ajaxindex()
	{
        // 搜索条件
        $keyword = input('post.keyword');
        $province = input('post.province');
        $city = input('post.city');
        $district = input('post.district');

        $where = array();
        $keyword ? $where['name'] = array('like', "%$keyword%") : false;
        $province ? $where['province'] = $province : false;
        $city ? $where['city'] = $city : false;
        $district ? $where['district'] = $district : false;

		$count = DB::name('store')->where($where)->count();
		$AjaxPage = new AjaxPage($count, 10);
		$list = DB::name('store')
                ->where($where)
                ->order("id desc")
				->limit($AjaxPage->firstRow, $AjaxPage->listRows)
				->select();

		$this->assign('list', $list);
        $this->assign('AjaxPage', $AjaxPage);
        $this->assign('page', $AjaxPage->show());
		return $this->fetch();
	}

    public function add(){
        if(IS_POST){
            $data = input('post.');
            $data['createtime'] = time();

            M('store')->insert($data);
            $this->success('添加成功', U('store/index'));
        }

        //  获取省份
        $province = M('region')->where(array('parent_id'=>0,'level'=>1))->select();
        $this->assign('province',$province);
        return $this->fetch();   
    }

    public function edit(){
        if(IS_POST){
            $data = input('post.');
            $data['createtime'] = time();

            $id = $data['id'];
            unset($data['id']);
            M('store')->where('id', $id)->update($data);
            $this->success('修改成功', U('store/index'));
        }

        $id = input('get.id');
        $info = M('store')->where('id', $id)->find();

        //  获取省份
        $province = M('region')->where(array('parent_id'=>0,'level'=>1))->select();
        $city = M('region')->where(array('parent_id'=>$info['province']))->select();
        $district = M('region')->where(array('parent_id'=>$info['city']))->select();

        $this->assign('info',$info);
        $this->assign('province',$province);
        $this->assign('city',$city);
        $this->assign('district',$district);
        return $this->fetch();   
    }

    /**
     * [saleList 店铺下的销售列表]
     * @return [type] [description]
     */
    public function saleindex(){
        $store_id = input('get.store_id');

        $this->assign('store_id', $store_id);
        return $this->fetch();
    }

    /**
     * 店铺列表
     */
    public function ajaxSaleIndex()
    {
        // 搜索条件
        // $keyword = input('post.keyword');
        $store_id = input('post.store_id');

        $where = array();
        // $keyword ? $where['name'] = array('like', "%$keyword%") : false;
        $store_id ? $where['sa.store_id'] = $store_id : false;
       
        $count = DB::name('store_admin')->alias('sa')->where($where)->count();
        $AjaxPage = new AjaxPage($count, 10);
        $list = DB::name('store_admin')->alias('sa')
                ->join('admin a', 'sa.admin_id=a.admin_id')
                ->where($where)
                ->order("sa.id desc")
                ->limit($AjaxPage->firstRow, $AjaxPage->listRows)
                ->select();

        $this->assign('list', $list);
        $this->assign('AjaxPage', $AjaxPage);
        $this->assign('page', $AjaxPage->show());
        return $this->fetch();
    }

    /**
     * [selectSales 店铺添加选择销售]
     * @return [type] [description]
     */
    public function selectSales()
    {
        $store_id = input('store_id');
        $keyword = input('keyword');
        
        $where['role_id'] = config('SALE_ID');
        if($keyword) $where['realname'] = ['like','%'.$keywords.'%'];

        // 获取该店铺已有的销售
        $admin_ids = Db::name('store_admin')->where('store_id', $store_id)->column('admin_id');

        $count = Db::name('admin')->where($where)->count();
        $Page = new Page($count, 10);

        $list = Db::name('admin')
            ->where($where)
            ->where('admin_id', 'not in', $admin_ids)
            ->order('admin_id DESC')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('page', $Page);
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function ajax_add_sales(){
        $admin_ids = input('admin_ids');
        $store_id = input('store_id');

        $admin_ids = explode(',', $admin_ids);

        if(!empty($admin_ids)){
            foreach ($admin_ids as $admin_id) {
                $data = array(
                    'store_id' => $store_id,
                    'admin_id' => $admin_id,
                    'createtime' => time(),
                );
                Db::name('store_admin')->insert($data);
            }
        }

        die(json_encode(array('code'=>200)));
    }

}