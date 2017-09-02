<?php
    include "TopSdk.php";
    date_default_timezone_set('Asia/Shanghai'); 
	$c = new TopClient;
	$c->appkey = '24586997';
	$c->secretKey = '80baf815a26a997cb0b45bb344547eca';
	$req = new WirelessShareTpwdCreateRequest;
	$tpwd_param = new GenPwdIsvParamDto;
	//$tpwd_param->ext="{\"你是谁\":\"我是我\"}";
	$tpwd_param->logo="http://img.alicdn.com/imgextra/i4/2510108289/TB2mu4RpC8mpuFjSZFMXXaxpVXa_!!2510108289.jpg_440x440.jpg";//商品首图
	$tpwd_param->url="http://uland.taobao.com/coupon/edetail?activityId=6a2d46fd6e1a45fabcf6dfa4afcbf7e6&itemId=537089753090&pid=&src=tkjd_tkjdzs&dx=1";//商品优惠券地址
	$tpwd_param->text="这是一次10万次内的测试项目";//商品简介
	$tpwd_param->user_id="15176707870";//发券人的淘宝ID
	$req->setTpwdParam(json_encode($tpwd_param),TRUE);
	$resp = $c->execute($req);
	print_r($resp);
?>