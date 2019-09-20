<?php
/**
 * 团购、秒杀
 */
namespace app\api\controller;

use app\common\logic\GoodsLogic;
use app\common\logic\GoodsActivityLogic;
use app\common\model\FlashSale;
use app\common\model\GroupBuy;
use think\Db;
use think\Page;
use app\common\logic\ActivityLogic;

class Activity extends Base {
    public function __construct(){
        // 设置所有方法的默认请求方式
        $this->method = 'POST';

        parent::__construct();
    }

    /**
     * 团购活动列表
     */
    public function index()
    {
        $user_id = I('user_id');
        $city_code = I('city_code');
        $page = I('page',1);

        // 获取团购页banner
        $bannerList = Db::name('ad')
            ->where('pid', 3)
            ->where('enabled', 1)
            ->field('ad_name, ad_link, ad_code')
            ->order('orderby desc, ad_id desc')
            ->select();

        // 秒杀时间段
        $time_space = flash_sale_time_space();
        
        // 团购列表
        $group_by_where = array(
            'gb.start_time'=>array('lt',time()),
            'gb.end_time'=>array('gt',time()),
            'g.is_on_sale'=>1,
            'g.city_code'=>$city_code,
        );

        $GroupBuy = new GroupBuy();
        $grouplist = $GroupBuy
            ->alias('gb')
            ->join('__GOODS__ g', 'gb.goods_id=g.goods_id AND g.prom_type=2')
            ->where($group_by_where)
            ->page($page)
            ->limit(15)
            ->order('id desc')
            ->select();

       
       $result['bannerList'] = $bannerList;
       $result['time_space'] = $time_space;
       $result['grouplist'] = $grouplist;
       response_success($result);
    }


    /**
     * 秒杀活动列表
     */
    public function flash_sale_list()
    {
        $user_id = I('user_id');
        $city_code = I('city_code');
        $page = I('page', 1);
        $start_time = I('start_time');
        $end_time = I('end_time');
        $type = I('next');

        // 下期预告
        if(I('type') == 'next'){
            $start_time = date('Y-m-d', strtotime('+1 day'));
            $end_time = date('Y-m-d', strtotime('+2 day'));
        }

        $where = array(
            'fl.start_time'=>array('gt',$start_time),
            'fl.end_time'=>array('elt',$end_time),
            'g.is_on_sale'=>1
        );
        $FlashSale = new FlashSale();
        $list = $FlashSale->alias('fl')
            ->join('__GOODS__ g', 'g.goods_id = fl.goods_id')
            ->with(['goods'])
            ->field('*,100*(FORMAT(buy_num/goods_num,2)) as percent')
            ->where($where)
            ->select();
        
        
        response_success($list);
    }



    /**
     * 活动商品列表
     */
    public function discount_list(){
        $prom_id = I('id/d');    //活动ID
        $where = array(     //条件
            'is_on_sale'=>1,
            'prom_type'=>3,
            'prom_id'=>$prom_id,
        );
        $count =  M('goods')->where($where)->count(); // 查询满足要求的总记录数
         $pagesize = C('PAGESIZE');  //每页显示数
        $Page = new Page($count,$pagesize); //分页类
        $prom_list = Db::name('goods')->where($where)->limit($Page->firstRow.','.$Page->listRows)->select(); //活动对应的商品
        $spec_goods_price = Db::name('specGoodsPrice')->where(['prom_type'=>3,'prom_id'=>$prom_id])->select(); //规格
        foreach($prom_list as $gk =>$goods){  //将商品，规格组合
            foreach($spec_goods_price as $spk =>$sgp){
                if($goods['goods_id']==$sgp['goods_id']){
                    $prom_list[$gk]['spec_goods_price']=$sgp;
                }
            }
        }
        foreach($prom_list as $gk =>$goods){  //计算优惠价格
            $PromGoodsLogicuse = new \app\common\logic\PromGoodsLogic($goods,$goods['spec_goods_price']);
            if(!empty($goods['spec_goods_price'])){
                $prom_list[$gk]['prom_price']=$PromGoodsLogicuse->getPromotionPrice($goods['spec_goods_price']['price']);
            }else{
                $prom_list[$gk]['prom_price']=$PromGoodsLogicuse->getPromotionPrice($goods['shop_price']);
            }

        }
        $this->assign('prom_list', $prom_list);
        if(I('is_ajax')){
            return $this->fetch('ajax_discount_list');
        }
        return $this->fetch();
    }

    /**
     * 商品活动页面
     * @author lxl
     * @time2017-1
     */
    public function promote_goods(){
        $now_time = time();
        $where = " start_time <= $now_time and end_time >= $now_time ";
        $count = M('prom_goods')->where($where)->count();  // 查询满足要求的总记录数
        $pagesize = C('PAGESIZE');  //每页显示数
        $Page  = new Page($count,$pagesize); //分页类
        $promote = M('prom_goods')->field('id,title,start_time,end_time,prom_img')->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();    //查询活动列表
        $this->assign('promote',$promote);
        if(I('is_ajax')){
            return $this->fetch('ajax_promote_goods');
        }
        return $this->fetch();
    }

    public function coupon_list()
    {
        $atype = I('atype', 1);
        $user = session('user');
        $p = I('p', '');

        $activityLogic = new ActivityLogic();
        $result = $activityLogic->getCouponList($atype, $user['user_id'], $p);
        $this->assign('coupon_list', $result);
        if (request()->isAjax()) {
            return $this->fetch('ajax_coupon_list');
        }
        return $this->fetch();
    }

    /**
     * 领券
     */
    public function getCoupon()
    {
        $id = I('coupon_id/d');
        $user = session('user');
        $user['user_id'] = $user['user_id'] ?: 0;
        $activityLogic = new ActivityLogic();
        $return = $activityLogic->get_coupon($id, $user['user_id']);
        $this->ajaxReturn($return);
    }
}