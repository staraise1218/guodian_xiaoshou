<?php

namespace app\api\controller;
use think\Db;
use app\api\logic\SmsLogic;
use app\common\logic\CurlLogic;

class Auth extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}


    /**
     * 登录
     */
    public function login()
    {
        $mobile = trim(I('mobile'));
        $password = trim(I('password'));

        if (!$mobile || !$password) {
        	response_error('', '请填写账号或密码');
        }
        $user = Db::name('users')->where("mobile", $mobile)->find();
        if (!$user) {
            response_error('', '用户名或密码填写错误，请核对后再次提交');
        } elseif (encrypt($password) != $user['password']) {
            response_error('', '用户名或密码填写错误，请核对后再次提交');
        } elseif ($user['is_lock'] == 1) {
            response_error('', '账号异常已被锁定！');
        }

        $userInfo = M('users')->where('user_id', $user['user_id'])->find();
        unset($userInfo['password']);
       	response_success($userInfo);
    }

    // 检测是否注册
    public function isRegister(){
    	$mobile = I('mobile');

    	$where['mobile'] = $mobile;
    	$count = M('users')->where($where)->count();
    	if($count){
    		response_error('', '该手机号已注册');
    	}
    	response_success('', '未注册');
    }
    
    // 检测手机验证码
    public function checkMobileCode(){
        $mobile = I('mobile');
        $code = I('code');
        // 验证码检测
        $SmsLogic = new SmsLogic();
        if($SmsLogic->checkCode($mobile, $code, '1', $error) == false) response_error('', $error);
        response_success('', '验证码正确');
    }

    /**
     *  手机号注册
     */
    public function register() {
    	$mobile = I('mobile');
    	$code = I('code');
        $password = trim(I('password'));
        $password_confirm = trim(I('password_confirm'));
    	
        if($password_confirm != $password) response_error('', '两次密码不一致');

    	if(check_mobile($mobile) == false){
    		response_error('', '手机号格式错误');
    	}

    	$userInfo = Db::name('users')->where("mobile={$mobile}")->find();
    	if($userInfo) response_error('', '该手机号已注册');

    	// 验证码检测
    	$SmsLogic = new SmsLogic();
        if($SmsLogic->checkCode($mobile, $code, '1', $error) == false) response_error('', $error);


    	if(empty($password)) response_error('', '密码不能为空');

        $username = generateUsername();
    	$map = array(
    		'mobile' => $mobile,
            'username' => $username,
    		'nickname' => $mobile,
    		'password' => encrypt($password),
    		'reg_time' => time(),
    		'last_login' => time(),
    		'token' => md5(time().mt_rand(1,999999999)),
    	);

    	$user_id = M('users')->insertGetId($map);
        if($user_id === false) response_error('', '注册失败');
        
        $userInfo = M('users')->where('user_id', $user_id)->find();
        unset($userInfo['password']);
        response_success($userInfo, '注册成功');
    }

    // 忘记密码
    public function resetPwd(){
        $mobile = I('mobile');
        $code = I('code');
        $password = I('password');
        $password_confirm = I('password_confirm');

        if($password_confirm != $password) response_error('','两次密码不一致');

        if(check_mobile($mobile) == false){
            response_error('', '手机号码有误');
        }
        // 检测验证码
        $SmsLogic = new SmsLogic();
        if($SmsLogic->checkCode($mobile, $code, '2', $error) == false) response_error('', $error);

        $user = Db::name('users')->where("mobile = $mobile")->find();
        if(empty($user)) response_error('', '手机号未注册');

        $password = encrypt($password);
        Db::name('users')->where("mobile=$mobile")->update(array('password'=>$password));

        response_success('', '密码修改成功');
    }

    /**
     * [thirdIsRegister 检测第三方登录是否已经授权]
     * @return [type] [description]
     */
    /*public function thirdIsRegister(){
        $account_id = I('account_id');
        $type = I('type');
        if(!in_array($type, array('weixin', 'qq', 'weibo'))) response_error('', '类型不正确');

        $is_register = Db::name('oauth_users')->where(array('openid'=>$account_id, 'oauth'=>$type))->count();

        response_success(array('is_register'=>$is_register));
    }*/

    /*
     * 第三方登录接口
     * openid 微信 openid ,微博 id ,qq: openid
     */
    public function thirdLogin(){
        $type = I('type');
        $openid = I('openid');

        // 检测用户是否已注册
        $oauth_users = Db::name('oauth_users')->where("openid='$openid'")->field('user_id')->find();
        if($oauth_users){
            $user_id = $oauth_users['user_id'];
        } else {
            // 
            $third_data = array(
                'openid' => $openid,
                'oauth' => $type,
            );
            // 将信息写入第三方登录表
            $id = Db::name('oauth_users')->insertGetId($third_data);

            $user_id = '';
        }

        if($user_id){
            $userInfo = M('users')->where('user_id', $user_id)->find();
            unset($userInfo['password']);
        } else {
            $userInfo['user_id'] = '';
        }

        response_success($userInfo);
    }

    /**
     * [bindMobile 绑定手机号]
     * @return [type] [description]
     */
    public function bindMobile(){
        $openid = input('openid');
        $nickname = I('nickname');
        $head_pic = I('head_pic');
        $mobile = input('mobile');
        $code = input('code');

        // 检测手机号格式
        if(check_mobile($mobile) == false) response_error('', '手机号码有误');
        // 检测验证码
        $SmsLogic = new SmsLogic();
        if($SmsLogic->checkCode($mobile, $code, '5', $error) == false) response_error('', $error);

        // 查看手机号是否注册
        $users = Db::name('users')->where('mobile', $mobile)->field('user_id')->find();
        if($users){
            $user_id = $users['user_id'];
        } else {
            $username = generateUsername();
            // 将信息写入用户表
            $userData = array(
                'mobile' => $mobile,
                'username' => $username,
                'nickname' => $nickname,
                'head_pic' => $head_pic,
                'reg_time' => time(),
                'last_login' => time(),
                'token' => md5(time().mt_rand(1,999999999)),
            );

            $user_id = Db::name('users')->insertGetId($userData);
        }
        // 更新三方登录表记录的user_id
        Db::name('oauth_users')->where('openid', $openid)->update(array('user_id'=>$user_id));

        $userInfo = M('users')->where('user_id', $user_id)->find();
        unset($userInfo['password']);
        response_success($userInfo, '绑定成功');
    }

    /**
     * [sendMobleCode 发送手机验证码]
     * @param [scene 1 注册 2 找回密码 3 免密登录 4 解绑时验证旧手机号 5 绑定新手机号]
     * @return [type] [description]
     */
    public function sendMobileCode(){
        $mobile = I('mobile');
        $scene = I('scene', 1);

        $count = Db::name('users')->where('mobile', $mobile)->count();
        if($scene == 1 && $count) response_error('该手机号已注册'); // 注册时判断手机号是否已注册
        if($scene == 2 && ! $count) response_error('该手机号未注册'); // 找回密码时判断手机号是否已注册


        $SmsLogic = new SmsLogic();
        $code = $SmsLogic->send($mobile, $scene, $error);
        if($code != false){
            response_success(array('code'=>$code), '发送成功');
        } else {
            response_error('', $error);
        }
    }

    /**
     * [getOpenid 微信小程序用，通过code获取openid]
     * @return [type] [description]
     */
    public function getOpenid(){
        $code = input('code');
        if($code == '') return '';

        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=wxc0b7441de48a2dd4&secret=6359f387e7ed6a6fd131f3ed436283cc&js_code='.$code.'&grant_type=authorization_code';

        $CurlLogic = new CurlLogic();
        $result = $CurlLogic->url($url)->data();

        $result = json_decode($result, true);
        if($result['openid']){
            response_success($result);
        } else {
            response_error($result);
        }
    }

    // 免密登录
    public function freeLogin(){
        $mobile = I('mobile');
        $code = I('code');

        if(check_mobile($mobile) == false) response_error('', '手机号格式错误');

        // 验证码检测
        $SmsLogic = new SmsLogic();
        if($SmsLogic->checkCode($mobile, $code, '3', $error) == false) response_error('', $error);

        $userInfo = Db::name('users')->where("mobile={$mobile}")->find();
        if($userInfo) {
            unset($userInfo['password']);
            response_success($userInfo);
        } else {
                $username = generateUsername();
                $map = array(
                    'mobile' => $mobile,
                    'username' => $username,
                    'nickname' => $mobile,
                    'password' => '',
                    'reg_time' => time(),
                    'last_login' => time(),
                    'token' => md5(time().mt_rand(1,999999999)),
                );

                $user_id = M('users')->insertGetId($map);
                if($user_id === false) response_error('', '注册失败');
                
                $userInfo = M('users')->where('user_id', $user_id)->find();
                unset($userInfo['password']);
                response_success($userInfo);
        }
    }
}