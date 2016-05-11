<?php

/**
 * 日志类
 *
 */

class Log{

    private static $instance    = NULL;
    //文件句柄
    private static $handle      = NULL;
    //日志开关
    private $log_switch     = NULL;
    //日志相对目录
    private $log_file_path      = NULL;
    //日志文件前缀,
    private $log_file_pre       = '';


    /**
     * @name:__construct
     * @description: 构造方法，初始化
     * @author: Xiong Jianbang
     * @create: 2014-11-5 下午5:48:10
     **/
    protected function __construct(){
        $this->log_file_path     = LOG_FILE_PATH;
        $this->log_switch     = LOG_SWITCH;
        $log_type = '';
        if(defined('LOG_TYPE')){
            $log_type = LOG_TYPE;
        }
        $this->log_file_pre = $this->log_file_pre .date('Ymd').'_'.$log_type.'.log';
    }

    /**
     * @name:get_instance
     * @description: 单例模式
     * @author: Xiong Jianbang
     * @create: 2014-11-5 下午5:49:09
     **/
    public static function get_instance(){
        if(!self::$instance instanceof self){
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @name:log
     * @description: 写入日志方法
     * @param: $type=事件类型 $desc=日志描述
     * @author: Xiong Jianbang
     * @create: 2014-11-5 下午5:47:23
     **/
    public function log($type,$desc){
        $time = date('Y-m-d H:i:s');
        if($this->log_switch){

            if(self::$handle == NULL){
                $filename = $this->log_file_pre;
                if (! self::$handle = @fopen($this->log_file_path . '/' .$filename, 'a')) {
                    return false;
                }
            }
            @flock( self::$handle, LOCK_EX);         // 独占锁定
            switch($type){
            	case 'notice':
            	    fwrite(self::$handle, 'NOTICE LOG:' . "\t" .$time ."\t" . $desc . ' ' . "\n");
            	    break;
            	case 'warning':
            	    fwrite(self::$handle, 'WARNING LOG:' . "\t" .$time ."\t" . $desc . ' ' . "\n");
            	    break;
        	    case 'success':
        	        fwrite(self::$handle, 'SUCCESS LOG:' . "\t" .$time ."\t" . $desc . ' ' . "\n");
        	        break;
            	default:
            	    fwrite(self::$handle, 'SIMPLE LOG:' ."\t" . $time ."\t" . $desc . ' ' . "\n");
            	    break;
            }
            @flock(self::$handle, LOCK_UN);// 解锁
        }
    }

    /**
     * @name:close
     * @description: 关闭文件句柄 
     * @author: Xiong Jianbang
     * @create: 2014-11-5 下午5:48:41
     **/
    public function close(){
        fclose(self::$handle);
    }
}