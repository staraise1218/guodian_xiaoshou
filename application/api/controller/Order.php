<?php

namespace app\api\controller;

use app\common\model\TeamFound;
use app\common\logic\UsersLogic;
use app\common\logic\OrderLogic;
use app\common\logic\CommentLogic;
use app\common\logic\ShippingLogic;
use think\Page;
use think\Request;
use think\Db;

class Order extends Base
{

    public function __construct()
    {
        $this->method = 'post';
        parent::__construct();
    }

    /**
     * 订单列表
     * @return mixed
     * @param  $[type] [< 待付款：WAITPAY，待发货：WAITSEND， 待收货：WAITRECEIVE，待评价：WAITCCOMMENT, 已完成：FINISH ， 已取消：CANCEL 退款订单 ： REFUND>]
     */
    public function order_list()
    {
        $user_id = input('user_id/d');
        $type = input('type');
        $page = input('page/d', 1);

        $where = ' user_id=' . $user_id . ' and deleted = 0 ';
        //条件搜索
        if($type) $where .= C(strtoupper(I('get.type')));

        $order_str = "order_id DESC";
        $order_list = M('order')
            ->order($order_str)
            ->where($where)
            ->page($page)
            ->limit(10)
            ->select();

        //获取订单商品
        $model = new UsersLogic();
        foreach ($order_list as $k => $v) {
            $order_list[$k] = set_btn_order_status($v);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
            //$order_list[$k]['total_fee'] = $v['goods_amount'] + $v['shipping_fee'] - $v['integral_money'] -$v['bonus'] - $v['discount']; //订单总额
            $data = $model->get_order_goods($v['order_id']);
            $order_list[$k]['goods_list'] = $data['result'];
        }

        //统计订单商品数量
        foreach ($order_list as $key => $value) {
            $count_goods_num = 0;
            foreach ($value['goods_list'] as $kk => $vv) {
                $count_goods_num += $vv['goods_num'];
            }
            $order_list[$key]['count_goods_num'] = $count_goods_num;
        }
        
        response_success($order_list);
    }

    /**
     * 订单详情
     * @return mixed
     */
    public function order_detail()
    {
        $user_id = input('user_id');
        $order_id = input('order_id');

        $map['order_id'] = $order_id;
        $map['user_id'] = $user_id;
        $order_info = M('order')->where($map)->find();
        $order_info = set_btn_order_status($order_info);  // 添加属性 ,包括按钮显示属性和订单状态显示属性
        if (!$order_info) response_error('', '没有获取到订单信息');


        //获取订单商品
        $model = new UsersLogic();
        $data = $model->get_order_goods($order_info['order_id']);
        $order_info['goods_list'] = $data['result'];
        
        // if($order_info['order_prom_type'] == 4){
        //     $pre_sell_item =  M('goods_activity')->where(array('act_id'=>$order_info['prom_id']))->find();
        //     $pre_sell_item = array_merge($pre_sell_item,unserialize($pre_sell_item['ext_info']));
        //     $order_info['pre_sell_is_finished'] = $pre_sell_item['is_finished'];
        //     $order_info['pre_sell_retainage_start'] = $pre_sell_item['retainage_start'];
        //     $order_info['pre_sell_retainage_end'] = $pre_sell_item['retainage_end'];
        //     $order_info['pre_sell_deliver_goods'] = $pre_sell_item['deliver_goods'];
        // }else{
        //     $order_info['pre_sell_is_finished'] = -1;//没有参与预售的订单
        // }
        
        // 计算完成地址
        $area_id[] = $order_info['province'];
        $area_id[] = $order_info['city'];
        $area_id[] = $order_info['district'];
        $area_id = array_filter($area_id);
        $area_id = implode(',', $area_id);
        $regionList = Db::name('region2')->where("id", "in", $area_id)->getField('id,name');
        $order_info['fulladdress'] = $regionList[$order_info['province']].$regionList[$order_info['city']].$regionList[$order_info['district']].$order_info['address'];
        // 获取订单号
        $invoice_no = M('DeliveryDoc')->where("order_id", $order_id)->getField('invoice_no');
        $order_info['invoice_no'] = $invoice_no;
        

        // $this->assign('order_status', C('ORDER_STATUS'));
        // $this->assign('shipping_status', C('SHIPPING_STATUS'));
        // $this->assign('pay_status', C('PAY_STATUS'));
        // $this->assign('region_list', $region_list);
        // $this->assign('order_info', $order_info);
        // $this->assign('order_action', $order_action);

        response_success($order_info);
    }
    //拼团订单列表
    public function team_list(){
        $type = input('type');
        $Order = new \app\common\model\Order();
        $order_where = ['order_prom_type' => 6, 'user_id' => $this->user_id, 'deleted' => 0,'pay_code'=>['<>','cod']];//拼团基础查询
        $listRows = 10;
        switch (strval($type)) {
            case 'WAITPAY':
                //待支付订单
                $order_where['pay_status'] = 0;
                $order_where['order_status'] = 0;
                break;
            case 'WAITTEAM':
                //待成团订单
                $found_order_id = Db::name('team_found')->where(['user_id'=>$this->user_id,'status'=>1])->limit($listRows)->order('found_id desc')->getField('order_id',true);//团长待成团
                $follow_order_id = Db::name('team_follow')->where(['follow_user_id'=>$this->user_id,'status'=>1])->limit($listRows)->order('follow_id desc')->getField('order_id',true);//团员待成团
                $team_order_id = array_merge($found_order_id,$follow_order_id);
                if (count($team_order_id) > 0) {
                    $order_where['order_id'] = ['in', $team_order_id];
                }else{
                    $order_where['order_id'] = 0;
                }
                break;
            case 'WAITSEND':
                //待发货
                $order_where['pay_status'] = 1;
                $order_where['shipping_status'] = ['<>',1];
                $order_where['order_status'] = ['elt',1];
                $found_order_id = Db::name('team_found')->where(['user_id'=>$this->user_id,'status'=>2])->limit($listRows)->order('found_id desc')->getField('order_id',true);//团长待成团
                $follow_order_id = Db::name('team_follow')->where(['follow_user_id'=>$this->user_id,'status'=>2])->limit($listRows)->order('follow_id desc')->getField('order_id',true);//团员待成团
                $team_order_id = array_merge($found_order_id,$follow_order_id);
                if (count($team_order_id) > 0) {
                    $order_where['order_id'] = ['in', $team_order_id];
                }else{
                    $order_where['order_id'] = 0;
                }
                break;
            case 'WAITRECEIVE':
                //待收货
                $order_where['shipping_status'] = 1;
                $order_where['order_status'] = 1;
                break;
            case 'WAITCCOMMENT':
                //已完成
                $order_where['order_status'] = 2;
                break;
        }
        $request = Request::instance();
        $order_count = $Order->where($order_where)->count();
        $page = new Page($order_count, $listRows);
        $order_list = $Order->with('orderGoods')->where($order_where)->limit($page->firstRow . ',' . $page->listRows)->order('order_id desc')->select();
        $this->assign('order_list',$order_list);
        if ($request->isAjax()) {
            return $this->fetch('ajax_team_list');
//            $this->ajaxReturn(['status'=>1,'msg'=>'获取成功','result'=>$order_list]);
        }
        return $this->fetch();
    }

    public function team_detail(){
        $order_id = input('order_id');
        $Order = new \app\common\model\Order();
        $TeamFound = new TeamFound();
        $order_where = ['order_prom_type' => 6, 'order_id' => $order_id, 'deleted' => 0];
        $order = $Order->with('orderGoods')->where($order_where)->find();
        if (empty($order)) {
            $this->error('该订单记录不存在或已被删除');
        }
        $orderTeamFound = $order->teamFound;
        if ($orderTeamFound) {
            //团长的单
            $this->assign('orderTeamFound', $orderTeamFound);//团长
        } else {
            //去找团长
            $teamFound = $TeamFound::get(['found_id' => $order->teamFollow['found_id']]);
            $this->assign('orderTeamFound', $teamFound);//团长
        }
        $this->assign('order', $order);
        return $this->fetch();
    }

    /**
     * 物流信息
     * @return mixed
     */
    public function express()
    {
        $order_id = I('get.order_id/d', 0);
        $order_goods = M('order_goods')->where("order_id", $order_id)->select();
        if(empty($order_goods) || empty($order_id)){
            $this->error('没有获取到订单信息');
        }
        $delivery = M('delivery_doc')->where("order_id", $order_id)->find();
        $this->assign('order_goods', $order_goods);
        $this->assign('delivery', $delivery);
        return $this->fetch();
    }

    /**
    * 取消订单
    */
    public function cancel_order()
    {
        $user_id = I('user_id');
        $order_id = I('order_id');

        $logic = new OrderLogic();
        $data = $logic->cancel_order($user_id, $order_id);
        
        if($data['status'] == 1){
            response_success('', '取消成功');
        } else {
            response_error('', $data['msg']);
        }
    }

    /**
     * [del_order 取消后可删除订单]
     * @return [type] [description]
     */
    public function del_order(){
        $user_id = I('user_id');
        $order_id = I('order_id');

        $OrderLogic = new OrderLogic();
        $OrderLogic->setUserId($user_id);
        $data = $OrderLogic->delOrder($order_id);
        
        if($data['status'] == 1){
            response_success('', '删除成功');
        } else {
            response_error('', $data['msg']);
        }
    }
    /**
     * 确定收货
     */
    public function receive_order()
    {
        $user_id = I('user_id');
        $order_id = I('order_id');

        $data = confirm_order($order_id, $user_id);

        if ($data['status'] != 1) {
            response_error('', $data['msg']);
        } else {
            response_success('', '操作成功');
        }
    }
    
    /**
     * 确定收货成功
     */
    public function order_confirm()
    {
        $id = I('id/d', 0);
        $data = confirm_order($id, $this->user_id);
        if(request()->isAjax()){
            $this->ajaxReturn($data);
        }
        if ($data['status'] != 1) {
            $this->error($data['msg'],U('Mobile/Order/order_list'));
        } else {
            $model = new UsersLogic();
            $order_goods = $model->get_order_goods($id);
            $this->assign('order_goods', $order_goods);
            return $this->fetch();
            exit;
        }
    }
    //订单支付后取消订单（退款）
    public function refund_order()
    {

        $user_id   = input('post.user_id', 0);
        $order_id   = input('post.order_id', 0);

        $OrderLogic = new OrderLogic;
        $return = $OrderLogic->recordRefundOrder($user_id, $order_id);

        if($return['status'] == 1){
            response_success('', '申请成功');
        } else {
            response_error('', $return['msg']);
        }
    }

    /**
     * 商品申请退款退货
     */
    public function return_goods()
    {
        $user_id = I('user_id');
        $order_id = I('order_id');

        $return_goods = M('return_goods')->where(array('order_id'=>$order_id))->find();
        if($return_goods) response_error('', '已经提交过退货申请');

        $order = M('order')->where(array('order_id'=>$order_id,'user_id'=>$user_id))->find();
        if(empty($order)) response_error('', '订单不存在');
        if($order['shipping_status'] == 1) response_error('', '订单已发货，不支持退款');

        
        $model = new OrderLogic();
        $res = $model->addReturnGoods($rec_id,$order);  //申请售后
        if($res['status']==1) response_success('', $res['msg']);
        response_error('', $res['msg']);
}

    /**
     * 退换货列表
     */
    public function return_goods_list()
    {
        //退换货商品信息
        $count = M('return_goods')->where("user_id", $this->user_id)->count();
        $pagesize = C('PAGESIZE');
        $page = new Page($count, $pagesize);
        $list = Db::name('return_goods')->alias('rg')
            ->field('rg.*,og.goods_name,og.spec_key_name')
            ->join('order_goods og','rg.rec_id=og.rec_id','LEFT')
            ->where(['rg.user_id'=>$this->user_id])
            ->order("rg.id desc")
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();
        $state = C('REFUND_STATUS');
        $this->assign('list', $list);
        $this->assign('state',$state);
        $this->assign('page', $page->show());// 赋值分页输出
        if (I('is_ajax')) {
            return $this->fetch('ajax_return_goods_list');
            exit;
        }
        return $this->fetch();
    }

    /**
     *  退货详情
     */
    public function return_goods_info()
    {
        $id = I('id/d', 0);
        $return_goods = M('return_goods')->where("id = $id")->find();
        if(empty($return_goods)){
            $this->error('参数错误');
        }
        $return_goods['seller_delivery'] = unserialize($return_goods['seller_delivery']);  //订单的物流信息，服务类型为换货会显示
        $return_goods['delivery'] = unserialize($return_goods['delivery']);  //订单的物流信息，服务类型为换货会显示
        if ($return_goods['imgs'])
            $return_goods['imgs'] = explode(',', $return_goods['imgs']);
        $goods = M('order_goods')->where("rec_id = {$return_goods['rec_id']} ")->find();
        $this->assign('state',C('REFUND_STATUS'));
        $this->assign('return_type', C('RETURN_TYPE'));
        $this->assign('goods', $goods);
        $this->assign('return_goods', $return_goods);
        return $this->fetch();
    }

    /**
     * 修改退货状态，发货
     */
    public function checkReturnInfo()
    {
        $data = I('post.');
        $data['delivery'] = serialize($data['delivery']);
        $data['status'] = 2;
        $res = M('return_goods')->where(['id'=>$data['id'],'user_id'=>$this->user_id])->save($data);
        if($res !== false){
            $this->ajaxReturn(['status'=>1,'msg'=>'发货提交成功','url'=>'']);
        }else{
            $this->ajaxReturn(['status'=>-1,'msg'=>'提交失败','url'=>'']);
        }
    }

    public function return_goods_refund()
    {
        $order_sn = I('order_sn');
        $where = array('user_id'=>$this->user_id);
        if($order_sn){
            $where['order_sn'] = $order_sn;
        }
        $where['status'] = 5;
        $count = M('return_goods')->where($where)->count();
        $page = new Page($count,10);
        $list = M('return_goods')->where($where)->order("id desc")->limit($page->firstRow, $page->listRows)->select();
        $goods_id_arr = get_arr_column($list, 'goods_id');
        if(!empty($goods_id_arr))
            $goodsList = M('goods')->where("goods_id in (".  implode(',',$goods_id_arr).")")->getField('goods_id,goods_name');
        $this->assign('goodsList', $goodsList);
        $state = C('REFUND_STATUS');
        $this->assign('list', $list);
        $this->assign('state',$state);
        $this->assign('page', $page->show());// 赋值分页输出
        return $this->fetch();
    }

    /**
     * 取消售后服务
     * @author lxl
     * @time 2017-4-19
     */
    public function return_goods_cancel(){
        $id = I('id',0);
        if(empty($id))$this->ajaxReturn(['status'=>-1,'msg'=>'参数错误']);
        $return_goods = M('return_goods')->where(array('id'=>$id,'user_id'=>$this->user_id))->find();
        if(empty($return_goods)) $this->ajaxReturn(['status'=>-1,'msg'=>'参数错误']);
        $res= M('return_goods')->where(array('id'=>$id))->save(array('status'=>-2,'canceltime'=>time()));
        if ($res !== false){
            $this->ajaxReturn(['status'=>1,'msg'=>'取消成功']);
        }else{
            $this->ajaxReturn(['status'=>-1,'msg'=>'取消失败']);
        }
    }
    /**
     * 换货商品确认收货
     * @author lxl
     * @time  17-4-25
     * */
    public function receiveConfirm(){
        $return_id=I('return_id/d');
        $return_info=M('return_goods')->field('order_id,order_sn,goods_id,spec_key')->where('id',$return_id)->find(); //查找退换货商品信息
        $update = M('return_goods')->where('id',$return_id)->save(['status'=>3]);  //要更新状态为已完成
        if($update) {
            M('order_goods')->where(array(
                'order_id' => $return_info['order_id'],
                'goods_id' => $return_info['goods_id'],
                'spec_key' => $return_info['spec_key']))->save(['is_send' => 2]);  //订单商品改为已换货
            $this->success("操作成功", U("Order/return_goods_info", array('id' => $return_id)));
        }
        $this->error("操作失败");
    }

    /**
     *  评论晒单
     * @return mixed
     */
    public function comment()
    {
        $user_id = $this->user_id;
        $status = I('get.status');
        $logic = new CommentLogic;
        $data = $logic->getComment($user_id, $status); //获取评论列表
        $this->assign('page', $data['page']);// 赋值分页输出
        $this->assign('comment_page', $data['page']);
        $this->assign('comment_list', $data['result']);
        $this->assign('active', 'comment');
        if(I('is_ajax')){
            return $this->fetch('ajax_comment_list');
        }
        return $this->fetch();
    }

    /**
     *添加评论
     */
    public function add_comment()
    {
        $user_id = input('user_id');
        // 晒图片
        $images = input('images');
        if($images) {
            $images = json_decode(html_entity_decode($images), true);
            $add['img'] = serialize($images);
        }

        $user_info = M('users')->where('user_id', $user_id)->field('email, nickname')->find();
        $logic = new UsersLogic();
        $add['user_id'] = $user_id;
        $add['email'] = $user_info['email'];
        $add['username'] = $user_info['nickname'];

        $add['rec_id'] = I('rec_id/d');
        $add['goods_id'] = I('goods_id/d');
        $add['is_anonymous'] = I('is_anonymous');  //是否匿名评价:0不是\1是
        $add['order_id'] = I('order_id/d');
        $add['service_rank'] = I('service_rank');
        $add['deliver_rank'] = I('deliver_rank');
        $add['goods_rank'] = I('goods_rank');
        $add['is_show'] = 1; //默认显示
        //$add['content'] = htmlspecialchars(I('post.content'));
        $add['content'] = I('content');
        $add['add_time'] = time();
        $add['ip_address'] = request()->ip();

        //添加评论
        $row = $logic->add_comment($add);
        if ($row['status'] == 1) {
            response_success('', '评论成功');
        } else {
            response_error('', $row['msg']);
        }
       
    }

    /**
     * 待收货列表
     * @author lxl
     * @time   2017/1
     */
    public function wait_receive()
    {
        $where = ' user_id=' . $this->user_id;
        //条件搜索
        if (I('type') == 'WAITRECEIVE') {
            $where .= C(strtoupper(I('type')));
        }
        $count = M('order')->where($where)->count();
        $pagesize = C('PAGESIZE');
        $Page = new Page($count, $pagesize);
        $show = $Page->show();
        $order_str = "order_id DESC";
        $order_list = M('order')->order($order_str)->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //获取订单商品
        $model = new UsersLogic();
        foreach ($order_list as $k => $v) {
            $order_list[$k] = set_btn_order_status($v);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
            $data = $model->get_order_goods($v['order_id']);
            $order_list[$k]['goods_list'] = $data['result'];
        }

        //统计订单商品数量
        foreach ($order_list as $key => $value) {
            $count_goods_num = 0;
            foreach ($value['goods_list'] as $kk => $vv) {
                $count_goods_num += $vv['goods_num'];
            }
            $order_list[$key]['count_goods_num'] = $count_goods_num;
            //订单物流单号
            $invoice_no = M('DeliveryDoc')->where("order_id", $value['order_id'])->getField('invoice_no', true);
            $order_list[$key][invoice_no] = implode(' , ', $invoice_no);
        }
        $this->assign('page', $show);
        $this->assign('order_list', $order_list);
        if ($_GET['is_ajax']) {
            return $this->fetch('ajax_wait_receive');
            exit;
        }
        return $this->fetch();
    }

    /**
     * 评论详情
     * @return mixed
     */
    public function comment_info(){
        $commentLogic = new \app\common\logic\CommentLogic;
        $comment_id = I('comment_id/d');
        $res = $commentLogic->getCommentInfo($comment_id);
        if(empty($res)){
            $this->error('参数错误！！');
        }
        if(!empty($res['comment_info']['img'])) $res['comment_info']['img'] = unserialize($res['comment_info']['img']);
        $user = get_user_info($res['comment_info']['user_id']);
        $res['comment_info']['nickname'] = $user['nickname'];
        $this->assign('comment_info',$res['comment_info']);
        $this->assign('comment_id',$res['comment_info']['comment_id']);
        $this->assign('reply',$res['reply']);
        $this->assign('user',$this->user);
        return $this->fetch();
    }

    /**
     * 评论别的用户评论
     */
    public function replyComment(){
        $data=I('post.');
        $data['reply_time'] = time();
        $data['deleted'] = 0;
        $return = Db::name('reply')->add($data);
        if($return){
            Db::name('comment')->where(['comment_id'=>$data['comment_id']])->setInc('reply_num');
            $data['reply_time'] = date('Y-m-d H:m',$data['reply_time']);
            $this->ajaxReturn(['status'=>1,'msg'=>'评论成功！','result'=>$data]);
            exit;
        } else {
            $this->ajaxReturn(['status'=>0,'msg'=>"评论失败"]);
        }
    }

    /**
     *  点赞
     */
    public function ajaxZan()
    {
        $comment_id = I('post.comment_id/d');
        $user_id = $this->user_id;
        $comment_info = M('comment')->where(array('comment_id' => $comment_id))->find();  //获取点赞用户ID
        $comment_user_id_array = explode(',', $comment_info['zan_userid']);
        if (in_array($user_id, $comment_user_id_array)) {  //判断用户有没点赞过
            $result = ['status' => 0, 'msg' => '您已经点过赞了~', 'result' => ''];
        } else {
            array_push($comment_user_id_array, $user_id);  //加入用户ID
            $comment_user_id_string = implode(',', $comment_user_id_array);
            $comment_data['zan_num'] = $comment_info['zan_num'] + 1;  //点赞数量加1
            $comment_data['zan_userid'] = $comment_user_id_string;
            M('comment')->where(array('comment_id' => $comment_id))->save($comment_data);
            $result = ['status' => 1, 'msg' => '点赞成功~', 'result' => ''];
        }
        exit(json_encode($result));
    }

    // 获取快递运输信息
    public function getExpressInfo(){
        $invoice_no = I('invoice_no');

        if($invoice_no == '') response_error('', '运单号不能为空');

        $ShippingLogic = new ShippingLogic();
        $result = $ShippingLogic->getExpressInfo($invoice_no);
        $result = json_decode($result, true);

        if($result['status'] == 200){
            response_success($result);
        } else {
            response_error('', '获取失败');
        }
    }

    // 执行退款单
   /* public function returnOrder(){
    	$user_id = I('user_id');
    	$order_id = I('order_id');

    	// 获取订单信息
    	$order = Db::name('order')->where('order_id', $order_id)->find();
    	if(empty($order)) response_error('', '订单不存在');
    	if($order['user_id'] != $user_id) response_error('', '非法操作');

    	if($order['pay_status'] != 1 || $order['shipping_status'] == 1) response_error('', '订单状态不允许退款');

    	// 执行退款申请
    	$order_return_data = array(
    		'order_id' => $order_id,
    		'user_id' => $user_id,
    		'add_time' => time(),
    	);
    	Db::name('return_order')->insert($order_return_data);
    	// 原订单标记退款状态
    	Db::name('order')->where('order_id', $order_id)->setField('order_status', 6);

    	response_success('', '操作成功');
    }*/
}