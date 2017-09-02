<?php
namespace Home\Controller;
use Think\Controller;
class MainController extends Controller {
    public function index(){

		$this->chushihua();
		$this->display();
    }
	public function chushihua(){
		$weichat = M("weichat_config")->select();
		$user_cookie = session('user_cookie');
		$url = "https://" . session('wx_url') . "/cgi-bin/mmwebwx-bin/webwxinit?r=" . time() . "&lang=ch_ZN&pass_ticket=" . $user_cookie[pass_ticket];
		$DeviceID = "e" . rand(10000000000, 9999999999) . rand(10000, 99999);
		$user_cookie_data = json_encode(array("BaseRequest"=>array("Uin"=>$user_cookie[wxuin],"Sid"=>$user_cookie[wxsid],"Skey"=>$user_cookie[skey],"DeviceID"=>$DeviceID)));
		$result = A('Curl');
		$result = $result->curl_post($url,$user_cookie_data);
		$result = json_decode($result);
		if($result->BaseRequest->Ret != 0){
			$this->error("初始化失败，请稍后再试","/index.php");
		}
		if($result->User->Sex == 0){
			$sex = "未设置";
		}elseif($result->User->Sex == 1){
			$sex = "男";
		}elseif($result->User->Sex == 2){
			$sex = "女";
		}
		$headimgurl = 'https://' . session('wx_url') . $result->User->HeadImgUrl;
		session('synckey',json_decode(json_encode($result->SyncKey),true));
		session('user', $result->User);
		$result_header = A('Curl');
		$headimgurl = $result_header->curl_get_cookie($headimgurl);
		$file_name = time().'_header.jpg';
		$fp = @fopen('ImageCache/'.$file_name, "a");
		fwrite($fp, $headimgurl);
		fclose($fp);
		$headimgurl = dirname(__ROOT__).'/../../../ImageCache/' .$file_name;
		$this->assign('headimgurl',$headimgurl);
		$this->assign('nickname',$result->User->NickName);
		$this->assign('sex',$sex);
		$this->assign('signature',$result->User->Signature);
		$this->assign('taobaopid',$weichat[0][taobao_pid]);
		$this->assign('alimama_appkey',$weichat[0][alimama_appkey]);
		$this->assign('alimama_secret',$weichat[0][alimama_secret]);
		$this->assign('robot_sends',$weichat[0][weichatqun]);
		$this->assign('taobao_goods',$weichat[0][dataoke]);
		$this->assign('robots_time',$weichat[0][robots_time]);
		$this->start_active();
	}
	public function start_active(){
		$url = "https://" . session('wx_url') . "/cgi-bin/mmwebwx-bin/webwxstatusnotify?lang=zh_CN&pass_ticket=" . $user_info[pass_ticket];
		$data = json_encode(array("BaseRequest"=>$user_info_arr,"Code"=>"3","FromUserName"=>$result->User->UserName,"ToUserName"=>$result->User->UserName,"ClientMsgId"=>time()),true);
		$result = A('Curl');
		$result = $result->curl_post($url,$data);
		$result = json_decode($result);
		if($result->BaseResponse->Ret != 0){
			$this->error("开启通知状态失败,请稍后再试","/index.php");
		}
		$this->get_friend_list();
	}
	public function get_friend_list(){
		$weichat = M("weichat_config")->select();
		$user_cookie = session('user_cookie');
		$getMillisecond = R('Curl/getMillisecond');
		$url = "https://" . session('wx_url') . "/cgi-bin/mmwebwx-bin/webwxgetcontact?lang=zh_CN&pass_ticket=" . $user_cookie[pass_ticket] . "&r=" . $getMillisecond . "&seq=0&skey=" . $user_cookie[skey];
		$data = "";
		$result = A('Curl');
		$result = $result->curl_post($url,$data);
		$friend_list_result = json_decode($result);
		if($friend_list_result->BaseResponse->Ret != 0){
			$this->error("获取好友列表失败,请稍后再试","/index.php");
		}
		foreach($friend_list_result->MemberList as $key=>$value){
			$member_list[$key] = json_decode(json_encode($value),true);
			$friend_or_qun_count = substr_count($member_list[$key][UserName],"@");
			if($friend_or_qun_count == 0){
				//echo "这是文件传输助手";
			}else if($friend_or_qun_count == 1){
				$frient_list[$key] = json_decode(json_encode($value),true);
			}else if($friend_or_qun_count == 2){
				$qun_list[$key] = json_decode(json_encode($value),true);
				foreach($qun_list as $val){
					if($val[NickName] == trim($weichat[0][weichatqun])){//优猫优惠券5GA5F5G
						session('taobaokequn',$val[UserName]);
					}
				}
			}
		}
		$this->assign('membercount', $friend_list_result->MemberCount);
		$this->assign('friendlist',$frient_list);//好友列表
		$this->assign('qunlist',$qun_list);//群列表
	}
	public function send_msg(){
		$this->assign('to_username',$_GET['username']);
		$this->display();
	}
	public function send_msg_do(){
		$msg = $_POST['msg'];
		$from_username = json_decode(json_encode(session('user')),true);
		$to_username = $_POST['username'];
		$user_cookie = session('user_cookie');
		$date = time().rand(1000,9999);
		$DeviceID = "e" . rand(10000000000, 9999999999) . rand(10000, 99999);
		$url = "https://" . session('wx_url') . "/cgi-bin/mmwebwx-bin/webwxsendmsg?lang=zh_CN&pass_ticket=" . $user_cookie[pass_ticket];
		$send_msg_user_cookie = array("DeviceID"=>$DeviceID,"Sid"=>$user_cookie[wxsid],"Skey"=>$user_cookie[skey],"Uin"=>$user_cookie[wxuin]);
		$send_msg_user_info = array("Type"=>1,"Content"=>$msg,"FromUserName"=>$from_username[UserName],"ToUserName"=>$to_username,"ClientMsgId"=>$date,"LocalID"=>$date,);
		$data = json_encode(array("BaseRequest"=>$send_msg_user_cookie,"Msg"=>$send_msg_user_info,"Scene"=>0),JSON_UNESCAPED_UNICODE);
		$result = A('Curl');
		$result = $result->curl_post($url,$data);
		$result = json_decode($result);
		if($result->BaseResponse->Ret == 0 ){
			echo "success";
			return;
		}
	}
	public function sync_status(){
		$user_cookie = session('user_cookie');
		$DeviceID = "e" . rand(10000000000, 9999999999) . rand(10000, 99999);
		$synckey = session('synckey');
		$getMillisecond = R('Curl/getMillisecond');
		foreach($synckey['List'] as $value){
			if($value[Key] == 1){
				$sync_key = $value['Key'] . "_" . $value['Val'];
			}else{
				$sync_key .= "|" . $value['Key'] . "_" . $value['Val'];
			}
		}
		
		$wx_url_push = session('wx_url') == 'wx2.qq.com' ？'webpush.wx2.qq.com' ：'webpush.wx.qq.com';
		
		$url = "https://" . $wx_url_push . "/cgi-bin/mmwebwx-bin/synccheck?r=" . $getMillisecond . "&skey=" . $user_cookie[skey] . "&sid=" . $user_cookie[wxsid] . "&uin=" . $user_cookie[wxuin] . "&deviceid=" . $DeviceID . "&synckey=" . $sync_key . "&_=" . $getMillisecond;
		$result = A('Curl');
		$result = $result->curl_get_cookie($url);
		$result = explode('"', $result);
		
		$data_log = "【skey】:" . $user_cookie[skey] . "\r\n" . "【wxsid】:" . $user_cookie[wxsid] . "\r\n" . "【uid】:" . $user_cookie[wxuin] . "\r\n" . "【synckey】:" . $sync_key . "\r\n" . "【提交地址】:" . $url . "\r\n" . "【同步结果】：" .  $result[1] . "\r\n" . '-------------------我是日志华丽丽的分割线【' . date('Y-m-d H:i:s',time()) . '】---------------------------------------------------' . "\r\n";
		$file_name = date('Y-m-d',time()).'_sync_status.txt';
		$fp = @fopen('SystemLog/'.$file_name, "a");
		fwrite($fp, $data_log);
		fclose($fp);
		
		if (trim($result[1]) != '0' ){
			echo 'error';
			return;
		}
//		}else{
//			$this->get_msg();
//		}
	}
	public function get_msg(){
		$user_cookie = session('user_cookie');
		$url = "https://" . session('wx_url') . "/cgi-bin/mmwebwx-bin/webwxsync?sid=" . $user_cookie[wxsid] . "&skey=" . $user_cookie[skey] . "&lang=zh_CN&pass_ticket=" . $user_cookie[pass_ticket];
		$DeviceID = "e" . rand(10000000000, 9999999999) . rand(10000, 99999);
		$synckey = session('synckey');
		$send_msg_user_cookie = array("Uin"=>$user_cookie[wxuin],"Sid"=>$user_cookie[wxsid],"Skey"=>$user_cookie[skey],"DeviceID"=>$DeviceID);
		$data = json_encode(array("BaseRequest"=>$send_msg_user_cookie,"SyncKey"=>$synckey,"rr"=>~time()));
		$result = A('Curl');
		$result = $result->curl_post($url,$data);
		$new_msg = json_decode($result,TRUE);
		session('synckey',$new_msg[SyncKey]);
		if(!empty($new_msg[AddMsgList])){
			foreach ($new_msg[AddMsgList] as $value){
				$msg[username] = $value[FromUserName];
				$msg[content] = $value[Content];
				$robot_api = "http://www.tuling123.com/openapi/api?key=84670767f4bb4fe8bcca0f0330446ddc&info=".$msg[content];
				$result_msg = json_decode(file_get_contents($robot_api),true);
				$count_username = substr_count($msg[username], "@");
				if($count_username == 1){
					$this->robot_send_msg_pro($msg[username],$result_msg[text]);////此处可以设定自动聊天对话
				}
				if($count_username == 2){
					//$this->robot_send_msg(session('taobaokequn'),$result_msg[text]);//此处可以设定自动聊天的群
				}
			}
		}
	}
	public function robot_send_msg_pro($tousername,$msg){
		$from_username = json_decode(json_encode(session('user')),true);
		$user_cookie = session('user_cookie');
		$date = time().rand(1000,9999);
		$DeviceID = "e" . rand(10000000000, 9999999999) . rand(10000, 99999);
		$url = "https://" . session('wx_url') . "/cgi-bin/mmwebwx-bin/webwxsendmsg?lang=zh_CN&pass_ticket=" . $user_cookie[pass_ticket];
		$send_msg_user_cookie = array("DeviceID"=>$DeviceID,"Sid"=>$user_cookie[wxsid],"Skey"=>$user_cookie[skey],"Uin"=>$user_cookie[wxuin]);
		$send_msg_user_info = array("Type"=>1,"Content"=>$msg,"FromUserName"=>$from_username[UserName],"ToUserName"=>$tousername,"ClientMsgId"=>$date,"LocalID"=>$date,);
		$data = json_encode(array("BaseRequest"=>$send_msg_user_cookie,"Msg"=>$send_msg_user_info,"Scene"=>0),JSON_UNESCAPED_UNICODE);
		$result = A('Curl');
		$result = $result->curl_post($url,$data);
		$result = json_decode($result);
	}

	public function taobaoke(){
		$weichat = M("weichat_config")->select();
		$pid = $weichat[0][taobao_pid];
		$url = $weichat[0][dataoke];
		$result = A('Curl');
		$result = $result->curl_get($url);
		$result = json_decode($result,TRUE);
		foreach($result[result] as $value){
			if($value[Org_Price] <= '100' && $value[Sales_num] >= '50' && $value[Quan_price] >= '50'){
				$tk_result['title'] = $value[D_title];//商品短标题
				$tk_result['goodsid'] = $value[GoodsID];
				$tk_result['goods_img'] = $value[Pic];//商品主图
				$tk_result['org_price'] = $value[Org_Price];//商品原价
				$tk_result['quan_price'] = $value[Quan_price];//优惠券金额
				$tk_result['price'] = $value[Price];//券后价
				$tk_result['introduce'] = $value[Introduce];//商品文案
				$tk_result['quan_link'] = explode('activity_id=', $value[Quan_link]);//二合一链接需要的参数
			}
		}
		//二合一链接拼接
		$erheyi_url = 'https://uland.taobao.com/coupon/edetail?activityId=' . $tk_result['quan_link'][1] . '&itemId=' . $tk_result['goodsid'] . '&pid=' . $pid;
		////////////////////////////////判断商品title是否与上一次相同如果相同休眠10分钟之后继续获取
		if(empty(session('goods_title')) || session('goods_title') !=  $tk_result['title'] ){
		////////////////////////////////调用阿里妈妈接口生成淘口令开始
			vendor('Alimama.TopSdk');
			date_default_timezone_set('Asia/Shanghai'); 
			$c = new \TopClient;
			$c->appkey = $weichat[0][alimama_appkey];
			$c->secretKey = $weichat[0][alimama_secret];
			$req = new \WirelessShareTpwdCreateRequest;
			$tpwd_param = new \GenPwdIsvParamDto;
			//$tpwd_param->ext="{\"你是谁\":\"我是我\"}";
			$tpwd_param->logo= $tk_result['goods_img'];//商品首图
			$tpwd_param->url= $erheyi_url;//商品优惠券地址
			$tpwd_param->text= $tk_result['introduce'];//商品文案
			$tpwd_param->user_id= "30209236";//发券人的淘宝ID
			$req->setTpwdParam(json_encode($tpwd_param),TRUE);
			$resp = $c->execute($req);
			$resp = json_decode(json_encode($resp),true);
			////////////////////////////////调用阿里妈妈接口生成淘口令结束
		
			////////////////////////获取图片Mediaid
			$mediaid = $this->img_msg_upload($tk_result['goods_img']);
			////////////////////////发送图片
			if($mediaid){
				sleep(2);
				$img_send_result = $this->robot_send_msg_img($mediaid);
				
				if($img_send_result == 0){
					////////////////////////发送文字消息
					sleep(2);
					$text_msg_send_result = $this->taobao_send_msg($tk_result['title'],$tk_result['org_price'],$tk_result['quan_price'],$tk_result['price'],$resp['model']);
					session('goods_title',$tk_result['title']);
					
					if($text_msg_send_result->Ret != 0 ){
						echo 'error';
						return;
					}
				}
			}else{
				echo 'error';
				return;
			}
		}else{
			sleep(60);
			$this->taobaoke();
		}
		
		
	}
	
	public function taobao_send_msg($title,$org_price,$quan_price,$price,$resp){
$msg = '【标题】' . $title . '
【原价】' . $org_price . '元
【优惠券】' . $quan_price . '元
【券后价】' . $price . '元
----------------------------
☆淘口令' . $resp . '
★复制消息
☆打开【手机淘宝】即可查看
★享受低价好生活做持家小能手';
		$result = $this->robot_send_msg($msg);
		return $result;
	}
	public function robot_send_msg($msg){
		$tousername = session('taobaokequn');
		$from_username = json_decode(json_encode(session('user')),true);
		$user_cookie = session('user_cookie');
		$date = time().rand(1000,9999);
		$DeviceID = "e" . rand(10000000000, 9999999999) . rand(10000, 99999);
		$url = "https://" . session('wx_url') . "/cgi-bin/mmwebwx-bin/webwxsendmsg?lang=zh_CN&pass_ticket=" . $user_cookie[pass_ticket];
		$send_msg_user_cookie = array("DeviceID"=>$DeviceID,"Sid"=>$user_cookie[wxsid],"Skey"=>$user_cookie[skey],"Uin"=>$user_cookie[wxuin]);
		$send_msg_user_info = array("Type"=>1,"Content"=>$msg,"FromUserName"=>$from_username[UserName],"ToUserName"=>$tousername,"ClientMsgId"=>$date,"LocalID"=>$date,);
		$data = json_encode(array("BaseRequest"=>$send_msg_user_cookie,"Msg"=>$send_msg_user_info,"Scene"=>0),JSON_UNESCAPED_UNICODE);
		$result = A('Curl');
		$result = $result->curl_post($url,$data);
		$result = json_decode($result);
		return $result;
	}
	
	public function img_msg_upload($img){
		$from_username = json_decode(json_encode(session('user')),true);
		if(empty($img)){
			return $this->taobaoke();
		}
		///////////////获取远程图片到本地开始
		ob_start();
		readfile($img);
		$ext = strrchr($img, '.');
		$file_name = time().mt_rand(10000, 99999).$ext;
		$img = ob_get_contents();
		ob_end_clean();
		$fp = @fopen('ImageCache/'.$file_name, "a");
		fwrite($fp, $img);
		fclose($fp);
		sleep(2);
		///////////////获取远程图片到本地结束
		$img_juedui = dirname(__FILE__).'/../../../ImageCache/' .$file_name;
		//print_r(filesize($img_juedui));
		/////////////////////////////////////////////////////////////////上传图片到微信服务器
		$user_cookie = session('user_cookie');
		$date = time().rand(1000,9999);
		$DeviceID = "e" . rand(10000000000, 9999999999) . rand(10000, 99999);
		$cookies = dirname(__FILE__).'/wechat.cookie.txt';
		$url = 'https://file.' . session('wx_url') . '/cgi-bin/mmwebwx-bin/webwxuploadmedia?f=json';
		$fp = fopen($cookies, 'r');
		while($line = fgets($fp)){
			if(strpos($line,'webwx_data_ticket') !== FALSE){
				$arr = explode('webwx_data_ticket', trim($line));
				$webwx_data_ticket = trim($arr[1]);
				break;
			}
		}
		fclose($fp);
		if($webwx_data_ticket == ''){
			$this->error('cookies值不完整','/index.php');
		}
		$send_msg_user_cookie = array("DeviceID"=>$DeviceID,"Sid"=>$user_cookie[wxsid],"Skey"=>$user_cookie[skey],"Uin"=>$user_cookie[wxuin]);
		$media_id = 0;//文件ID
		$clientmediaid = time();
		$lastmodifiedate = gmdate('D M d Y H:i:s TO', filemtime($img_juedui)).' (CST)';//最后修改时间
		$file_size = filesize($img_juedui);//文件大小
		if(getimagesize($img_juedui) == false){
			sleep(30);
			return $this->taobaoke();
		}
		$mediatype = 'pic';//文件媒体类型doc和pic
		$file_md5 = md5_file($img_juedui);
		$fromusername = $from_username[UserName];
		$tousername = session('taobaokequn');
		$uploadmediarequest = json_encode(array('BaseRequest'=>$send_msg_user_cookie,'ClientMediaId'=>$clientmediaid,'TotalLen'=>$file_size,'StartPos'=>0,'DataLen'=>$file_size,'MediaType'=>4,'UploadType'=>2,'FromUserName'=>$fromusername,'ToUserName'=>$tousername,'FileMd5'=>$file_md5),JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$pass_ticket = $user_cookie[pass_ticket];
		
		$data['id'] = 'WU_FILE_' .$media_id;
		$data['name'] = basename($img_juedui);
		$data['type'] = mime_content_type($img_juedui);
		$data['lastModifieDate'] = $lastmodifiedate;
		$data['size'] = filesize($img_juedui);
		$data['mediatype'] = $mediatype;
		$data['uploadmediarequest'] = $uploadmediarequest;
		$data['webwx_data_ticket'] = $webwx_data_ticket;
		$data['pass_ticket'] = $pass_ticket;
		$data['filename'] = new \CURLFile($img_juedui);

		$cookies = dirname(__FILE__).'/wechat.cookie.txt';
        $curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);  
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36');
		curl_setopt($curl, CURLOPT_REFERER, 'https://'. session('wx_url'));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		if($cookies){
			curl_setopt($curl, CURLOPT_COOKIEFILE, $cookies);
		}
		$output = curl_exec($curl);  
		curl_close($curl);
		$result = json_decode($output,TRUE);
		if($result[Ret] != 0){
			$this->error('图片上传失败','/index.php');
		}
		sleep(2);
		return $result[MediaId];
		
	}
	public function robot_send_msg_img($mediaid){
		$tousername = session('taobaokequn');
		$from_username = json_decode(json_encode(session('user')),true);
		$user_cookie = session('user_cookie');
		$date = time().rand(1000,9999);
		$DeviceID = "e" . rand(10000000000, 9999999999) . rand(10000, 99999);
		$url = "https://" . session('wx_url') . "/cgi-bin/mmwebwx-bin/webwxsendmsgimg?fun=async&f=json&lang=zh_CN&pass_ticket=" . $user_cookie[pass_ticket];
		$send_msg_user_cookie = array("DeviceID"=>$DeviceID,"Sid"=>$user_cookie[wxsid],"Skey"=>$user_cookie[skey],"Uin"=>$user_cookie[wxuin]);
		$send_msg_user_info = array("Type"=>3,"MediaId"=>$mediaid,"FromUserName"=>$from_username[UserName],"ToUserName"=>$tousername,"ClientMsgId"=>$date,"LocalID"=>$date,);
		$data = json_encode(array("BaseRequest"=>$send_msg_user_cookie,"Msg"=>$send_msg_user_info,"Scene"=>0),JSON_UNESCAPED_UNICODE);
		$result = A('Curl');
		$result = $result->curl_post($url,$data);
		$result = json_decode($result);
		sleep(2);
		return $result->Ret;
	}
	public function login_out(){
		$do = $_GET['do'];
		$user_cookie = session('user_cookie');
		if($do == 'login_out'){
			$url = 'https://' . session('wx_url') . '/webwxlogout?redirect=1&type=1&skey=' . $user_cookie[skey];
	        $data = array('sid' => $user_cookie[wxsid],'uin' => $user_cookie[wxuin]);
			$result = A('Curl');
			$result = $result->curl_post($url,$data);
		}
    }
    public function update_taobao_info(){
		
		$id = $_POST['id'];
		$value = $_POST['val'];

		if($id){
			if($value){
					if($id == 'taobaopid_save'){
						$data['taobao_pid'] = $value;
					}elseif($id == 'alimama_appkey_save'){
						$data['alimama_appkey'] = $value;
					}elseif($id == 'alimama_secret_save'){
						$data['alimama_secret'] = $value;
					}elseif($id == 'taobao_goods_save'){
						$data['dataoke'] = $value;
					}elseif($id == 'robot_sends_save'){
						$data['weichatqun'] = $value;
					}elseif($id == 'robots_time_save'){
						$data['robots_time'] = $value;
					}
				$result = M('weichat_config')->where('id = 1')->save($data);

			}else{
				echo "error";
				return;
			}
	
		}else{
			echo "error";
			return; 
		}
		
    }
    
}