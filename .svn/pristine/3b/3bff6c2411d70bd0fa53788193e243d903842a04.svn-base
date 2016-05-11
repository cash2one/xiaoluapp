<?PHP
/**
 * @copyright: @快游戏 2015
 * @description:TV端获取到的内网信息提交到服务器端
 * @file: put_weixin.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2015-05-18  17:58
 * @version 1.0
 **/
include_once("../../config.inc.php");
//include_once("../../config.open.api.php");
include_once("../../db.config.inc.php");
/*参数*/

$mydata = array();
$mydata['time'] = time();
$mydata['id'] = trim(get_param('id'));//id 是设备的唯一id(device_id)
$mydata['name'] = get_param('name');//name是机器名字
$mydata['ip'] = trim(get_param('ip'));//ip是内网ip
$mydata['wifi'] = get_param('wifi');//wifi是用户wifi名字, 可能为空
$mydata['outip'] = get_onlineip();//客户端外网IP
if(strstr($mydata['outip'],',')){
    $mydata['outip'] = strstr($mydata['outip'], ',', true);
}
$mydata['down'] = intval(get_param('down'));//业务标记，新版本还是旧版本

$mydata['mykey'] = get_param('key');//有效key(备用)

//======begin为了数据统计
$mydata['cpu']=get_param('cpu');//CPU参数
$mydata['gpu']=get_param('gpu');//GPU参数(可用于适配游戏)
$mydata['source']=get_param('source');//访问来源
$mydata['locale'] = get_param('locale');//言语版本
$mydata['density']=get_param('density');//分辨率
$mydata['brand']=get_param('brand');//品牌
$mydata['model']=get_param('model');//型号(可用于适配游戏)
$mydata['mac']=get_param('mac');//MAC地址
//======end为了数据统计


//定义日志文件的名称
define('LOG_TYPE', 'put_weixin');
// $obj_log = Log::get_instance();
$str_log_init = "{$mydata['outip']}|{$mydata['ip']}|{$mydata['id']}|{$mydata['name']}|{$mydata['brand']}|{$mydata['model']}|{$mydata['wifi']}|";

if(empty($mydata['outip'])){//设备的唯一id(device_id)
    $arr = array('version'=>1,'ret'=>false,'message'=>'外网IP不存在!');
//     $obj_log->log('warning', $str_log_init . '外网IP不存在!');
    $str_encode = responseJson($arr,false);
    exit($str_encode);
}

if(empty($mydata['id'])){//设备的唯一id(device_id)
    $arr = array('version'=>1,'ret'=>false,'message'=>'设备ID号不存在!');
//     $obj_log->log('warning', $str_log_init . '设备ID号不存在!');
    $str_encode = responseJson($arr,false);
    exit($str_encode);
}

if(empty($mydata['ip'])){//内网IP地址
    $arr = array('version'=>1,'ret'=>false,'message'=>'内网IP不存在!');
//     $obj_log->log('warning', $str_log_init . '内网IP不存在!');
    $str_encode = responseJson($arr,false);
    exit($str_encode);
}


$mem_obj = new kyx_memcache();
$cache_key = $mydata['id'];
$old_value = $mem_obj->get($cache_key);
$new_value = md5($mydata['ip'] . $mydata['outip'] . $mydata['down']);
if(!empty($old_value) && $old_value==$new_value){
    $arr = array('version'=>1,'ret'=>false,'message'=>'该数据已经存在!');
    $str_encode = responseJson($arr,false);
    $conn->close();//显式关闭
    unset($conn);
    exit($str_encode);
}

//查找是否有设备信息
$sql_data = "SELECT `id`,`wan_ip`,`lan_ip`,`down`  FROM `mzw_weixin_device` WHERE `device_id`='".$mydata['id'] ."'" ;
$res = $conn->get_one($sql_data);
$arr = array('version'=>1,'ret'=>false,'message'=>'error!');

$data = array(
        'wan_ip'=>$mydata['outip'],//'外网IP',
        'lan_ip'=>$mydata['ip'], //'内网IP',
        'net_name'=>$mydata['wifi'],//'网络名称即wifi名字',
        'device_id'=>$mydata['id'],//'设备ID号',
        'device_name'=>$mydata['name'], //'设备名称',
        'device_brand'=>$mydata['brand'],//'设备品牌',
        'device_model'=>$mydata['model'],//'设备型号',
        'device_gpu'=>$mydata['gpu'],//'GPU',
        'created'=>$mydata['time'],//'创建时间',
        'weixin_key'=>$mydata['mykey'],//'连接key(未用,以后可能用到)',
        'down'=> $mydata['down'],//业务标记，新版本还是旧版本
);

if(empty($res)){
    $tmp_return = $conn->save('mzw_weixin_device', $data);
    if($tmp_return){
        $arr = array('version'=>1,'ret'=>true,'message'=>'上报数据保存成功!');
    }else{
        $arr = array('version'=>1,'ret'=>false,'message'=>'上报数据保存失败!');
//         $obj_log->log('warning', $str_log_init . '上报数据保存失败，SQL语句为:' . $conn->save('mzw_weixin_device', $data,true));
    }
}else{
    if(empty($res['id'])){
        $arr = array('version'=>1,'ret'=>false,'message'=>'设备ID号不存在!');
//         $obj_log->log('warning', $str_log_init . '设备ID号不存在，更新失败!');
    }
    $cache_old = md5($res['wan_ip'].$res['lan_ip'].$mydata['id'].$res['down']);  //旧数据的缓存键名，需要修改
    $cache_new = md5($mydata['outip'].$mydata['ip'].$mydata['id'].$mydata['down']);//新数据的缓存键名，需要修改
    if($cache_old<>$cache_new){
        $data['id'] = $res['id'];
        $tmp_return = $conn->update('mzw_weixin_device', $data);
        if($tmp_return==false){//如果保存出错
            $arr = array('version'=>1,'ret'=>false,'message'=>'上报数据更新失败!');
//             $obj_log->log('warning', $str_log_init . '上报数据更新失败，SQL语句为:' .  $conn->update('mzw_weixin_device', $data,true));
        }else{//如果保存成功
            //做好存在记录的标记
            $tomorrow = strtotime('tomorrow');
            $diff_time = $tomorrow - time();
            $mem_obj->set($cache_key,$new_value,$diff_time);
            $arr = array('version'=>1,'ret'=>true,'message'=>'上报数据更新成功!');
        }
    }else{
        //该设备记录已经存在，不需要重复上报
        $arr = array('version'=>1,'ret'=>false,'message'=>'该设备记录已经存在，不需要重复上报!');
    }
}
$str_encode = responseJson($arr,false);
$conn->close();//显式关闭
unset($conn);
exit($str_encode);