<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 订阅接口
 * @file: video_user_sub.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2016-01-19  14:43
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['uid'] = intval(get_param('uid')); //用户id
$mydata['mac'] = get_param('mac');//用户mac地址
$mydata['imei'] = get_param('imei');//用户imei地址
$mydata['subid'] = intval(get_param('subid'));//订阅id
$mydata['subtype'] = intval(get_param('subtype')); //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
$mydata['batchsub'] = get_param('batchsub'); //取消订阅json数组（用于批量订阅、取消订阅）
$mydata['isbatch'] = intval(get_param('isbatch')); //是否批量（1：是 0：否）
$mydata['opertype'] = intval(get_param('opertype')); //操作类型（1：订阅 2：取消订阅）
$mydata['opertype'] = empty($mydata['opertype']) ? 1 : $mydata['opertype'];
$mydata['isdel'] = intval(get_param('isdel')); //是否删除数据（1：是 0：否）
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);


//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

$msg = ($mydata['opertype'] == 2) ? '取消订阅失败' : '订阅失败';

//订阅id或订阅类型为空（非批量）
if((empty($mydata['subid']) || empty($mydata['subtype'])) && empty($mydata['isbatch'])){
    $returnArr = array('code'=>10006,'msg'=>$msg);
    $str_encode = responseJson($returnArr,$mydata['encrypt']);
    exit($str_encode);
}

//订阅id或订阅类型为空（批量）
if((empty($mydata['batchsub'])) && !empty($mydata['isbatch'])){
    $returnArr = array('code'=>10006,'msg'=>$msg);
    $str_encode = responseJson($returnArr,$mydata['encrypt']);
    exit($str_encode);
}

$mem_obj = new kyx_memcache();

$check_where = '';
if(!empty($mydata['mac']) && $mydata['mac'] <> '00:00:00:00:00:00'){
    $check_where .= " AND `mac` = '".$mydata['mac']."'";
}
if(!empty($mydata['imei'])){
    $check_where .= " AND `imei` = '".$mydata['imei']."'";
}

//用户订阅存储key
$user_sub_info_key = 'user_sub_info_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei']);

//判断是否需要先清原始订阅数据
if(!empty($mydata['isdel'])){
    $sql = "DELETE FROM `video_user_sub_info` WHERE 1 ".$check_where;
    $conn->query($sql);
    $mem_obj->delete($user_sub_info_key);
}

//查询用户订阅总数
$sql = "SELECT COUNT(1) AS num FROM `video_user_sub_info` WHERE `status` = 1 ".$check_where;
$sub_num = $conn->count($sql);

//超过10，不可再订阅
if($sub_num >= 10 && $mydata['opertype'] == 1){
    $returnArr = array('code'=>10007,'msg'=>'订阅数已达最大');
    $str_encode = responseJson($returnArr,$mydata['encrypt']);
    exit($str_encode);
}

//用户已订阅信息
$sub_info = $mem_obj->get($user_sub_info_key);
if($sub_info === false){
    $sub_info = array();
    $sql = "SELECT `subid`,`subtype` FROM `video_user_sub_info` WHERE `status` = 1 ".$check_where;
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){
            $sub_info[] = array(
                'subid' => intval($val['subid']),
                'subtype' => intval($val['subtype'])
            );
        }
    }
    $mem_obj->set($user_sub_info_key,$sub_info,600);
}

$update_time = time();
if($mydata['opertype'] == 2){ //取消订阅

    if(!empty($mydata['isbatch'])){
        $batch_arr = json_decode(stripslashes($mydata['batchsub']),true);
        foreach($batch_arr as $bval){
            if(!empty($bval['subtype']) && !empty($bval['subid'])){
                //更新数据库订阅状态
                $sql = "UPDATE video_user_sub_info SET `status` = 2,`update_time` = ".$update_time."
                        WHERE `status` = 1 AND `subid` = ".$bval['subid']." AND `subtype` = ".$bval['subtype'].$check_where;
                $res = $conn->query($sql);
            }
        }
    }else{
        //更新数据库订阅状态
        $sql = "UPDATE video_user_sub_info SET `status` = 2,`update_time` = ".$update_time."
                WHERE `status` = 1 AND `subid` = ".$mydata['subid']." AND `subtype` = ".$mydata['subtype'].$check_where;
        $res = $conn->query($sql);
    }
}else{ //订阅

    if(!empty($mydata['isbatch'])){
        $batch_arr = json_decode(stripslashes($mydata['batchsub']),true);
        foreach($batch_arr as $bval){
            //检查是否已经订阅过了
            if(!empty($sub_info)){
                foreach($sub_info as $val){
                    if($val['subid'] == $mydata['subid'] && $val['subtype'] == $mydata['subtype']){
                        continue;
                    }
                }
            }

            //检查订阅信息是否存在取消订阅信息里
            $sql = "SELECT `id`,`subid`,`subtype` FROM `video_user_sub_info`
                    WHERE `status` = 2 AND `subid` = ".$bval['subid']." AND `subtype` = ".$bval['subtype'].$check_where." LIMIT 1";
            $check = $conn->find($sql);
            if(isset($check[0]['id']) && !empty($check[0]['id'])){
                //更新已取消状态的订阅信息
                $res = $conn->update('video_user_sub_info',array('id' => intval($check[0]['id']),'status' => 1));
            }else{
                $save_arr = array(
                    'uid' => $mydata['uid'],
                    'mac' => $mydata['mac'],
                    'imei' => $mydata['imei'],
                    'subid' => $bval['subid'],
                    'subtype' => $bval['subtype'],
                    'status' => 1,
                    'create_time' => time(),
                    'update_time' => ''
                );
                $res = $conn->save('video_user_sub_info',$save_arr);
            }
        }
    }else{
        //检查是否已经订阅过了
        if(!empty($sub_info)){
            foreach($sub_info as $val){
                if($val['subid'] == $mydata['subid'] && $val['subtype'] == $mydata['subtype']){
                    $returnArr = array('code'=>10008,'msg'=>'已订阅，请勿重复订阅');
                    $str_encode = responseJson($returnArr,$mydata['encrypt']);
                    exit($str_encode);
                }
            }
        }

        //检查订阅信息是否存在取消订阅信息里
        $sql = "SELECT `id`,`subid`,`subtype` FROM `video_user_sub_info`
                WHERE `status` = 2 AND `subid` = ".$mydata['subid']." AND `subtype` = ".$mydata['subtype'].$check_where." LIMIT 1";
        $check = $conn->find($sql);
        if(isset($check[0]['id']) && !empty($check[0]['id'])){
            //更新已取消状态的订阅信息
            $res = $conn->update('video_user_sub_info',array('id' => intval($check[0]['id']),'status' => 1));
        }else{
            $save_arr = array(
                'uid' => $mydata['uid'],
                'mac' => $mydata['mac'],
                'imei' => $mydata['imei'],
                'subid' => $mydata['subid'],
                'subtype' => $mydata['subtype'],
                'status' => 1,
                'create_time' => time(),
                'update_time' => ''
            );
            $res = $conn->save('video_user_sub_info',$save_arr);
        }
    }
}

//更新订阅缓存
$sub_info = array();
$sql = "SELECT `subid`,`subtype` FROM `video_user_sub_info` WHERE `status` = 1 ".$check_where;
$data = $conn->find($sql);
if(!empty($data)){
    foreach($data as $val){
        $sub_info[] = array(
            'subid' => intval($val['subid']),
            'subtype' => intval($val['subtype'])
        );
    }
}
$mem_obj->set($user_sub_info_key,$sub_info,600);

//删除订阅全部内容
$temp_data_key = 'user_sub_index_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei']);
$mem_obj->delete($temp_data_key);


$msg = ($mydata['opertype'] == 2) ? '取消订阅成功' : '订阅成功';
$returnArr = array('code'=>200,'msg'=>$msg);
$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);





