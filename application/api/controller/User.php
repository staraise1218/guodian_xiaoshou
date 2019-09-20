<?php

namespace app\api\controller;

use think\Db;
use app\api\logic\FileLogic;
use app\api\logic\SmsLogic;
use app\common\logic\UsersLogic;
use app\common\logic\ActivityLogic;

class User extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}

    public function index(){
        $user_id = I('user_id');


        $UsersLogic = new UsersLogic();
        $data = $UsersLogic->get_info($user_id);
        // 为您挑选
        $sql = "SELECT goods_id, goods_name,shop_price, market_price, original_img FROM tp_goods
                WHERE goods_id >= (SELECT floor( RAND() * ((SELECT MAX(goods_id) FROM tp_goods)-(SELECT MIN(goods_id) FROM tp_goods)) + (SELECT MIN(goods_id) FROM tp_goods))) and store_count > 0
                ORDER BY store_count !=0 desc, goods_id LIMIT 8";
        $recommendList = Db::query($sql);



        $result['userInfo'] = $data['result'];
        $result['recommendList'] = $recommendList;
        response_success($result);
        
    }

    public function getUserInfo(){
        $user_id = I('user_id');
        $userInfo = M('users')
            ->where("user_id", $user_id)
            ->where('is_lock', 0)
            ->find();
        unset($userInfo['password']);
        unset($userInfo['pay_password']);
       
       // 计算年龄
        response_success($userInfo);
    }

    /**
     * [uploadFile 上传头像/认证视频 app 原生调用]
     * @param [type] $[type] [文件类型 head_pic 头像 auth_video 视频认证]
     * @param  $[action] [ 默认 add 添加 edit 修改]
     * @return [type] [description]
     */
    public function changeHeadPic(){
        $user_id = I('user_id/d');

        $uploadPath = UPLOAD_PATH.'head_pic/';

        $FileLogic = new FileLogic();
        $result = $FileLogic->uploadSingleFile('head_pic', $uploadPath);
        if($result['status'] == '1'){
            $fullPath = $result['fullPath'];

            Db::name('users')->update(array('user_id'=>$user_id, 'head_pic'=>$fullPath));

            $resultdata = array('head_pic'=>$fullPath);
            response_success($resultdata, '上传成功');
            
        } else {
            response_error('', '上传失败');
        }
    }

    /**
     * [changeField description]
     * @return [type] [description]
     */
    public function changeField(){
        $user_id = I('user_id');
        $field = input('post.field');
        $fieldValue = input('post.fieldValue');

        if(!in_array($field, array('username', 'nickname', 'personal_statement', 'realname', 'sex', 'birthday'))) response_error('', '不被允许的字段');

        if($field == 'username' || $field == 'nickname'){
            if(!preg_match('/^[A-Za-z0-9_\x{4e00}-\x{9fa5}]+$/u', $fieldValue) || mb_strlen($fieldValue) > 20 || mb_strlen($fieldValue) < 4){
                response_error('', '该值不符合规定');
            }

            // 检测是否存在
            $count = Db::name('users')->where("$field", "$fieldValue")->count();
            if($count) response_error('', '已存在');
        }

        Db::name('users')->where('user_id', $user_id)->update(array($field=>$fieldValue));
        response_success('', '修改成功');
    }

    /**
     * [checkMobile 设置支付密码前的手机号验证]
     * @return [type] [description]
     */
    public function checkMobile(){
        $user_id = I('user_id');
        $mobile = I('mobile');
        $code = I('code');

         // 验证码检测
        $SmsLogic = new SmsLogic();
        if($SmsLogic->checkCode($mobile, $code, 3, $error) == false) response_error('', $error);

        // 检测手机号是否存在
        $count = Db::name('users')->where('user_id', $user_id)->where('mobile', $mobile)->count();
        if(!$count) response_error('', '手机号不存在');

        response_success('', '验证成功');
    }

    public function setPayPassword(){
        $user_id = I('user_id');
        $password = I('password');
        $password_confirm = I('password_confirm');

        if($password_confirm != $password) response_error('', '两次密码不一致');
        Db::name('users')->where('user_id', $user_id)->setField('pay_password', encrypt($password));

        response_success('', '设置成功');
    }

    // 余额明细
    public function userMoneyLog(){
        $user_id = I('user_id/d');
        $page = I('page/d', 1);

        $account_log = M('account_log')
        ->where("user_id=" . $user_id." and user_money!=0 ")
        ->field("user_money, FROM_UNIXTIME(change_time, '%Y-%m-%d %H:%i:%s') change_time, desc, order_sn")
        ->page($page)
        ->limit(20)
        ->order('log_id desc')
        ->select();

        response_success($account_log);
    }

    /**
     * [couponlist 优惠券列表/优惠券领取页]
     * @return [type] [description]
     */
    public function couponlist(){
        $user_id = I('user_id');
        $page = I('page', 1);

        $where = array(
            'type'  => 2,
            'send_start_time'   => ['<', time()],
            'send_end_time' => ['>', time()],
            'status'    => 1,
            'createnum' => ['exp', ' > `send_num`'],
        );
        $list = Db::name('coupon')->alias('c')
            ->where($where)
            ->where(function($query) use ($user_id){
                $query->table('tp_coupon_list')->where('uid', $user_id)->where('cid', 'exp', '=`c`.`id`');
            }, 'not exists')
            ->field('id, name, money, condition, use_start_time, use_end_time')
            ->order('id desc')
            ->page($page)
            ->limit(20)
            ->select();
        if(!empty($list)){
            foreach ($list as &$item) {
                $count =  Db::name('coupon_list')
                    ->where('cid', $item['id'])
                    ->where('uid', $user_id)
                    ->count();
                $item['is_get'] = $count ? 1 : 0;
            }
        }

        response_success($list);
    }
    
    /**
     * 我的优惠券
     */
    public function coupon()
    {
        $user_id = I('user_id');
        $type = I('type/d');

        $logic = new UsersLogic();
        $data = $logic->get_coupon($user_id, $type);

        foreach($data['result'] as $k =>$v){
            $user_type = $v['use_type'];
            $data['result'][$k]['use_scope'] = C('COUPON_USER_TYPE')["$user_type"];
            /*if($user_type==1){ //指定商品
                $data['result'][$k]['goods_id'] = M('goods_coupon')->field('goods_id')->where(['coupon_id'=>$v['cid']])->getField('goods_id');
            }
            if($user_type==2){ //指定分类
                $data['result'][$k]['category_id'] = Db::name('goods_coupon')->where(['coupon_id'=>$v['cid']])->getField('goods_category_id');
            }*/
        }
        $coupon_list = $data['result'];
        
        response_success($coupon_list);
    }

    /**
     * 领优惠券
     */
    public function getCoupon()
    {
        $user_id = input('user_id/d');
        $coupon_id = I('coupon_id/d');

        $activityLogic = new ActivityLogic();
        $result = $activityLogic->get_coupon($coupon_id, $user_id);

        if($result['status'] == 1){
            response_success('', '领取成功');
        } else {
            response_error('', $result['msg']);
        }
        
    }

    /**
     * 浏览记录
     */
    public function visit_log()
    {
        $user_id = I('user_id/d');
        $page = I('page/d', 1);

        $visit = M('goods_visit')->alias('v')
            ->field('v.visit_id, v.goods_id, v.visittime, g.goods_name, g.original_img, g.shop_price, g.cat_id')
            ->join('__GOODS__ g', 'v.goods_id=g.goods_id')
            ->where('v.user_id', $user_id)
            ->order('v.visittime desc')
            ->page($page)
            ->limit(20)
            ->select();

        /* 浏览记录按日期分组 */
        $curyear = date('Y');
        $visit_list = [];
        foreach ($visit as $v) {
            if ($curyear == date('Y', $v['visittime'])) {
                $date = date('m月d日', $v['visittime']);
            } else {
                $date = date('Y年m月d日', $v['visittime']);
            }
            $visit_list[$date][] = $v;
        }

        response_success($visit_list);
    }

    /**
     * 删除浏览记录
     */
    public function del_visit_log()
    {
        $visit_ids = I('get.visit_ids', 0);
        $visit_ids = trim($visit_ids, ',');

        $row = M('goods_visit')->where('visit_id','IN', $visit_ids)->delete();

        if($row) {
            response_success('', '操作成功');
        } else {
            response_error('', '操作失败');
        }
    }

    // 收藏的商品
    public function collectGoodslist()
    {
        $user_id = I('user_id');
        $page = I('page');

        $list = M('goods_collect')->alias('c')
            ->field('c.collect_id,c.add_time,g.goods_id,g.goods_name,g.shop_price,g.original_img,g.store_count')
            ->join('goods g','g.goods_id = c.goods_id','INNER')
            ->where("c.user_id = $user_id")
            ->where('g.is_on_sale', 1)
            ->page($page)
            ->limit(20)
            ->select();

        response_success($list);
    }

    /*
     *取消收藏
     */
    public function cancel_collect()
    {
        $user_id = I('user_id');
        $collect_id = I('collect_id/d');

        if (M('goods_collect')->where(['collect_id' => $collect_id, 'user_id' => $user_id])->delete()) {
            response_success('', '操作成功');
        } else {
            response_error('', '操作失败');
        }
    }

    

    // 消息列表
    public function message(){
        $user_id = I('user_id/d');
        $page = I('page/d', 1);

        $limit_start = ($page-1)*20;

        $message = Db::name('message')->alias('m')
            ->join('user_message um', 'um.message_id=m.message_id', 'left')
            ->where('user_id', $user_id)
            ->whereOr('m.type', 1)
            ->field('m.message_id, message, m.category, data, send_time, status')
            ->order('message_id desc')
            ->limit($limit_start, 20)
            ->select();

        if(!empty($message)){
            // $now_date = strtotime(date('Y-m-d')); // 今日凌晨
            // $mid_date = strtotime(date('Y-m-d 12:00:00')) ;// 今日中午

            foreach ($message as &$item) {
                // if($item['send_time'] < $now_date) $item['send_time'] = date('Y-m-d', $item['send_time']);
                // if($item['send_time'] > $now_date && $item['send_time'] < $mid_date) $item['send_time'] = '上午'.date('H:i', $item['send_time']);
                // if($item['send_time'] > $mid_date) $item['send_time'] = '下午'.date('H:i', $item['send_time']);

                if($item['data']) $item['data'] = unserialize($item['data']);
            }
        }

        response_success($message);
    }

    // 验证旧手机号
    public function checkOldMobile(){
        $user_id = I('user_id');
        $code = I('code');

        $user = Db::name('users')->where('user_id', $user_id)->find();
        if($user['mobile'] == '') response_error('', '手机号未填写');
         // 验证码检测
        $SmsLogic = new SmsLogic();
        if($SmsLogic->checkCode($user['mobile'], $code, 4, $error) == false) response_error('', $error);

        response_success('', '验证成功');
    }

    /**
     * [bindMobile 绑定新手机号]
     * @return [type] [description]
     */
    public function bindNewMobile(){
        $user_id = input('user_id');
        $mobile = input('mobile');
        $code = input('code');

        // 检测手机号格式
        if(check_mobile($mobile) == false) response_error('', '手机号码有误');
        // 检测验证码
        $SmsLogic = new SmsLogic();
        if($SmsLogic->checkCode($mobile, $code, 5, $error) == false) response_error('', $error);

        // 查看手机号是否注册
        $count = Db::name('users')
            ->where('mobile', $mobile)
            ->where('user_id', ['neq', $user_id])
            ->count();
        if($count) response_error('', '该手机号已被别人绑定');
        // 更新手机号
        Db::name('users')->where('user_id', $user_id)->update(array('mobile'=>$mobile));

        response_success('', '绑定成功');
    }

    // 身份证实名认证
    public function IDCardAuth(){
        $user_id = input('post.user_id');
        $IDCard = input('post.IDCard');
        $realname = input('post.realname');
        if($realname == '') response_error('', '姓名不能为空');
        if( ! is_idcard($IDCard)) response_error('', '身份证号格式不正确');

        $host = "https://idcert.market.alicloudapi.com";
        $path = "/idcard";
        $method = "GET";
        $appcode = "b39931ae19e7430885cd5e77845af2b4";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "idCard={$IDCard}&name={$realname}";
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, ture);
        //curl_setopt($curl, CURLOPT_HEADER, true); 如不输出json, 请打开这行代码，打印调试头部状态码。
        //状态码: 200 正常；400 URL无效；401 appCode错误； 403 次数用完； 500 API网管错误
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $out_put = curl_exec($curl);

        $result = json_decode($out_put, true);

        if($result['status'] == '01') {
            $data = array(
                'realname' => $realname,
                'IDCard' => $IDCard,
                'sex' => $result['sex'] == '男' ? '1' : '2',
                'birthday' => $result['birthday'],
            );
            Db::name('users')->where('user_id', $user_id)->update($data);
            response_success($result, '认证通过');
        } else {
            response_error('', '认证失败');
        }
    }
}