
<?php
/**
 * @copyright: @快游戏 2015
 * @description: 更新解说头像
 * @file:author_auto_ico.php
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

$data = array(
        103840 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/8d83c5aa12bd0970b8f953b78611aa3b.jpg',
        103839 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/aceb66815a5d4fdc50495a13f6edf552.jpg',
        103727 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/1a854d8786457882d21f199d172b7380.jpg',
        103722 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/03a59e32e3b3249ee41169d91c960411.jpg',
        103737 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/0c1621c71a2e2434cb9da2491b47a537.jpg',
        103725 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/a58856c5bbb0459578b0f4bccf96064d.jpg',
        103715 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/48eb7a3d73333509b671e5495837b8ff.jpg',
        103716 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/f2d9f2897b5d9fa2277cb8000350812e.jpg',
        103719 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/e20e7bfea2e396193f1592b2b31e9960.jpg',
        103802 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/ca5f28ef85fd5981cff58db931ecf7be.jpg',
        103783 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/7b2b600b9c6981f979066395afef63ef.jpg',
        103776 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/b0a640b02171b00d68a6d60dd91f0315.jpg',
        103771 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/67b5c68d418201d05a1666fb7d0eea5c.jpg',
        103780 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/4cc0059c6f544e598f6bff7892dc5031.jpg',
        103800 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/ad2295e9206db7ed94666b77f827c2d0.jpg',
        103805 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/1ab9888b0000677a65caaf672b7d2d81.jpg',
        103829 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/a3fcb073bc66a1cefea0f1cbe254e13a.jpg',
        103830 => 'http://ksadmin.youxilaile.com/uploads/img/author_ico/2015/11/13/dcbfa3aa960b1b2763fe8a7a149b2ccf.jpg'
);

if(!empty($data)){
    foreach($data as $key => $val){

        //生产环境抓取图片的接口
        $get_img_url = UC_API . '/api/get_avatar_img.php';
        $arr_img = array('local_img'=>$val,'uid'=>$key);
        //调用ucenter的头像处理接口
        $json = curl_post($get_img_url,$arr_img);
        $arr = json_decode($json,TRUE);
        if($arr['status']==400){
            echo("ID为".$reg_id."头像上传失败".chr(10).chr(13));
        }else{
            echo("ID为".$reg_id."头像上传成功".chr(10).chr(13));
        }

    }
}