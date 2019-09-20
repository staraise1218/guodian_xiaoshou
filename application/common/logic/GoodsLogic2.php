<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采用最新Thinkphp5助手函数特性实现单字母函数M D U等简写方式
 * ============================================================================
 */

namespace app\common\logic;

use app\common\model\Goods;
use think\Model;
use think\Db;
/**
 * 分类逻辑定义
 * Class CatsLogic
 * @package common\Logic
 */
class GoodsLogic2 extends Model
{
    /**
     * @param $special|属性
     * @return array|mixed
     * 根据特殊商品标记 查找 商品id
     * 80_BT4.0_BT4.1
     */
    function getGoodsIdBySpecial()
    {

        $arr = Db::name('goods')->where('temai', 1)->getField('goods_id', true);
        return ($arr ? $arr : array_unique($arr));
    }


    /**
     * @param $goods_id_arr
     * @param $filter_param
     * @param $action
     * @param int $mode  0  返回数组形式  1 直接返回result
     * @return array 这里状态一般都为1 result 不是返回数据 就是空
     * 获取 商品列表页帅选规格
     */
    public function get_filter_spec($goods_id_arr, $filter_param, $action, $mode = 0)
    {
        $goods_id_str = implode(',', $goods_id_arr);
        $goods_id_str = $goods_id_str ? $goods_id_str : '0';
        $spec_key = DB::query("select group_concat(`key` separator  '_') as `key` from __PREFIX__spec_goods_price where goods_id in($goods_id_str)");  //where("goods_id in($goods_id_str)")->select();

        $spec_key = explode('_', $spec_key[0]['key']);
        $spec_key = array_unique($spec_key);
        $spec_key = array_filter($spec_key);

        if (empty($spec_key)) {
            if ($mode == 1) return array();
            return array('status' => 1, 'msg' => '', 'result' => array());
        }
        $spec = M('spec')->where(array('search_index'=>1))->getField('id,name');
        $spec_item = M('spec_item')->where(array('spec_id'=>array('in',array_keys($spec))))
            ->getField('id,spec_id,item');

        $list_spec = array();
        $old_spec = $filter_param['spec'];
        foreach ($spec_key as $k => $v) {
            if (strpos($old_spec, $spec_item[$v]['spec_id'] . '_') === 0 || strpos($old_spec, '@' . $spec_item[$v]['spec_id'] . '_') || $spec_item[$v]['spec_id'] == '')
                continue;
            $list_spec[$spec_item[$v]['spec_id']]['spec_id'] = $spec_item[$v]['spec_id'];
            $list_spec[$spec_item[$v]['spec_id']]['name'] = $spec[$spec_item[$v]['spec_id']];
            //$list_spec[$spec_item[$v]['spec_id']]['item'][$v] = $spec_item[$v]['item'];

            // 帅选参数
            if (!empty($old_spec))
                $filter_param['spec'] = $old_spec . '@' . $spec_item[$v]['spec_id'] . '_' . $v;
            else
                $filter_param['spec'] = $spec_item[$v]['spec_id'] . '_' . $v;
            $list_spec[$spec_item[$v]['spec_id']]['item'][] = array('key' => $spec_item[$v]['spec_id'], 'val' => $v, 'item' => $spec_item[$v]['item'], 'href' => urldecode(U("Goods/$action", $filter_param, '')));
        }

        if ($mode == 1) return $list_spec;
        return array('status' => 1, 'msg' => '', 'result' => $list_spec);
    }
    /**
     * @param $goods_id_arr
     * @param $filter_param
     * @param $action
     * @return array|mixed 这里状态一般都为1 result 不是返回数据 就是空
     * 获取 商品列表页帅选品牌
     */
    public function get_filter_brand($goods_id_arr, $filter_param, $action)
    {
        if (!empty($filter_param['brand_id'])) {
            return array();
        }

        $map['goods_id'] = ['in', $goods_id_arr];
        $map['brand_id'] = ['>', 0];
        $brand_id_arr = M('goods')->where($map)->column('brand_id');
        $list_brand = M('brand')->where('id','in',$brand_id_arr)->order('name asc')->limit('30')->select();

        foreach ($list_brand as $k => $v) {
            // 帅选参数
            $filter_param['brand_id'] = $v['id'];
            $list_brand[$k]['href'] = urldecode(U("Goods/$action", $filter_param, ''));
        }
        return $list_brand;
    }

    /**
     * 根据配送地址获取多个商品的运费
     * @param $goodsArr
     * @param $region_id
     * @return int
     */
    public function getFreight($goodsArr, $region_id)
    {
        $Goods = new Goods();
        $freightLogic = new FreightLogic();
        $freightLogic->setRegionId($region_id);
        $goods_ids = get_arr_column($goodsArr, 'goods_id');
        $goodsList = $Goods->field('goods_id,volume,weight,template_id,is_free_shipping')->where('goods_id', 'IN', $goods_ids)->select();
        $goodsList = collection($goodsList)->toArray();
        foreach ($goodsArr as $cartKey => $cartVal) {
            foreach ($goodsList as $goodsKey => $goodsVal) {
                if ($cartVal['goods_id'] == $goodsVal['goods_id']) {
                    $goodsArr[$cartKey]['volume'] = $goodsVal['volume'];
                    $goodsArr[$cartKey]['weight'] = $goodsVal['weight'];
                    $goodsArr[$cartKey]['template_id'] = $goodsVal['template_id'];
                    $goodsArr[$cartKey]['is_free_shipping'] = $goodsVal['is_free_shipping'];
                }
            }
        }
        $template_list = [];
        foreach ($goodsArr as $goodsKey => $goodsVal) {
            $template_list[$goodsVal['template_id']][] = $goodsVal;
        }
        $freight = 0;
        foreach ($template_list as $templateVal => $goodsArr) {
            $temp['template_id'] = $templateVal;
            foreach ($goodsArr as $goodsKey => $goodsVal) {
                $temp['total_volume'] += $goodsVal['volume'] * $goodsVal['goods_num'];
                $temp['total_weight'] += $goodsVal['weight'] * $goodsVal['goods_num'];
                $temp['goods_num'] += $goodsVal['goods_num'];
                $temp['is_free_shipping'] = $goodsVal['is_free_shipping'];
            }
            $freightLogic->setGoodsModel($temp);
            $freightLogic->setGoodsNum($temp['goods_num']);
            $freightLogic->doCalculation();
            $freight += $freightLogic->getFreight();
            unset($temp);
        }
        return $freight;
    }

    /**
     * 用户浏览记录
     * @author lxl
     * @time  17-4-20
     */
    public function add_visit_log($user_id,$goods){
        $record = M('goods_visit')->where(array('user_id'=>$user_id,'goods_id'=>$goods['goods_id']))->find();
        if($record){
            M('goods_visit')->where(array('user_id'=>$user_id,'goods_id'=>$goods['goods_id']))->save(array('visittime'=>time()));
        }else{
            $visit = array('user_id'=>$user_id,'goods_id'=>$goods['goods_id'],'visittime'=>time(),'cat_id'=>$goods['cat_id'],'extend_cat_id'=>$goods['extend_cat_id']);
            M('goods_visit')->add($visit);
        }
    }
}