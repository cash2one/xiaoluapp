<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: user.php 1078 2011-03-30 02:00:29Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class phonemodel {

	var $db;
	var $base;

	function __construct(&$base) {
		$this->phonemodel($base);
	}

	function phonemodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}
	
	/**
	 * @name:get_code_phone
	 * @description: 根据手机号码和短信验证码获取信息
	 * @param: $phone=手机号码
	 * @param:$code=短信验证码
	 * @return: array
	 * @author: Xiong Jianbang
	 * @create: 2015-9-19 下午3:01:52
	 **/
	function get_code_phone($phone,$code){
	    $arr = $this->db->fetch_first("SELECT  `id`,`created`,`is_end`,`ip` FROM ".UC_DBTABLEPRE."phone_reg
	            WHERE phone='{$phone}' AND `code`='{$code}' ORDER BY id DESC LIMIT 1");
	    return $arr;
	}

	/**
	 * @name:get_reg_code_by_phone
	 * @description: 获取注册时的手机数据
	 * @author: Xiong Jianbang
	 * @create: 2015-9-18 下午3:07:04
	 **/
    function get_reg_code_by_phone($phone){
        $arr = $this->db->fetch_first("SELECT  `id`,`code`,`created`,`is_end`,`ip` FROM ".UC_DBTABLEPRE."phone_reg 
                WHERE phone='{$phone}' AND `type`=0 ORDER BY id DESC LIMIT 1");
        return $arr;
    }
    
    /**
     * @name:get_forget_code_by_phone
     * @description: 获取忘记密码时的手机数据
     * @author: Xiong Jianbang
     * @create: 2015-9-18 下午3:07:04
     **/
    function get_forget_code_by_phone($phone){
        $arr = $this->db->fetch_first("SELECT  `id`,`code`,`created`,`is_end`,`ip` FROM ".UC_DBTABLEPRE."phone_reg
                WHERE phone='{$phone}' AND `type`=1 ORDER BY id DESC LIMIT 1");
                return $arr;
    }
    
    /**
     * @name:get_reset_pwd_code_by_phone
     * @description: 获取重置密码时的手机数据
     * @return: Array
     * @author: Xiong Jianbang
     * @create: 2015-10-8 下午3:20:44
     **/
    function get_reset_pwd_code_by_phone($phone){
        $arr = $this->db->fetch_first("SELECT  `id`,`code`,`created`,`is_end`,`ip` FROM ".UC_DBTABLEPRE."phone_reg
                WHERE phone='{$phone}' AND `type`=1 ORDER BY id DESC LIMIT 1");
       return $arr;
    }
    
    /**
     * @name:exist_info_by_phone
     * @description: 查看手机是否存在用户表 
     * @param: $phone=手机号码
     * @return: 数量
     * @author: Xiong Jianbang
     * @create: 2015-10-8 下午3:33:06
     **/
    function exist_info_by_phone($phone){
        $arr = $this->db->fetch_first("SELECT COUNT(1) AS ct FROM ".UC_DBTABLEPRE."members
                WHERE phone='{$phone}'");
                return $arr['ct'];
    }
    
    /**
     * @name:insert_phone
     * @description: 保存记录
     * @param: $arr=array
     * @return: insert_id
     * @author: Xiong Jianbang
     * @create: 2015-9-18 下午2:57:48
     **/
    function insert_phone($arr=array()){
        if(empty($arr)){
        	return FALSE;
        }
        $phone = $arr['phone'];
        if(empty($phone)){
        	return FALSE;
        }
        $type = intval($arr['type']); //$type=类型0：注册 1：忘记密码
        $code = isset($arr['code'])?$arr['code']:'';
        $model = isset($arr['model'])?$arr['model']:'';
        $brand = isset($arr['brand'])?$arr['brand']:'';
        $gpu = isset($arr['gpu'])?$arr['gpu']:'';
        $systemversion = isset($arr['systemversion'])?$arr['systemversion']:'';
        $cpu = isset($arr['cpu'])?$arr['cpu']:'';
        $deviceid = isset($arr['deviceid'])?$arr['deviceid']:'';
        $packagename = isset($arr['packagename'])?$arr['packagename']:'';
        $time=time();
        $ip = isset($arr['ip'])?$arr['ip']:'';
        $sql = "INSERT INTO ".UC_DBTABLEPRE."phone_reg SET 
                 phone='{$phone}', code='{$code}',type={$type},model='{$model}',brand='{$brand}',gpu='{$gpu}',
                systemversion='{$systemversion}',cpu='{$cpu}',deviceid='{$deviceid}',packagename='{$packagename}',
                is_end=0,created={$time},ip='{$ip}' ";
        $this->db->query($sql);
        $id = $this->db->insert_id();
        return $id;
    }
    
    /**
     * @name:update_phone
     * @description: 更新手机信息
     * @param: array
     * @return: id值
     * @author: Xiong Jianbang
     * @create: 2015-9-19 下午3:02:46
     **/
    function update_phone($data=array()){
        if(empty($data)){
            return FALSE;
        }
        $id = intval($data['id']);
        $arr = $data['arr'];
        $time = time();
        $str = '';
        if(!empty($arr)){
        	foreach ($arr as $key=>$value) {
        	    $str .=",{$key}='{$value}'";
        	}
        }
       $sql = "UPDATE ".UC_DBTABLEPRE."phone_reg SET `updated`={$time},`is_end`=1 {$str}  WHERE id={$id}";
        $this->db->query($sql);
        return $this->db->affected_rows();
        return $id;
        
    }
}

?>