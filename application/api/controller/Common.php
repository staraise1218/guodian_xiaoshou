<?php

namespace app\api\controller;

use think\Db;
use app\api\logic\FileLogic;
use app\api\logic\SmsLogic;
use app\common\logic\ShippingLogic;

class Common extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = '*';

		parent::__construct();
	}


    /**
     * [sendMobleCode 发送手机验证码]
     * @param [scene 1 注册 2 找回密码, 3 修改手机号]
     * @return [type] [description]
     */
    public function sendMobileCode(){
        $mobile = I('mobile');
        $scene = I('scene', 1);


        // 检测手机号
        if(check_mobile($mobile) == false) response_error('', '手机号格式错误');

        // 注册场景检测是否注册
        if($scene == '3'){
            $count = Db::name('users')->where("mobile=$mobile")
            	->count();
            if($count) response_error('', '该手机号已被占用');
        }

        $SmsLogic = new SmsLogic();
        $code = $SmsLogic->send($mobile, $scene, $error);
        if($code != false){
            response_success(array('code'=>$code), '发送成功');
        } else {
            response_error('', $error);
        }
    } 

    /**
     * [fileUpload 上传单文件]
     * @param   $[file] [<文件名>]
     * @param [type] $[type] [<类型 ]
     * @return [type] [description]
     */
    public function uploadFile(){
        $type = I('type');

        if( ! in_array($type, array('invite_image', 'dynamic_image', 'dynamic_video', 'head_pic'))) response_error('', '不被允许的类型');
        if(empty($_FILES)) response_error('文件不能为空');

        /************* 上传路径 ***************/        
        $uploadPath = UPLOAD_PATH.'files';
        if($type == 'invite_image') $uploadPath = UPLOAD_PATH.'invite/image';
        if($type == 'dynamic_image') $uploadPath = UPLOAD_PATH.'dynamics/image';
        if($type == 'dynamic_video') $uploadPath = UPLOAD_PATH.'dynamics/video';
        if($type == 'head_pic') $uploadPath = UPLOAD_PATH.'head_pic';
        
        $FileLogic = new FileLogic();
        $result = $FileLogic->uploadSingleFile('file', $uploadPath);
        if($result['status'] == '1'){
            $filepath = $result['fullPath'];
            $thumb = '';
            if($type == 'dynamic_video'){
                $thumb = $FileLogic->video2thumb($filepath);
            }
            response_success(array('filepath'=>$filepath, 'thumb'=>$thumb));
        } else {
            response_error('', '文件上传失败');
        }
    }

    // 多文件上传
    public function uploadMultiFile(){
        $type = I('type');

        if( ! in_array($type, array('goods_comment'))) response_error('', '不被允许的类型');
        if(empty($_FILES)) response_error('文件不能为空');

        /************* 上传路径 ***************/        
        if($type == 'goods_comment') $uploadPath = UPLOAD_PATH.'goods_comment';

        $FileLogic = new FileLogic();
        $result = $FileLogic->uploadMultiFile('file', $uploadPath);
        if($result['status'] == '1'){
            $images = $result['images'];

            response_success(array('images'=>$images));
        } else {
            response_error('', '文件上传失败');
        }

    }

    // 获取快递运输信息
    public function ajaxGetExpressInfo(){
        $invoice_no = I('invoice_no');

        if($invoice_no == '') response_error('', '运单号不能为空');

        $ShippingLogic = new ShippingLogic();
        $result = $ShippingLogic->getExpressInfo($invoice_no);

        echo $result;
    }

}