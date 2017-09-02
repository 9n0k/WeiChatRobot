<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
    	
		$url = 'https://login.weixin.qq.com/jslogin?appid=wx782c26e4c19acffb&redirect_uri=https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxnewloginpage&fun=new&lang=zh_CN&_=' . time();
		$result = A('Curl');
		$result = explode('"', $result->curl_get($url));	
		$erweima_img = 'https://login.weixin.qq.com/qrcode/' . $result[1];
		$this->assign('erweima_img',$erweima_img);
		$this->assign('uuid',$result[1]);
		$this->display();
    }
	public function login(){
		$uuid = $_GET['uuid'];
		$url = 'https://login.weixin.qq.com/cgi-bin/mmwebwx-bin/login?uuid=' . $uuid . '&tip=1&_=' . time();
		$data = '';
		$result = A('Curl');
		$result = $result->curl_get($url);
		$login_result = explode(';', $result);
		$login_result_1 = explode('"', $result);
		$login_result_url = explode('/', $result);
		session('wx_url',$login_result_url[2]);
		if($login_result[0] == 'window.code=201'){
			echo 'scan_success';
			return;
		}else if($login_result[0] == 'window.code=200'){
			$url = $login_result_1[1].'&fun=new';
			$cookies = dirname(__FILE__).'/wechat.cookie.txt';
			$result = A('Curl');
			$xml = simplexml_load_string($result->curl_get($url,$cookies));
			if($xml->ret == 1203){
				echo 'login_error';
				return;
			}
			session('user_cookie',json_decode(json_encode($xml),true));			
			echo 'login_success';
			return;
		}
	}
}