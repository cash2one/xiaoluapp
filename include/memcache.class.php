<?php
/**
 * Memcache 操作类
 *
 * 在config文件中 添加
     相应配置(可扩展为多memcache server)
    define('MEMCACHE_HOST', '10.35.52.33');//memcache服务器地址
    define('MEMCACHE_PORT', 11211);//memcache服务器端口
    define('MEMCACHE_EXPIRATION', 0);//缓存时间
    define('MEMCACHE_PREFIX', 'kyx');//KEY的MD5前辍
    define('MEMCACHE_COMPRESSION', FALSE);//是否对数据进行压缩
    demo:
        $cacheObj = new kyx_memcache();        
        $cacheObj -> set('keyName','this is value');
        $cacheObj -> get('keyName');
        exit;
 * @access  public
 * @return  object
 * @date    2014-12-22
 */
class kyx_memcache{
 
    private $local_cache = array();//缓存的数据
    private $m;//缓存操作类
    private $client_type;//留着用的，可以连不同的缓存系统（目前只有Memcache一个）
    protected $errors = array();//错误信息

    public function __construct(){
        $this->client_type = class_exists('Memcache') ? "Memcache" :FALSE;
         
        if($this->client_type){
            // 判断引入类型
            $this->m = new Memcache();
            $this->auto_connect();  
        }else{
            echo 'ERROR: Failed to load Memcached or Memcache Class (∩_∩)';
            exit;
        }
    }
     
    /**
     * @Name: auto_connect
     * @param:none
     * @todu 连接memcache server
     * @return : none
     * @author ddcai
    **/
    private function auto_connect(){
        $configServer = array(
                                'host' => MEMCACHE_HOST,
                                'port' => MEMCACHE_PORT,
                                'weight' => 1
                            );
        if(!$this->add_server($configServer)){
            echo 'ERROR: Could not connect to the server named '.MEMCACHE_HOST;
        }else{
            //echo 'SUCCESS:Successfully connect to the server named '.MEMCACHE_HOST;  
        }
    }
     
    /**
     * @Name: add_server
     * @param:$server=array('host' => MEMCACHE_HOST,//服务器IP
                            'port' => MEMCACHE_PORT,//服务器端口
                            'weight' => 1 //权重
                            );
     * @todu 连接memcache server
     * @return : TRUE or FALSE
     * @author ddcai
    **/
    public function add_server($server){
        extract($server);//把数组元素变为变量
        return $this->m->addServer($host, $port, FALSE, $weight);
    }
     
    /**
     * @Name: add_server
     * @todu 添加
     * @param:$key key
     * @param:如果$key是数组，则要求格式如下：
     * $key = array(
     * 			'key'=>Key值,
     * 			'value'=>数据,
     * 			'expiration'=>缓存时间
     * 		);
     * @param:$value 值
     * @param:$expiration 过期时间
     * @return : TRUE or FALSE
     * @author ddcai
    **/
    public function add($key = NULL, $value = NULL, $expiration = 0){
        if(is_null($expiration)){
            $expiration = MEMCACHE_EXPIRATION;
        }
        if(is_array($key)){
            foreach($key as $multi){
                if(!isset($multi['expiration']) || $multi['expiration'] == ''){
                    $multi['expiration'] = MEMCACHE_EXPIRATION;
                }
                $this->add($this->key_name($multi['key']), $multi['value'], $multi['expiration']);
            }
        }else{
            $this->local_cache[$this->key_name($key)] = $value;
            $add_status = $this->m->add($this->key_name($key), $value, MEMCACHE_COMPRESSION, $expiration);

            return $add_status;
        }
    }
     
    /**
     * @Name   与add类似,但服务器有此键值时仍可写入替换
     * @param  $key key
     * @param:如果$key是数组，则要求格式如下：
     * $key = array(
     * 			'key'=>Key值,
     * 			'value'=>数据,
     * 			'expiration'=>缓存时间
     * 		);
     * @param  $value 值
     * @param  $expiration 过期时间
     * @return TRUE or FALSE
     * @author ddcai
    **/
    public function set($key = NULL, $value = NULL, $expiration = NULL){
        if(is_null($expiration)){
            $expiration = MEMCACHE_EXPIRATION;
        }
        if(is_array($key)){
            foreach($key as $multi){
                if(!isset($multi['expiration']) || $multi['expiration'] == ''){
                    $multi['expiration'] = MEMCACHE_EXPIRATION;
                }
                $this->set($this->key_name($multi['key']), $multi['value'], $multi['expiration']);
            }
        }else{
            $this->local_cache[$this->key_name($key)] = $value;
            $add_status = $this->m->set($this->key_name($key), $value, MEMCACHE_COMPRESSION, $expiration);
            return $add_status;
        }
    }
     
    /**
     * @Name   get 根据键名获取值
     * @param  $key key
     * @param  $key 也可以是一个键数组
     * @return array OR json object OR string...
     * @author ddcai
    **/
    public function get($key = NULL){
        if($this->m){
            if(isset($this->local_cache[$this->key_name($key)])){
                return $this->local_cache[$this->key_name($key)];
            }
            if(is_null($key)){
                $this->errors[] = 'The key value cannot be NULL';
                return FALSE;
            }
             
            if(is_array($key)){
            	$arr = array();//要返回的数据
            	foreach($key as $n=>$k){
                    $arr[$n] = $this->m->get($this->key_name($k));
                }
                return $arr;
            }else{
                return $this->m->get($this->key_name($key));
            }
        }else{
            return FALSE;
        }      
    }
     
    /**
     * @Name   delete
     * @param  $key key
     * @param  $key 也可以是一个键数组
     * @param  $expiration 服务端等待删除该元素的总时间
     * @return true OR false
     * @author ddcai
    **/
    public function delete($key, $expiration = NULL){
        if(is_null($key)){
            $this->errors[] = 'The key value cannot be NULL';
            return FALSE;
        }
         
        if(is_null($expiration)){
            $expiration = MEMCACHE_EXPIRATION;
        }
         
        if(is_array($key)){
            foreach($key as $multi){
                $this->delete($multi, $expiration);
            }
        }else{
            unset($this->local_cache[$this->key_name($key)]);
            return $this->m->delete($this->key_name($key), $expiration);
        }
    }
     
    /**
     * @Name   replace
     * @param  $key 要替换的key
     * @param:如果$key是数组，则要求格式如下：
     * $key = array(
     * 			'key'=>Key值,
     * 			'value'=>数据,
     * 			'expiration'=>缓存时间
     * 		);
     * @param  $value 要替换的value
     * @param  $expiration 到期时间
     * @return none
     * @author ddcai
    **/
    public function replace($key = NULL, $value = NULL, $expiration = NULL){
        if(is_null($expiration)){
            $expiration = MEMCACHE_EXPIRATION;
        }
        if(is_array($key)){
            foreach($key as $multi) {
                if(!isset($multi['expiration']) || $multi['expiration'] == ''){
                    $multi['expiration'] = MEMCACHE_EXPIRATION;
                }
                $this->replace($multi['key'], $multi['value'], $multi['expiration']);
            }
        }else{
            $this->local_cache[$this->key_name($key)] = $value;
             
			$replace_status = $this->m->replace($this->key_name($key), $value, MEMCACHE_COMPRESSION, $expiration);
            return $replace_status;
        }
    }
     
    /**
     * @Name   replace 清空所有缓存
     * @return none
     * @author ddcai
    **/
    public function flush(){
        return $this->m->flush();
    }
     
    /**
     * @Name   获取服务器池中所有服务器的版本信息
    **/
    public function getversion(){
        return $this->m->getVersion();
    }
     
     
    /**
     * @Name   获取服务器池的统计信息
    **/
    public function getstats($type="items"){
        $stats = $this->m->getStats($type);
        return $stats;
    }
     
    /**
     * @Name: 开启大值自动压缩
     * @param:$tresh 控制多大值进行自动压缩的阈值。
     * @param:$savings 指定经过压缩实际存储的值的压缩率，值必须在0和1之间。默认值0.2表示20%压缩率。
     * @return : true OR false
     * @author ddcai
    **/
    public function setcompressthreshold($tresh, $savings=0.2){
        $setcompressthreshold_status = $this->m->setCompressThreshold($tresh, $savings=0.2);
        return $setcompressthreshold_status;
    }
     
    /**
     * @Name: 生成md5加密后的唯一键值
     * @param:$key key
     * @return : md5 string
     * @author ddcai
    **/
    private function key_name($key){
        return md5(strtolower(MEMCACHE_PREFIX.$key));
    }
     
    /**
     * @Name: 向已存在元素后追加数据
     * @param:$key key
     * @param:$value value
     * @return : true OR false
     * @author ddcai
    **/
    public function append($key = NULL, $value = NULL){
        $this->local_cache[$this->key_name($key)] = $value;
        $append_status = $this->m->append($this->key_name($key), $value);
        return $append_status;
    }//END append
 
}// END class
?>