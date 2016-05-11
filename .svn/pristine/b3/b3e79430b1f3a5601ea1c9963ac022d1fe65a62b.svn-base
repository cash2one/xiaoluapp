<?php
/**
 * @name:handle_data类
 * @description: 处理数据类
 * @author: Xiong Jianbang
 * @create: 2015-8-3 下午3:13:24
 **/
class handle_data{
    private $config;
    private $conn;
    private $table_import_name;
    private $import_fields;
    private $dir_name;
    private $last_date;
    private $table_statistics_name;
    private $statistics_fields;
    private $prefix;

    public function __construct($config=array(),$conn=NULL){
        //定义日志文件的名称
        if(empty($config)){
            return array('msg'=>'配置不能为空','status'=>400);
        }
        $this->config = $config;
        if(empty($conn)){
            return array('msg'=>'连接对象不能为空','status'=>400);
        }
        $this->table_import_name = isset($this->config['import_table_name'])?$this->config['import_table_name']:NULL;
        if(empty($this->table_import_name)){
            return array('msg'=>'导入表名称不能为空','status'=>400);
        }
        $this->import_fields = isset($this->config['import_fields'])&& !empty($this->config['import_fields'])?$this->config['import_fields']:NULL;
        if(empty($this->import_fields)){
            return array('msg'=>'导入数据的字段配置不能为空','status'=>400);
        }
        $this->dir_name = isset($this->config['dir_name'])?$this->config['dir_name']:'';
        if(empty($this->dir_name)){
            return array('msg'=>'目录不能为空','status'=>400);
        }
        $this->table_statistics_name = isset($this->config['statistics_table_name'])?$this->config['statistics_table_name']:NULL;
        if(empty($this->table_statistics_name)){
            return array('msg'=>'统计数据的表格名称不能为空','status'=>400);
        }
        $this->statistics_fields = isset($this->config['statistics_fields'])&& !empty($this->config['statistics_fields'])?$this->config['statistics_fields']:NULL;
        if(empty($this->import_fields)){
            return array('msg'=>'统计数据的字段配置不能为空','status'=>400);
        }
        $this->conn = $conn;
        //上一天的日期
        $this->last_date = $this->config['last_date'];
        $this->prefix = isset($this->config['import_prefix'])?$this->config['import_prefix']:'kyx_';
        
        
    }

    /**
     * @name:create_table
     * @description: 创建表格
     * @return: boolean
     * @author: Xiong Jianbang
     * @create: 2015-7-23 下午2:54:48
     **/
    public function create_table($type='import'){
        switch ($type) {
        	case 'import':
        	    $table = $this->table_import_name;
        	    $fields = $this->import_fields;
        	    break;
        	case 'statistics':
        	    $table = $this->table_statistics_name;
        	    $fields = $this->statistics_fields;
        	    break;
        	case 'file':
        	    $file_month = date("Ym",THIS_DATETIME - 86400 * 2);
        	    $table = $this->table_import_name.'_'.$file_month;
        	    $fields = $this->import_fields;
        	    break;
        }
        $sql = "CREATE TABLE  IF NOT EXISTS `{$table}` ( ";
        foreach ($fields as $key=>$value) {
            $type = $value['type'];
            $len = $value['len'];
            $comment = isset($value['comment'])?$value['comment']:'';
            $sql .= "`{$this->prefix}{$key}` {$type}({$len})  COMMENT '{$comment}',";//表字段加前缀
        }
        $sql = rtrim($sql,',');
        $sql .=" ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        if($this->conn->query($sql)){
            return array('msg'=>"{$table}表检测完成",'status'=>200);
        }
        return array('msg'=>"{$table}表创建失败",'status'=>400);
    }

    /**
     * @name:import_data
     * @description: 导入数据
     * @return: boolean
     * @author: Xiong Jianbang
     * @create: 2015-7-23 下午3:00:27
     **/
    public function import_data(){
        //日志数量
        $log_files_count = isset($this->config['log_files_count'])?$this->config['log_files_count']:1;
        //表的字段数量
        $field_count = count($this->import_fields);
        $arr_logs = range(1,$log_files_count);
        //删除同一天的旧记录
        $sql = "DELETE FROM `{$this->table_import_name}` WHERE {$this->prefix}in_date={$this->last_date}";
        $this->conn->query($sql);
        $sql = "insert into  {$this->table_import_name} (";
        foreach ($this->import_fields as $key=>$value) {
            $sql .= " `{$this->prefix}{$key}`,"; //表字段加前缀
        }
        $sql =rtrim($sql,',');
        $sql .=") values ";
        //拼装insert into语句
        $yesterday = strtotime($this->last_date);//取上一天的文件名
        foreach ($arr_logs as $value) {
            $filename = '';
            //电视客户端或手机客户端数据
            if(strpos($this->dir_name, 'mo_') !== false || strpos($this->dir_name, 'tv_') !== false ){
                $filename = WEBPATH_DIR."/data/{$this->dir_name}/".date('Y/m/Ymd',$yesterday)."_".$value.".dat";
            }
            //视频APP数据
            if(strpos($this->dir_name, 'video_app_') !== false ){
                $filename = WEBPATH_DIR."/data/{$this->dir_name}/data".date('Ymd',$yesterday)."_".$value.".dat";
            }
            if(empty($filename) || !is_file($filename)){
                continue;
            }
            $arr_txt = file($filename);
            if(empty($arr_txt)){
                continue;
            }
            $str_sql_2 ='';
            $i = 0;
            foreach ($arr_txt as $json) {
                if(empty($json)){
                    continue;
                }
                $arr = json_decode($json,TRUE);
                if(empty($arr)){
                    continue;
                }
                $tmp_sql_val  = '';
                $value_count = 1;//值的数量，因为已有日期值，所以初始化为1
                $str_sql_2 .= "(";
                //为了弥补旧数据中不带in_date数据的补救措施
//                 if(strpos($this->dir_name, 'video_app_') !== false ){ 
//                     $str_sql_2 .= date('Ymd',$yesterday).",";
//                 }
//                 $j=0;
                foreach ($this->import_fields as $k=>$v) {//根据表的字段名获取对应的值
//                     $j++;
//                     if($j==1){
//                     	continue;
//                     }
                    $k = trim($k);
                    $v1 = '';
                    if(isset($arr[$k])){
                        $v1 = $arr[$k];
                    }
                    $tmp_sql_val .= "'".$v1."',";
                    $value_count++;
                     
                }
                $str_sql_2 .= rtrim($tmp_sql_val,',')."),";
                if($i<>10){//每10条数据插入一次
                    $i++;
                }else{
                     $tmp_sql_3 = rtrim($sql.$str_sql_2,',');
                    $this->conn->query($tmp_sql_3);
                    $i = 0;
                    $str_sql_2 = "";
                }
            }
            $tmp_sql_3 = '';
            if(!empty($str_sql_2)){
                $tmp_sql_3 = rtrim($sql.$str_sql_2,',');
                $this->conn->query($tmp_sql_3);
            }
        }
        return array('msg'=>"{$this->table_import_name}表导入数据成功",'status'=>200);
    }

    /**
     * @name:file_data
     * @description: 归档归档两天的前数据
     * @author: Xiong Jianbang
     * @create: 2015-7-27 上午11:57:38
     **/
    public function file_data(){
        $tmp_this_day = date("Ymd",THIS_DATETIME -  2*24*60*60);
        $file_month = date('Ym',strtotime($tmp_this_day));
        $this->create_table('file');//创建归档表
        $table = $this->table_import_name;
        $prefix = $this->prefix;
        $sql = "DELETE FROM {$table}_{$file_month} WHERE {$prefix}in_date={$tmp_this_day}";
        $this->conn->query($sql);
        //归档两天的前数据
        $sql = "INSERT INTO `{$table}_{$file_month}` SELECT * FROM {$table} WHERE {$prefix}in_date={$tmp_this_day}";
        $rs = $this->conn->query($sql);
        // 从临时表中删除对应月的数据
        if($rs){
            //不归档当天的数据
            $sql = "DELETE FROM {$table} WHERE {$prefix}in_date={$tmp_this_day}";
            $this->conn->query($sql);
            return array('msg'=>"{$table}表归档到{$table}_{$file_month}表导入数据成功",'status'=>200);
        }else{
            return array('msg'=>"{$table}表归档到{$table}_{$file_month}表导入数据失败",'status'=>400);
        }
    }

    /**
     * @name:__destruct
     * @description: 析构方法
     * @author: Xiong Jianbang
     * @create: 2015-7-23 下午2:59:45
     **/
    public function __destruct(){
        $this->conn->close();
    }
}
