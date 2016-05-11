<?php
/**
 * @copyright: @速玩广州 2015
 * @description: 分析优酷的视频的json文件
 * @file: youku.parse.class.php
 * @author: xiongjianbang
 * @charset: UTF-8
 * @time: 2015-04-25 18:22
 * @version 1.0
 **/
class Youku {
    
    //优酷视频的vid号
    private $vid=0;
    
    public function __construct($vid=0){
    	$this->vid = $vid;
    }
    
    public function cget($url = '', $user_agent = '',$data='',$referer = '' ,$is_proxy = false){
        if (!$url) return;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_REFERER, "");
        if(!empty($data)){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        ob_start();
        curl_exec($ch);
        $html = ob_get_contents();
        ob_end_clean();
        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        if (!is_string($html) || !strlen($html)) {
            return false;
        }
        return $html;
    }
        
    public function yk_d($a){
    	if (!$a) {
    	   return '';
    	}
    	$f = strlen($a);
    	$b = 0;
    	$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    	for ($c = ''; $b < $f;) {
    	$e = $this->charCodeAt($a, $b++) & 255;
    	if ($b == $f) {
    		$c .= $this->charAt($str, $e >> 2);
    		$c .= $this->charAt($str, ($e & 3) << 4);
    		$c .= '==';
    		break;
    	}
    	$g = $this->charCodeAt($a, $b++);
    	if ($b == $f) {
    		$c .= $this->charAt($str, $e >> 2);
    		$c .= $this->charAt($str, ($e & 3) << 4 | ($g & 240) >> 4);
    		$c .= $this->charAt($str, ($g & 15) << 2);
    		$c .= '=';
    		break;
    	}
    	$h = $this->charCodeAt($a, $b++);
    	$c .= $this->charAt($str, $e >> 2);
    	$c .= $this->charAt($str, ($e & 3) << 4 | ($g & 240) >> 4);
    	$c .= $this->charAt($str, ($g & 15) << 2 | ($h & 192) >> 6);
    	$c .= $this->charAt($str, $h & 63);
    	}
    	return $c;
    }

    function yk_na($a){
    	if (!$a) {
    	return '';
    	}
    	$sz = '-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,62,-1,-1,-1,63,52,53,54,55,56,57,58,59,60,61,-1,-1,-1,-1,-1,-1,-1,0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,-1,-1,-1,-1,-1,-1,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,-1,-1,-1,-1,-1';
    	$h = explode(',', $sz);
    	$i = strlen($a);
    	$f = 0;
    	for ($e = ''; $f < $i;) {
    	do {
    		$c = $h[$this->charCodeAt($a, $f++) & 255];
    	} while ($f < $i && -1 == $c);
    	if (-1 == $c) {
    		break;
    	}
    	do {
    		$b = $h[$this->charCodeAt($a, $f++) & 255];
    	} while ($f < $i && -1 == $b);
    	if (-1 == $b) {
    		break;
    	}
    	$e .= $this->fromCharCode($c << 2 | ($b & 48) >> 4);
    	do {
    		$c = $this->charCodeAt($a, $f++) & 255;
    		if (61 == $c) {
    		return $e;
    		}
    		$c = $h[$c];
    	} while ($f < $i && -1 == $c);
    	if (-1 == $c) {
    		break;
    	}
    	$e .= $this->fromCharCode(($b & 15) << 4 | ($c & 60) >> 2);
    	do {
    		$b = $this->charCodeAt($a, $f++) & 255;
    		if (61 == $b) {
    		return $e;
    		}
    		$b = $h[$b];
    	} while ($f < $i && -1 == $b);
    	if (-1 == $b) {
    		break;
    	}
    	$e .= $this->fromCharCode(($c & 3) << 6 | $b);
    	}
    	return $e;
    }

    function yk_e($a, $c){
    	for ($f = 0, $i, $e = '', $h = 0; 256 > $h; $h++) {
    	$b[$h] = $h;
    	}
    	for ($h = 0; 256 > $h; $h++) {
    	$f = (($f + $b[$h]) + $this->charCodeAt($a, $h % strlen($a))) % 256;
    	   $i = $b[$h];
    	$b[$h] = $b[$f];
    	$b[$f] = $i;
    	}
    	for ($q = ($f = ($h = 0)); $q < strlen($c); $q++) {
    	$h = ($h + 1) % 256;
    	$f = ($f + $b[$h]) % 256;
    	$i = $b[$h];
    	$b[$h] = $b[$f];
    	$b[$f] = $i;
    	$e .= $this->fromCharCode($this->charCodeAt($c, $q) ^ $b[($b[$h] + $b[$f]) % 256]);
    	}
    	return $e;
    }
	
    public function fromCharCode($codes){
    	if (is_scalar($codes)) {
    	$codes = func_get_args();
    	}
    	$str = '';
    	foreach ($codes as $code) {
    	$str .= chr($code);
    	}
    	return $str;
    }
    
    public function charCodeAt($str, $index){
    	$charCode = array();
    	$key = md5($str);
    	$index = $index + 1;
    	if (isset($charCode[$key])) {
    	return $charCode[$key][$index];
    	}
    	$charCode[$key] = unpack('C*', $str);
    	return $charCode[$key][$index];
    }
    
    public function charAt($str, $index = 0){
    	return substr($str, $index, 1);
    }
    
    /**
     * @name:get_m3u8_file
     * @description: 获取m3u8文件
     * @return: m3u8文件的URL地址
     * @author: Xiong Jianbang
     * @create: 2015-4-25 下午3:51:50
     **/
    public function get_m3u8_file(){
    	if(empty($this->vid)){
    		return array('status'=>400,'msg'=>'youku vid is empty');
    	}
    	$link = 'http://v.youku.com/player/getPlaylist/VideoIDS/'.$this->vid.'/source/out/Pf/4/ctype/12/ev/1/Sc/2';
    	$retval = $this->cget($link);
    	if(empty($retval)){
    	    return array('status'=>400,'msg'=>'youku fetching is empty');
    	}
    	$rs = json_decode($retval, true);
    	$seed = isset($rs['data'][0]['seed'])?$rs['data'][0]['seed']:0;
    	if(empty($seed)){
    	    return array('status'=>400,'msg'=>'youku seed is empty');
    	}
    	$ip = $rs['data'][0]['ip'];
    	$videoid = $rs['data'][0]['videoid'];
    	list($sid, $token) = explode('_', $this->yk_e('becaf9be', $this->yk_na($rs['data'][0]['ep'])));
    	$ep = urlencode(iconv('gbk', 'UTF-8', $this->yk_d($this->yk_e('bf7e5f01', ((($sid . '_') . $videoid) . '_') . $token))));
    	$final_url = 'http://pl.youku.com/playlist/m3u8?ctype=12&ep='.$ep.'&ev=1&keyframe=1&oip='.$ip.'&sid='.$sid.'&token='.$token.'&vid='.$videoid.'&type=mp4';
    	return $final_url;
    }
}

// 使用方法
// $vid = 'XOTMxMDAzMzQ4';
// $obj_yk = new Youku($vid);
// echo $obj_yk->get_m3u8_file();