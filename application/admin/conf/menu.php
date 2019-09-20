<?php
return	array(	

	'shop'=>array('name'=>'商城','child'=>array(
				array('name' => '商品','child' => array(
				    array('name' => '商品列表', 'act'=>'goodsList', 'op'=>'Goods'),
					// array('name' => '商品分类', 'act'=>'categoryList', 'op'=>'Goods'),
					// array('name' => '库存日志', 'act'=>'stock_list', 'op'=>'Goods'),
					// array('name' => '商品模型', 'act'=>'goodsTypeList', 'op'=>'Goods'),
					// array('name' => '商品规格', 'act' =>'specList', 'op' => 'Goods'),
					// array('name' => '商品品牌', 'act'=>'brandList', 'op'=>'Goods'),
					// array('name' => '商品系列', 'act'=>'seriesList', 'op'=>'Goods'),
					// array('name' => '商品属性', 'act'=>'goodsAttributeList', 'op'=>'Goods'),
					//array('name' => '评论列表', 'act'=>'index', 'op'=>'Comment'),
					//array('name' => '商品咨询', 'act'=>'ask_list', 'op'=>'Comment'),
                                    
				)),
				array('name' => '订单','child'=>array(
					array('name' => '订单列表', 'act'=>'index', 'op'=>'Order'),
					//array('name' => '虚拟订单', 'act'=>'virtual_list', 'op'=>'Order'),
					// array('name' => '发货单', 'act'=>'delivery_list', 'op'=>'Order'),
					array('name' => '退款单', 'act'=>'refund_order_list', 'op'=>'Order'),
					// array('name' => '退货单', 'act'=>'return_list', 'op'=>'Order'),
					// array('name' => '添加订单', 'act'=>'add_order', 'op'=>'Order'),
					// array('name' => '订单日志','act'=>'order_log','op'=>'Order'),
					//array('name' => '发票管理','act'=>'index', 'op'=>'Invoice'),
			        //array('name' => '拼团列表','act'=>'team_list','op'=>'Team'),
			        //array('name' => '拼团订单','act'=>'order_list','op'=>'Team'),
				)),
				array('name' => '促销','child' => array(
					// array('name' => '抢购管理', 'act'=>'flash_sale', 'op'=>'Promotion'),
					//array('name' => '团购管理', 'act'=>'group_buy_list', 'op'=>'Promotion'),
					// array('name' => '优惠促销', 'act'=>'prom_goods_list', 'op'=>'Promotion'),
					// array('name' => '订单促销', 'act'=>'prom_order_list', 'op'=>'Promotion'),
					array('name' => '优惠券','act'=>'index', 'op'=>'Coupon'),
					//array('name' => '预售管理','act'=>'pre_sell_list', 'op'=>'Promotion'),
					//array('name' => '拼团管理','act'=>'index', 'op'=>'Team'),
				)),
				array('name' => '仓库','child' => array(
					array('name' => '仓库管理','act'=>'index', 'op'=>'Storehouse'),
					array('name' => '调库记录','act'=>'index', 'op'=>'GoodsStorehouseLog'),
				)),
				array('name' => '店铺', 'child' => array(
					array('name' => '店铺管理', 'act'=>'index', 'op'=>'store'),
				))

				/*
				array('name' => '分销','child' => array(
						array('name' => '分销商品列表', 'act'=>'goods_list', 'op'=>'Distribut'),
						array('name' => '分销商列表', 'act'=>'distributor_list', 'op'=>'Distribut'),
						array('name' => '分销关系', 'act'=>'tree', 'op'=>'Distribut'),
						array('name' => '分销商等级', 'act'=>'grade_list', 'op'=>'Distribut'),
						array('name' => '分成日志', 'act'=>'rebate_log', 'op'=>'Distribut'),
				)),*/
				/*
	    	    array('name' => '微信','child' => array(
	    	        array('name' => '公众号配置', 'act'=>'index', 'op'=>'Wechat'),
	    	        array('name' => '微信菜单管理', 'act'=>'menu', 'op'=>'Wechat'),
	    	        array('name' => '文本回复', 'act'=>'text', 'op'=>'Wechat'),
	    	        //array('name' => '图文回复', 'act'=>'img', 'op'=>'Wechat'),
	    	    )),*/

				/*
				array('name' => '统计','child' => array(
						array('name' => '销售概况', 'act'=>'index', 'op'=>'Report'),
						array('name' => '销售排行', 'act'=>'saleTop', 'op'=>'Report'),
						array('name' => '会员排行', 'act'=>'userTop', 'op'=>'Report'),
						array('name' => '销售明细', 'act'=>'saleList', 'op'=>'Report'),
						array('name' => '会员统计', 'act'=>'user', 'op'=>'Report'),
						array('name' => '运营概览', 'act'=>'finance', 'op'=>'Report'),
						array('name' => '平台支出记录','act'=>'expense_log','op'=>'Report'),
				)),*/
	)),
		
	'member'=>array('name'=>'用户管理','child'=>array(
			
			array('name' => '用户','child'=>array(
					array('name'=>'用户列表','act'=>'index','op'=>'User'),
					// array('name'=>'会员等级','act'=>'levelList','op'=>'User'),
					//array('name'=>'充值记录','act'=>'recharge','op'=>'User'),
					//array('name'=>'提现申请','act'=>'withdrawals','op'=>'User'),
					//array('name'=>'汇款记录','act'=>'remittance','op'=>'User'),
					//array('name'=>'会员整合','act'=>'integrate','op'=>'User'),
					//array('name'=>'会员签到','act'=>'signList','op'=>'User'),
			)),
	)),
	
);