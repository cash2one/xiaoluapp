<?php
class imagelogo
{
 var $input_image_file = "";        //输入图片的文件名
 var $output_image_file = "";       //生成文件的名称
 var $logo_image_name = "";         //包含存放路径的水印图片的文件名
 var $logo_text = "";               //水印文字
 var $logo_text_size;          //水印文字大小
 var $logo_text_angle;          //水印文字角度
 var $logo_text_pos;            //水印文字放置位置
 var $logo_text_font = "../include/simhei.ttf";         //水印文字的字体
 var $logo_text_color = ""; //水印字体的颜色值
 var $logo_image_pos;           //水印图片放置的位置
    //1 = 顶部居左，2 = 顶部居中，3 = 顶部居右，4 = 底部居左
    //5 = 底部居中，6 = 底部居右，7 = 中间居左，8 = 居中，9 = 中间居右
 var $logo_image_transition = 25;   //水印图片与原图片的融合度(1=100)
 var $jpeg_quality = 75;            //jpeg图片的质量
 //初始化参数 
 function __construct($size=18,$textpos=6,$imgpos=6,$color='#ffffff',$angle='4') {
 	$this->logo_text_size = $size;
	$this->logo_text_pos = $textpos;
	$this->logo_image_pos = $imgpos;
	$this->logo_text_color = $color;
	$this->logo_text_angle = $angle;
	
 }
 //生成水印图片
 function create($filename="")
 {
    if ($filename)
    {
      $this->input_image_file = strtolower(trim($filename));
    }
    $src_image_type = $this->get_type($this->input_image_file);
    $src_image = $this->createImage($src_image_type,$this->input_image_file);
    if (!$src_image)
    {
      return;
    }
    $src_image_w=imagesx($src_image);
    $src_image_h=imagesy($src_image);
    //开始处理水印logo图片信息，把两个图片合成为一个图片
    if ($this->logo_image_name)
    {
      $this->logo_image_name = strtolower(trim($this->logo_image_name));
      $logo_image_type = $this->get_type($this->logo_image_name);
      $logo_image = $this->createImage($logo_image_type,$this->logo_image_name);
      $logo_image_w=imagesx($logo_image);
      $logo_image_h=imagesy($logo_image);
      $temp_logo_image = $this->getPos($src_image_w,$src_image_h,$this->logo_image_pos,$logo_image);
      $logo_image_x = $temp_logo_image["dest_x"];
      $logo_image_y = $temp_logo_image["dest_y"];
      imagecopymerge($src_image, $logo_image,$logo_image_x,$logo_image_y,0,0,$logo_image_w,$logo_image_h,$this->logo_image_transition);
    }
    //水印为纯文本
    if ($this->logo_text)
     {
      //$this->logo_text = $this->gb2utf8($this->logo_text); 要转换就出问题
      $temp_logo_text = $this->getPos($src_image_w,$src_image_h,$this->logo_text_pos);
      $logo_text_x = $temp_logo_text["dest_x"];
      $logo_text_y = $temp_logo_text["dest_y"];
      if(preg_match("/([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])/i", $this->logo_text_color, $color))
      {
        $red = hexdec($color[1]);
        $green = hexdec($color[2]);
        $blue = hexdec($color[3]);
        $logo_text_color = imagecolorallocate($src_image, $red,$green,$blue);
      }else{
        $logo_text_color = imagecolorallocate($src_image, 255,255,255);
      }
      //用TrueType字体向图像写入文本
      imagettftext($src_image,$this->logo_text_size,$this->logo_angle,$logo_text_x,$logo_text_y,$logo_text_color,$this->logo_text_font,$this->logo_text);
    }
    //保存生成的图片为新的文件
    if ($this->output_image_file)
    {
      switch ($this->get_type($this->output_image_file))
      {
        case 'gif':
         $src_img=imagegif($src_image, $this->output_image_file); 
         break;
        case 'jpeg':
         $src_img=imagejpeg($src_image, $this->output_image_file, $this->jpeg_quality);
          break;
        case 'png':
         $src_img=imagepng($src_image, $this->output_image_file); 
         break;
        default:
         $src_img=imagejpeg($src_image, $this->output_image_file, $this->jpeg_quality); 
         break;
      }
    }
    else
    { //在原来图片的基础上生成新的合成图片
     if ($src_image_type = "jpg")
     { 
      $src_image_type="jpeg";
     }
     header("Content-type: image/{$src_image_type}");
     switch ($src_image_type)
     {
       case 'gif':
        $src_img=imagepng($src_image); 
        break;
       case 'jpg':
        $src_img=imagejpeg($src_image, "", $this->jpeg_quality);
        break;
       case 'png':
        $src_img=imagepng($src_image);
        break;
       default:
        $src_img=imagejpeg($src_image, "", $this->jpeg_quality);
        break;
     }
    }
    imagedestroy($src_image);
 }
 //根据文件名和类型创建图片
 function createImage($type,$img_name)
 {
    if (!$type)
    {
     $type = $this->get_type($img_name);
    }
    switch ($type)
    {
     case 'gif':
      if (function_exists('imagecreatefromgif'))
     $tmp_img=@imagecreatefromgif($img_name );
      break;
     case 'jpg':
      $tmp_img=imagecreatefromjpeg($img_name);
      break;
     case 'png':
      $tmp_img=imagecreatefrompng($img_name);
      break;
     default:
      $tmp_img=imagecreatefromstring($img_name);
      break;
    }
    return $tmp_img;
 }
 //根据源图像的长、宽，位置代码，水印图片id来生成把水印放置到源图像中的位置
 function getPos($sourcefile_width,$sourcefile_height,$pos,$logo_image="")
 {
    if ($logo_image)
    {
     $insertfile_width = imagesx($logo_image);
     $insertfile_height = imagesy($logo_image);
    }else {
     $lineCount = explode("\r\n",$this->logo_text);
     $fontSize = imagettfbbox($this->logo_text_size,$this->logo_text_angle,$this->logo_text_font,$this->logo_text);
     $insertfile_width = $fontSize[2] - $fontSize[0];
     $insertfile_height = count($lineCount)*($fontSize[1] - $fontSize[3]);
    }
    switch ($pos)
    {
     case 1://顶部居左
      $dest_x = 0;
      if ($this->logo_text){
       $dest_y = $insertfile_height;
      }else{
       $dest_y = 0;
      }
      break;
     case 2://顶部居中
      $dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
      if ($this->logo_text){
       $dest_y = $insertfile_height;
      }else{
       $dest_y = 0;
      }
      break;
     case 3://顶部居右
      $dest_x = $sourcefile_width - $insertfile_width;
      if ($this->logo_text){
       $dest_y = $insertfile_height;
      }else{
       $dest_y = 0;
      }
      break;
     case 4://底部居左
      $dest_x = 0;
      $dest_y = $sourcefile_height - $insertfile_height;
      break;
     case 5://底部居中
      $dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
      $dest_y = $sourcefile_height - $insertfile_height;
      break;
     case 6://底部居右
      $dest_x = $sourcefile_width - $insertfile_width;
      $dest_y = $sourcefile_height - $insertfile_height;
      break;
     case 7://中间居左
      $dest_x = 0;
      $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
      break;
     case 8://居中
      $dest_x = ( $sourcefile_width / 2 ) - ( $insertfile_width / 2 );
      $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
      break;
     case 9://中间居右
      $dest_x = $sourcefile_width - $insertfile_width;
      $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
      break;
	 case 10://特殊情况
	 	//$dest_x = 180 - $insertfile_width;
		$dest_x = ( 230 / 2 ) - ( $insertfile_width / 2 );
		$dest_y = 36;
	 break;
	 case 11://特殊情况
        $dest_x = 140-$insertfile_width;
        $dest_y = $sourcefile_height-50;
	 break;
     default://底部居右
      $dest_x = $sourcefile_width - $insertfile_width;
      $dest_y = $sourcefile_height - $insertfile_height;
      break;
    }
    return array("dest_x"=>$dest_x,"dest_y"=>$dest_y);
 }
 //获取图片的格式，主要包括jpg,png和gif
 function get_type($img_name)
 {
    $name_array = explode(".",$img_name);
    if (preg_match("/\.(gif|jpg|jpeg|png)$/", $img_name, $matches)){
     $type = strtolower($matches[1]);
    }else{
     $type = "string";
    }
    return $type;
 }
 //指定的文字转换为UTF-8格式，使用gb2312保证中文的正常显示
 function gb2utf8($gb){
    if(!trim($gb))
    {
     return $gb;
    }
    $filename="./gb2312.txt";
    $tmp=file($filename);
    $codetable=array();
    while(list($key,$value)=each($tmp))
     $codetable[hexdec(substr($value,0,6))]=substr($value,7,6);
    $utf8="";
    while($gb){
     if (ord(substr($gb,0,1))>127)
     {
      $tthis=substr($gb,0,2);
      $gb=substr($gb,2,strlen($gb)-2);
      $utf8.=$this->u2utf8(hexdec($codetable[hexdec(bin2hex($tthis))-0x8080]));
     } else {
      $tthis=substr($gb,0,1);
      $gb=substr($gb,1,strlen($gb)-1);
      $utf8.=$this->u2utf8($tthis);
     }
    }
    return $utf8;
 }
 //转换为UTF8编码
 function u2utf8($c){
    $str="";
    if ($c < 0x80)
    {
     $str.=$c;
    }else if ($c < 0x800)
    {
     $str.=chr(0xC0 | $c>>6);
     $str.=chr(0x80 | $c & 0x3F);
    }else if ($c < 0x10000)
    {
     $str.=chr(0xE0 | $c>>12);
     $str.=chr(0x80 | $c>>6 & 0x3F);
     $str.=chr(0x80 | $c & 0x3F);
    }else if ($c < 0x200000)
    {
     $str.=chr(0xF0 | $c>>18);
     $str.=chr(0x80 | $c>>12 & 0x3F);
     $str.=chr(0x80 | $c>>6 & 0x3F);
     $str.=chr(0x80 | $c & 0x3F);
    }
    return $str;
 }
}
?>
