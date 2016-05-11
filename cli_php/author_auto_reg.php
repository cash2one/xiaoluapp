#! /usr/local/php/bin/php -q
<?php
/**
 * @copyright: @快游戏 2015
 * @description: 解说者自动注册
 * @file:author_auto_reg.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-11-10  16:39
 * @version 1.0
 **/

//exit('非法操作');

include_once(str_replace("cli_php","",dirname(__FILE__))."config.inc.php");
include_once(WEBPATH_DIR."db.config.inc.php");
include_once(WEBPATH_DIR.'api/ucenter.config.inc.php');
include_once(WEBPATH_DIR."uc_client/client.php");

$sql = "SELECT `id`,`va_name`,`va_icon_get`,`va_intro` FROM `video_author_info` WHERE `va_isshow` = 1 GROUP BY `va_name` ORDER BY id DESC";
$data = $conn->find($sql);

if(!empty($data)){
    foreach($data as $key => $val){
        $username = 'k_'.time().$key; //自动生成用户名
        $password = 'kyx66666666';     //统一生成密码

        //注册
        $reg_id = uc_user_register($username,$password,'',3,'','','','127.0.0.1');
        if($reg_id > 0){

            echo("ID为".$reg_id."用户注册成功".chr(10).chr(13));

            //检测改昵称是否存在
            $status = uc_get_same_nickname($val['va_name'],$username);
            if(!$status){
                //更新用户昵称
                $status = uc_user_info_edit($username, $val['va_name'],'',$val['va_intro']);

                //更新解说者关联用户id
                if($status){

                    echo("ID为".$reg_id."用户昵称更新成功".chr(10).chr(13));

                    //更新作者头像
                    $pic_url = 'http://ksadmin.youxilaile.com/uploads/img'.$val['va_icon_get'];
//                    $pic_url = 'http://d.admin.kuaiyouxi.com/uploads/img'.$val['va_icon_get'];

                    //生产环境抓取图片的接口
                    $get_img_url = UC_API . '/api/get_avatar_img.php';
                    $arr_img = array('local_img'=>$pic_url,'uid'=>$reg_id);
                    //调用ucenter的头像处理接口
                    $json = curl_post($get_img_url,$arr_img);
                    $arr = json_decode($json,TRUE);
                    if($arr['status']==400){
                        echo("ID为".$reg_id."头像上传失败".chr(10).chr(13));
                    }else{
                        echo("ID为".$reg_id."头像上传成功".chr(10).chr(13));
                    }

                    //获取该昵称关联的所有解说者id
                    $aid_sql = "SELECT `id` FROM `video_author_info` WHERE `va_isshow` = 1 AND `va_name` = '".$val['va_name']."'";
                    $aid_data = $conn->find($aid_sql);
                    if(!empty($aid_data)){
                        foreach($aid_data as $akey => $aval){

                            //更新解说uid
                            $status = $conn->update2('video_author_info',array('va_uid' => $reg_id),array('id' => $aval['id']));

                            if($status){
                                echo("解说ID为".$aval['id']."专辑关联用户ID成功".chr(10).chr(13));
                            }else{
                                echo("解说ID为".$aval['id']."专辑关联用户ID失败".chr(10).chr(13));
                            }

                            //更新解说专辑uid
                            $status = $conn->update2('video_category_info',array('vc_uid' => $reg_id),array('vc_author_id' => $aval['id']));

                            if($status){
                                echo("解说ID为".$aval['id']."专辑关联用户ID成功".chr(10).chr(13));
                            }else{
                                echo("解说ID为".$aval['id']."专辑关联用户ID失败".chr(10).chr(13));
                            }

                            //更新视频uid
                            $status = $conn->update2('video_video_list',array('vvl_uid' => $reg_id),array('vvl_author_id' => $aval['id']));

                            if($status){
                                echo("解说ID为".$aval['id']."视频关联用户ID成功".chr(10).chr(13));
                            }else{
                                echo("解说ID为".$aval['id']."视频关联用户ID失败".chr(10).chr(13));
                            }
                        }
                    }
                }
            }else{
                echo("ID为".$reg_id."用户昵称更新失败，昵称已存在或不合法".chr(10).chr(13));
            }
        }else{
            echo("用户注册失败".chr(10).chr(13));
        }
    }
}