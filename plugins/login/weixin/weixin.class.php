<?php
use think\Model;
use think\Request;

class weixin extends Model{
//回调地址
    public $return_url;
    public $app_id = 'wx9bd20663dd090da1';
    public $app_secret = '04bcaf1a0a45e15953f8f629b5d815ca';
    public function __construct($config){
        $this->return_url = "http://".$_SERVER['HTTP_HOST']."/index.php/Home/LoginApi/callback/oauth/weixin";

        //$this->app_id = $config['app_id'];
        //$this->app_secret = $config['app_secret'];

    }
    //构造要请求的参数数组，无需改动
    public function login(){
        $_SESSION['state'] = md5(uniqid(rand(), TRUE));
        //拼接URL
        $dialog_url = "https://open.weixin.qq.com/connect/qrconnect?appid="
            . $this->app_id . "&redirect_uri=" . urlencode($this->return_url) . "&scope=snsapi_login&response_type=code&state="
            . $_SESSION['state'];
        echo("<script> top.location.href='" . $dialog_url . "'</script>");
        exit;
    }

    public function respon(){
        if($_REQUEST['state'] == $_SESSION['state'])
        {
            $code = $_REQUEST["code"];
            if (!$code) $this->redirect(U('Home/Index/index'));

            //拼接URL,获取access_t和 openid，unionid,具体看代码
            $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->app_id."&secret=".$this->app_secret."&code=".$code."&grant_type=authorization_code";
            $response = $this->get_contents($token_url);
            $data = json_decode($response,true);
            if (isset($data['errcode'])) {
                echo "<h3>error:</h3>" . $data['errmsg'];
                exit;
            }
            $arr['openid']=$data['openid'];
            $arr['oauth']='weixin';
            //拼接URL,获取用户个人信息
            $token_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$data['access_token']."&openid=".$data['openid'];
            $response = $this->get_contents($token_url);
            $data = json_decode($response,true);
            if (isset($data['errcode'])) {
                echo "<h3>error:</h3>" . $data['errmsg'];
                exit;
            }
            $arr['nickname'] = $data['nickname'];
            $arr['sex'] = $data['sex'];
//            $arr['province1'] = $data['province'];
//            $arr['city1'] = $data['city'];
            $arr['head_pic'] = $data['headimgurl'];
            $arr['unionid']=$data['unionid'];
return $arr;

        }else{
            return false;
        }
    }

    public function get_contents($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        $response =  curl_exec($ch);
        curl_close($ch);

        //-------请求为空
        if(empty($response)){
            exit("50001");
        }

        return $response;
    }
}
