<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: user.php 1082 2011-04-07 06:42:14Z svn_project_zhangjie $
*/

!defined('IN_UC') && exit('Access Denied');


class phonecontrol extends base {


	function __construct() {
		$this->phonecontrol();
	}

	function phonecontrol() {
		parent::__construct();
		$this->load('phone');
		$this->app = $this->cache['apps'][UC_APPID];
	}
	
	function onget_code(){
	    $this->init_input();
	    $phone = $this->input('phone');
	    $code = $this->input('code');
	    $arr = $_ENV['phone']->get_code_phone($phone,$code);
	    return $arr;
	}
	
	function onget_reg(){
	    $this->init_input();
	    $phone = $this->input('phone');
	    $arr = $_ENV['phone']->get_reg_code_by_phone($phone);
	    return $arr;
	}
	
	function onget_forget(){
	    $this->init_input();
	    $phone = $this->input('phone');
	    $arr = $_ENV['phone']->get_forget_code_by_phone($phone);
	    return $arr;
	}
	
	function onget_reset_pwd(){
	    $this->init_input();
	    $phone = $this->input('phone');
	    $arr = $_ENV['phone']->get_reset_pwd_code_by_phone($phone);
	    return $arr;
	}
	
	function onexist_info(){
	    $this->init_input();
	    $phone = $this->input('phone');
	    $count = $_ENV['phone']->exist_info_by_phone($phone);
	    return $count;
	}
	
	function oninsert(){
	    $this->init_input();
	    $arr['phone'] = $this->input('phone');
	    $arr['code'] = $this->input('code');
	    $arr['type'] = $this->input('type');
	    $arr['model'] = $this->input('model');
	    $arr['brand'] = $this->input('brand');
	    $arr['gpu'] = $this->input('gpu');
	    $arr['systemversion'] = $this->input('systemversion');
	    $arr['cpu'] = $this->input('cpu');
	    $arr['deviceid'] = $this->input('deviceid');
	    $arr['packagename'] = $this->input('packagename');
	    $arr['ip'] = $this->input('ip');;
	    $status = $_ENV['phone']->insert_phone($arr);
	    return $status;
	}
	
	function onupdate(){
	    $this->init_input();
	    $data['id'] = intval($this->input('id'));
	    $data['arr'] = $this->input('arr');
	    $status = $_ENV['phone']->update_phone($data);
	    return $status;
	}

}

?>