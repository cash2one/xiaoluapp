<?php
class Uc_base{
    protected  function set_member_login($arr){
        //设定要记录的用户信息
        $tmp_auth_arr = array('uid'=>$arr['uid'],
                'uname'=>$arr['username'],
                'email'=>$arr['email'],
                'time'=>time(),
                'pwd'=>$arr['pwd']);
        //进行数据转JSON格式的转换，然后进行BASE64编码
        $tmp_auth = base64_encode(json_encode($tmp_auth_arr));
        //加密内容存入cookies
        $tmp_auth = $this->authcode($tmp_auth,'ENCODE',UC_KEY);
        if(!empty($tmp_auth)){
            //把加密的内容进行BASE64编码，然后再存入COOKIES，并设置跨域
            $this->set_cookie("Example_auth",base64_encode($tmp_auth),1,'/','kuaiyouxi.test');
        }else{
            //没有取到值时
        }
        return TRUE;
    }
    
    
   protected  function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0){
        $ckey_length = 4;
        $key = md5($key ? $key : UC_KEY);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
    
        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
    
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);
    
        $result = '';
        $box = range(0, 255);
    
        $rndkey = array();
        for($i = 0; $i <= 255; $i++){
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
    
        for($j = $i = 0; $i < 256; $i++){
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
    
        for($a = $j = $i = 0; $i < $string_length; $i++){
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
    
        if($operation == 'DECODE'){
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)){
                return substr($result, 26);
            }else{
                return '';
            }
        }else{
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }
    
   protected  function set_cookie($var, $value, $life=0,$cookiepath='/',$cookiedomain='') {
        if(!empty($life)){
            $life = time() + $life * 86400 * 365;
        }
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        if($cookiedomain!=""){
            setcookie($var, $value, $life,$cookiepath,$cookiedomain,$_SERVER['SERVER_PORT']==443?1:0);
        }else{
            setcookie($var, $value, $life,$cookiepath);
        }
        return TRUE;
    }
}