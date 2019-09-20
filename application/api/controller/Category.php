<?php

namespace app\api\controller;

use think\Db;
use app\common\logic\CharacterLogic;
use app\common\logic\GoodsLogic;

class Category extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}

	// 获取所有的品牌列表
	public function allBrandList(){
		$list = Db::name('brand')->alias('b')
			->join('goods_category gc', 'gc.id=b.parent_cat_id')
			->field('b.id, b.name, gc.mobile_name cat_name')
			->select();

		$CharacterLogic = new CharacterLogic();
		$list = $CharacterLogic->groupByInitials($list, 'name');

		response_success($list);
	}

	// 分类下的品牌列表
	public function cateBrandList(){
		$cat_id = I('cat_id');

		// 获取分类信息
		$category = Db::name('goods_category')->where('id', $cat_id)->field('name, image2')->find();

		$list = Db::name('brand')->alias('b')
			->join('goods_category gc', 'gc.id=b.parent_cat_id')
			->where('b.parent_cat_id', $cat_id)
			->field('b.id, b.name, b.logo, gc.mobile_name cat_name')
			->select();

		$result['category'] = $category;
		$result['list'] = $list;
		response_success($result);
	}

	/**
	 * [getTopCategory 获取顶级分类]
	 * @return [type] [description]
	 */
	public function topCategoryList(){
		$list = Db::name('goods_category')
			->where('is_show', 1)
			->order('sort_order')
			->field('id, mobile_name cat_name, image')
			->select();

		response_success($list);
	}


	/**
	 * [goodslist 通过品牌获取商品列表]
	 * @param [type] $[brand_id] [品牌id]
	 * @param [is_new] $[type] [1 按新品排序]
	 * @param [type] $[sales_num] [销量 desc/asc]
	 * @param [type] $[shop_price] [价格 desc/asc]
	 * @return [type] [description]
	 */
	public function goodsList(){
		$brand_id = I('brand_id');
		$page = I('page', 1);

		/*filter*/
		$type = I('type', 0);
		$sales_num = I('sales_num');
		$shop_price = I('shop_price');


		// 排序
		$order = ' store_count !=0 desc, sort asc, goods_id desc';
		$sales_num && $order = " store_count !=0 desc, sales_sum $sales_num";
		$shop_price && $order = " store_count !=0 desc, shop_price $shop_price";
		$is_new == 1 && $order = " store_count !=0 desc, is_new desc";

		$where = array(
			'brand_id' => $brand_id, // 品牌筛选
			'is_on_sale' => 1,  // 上架中
			'prom_type' => 0, // 普通商品
		);
		$goodsList = Db::name('goods')
			->where($where)
			->order($order)
			->field('goods_id, goods_name, store_count, original_img, shop_price, market_price, reserved')
			->page($page)
			->limit(12)
			->select();

		// 通过品牌获取所属分类
		$brand = Db::name('brand')->where('id', $brand_id)->field('parent_cat_id')->find();
		// 根据后台添加的模型，手动将分类和模型对应 cat_id=>type
		$categoryModule = array(
			'1' => 1, // 腕表
			'2' => 4, // 箱包皮具
			'3' => 8, // 珠宝
			'5' => 3, // 鞋履
			'6' => 5, // 配饰
		);

		$specList = array();
		// if($brand['parent_cat_id'] == 1){
		// 	Db::name()
		// }

		response_success($goodsList);
	}

	  /**
   	 * 生成目录树结构
   	 */
	  private function _tree($data){

   		$tree = array();
   		foreach ($data as $item) {
               if(isset($data[$item['parent_id']])){
                  $data[$item['parent_id']]['sub'][] = &$data[$item['id']];
               } else {
                  $tree[] = &$data[$item['id']];
               }
   		}

   		return $tree;
   	}

}