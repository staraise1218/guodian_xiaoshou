<?php

namespace app\api\controller; 
use think\Request;
use think\Db;

class Payment extends Base {
    
    public $payment; //  具体的支付类
    public $pay_code; //  具体的支付code
 
    /**
     * 析构流函数
     */
    public function  __construct() {   
        // 设置所有方法的默认请求方式
        $this->method = 'POST';

        parent::__construct();
        
        // 订单支付提交
        $pay_radio = $_REQUEST['pay_radio'];
        if(!empty($pay_radio)) 
        {                         
            $pay_radio = parse_url_param($pay_radio);
            $this->pay_code = $pay_radio['pay_code']; // 支付 code
        }
        else // 第三方 支付商返回
        {
            //file_put_contents('./a.html',$_GET,FILE_APPEND);    
            $this->pay_code = I('get.pay_code');
            unset($_GET['pay_code']); // 用完之后删除, 以免进入签名判断里面去 导致错误
        }                        
        //获取通知的数据
        if(empty($this->pay_code) || !in_array($this->pay_code, array('unionpay')))
            exit('支付方式异常');
        // 导入具体的支付类文件 
        include_once  "plugins/payment/{$this->pay_code}/{$this->pay_code}.class.php"; // D:\wamp\www\svn_tpshop\www\plugins\payment\alipay\alipayPayment.class.php                       
        $code = '\\'.$this->pay_code; // \alipay
        $this->payment = new $code();
    }
   
    /**
     * 提交支付方式
     */
    public function getCode(){        
            $order_id = I('order_id/d'); // 订单id
            $order = Db::name('Order')->where(['order_id' => $order_id])->find();
            if(empty($order)) response_error('', '订单不存在');
            if($order['order_status'] > 1) response_error('', '订单状态不允许');
            if($order['pay_status'] == 1) response_error('', '该订单已支付');


            // 订单支付提交
            $pay_radio = $_REQUEST['pay_radio'];
            $config_value = parse_url_param($pay_radio); // 类似于 pay_code=alipay&bank_code=CCB-DEBIT 参数
            $payBody = getPayBody($order_id);
            $config_value['body'] = $payBody;
            
            //微信JS支付
           if($this->pay_code == 'weixin' && $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
               $code_str = $this->payment->getJSAPI($order,$config_value);
               exit($code_str);
           }else{
             // p($order, $config_value);
                $config_value['frontUrl_app'] = 'http://www.guodianjm.com/index.php/home/payment/returnUrl/pay_code/unionpay/type/app';
           	    $code_str = $this->payment->get_code($order,$config_value);
           }
           
           response_success($code_str);
    }
    
    // 服务器点对点       
    public function notifyUrl(){
        $order_sn = input('post.orderId');
        if($this->payment->response()){
            $data = array(
                'pay_status' => 1,
                'pay_time' => time(),
            );
            Db::name('order')->where('order_sn', $order_sn)->update($data);
        }
    }

    // 页面跳转   
    public function returnUrl(){

        $result = $this->payment->respond2(); // $result['order_sn'] = '201512241425288593';


        $order = M('order')->where("order_sn", $result['order_sn'])->find();
        if(empty($order)) // order_sn 找不到 根据 order_id 去找
        {
            $order_id = session('order_id'); // 最近支付的一笔订单 id        
            $order = M('order')->where("order_id", $order_id)->find();
        }

        $this->assign('order', $order);
        if($result['status'] == 1){

            if(isMobile()){
                $this->redirect('mobile/payment/pay_success'); 
            }
            return $this->fetch('success');   
        } else {
            if(isMobile()){
                $this->redirect('mobile/payment/pay_error'); 
            }
            return $this->fetch('error');   
        }
    }  

    public function refundBack(){
    	$this->payment->refund_respose();
    	exit();
    }
    
    public function transferBack(){
    	$this->payment->transfer_response();
    	exit();
    }
}
