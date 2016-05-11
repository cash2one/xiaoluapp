<?php
/**
 * @copyright: @ 陈飞 2013
 * @description: 全局函数文件
 * @file: function.inc.php
 * @author: Chen Fei
 * @charset: UTF-8
 * @time: 2012-05-04 14:26:50
 * @version 1.0
 **/

/**
 * @name: is_empty
 * @description: 检测变量是否为空
 * @param: mixed 需要判断变量
 * @return: boolean
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function is_empty($var_name) {
	$return = FALSE;
	!isset($var_name) && $return = TRUE;
	if (!$return) {
		switch(strtolower(gettype($var_name))) {
			case 'null' : { $return = TRUE;
				BREAK;
			}
			case 'integer' : { $return = FALSE;
				BREAK;
			}
			case 'double' : { $return = FALSE;
				BREAK;
			}
			case 'boolean' : { $return = FALSE;
				BREAK;
			}
			case 'string' : { $return = $var_name === '' ? TRUE : FALSE;
				BREAK;
			}
			case 'array' : { $return = count($var_name) > 0 ? FALSE : TRUE;
				BREAK;
			}
			case 'object' : { $return = $var_name === null ? TRUE : FALSE;
				BREAK;
			}
			case 'resource' : { $return = $var_name === null ? TRUE : FALSE;
				BREAK;
			}
			default : { $return = TRUE;
			}
		}
	}
	return $return;
}

/**
 * @name: is_type
 * @description: 检测变量类型是否为指定
 * @param: mixed 需要判断变量
 * @param: string 判断的类别
 * @return: boolean
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function is_type($var_name, $var_type) {
	$return = FALSE;
	$var_name_resource_type = NULL;
	$var_name_type = strtolower(gettype($var_name));
	$var_name_type == 'resource' && $var_name_resource_type = strtolower(get_resource_type($var_name));
	$var_typeType = strtolower(gettype($var_type));
	if ($var_typeType == 'array') {
		if (count($var_type) > 0) {
			foreach ($var_type as $key => $Val) {
				$var_type[$key] = strtolower($Val);
			}
		}
		$return = in_array($var_name_type, $var_type, TRUE) ? TRUE : FALSE;
		(!$return && !is_empty($var_name_resource_type)) && $return = in_array($var_name_type . '-' . $var_name_resource_type, $var_type, TRUE) ? TRUE : FALSE;
	}
	$var_typeType == 'string' && $return = ($var_name_type == strtolower($var_type) || $var_name_type . '-' . $var_name_resource_type == strtolower($var_type)) ? TRUE : FALSE;
	return $return;
}

/**
 * @name: is_exists
 * @description: 判断是否存在[变量、类、接口、类方法、函数、文件、路径]
 * @param: mixed 需要判断变量
 * @param: string 检测类型 default[var]
 * @param: object 类对象 default[NULL]
 * @return: boolean
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function is_exists($var_name, $var_type = 'var', $object = NULL) {
	$return = FALSE;
	switch(strtolower(trim($var_type))) {
		case 'var' : { $return = isset($var_name) ? TRUE : FALSE;
			BREAK;
		}
		case 'file' : { $return = file_exists($var_name) ? TRUE : FALSE;
			BREAK;
		}
		case 'function' : { $return = function_exists($var_name) ? TRUE : FALSE;
			BREAK;
		}
		case 'class' : { $return = class_exists($var_name) ? TRUE : FALSE;
			BREAK;
		}
		case 'interface' : { $return = interface_exists($var_name) ? TRUE : FALSE;
			BREAK;
		}
		case 'method' : { $return = method_exists($object, $var_name) ? TRUE : FALSE;
			BREAK;
		}
		case 'dir' : {
			$return = is_dir($var_name) ? TRUE : FALSE;
			$return && $return = is_exists($var_name, 'file', $object);
			BREAK;
		}
	}
	return $return;
}

/**
 * @name: is_include
 * @description: 文件是否被引入
 * @param: string 引入的文件全路径
 * @return: boolean
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function is_include($include_file) {
	return in_array($include_file, get_included_files(), TRUE) ? TRUE : FALSE;
}

/**
 * @name: get_cur_time
 * @description: 获取当前时间
 * @param: boolean 返回是否字符串[FALSE]
 * @return: array
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function get_cur_time($is_string = FALSE) {
	$cur_time = microtime();
	return $is_string ? $cur_time : array(doubleval(substr($cur_time, 0, 10)), intval(substr($cur_time, 11, 10)));
}

/**
 * @name: time_array
 * @description: 字符串转换成数组
 * @param: string 时间字符串[microtime]
 * @return: array
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function time_array($string) {
	return array(doubleval(substr($string, 0, 10)), intval(substr($string, 11, 10)));
}

/**
 * @name: time_diff
 * @description: 计算时间差
 * @param: array 开始时间
 * @param: array 结束时间按
 * @param: integer 取小数点位
 * @return: double
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function time_diff($time_form, $time_to = NULL, $point = 10) {
	$return = 0.0;
	is_empty($time_to) && $time_to = get_cur_time();
	is_type($time_form, 'string') && $time_form = time_array($time_form);
	if (is_empty($time_form) || !is_type($time_form, 'array'))
		return FALSE;
	$return = ($time_to[0] - $time_form[0]) + ($time_to[1] - $time_form[1]);
	return sprintf("%." . $point . "f", $return);
}

/**
 * @name: var_string
 * @description: 将变量转换成字符串
 * @param: mixed 变量
 * @return: string
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function var_string($value) {
	switch(strtolower(gettype($value))) {
		case 'null' : {
			return NULL;
			BREAK;
		}
		case 'integer' : { settype($value, 'string');
			return $value;
			BREAK;
		}
		case 'double' : { settype($value, 'string');
			return $value;
			BREAK;
		}
		case 'string' : {
			return '"' . $value . '"';
			BREAK;
		}
		case 'array' : {
			$return = '';
			$i = 0;
			foreach ($value as $key => $val) {
				$return .= ($return == '' ? '' : ', ');
				$return .= (gettype($key) == 'integer' ? $key : '"' . $key . '"');
				$return .= ' => ';
				$tmp_type = gettype($val);
				$return .= ($tmp_type == 'array' ? var_string($val) : ($tmp_type == 'integer' ? $val : '"' . $val . '"'));
			}
			return 'Array(' . $return . ')';
		}
		case 'object' : { settype($value, 'string');
			return $value;
		}
		case 'resource' : { settype($value, 'string');
			return $value;
		}
		case 'boolean' : {
			return $value ? 'TRUE' : 'FALSE';
		}
	}
	return TRUE;
}

/**
 * @name: get_rand_string
 * @description: 获取随机字符串
 * @param: integer 随机字符的长度
 * @param: integer 随机字符的模式 default[7],1-15
 * @param: boolean 是否去除字符 default[FALSE] O,o,0
 * @return: string
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function get_rand_string($leng, $type = 7, $dark = FALSE) {
	$tmp_array = array('1' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', '2' => 'abcdefghijklmnopqrstuvwxyz', '4' => '0123456789', '8' => '~!@$&()_+-=,./<>?;\'\\:"|[]{}`');
	$return = $target_string = '';
	$array = array();
	$bin_string = decbin($type);
	$bin_leng = strlen($bin_string);
	for ($i = 0; $i < $bin_leng; $i++)
		if ($bin_string{$i} == 1)
			$array[] = pow(2, $bin_leng - $i - 1);
	if (in_array(1, $array, TRUE))
		$target_string .= $tmp_array['1'];
	if (in_array(2, $array, TRUE))
		$target_string .= $tmp_array['2'];
	if (in_array(4, $array, TRUE))
		$target_string .= $tmp_array['4'];
	if (in_array(8, $array, TRUE))
		$target_string .= $tmp_array['8'];
	$target_leng = strlen($target_string);
	mt_srand((double)microtime() * 1000000);
	while (strlen($return) < $leng) {
		$tmp_string = substr($target_string, mt_rand(0, $target_leng), 1);
		$dark && $tmp_string = (in_array($tmp_string, array('0', 'O', 'o'))) ? '' : $tmp_string;
		$return .= $tmp_string;
	}
	return $return;
}

/**
 * @name: en_de_code
 * @description: 加密解密数据
 * @param: string 被加密or解密的字符串
 * @param: string 加密or解密关键key default[123456]
 * @param: integer 加密or解密 default[1],1-加密,2-解密
 * @return: string
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function en_de_code($string, $key = '', $types = 1) {
	($key = trim($key)) && is_empty($key) && $key = '123456';
	$key = md5($key);
	$key_leng = strlen($key);
	if ($key_leng == 0)
		return FALSE;
	$string = $types != 1 ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;
	$stringLeng = strlen($string);
	$rndkey = $box = array();
	$result = '';
	for ($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($key[$i % $key_leng]);
		$box[$i] = $i;
	}
	for ($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for ($a = $j = $i = 0; $i < $stringLeng; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if ($types != 1) {
		if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
			return substr($result, 8);
		} else {
			return '';
		}
	} else {
		return str_replace('=', '', base64_encode($result));
	}
}

/**
 * @name: icon_var
 * @description: 转换字符串编码
 * @param: string 被转换的原字符串
 * @param: string 被转换的类型 default[gb2312,utf8,i]
 * @return: string
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function icon_var($string, $type = 'gb2312,utf8,i') {
	$type_array = explode(',', $type);
	$type_leng = count($type_array);
	if ($type_leng != 2 && $type_leng != 3)
		return FALSE;
	$form = strtoupper(trim($type_array[0]));
	$to = strtoupper(trim($type_array[1]));
	$prame = '';
	$type_leng == 3 && ($prame = '//' . (strtoupper(trim($type_array[2])) == 't' ? 'TRANSLIT' : 'IGNORE'));
	return iconv($form, $to . $prame, $string);
}

/**
 * @name: get_var_get
 * @description: GET方式获取表单数据
 * @param: string 表单name参数名称
 * @param: boolean 是否过滤字符串安全
 * @return: mixed
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function get_var_get($var_name, $is_filter = TRUE) {
	$return = isset($_GET[$var_name]) ? $_GET[$var_name] : NULL;
	if ($is_filter && !is_empty($return))
		$return = filter_string($return);
	return $return;
}

/**
 * @name: get_var_post
 * @description: POST方式获取表单数据
 * @param: string 表单name参数名称
 * @param: boolean 是否过滤字符串安全
 * @return: mixed
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function get_var_post($var_name, $is_filter = TRUE) {
	$return = isset($_POST[$var_name]) ? $_POST[$var_name] : NULL;
	if ($is_filter && !is_empty($return))
		$return = filter_string($return);
	return $return;
}

/**
 * @name: get_var_value
 * @description: 获取表单数据(GET 和 POST)
 * @param: string 表单name参数名称
 * @param: boolean 是否过滤字符串安全
 * @param: boolean 是否优先获取POST
 * @return: mixed
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function get_var_value($var_name, $is_filter = TRUE, $is_post = TRUE) {
	$return = NULL;
	if ($is_post) {
		$return = get_var_post($var_name, $is_filter);
		$return === NULL && $return = get_var_get($var_name, $is_filter);
	} else {
		$return = get_var_get($var_name, $is_filter);
		$return === NULL && $return = get_var_post($var_name, $is_filter);
	}
	return $return;
}

/**
 * @name: get_ip
 * @description: 获取客户端IP地址(客户端使用代理或者服务器做了负载均衡后就获取不到玩家真实IP了)
 * @return: string
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function get_ip() {
	if ($_SERVER['REMOTE_ADDR'])
		return $_SERVER['REMOTE_ADDR'];
	elseif ($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"])
		return $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
	elseif ($HTTP_SERVER_VARS["HTTP_CLIENT_IP"])
		return $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
	elseif ($HTTP_SERVER_VARS["REMOTE_ADDR"])
		return $HTTP_SERVER_VARS["REMOTE_ADDR"];
	elseif (getenv("HTTP_X_FORWARDED_FOR"))
		return getenv("HTTP_X_FORWARDED_FOR");
	elseif (getenv("HTTP_CLIENT_IP"))
		return getenv("HTTP_CLIENT_IP");
	elseif (getenv("REMOTE_ADDR"))
		return getenv("REMOTE_ADDR");
	else
		return '127.0.0.1';
}


/**
 * @name: get_onlineip
 * @description: 获取客户端IP地址(当玩家用代码时也可以获取到真实IP)
 * @description: 在服务器使用代理或者负载均衡之后使用该函数才能获取真实的玩家IP
 * @return: string
 * @author: chengdongcai
 * @create: 2014-10-10  19:01
 **/
function get_onlineip() {
	$onlineip = '';
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$onlineip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	return $onlineip;
}

/**
 * @name: time_int
 * @description: 转换时间成整形
 * @param: string 被转换时间
 * @return: integer
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function time_int($time_string) {
	$return = FALSE;
	if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/', $time_string, $match)) {
		if (!isset($match[3]))
			return FALSE;
		$return = mktime($match[4], $match[5], $match[6], $match[2], $match[3], $match[1]);
	}
	return $return;
}

/**
 * @name: get_var_name
 * @description: 获取变量的名字[引用变量返回数组]
 * @param: mixed 变量的值
 * @param: mixed 变量的作用域 default[GLOBALS]
 * @return: string
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function get_var_name(&$var_name, $scope = NULL) {
	$return = FALSE;
	is_empty($scope) && $scope = $GLOBALS;
	$tmp = $var_name;
	$var_name = 'varname_isexists_' . mt_rand();
	$return = array_keys($scope, $var_name, TRUE);
	$var_name = $tmp;
	(is_type($return, 'array') && count($return) == 1) && $return = $return[0];
	return $return;
}

/**
 * @name: file_size_string
 * @description: 计算文件格式单位
 * @param: integer 被转换的数字
 * @param: integer 小数点位数 default[2]2位小数点
 * @param: integer 进制单位大小 default[1024]
 * @param: integer 取整类型 default[0]0-四舍五入,1-向下取整,2-向上取整
 * @return: string
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function file_size_string($file_size, $decim = 2, $units = 1024, $val_crf = 0) {
	$tmp_array = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	$i = 1;
	$j = count($tmp_array);
	$decim_pow = pow(10, $decim);
	while ($file_size >= pow($units, $i) && $i <= $j)
		++$i;
	if ($val_crf == 2) {
		return ceil(($file_size / pow($units, $i - 1)) * $decim_pow) / $decim_pow . ' ' . $tmp_array[$i - 1];
	} else if ($val_crf == 1) {
		return round(($file_size / pow($units, $i - 1)) * $decim_pow) / $decim_pow . ' ' . $tmp_array[$i - 1];
	} else {
		return floor(($file_size / pow($units, $i - 1)) * $decim_pow) / $decim_pow . ' ' . $tmp_array[$i - 1];
	}
}

/**
 * @name: hex_bin
 * @description: 十六进制转二进制
 * @param: string 被转换字符串
 * @return: string
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function hex_bin($string) {
	$return = '';
	$length = strlen($string);
	for ($i = 0; $i < $length; $i += 2)
		$return .= pack('C', hexdec(substr($string, $i, 2)));
	return $return;
}

/**
 * @name: long_ip
 * @description: 长整型转成ip地址
 * @param: integer 数字
 * @return: string
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function long_ip($ip_long) {
	return long2ip($ip_long);
}

/**
 * @name: ip_long
 * @description: ip地址转成长整型
 * @param: string ip地址
 * @return: integer
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function ip_long($ip_string) {
	$return = 0;
	$tmp_array = explode('.', $ip_string);
	foreach ($tmp_array as $key => $val)
		$return += intval($val) * pow(256, abs($key - 3));
	return $return;
}

/**
 * @name: load_file
 * @description: 引入文件
 * @param: string 引入文件名称
 * @param: boolean 引入是否必须 [default-TRUE]
 * @param: boolean 引入类型 [default-TRUE(include)]
 * @param: boolean 引入是否唯一 [default-TRUE]
 * @param: string 被声明的全局变量[,分隔符] [default]
 * @return: boolean
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function load_file($file_url, $is_must = TRUE, $is_include = TRUE, $is_once = TRUE, $global_var = '') {
	if (!is_exists($file_url, 'file')) {
		return $is_must ? FALSE : TRUE;
	}
	!is_empty($global_var) && eval('global ' . $global_var . ';');
	if ($is_include) {
		$is_once &&
		include_once ($file_url);
		!$is_once &&
		include ($file_url);
	} else {
		$is_once &&
		require_once ($file_url);
		!$is_once &&
		require ($file_url);
	}
	return TRUE;
}

/**
 * @name: shift_right
 * @description: 无符号右移位
 * @param: integer 被移动值
 * @param: integer 移动的位数
 * @return: integer
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function shift_right($var_int, $move_int) {
	!defined('STR_PAD_LEFT') && define('STR_PAD_LEFT', 0);
	if ($move_int <= 0)
		return $var_int;
	if ($move_int >= 32)
		return 0;
	$var_int = decbin($var_int);
	$var_int_leng = strlen($var_int);
	if ($var_int_leng > 32) {
		$var_int = substr($var_int, $var_int_leng - 32, 32);
	} elseif ($var_int_leng < 32) {
		$var_int = str_pad($var_int, 32, '0', STR_PAD_LEFT);
	}
	return bindec(str_pad(substr($var_int, 0, 32 - $move_int), 32, '0', STR_PAD_LEFT));
}

/**
 * @name: shift_left
 * @description: 无符号左移位
 * @param: integer 被移动值
 * @param: integer 移动的位数
 * @return: integer
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function shift_left($var_int, $move_int) {
	!defined('STR_PAD_LEFT') && define('STR_PAD_LEFT', 0);
	!defined('STR_PAD_RIGHT') && define('STR_PAD_RIGHT', 1);
	if ($move_int <= 0)
		return $var_int;
	if ($move_int >= 32)
		return 0;
	$var_int = decbin($var_int);
	$var_int_leng = strlen($var_int);
	if ($var_int_leng > 32) {
		$var_int = substr($var_int, $var_int_leng - 32, 32);
	} elseif ($var_int_leng < 32) {
		$var_int = str_pad($var_int, 32, '0', STR_PAD_LEFT);
	}
	return bindec(str_pad(substr($var_int, $move_int), 32, '0', STR_PAD_RIGHT));
}

/**
 * @name: get_array_num
 * @description: 返回数组的深度数
 * @param: array 检测的数组
 * @param: integer 当前计算深度 default[1]
 * @return: integer
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function get_array_num($array, $i = 1) {
	if (!is_type($array, 'array'))
		return FALSE;
	$i = $i < 1 ? 1 : $i;
	$return = $i;
	if (!is_empty($array)) {
		foreach ($array as $val) {
			if (is_type($val, 'array')) {
				$return = max($i, $return, get_array_num($val, $i + 1));
			}
		}
	}
	return $return;
}

/**
 * @name: get_array_sum
 * @description: 返回数组的全部个数
 * @param: array 检测的数组
 * @return: integer
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function get_array_sum($array) {
	if (!is_type($array, 'array'))
		return FALSE;
	$return = 1;
	if (!is_empty($array)) {
		foreach ($array as $val) {
			if (is_type($val, 'array')) {
				$return += get_array_sum($val);
			}
		}
	}
	return $return;
}

/**
 * @name: xml_array
 * @description: XML转成数组
 * @param: string Xml字符串
 * @param: boolean 是否启用 attribute default[FALSE]
 * @return: array
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function xml_array($xml_string, $attribute = FALSE) {
	$return = array();
	$search = $attribute ? '|<((\S+)(.*))\s*>(.*)</\2>|Ums' : '|<((\S+)()).*>(.*)</\2>|Ums';
	$xml_string = preg_replace('|>\s*<|', ">\n<", $xml_string);
	$xml_string = preg_replace('|<\?.*\?>|', '', $xml_string);
	$xml_string = preg_replace('|<(\S+?)(.*)/>|U', '<$1$2></$1>', $xml_string);
	if (!preg_match_all($search, $xml_string, $match) || is_empty($match[1]))
		return FALSE;
	foreach ($match[1] as $key => $val) {
		if (!isset($return[$val]))
			$return[$val] = array();
		$return[$val][] = xml_array($match[4][$key], $attribute);
	}
	return $return;
}

/**
 * @name: sql_normalize
 * @description: 处理sql语句条件
 * @param: string 处理的sql
 * @return: string
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function sql_normalize($sql) {
	$sql = preg_replace("/\\/\\*.*\\*\\//sU", '', $sql);
	// remove multiline comments
	$sql = preg_replace("/([\"'])(?:\\\\.|\"\"|''|.)*\\1/sU", "{}", $sql);
	// remove quoted strings
	$sql = preg_replace("/(\\W)(?:-?\\d+(?:\\.\\d+)?)/", "\\1{}", $sql);
	// remove numbers
	$sql = preg_replace("/(\\W)null(?:\\Wnull)*(\\W|\$)/i", "\\1{}\\2", $sql);
	// remove nulls
	$sql = str_replace(array("\\n", "\\t", "\\0"), ' ', $sql);
	// replace escaped linebreaks
	$sql = preg_replace("/\\s+/", ' ', $sql);
	// remove multiple spaces
	$sql = preg_replace("/ (\\W)/", "\\1", $sql);
	// remove spaces bordering with non-characters
	$sql = preg_replace("/(\\W) /", "\\1", $sql);
	// --,--
	$sql = preg_replace("/\\{\\}(?:,?\\{\\})+/", "{}", $sql);
	// repetitive {},{} to single {}
	$sql = preg_replace("/\\(\\{\\}\\)(?:,\\(\\{\\}\\))+/", "({})", $sql);
	// repetitive ({}),({}) to single ({})
	$sql = strtolower(trim($sql, " \t\n)("));
	// trim spaces and strolower
	return $sql;
}

/**
 * @name: filter_string
 * @description: 过滤非安全字符
 * @param: mixed 被过滤的原字符串或数组
 * @return: mixed
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function filter_string($string) {
	if (is_empty($string))
		return '';
	if (is_array($string)) {
		foreach ($string as $key => $val)
			$string[$key] = filter_string($val);
		return $string;
	} else {
		$search = array("'<script[^>]*?>.*?</script>'si", "'<[\/\!]*?[^<>]*?>'si", "'([\r\n])[\s]+'", "'&(quot|#34);'i", "'&(amp|#38);'i", "'&(lt|#60);'i", "'&(gt|#62);'i", "'&(nbsp|#160);'i", "'&(iexcl|#161);'i", "'&(cent|#162);'i", "'&(pound|#163);'i", "'&(copy|#169);'i", "'&#(\d+);'e");
		$replace = array("", "", "\\1", "\"", "&", "<", ">", " ", chr(161), chr(162), chr(163), chr(169), "chr(\\1)");
		return trim(addslashes(nl2br(stripslashes(preg_replace($search, $replace, $string)))));
	}
}

/**
 * @name: up_file
 * @description: 上传文件
 * @param: array 被上传的文件数组信息
 * @param: string 上传文件的目录路径和文件名称
 * @param: array 允许、不允许上传的文件类型[NULL不限制,array('jpg|png', 'php')]
 * @param: integer 允许上传的大小[字节,-1不限制]
 * @param: string 上传文件的目录路径和文件名称备用
 * @param: string 上传文件的后缀名[default无,AUTO-自动带点]
 * @return: string[A-不允许类型,B-拒绝类型文件,S-超过大小,F-文件存在,T-备用文件存在,N(false)-失败,Y-成功]
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function up_file($files, $dest_file, $allow = NULL, $size = -1, $filet = NULL, $annx = NULL) {
	if (is_empty($files) || !is_type($files, 'array'))
		return FALSE;
	if (is_empty($dest_file) || !is_type($dest_file, 'string'))
		return FALSE;
	$up_size = intval($files['size']);
	$up_type = trim($files['type']);
	$up_name = trim($files['name']);
	$up_tmp_name = trim($files['tmp_name']);
	$up_name_annx = strtolower(substr($up_name, strrpos($up_name, '.') + 1));
	if (!is_empty($annx)) {
		if (strtoupper(substr($annx, 0, 4)) == 'AUTO') {
			if ($annx{4} == '+') {
				$return = '.' . $up_name_annx . substr($annx, 5);
			} else {
				$return = '.' . $up_name_annx;
			}
			$dest_file .= $return;
		} else {
			$dest_file .= $annx;
		}
	}
	if (file_exists($dest_file)) {
		if (is_empty($filet)) {
			return 'F';
		} else {
			if (file_exists($filet) && $dest_file != $filet)
				return 'T';
			$dest_file = $filet;
		}
	}
	if ($size >= 0 && $up_size > $size)
		return 'S';
	if (!is_empty($allow)) {
		if (isset($allow[0]) && !is_empty($allow[0])) {//允许
			if ($allow[0] != '*') {
				$tmp = explode('|', $allow[0]);
				$rs = FALSE;
				if (!is_empty($tmp)) {
					foreach ($tmp as $val) {
						if ($val == '*' || in_array($up_name_annx, $tmp)) {$rs = TRUE;
							break;
						}
					}
				}
				if (!$rs) {
					return 'A';
				}
			}
		}
		if (isset($allow[1]) && !is_empty($allow[1])) {//拒绝
			$tmp = explode('|', $allow[1]);
			$rs = FALSE;
			if (!is_empty($tmp)) {
				foreach ($tmp as $val) {
					if (in_array($up_name_annx, $tmp)) {$rs = TRUE;
						break;
					}
				}
			}
			if ($rs) {
				return 'B';
			}
		}
	}
	if (@move_uploaded_file($up_tmp_name, $dest_file)) {
		if (isset($return)) {
			return $return;
		} else {
			return 'Y';
		}
	} else {
		return 'N';
	}
}

/**
 * @name: object_to_array
 * @description: 对象转成数组
 * @param: object 实例化的对象
 * @return: array
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 **/
function object_to_array($object) {
	$array = (Array)$object;
	foreach ($array as $key => $val) {
		unset($array[$key]);
		$array[preg_replace('/^.+\0/', '', $key)] = $val;
	}
	return $array;
}

/**
 * @name: delete_html
 * @description: 删除html标签
 * @param: String 内容
 * @return: String
 * @author: Chen Fei
 * @create: 2012-05-11 18:26:50
 **/
function delete_html($document) {
	$document = trim($document);
	if (strlen($document) <= 0) {
		return $document;
	}
	$search = array("'<script[^>]*?>.*?</script>'si", // 去掉 javascript
	"'<[\/\!]*?[^<>]*?>'si", // 去掉 HTML 标记
	"'([\r\n])[\s]+'", // 去掉空白字符
	"'&(quot|#34);'i", // 替换 HTML 实体
	"'&(amp|#38);'i", "'&(lt|#60);'i", "'&(gt|#62);'i", "'&(nbsp|#160);'i");
	// 作为 PHP 代码运行
	$replace = array("", "", "\1", "\"", "&", "<", ">", " ");
	$document = preg_replace($search, $replace, $document);

//	$document = preg_replace("/[rn]{1,}/isU", "
//			rn", $document);

	$preg = '/<div.*>/';

	preg_match($preg, $document, $arr);

	return strip_only_tags($document, array('div'));
}

/**
 * @name strip_only_tags
 * @description: 删除指定的html标签
 * @param string $str
 * @param array $tags 形如:array('a','div')
 * @param boolean $stripContent 是否删除标签中的内容
 * @author 陈 飞
 * @create: 2012-05-11 18:26:50
 */
function strip_only_tags($str, $tags, $stripContent = FALSE) {
	$content = '';
	if (!is_array($tags)) {
		$tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
		if (end($tags) == '') {
			array_pop($tags);
		}
	}
	foreach ($tags as $tag) {
		if ($stripContent) {
			$content = '(.+<!--' . $tag . '(-->|\s[^>]*>)|)';
		}
		$str = preg_replace('#<!--?' . $tag . '(-->|\s[^>]*>)' . $content . '#is', '', $str);
	}
	return $str;
}

/**
 * FunctionName: get_referer
 * Description: 获取客户来源
 * Author: 陈飞
 * Return: string
 * Date: 2012-06-07 10:02:56
 **/
function get_referer() {
	$referer = false;
	if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '') {
		$referer = $_SERVER['HTTP_REFERER'];
	}
	return $referer;
}

/**
 * FunctionName: terminal
 * Description: 执行服务器命令函数
 * Author: 陈 飞
 * Return: string
 * Date: 2012-06-15 16:02:56
 **/
function terminal($command) {
	//system
	if (function_exists('system')) {
		ob_start();
		system($command, $return_var);
		$output = ob_get_contents();
		ob_end_clean();
	}
	//passthru
	else if (function_exists('passthru')) {
		ob_start();
		passthru($command, $return_var);
		$output = ob_get_contents();
		ob_end_clean();
	}
	//exec
	else if (function_exists('exec')) {
		exec($command, $output, $return_var);
		$output = implode("\n", $output);
	}
	//shell_exec
	else if (function_exists('shell_exec')) {
		$output = shell_exec($command);
	} else {
		$output = 'Command execution not possible on this system';
		$return_var = 1;
	}
	return array('output' => $output, 'status' => $return_var);
}

/**
 * @name: get_n_number_code
 * @description: 生成随即N位数字验证码
 * @param: integer
 * @return: string
 * @author: Chen Fei
 * @create: 2011-12-11 14:43:15
 **/
function get_n_number_code($length = 6) {
	$hash = '';
	$chars = '0123456789';
	$max = strlen($chars) - 1;
	mt_srand((double)microtime() * 1000000);
	for ($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

/**
 * @name: pinyin
 * @description: 将汉字转换为拼音
 * @param: String 待转换的字符串
 * @param: string 编码格式
 * @author: Chen Fei
 * @create: 2012-05-04 14:26:50
 * @return: String 拼音字符串
 **/
function pinyin($_String, $_Code = 'UTF8') {//GBK页面可改为gb2312，其他随意填写为UTF8
	$_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha" . "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|" . "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er" . "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui" . "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang" . "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang" . "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue" . "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne" . "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen" . "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang" . "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|" . "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|" . "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu" . "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you" . "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|" . "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
	$_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990" . "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725" . "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263" . "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003" . "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697" . "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211" . "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922" . "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468" . "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664" . "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407" . "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959" . "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652" . "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369" . "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128" . "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914" . "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645" . "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149" . "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087" . "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658" . "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340" . "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888" . "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585" . "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847" . "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055" . "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780" . "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274" . "|-10270|-10262|-10260|-10256|-10254";
	$_TDataKey = explode('|', $_DataKey);
	$_TDataValue = explode('|', $_DataValue);
	$_Data = array_combine($_TDataKey, $_TDataValue);
	arsort($_Data);
	reset($_Data);
	if ($_Code != 'gb2312')
		$_String = _U2_Utf8_Gb($_String);
	$_Res = '';
	for ($i = 0; $i < strlen($_String); $i++) {
		$_P = ord(substr($_String, $i, 1));
		if ($_P > 160) {
			$_Q = ord(substr($_String, ++$i, 1));
			$_P = $_P * 256 + $_Q - 65536;
		}
		$_Res .= _Pinyin($_P, $_Data);
	}
	return preg_replace("/[^a-z0-9]*/", '', $_Res);
}

function _Pinyin($_Num, $_Data) {
	if ($_Num > 0 && $_Num < 160) {
		return chr($_Num);
	} elseif ($_Num < -20319 || $_Num > -10247) {
		return '';
	} else {
		foreach ($_Data as $k => $v) {
			if ($v <= $_Num)
				break;
		}
		return $k;
	}
}

/**
* @name:pinyin_first
* @description:获取汉字拼音首字母
* @param:$zh=汉字字符串
* @return:词组的第一个拼音字母
* @create: 2014-9-28
* @author: xiongjianbang
*/
function pinyin_first($zh){
	$ret = "";
	$s1 = @iconv("utf-8","gbk//TRANSLIT//IGNORE", $zh);
	$s2 = @iconv("gbk","utf-8//TRANSLIT//IGNORE", $s1);
	if($s2 == $zh){$zh = $s1;}
	for($i = 0; $i < strlen($zh); $i++){
		$s1 = substr($zh,$i,1);
		$p = ord($s1);
		if($p > 160){
			$s2 = substr($zh,$i++,2);
			$ret .= get_first_char($s2);
		}else{
			$ret .= $s1;
		}
	}
	return strtolower($ret);
}

function get_first_char($s0){
	$fchar = ord($s0{0});
	if($fchar >= ord("A") and $fchar <= ord("z") )return strtoupper($s0{0});
	$s1 = @iconv("utf-8","gb2312//TRANSLIT//IGNORE", $s0);
	$s2 = @iconv("gb2312","utf-8//TRANSLIT//IGNORE", $s1);
	if($s2 == $s0){$s = $s1;}else{$s = $s0;}
	$asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
	if($asc >= -20319 and $asc <= -20284) return "A";
	if($asc >= -20283 and $asc <= -19776) return "B";
	if($asc >= -19775 and $asc <= -19219) return "C";
	if($asc >= -19218 and $asc <= -18711) return "D";
	if($asc >= -18710 and $asc <= -18527) return "E";
	if($asc >= -18526 and $asc <= -18240) return "F";
	if($asc >= -18239 and $asc <= -17923) return "G";
	if($asc >= -17922 and $asc <= -17418) return "I";
	if($asc >= -17417 and $asc <= -16475) return "J";
	if($asc >= -16474 and $asc <= -16213) return "K";
	if($asc >= -16212 and $asc <= -15641) return "L";
	if($asc >= -15640 and $asc <= -15166) return "M";
	if($asc >= -15165 and $asc <= -14923) return "N";
	if($asc >= -14922 and $asc <= -14915) return "O";
	if($asc >= -14914 and $asc <= -14631) return "P";
	if($asc >= -14630 and $asc <= -14150) return "Q";
	if($asc >= -14149 and $asc <= -14091) return "R";
	if($asc >= -14090 and $asc <= -13319) return "S";
	if($asc >= -13318 and $asc <= -12839) return "T";
	if($asc >= -12838 and $asc <= -12557) return "W";
	if($asc >= -12556 and $asc <= -11848) return "X";
	if($asc >= -11847 and $asc <= -11056) return "Y";
	if($asc >= -11055 and $asc <= -10247) return "Z";
	return null;
}

function _U2_Utf8_Gb($_C) {
	$_String = '';
	if ($_C < 0x80) {
		$_String .= $_C;
	} elseif ($_C < 0x800) {
		$_String .= chr(0xC0 | $_C>>6);
		$_String .= chr(0x80 | $_C & 0x3F);
	} elseif ($_C < 0x10000) {
		$_String .= chr(0xE0 | $_C>>12);
		$_String .= chr(0x80 | $_C>>6 & 0x3F);
		$_String .= chr(0x80 | $_C & 0x3F);
	} elseif ($_C < 0x200000) {
		$_String .= chr(0xF0 | $_C>>18);
		$_String .= chr(0x80 | $_C>>12 & 0x3F);
		$_String .= chr(0x80 | $_C>>6 & 0x3F);
		$_String .= chr(0x80 | $_C & 0x3F);
	}
	return iconv('UTF-8', 'GB2312//TRANSLIT//IGNORE', $_String);
}

/**
 * @name: get_user_seg_table_index
 * @description:  获取分表索引号
 * @param:  Integer 唯一id
 * @param:  Integer 分多少个表 默认30
 * @param:  Integer 每表装多少条记录后装入下一个表? 默认100
 * @author: Chen Fei
 * @create: 2012-08-21 17:22:50
 * @return: Integer 索引序号
 **/
function get_user_seg_table_index($segid, $num = 30, $lun_num = 100) {
	return (intval($segid / $lun_num) % $num + 1);
}

/**
 * @name: del_dir
 * @description:遍历删除目标文件夹
 * @param:  String 目标文件夹
 * @author: Chen Fei
 * @create: 2012-11-19 14:56
 * @return: Integer 索引序号
 **/
function del_dir($dirname) {
	if (!is_dir($dirname))
		return FALSE;
	if (file_exists($dirname)) {
		$dir = opendir($dirname);
		$tmp = '';
		while ($dir_file = readdir($dir)) {
			if ($dir_file != "." && $dir_file != "..") {
				$file = $dirname . '/' . $dir_file;
				if (is_dir($file)) {
					del_dir($file);
					//递归执行
				} else {
					$tmp[] = $file . '文件删除成功<br>
					';
					unlink($file);
				}
			}
		}
		closedir($dir);
		if (file_exists($dirname))
			rmdir($dirname);
		return TRUE;
	}
}

/**
 * @name: is_mobile
 * @description:  判断客户端是否为手机移动设备
 * @param:  
 * @author: Chen Fei
 * @create: 2013-04-10 14:00
 * @return: boolean
 **/
function is_mobile() {
    $useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';  
	$useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';  	  
	function CheckSubstrs($substrs,$text){  
		foreach($substrs as $substr)  
			if(false!==strpos($text,$substr)){  
				return true;  
			}  
			return false;  
	}
	$mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
	$mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');  
		  
	$found_mobile=CheckSubstrs($mobile_os_list,$useragent_commentsblock) ||  
			  CheckSubstrs($mobile_token_list,$useragent);  
		  
	if ($found_mobile){  
		return true;  
	}else{  
		return false;  
	}  
}

/**
 * @name: get_extension
 * @description:获取目标文件后缀
 * @param:文件路径
 * @author: Chen Fei
 * @create: 2013-04-10 14:00
 * @return: boolean
 **/
function get_extension($file)
{
	if(is_empty($file)){
		return false;
	}
	$tmp_arr = explode('.', $file);
	if(is_array($tmp_arr)){
		return end($tmp_arr);
	}else{
		return false;
	}
}

/**
 * @name: create_my_file_path
 * @description:建立文件目录
 * @param:目标路径
 * @author: Chen Fei
 * @create: 2013-04-10 14:00
 * @return: boolean
 **/
function create_my_file_path($file_path='' , $mod_num=0777){
	if(is_empty($file_path)) return false;
	if(file_exists($file_path)) return true;
	return mkdir($file_path,$mod_num,true) ? true : false;
}

/**
 * @name: pre
 * @description:调试打印
 * @param:	需要输出到页面的内容
 * @author: Quan Zelin
 * @create: 2013-09-24 12:40
 **/
function pre(){
	echo '<pre>';
	$args = func_get_args();
	if( !is_empty( $args ) ){
		foreach( $args as $v )
			var_dump( $v );
	}
	echo '</pre>';
}

/**
* @name: get_normol_json
* @description: 根据传递参数返回json对象数据
* @param: 待转换的原始数组 | 前端datatables所需的sEcho参数值 | 查询总记录数
* @return: String json 返回的json字串
* @author: Chen Fei
* @create: 2014-09-23 21:26
**/
function get_normol_json( $data_array = null, $sEcho, $nums ) {

       foreach( $data_array as &$val ){ //为了兼容datatables 额外加入了这两个参数key
               $val['select_name_cf'] = '';
               $val['do_name_cf'] = '';
               $val['iTotalRecords'] = $nums;
               $val['iTotalDisplayRecords'] = $nums;
       }

       $tmp_arr = array(
               "sEcho"   				=> $sEcho,
               "iTotalRecords"   		=> $nums,
               "iTotalDisplayRecords"   => $nums,
               "aaData"  				=> $data_array,
       );

       return json_encode($tmp_arr);
}

/**
 * @name:save_remote_image
 * @description:通过响应头来判断，响应头有一个属性 Content-Type ，它就是 mime ，做好 mime 和 文件扩展名的映射，就可以知道文件的扩展名了。
 * @param:$url=图片的URL地址，$to_save=图片保存的目录,如/Data/image/
 * @return:一般返回图片的相对地址，如2014/10/01/a6c624822a1735de1dad8930b4c9c1cc.gif，如果不是图片则返回FALSE
 * @create: 2014-9-29
 * @author: xiongjianbang
 * @demo:$img=save_remote_image('http://www.php100.com/statics/images//php100/logo.gif','/Data/image/');
 */
function save_remote_image($url,$to_save){
	// mime 和 扩展名 的映射
	$mimes=array(
			'image/bmp'=>'bmp',
			'image/gif'=>'gif',
			'image/jpeg'=>'jpg',
			'image/png'=>'png',
	);
	if(is_url_exists($url)){
	    $headers=get_headers($url, 1);
	    // 获取响应的类型
	    $type=$headers['Content-Type'];
	    // 如果符合我们要的类型
	    if(isset($mimes[$type])){
	        $filename= md5(uniqid().$url);
	        $ext=$mimes[$type];
	        $file="{$to_save}{$filename}.$ext";
	        // 获取数据并保存
	        $contents=file_get_contents($url);
	        file_put_contents($file, $contents);
	        return $file;
	    }
	}
	return FALSE;
}

/**
 * @name:is_url_exists
 * @description: 检查网址是否存在
 * @param: $url=网址
 * @return: boolean
 * @author: Xiong Jianbang
 * @create: 2014-10-17 下午8:51:04
 **/
function is_url_exists($url){
    $head = @get_headers($url);
    return is_array($head) ?  TRUE : FALSE;
}

/**
 * 
 * @name: substr_ext
 * @description: 字符串截取，支持中文和其他编码
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断字符串后缀
 * @return string
 * @author: Quan Zelin
 * @create: 2014-10-16 11:26
 */
function substr_ext( $str, $start = 0, $length = 150, $charset = "utf-8", $suffix = "" )
{
	if( function_exists( "mb_substr" ) ){
		return mb_substr( $str, $start, $length, $charset ).$suffix;
	}
	else if( function_exists( 'iconv_substr' ) ){
		return iconv_substr( $str,$start,$length,$charset ).$suffix;
	}
	$re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
	preg_match_all( $re[$charset], $str, $match );
	$slice = join( "", array_slice( $match[0], $start, $length ) );
	return $slice.$suffix;
}



//获取POST，或者GET数据
function get_param($param_name)
{
	$param_value = "";
	if(isset($_POST[$param_name])){
		$param_value = trim($_POST[$param_name]);
	}else if(isset($_GET[$param_name])){
		$param_value = trim($_GET[$param_name]);
	}
    //$param_value = RemoveXSS($param_value);
	if(!get_magic_quotes_gpc()){//加上检查数据防sql注入
		$param_value = sql_addslashes($param_value);
	}
	return $param_value;
}
function sql_addslashes($value){
    if (empty($value)){
        return $value;
    }else{
        return is_array($value) ? array_map('sql_addslashes', $value) : addslashes($value);
    }
}
/**
* @去除XSS（跨站脚本攻击）的函数
* @par $val 字符串参数，可能包含恶意的脚本代码如<script language="javascript">alert("hello world");</script>
* @return  处理后的字符串
* @Recoded By Androidyue
**/
function RemoveXSS($val) {  
   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed  
   // this prevents some character re-spacing such as <java\0script>  
   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs  
   $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);  
 
   // straight replacements, the user should never need these since they're normal characters  
   // this prevents like <IMG SRC=@avascript:alert('XSS')>  
   $search = 'abcdefghijklmnopqrstuvwxyz'; 
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';  
   $search .= '1234567890!@#$%^&*()'; 
   $search .= '~`";:?+/={}[]-_|\'\\'; 
   for ($i = 0; $i < strlen($search); $i++) { 
      // ;? matches the ;, which is optional 
      // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars 
 
      // @ @ search for the hex values 
      $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ; 
      // @ @ 0{0,7} matches '0' zero to seven times  
      $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ; 
   } 
 
   // now the only remaining whitespace attacks are \t, \n, and \r 
   $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base'); 
   $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'); 
   $ra = array_merge($ra1, $ra2); 
 
   $found = true; // keep replacing as long as the previous round replaced something 
   while ($found == true) { 
      $val_before = $val; 
      for ($i = 0; $i < sizeof($ra); $i++) { 
         $pattern = '/'; 
         for ($j = 0; $j < strlen($ra[$i]); $j++) { 
            if ($j > 0) { 
               $pattern .= '(';  
               $pattern .= '(&#[xX]0{0,8}([9ab]);)'; 
               $pattern .= '|';  
               $pattern .= '|(&#0{0,8}([9|10|13]);)'; 
               $pattern .= ')*'; 
            } 
            $pattern .= $ra[$i][$j]; 
         } 
         $pattern .= '/i';  
         $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag  
         $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags  
         if ($val_before == $val) {  
            // no replacements were made, so exit the loop  
            $found = false;  
         }  
      }  
   }  
   return $val;  
}


/**
 * @name: standardize_time
 * @param: string 起始时间
 * @param: string 结束时间
 * @description: 调整通过时间搜索的条件
 * @return:
 * @author: Quan Zelin
 * @create: 2014-10-17 17:43:20
 **/
function standardize_time( $begin, $end ){
	$begin_preg = preg_match( '/\d{4}-\d{2}-\d{2}/', $begin );
	$end_preg 	= preg_match( '/\d{4}-\d{2}-\d{2}/', $end );
	if( !$begin_preg && !$end_preg )  return FALSE;

	if( $begin_preg ){
		$begin = $begin.' 00:00:00';
	}else{
		$begin = '1970-01-01 00:00:00';		//默认起始时间
	}
	if( $end_preg ){
		$end = $end.' 23:59:59';
	}else{
		$end = date( 'Y-m-d H:i:s', time() ); //默认截止时间
	}
	if( $begin > $end ){
		$tmp 	= $begin;
		$begin 	= $end;
		$end	= $tmp;
	}
	return array( 'begin' => $begin, 'end' => $end );
}

/**
 * @name: send_curl_post
 * @param: string 请求url
 * @param: Array 请求的post数组
 * @param: 传递进来的数据类型 1为json格式 其他为Array格式
 * @param: POST的KEY键值
 * @description: 发送curl_post请求
 * @return: String 返回结果
 * @author: Chen Fei 
 * @create: 2014-10-17 17:43:20
 **/
function send_curl_post($url=null, $data=null, $data_type=1, $key=null){ //发送curl
	if($data_type==1){
		$go_data = $data;
	}else{
		$go_data = json_encode($data);
	}
	$data_string = "{$key}=" . $go_data . "&";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	$results = curl_exec($ch);
	return $results;
}


function curl_get($url, $second=60){
    if(empty($url)){
        return false;
    }
    $user_agent = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0';
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_TIMEOUT,$second);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);
    $data = curl_exec($ch);
    curl_close($ch);
    if ($data){
        return $data;
    }else{
        return false;
    }
}


function curl_post($url, $vars=array(), $second=20)
{
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_TIMEOUT,$second);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
    $data = curl_exec($ch);
    curl_close($ch);
    if($data)
    return $data;
    else
    return false;
}



/**
 * @name: send_curl_get
 * @param: string 请求url
 * @description: 发送curl_get请求 url中包含参数
 * @return: String 返回结果
 * @author: Chen Fei 
 * @create: 2014-10-17 17:43:20
 **/
function send_curl_get( $url=null ){ //发送curl
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$results = curl_exec($ch);
	return $results;
}

//=================================================
//========begin 接口特别需要函数======================
/*输出json*/
function ResponseJson($array,$ifencrypt=false){
	header('Content-Type:application/Json;charset=utf-8');
	if($ifencrypt){
		return base64_encode(encrypt(json_encode($array)));
	}else{
		return json_encode($array);
	}
}
//解密函数
function decrypt($source) {
	define ( "ORD_x", ord ( 'x' ) ); 
	define ( "ORD_z", ord ( 'z' ) );
	$cSrc = str_split ( $source );
	
	$i = 0;
	$h = 0;
	$l = 0;
	$m = 0;
	$n = 0;
	$j = 0;
	
	
	$len = count ( $cSrc );
	$arr = array ();
	for($i = 0; $i < $len; $i = $i + 2) {			

		$h = (ord ( $cSrc [$i] ) - ORD_x);
		$l = (ord ( $cSrc [$i + 1] ) - ORD_z);
		$m = $h << 4;
		$n = $l & 0xf;
		$r = $m + $n;
		$arr [$j] = '\0';
		$arr [$j] = chr ( $r );
		$j ++;
	}
	
	$result = '';
	foreach ( $arr as $k => $v ) {
		$result .= $v;
	}
	
	return $result;
}
//加密函数	
function encrypt($source) {
		
		define ( "ORD_x", ord ( 'x' ) ); 
		define ( "ORD_z", ord ( 'z' ) ); 
		$c = '';
		$i = 0;
		$h = 0;
		$l = 0;
		$j = 0;
		$cSrc = str_split ( $source );
		$len = count ( $cSrc );
		$arr = array ();
		for($i = 0; $i < $len; $i ++) {
			$c = ord ( $cSrc [$i] );
			
			$h = ($c >> 4) & 0xf;
			
			$l = $c & 0xf;
			
			$arr [$j] = '\0';
			$arr [$j] = chr ( $h + ORD_x );
			$arr [$j + 1] = chr ( $l + ORD_z );
			$j += 2;
		}
		
		$result = '';
		foreach ( $arr as $k => $v ) {
			$result .= $v;
		}
		
		return $result;
}

/* 账号密码加解密 start */

//账号密码加密
function new_encrypt($str)
{
    $size = mcrypt_get_block_size ( MCRYPT_DES, MCRYPT_MODE_CBC );
    $str = pkcs5Pad ( $str, $size );

    $data=mcrypt_cbc(MCRYPT_DES, PASSWORD_KYX_KEY, $str, MCRYPT_ENCRYPT, PASSWORD_KYX_KEY);
    return base64_encode($data);
}

//账号密码解密
function new_decrypt($str)
{
    $str = base64_decode ($str);
    //$strBin = $this->hex2bin( strtolower($str));
    $str = mcrypt_cbc(MCRYPT_DES, PASSWORD_KYX_KEY, $str, MCRYPT_DECRYPT, PASSWORD_KYX_KEY);
    $str = pkcs5Unpad( $str );
    return $str;
}

function pkcs5Pad($text, $blocksize)
{
    $pad = $blocksize - (strlen ( $text ) % $blocksize);
    return $text . str_repeat ( chr ( $pad ), $pad );
}

function pkcs5Unpad($text)
{
    $pad = ord ( $text {strlen ( $text ) - 1} );
    if ($pad > strlen ( $text ))
        return false;
    if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)
        return false;
    return substr ( $text, 0, - 1 * $pad );
}

/* 账号密码加解密 end */

/*
 *检测结果
 */
function gettestInfo($pid){
    	$test_app=array(
	         0=>array('code'=>'1','title'=>'需市场'),
	         1=>array('code'=>'2','title'=>'需WIFI'),
	         2=>array('code'=>'4','title'=>'有广告'),
             8=>array('code'=>'8','title'=>'道具收费'),
             16=>array('code'=>'16','title'=>'修改版'),
			 32=>array('code'=>'32','title'=>'游戏手柄'),
			 64=>array('code'=>'64','title'=>'空鼠遥控器'),
			 128=>array('code'=>'128','title'=>'普通遥控器'),
			 256=>array('code'=>'256','title'=>'体感摄像头'),
    		 512=>array('code'=>'512','title'=>'支持外置存贮卡')
         );	
    	$pid=intval($pid);
    	$res=array();
    	foreach($test_app as $k=>$v){
    	  if($pid&$v['code']) $res[$k].=$v['code'];
    	}
    	return $res;
}
//快游戏 版验证加密key
function verify_key_kyx($key){
	$path=URL_KYX_KEY.$_SERVER["PHP_SELF"];
	$str = $_SERVER["QUERY_STRING"];
	parse_str($str, $output);
	$query_str = '';
	foreach($output as $k=>$v){
		if($k =='key')
        {
            //  
        }elseif($k =='debug')
		{
			//
		}else
		{
			$query_str .= $k.'='.urlencode($v).'&';
		}
	}	
	$query_str=substr($query_str,0,strlen($query_str)-1);
	$_key = md5(md5($path).$query_str);
	if($_key !=urlencode($key))
	{
	    $host = explode('.',$_SERVER['HTTP_HOST']);
        if($host[0] != 'api')
        {
            exit('error key');    
        }
	}
}

/**
 * @name:接口授权校验
 * @description: 
 * get请求校验方式：md5(md5(域名+@youxikyxlaile)+请求串)
 * post请求校验方式：md5(域名+@youxikyxlaile)
 * @param: $key=校验码，$method=提交方式
 * @return: boolean
 * @author: Xiong Jianbang
 * @create: 2015-6-12 上午11:43:24
 **/
function kyx_authorize_key($key='',$method='get'){
    if(empty($key)){
    	return FALSE;
    }
	$token = '@youxikyxlaile';
	$host = URL_KYX_KEY;
	$query_str = isset($_SERVER["QUERY_STRING"])?$_SERVER["QUERY_STRING"]:'';
    if(!empty($query_str)){
        $new_query_str = '';
        $output = explode('&',$query_str);
        foreach($output as $okey => $val){
            if(!strstr($val,'key=') || strstr($val,'searchkey=')){
                $new_query_str .= $val.'&';
            }
        }
        $query_str = rtrim($new_query_str,'&');
	}
	$_key = '';
	switch ($method) {
		case 'GET':
		    $_key = md5(md5($host . $token ) . $query_str);
		break;
		case 'POST':
		    $_key = md5($host . $token );
		    break;
		default:
		    return FALSE;
		break;
	}
	if($_key<>$key){
		return FALSE;
	}
	return TRUE;
}

//快游戏开放平台接口KEY验证
/**
 * @Name: open_key_kyx
 * @param:$data 要验证的数据array()
 * @param: $key 验证的KEY
 * @todu 生成验KEY
 * @return : 返回成生后的KEY
 * @author chengdongcai
 * @data 2015-01-14 11:46
**/
function open_key_kyx($data,$key){
	$tmp_str = '';
	foreach($data as $k=>$v){
		$tmp_str .= $v;
	}
	$tmp_str = md5($tmp_str.$key);
	return $tmp_str;
}

/**
 * @Name: new_open_key_kyx
 * @param:$data 要验证的数据array()
 * @param: $key 验证的KEY
 * @todu 生成验KEY
 * @return : 返回成生后的KEY
 * @author chengdongcai
 * @data 2015-01-14 11:46
 **/
function new_open_key_kyx(){
    $key = 'kke324lfdjerlk.key.viaplay';
    $query_str = isset($_SERVER["QUERY_STRING"])?$_SERVER["QUERY_STRING"]:'';
    $tmp_str = '';
    if(!empty($query_str)){
        $output = explode('&',$query_str);
        foreach($output as $okey => $val){
            if(!strstr($val,'key=')){
                $query_data = explode('=',$val);
                $tmp_str .= isset($query_data[1]) ? $query_data[1] : '';
            }
        }
    }
    $tmp_key = md5($tmp_str.$key);
    return $tmp_key;
}

/**
 * @Name: new_open_key_mzw
 * @param:$data 要验证的数据array()
 * @param: $key 验证的KEY
 * @todu 生成验KEY
 * @return : 返回成生后的KEY
 * @author chengdongcai
 * @data 2015-01-14 11:46
 **/
function new_open_key_mzw(){
    $key = 'fdsie32900kdjsfi.key@muzhiwan';
    $query_str = isset($_SERVER["QUERY_STRING"])?$_SERVER["QUERY_STRING"]:'';
    $tmp_str = '';
    if(!empty($query_str)){
        $output = explode('&',$query_str);
        foreach($output as $okey => $val){
            if(!strstr($val,'key=')){
                $query_data = explode('=',$val);
                $tmp_str .= isset($query_data[1]) ? $query_data[1] : '';
            }
        }
    }
    $tmp_key = md5($tmp_str.$key);
    return $tmp_key;
}

//========end 接口特别需要函数========================
//=================================================

//==============================================
//==============begin 记录运行时间================
//返回毫秒的时间
function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

/**
 * 页面完成时调用，记录页面执行时间
 *
 */
function sys_log_shutdown_handler(){
	// 1 开始与结束时间
	$sys_log_end_time = microtime_float();

	// 2 运行时间
	$diff = number_format( $sys_log_end_time - $GLOBALS['SYS_START_TIME'], '4' );

	// 3 写入到文件中
	if( $diff > SYS_LOG_MAX_SECOND ){
		sys_log_write_content( $diff );
	}
}

/**
 * 建立日志目录,递归建立
 *
 * @param str $param 路径名
 */
function sys_log_mkdir( $param ) {
	if( !file_exists( $param ) ) {
		sys_log_mkdir( dirname( $param ) );
		@mkdir($param);
	}
	return true;
}

/**
 * 保存日志
 */
function sys_log_write_content( $msg ,$path="sys_log",$f_name = ""){
	// 1 每天一个日志文件
	$logDir = WEBPATH_DIR.'cache'.DS.$path.DS;
	sys_log_mkdir( $logDir );
	if($f_name==''){
		$logFile = $logDir.date('Y_m_d').'_log.txt';
	}else{
		$logFile = $logDir.$f_name."_".date('Y_m_d').'_log.txt';
	}
	// 2 日志内容： 时间-毫秒-开始（结束）-执行时间-当前链接
	$content = date("Y-m-d H:i:s").'	'.$msg;
	$content .= '	'.$_SERVER['REQUEST_URI'];
	@$content .= '	'.isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
	$content .= "\r\n";
	// 3 写入到指定文件中
	@file_put_contents( $logFile, $content, FILE_APPEND );
	return true;
}
//==============end 记录运行时间================
//============================================

//
/**
 * @name: write_file_random
 * @description: 随机个找文件来保存访问记录
 * @param: $str 要写入的内容
 * @param: $mydir 要写入的目录
 * @param: $append 写入方式，true 把新内容写入到保留原内容后面，false 把原内容去掉再写入新内容
 * @param: $date 文件名是否要加入日期
 * @return: ture/false
 * @author: chengdongcai
 * @create: 2014-10-17 17:43:20
 **/
function write_file_random($str,$mydir="show",$append = true,$date=''){
	//文件夹不存在则创建文件夹
    if(!is_dir(WEBPATH_DIR."data/".$mydir)){
        mkdir(WEBPATH_DIR."data/".$mydir);
    }
	$path = WEBPATH_DIR."data/".$mydir."/data".$date."_".rand(1,5).".dat";

	$type = $append ? 'ab' : 'wb';
	if (!$fp = @fopen($path, $type)) {
		return false;
	}
	@flock($fp, LOCK_EX);         // 独占锁定
	$ok = @fwrite($fp, $str);   // 写入
	@flock($fp, LOCK_UN);         // 解锁
	@fclose($fp);               // 关闭
	return $ok;
}

/**
 * @name: write_file_random_sdk
 * @description: 随机个找文件来保存访问记录
 * @param: $str 要写入的内容
 * @param: $mydir 要写入的目录
 * @param: $append 写入方式，true 把新内容写入到保留原内容后面，false 把原内容去掉再写入新内容
 * @param: $date 文件名是否要加入日期
 * @return: ture/false
 * @author: chengdongcai
 * @create: 2014-10-17 17:43:20
 **/
function write_file_random_sdk($str,$mydir="show",$append = true,$date=''){
    //文件夹不存在则创建文件夹
    if(!is_dir(WEBPATH_DIR."data/".$mydir)){
        mkdir(WEBPATH_DIR."data/".$mydir);
    }
    $path = WEBPATH_DIR."data/".$mydir."/data".$date."_".rand(1,15).".dat";

    $type = $append ? 'ab' : 'wb';
    if (!$fp = @fopen($path, $type)) {
        return false;
    }
    @flock($fp, LOCK_EX);         // 独占锁定
    $ok = @fwrite($fp, $str);   // 写入
    @flock($fp, LOCK_UN);         // 解锁
    @fclose($fp);               // 关闭
    return $ok;
}

/**
 * @name:write_tv_client_file_random
 * @description: 电视客户端随机个找文件来保存访问记录
 * @param: $str 要写入的内容
 * @param: $mydir 要写入的目录
 * @param: $append 写入方式，true 把新内容写入到保留原内容后面，false 把原内容去掉再写入新内容
 * @return:  ture/false
 * @author: Xiong Jianbang
 * @create: 2015-7-24 上午11:59:16
 **/
function write_tv_client_file_random($str,$mydir="show",$append = true,$log_count=5){
    //文件夹不存在则创建文件夹
    $month = date('Y/m');
    $date = date('d');
    $dir = WEBPATH_DIR."data/$mydir/{$month}";
    if(!is_dir($dir)){
        create_my_file_path($dir);
    }
    
    $path = "{$dir}/".date('Ymd')."_".rand(1,$log_count).".dat";

    $type = $append ? 'ab' : 'wb';
    if (!$fp = @fopen($path, $type)) {
        return false;
    }
    
    $start_time = microtime();
    do {
        $can_write = flock($fp, LOCK_EX);
        if(!$can_write) usleep(round(rand(0, 100)*1000));
    } while ((!$can_write) &&  ((microtime()-$start_time) < 1000));
    if ($can_write) {
        $ok = @fwrite($fp, $str);   // 写入
    }
    @flock($fp, LOCK_UN);         // 解锁
    @fclose($fp);
    return $ok;
    
//     @flock($fp, LOCK_EX);         // 独占锁定
//     $ok = @fwrite($fp, $str);   // 写入
//     @flock($fp, LOCK_UN);         // 解锁
//     @fclose($fp);               // 关闭
//     return $ok;
}


/**
 * @name: read_file_random
 * @description: 随机读取文件来保存的访问记录,并把内容重置为空
 * @param: $myid 要读取的记录文件ID(1到10)
 * @param: $mydir 要读取的目录
 * @param: $append 是否把内容重置为空(true 是/false 否),默认 是
 * @return: $arr 返回结果
 * @author: chengdongcai
 * @create: 2014-10-17 17:43:20
 **/
function read_file_random($myid="",$mydir="show",$append=true){
	if(empty($myid)){
		$myid = rand(1,5);
	}
	$path = WEBPATH_DIR."data/".$mydir."/data_".$myid.".dat";
	$arr = @file($path);   // 读取
	
	$type = $append ? 'wb' : 'ab';
	if (!$fp = @fopen($path, $type)) {
		return false;
	}
	@fclose($fp);   // 关闭
	return $arr;
}

/**
 * @name: write_data_cache
 * @description: 接口数据更新保存记录
 * @param: $arr=array(
			'update'=>time(),//保存时间,
			'data'=>array()//数据数组
			);要写入的内容数据
 * @param: $mykey 要写入的数据key
 * @param: $append 写入方式，true 把新内容写入到保留原内容后面，false 把原内容去掉再写入新内容
 * @return: ture/false
 * @author: chengdongcai
 * @create: 2014-10-17 17:43:20
 **/
function write_data_cache($arr,$mykey="",$append = true){
	// 如果无法写入文件，则记录错误
	$path = WEBPATH_DIR."data/update/".$mykey.".dat";

	$type = $append ? 'ab' : 'wb';
	if (!$fp = @fopen($path, $type)) {
		return false;
	}
	$str = json_encode($arr);
	@flock($fp, LOCK_EX);         // 独占锁定
	$ok = @fwrite($fp, $str);   // 写入
	@flock($fp, LOCK_UN);         // 解锁
	@fclose($fp);               // 关闭
	return $ok;
}

/**
 * @name: read_data_cache
 * @description: 读取缓存数据
 * @param: $mykey 要读取的数据key
 * @return: $arr 返回结果数组
 * @author: chengdongcai
 * @create: 2014-10-17 17:43:20
 **/
function read_data_cache($mykey=""){
	$path = WEBPATH_DIR."data/update/".$mykey.".dat";
	if(file_exists($path)){
		$str = @file_get_contents($path);
		$str = json_decode($str,true);
	}else{
		$str = '';
	}
	return $str;
}

/**
 * @name: get_file_mod_time
 * @description: 读取文件修改时间
 * @param: $mykey 要读取的数据key
 * @return: $arr 返回文件的修改时间
 * @author: chengdongcai
 * @create: 2014-12-04 17:43:20
 **/
function get_file_mod_time($path){
	$date = (@filemtime($path)) ? filemtime($path) : getdate(time());
	return $date;
}

/*
 * @name: sort_array_to_max
 * @description:功能：二维数组根据二维关键字的值排序
 * @param:$arr 二维数组,
 * @param:$mykey 二维关键字名称,
 * @param:$mytype=1(1从大到小，2从小到大)
 * @author: chengdongcai
 * @create: 2014-12-24 18:12:20
*/
function sort_array_to_max($arr,$mykey,$mytype=1){
	$num = count($arr);
	if($num<2){
		return $arr;
	}
	$tmp_arr = "";
	$tmp_arr2 = "";
	for($j=0;$j<$num;$j++){
		for($i=0;$i<$num;$i++){
			if($mytype==1){
				if($arr[$j][$mykey]>$arr[$i][$mykey]){
					$tmp_arr = $arr[$i];
					$arr[$i] = $arr[$j];
					$arr[$j] = $tmp_arr;
				}
			}else{
				if($arr[$j][$mykey]<$arr[$i][$mykey]){
					$tmp_arr = $arr[$i];
					$arr[$i] = $arr[$j];
					$arr[$j] = $tmp_arr;
				}
			}
		}
	}
	return $arr;
}
/*
 * @name: filter_search
* @description:功能：过滤搜索时多余的没用符号
* @param:$keyword 要搜索的关键词,
* @param:$restr 干净的关键词
* @author: chengdongcai
* @create: 2014-12-24 18:12:20
*/
function filter_search($keyword){
	$keyword = preg_replace("/[\"\r\n\t\$\\><']/", '', $keyword);
	
	return $keyword;
}

/*
 * @name: get_handle_pattern
* @description:功能：通过手柄的按键返回手柄对应的模式
* @param:$mykeys 手柄的按键JSON字符串,
* @param:手柄对应的模式
* @author: chengdongcai
* @create: 2015-02-05 17:34:20
*/
function get_handle_pattern($mykeys){
	//设定手柄支持的模式
	$tmp_ghc_pattern = -1;
	$tmp_key = $mykeys;
	if(!is_empty($tmp_key)){
		$tmp_key = json_decode($tmp_key,true);
		foreach ($GLOBALS['SYS_HANDLE_PATTERN'] as $val){
			$tmp_key2 = json_decode($val['keys'],true);
			$tmp_num = 0;//设置键值相等的次数
			$tmp_key_num = count($tmp_key2);//求键值需要相等的次数(即求有多少个键值)
			foreach ($tmp_key2 as $val2){
				reset($tmp_key);
				foreach ($tmp_key as $val3){
					//先判断是否有这个键值
					if(isset($val2['name']) && isset($val3['name'])
						&& isset($val2['motion']) && isset($val3['motion'])
						&& isset($val2['key']) && isset($val3['key'])){
						
						if($val2['name']==$val3['name'] 
							&& $val2['key']==$val3['key']){
							$tmp_num++;
						}else{
							
						}
					}
				}
				//如果键值相等的数量与要求于相等次数相同，则表示适配上了
				if($tmp_num==$tmp_key_num){
					$tmp_ghc_pattern = $val['val'];
					break;
				}
			}
			//如果已经适配到手柄的模式，则跳出循环
			if( $tmp_ghc_pattern!=-1 ){
				break;
			}
		}
	}
	return $tmp_ghc_pattern;
}

/**
 * @name:unique_arr
 * @description: 二维数组完全去重
 * @author: Xiong Jianbang
 * @create: 2015-7-16 下午5:11:30
 **/
function unique_arr($array2D,$stkeep=false,$ndformat=true){
    // 判断是否保留一级数组键 (一级数组键可以为非数字)
    if($stkeep) $stArr = array_keys($array2D);
    // 判断是否保留二级数组键 (所有二级数组键必须相同)
    if($ndformat) $ndArr = array_keys(end($array2D));
    //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
    foreach ($array2D as $v){
        $v = join(",",$v);
        $temp[] = $v;
    }
    //去掉重复的字符串,也就是重复的一维数组
    $temp = array_unique($temp);
    //再将拆开的数组重新组装
    foreach ($temp as $k => $v){
        if($stkeep) $k = $stArr[$k];
        if($ndformat){
            $tempArr = explode(",",$v);
            foreach($tempArr as $ndkey => $ndval) $output[$k][$ndArr[$ndkey]] = $ndval;
        }
        else $output[$k] = explode(",",$v);
    }
    return $output;
}

/**
 * @name:assoc_unique
 * @description: 二维数组根据键名去重
 * @author: Xiong Jianbang
 * @create: 2015-7-16 下午5:11:47
 **/
function assoc_unique($arr, $key){
    $tmp_arr = array();
    foreach($arr as $k => $v){
        if(in_array($v[$key], $tmp_arr)) {
            unset($arr[$k]);
        }
        else {
            $tmp_arr[] = $v[$key];
        }
    }
    sort($arr);
    return $arr;
}

/**
 * @name:check_phoneformat
 * @description: 检查手机格式
 * @author: Xiong Jianbang
 * @create: 2015-9-16 下午6:28:11
 **/
function check_phoneformat($phone) {
    return preg_match("/^\d{11}$/",$phone);
}

/**
 * @name:mbstrlen
 * @description: 计算中文字符串的字数
 * @param: $str=字符串
 * @return: 字符长度
 * @author: Xiong Jianbang
 * @create: 2015-11-6 上午10:19:17
 **/
function mbstrlen($str,$encoding="utf8"){
    if (($len = strlen($str)) == 0) {
        return 0;
    }
    $encoding = strtolower($encoding);
    if ($encoding == "utf8" or $encoding == "utf-8") {
        $step = 3;
    } elseif ($encoding == "gbk" or $encoding == "gb2312") {
        $step = 2;
    } else {
        return false;
    }
    $count = 0;
    for ($i=0; $i<$len; $i++) {
        $count++;
        if (ord($str{$i}) >= 0x80) {
            $i = $i + $step - 1;
        }
    }
    return $count;
}

/**
 * @name:image_to_jpg
 * @description: 图片统一转换为JGP
 * @param: $srcFile=源图片地址，$dstFile=目标图片地址，$towidth=目标图片宽度，$toheight=目标图片高度
 * @return: 
 * @author: Xiong Jianbang
 * @create: 2015-11-12 上午10:13:09
 **/
function image_to_jpg($srcFile,$dstFile,$towidth,$toheight){
    $quality=100;
    $data = @getimagesize($srcFile);
    switch ($data['2']){
    	case 1:
    	    $im = imagecreatefromgif($srcFile);
    	    break;
    	case 2:
    	    $im = imagecreatefromjpeg($srcFile);
    	    break;
    	case 3:
    	    $im = imagecreatefrompng($srcFile);
    	    break;
    	case 6:
    	    $im = imagecreatefromwbmp( $srcFile );
    	    break;
    }
    $srcW=@imagesx($im);
    $srcH=@imagesy($im);
    if($toheight/$srcW > $towidth/$srcH){
        $b = $toheight/$srcH;
    }else{
        $b = $towidth/$srcW;
    }
    $new_w = floor($srcW*$b);
    $new_h = floor($srcH*$b);
    $dstX=$new_w;
    $dstY=$new_h;
    $ni=@imagecreatetruecolor($dstX,$dstY);
    @imagecopyresampled($ni,$im,0,0,0,0,$dstX,$dstY,$srcW,$srcH);
    @imagejpeg($ni,$dstFile,$quality);
    @imagedestroy($im);
    @imagedestroy($ni);
    return TRUE;
}

/**
 * @name: data_sort
 * @description: 二维数组的排序
 * @param: data array 排序数组
 * @param: key 排序key
 * @param: order string 排序 'DESC':降序 'ASC':升序
 * @return: array
 * @author: Chen Zhong
 * @create: 2014-12-05 16:10
 **/
function data_sort( $array, $keys, $type = 'desc' ){
    if(!isset($array) || !is_array($array) || empty($array)){
        return '';
    }
    if(!isset($keys) || trim($keys)==''){
        return '';
    }
    if(!isset($type) || $type=='' || !in_array(strtolower($type),array('asc','desc'))){
        return '';
    }

    $keysvalue=array();
    foreach($array as $key=>$val){
        $val[$keys] = str_replace('-','',$val[$keys]);
        $val[$keys] = str_replace(' ','',$val[$keys]);
        $val[$keys] = str_replace(':','',$val[$keys]);
        $keysvalue[$key] =$val[$keys];
    }

    if(strtolower($type) == 'asc'){
        asort($keysvalue); //key值降序排序
    }else{
        arsort($keysvalue); //key值升序排序
    }

    $temp_arr = array();
    foreach($keysvalue as $key => $val){
        $temp_arr[] = $array[$key];
    }

    return $temp_arr;
}

/**
 * @name:get_redirect_url
 * @description: 获取重定向后的网址
 * @param: $url=原网址
 * @return: 重定向后的网址
 * @author: Xiong Jianbang
 * @create: 2015-10-14 下午12:09:41
 **/
function get_redirect_url($url){
    $header = get_headers($url, 1);
    if (strpos($header[0],'301') !== false || strpos($header[0], '302') !== false) {
        if(is_array($header['Location'])) {
            return $header['Location'][count($header['Location'])-1];
        }else{
            return $header['Location'];
        }
    }else {
        return $url;
    }
}

