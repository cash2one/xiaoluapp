<?PHP
/**
 * @copyright: @快游戏 2015
 * @description:TV端获取到的游戏信息提交到服务器端
 * @file: put_game.php
 * @author: xiongjianbang
 * @charset: UTF-8
 * @time: 2015-06-03  10:46
 * @version 1.0
 **/
include_once("../../config.inc.php");
//include_once("../../config.open.api.php");
include_once("../../db.config.inc.php");
/*参数*/

$mydata = array();

$mydata['key'] = trim(get_param('key'));//验证KEY
//验证key是否正确
verify_key_kyx($mydata['key']);

//内网ip地址
$mydata['ip'] = urldecode(get_param('ip'));
//内网端口
$mydata['port'] = get_param('port');
//设备的唯一deviceid
$mydata['deviceid'] = get_param('deviceid');
//设备品牌
$mydata['devicebrand'] = urldecode(get_param('devicebrand'));
//设备型号
$mydata['devicemodel'] = urldecode(get_param('devicemodel'));
//wifi名称
$mydata['wifiname'] = urldecode(get_param('wifiname'));
//gpu型号
$mydata['gpu']= urldecode(get_param('gpu'));
//游戏名称
$mydata['title'] = urldecode(get_param('title'));
//游戏包名
$mydata['packagename'] = get_param('packagename');
//sdk基础版本号
$mydata['sdkbaseversion'] = get_param('sdkbaseversion');
//sdk版本号
$mydata['sdkversion'] = get_param('sdkversion');
//游戏版本号
$mydata['versioncode'] = get_param('versioncode');
//游戏版本名称
$mydata['versionname'] = urldecode(get_param('versionname'));
//MAC地址
$mydata['mac'] = urldecode(get_param('mac'));
//渠道
$mydata['channel'] = urldecode(get_param('channel'));
//外网IP地址
$mydata['outip'] = get_onlineip();//客户端外网IP
if(!empty($mydata['outip']) && strstr($mydata['outip'],',')){
    $mydata['outip'] = strstr($mydata['outip'], ',', true);
}
$mydata['time'] = time();

//======begin为了数据统计
$mydata['cpu']=get_param('cpu');//CPU参数
$mydata['source']=get_param('source');//访问来源
$mydata['locale'] = get_param('locale');//言语版本
$mydata['density']=get_param('density');//分辨率
$mydata['brand']=get_param('brand');//品牌
$mydata['model']=get_param('model');//型号(可用于适配游戏)
$mydata['mac']=get_param('mac');//MAC地址
//======end为了数据统计


//定义日志文件的名称
define('LOG_TYPE', 'put_game');
// $obj_log = Log::get_instance();

$str_log_init = "{$mydata['outip']}|{$mydata['ip']}|{$mydata['port']}|{$mydata['deviceid']}|{$mydata['devicebrand']}|{$mydata['devicemodel']}|{$mydata['title']}|{$mydata['versionname']}|{$mydata['packagename']}|{$mydata['sdkversion']}|";

//没有设备ID号，内网IP，内网端口号，则失败
if(empty($mydata['outip'])){
    $arr = array('status'=>400,'ret'=>false,'message'=>'外网IP不能为空，上报失败!');
//     $obj_log->log('warning', $str_log_init . '外网IP不能为空，上报失败!');
    $str_encode = responseJson($arr,false);
    exit($str_encode);
}
if(empty($mydata['deviceid'])){
    $arr = array('status'=>400,'ret'=>false,'message'=>'设备ID不能为空，上报失败!');
//     $obj_log->log('warning', $str_log_init . '设备ID不能为空，上报失败!');
    $str_encode = responseJson($arr,false);
    exit($str_encode);
}
if(empty($mydata['ip'])){
    $arr = array('status'=>400,'ret'=>false,'message'=>'内网IP不能为空，上报失败!');
//     $obj_log->log('warning', $str_log_init . '内网IP不能为空，上报失败!');
    $str_encode = responseJson($arr,false);
    exit($str_encode);
}
if(empty($mydata['port'])){
    $arr = array('status'=>400,'ret'=>false,'message'=>'端口号不能为空，上报失败!');
//     $obj_log->log('warning',$str_log_init .  '端口号不能为空，上报失败!');
    $str_encode = responseJson($arr,false);
    exit($str_encode);
}
if(empty($mydata['packagename'])){
    $arr = array('status'=>400,'ret'=>false,'message'=>'包名不能为空，上报失败!');
//     $obj_log->log('warning', $str_log_init . '包名不能为空，上报失败!');
    $str_encode = responseJson($arr,false);
    exit($str_encode);
}
if(empty($mydata['versioncode'])){
    $arr = array('status'=>400,'ret'=>false,'message'=>'游戏版本号不能为空，上报失败!');
//     $obj_log->log('warning', $str_log_init . '游戏版本号不能为空，上报失败!');
    $str_encode = responseJson($arr,false);
    exit($str_encode);
}





// $mem_obj = new kyx_memcache();
// $cache_key = $mydata['deviceid'];
// $old_value = $mem_obj->get($cache_key);
// $new_value = md5($mydata['ip'] . $mydata['outip'] .$mydata['port'] . $mydata['packagename'] . $mydata['version']  . $mydata['sdkversion'] );
// if(!empty($old_value) && $old_value==$new_value){
//     $arr = array('status'=>400,'ret'=>false,'message'=>'该数据已经存在!');
//     $str_encode = responseJson($arr,false);
//     $conn->close();//显式关闭
//     unset($conn);
//     exit($str_encode);
// }

$data = array(
        'wan_ip'=>$mydata['outip'],//'外网IP',
        'lan_ip'=>$mydata['ip'], //'内网IP',
        'lan_port'=>$mydata['port'], //'内网端口',
        'net_name'=>$mydata['wifiname'],//'网络名称即wifi名字',
        'device_id'=>$mydata['deviceid'],//'设备ID号',
        'device_brand'=>$mydata['devicebrand'],//'设备品牌',
        'device_model'=>$mydata['devicemodel'],//'设备型号',
        'device_gpu'=>$mydata['gpu'],//'GPU',
        'game_title'=>$mydata['title'],//'游戏名称,
        'game_packagename'=>$mydata['packagename'],//'游戏包名,
        'game_sdkbase_version'=>$mydata['sdkbaseversion'],//'sdk基础版本号,
        'game_sdkversion'=>$mydata['sdkversion'],//'游戏SDK版本号,
        'game_version_code'=>$mydata['versioncode'],
        'game_version_name'=>$mydata['versionname'],
        'mac'=>$mydata['mac'],
        'channel'=>$mydata['channel'],
        'created'=>$mydata['time'],//'创建时间',
);

//查找是否有设备信息
$sql_data = "SELECT *  FROM `mzw_game_device` WHERE `device_id`='".$mydata['deviceid'] ."'" ;
$res = $conn->get_one($sql_data);

if(empty($res)){
    $tmp_return = $conn->save('mzw_game_device', $data);
    if($tmp_return){
        $arr = array('status'=>200,'ret'=>true,'message'=>'保存成功!');
    }else{
        $arr = array('status'=>400,'ret'=>false,'message'=>'保存失败!');
//         $obj_log->log('warning', $str_log_init . '数据保存失败,SQL语句为:'. $conn->save('mzw_game_device', $data,true));
    }
}else{
    if(empty($res['id'])){
        $arr = array('status'=>400,'ret'=>false,'message'=>'更新失败!');
//         $obj_log->log('warning', $str_log_init . 'ID为空，数据更新失败!');
    }
    $data['id'] = $res['id'];
    $tmp_return = $conn->update('mzw_game_device', $data);
    if($tmp_return==false){//如果保存出错
        $arr = array('status'=>400,'ret'=>false,'message'=>'更新失败!');
//         $obj_log->log('warning', $str_log_init . '数据更新失败!,SQL语句为:' . $conn->update('mzw_game_device', $data,true));
    }else{//如果保存成功
        //     $mem_obj->set($cache_key,$new_value,0);
        $arr = array('status'=>200,'ret'=>true,'message'=>'更新成功!');
    }
}
$conn->close();//显式关闭
unset($conn);
$str_encode = responseJson($arr,false);
exit($str_encode);