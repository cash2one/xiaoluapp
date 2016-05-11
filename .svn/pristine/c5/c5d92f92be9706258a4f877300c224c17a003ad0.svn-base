<?php
/**
 * @copyright: @快游戏广州 2014
 * @description: 搜索接口数据层类
 * @file: search_model.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-09-22  12:32
 * @version 1.0
 **/

class search_model{
	public $table_hotword			= 'mzw_hot_word';			//搜索热词表
	public $table_recommend			= 'mzw_game_recommend';		//热门、最新游戏表
	public $table_game				= 'mzw_game';				//游戏主表
	public $table_game_version		= 'mzw_game_version';		//游戏版本表
	public $table_search_recommend 	= 'mzw_search_recommend';	//搜索结果游戏关联推荐
	
	private $_mzw_online_type_key 	= 'redis_mzw_online_game_type_';	//网游分类key
	private $_mzw_game_firm_key		= 'redis_mzw_game_firm_';			//游戏厂商key
	private $mem = null;//缓存对像
	private $_conn = null;//数据库连接对像
	
	public function __construct(){
		global $conn;
		//parent::__construct();
		//$config['redis_host'] = $this->config->item( 'redis_host' );
		//$config['redis_port'] = $this->config->item( 'redis_port' );
		//$config['redis_auth'] = $this->config->item( 'redis_auth' );
		//$this->load->library( 'lib_redis' , $config, 'redis' );
		$this->mem = new kyx_memcache();//建立缓存对像
		$this->_conn = $conn;
	}
	
	/**
	 * @name: get_newest_gv_id_from_recommend
	 * @description: 通过mzw_game_recommend表里的g_id获取对应游戏的最新版本id
	 * @return: array 成功返回最新版本gv_id数组，失败返回空数组
	 * @author: Quan Zelin
	 * @create: 2014-11-18  16:35
	 **/
	public function get_newest_gv_id_from_recommend(){
		$return = array();
		
		$sql = 'SELECT b.g_version_newest_id FROM '.$this->table_recommend.' a LETF JOIN '.$this->table_game
				.' b ON a.g_id=b.g_id';
		$arr = $this->_conn->find($sql);
		if(isset($arr) && !is_empty($arr)){
			foreach( $arr as $val ){
				$return[] = $val['g_version_newest_id'];
			}
		}
		return $return;
	}
	
	/**
	 * @name: update_search_result_recommend
	 * @description: 更新或添加关联推荐
	 * @param $param = array(
	 * 					'g_id'=>array(0=>g_id,1=>g_id....),//
	 * 					'recommend_g_id'=>array(0=>recommend_g_id,1=>recommend_g_id....),//
	 * 				);
	 * @return: boolean
	 * @author: Quan Zelin
	 * @create: 2014-12-14  16:35
	 **/
	public function update_search_recommend( $param ){
		$return = FALSE;
		//参数不能为空
		if( is_empty( $param ) || !isset( $param['g_id'] ) || is_empty( $param['g_id']
			|| !isset( $param['recommend_g_id'] ) || is_empty( $param['recommend_g_id'] ) ) ){
			return $return;
		}
		foreach( $param['g_id'] as $val ){
			$one_sql = 'SELECT g_id FROM '.$this->table_search_recommend.' WHERE g_id='.$val;
			$is_unique = $this->_conn->get_one($one_sql);
			if( !$is_unique ){
				//如果不存在，添加记录
				$this->_conn->save_info( $this->table_search_recommend, array( 'g_id' => $val, 'recommend_g_id' => implode( ',', $param['recommend_g_id'] ) ) );
			}else{
				//如果存在，将数据更新到后面
				$old_rcm_g_id 	= $this->get_search_recommend_id_by_g_id( $val );
				$rcm_g_id		= array_unique( array_merge( $old_rcm_g_id, $param['recommend_g_id'] ) );
				$this->_conn->update( $this->table_search_recommend, array('recommend_g_id' => $rcm_g_id,'g_id' => $val ), 'g_id');
			}
		}
		return TRUE;
	}
	
	/**
	 * @name: get_search_recommend_id_by_g_id
	 * @description: 通过g_id获取相应的推荐游戏
	 * @return: array 成功返回id数组，失败返回空数组
	 * @author: Quan Zelin
	 * @create: 2014-12-15  16:35
	 **/
	public function get_search_recommend_id_by_g_id( $g_id ){
		$return = array();
		if( is_empty( $g_id ) ){
			return $return;
		}
		$sql = 'SELECT recommend_g_id FROM '.$this->table_search_recommend.' WHERE g_id='.$g_id;
		$arr = $this->_conn->find($sql);
		if(isset($arr) && !is_empty($arr)){
			$return = explode( ',', $arr[0]['recommend_g_id'] );
		}
		return $return;
	}

	/**
	 * @name: get_online_type
	 * @description: 	获取网游分类id
	 * @return:	array
	 * @author: Quan Zelin
	 * @create: 2014-12-3 15:26:50
	 **/
	public function get_online_type(){
		$return = array();

		$sql = "SELECT `t_id` FROM `mzw_game_type` WHERE `t_p_id`=3";
		$res = $this->_conn->find( $sql );
		if( !is_empty( $res ) ){
			foreach( $res as $val )
				$return[] = $val['t_id'];
		}
			
		return $return;
	}
	
	/**
	 * @name: count_word
	 * @description: 	搜索计数
	 * @return:	void
	 * @author: Quan Zelin
	 * @create: 2014-12-16 15:26:50
	 **/
	public function count_word( $key, $keyword,$source = 1,$type = 1 ){
			$sql = "SELECT `mhw_weight` FROM `mzw_hot_word` WHERE `mhw_hotword`='{$keyword}' AND `mhw_source` = ".intval($source);
			//die($sql);
			$row = $this->_conn->get_one( $sql );
			if( $row ){//如果已有，则+1
				$sql_update = "UPDATE `mzw_hot_word` set mhw_weight=mhw_weight+1 WHERE `mhw_hotword`='{$keyword}' AND `mhw_source` = ".intval($source);
				$this->_conn->query($sql_update);
			}else{//如果没有，则插入数据库
				$data = array(
					"mhw_hotword"=>$keyword,//'搜索关键词',
					"mhw_weight"=>0,//'权重排序（默认为0，值越大越靠前）',
					"mhw_status"=>1,//'是否禁用（0禁用，1启用，2搜索框下显示，默认为1）',
                    "mhw_type" => intval($type), //类型
                    "mhw_source"=>intval($source),//搜索来源（1：游戏搜索 2：视频搜索 3:用户搜索 4：标签搜索 5:视频游戏搜索）,
				);
				$this->_conn->save('mzw_hot_word', $data);
				$row['mhw_weight'] = 0;
			}
		return $row['mhw_weight']+1;
	}
	
	/**
	 * @name: save_count_word
	 * @description: 	搜索计数
	 * @param: $arr = array(
	 * 'keyword'=>,
	 * 'ok'=>,
	 * 'iscache'=>
	 * )
	 * @return:	void
	 * @author: chengdongcai
	 * @create: 2015-01-15 15:26:50
	 **/
	public function save_count_word( $arr ){
		/*顺序不能搞错*/
		$mydata['keyword'] = $arr['keyword'];//搜索关键词
		$mydata['success'] = intval($arr['ok']);//搜索成功
		$mydata['time'] = time();//搜索时间
		$mydata['date'] = date("Ymd",$mydata['time']);//搜索日期
		$mydata['md5'] = md5($mydata['keyword']);//搜索关键词的MD5值
		$mydata['iscache'] = intval($arr['iscache']);//搜索来源缓存
        $mydata['source'] = intval($arr['source']);//搜索来源（1：游戏搜索 2：视频搜索）
		$tmp_str = '';
		foreach ($mydata as $key => $val){
			if(!is_empty($val)){
				$tmp_str .= $val.'|';
			}else{
				$tmp_str .= '0|';
			}
		}
		$tmp_str = substr($tmp_str,0,-1).chr(13).chr(10);
		//记录日志
		write_file_random($tmp_str,"keyword",true);
	}
	
	
	/**
	 * @name: get_firm_name
	 * @description: 	获取厂商名称
	 * @return:	void
	 * @author: Quan Zelin
	 * @create: 2014-12-16 15:26:50
	 **/
	public function get_firm_name( $f_id ){
		$return = '';
		if( is_empty( $f_id ) ){
			return $return;
		}
		$sql = "SELECT `f_id`, `f_name_en` FROM `mzw_game_firm` WHERE f_id=".$f_id;
		$res = $this->_conn->find( $sql );
		$list = array();
		if( $res ){
			foreach( $res as $val ){
				$list[$val['f_id']] = $val['f_name_en'];
			}
			$return = $list[$f_id];
		}
		return $return;
	}
		
}