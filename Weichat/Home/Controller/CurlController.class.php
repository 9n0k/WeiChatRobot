<?php
namespace Home\Controller;
use Think\Controller;
class CurlController extends Controller {
    public function curl_get($url,$cookies){
        $curl = curl_init();  
		curl_setopt($curl, CURLOPT_URL, $url);  
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		if($cookies){
			///把cookies写入文件
			curl_setopt($curl, CURLOPT_COOKIEJAR, $cookies);
		}
		$output = curl_exec($curl);  
		curl_close($curl);
		return $output;
    }
	public function curl_post($url,$user_cookie_data){
		$cookies = dirname(__FILE__).'/wechat.cookie.txt';
        $curl = curl_init();  
		curl_setopt($curl, CURLOPT_URL, $url);  
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $user_cookie_data); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		if($cookies){
			curl_setopt($curl, CURLOPT_COOKIEFILE, $cookies);
		}
		$output = curl_exec($curl);  
		curl_close($curl);
		return $output;
    }
	public function curl_get_cookie($url){
		$cookies = dirname(__FILE__).'/wechat.cookie.txt';
        $curl = curl_init();  
		curl_setopt($curl, CURLOPT_URL, $url);  
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $cookies);
		$output = curl_exec($curl);  
		curl_close($curl);
		return $output;
    }
	
	public function getMillisecond(){
        list($t1, $t2) = explode(' ', microtime());
        return $t2 . ceil(($t1 * 1000));
    }
}