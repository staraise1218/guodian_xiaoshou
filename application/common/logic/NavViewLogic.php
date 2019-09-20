<?php

namespace app\common\logic;

use think\Model;
use think\Db;
/**
* 获取公共页面信息逻辑定义
* Class NavViewLogic
* @package common\Logic
*/
class NavViewLogic extends Model
{
    /**
     * 获取页面下拉菜单
     * @return array
     */
    static public function getNav()
    {
        //$index_nav = S('index_nav');
        if(empty($index_nav))
        {
            //获取分类(规格)
//            $sql = "select DISTINCT type_id from ".C('database.prefix')."spec where search_index=1";
//            $data = Db::query($sql);
//            foreach ($data as $v){
//                $sql1 = "select a.type_id,a.name,b.id,b.item,b.spec_id from ".C('database.prefix')."spec as a join ";
//                $sql1 .= C('database.prefix')."spec_item as b where a.type_id=".$v['type_id']." and a.id=b.spec_id and a.search_index=1 order by a.order asc,b.id asc limit 8";//二级分类下热卖商品
//                $index_nav[$v['type_id']]['spec'] = Db::query($sql1);
//            }

            //获取显示的分类及下属品牌
            $sql = "select a.id,a.name,b.name as brand_name,b.id as brand_id from ".C('database.prefix')."goods_category as a inner join ".C('database.prefix')."brand as b on a.id = b.parent_cat_id where a.is_show=1 AND a.parent_id=0 order by b.sort,b.id asc";
            $data = Db::query($sql);
            $sql1 = "select id,name from ".C('database.prefix')."goods_category where parent_id=0 and is_show=1 order by sort_order asc";
            $cate = Db::query($sql1);
            $list = array();
            foreach($data as $k=>$v){
                if(empty($list[$v['id']])){
                    $list[$v['id']][] = $v;
                }else{
                    //$list[$v['id']] = array_push($list[$v['id']],$v['id']);
                    $list[$v['id']][] = $v;
                }
            }
            $index_nav['cate'] = $cate;
            $index_nav['list'] = $list;
            //获取品牌
            //$sql = "select DISTINCT parent_cat_id from ".C('database.prefix')."brand where is_hot=1";
            //$data = Db::query($sql);
//            foreach ($data as $v){
//                $sql2 = "select id,name from ".C('database.prefix')."brand where parent_cat_id=".$v['parent_cat_id']." and is_hot=1 order by sort asc limit 24";
//                $index_nav[$v['parent_cat_id']]['brand'] = Db::query($sql2);
//            }
            //获取热门商品
//            $sql = "select DISTINCT cat_id from ".C('database.prefix')."goods where is_hot=1 and is_on_sale=1";
//            $data = Db::query($sql);
//            foreach ($data as $v){
//                $sql3 = "select goods_id,goods_name from ".C('database.prefix')."goods where cat_id=".$v['cat_id']." and is_hot=1 and is_on_sale=1 order by sort asc limit 10";
//                $index_nav[$v['cat_id']]['goods'] = Db::query($sql3);
//            }
            S('index_nav',$index_nav,TPSHOP_CACHE_TIME);
        }
        return $index_nav;
    }
}