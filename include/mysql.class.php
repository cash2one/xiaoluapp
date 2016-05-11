<?php
//
 /****================================
  * 数据库操作类
  * @author ddcai
  * @email 252378189@qq.com
  * @package: 后台管理系统
  $c = array(
  "port"=>'3306',//端口
  "host"=>'127.0.0.1',//主机IP
  "dbname"=>'mydbname',//数据库名
  "username"=>'root',//用户名
  "password"=>'123456',//密码
  "charset"=>'utf8'//数据库编码
  );
 ==================================***/
class Mysql{
    private $conn;

    public function __construct($c=''){
		if( !isset($c['host']) || empty($c['host'])){
			global $c;//如果没有初始化数据库连接信息，则使用公共变量里的连接
		}
        !isset($c['port']) && $c['port'] = '3306';
        $server = $c['host'] . ':' . $c['port'];
        $this->conn = @mysql_connect($server, $c['username'], $c['password'], true) or die('connect db error');
        @mysql_select_db($c['dbname'], $this->conn) or die('select db error');
        if($c['charset']){
            @mysql_query("set names " . $c['charset'], $this->conn);
        }
    }

    /**
     * 执行 mysql_query 并返回其结果.
     */
    public function query($sql){
        $result = @mysql_query($sql, $this->conn);
        return $result;
    }

    /**
     * 执行 SQL 语句, 返回结果的第一条记录(是一个对象).
     */
    public function get_one($sql){
        $rs = $this->query($sql);
        if((mysql_num_rows($rs)>0) && ($row = mysql_fetch_array($rs)) ){
            return $row;
        }else{
            return false;
        }
    }
    /*
     * 返回查询集的一行结果
     */
	public function FetchArray($result){
		return @mysql_fetch_array($result, MYSQL_ASSOC);
	}
    /**
     * 返回查询结果集, 以 key 为键组织成关联数组, 每一个元素是一个对象.
     * 如果 key 为空, 则将结果组织成普通的数组.
     */
    public function find($sql, $key=null){
        $data = array();
        $result = $this->query($sql);
        while($row = mysql_fetch_array($result)){
            if(!empty($key)){
                $data[$row[$key]] = $row;
            }else{
                $data[] = $row;
            }
        }
        return $data;
    }
	/**
	*返回最近插入的自增ID
	*
	**/
    public function last_insert_id(){
        return @mysql_insert_id($this->conn);
    }

    /**
     * 执行一条带有结果集计数的 count SQL 语句, 并返该计数.
     */
    public function count($sql){
        $result = $this->query($sql);
        if($row = @mysql_fetch_array($result)){
            return (int)$row[0];
        }else{
            return 0;
        }
    }

    /**
     * 获取指定编号的记录.
     * @param int $id 要获取的记录的编号.
     * @param string $field 字段名, 默认为'id'.
     */
    public function load($table, $id, $field='id'){
        $sql = "SELECT * FROM `{$table}` WHERE `{$field}`='{$id}'";
        $row = $this->get($sql);
        return $row;
    }

    /**
     * 保存一条记录
     * @param object $row
     */
    public function save($table, $row,$returnsql=false){
        $sqlA = '';
        foreach($row as $k=>$v){
            $sqlA .= "`$k` = '$v',";
        }
        $sqlA = substr($sqlA, 0, -1);
        $sql  = "INSERT INTO `{$table}` SET $sqlA";
      	if($returnsql){
        	return $sql;
        }
        if($this->query($sql)){
            return $this->last_insert_id();
        }else{
            return false;
        }
    }

    /**
     * 更新$arr[id]所指定的记录.
     * @param array $row 要更新的记录, 键名为id的数组项的值指示了所要更新的记录.
     * @return int 影响的行数.
     * @param string $field 字段名, 默认为'id'.
     */
    public function update($table, $row, $field='id',$returnsql=false){
        $sqlF = '';
        foreach($row as $k=>$v){
            $sqlF .= "`$k` = '$v',";
        }
        $sqlF = substr($sqlF, 0, -1);
        if(is_object($row)){
            $id = $row->{$field};
        }else{
            $id = $row[$field];
        }
        $sql  = "UPDATE `{$table}` SET $sqlF WHERE `{$field}`=$id";
        if($returnsql){
        	return $sql;
        }
        return $this->query($sql);
    }
    
    /**
     * 更新$arr[id]所指定的记录.
     * @param array $row 要更新的记录, 键名为id的数组项的值指示了所要更新的记录.
     * @return int 影响的行数.
     * @param string $field 字段名, 默认为'id'.
     */
    public function update2($table, $row, $where,$returnsql=false){
    	$sqlF = '';
    	foreach($row as $k=>$v){
    		if( preg_match('/[\+\-\*\/]/',$v)>0 ){//判断是否要求计算
    			$sqlF .= "`".$k."`=".$v.",";
    		}else{
    			$sqlF .= "`".$k."`='".mysql_real_escape_string($v)."',";
    		}
    	}
    	$sqlF = substr($sqlF, 0, -1);
    	
    	$str_where = ' ';
    	foreach ( $where as $k2=>$v2){
    		if(!is_empty($k2) && !is_empty($v2)){
    			$str_where .= " AND ".$k2."='".$v2."'";
    		}
    	}
    	
    	$sql  = "UPDATE `{$table}` SET $sqlF WHERE 1 ".$str_where;
    	if($returnsql){
    		return $sql;
    	}
    	return $this->query($sql);
    }
    /**
     * 删除一条记录.
     * @param int $id 要删除的记录编号.
     * @return int 影响的行数.
     * @param string $field 字段名, 默认为'id'.
     */
    public function remove($table, $id, $field='id'){
        $sql  = "DELETE FROM `{$table}` WHERE `{$field}`='{$id}'";
        return $this->query($sql);
    }

    /*开始一个事务.*/
    public function begin(){
        @mysql_query('begin');
    }
    /* 提交一个事务.*/
    public function commit(){
        @mysql_query('commit');
    }
    /*回滚一个事务.*/
    public function rollback(){
        @mysql_query('rollback');
    }
	/*释放连接资源.*/
	public function close(){
		if($this->conn){
			@mysql_close($this->conn);
		}
	}
	/*确保数据库连接不超时.*/
	public function ping(){
		return @mysql_ping($this->conn);
	}
	
	
	//析构函数释放连接资源
    public function __destruct(){     
       if($this->conn){
	   		@mysql_close($this->conn);
	   }
    }
    
}