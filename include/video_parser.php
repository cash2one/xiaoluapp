<?php
/**
 * @copyright: @速玩广州 2015
 * @description: 获取视频的json文件
 * @author: xiongjianbang
 * @file:vedio_parser.php
 * @charset: UTF-8
 * @time: 2015-04-25 18:22
 * @version 1.0
 **/
    

class Video_parser {
    
    private $url;
    
    public function __construct($url=''){
        if(!empty($url)){
           $this->set_url($url);
        }
    }
    
    /**
     * @name:set_url
     * @description: 设置url
     * @param: $url=待抓取的url地址
     * @return: url地址
     * @author: Xiong Jianbang
     * @create: 2015-4-28 下午4:55:43
     **/
    public function set_url($url){
    	if(empty($url)){
    		return json_encode(array('msg'=>'Page is empty','status'=>400));
    	}
    	$this->url = $url;
    }
    
    /**
     * @name:get_url
     * @description: 获取网址
     * @return: 获取网址
     * @author: Xiong Jianbang
     * @create: 2015-4-28 下午4:52:27
     **/
    public function get_url(){
    	return $this->url;
    }
    
    /**
     * @name:map_source_type
     * @description: 视频来源类型和数字的映射关系
     * @param:来源字符串
     * @return: 来源类型ID
     * @author: Xiong Jianbang
     * @create: 2015-4-28 下午5:11:04
     **/
    public function map_source_type($type=''){
    	$arr = array(
    		'youku' => 1,
    		'duowan_letv' => 2,
	        'sohu' => 3,
	        'qq' => 4,
	        'tudou' => 5,
    	    'ku6'=>6,
    	    'aipai'=>7,
    	    'leshiyun'=>8,
    	    '17173'=>9,
    	    '4399'=>10,
    	   'kamcord'=>11,
    	);
    	return isset($arr[$type])?$arr[$type]:0;
    }
    
    /**
     * @name:get_source_arr
     * @description: 获取来源类型的数组
     * @return: array
     * @author: Xiong Jianbang
     * @create: 2015-5-6 下午7:10:38
     **/
    public function get_source_arr(){
       return$arr = array(
                1 => 'youku',
                2 => 'duowan_letv',
                3 => 'sohu',
                4 =>  'qq',
                5 => 'tudou',
                6=>'ku6',
                7=>'aipai',
                8=>'leshiyun',
                9=> '17173',
               10=>'4399',
               11=>'kamcord',
        );
    }
    /**
     * @name:remap_source_type
     * @description: 视频来源类型和数字的反映射关系
     * @param $type_id=来源类型ID
     * @return: 来源字符串
     * @author: Xiong Jianbang
     * @create: 2015-4-28 下午5:11:04
     **/
    public function remap_source_type($type_id=''){
        $arr = $this->get_source_arr();
        return isset($arr[$type_id])?$arr[$type_id]:'其他';
    }
    
    /**
     * @name:parse
     * @description: 对指定的不同网址提取video视频源地址
     * @return: video视频源地址
     * @author: Xiong Jianbang
     * @create: 2015-4-25 下午5:10:29
     **/
    public function parse(){
    	$host = $this->get_host();
    	if(empty($host)){
    	    return json_encode(array('msg'=>'Page is error','status'=>400));
    	}
    	$sub_host = substr($host,strpos($host,'.'));
    	switch ($sub_host) {
    	    //优酷网站
    	    case '.youku.com':
    	        return $this->get_youku_vedio();
	        break;
	        //pcgames.com.cn 太平洋游戏网手游频道
	        case '.pcgames.com.cn':
	        	return $this->get_pcgames_vedio();
	        	break;
        	//hs.tuwan.com 兔玩戏网手游频道
        	case '.tuwan.com':
        		return $this->get_tuwan_vedio();
            break;
            //5353网站
            case '.5253.com':
                return $this->get_5253_vedio();
            break;
            case '.tgbus.com':
                return $this->get_tgbus_vedio();
            break;
            case '.40407.com':
                return $this->get_40407_vedio();
            break;
            case '.yxzoo.com':
                return $this->get_yxzoo_vedio();
            break;
             case '.mofang.com':
                 return $this->get_mofang_vedio();
            break;
            case '.gao7.com':
                return $this->get_gao7_vedio();
             break;
            //手游网站
            case '.shouyou.com':
                return $this->get_shouyou_vedio();
            break;
	        //多玩网站，分多种视频来源，现发现的有乐视云，优酷，土豆
	        case '.duowan.com':
	            return $this->get_duowan_vedio();
	        break;
	        //腾讯视频
	        case '.qq.com':
	            return $this->get_qq_vedio();
	        break;
	        case '.tudou.com':
	            return $this->get_tudou_vedio();
	        break;
	        //178网站
	        case '.178.com':
	            return $this->get_178_vedio();
	        break;
	        //66游手机
	        case '.66u.com':
	            return $this->get_66u_vedio();
	        break;
	        //4399
	        case '.4399.com':
	            return $this->get_4399_vedio();
	        break;
	        //4399
	        case '.4399pk.com':
	            return $this->get_4399pk_vedio();
	        break;
    	    //德玛西亚
    		case '.demaxiya.com':
    		    return $this->get_demaxiya_video();
    		break;
    		//撸撸趣
    		case '.lolqu.com':
    		    return $this->get_lolqu_video();
    		break;
    		//安游
    		case '.ahgame.com':
    		    return $this->get_ahgame_vedio();
    		break;
    		//全球电竟网
    		case '.ooqiu.com':
    		    return $this->get_ooqiu_vedio();
		    break;
		    //爱拍游戏网
		    case '.aipai.com':
		        return $this->get_aipai_video();
		    break;
		    case '.kamcord.com':
		        return $this->get_kamcord_video();
		    break;
		    case '.huya.com':
		        return $this->get_huya_video();
		    break;
    		default:
    			return json_encode(array('msg'=>'Error URL','status'=>400));
    		break;
    	}
    }
    
    /**
     * @name:get_gao7_vedio
     * @description: 分析gao7网站的真实视频地址
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-5-22 上午11:20:27
     **/
    public function get_gao7_vedio(){
        $html = $this->curl_get($this->url);
        if(preg_match('/embed\/(.*?)\'/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
    }
    
    /**
     * @name:get_mofang_vedio
     * @description: 分析mofang网站的真实视频地址
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-5-22 上午11:20:27
     **/
    public function get_mofang_vedio(){
        $html = $this->curl_get($this->url);
        if(preg_match('/sid\/(.*?)\/v\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
    }
    
    
    /**
     * @name:get_yxzoo_vedio
     * @description: 分析yxzoo网站的真实视频地址
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-5-22 上午11:20:27
     **/
    public function get_yxzoo_vedio(){
        $html = $this->curl_get($this->url);
        if(preg_match('/sid\/(.*?)==\/v\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        elseif(preg_match('/sid\/(.*?)\/v\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
    }
    
    /**
     * @name:get_40407_vedio
     * @description: 分析40407网站的真实视频地址
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-5-22 上午11:20:27
     **/
    public function get_40407_vedio(){
        $html = $this->curl_get($this->url);
        //优酷
        if(preg_match('/player\.php\/sid\/(.*?)==\/v\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        elseif(preg_match('/sid\/(.*?)\/v\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        //多玩
        elseif(preg_match('/vu=(.*?)&/', $html,$match)){
            $vu = isset($match[1])?$match[1]:'';
            return $this->get_duowan_video_by_vu($vu);
        }
        ///土豆
        elseif(preg_match('/v\/(.*?)\/&/', $html,$match)){
            $page_id = isset($match[1])?$match[1]:0;
            if(!empty($page_id)){
                $tudou_url = "http://www.tudou.com/programs/view/$page_id";
                $html = $this->curl_get($tudou_url);
                if(preg_match('/iid: (\d{1,})/', $html,$match)){
                    $vid = isset($match[1])?$match[1]:0;
                    return json_encode(array('msg'=>$this->get_tudou_video_json($vid),'status'=>200,'type'=>'tudou','vid'=>$vid));
                }
            }
        }
    }
    
    /**
     * @name:get_tgbus_vedio
     * @description: 分析tgbus网站的真实视频地址
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-5-22 上午11:20:27
     **/
    public function get_tgbus_vedio(){
        $html = $this->curl_get($this->url);
        if(preg_match('/player\.php\/sid\/(.*?)\/v\.swf"/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                 return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
    }
    
    /**
     * @name:get_4399_vedio
     * @description: 分析4399pk网站的真实视频地址
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-5-22 上午11:20:27
     **/
    public function get_4399_vedio(){
        $html = $this->curl_get($this->url);
        if(preg_match('/flvid = (\d*?);/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_4399_video_url($vid),'status'=>200,'type'=>'4399','vid'=>$vid));
            }
        }
    }
    
    /**
     * @name:get_4399pk_vedio
     * @description: 分析4399pk网站的真实视频地址
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-5-22 上午11:20:27
     **/
    public function get_4399pk_vedio(){
        $html = $this->curl_get($this->url);
         if(preg_match('/F_ID = "(\d*?)"/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_4399_video_url($vid),'status'=>200,'type'=>'4399','vid'=>$vid));
            }
        }
    }
    
    /**
     * @name:get_66u_vedio
     * @description: 分析66u网站的真实视频地址
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-5-22 上午11:20:27
     **/
    public function get_66u_vedio(){
        $html = $this->curl_get($this->url);
        //优酷
       if(preg_match('/player\.youku\.com\/embed\/(.*?)"/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
    }
    
    /**
     * @name:get_tudou_vedio
     * @description: 获取土豆本站的视频地址
     * @return: 视频的json格式
     * @author: Xiong Jianbang
     * @create: 2015-5-22 下午3:14:32
     **/
    public function get_tudou_vedio(){
        $html = $this->curl_get($this->url);
        if(preg_match('/iid: (\d{1,})/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            return json_encode(array('msg'=>$this->get_tudou_video_json($vid),'status'=>200,'type'=>'tudou','vid'=>$vid));
        }
    }
    
    /**
     * @name:get_shouyou_vedio
     * @description: 分析手游游戏网站的真实视频地址
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-5-22 上午11:20:27
     **/
    public function get_shouyou_vedio(){
        $html = $this->curl_get($this->url);
        if(preg_match('/17173cdn\.com\/player_f2\/(.*?)\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                $url_new = "http://v.17173.com/v_1_11113/{$vid}.html";
                $html = $this->curl_get($url_new);
                return $this->get_17173_video_json($html);
            }
        }
        //优酷
        elseif(preg_match('/sid\/(.*?)\/v\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
    }
    
    /**
     * @name:get_5253_vedio
     * @description: 分析5253游戏网站的真实视频地址
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-5-22 上午10:49:50
     **/
    public function get_5253_vedio(){
        $html = $this->curl_get($this->url);
        //多玩乐视
        if(preg_match('/vid=(\d*?)&/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_duowan_video_json($vid),'status'=>200,'type'=>'duowan_letv','vid'=>$vid));
            }
        }
        elseif(preg_match('/vu=(.*?)&/', $html,$match)){
            $vu = isset($match[1])?$match[1]:'';
            return $this->get_duowan_video_by_vu($vu);
        }
        //优酷
        elseif(preg_match('/player\.youku\.com\/player\.php\/sid\/(.*?)\/partnerid/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        elseif(preg_match('/sid\/(.*?)\/v\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        elseif(preg_match('/sid\/(.*?)\.html\/v\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        //爱拍
        elseif(preg_match('/www\.aipai\.com\/c28\/(.*?)\/playerOut\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                $url_new = "http://www.aipai.com/c24/{$vid}.html";
                $file = fopen($url_new, "rb");
                //只读2字节  如果为(16进制)1f 8b (10进制)31 139则开启了gzip ;
                $bin = fread($file, 2);
                fclose($file);
                $str_info = @unpack("C2chars", $bin);
                $is_gzip = intval($str_info['chars1'].$str_info['chars2']);
                $html = $this->curl_get($url_new);
                return $this->get_aipai_video_json($html,$is_gzip);
            }
        }
    }
    
    /**
     * @name:get_17173_vedio
     * @description: 分析17173本站游戏网站的真实视频地址
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-5-20 下午6:08:35
     **/
    public function get_17173_vedio(){
        $html = $this->curl_get($this->url);
        return $this->get_17173_video_json($html);
    }
    
    

    
    /**
     * @name:get_178_vedio
     * @description: 分析178游戏网站的真实视频地址
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-5-20 下午4:34:45
     **/
    public function get_178_vedio(){
        $html = $this->curl_get($this->url);
        //优酷视频
        if(preg_match('/player\.youku\.com\/embed\/(.*?)"/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        if(preg_match('/\/sid\/(.*?)\/v\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        //乐视云
        elseif(preg_match('/uu=(.*?)&amp;vu=(.*?)&/', $html,$match)){
            $uu = isset($match[1])?$match[1]:'';
            $vu = isset($match[2])?$match[2]:'';
            if(!empty($uu) && !empty($vu)){
                return json_encode(array('msg'=>$this->get_leshiyun_video_json($uu,$vu),'status'=>200,'type'=>'leshiyun','vid'=>$vu));
            }
        }
       
    }
    
    /**
     * @name:get_kamcord_video
     * @description: 分析kamcord网站的真实视频地址
     * @return: mp4地址
     * @author: Xiong Jianbang
     * @create: 2015-7-22 下午3:27:03
     **/
    public function get_kamcord_video(){
        $vid= 0;
        if(preg_match('/\/v\/(.*?)$/', $this->url,$match)){
            $vid = isset($match[1])?$match[1]:0;
        }
        $html = $this->curl_get($this->url);
        if(preg_match('/name\="twitter:player:stream" content="(.*?)"/', $html,$match)){
            $mp4 = isset($match[1])?$match[1]:0;
            if(!empty($mp4)){
                return json_encode(array('msg'=>$mp4,'status'=>200,'type'=>'kamcord','vid'=>$vid));
            }
        }
    }
    
    /**
     * @name:get_aipai_video
     * @description: 分析爱拍游戏网站的真实视频地址
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-5-19 下午2:08:42
     **/
    public function get_aipai_video(){
        $file = fopen($this->url, "rb");
        //只读2字节  如果为(16进制)1f 8b (10进制)31 139则开启了gzip ;
        $bin = fread($file, 2);
        fclose($file);
        $str_info = @unpack("C2chars", $bin);
        $is_gzip = intval($str_info['chars1'].$str_info['chars2']);
        $html = $this->curl_get($this->url);
        return $this->get_aipai_video_json($html,$is_gzip);
    }
    
    /**
     * @name:get_huya_video
     * @description: 分析虎牙站的视频源json文件
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-7-28 上午11:42:23
     **/
    public function get_huya_video(){
        $html = $this->curl_get($this->url);
        if(preg_match('/\&vid=(\d*?)\&/', $html,$match) ){
            $vid = isset($match[1])?$match[1]:'';
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_duowan_video_json($vid),'status'=>200,'type'=>'duowan_letv','vid'=>$vid));
            }
        }
    }
    
    
    /**
     * @name:get_duowan_vedio
     * @description: 分析多玩lol站的视频源json文件
     * 该网址的视频分乐视云和搜狐视频
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-4-27 下午6:03:39
     **/
    public function get_duowan_vedio(){
        $html = $this->curl_get($this->url);
        //页面存在乐视云视频vid的情况
//         if(preg_match('/\"vid\":\"(\d*)\"/', $html,$match)){
//             $vid = isset($match[1])?$match[1]:0;
//             if(!empty($vid)){
//                 return json_encode(array('msg'=>$this->get_duowan_video_json($vid),'status'=>200,'type'=>'duowan'));
//             }else{
//                 return json_encode(array('msg'=>'vid is empty','status'=>400));
//             }
//         }
        //根据乐视云视频letvVideoUnique值到另一URL获取vid
        $vu = '';
        if(preg_match('/vu=(.*?)&/',$html,$match)){
            $vu = isset($match[1])?$match[1]:'';
            if(!empty($vu)){
                return $this->get_duowan_video_by_vu($vu);
            }
        }
        if(preg_match('/\"letvVideoUnique\":\"(.*?)\"/', $html,$match)){
            $vu = isset($match[1])?$match[1]:'';
            if(!empty($vu)){
                return $this->get_duowan_video_by_vu($vu);
            }
        }
        if(preg_match('/\"vid\"\:\"(\d*?)\"/', $html,$match) ){
            $vid = isset($match[1])?$match[1]:'';
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_duowan_video_json($vid),'status'=>200,'type'=>'duowan_letv','vid'=>$vid));
            }
        }
        if(preg_match('/vid=(\d*?)&/', $html,$match) ){
            $vid = isset($match[1])?$match[1]:'';
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_duowan_video_json($vid),'status'=>200,'type'=>'duowan_letv','vid'=>$vid));
            }
        }
        //嵌入的是搜狐视频
        elseif(preg_match('/share.vrs.sohu.com\/my\/v\.swf\&amp;id=(\d*?)&amp;skinNum/', $html,$match)){
            $vid =  isset($match[1])?$match[1]:'';
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_sohu_video_json($vid),'status'=>200,'type'=>'sohu','vid'=>$vid));
            }
        }
        elseif(preg_match('/share.vrs.sohu.com\/my\/v\.swf\&amp;topBar=1\&amp;id=(\d*?)&/', $html,$match)){
            $vid =  isset($match[1])?$match[1]:'';
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_sohu_video_json($vid),'status'=>200,'type'=>'sohu','vid'=>$vid));
            }
        }
        elseif(preg_match('/share\.vrs\.sohu\.com\/my\/v\.swf&id=(\d*?)&/', $html,$match)){
            $vid =  isset($match[1])?$match[1]:'';
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_sohu_video_json($vid),'status'=>200,'type'=>'sohu','vid'=>$vid));
            }
        }
        elseif(preg_match('/share\.vrs\.sohu\.com\/my\/v\.swf&amp;id=(\d*?)&/', $html,$match)){
            $vid =  isset($match[1])?$match[1]:'';
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_sohu_video_json($vid),'status'=>200,'type'=>'sohu','vid'=>$vid));
            }
        }
        //嵌入的是优酷视频
        elseif(preg_match('/player\.youku\.com\/player\.php\/sid\/(.*?)\/v\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        elseif(preg_match('/sid\/(.*?)\/v\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        elseif(preg_match('/player\.php\/sid\/(.*?)\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        elseif(preg_match('/==\/sid\/(.*?)\/v\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        elseif(preg_match('/v\/swf\/qplayer\.swf\?VideoIDS=(.*?)&/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        elseif(preg_match('/v\/swf\/loader\.swf\?VideoIDS=(.*?)&/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }
        //嵌入的是土豆视频
        elseif(preg_match('/www\.tudou\.com\/v\/(.*?)\//', $html,$match)){
             $page_id = isset($match[1])?$match[1]:0;
             if(!empty($page_id)){
                 $tudou_url = "http://www.tudou.com/programs/view/$page_id";
                 $html = $this->curl_get($tudou_url);
                 if(preg_match('/iid: (\d{1,})/', $html,$match)){
                    $vid = isset($match[1])?$match[1]:0;
                    return json_encode(array('msg'=>$this->get_tudou_video_json($vid),'status'=>200,'type'=>'tudou','vid'=>$vid));
                 }
             }
        }
        //嵌入的是酷六视频
        elseif(preg_match('/refer\/(.*?\.\.)\/v\.swf/', $html,$match)){
            $vid = isset($match[1])?$match[1]:0;
            if(!empty($vid)){
                return json_encode(array('msg'=>$this->get_ku6_video_json($vid),'status'=>200,'type'=>'ku6','vid'=>$vid));
            }
        }
    }
    
    
    
    /**
     * @name:handle_tudou_video
     * @description: 
     * 土豆视频做了一系列处理，我们需要遵守下面的算法，获取真实的视频地址
     * 1，先拿到页面的ID值，类似如：bhDZE0BdPHk
     * 2，再跳到对应的土豆页面，获取iid值，获取数字，类似于22053269
     * 3，访问http://www.tudou.com/outplay/goto/getItemSegs.action?iid=22053269 返回json字符串
     * 4，获取其中的k值，再访问http://v2.tudou.com/f?id={k}
     * 5，获取XML格式的土豆视频真实地址
     * @param: $vid=视频ID号
     * @return: Array
     * @author: Xiong Jianbang
     * @create: 2015-4-28 下午5:04:24
     **/
    public function handle_tudou_video($vid=0){
        $json = $this->curl_get("http://www.tudou.com/outplay/goto/getItemSegs.action?iid=$vid");
        if(empty($json)){
            return FALSE;
        }
        $arr = json_decode($json,TRUE);
        foreach ($arr as $key=>$value) { //$key = 3,2,5分别表示高清，标清，超清
            foreach ($value as $k=>$v) {
                $key_hash = $v['k']."<br>";
                $xml = $this->curl_get("http://v2.tudou.com/f?id=$key_hash");
                if(preg_match('/<f[^>]*?>(.*?)<\/f>/', $xml,$match)){
                	$arr[$key][$k]['real_flv_url'] = isset($match[1])?$match[1]:'';
                }
            }
        }
        return $arr;
    }
    
    
    /**
     * @name:handle_sohu_video
     * @description: 
     * 搜狐视频做了地址伪装，我们需要遵守下面的算法，获取真实的视频地址
     *  
     *  1，打开上面url(http://my.tv.sohu.com/videinfo.jhtml?m=viewtv&vid=xxxxx)之后是个json格式，但还无法找到下载地址
        http://allot/?prot=prot&file=clipsURL[i]&new=su[i]
         2， 因为视频有多个切片所以写成了 [i]  这种形式,在json中找到上面的字段 allot、 prot、 clipsURL、su
           例如：
           http://220.181.61.213/?prot=2&file=220.181.89.24/148188491b1c61e718f43082e880f898486a7f6c4ef3f1fe9e476443a3f942d684b3b9c5045314bf7aba2ca44012fefc.mp4
           &new=/67/66/Az9cxRoLnpe2McInJOmN17.mp4
          3， 打开后是这样子：
            http://101.226.200.16/sohu/6/|324|114.80.133.7|ywAYHUJiiFObDbpaJEIE9iCgYQ5iVim1PKiuhA..|1|0
         4，我们需要处理一些字段下载地址的组合为：
          http://101.226.200.16/sohu/6/+su[i]+?key= ywAYHUJiiFObDbpaJEIE9iCgYQ5iVim1PKiuhA..
          5， 主要上面的下载地址还用到了之前json页面上的 su[i]   另外添加上了?key=   这几个字符， 最后组合成下载地址，如：
         http://101.226.200.16/sohu/6//67/66/Az9cxRoLnpe2McInJOmN17.mp4?key=ywAYHUJiiFObDbpaJEIE9iCgYQ5iVim1PKiuhA..
     * @param: $vid=视频ID号
     * @return: Array
     * @author: Xiong Jianbang
     * @create: 2015-4-28 上午11:19:32
     **/
    public  function handle_sohu_video($vid=0){
        $json = $this->curl_get("http://my.tv.sohu.com/videinfo.jhtml?m=viewtv&vid=$vid");
        if(!empty($json)){
            $arr = json_decode($json,TRUE);
            $allot = trim($arr['allot']);
            $prot = trim($arr['prot']);
            $arr_clipsURL = $arr['data']['clipsURL'];
            $arr_su = $arr['data']['su'];
            if(empty($arr_clipsURL)){
                 return FALSE;
            }
            foreach ($arr_clipsURL as $key=>$value) {
                $join_url = "http://$allot/?prot=$prot&file=$value&new=$arr_su[$key]";
                $fetch_str = $this->curl_get($join_url);
                $new_url = preg_replace('/\/\|\d*?\|.*?\|/', "{$arr_su[$key]}?key=", $fetch_str);
                $new_url = preg_replace('/\|\d{1,}\|\d{1,}\|\d{1,}\|\d{1,}\|\d{1}/','',$new_url);
                $arr['real_mp4_url'][] = $new_url;
            }
            return $arr;
        }
        return FALSE;
    }
    
    
    /**
     * @name:get_duowan_video_by_vu
     * @description: 根据vu处理乐视云视频
     * @param: $vu=乐视云的vu参数
     * @return: json
     * @author: Xiong Jianbang
     * @create: 2015-4-29 下午3:09:22
     **/
    private function get_duowan_video_by_vu($vu=0){
        if(!empty($vu)){
            $json = $this->curl_get("http://playapi.v.duowan.com/index.php?r=play/baseinfo&vid=&letv_video_unique=$vu");
            if(!empty($json)){
                $arr = json_decode($json,TRUE);
                $vid = isset($arr['vid'])?$arr['vid']:0;
                unset($arr);
                if(!empty($vid)){
                    return json_encode(array('msg'=>$this->get_duowan_video_json($vid),'status'=>200,'type'=>'duowan_letv','vid'=>$vid));
                }else{
                    return json_encode(array('msg'=>'vid is empty','status'=>400));
                }
            }else{
                return json_encode(array('msg'=>'duowan json is empty','status'=>400));
            }
        }else{
            return json_encode(array('msg'=>'letvVideoUnique is empty','status'=>400));
        }
    }
    
    /**
     * @name:get_qq_vedio
     * @description: 分析优酷本站的视频源json文件
     * @return: 视频源json文件
     * @author: Xiong Jianbang
     * @create: 2015-4-27 下午6:24:04
     **/
    public function get_qq_vedio(){
    	$arr = parse_url($this->url);
    	$query = isset($arr['query'])?$arr['query']:'';
    	unset($arr);
    	if(empty($query)){
    	    return json_encode(array('msg'=>'QQ vid is empty','status'=>400));
    	}
    	$arr = explode('=', $query);
    	$vid = end($arr);
    	unset($arr);
    	return json_encode(array('msg'=>$this->get_tencent_video_json($vid),'status'=>200,'type'=>'qq','vid'=>$vid));
    }
    
    
    /**
     * @name:get_youku_vedio
     * @description: 分析优酷本站的视频源m3u8文件
     * @return: 视频源m3u8文件
     * @author: Xiong Jianbang
     * @create: 2015-4-27 上午11:11:48
     **/
    public function get_youku_vedio(){
        $arr_info = pathinfo($this->url);
        $filename = $arr_info['filename'];
        $pos = strpos($filename, '?');
        if($pos==FALSE){
            $vid =  str_replace('id_','',basename($filename,'.html'));
        }else{
            $vid =  str_replace('id_','',basename(strstr($filename, '?',TRUE),'.html'));
        }
        if(empty($vid)){
            return json_encode(array('msg'=>'Youku vid is empty','status'=>400));
        }else{
            return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
        }
    }
    /**
     * @name:get_pcgames_vedio
     * @description: 分析太平洋游戏网手游频道的域名是http://http://hs.pcgames.com.cn/的视频源json文件，目前找到了优酷的视频
     * @return: 视频源json文件或者m3u8文件
     * @author: chengdongcai
     * @create: 2015-5-20 16:46:32
     **/
    public function get_pcgames_vedio(){
    	$html = $this->curl_get($this->url);
    	//找出页面里的iframe 地址
     	$tmp_iframe = preg_match('/<iframe class="iframe_video" frameborder="0" height="400" src="(.*?)" width="480"><\/iframe>/', $html,$match_iframe);
        //优酷视频(找出iframe里包含的vid)
        if($tmp_iframe && preg_match('/\#(.*?)$/', $match_iframe[1],$match)){
            $vid = $match[1];
            if(empty($vid)){
                return json_encode(array('msg'=>'Youku vid is empty','status'=>400));
            }else{
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }else{
            return json_encode(array('msg'=>'Video json file is empty','status'=>400));
        }
    }
    /**
     * @name:get_tuwan_vedio
     * @description: 分析兔玩的域名是http://hs.tuwan.com的视频源json文件，目前找到了优酷的视频
     * @return: 视频源json文件或者m3u8文件
     * @author: chengdongcai
     * @create: 2015-5-20 16:46:32
     **/
    public function get_tuwan_vedio(){
    	$html = $this->curl_get($this->url);
    	//找出页面里的iframe 地址
    	//<iframe width="726" height="516" src="http://player.youku.com/embed/XOTU4ODM4NDg4" frameborder="0" allowfullscreen></iframe>
    	//优酷视频(找出iframe里包含的vid)
    	if(preg_match('/player\.youku\.com\/embed\/(.*?)"/', $html,$match)){
    		$vid = $match[1];
    		if(empty($vid)){
    			return json_encode(array('msg'=>'Youku vid is empty','status'=>400));
    		}else{
    			return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
    		}
    	}else{
    		return json_encode(array('msg'=>'Video json file is empty','status'=>400));
    	}
    }
    /**
     * @name:get_ooqiu_vedio  
     * @description: 分析全球电竟网的域名是http://www.ooqiu.com/的视频源json文件，目前找到了腾讯和优酷的视频
     * @return: 视频源json文件或者m3u8文件
     * @author: Xiong Jianbang
     * @create: 2015-4-25 下午5:46:32
     **/
    public function get_ooqiu_vedio(){
        $html = $this->curl_get($this->url);
        //腾讯视频
        if(preg_match('/v\.qq\.com\/iframe\/player\.html\?vid=(.*?)&/', $html,$match)){
            $vid = $match[1];
            if(empty($vid)){
                return json_encode(array('msg'=>'Tencent vid is empty','status'=>400));
            }else{
                return json_encode(array('msg'=>$this->get_tencent_video_json($vid),'status'=>200,'type'=>'qq','vid'=>$vid));
            }
        }
        //优酷视频
        if(preg_match('/player\.youku\.com\/player\.php\/sid\/(.*?)\/v\.swf/', $html,$match)){
            $vid = $match[1];
            if(empty($vid)){
                return json_encode(array('msg'=>'Youku vid is empty','status'=>400));
            }else{
                return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
            }
        }else{
            return json_encode(array('msg'=>'Video json file is empty','status'=>400));
        }
    }
    
    /**
     * @name:get_ahgame_vedio
     * @description: 分析安游的域名是http://lol.ahgame.com/的视频源json文件，目前找到了优酷的视频
     * @return: 视频源m3u8文件
     * @author: Xiong Jianbang
     * @create: 2015-4-25 下午5:58:12
     **/
    public function get_ahgame_vedio(){
         $html = file_get_contents($this->url);
        if(preg_match('/swf\/loader\.swf\?VideoIDS=(.*?)\&/', $html,$match)   ||  preg_match('/player\.youku\.com\/player\.php\/sid\/(.*?)\/v\.swf/', $html,$match)){
            $vid = $match[1];
                if(empty($vid)){
                    return json_encode(array('msg'=>'Youku vid is empty','status'=>400));
                }else{
                    return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
                }
            }
            else{
                return json_encode(array('msg'=>'Video json file is empty','status'=>400));
            }
        }
        
        /**
         * @name:get_lolqu_video
         * @description: 分析撸撸趣的域名是www.lolqu.com的视频源json文件，目前找到了优酷的视频
         * @return: 视频源m3u8文件
         * @author: Xiong Jianbang
         * @create: 2015-4-25 下午5:29:14
         **/
        public function get_lolqu_video(){
            $html = $this->curl_get($this->url);
            //优酷视频
            if(preg_match('/player\.youku\.com\/player\.php\/sid\/(.*?)\/v\.swf/', $html,$match)){
                $vid = $match[1];
                if(empty($vid)){
                    return json_encode(array('msg'=>'Youku vid is empty','status'=>400));
                }else{
                    return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
                }
            }else{
                return json_encode(array('msg'=>'Video json file is empty','status'=>400));
            }
        }
        
        /**
         * @name:get_demaxiya_video
         * @description: 分析德玛西亚的域名是www.demaxiya.com的视频源json文件，目前找到了优酷和腾讯的视频
         * @return: 视频源json文件或者m3u8文件
         * @author: Xiong Jianbang
         * @create: 2015-4-25 下午5:14:06
         **/
        public function get_demaxiya_video(){
            $arr = pathinfo($this->url);
            $file_id = $arr['filename'];
            if(empty($file_id) || !is_numeric($file_id)){//如果不是数字
                return json_encode(array('msg'=>'File ID is empty','status'=>400));
            }
            $fetch_url = "http://www.demaxiya.com/s/play.php?aid=$file_id";
            $json = $this->curl_get($fetch_url);
            if(empty($json)){
                return json_encode(array('msg'=>'javascript file is empty','status'=>400));
            }
            $arr = json_decode($json,TRUE);
            $html = $arr['playhtml'];
            //腾讯视频
            if(preg_match('/TencentPlayer.swf\?vid=(.*?)&/', $html,$match)){
                $vid = $match[1];
                if(empty($vid)){
                    return json_encode(array('msg'=>'Tencent vid is empty','status'=>400));
                }else{
                    return json_encode(array('msg'=>$this->get_tencent_video_json($vid),'status'=>200,'type'=>'qq','vid'=>$vid));
                }
            }
            //优酷视频
            elseif(preg_match('/player\.youku\.com\/player\.php\/sid\/(.*?)\/v\.swf/', $html,$match)){
                $vid = $match[1];
                if(empty($vid)){
                    return json_encode(array('msg'=>'Youku vid is empty','status'=>400));
                }else{
                    return json_encode(array('msg'=>$this->get_youku_video_json($vid),'status'=>200,'type'=>'youku','vid'=>$vid));
                }
            }else{
                return json_encode(array('msg'=>'Video json file is empty','status'=>400));
            }
        }
        
        /**
         * @name:get_4399_video_url
         * @description: 获取4399视频的播放地址
         * @param: $vid=播放id
         * @return: 视频地址
         * @author: Xiong Jianbang
         * @create: 2015-5-25 上午10:15:06
         **/
        private function get_4399_video_url($vid){
        	$xml = simplexml_load_file("http://video.5054399.com/v/v2/video_{$vid}.xml"); //创建 SimpleXML对象
        	foreach($xml->item->attributes() as $key => $value){
        	    if($key=='url'){
        	    	return strval($value);
        	    }
        	}
        }
        
        /**
         * @name:get_17173_video_json
         * @description: 获取17173的mp4地址
         * @param: $html17173本站的页面
         * @return: 17173的json格式地址
         * @author: Xiong Jianbang
         * @create: 2015-5-22 上午11:03:22
         **/
        private function get_17173_video_json($html){
            if(preg_match('/data-pnum="(\d*?)"/', $html,$match)){
                $vid = isset($match[1])?$match[1]:0;
                if(!empty($vid)){
                    return json_encode(array('msg'=>$this->get_17173_video_api($vid),'status'=>200,'type'=>'17173','vid'=>$vid));
                }
            }
        }
        
        
        /**
         * @name:get_aipai_video_json
         * @description: 获取爱拍的mp4地址
         * @param: $html爱拍本站的页面
         * @return: 爱拍的json格式地址
         * @author: Xiong Jianbang
         * @create: 2015-5-22 上午11:03:22
         **/
        private function get_aipai_video_json($html,$is_gzip=''){
            //还有一个是6033，不知道是什么东西
            if($is_gzip==31139){
                $html = $this->gzdecode($html);
            }
            if(preg_match('/property="og:videosrc" content="(.*?)\?{1,}.*?"/', $html,$match)){
                $video_url = isset($match[1])?$match[1]:'';
                if(!empty($video_url)){
                    return json_encode(array('msg'=>$video_url,'status'=>200,'type'=>'aipai'));
                }
            }
            if(preg_match('/property="og:videosrc" content="(.*?)"/', $html,$match)){
                $video_url = isset($match[1])?$match[1]:'';
                if(!empty($video_url)){
                    return json_encode(array('msg'=>$video_url,'status'=>200,'type'=>'aipai'));
                }
            }
        }
        
        private function gzdecode($data) {
            $len = strlen($data);
            if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) {
                return null;  // Not GZIP format (See RFC 1952)
            }
            $method = ord(substr($data,2,1));  // Compression method
            $flags  = ord(substr($data,3,1));  // Flags
            if ($flags & 31 != $flags) {
                // Reserved bits are set -- NOT ALLOWED by RFC 1952
                return null;
            }
            // NOTE: $mtime may be negative (PHP integer limitations)
            $mtime = unpack("V", substr($data,4,4));
            $mtime = $mtime[1];
            $xfl   = substr($data,8,1);
            $os    = substr($data,8,1);
            $headerlen = 10;
            $extralen  = 0;
            $extra     = "";
            if ($flags & 4) {
                // 2-byte length prefixed EXTRA data in header
                if ($len - $headerlen - 2 < 8) {
                    return false;    // Invalid format
                }
                $extralen = unpack("v",substr($data,8,2));
                $extralen = $extralen[1];
                if ($len - $headerlen - 2 - $extralen < 8) {
                    return false;    // Invalid format
                }
                $extra = substr($data,10,$extralen);
                $headerlen += 2 + $extralen;
            }
        
            $filenamelen = 0;
            $filename = "";
            if ($flags & 8) {
                // C-style string file NAME data in header
                if ($len - $headerlen - 1 < 8) {
                    return false;    // Invalid format
                }
                $filenamelen = strpos(substr($data,8+$extralen),chr(0));
                if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
                    return false;    // Invalid format
                }
                $filename = substr($data,$headerlen,$filenamelen);
                $headerlen += $filenamelen + 1;
            }
        
            $commentlen = 0;
            $comment = "";
            if ($flags & 16) {
                // C-style string COMMENT data in header
                if ($len - $headerlen - 1 < 8) {
                    return false;    // Invalid format
                }
                $commentlen = strpos(substr($data,8+$extralen+$filenamelen),chr(0));
                if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
                    return false;    // Invalid header format
                }
                $comment = substr($data,$headerlen,$commentlen);
                $headerlen += $commentlen + 1;
            }
        
            $headercrc = "";
            if ($flags & 1) {
                // 2-bytes (lowest order) of CRC32 on header present
                if ($len - $headerlen - 2 < 8) {
                    return false;    // Invalid format
                }
                $calccrc = crc32(substr($data,0,$headerlen)) & 0xffff;
                $headercrc = unpack("v", substr($data,$headerlen,2));
                $headercrc = $headercrc[1];
                if ($headercrc != $calccrc) {
                    return false;    // Bad header CRC
                }
                $headerlen += 2;
            }
        
            // GZIP FOOTER - These be negative due to PHP's limitations
            $datacrc = unpack("V",substr($data,-8,4));
            $datacrc = $datacrc[1];
            $isize = unpack("V",substr($data,-4));
            $isize = $isize[1];
        
            // Perform the decompression:
            $bodylen = $len-$headerlen-8;
            if ($bodylen < 1) {
                // This should never happen - IMPLEMENTATION BUG!
                return null;
            }
            $body = substr($data,$headerlen,$bodylen);
            $data = "";
            if ($bodylen > 0) {
                switch ($method) {
                	case 8:
                	    // Currently the only supported compression method:
                	    $data = gzinflate($body);
                	    break;
                	default:
                	    // Unknown compression method
                	    return false;
                }
            } else {
                // I'm not sure if zero-byte body content is allowed.
                // Allow it for now...  Do nothing...
            }
        
            // Verifiy decompressed size and CRC32:
            // NOTE: This may fail with large data sizes depending on how
            //       PHP's integer limitations affect strlen() since $isize
            //       may be negative for large sizes.
            if ($isize != strlen($data) || crc32($data) != $datacrc) {
                // Bad format!  Length or CRC doesn't match!
                return false;
            }
            return $data;
        }
        
        /**
         * @name:get_17173_video_api
         * @description: 获取17173视频的json格式的视频文件
         * @param: $vid=游戏视频id
         * @return: JSON格式的视频文件的URL地址
         * @author: Xiong Jianbang
         * @create: 2015-5-20 下午6:06:49
         **/
        private function get_17173_video_api($vid){
            return "";
//             return "http://v.17173.com/api/video/vInfo/id/{$vid}";
        }
        
        /**
         * @name:get_leshiyun_video_json
         * @description: 获取乐视云视频的json格式的视频文件
         * @param: $uu=用户唯一标识码，由乐视网统一分配并提供
         * @param:$vu=视频唯一标识码
         * @return: 视频播放地址
         * @author: Xiong Jianbang
         * @create: 2015-4-28 上午11:26:55
         **/
        private function  get_leshiyun_video_json($uu,$vu){
        	return "http://yuntv.letv.com/bcloud.html?uu={$uu}&vu={$vu}";
        }
        
        /**
         * @name:get_ku6_video_json
         * @description: 获取酷六视频的json格式的视频文件
         * @param: $vid=酷六视频的VID号
         * @return: JSON格式的视频文件的URL地址
         * @author: Xiong Jianbang
         * @create: 2015-4-28 上午11:26:55
         **/
        private  function get_ku6_video_json($vid=0){
            return "http://v.ku6.com/fetch.htm?t=getVideo4Player&vid=$vid";
        }
        
        /**
         * @name:get_duowan_video_json
         * @description: 获取多玩的json格式的视频文件
         * @param: $vid=多玩的VID号
         * @return: 视频文件的URL地址
         * @author: Xiong Jianbang
         * @create: 2015-4-25 下午5:02:28
         **/
        private function get_duowan_video_json($vid=0){
            return "http://playapi.v.duowan.com/index.php?vid=$vid&r=play%2Fvideo";
        }
        
        /**
         * @name:get_tencent_video_json
         * @description: 获取腾讯视频的json格式的视频文件
         * @param: $vid=腾讯视频的VID号
         * @return: JSON格式的视频文件的URL地址
         * @author: Xiong Jianbang
         * @create: 2015-4-25 下午5:02:28
         **/
        private function get_tencent_video_json($vid=0){
            return "http://vv.video.qq.com/geturl?vid=$vid&otype=json";
        }
        
        /**
         * @name:get_sohu_video_json
         * @description: 获取搜狐视频的json格式的视频文件
         * @param: $vid=搜狐视频的VID号
         * @return: JSON格式的视频文件的URL地址
         * @author: Xiong Jianbang
         * @create: 2015-4-28 上午11:26:55
         **/
        private  function get_sohu_video_json($vid=0){
            return "";
//             return "http://my.tv.sohu.com/videinfo.jhtml?m=viewtv&vid=$vid";
        }
        
        /**
         * @name:get_youku_video_json
         * @description: 获取优酷的json格式的视频文件
         * @param: $vid=优酷的VID号
         * @return: m3u8格式视频文件的URL地址
         * @author: Xiong Jianbang
         * @create: 2015-4-25 下午5:16:49
         **/
        private function get_youku_video_json($vid=0){
            require_once 'youku.class.php';
            $obj_yk = new Youku($vid);
            $ret = $obj_yk->get_m3u8_file();
            if(isset($ret['status']) && $ret['status']==400){
            	return '';
            }
            return $ret;
        }
        
        
        
        /**
         * @name:get_tudou_video_json
         * @description: 获取土豆视频的json格式的视频文件
         * @param: $vid=土豆视频的VID号
         * @return: json格式视频文件的URL地址
         * @author: Xiong Jianbang
         * @create: 2015-4-25 下午5:16:49
         **/
        private function get_tudou_video_json($vid=0){
            return "http://www.tudou.com/outplay/goto/getItemSegs.action?iid=$vid";
        }
        
        /**
         * @name:get_host
         * @description: 获取域名
         * @return: 域名字符串
         * @author: Xiong Jianbang
         * @create: 2015-4-25 下午5:10:05
         **/
        private function get_host(){
            $arr_url = parse_url($this->url);
            if(empty($arr_url)){
                return FALSE;
            }
            $host = $arr_url['host'];
            return $host;
        }
        
        /**
         * @name:curl_get
         * @description: CURL GET请求
         * @param: $url=请求地址  $second=超时时间
         * @return: string or boolean
         * @author: Xiong Jianbang
         * @create: 2014-12-9 上午11:54:29
         **/
        private function curl_get($url, $second=10){
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
    }
    
    // $url = 'http://www.demaxiya.com/lol/22744.html';
    // $url = "http://www.lolqu.com/dashen/sktfaker/11318.html";
    // $url = "http://lol.ahgame.com/mov/2014020844805.shtml";
    //$url = "http://lol.ahgame.com/mov/2013111930885.shtml";
    // $url = "http://www.ooqiu.com/2015/0424/66934.html";
    // $url = "http://www.ooqiu.com/2014/0913/18038.html";
    //http://lol.duowan.com/1312/251115918553.html  多玩的土豆视频来源
