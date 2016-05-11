<?php
	/**
	 * @copyright: @快游戏广州 2014
	 * @description: sphinx检索类
	 * @file: search.class.php
	 * @author: chengdongcai
	 * @charset: UTF-8
	 * @time: 2014-11-10  12:32
	 * @version 1.0
	 **/
	class search{
		private $_sphinx_ip;					//主机
		private $_sphinx_port;					//端口
		private $_sphinx_match_mode;			//匹配模式
		private $_sphinx_sort_mode;				//排序模式
		private $_sphinx_sort_expr;				//排序表达式
		private $_weight;						//默认权重
		private $_enc_key; 						//加密key
		//redis
		private $_search_result_key = 'mem_search_result_'; 		//搜索结果key前缀
		private $_search_count_key	= 'mem_search_count_';		//搜索词计数key前缀
        private $_search_video_result_key = 'mem_search_video_result_';     //视频搜索结果key前缀
        private $_search_video_count_key  = 'mem_search_video_count_';		//视频搜索词计数key前缀
        private $_search_user_result_key = 'mem_search_user_result_';     //用户搜索结果key前缀
        private $_search_user_count_key  = 'mem_search_user_count_';		//用户搜索词计数key前缀
        private $_search_hot_user_result_key = 'mem_search_hot_user_result_';     //用户搜索结果key前缀
        private $_search_hot_user_count_key  = 'mem_search_hot_user_count_';		//用户搜索词计数key前缀
        private $_search_tag_result_key = 'mem_search_tag_result_';     //标签搜索结果key前缀
        private $_search_tag_count_key  = 'mem_search_tag_count_';		//标签搜索词计数key前缀
        private $_search_video_game_result_key = 'mem_search_video_game_result_';     //视频游戏搜索结果key前缀
        private $_search_video_game_count_key  = 'mem_search_video_game_count_';		//视频游戏搜索词计数key前缀
		private $spx = null;//搜索对像
		private $mem = null;//缓存对像
		private $search = null;//搜索数据模块
		private $_conn = null;//数据库连接对像
		/**
		 * @name: __construct
		 * @description: 构造方法
		 * @return: 
		 * @author: chengdongcai
		 * @create: 2014-11-11 16:26:50
		 **/
		public function __construct(){
			global $conn;
            global $uconn;
			$this->_conn = $conn;
            $this->_uconn = $uconn;
			$this->_enc_key	= SYS_URL_KYX_KEY;
			$this->spx = new SphinxClient();//建立搜索对像
			$this->mem = new kyx_memcache();//建立缓存对像
			$this->search = new search_model();//建立搜索数据模块
			//$this->load->library( 'lib_redis' , $config, 'redis' );
			//$this->load->model('search/search_model', 'search');
			
			//$this->load->database();
			//获取配置中定义的sphinx服务器ip和端口
			$this->_sphinx_ip 			= SPHINX_HOST;//在配置文件里定义了
			$this->_sphinx_port			= SPHINX_PORT;//在配置文件里定义了
			$this->_sphinx_match_mode	= SPH_MATCH_PHRASE;	//匹配模式(在sphinxclient.php里有定义的)p
			$this->_sphinx_sort_mode	= SPH_SORT_EXTENDED;	//排序模式(在sphinxclient.php里有定义的)
			$this->_sphinx_sort_expr	= 'total_down_nums DESC, @relevance DESC';//排序表达式
			//各索引中的默认权重
			$this->_weight = array(
				//游戏索引
				'mzw_game' => array(
						'gv_title'			=> 30,		//游戏中文名称
						'gv_title_en' 		=> 30, 		//游戏英文名称
						'gv_pinyin_all' 	=> 30, 		//游戏名称全拼
						'gv_pinyin_simple' 	=> 20, 		//游戏名称拼音首字母
						'gv_letter'			=> 20,		//英文首字母缩写
						'g_firm_name'		=> 1,		//厂商名称
				),
                'mzw_video' => array( //视频索引
                    'vvl_title'			=> 30,		//视频中文名称
                    'vvl_title_en' 		=> 10 		//视频英文名称
                ),
                'mzw_user' => array( //视频索引
                    'nickname'	    => 40,		//用户昵称
                    'username' 		=> 20, 		//用户名
                    'email' 	    => 10, 		//用户邮箱
                    'desc' 	    => 10 		//用户描述
                ),
                'mzw_video_tag' => array( //视频标签索引
                    'vtc_name'	    => 20		//标签名称
                ),
                'mzw_video_game' => array( //视频游戏索引
                    'gi_name'	    => 40,		//视频游戏名称
                    'gi_intro'	    => 10,		//视频游戏简介
                    'gi_simple_txt'	    => 10,		//视频游戏一句话描述
                )
			);
			
		}
		
		/**
		 * @name: 	retrieve
		 * @description: 	sphinx搜索游戏数据
		 * @param:	string	$key		对称加密key
		 * @param:	string	$keyword	搜索关键词
		 * @param:	string	$index_name	索引名称
		 * @param:	array	$weight		字段权重
		 * @param:	array	$filter		设置过滤(values为整数数组)		array( 'attr' => 'g_id', 'values'=>array(1,3), 'exclude'=>false );
		 * @param:	int		$offset		起始
		 * @param:	int		$limit		条数
		 * @param:	array	$sph_config	自定义主机和端口配置参数，注意ip和端口需要对称加密	如array( 'sphinx_ip' => 'host', 'sphinx_port' => port, 'sphinx_match_mode' => ..., 'sphinx_sort_model'=>.. )
		 * @return:	array
		 * @author: chengdongcai
		 * @create: 2014-11-11 16:26:50
		 **/
		public function retrieve(){
			//定义初始返回内容
			$return 	= array( "status" => "-1", "data" => "");
			$keyword 	= strtolower( get_param( 'keyword' ) );						//关键字	
			$keyword	= filter_search($keyword);
			$index_name	= strtolower( get_param( 'index_name' ) ) ;					//索引名称
			$key		= get_param( 'key' );																		
			$is_cache	= !is_empty( $this->mem );	//是否可以通过缓存存取数据
			//无该索引或搜索关键字为空时
			if( !isset( $this->_weight[$index_name] ) || is_empty( $keyword )){
				exit( json_encode( $return ) );
			}
			
			//key验证
			
			$limit		= ( $limit = get_param( 'limit' ) ) ? abs( $limit ) : 10;//查询条数
			$offset		= ( $page = abs(get_param( 'page' ) ) ) ? ($page - 1 )*$limit : 0;//起始查询位置
			$weight		= ( $weight = get_param( 'weight' ) ) ? $weight : $this->_weight[$index_name];//字段权重
			$filter		= ( $filter = get_param( 'filter' ) ) ? $filter : 0;	//过滤条件(0表示不过滤，1表示过滤)
			
			$sph_config = ( $sph_config = get_param( 'sph_config' ) ) ? $sph_config : null;	//自定义配置
			//初始化配置
			$this->load_sph_config( $sph_config );
			if( is_empty( $this->_sphinx_ip ) || is_empty( $this->_sphinx_port ) ){
				 exit( json_encode( $return ) );
			}
			
			//搜索词计数
			$tmp_count_arr = array(
					'keyword'=>$keyword,//关键词
					'ok'=>2,//是否搜索成功（1是搜索成功，2搜索失败）
					'iscache'=>1,//是否缓存返回(1是缓存返回,2非缓存返回）
                    'source'=>1
			);
			$mem_count_key = $this->_search_count_key.'_'.$keyword;
			$new_count = $this->search->count_word( $mem_count_key, $keyword );
			
			//如果可以查缓存，则
			if($is_cache){
				//先查缓存服务器
				$mem_result_key = $this->_search_result_key.'_'.$keyword;
				$tmp_info = $this->mem -> get($mem_result_key);
		        if($tmp_info!==FALSE){//如果有找到，则直接返回缓存的内容
		        	$tmp_info_arr = json_decode($tmp_info,true);
		        	//判断是否有搜索到数据
		        	if(isset($tmp_info_arr['data']) && !is_empty($tmp_info_arr['data'])){
		        		$tmp_count_arr['ok'] = 1;//搜索 1成功
		        	}else{
		        		$tmp_count_arr['ok'] = 2;//搜索 2失败
		        	}
		        	$tmp_count_arr['iscache'] = 1;//缓存返回
                    $tmp_count_arr['source'] = 1;//搜索来源（1：游戏搜索 2：视频搜索）
		        	$this->search->save_count_word( $tmp_count_arr );
		        	unset($tmp_info_arr,$tmp_count_arr);
		        	//返回数据
		        	exit( $tmp_info );
		        }
			}
			try{
				$this->spx -> setServer( $this->_sphinx_ip, $this->_sphinx_port );					//主机和端口
				$this->spx -> setArrayResult( TRUE );												//控制搜索结果集的返回格式为数组
				$this->spx -> setFieldWeights( $weight );											//设置权重
				$this->spx -> setLimits( $offset, $limit );											//分页
				$this->spx -> setMatchMode( $this->_sphinx_match_mode );							//匹配模式
				//排序模式
				/*
				 *  有如下可选的匹配模式：
			    SPH_MATCH_ALL, 匹配所有查询词(默认模式);
			    SPH_MATCH_ANY, 匹配查询词中的任意一个;
			    SPH_MATCH_PHRASE, 将整个查询看作一个词组，要求按顺序完整匹配;
			    SPH_MATCH_BOOLEAN, 将查询看作一个布尔表达式 (参见 Section 4.2, “布尔查询语法”);
			    SPH_MATCH_EXTENDED, 将查询看作一个Sphinx/Coreseek内部查询语言的表达式 。
			    SPH_MATCH_EXTENDED2, 使用第二版的“扩展匹配模式”对查询进行匹配.
			    SPH_MATCH_FULLSCAN, 强制使用下文所述的“完整扫描”模式来对查询进行匹配。
				 */
				if(is_empty( $this->_sphinx_sort_expr )){
					$this->spx->setSortMode( $this->_sphinx_sort_mode );
				}else{
					$this->spx->setSortMode( $this->_sphinx_sort_mode, $this->_sphinx_sort_expr );
				}
				//整理推荐数据(加上了增量索引的查找)
				$result_rcm = $this->spx->query( $keyword, $index_name );
//				var_dump($result_rcm);exit;
				//将没有过滤下架的数据排序
				$result_rcm = $this->sort_data2( $keyword, $result_rcm );
				
				//获取对应的推荐数据
				$rcm_data = $this->get_search_recommend( $result_rcm['tmp_all_ids'] );
				if( !is_empty( $rcm_data ) ){
					$data_g_ids = array();//获取有用的游戏ID（从过滤规则上来取）
					if($filter==1){//如果要求过滤下架的游戏，则
						//经过过滤的搜索结果列表id集合(上架或者即将上架的游戏ID)
						if(!is_empty($result_rcm['tmp_up_ids'])){
							$data_g_ids = $result_rcm['tmp_up_ids'];
						}
					}else{//如果不过滤，则
						$data_g_ids = $result_rcm['tmp_all_ids'];
					}

					//插入的数据为一个推荐列表，需要将这些数据提到同级列表中
					$tmp_return = array();
					foreach( $result_rcm['data'] as $key => $val ){
						if(in_array($key, $data_g_ids)){
							$tmp_return[] = $val;
						}
						//判断推荐里是否有
						if( isset( $rcm_data[$key]) && is_array($rcm_data[$key])){
							foreach( $rcm_data[$key] as $v ){
								if( !isset( $tmp_return[$v['gv_id']] ) ){
									$tmp_return[] = $v;
								}
							}
						}
					}
					//去除重复
					$return['data'] = $tmp_return_id = array();
					foreach( $tmp_return as $val ){
						if( !in_array( $val['gv_id'], $tmp_return_id ) ){
							$return['data'][] 	= $val;
							$tmp_return_id[]	= $val['gv_id'];
						}
					}
					$return['data'] =  array_shift( array_chunk( $return['data'], $limit, TRUE ) );
					
				}else{
					if($filter==1){//如果要求过滤下架的游戏，则
						//如果没有推荐相关的游戏则查询上架数据
						$data 	= array();
						foreach ($result_rcm['data'] as $key=>$val){
							if(!in_array($val['gv_status'], array(2,4))){//不是下架的游戏就是上架的了
								$data[$key] = $val;
							}
						}
						$return['data'] = $data;
					}else{//如果不过滤，则
						$return['data'] = $result_rcm['data'];
					}
				}
				$return["status"] = 200;
				
				$return_json = json_encode( $return );
				//将搜索结果存入缓存服务器
				if( $is_cache ){
					$this->mem -> set($mem_result_key,$return_json,300);//设置为300秒过期
				}
				//记录搜索关键词
	        	//判断是否有搜索到数据
	        	if(isset($return['data']) && !is_empty($return['data'])){
	        		$tmp_count_arr['ok'] = 1;//搜索 1成功
	        	}else{
	        		$tmp_count_arr['ok'] = 2;//搜索 2失败
	        	}
	        	$tmp_count_arr['iscache'] = 2;//非缓存返回(1是缓存返回,2非缓存返回）
                $tmp_count_arr['source'] = 1;//搜索来源（1：游戏搜索 2：视频搜索）
	        	$this->search->save_count_word( $tmp_count_arr );
	        	unset($return,$tmp_count_arr);
				//返回数据
				exit( $return_json );
			}catch( Exception $e ){
				//记录搜索关键词
				$tmp_count_arr['ok'] = 2;//搜索 1成功/2失败
				$tmp_count_arr['iscache'] = 2;//非缓存返回(1是缓存返回,2非缓存返回）
				$this->search->save_count_word( $tmp_count_arr );
				//返回数据
				exit( json_encode( $return ) );
			}
		}
		
		/**
		 * @name: get_search_recommend
		 * @description: 	获取推荐数据
		 * @param:	array	$g_ids		需要查询是否有关联推荐的游戏id
		 * @return:	array
		 * @author: Quan Zelin
		 * @create: 2014-12-9 10:26:50
		 **/
		private function get_search_recommend( $g_ids ){
			$return = array();
			if( empty( $g_ids ) ){
				return $return;
			}
			//查询搜索结果中拥有关联游戏推荐的数据
			$sql = "SELECT `g_id`, `recommend_g_id` FROM mzw_search_recommend WHERE `g_id` IN (" .implode(',', $g_ids). ")";
			
			$res = $this->_conn->find( $sql );
			if( !is_empty( $res ) ){
				$tmp = '';
				$data_tmp = array();
				foreach( $res as $val ){
					$tmp .= $val['recommend_g_id'].',';
					$data_tmp[$val['g_id']] = explode( ',', $val['recommend_g_id'] );//用于记录哪个游戏下拥有哪些推荐游戏
				}
				//所有推荐游戏的id
				$rcm_g_ids 	= array_unique( preg_split( '/[\s,]+/', $tmp, null, PREG_SPLIT_NO_EMPTY ) );
				//查取推荐的游戏信息
				$data = array();
				if( !is_empty( $rcm_g_ids ) ){
					$sql = "SELECT gv.`g_id`, gv.`gv_id`, `gv_package_name`, gv.`gv_update_time`, gv.`firm_id`,gv.`gv_title`,gv.gv_description, gv.`gv_letter`,gv.`gv_title_en`,gv.`gv_type_id`,
							 gv.`gv_status`, gv.`gv_down_nums`,gv.`gv_ico_key`,gv.gv_version_no,gv.gv_version_name
						FROM `mzw_game_version` AS gv WHERE gv.`gv_status`=1 AND gv.`gv_id` IN (".implode(',', $rcm_g_ids).") ";
					$info = $this->_conn->find( $sql );
					if( !is_empty( $info ) ){
						foreach( $info as $val ){
							$data[$val['gv_id']] = array(
									'g_id'					=> (int)$val['g_id'],
									'gv_id'					=> (int)$val['gv_id'],
									'gv_package_name'		=> $val['gv_package_name'],
									'firm_id'				=> (int)$val['firm_id'],
									'g_firm_name'			=> $this->search->get_firm_name( intval($val['firm_id']) ),
									'gv_title'				=> $val['gv_title'],
									'gv_update_time'		=> $val['gv_update_time'],
									'gv_description'		=> $val['gv_description'],
									'gv_title_en'			=> $val['gv_title_en'],
									'gv_type_id'			=> (int)$val['gv_type_id'],
									'gv_status'				=> (int)$val['gv_status'],
									'total_down_nums'		=> intval($val['gv_down_nums']),//各版本下载量总和
									'gv_ico_key'			=> $val['gv_ico_key'],
									'gv_version_no'			=> $val['gv_version_no'],
									'gv_version_name'			=> $val['gv_version_name']
							);

						}
						foreach( $data as $g_id => $val ){
							//记录哪个游戏下拥有哪些推荐游戏
							foreach( $data_tmp as $b_k => $b_v ){
								if( in_array( $g_id, $b_v ) ){
									$return[$b_k][] = $data[$g_id];
								}
							}
							//按sort_rcm_data方式进行排序
							uasort( $return[$b_k], array( $this, 'sort_rcm_data') );
						}
					}
				}
			}
			return $return;
		}
		
		/**
		 * @name: sort_rcm_data
		 * @description: 	按下载量排序推荐数据
		 * @param:	array	$a	
		 * @param: 	array 	$b
		 * @return:	int
		 * @author: Quan Zelin
		 * @create: 2014-12-9 10:26:50
		 **/
		private function sort_rcm_data( $a, $b ){
			$a_num = $a['total_down_nums'];
			$b_num = $b['total_down_nums'];
			return ( $a_num == $b_num ) ? 0 : ( ( $a_num < $b_num ) ? 1 : -1 );
		}

		
		/**
		 * @name: sort_data2
		 * @description: 	对检索结果进行排序（按网游分类、下载量综合排序）
		 * @param:	array	$a
		 * @param: 	array 	$b
		 * @return:	int
		 * @author: chengdongcai
		 * @create: 2014-12-9 10:26:50
		 **/
		private function sort_data2( $keyword, $result ){
			$data = array();
			if( !is_empty( $result ) && isset( $result['matches'] ) && !is_empty( $result['matches'] ) ){
				$tmp_data = $tmp_num = $tmp_online = $tmp_single = array();
				//获取网游分类id
				$online_type = $this->search->get_online_type();
				
				$tmp_up_ids_arr = '';//上架的游戏ID
				$tmp_down_ids_arr = '';//下载的游戏ID
				foreach( $result['matches'] as $key => $val ){
					$result['matches'][$key]['attrs']['gv_id'] = $val['id'];//这个就是gv_id来的，要注意
					$tmp_num[$val['id']] = $val['attrs']['total_down_nums'];//用于排序的下载量数组
					//分开存储单机和网游
					if(in_array( $val['attrs']['gv_type_id'], $online_type )){
						//网游
						array_unshift( $tmp_data, $result['matches'][$key]['attrs']);
					}else{
						//单机游戏
						array_push($tmp_data,$result['matches'][$key]['attrs']);
					}
					//已经下架或者隐藏的游戏ID
					if(in_array( $val['attrs']['gv_status'],array(2,4))){
						$tmp_down_ids_arr[] = $val['id'];
					}else{//上架或者即将上线的游戏ID
						$tmp_up_ids_arr[] = $val['id'];
					}
				}
				//按下载量排序完后按相关度排序
				$tmp_firm_data = array();
				$tmp_all_ids_1 = array();
				$tmp_all_ids_2 = array();
				foreach( $tmp_data as $val ){
					$str = strtolower( $val['gv_title'].'|'.$val['gv_title_en'].'|'.$val['gv_pinyin_all'].'|'.$val['gv_pinyin_simple'].'|'.$val['gv_letter'] );
					if( stripos( $str, $keyword ) !== FALSE ){
						$data[$val['gv_id']] = $val;
						$tmp_all_ids_1[] = $val['gv_id'];
					}else{
						$tmp_firm_data[$val['gv_id']] = $val;
						$tmp_all_ids_2[] = $val['gv_id'];
					}
				}
				//将通过厂商搜出来的放在后面
				//$return_data['data'] = array_merge( $data, $tmp_firm_data );	
				$return_data['data'] = $data + $tmp_firm_data;
				$return_data['tmp_down_ids'] = $tmp_down_ids_arr;//下架的游戏ID
				$return_data['tmp_up_ids']   = $tmp_up_ids_arr;//上架的游戏ID
				$return_data['tmp_all_ids'] = $tmp_all_ids_1 + $tmp_all_ids_2;//所有的游戏ID
				//var_dump($return_data['tmp_all_ids']);exit;
			}else{
				$return_data = array();
			}
			return $return_data;
		}
		
		
		/**
		 * @name: sort_data
		 * @description: 	对检索结果进行排序（按网游分类、下载量综合排序）
		 * @param:	array	$a	
		 * @param: 	array 	$b
		 * @return:	int
		 * @author: Quan Zelin
		 * @create: 2014-12-9 10:26:50
		 **/
		private function sort_data( $keyword, $result ){
			$data = array();
			if( !is_empty( $result ) && isset( $result['matches'] ) && !is_empty( $result['matches'] ) ){
				$tmp_data = $tmp_num = $tmp_online = $tmp_single = array();
				//获取网游分类id
				$online_type = $this->search->get_online_type();
				//$filter = array('attr'=>'gv_status', 'values'=>array(2,4));//下架/隐藏状态的游戏
				$tmp_up_ids_arr = '';//上架的游戏ID
				$tmp_down_ids_arr = '';//下载的游戏ID
				foreach( $result['matches'] as $key => $val ){
					$result['matches'][$key]['attrs']['gv_id'] = $val['id'];//这个就是gv_id来的，要注意
					$tmp_num[$val['id']] = $val['attrs']['total_down_nums'];//用于排序的下载量数组
					//分开存储单机和网游
					if(in_array( $val['attrs']['gv_type_id'], $online_type )){
						$tmp_online[$val['id']] = $result['matches'][$key]['attrs'];
					}else{
						$tmp_single[$val['id']] = $result['matches'][$key]['attrs'];
					}
					//已经下架或者隐藏的游戏ID
					if(in_array( $val['attrs']['gv_status'],array(2,4))){
						$tmp_down_ids_arr[] = $val['id'];
					}else{//上架或者即将上线的游戏ID
						$tmp_up_ids_arr[] = $val['id'];
					}
				}
				//按照下载量升序排序
				asort( $tmp_num );
				//将网游排前面
				foreach( $tmp_num as $key => $val ){
					if( isset( $tmp_online[$key] ) ){
						array_unshift( $tmp_data, $tmp_online[$key] );
						unset( $tmp_num[$key] );
					}
				}
				//降序排序
				arsort( $tmp_num );
				//单机排后面
				foreach( $tmp_num as $key => $val ){
					if( isset( $tmp_single[$key] ) ){
						array_push( $tmp_data, $tmp_single[$key] );
						unset( $tmp_num[$key] );
					}
				}
				//按下载量排序完后按相关度排序
				$tmp_firm_data = array();
				foreach( $tmp_data as $val ){
					$str = strtolower( $val['gv_title'].'|'.$val['gv_title_en'].'|'.$val['gv_pinyin_all'].'|'.$val['gv_pinyin_simple'].'|'.$val['gv_letter'] );
					if( stripos( $str, $keyword ) !== FALSE ){
						$data[$val['gv_id']] = $val;
					}else{
						$tmp_firm_data[$val['gv_id']] = $val;
					}
				}
				//$return_data['data'] = array_merge( $data, $tmp_firm_data );	//将通过厂商搜出来的放在后面
				$return_data['data'] = $data + $tmp_firm_data;
				$return_data['tmp_down_ids'] = $tmp_down_ids_arr;//下架的游戏ID
				$return_data['tmp_up_ids']   = $tmp_up_ids_arr;//上架的游戏ID
			}else{
				$return_data = array();
			}
			return $return_data;
		}
		
		
		/**
		 * @name: load_sph_config
		 * @description: 	自定义主机和端口配置
		 * @param:	array	$sph_config	自定义主机和端口配置参数
		 * @return:	array
		 * @author: Quan Zelin
		 * @create: 2014-11-11 16:26:50
		 **/
		private function load_sph_config( $sph_config ){
			if( !is_empty( $sph_config ) && is_type( $sph_config, 'array' ) ){
				//定义主机
				$this->_sphinx_ip			= ( isset( $sph_config['sphinx_ip'] ) && !is_empty( $sph_config['sphinx_ip'] ) ) ? en_de_code( $sph_config['sphinx_ip'], $this->_enc_key, 2 ) : $this->_sphinx_ip;
				//定义端口
				$this->_sphinx_port			= ( isset( $sph_config['sphinx_port'] ) && !is_empty( $sph_config['sphinx_port'] ) ) ? (int)en_de_code( $sph_config['sphinx_port'], $this->_enc_key, 2 ) : $this->_sphinx_port;
				//定义匹配模式
				$this->_sphinx_match_mode 	= ( isset( $sph_config['sphinx_match_mode'] ) && !is_empty( $sph_config['sphinx_match_mode'] ) ) ? $sph_config['sphinx_match_mode'] : $this->_sphinx_match_mode;
				//定义排序模式
				$this->_sphinx_sort_mode	= ( isset( $sph_config['sphinx_sort_mode'] )  && !is_empty( $sph_config['sphinx_sort_mode'] ) ) ? $sph_config['sphinx_sort_mode'] : $this->_sphinx_sort_mode;
				//定义排序表达式
				$this->_sphinx_sort_expr	= ( isset( $sph_config['sphinx_sort_expr'] ) && !is_empty( $sph_config['sphinx_sort_expr'] ) ) ? $sph_config['sphinx_sort_expr'] : $this->_sphinx_sort_expr;
			}
		}
		
		

		
		/**
		 * @name: redis_save_search_result
		 * @description: 	将搜索结果存入redis
		 * @param:	string	请求的完整地址
		 * @param:	array	通过该地址得到的查询结果
		 * @return:	array
		 * @author: Quan Zelin
		 * @create: 2014-12-3 15:26:50
		 **/
		private function redis_save_search_result( $raw_url, $result, $expire=0 ){
			$key 	= md5( $this->_search_result_key.$raw_url );
			if( !is_type( $result, 'string' ) )
				$result = json_encode( $result );
			$this->redis->set( $key, $result, $expire );
		}


        /**
         * @name: 	video_retrieve
         * @description: 	sphinx搜索视频数据
         * @param:	string	$key		对称加密key
         * @param:	string	$keyword	搜索关键词
         * @param:	string	$index_name	索引名称
         * @param:	array	$weight		字段权重
         * @param:	array	$filter		设置过滤(values为整数数组)		array( 'attr' => 'g_id', 'values'=>array(1,3), 'exclude'=>false );
         * @param:	int		$offset		起始
         * @param:	int		$limit		条数
         * @param:	array	$sph_config	自定义主机和端口配置参数，注意ip和端口需要对称加密	如array( 'sphinx_ip' => 'host', 'sphinx_port' => port, 'sphinx_match_mode' => ..., 'sphinx_sort_model'=>.. )
         * @return:	array
         * @author: chengdongcai
         * @create: 2014-11-11 16:26:50
         **/
        public function video_retrieve($param){

            //定义初始返回内容
            $return = array(
                'status' => -1, //状态码
                'total' => 0, //数据总条数
                'pagecount' => 0, //总页数
                'pagesize' => intval($param['pagesize']), //每页显示数据
                'pagenum' => intval($param['pagenum']), //当前页
                'rows' => array() //数据数组
            );

            $keyword 	= isset($param['keyword']) ? filter_search($param['keyword']) : '';		//关键字
            $index_name	= isset($param['index_name']) ? $param['index_name'] : 'mzw_video';		//索引名称
            $game_id	= isset($param['game_id']) ? $param['game_id'] : 0;		//游戏id
            $is_return	= isset($param['is_return']) ? intval($param['is_return']) : 0;		//是否返回数据（0：不返回 1:返回）
            $filter	= isset($param['filter']) ? $param['filter'] : 0;		//是否过滤隐藏视频
            $is_cache	= !is_empty( $this->mem );	//是否可以通过缓存存取数据

            //无该索引或搜索关键字为空时
            if( !isset( $this->_weight[$index_name] ) || is_empty( $keyword ) || is_empty($game_id)){
                exit( json_encode( $return ) );
            }

            //其他参数
            $limit		= $param['pagesize'];//查询条数
            $offset		= isset($param['pagenum']) ? (($param['pagenum'] - 1 ) * $limit) : 0;//起始查询位置
            $weight		= (isset($param['weight']) && !empty($param['weight'])) ? $param['weight'] : $this->_weight[$index_name];//字段权重
            $sph_config =  isset($param['sph_config']) ? $param['sph_config'] : null;	//自定义配置

            //初始化配置
            $this->load_sph_config( $sph_config );
            if( is_empty( $this->_sphinx_ip ) || is_empty( $this->_sphinx_port ) ){
                exit( json_encode( $return ) );
            }

            //搜索词计数,更新热词搜索次数
            $tmp_count_arr = array(
                'keyword'=>$keyword,//关键词
                'ok'=>2,//是否搜索成功（1是搜索成功，2搜索失败）
                'iscache'=>1,//是否缓存返回(1是缓存返回,2非缓存返回）
                'source'=>2 //搜索来源（1：游戏搜索 2：视频搜索 3：用户搜索 4：标签搜索）
            );
            $mem_count_key = $this->_search_video_count_key.'_'.$keyword;
            $new_count = $this->search->count_word( $mem_count_key, $keyword,2,2 );

            //如果可以查缓存，则
            if($is_cache){
                //先查缓存服务器
                $mem_result_key = $this->_search_video_result_key.'_'.md5($keyword.$index_name.$game_id.$filter.$param['pagenum'].$param['pagesize']);
                $tmp_info = $this->mem -> get($mem_result_key);
                if($tmp_info!==FALSE){//如果有找到，则直接返回缓存的内容
                    $tmp_info_arr = json_decode($tmp_info,true);

                    //判断是否有搜索到数据
                    if(isset($tmp_info_arr['rows']) && !is_empty($tmp_info_arr['rows'])){
                        $tmp_count_arr['ok'] = 1;//搜索 1成功
                    }else{
                        $tmp_count_arr['ok'] = 2;//搜索 2失败
                    }
                    $tmp_count_arr['iscache'] = 1;//缓存返回
                    $tmp_count_arr['source'] = 2;//搜索来源（1：游戏搜索 2：视频搜索 3：用户搜索 4：标签搜索）
                    $this->search->save_count_word( $tmp_count_arr );
                    unset($tmp_count_arr);

                    //返回数据
                    if(!empty($is_return)){
                        unset($tmp_info);
                        return $tmp_info_arr;
                    }else{
                        unset($tmp_info_arr);
                        exit( $tmp_info );
                    }
                }
            }
            try{
                $this->spx -> setServer( $this->_sphinx_ip, $this->_sphinx_port );					//主机和端口
                $this->spx -> setArrayResult( TRUE );												//控制搜索结果集的返回格式为数组
                $this->spx -> setFieldWeights( $weight );											//设置权重
                $this->spx -> setLimits( $offset, $limit );											//分页
                $this->spx -> setMatchMode( $this->_sphinx_match_mode );							//匹配模式

                //游戏过滤模式匹配
                if($game_id == 2){ //我的世界匹配我的世界跟故事模式的视频
                    $this->spx -> setFilter( 'vvl_game_id',array(2,12) );
                }elseif($game_id > 0 && $game_id <> 18){
                    $this->spx -> setFilter( 'vvl_game_id',array($game_id) );
                }

                //显示属性过滤模式匹配
                if(!empty($filter)){
                    $this->spx -> setFilter( 'va_isshow',array($filter) );
                }

                /*  排序模式
                 *  有如下可选的匹配模式：
                    SPH_MATCH_ALL, 匹配所有查询词(默认模式);
                    SPH_MATCH_ANY, 匹配查询词中的任意一个;
                    SPH_MATCH_PHRASE, 将整个查询看作一个词组，要求按顺序完整匹配;
                    SPH_MATCH_BOOLEAN, 将查询看作一个布尔表达式 (参见 Section 4.2, “布尔查询语法”);
                    SPH_MATCH_EXTENDED, 将查询看作一个Sphinx/Coreseek内部查询语言的表达式 。
                    SPH_MATCH_EXTENDED2, 使用第二版的“扩展匹配模式”对查询进行匹配.
                    SPH_MATCH_FULLSCAN, 强制使用下文所述的“完整扫描”模式来对查询进行匹配。
                 */

                //根据视频播放数排序
                $sphinx_sort_expr = '@relevance DESC,vvl_count DESC'; //先按权重排序，再按播放数排序（具体的排序规则根据需求变动）
                $this->spx->setSortMode( $this->_sphinx_sort_mode, $sphinx_sort_expr );

                //搜索数据(加上了增量索引的查找)
                $result_rcm = $this->spx->query( $keyword, $index_name );

                $return["status"] = 200;
                if(!empty($result_rcm)){
                    $return['total'] = isset($result_rcm['total']) ? intval($result_rcm['total']) : 0;

                    //最大页数
                    $return['pagecount'] = intval(ceil($return['total']/$param['pagesize']));

                    //视频数据
                    if(isset($result_rcm['matches']) && !empty($result_rcm['matches'])){
                        foreach($result_rcm['matches'] as $key => $val){
                            if(isset($val['attrs']) && !empty($val['attrs'])){
                                $uid = intval($val['attrs']['vvl_uid']);
                                $author_id = intval($val['attrs']['vvl_author_id']);

                                //获取视频缓存播放次数
                                $a_play_key = 'video_play_num_'.intval($val['id']); //视频播放key
                                $a_old_play_val = $this->mem->get($a_play_key); //获取视频原始播放数

                                //获取作者名称、头像
                                $author_data = array();
                                if(isset($uid) && !empty($uid)){
                                    if(!empty($uid)){
                                        $author_data_key = 'user_data_'.$uid;
                                        $author_data = $this->mem->get($author_data_key);
                                        if($author_data === false){
                                            $sql = "SELECT `nickname`,`source` FROM `uc_members` WHERE `uid` = ".$uid;
                                            $author_data = $this->_uconn->get_one($sql);
                                            $this->mem->set($author_data_key,$author_data,3600);
                                        }
                                    }
                                    $author_name = isset($author_data['nickname']) ? $author_data['nickname'] : '网友';
                                    $author_img = UC_API.'/avatar.php?uid='.$uid.'&type=real&size=big';
                                }else{
                                    if(!empty($author_id)){
                                        $author_data_key = 'author_data_'.$author_id;
                                        $author_data = $this->mem->get($author_data_key);
                                        if($author_data === false){
                                            $sql = "SELECT `va_name`,`va_icon`,`va_icon_get` FROM `video_author_info` WHERE `va_isshow` = 1 AND `id` = ".$author_id;
                                            $author_data = $this->_uconn->get_one($sql);
                                            $this->mem->set($author_data_key,$author_data,3600);
                                        }
                                    }
                                    $author_name = isset($author_data['va_name']) ? $author_data['va_name'] : '网友';
                                    $author_img = (isset($author_data['va_icon_get']) && !empty($author_data['va_icon_get'])) ? (LOCAL_URL_DOWN_IMG.$author_data['va_icon_get']) : (isset($author_data['va_icon']) ? $author_data['va_icon'] : '');
                                }

                                $return['rows'][$key] = array(
                                    'appid' => intval($val['id']), //视频id
                                    'gameid' => intval($val['attrs']['vvl_game_id']), //游戏id
                                    'title' => filter_search(delete_html($val['attrs']['vvl_title'])), //视频标题
                                    'entitle' => filter_search(delete_html($val['attrs']['vvl_title_en'])), //视频英文标题
                                    'uid' => $uid, //用户id
                                    'authorid' => $author_id, //作者id
                                    'authorname' => $author_name, //作者名称
                                    'authorimg' => $author_img, //作者头像
                                    'tagid' => intval($val['attrs']['vvl_tags']), //标签id
                                    'packagename' => $val['attrs']['vvl_package_name'], //游戏包名
                                    'categoryid' => intval($val['attrs']['vvl_category_id']), //关联专辑id
                                    'videoid' => $val['attrs']['vvl_video_id'], //关联视频id
                                    'sourcetype' => intval($val['attrs']['vvl_sourcetype']), //来源类型
                                    'duration' => $val['attrs']['vvl_time'], //视频时长
                                    'playnum' => intval($val['attrs']['vvl_count']) + intval($a_old_play_val), //视频播放数 + 缓存播放数
                                    'typeid' => intval($val['attrs']['vvl_type_id']), //视频类型
                                    'imgurl' => !empty($val['attrs']['vvl_imgurl_get']) ? (LOCAL_URL_DOWN_IMG.$val['attrs']['vvl_imgurl_get']) : $val['attrs']['vvl_imgurl'], //视频图片
                                    'time' => empty($val['attrs']['vvl_upload_time']) ? '' : date('Y-m-d',$val['attrs']['vvl_upload_time']) //采集时间
                                );
                            }
                        }
                    }
                }

                $return_json = json_encode( $return );
                //将搜索结果存入缓存服务器
                if( $is_cache ){
                    $this->mem -> set($mem_result_key,$return_json,300);//设置为300秒过期
                }

                //记录搜索关键词
                if(isset($return['rows']) && !is_empty($return['rows'])){
                    $tmp_count_arr['ok'] = 1;//搜索 1成功
                }else{
                    $tmp_count_arr['ok'] = 2;//搜索 2失败
                }
                $tmp_count_arr['iscache'] = 2;//非缓存返回(1是缓存返回,2非缓存返回）
                $tmp_count_arr['source'] = 2; //搜索来源（1：游戏搜索 2：视频搜索 3：用户搜索 4：标签搜索）
                $this->search->save_count_word( $tmp_count_arr );
                unset($tmp_count_arr);

                //返回数据
                if(!empty($is_return)){
                    unset($return_json);
                    return $return;
                }else{
                    unset($return);
                    exit( $return_json );
                }
            }catch( Exception $e ){
                //记录搜索关键词
                $tmp_count_arr['ok'] = 2;//搜索 1成功/2失败
                $tmp_count_arr['iscache'] = 2;//非缓存返回(1是缓存返回,2非缓存返回）
                $tmp_count_arr['source'] = 2; //搜索来源（1：游戏搜索 2：视频搜索 3：用户搜索 4：标签搜索）
                $this->search->save_count_word( $tmp_count_arr );

                //返回数据
                if(!empty($is_return)){
                    return $return;
                }else{
                    exit( json_encode( $return ) );
                }
            }
        }

        /**
         * @name: 	user_retrieve
         * @description: 	sphinx搜索用户数据
         * @param:	string	$key		对称加密key
         * @param:	string	$keyword	搜索关键词
         * @param:	string	$index_name	索引名称
         * @param:	array	$weight		字段权重
         * @param:	array	$filter		设置过滤(values为整数数组)		array( 'attr' => 'g_id', 'values'=>array(1,3), 'exclude'=>false );
         * @param:	int		$offset		起始
         * @param:	int		$limit		条数
         * @param:	array	$sph_config	自定义主机和端口配置参数，注意ip和端口需要对称加密	如array( 'sphinx_ip' => 'host', 'sphinx_port' => port, 'sphinx_match_mode' => ..., 'sphinx_sort_model'=>.. )
         * @return:	array
         * @author: chengdongcai
         * @create: 2014-11-11 16:26:50
         **/
        public function user_retrieve($param){

            //定义初始返回内容
            $return = array(
                'status' => '-1', //状态码
                'total' => 0, //数据总条数
                'pagecount' => 0, //总页数
                'pagesize' => intval($param['pagesize']), //每页显示数据
                'pagenum' => intval($param['pagenum']), //当前页
                'rows' => array() //数据数组
            );

            $keyword 	= isset($param['keyword']) ? filter_search($param['keyword']) : '';		//关键字
            $game_id	= isset($param['game_id']) ? $param['game_id'] : 0;		//游戏id
            $index_name	= isset($param['index_name']) ? $param['index_name'] : 'mzw_user';		//索引名称
            $is_return	= isset($param['is_return']) ? intval($param['is_return']) : 0;		//是否返回数据（0：不返回 1:返回）
            $is_cache	= !is_empty( $this->mem );	//是否可以通过缓存存取数据

            //无该索引或搜索关键字为空时
            if( !isset( $this->_weight[$index_name] ) || is_empty( $keyword )){
                exit( json_encode( $return ) );
            }

            //其他参数
            $limit		= $param['pagesize'];//查询条数
            $offset		= isset($param['pagenum']) ? (($param['pagenum'] - 1 ) * $limit) : 0;//起始查询位置
            $weight		= (isset($param['weight']) && !empty($param['weight'])) ? $param['weight'] : $this->_weight[$index_name];//字段权重
            $sph_config =  isset($param['sph_config']) ? $param['sph_config'] : null;	//自定义配置

            //初始化配置
            $this->load_sph_config( $sph_config );
            if( is_empty( $this->_sphinx_ip ) || is_empty( $this->_sphinx_port ) ){
                exit( json_encode( $return ) );
            }

            //搜索词计数,更新热词搜索次数
            $tmp_count_arr = array(
                'keyword'=>$keyword,//关键词
                'ok'=>2,//是否搜索成功（1是搜索成功，2搜索失败）
                'iscache'=>1,//是否缓存返回(1是缓存返回,2非缓存返回）
                'source'=>3 //搜索来源（1：游戏搜索 2：视频搜索 3:用户搜索）
            );
            $mem_count_key = $this->_search_user_count_key.'_'.$keyword;
            $new_count = $this->search->count_word( $mem_count_key, $keyword,3,2 );

            //如果可以查缓存，则
            if($is_cache){
                //先查缓存服务器
                $mem_result_key = $this->_search_user_result_key.'_'.md5($keyword.$game_id.$index_name.$param['pagenum'].$param['pagesize']);
                $tmp_info = $this->mem -> get($mem_result_key);
                if($tmp_info!==FALSE){//如果有找到，则直接返回缓存的内容
                    $tmp_info_arr = json_decode($tmp_info,true);

                    //判断是否有搜索到数据
                    if(isset($tmp_info_arr['rows']) && !is_empty($tmp_info_arr['rows'])){
                        $tmp_count_arr['ok'] = 1;//搜索 1成功
                    }else{
                        $tmp_count_arr['ok'] = 2;//搜索 2失败
                    }
                    $tmp_count_arr['iscache'] = 1;//缓存返回
                    $tmp_count_arr['source'] = 3;//搜索来源（1：游戏搜索 2：视频搜索 3:用户搜索 4：标签搜索）
                    $this->search->save_count_word( $tmp_count_arr );
                    unset($tmp_count_arr);

                    //返回数据
                    if(!empty($is_return)){
                        unset($tmp_info);
                        return $tmp_info_arr;
                    }else{
                        unset($tmp_info_arr);
                        exit( $tmp_info );
                    }
                }
            }
            try{
                $this->spx -> setServer( $this->_sphinx_ip, $this->_sphinx_port );					//主机和端口
                $this->spx -> setArrayResult( TRUE );												//控制搜索结果集的返回格式为数组
                $this->spx -> setFieldWeights( $weight );											//设置权重
                $this->spx -> setLimits( $offset, $limit );											//分页
                $this->spx -> setMatchMode( $this->_sphinx_match_mode );							//匹配模式

                /*  排序模式
                 *  有如下可选的匹配模式：
                    SPH_MATCH_ALL, 匹配所有查询词(默认模式);
                    SPH_MATCH_ANY, 匹配查询词中的任意一个;
                    SPH_MATCH_PHRASE, 将整个查询看作一个词组，要求按顺序完整匹配;
                    SPH_MATCH_BOOLEAN, 将查询看作一个布尔表达式 (参见 Section 4.2, “布尔查询语法”);
                    SPH_MATCH_EXTENDED, 将查询看作一个Sphinx/Coreseek内部查询语言的表达式 。
                    SPH_MATCH_EXTENDED2, 使用第二版的“扩展匹配模式”对查询进行匹配.
                    SPH_MATCH_FULLSCAN, 强制使用下文所述的“完整扫描”模式来对查询进行匹配。
                 */

                //用户视频数过滤模式匹配
                $this->spx->SetFilterRange('video_num',1,10000000);

                //根据视频播放数排序
                $sphinx_sort_expr = '@relevance DESC,video_num DESC'; //先按权重排序（具体的排序规则根据需求变动）
                $this->spx->setSortMode( $this->_sphinx_sort_mode, $sphinx_sort_expr );

                //搜索数据(加上了增量索引的查找)
                $result_rcm = $this->spx->query( $keyword, $index_name );

                $return["status"] = 200;
                if(!empty($result_rcm)){

                    if($game_id <> 18){
                        //总页数
                        $pagecount = intval(ceil($result_rcm['total']/$param['pagesize']));
                        $temp_arr = array();
                        for($i = 1;$i<=$pagecount;$i++){
                            $offset	= ($i - 1 ) * $limit;//起始查询位置
                            $this->spx -> setLimits( $offset, $limit );	//分页

                            //搜索数据(加上了增量索引的查找)
                            $page_data = $this->spx->query( $keyword, $index_name );

                            if(isset($page_data['matches']) && !empty($page_data['matches'])){
                                foreach($page_data['matches'] as $key => $val){
                                    $temp_arr[] = $val;
                                }
                            }
                        }

                        //过滤特定游戏下的用户
                        $result_rcm = $this->get_filter_user_list($game_id,$temp_arr,$param['pagenum'],$limit);
                    }

                    $return['total'] = isset($result_rcm['total']) ? intval($result_rcm['total']) : 0;

                    //最大页数
                    $return['pagecount'] = intval(ceil($return['total']/$param['pagesize']));

                    //用户数据
                    if(isset($result_rcm['matches']) && !empty($result_rcm['matches'])){
                        foreach($result_rcm['matches'] as $kkey => $vval){
                            if(isset($vval['attrs']) && !empty($vval['attrs'])){
                                $uv_count_key = 'uv_count_'.$vval['id'];
                                $video_count = $this->mem->get($uv_count_key);
                                if($video_count === false){
                                    $video_count = $this->get_video_count($vval['id'],$vval['attrs']['source'],$vval['attrs']['username']);
                                    $this->mem->set($uv_count_key,$video_count,3600);
                                }

                                $return['rows'][$kkey] = array(
                                    'uid' => intval($vval['id']), //用户id
                                    'authorname' => $vval['attrs']['nickname'], //用户昵称
                                    'gender' => intval($vval['attrs']['gender']), //用户性别（1：男 2：女）
                                    'desc' => $vval['attrs']['desc'], //用户描述
                                    'authorimg' => UC_API.'/avatar.php?uid='.intval($vval['id']).'&type=real&size=big',
                                    'videocount' => $video_count
                                );
                            }
                        }
                    }
                }

                $return_json = json_encode( $return );
                //将搜索结果存入缓存服务器
                if( $is_cache ){
                    $this->mem -> set($mem_result_key,$return_json,300);//设置为300秒过期
                }

                //记录搜索关键词
                if(isset($return['rows']) && !is_empty($return['rows'])){
                    $tmp_count_arr['ok'] = 1;//搜索 1成功
                }else{
                    $tmp_count_arr['ok'] = 2;//搜索 2失败
                }
                $tmp_count_arr['iscache'] = 2;//非缓存返回(1是缓存返回,2非缓存返回）
                $tmp_count_arr['source'] = 3; //搜索来源（1：游戏搜索 2：视频搜索）
                $this->search->save_count_word( $tmp_count_arr );
                unset($tmp_count_arr);

                //返回数据
                if(!empty($is_return)){
                    unset($return_json);
                    return $return;
                }else{
                    unset($return);
                    exit( $return_json );
                }
            }catch( Exception $e ){
                //记录搜索关键词
                $tmp_count_arr['ok'] = 2;//搜索 1成功/2失败
                $tmp_count_arr['iscache'] = 2;//非缓存返回(1是缓存返回,2非缓存返回）
                $tmp_count_arr['source'] = 3; //搜索来源（1：游戏搜索 2：视频搜索）
                $this->search->save_count_word( $tmp_count_arr );

                //返回数据
                if(!empty($is_return)){
                    return $return;
                }else{
                    exit( json_encode( $return ) );
                }
            }
        }

        /**
         * @name: get_video_count
         * @description: 	获取用户视频数
         * @param: uid int 用户id
         * @return:	array
         * @author: Chen Zhong
         * @create: 2014-11-11 11:18:50
         **/
        private function get_filter_user_list( $game_id = 0,$result = array(),$pagenum = 1,$pagesize=20){

            if(empty($game_id) || $game_id == 18){
                return $result;
            }

            $temp_arr = array();
            $return_temp_arr = array();
            if(isset($result) && !empty($result)){
                foreach($result as $key => $val){
                    $game_arr = (isset($val['attrs']['video_game']) && !empty($val['attrs']['video_game'])) ? explode(',',$val['attrs']['video_game']) : array();
                    if($game_id == 2){
                        if(in_array(2,$game_arr) || in_array(12,$game_arr)){
                            $temp_arr[] = $val;
                        }
                    }else{
                        if(in_array($game_id,$game_arr)){
                            $temp_arr[] = $val;
                        }
                    }
                }

                $return_temp_arr['total'] = count($temp_arr);
                if(!empty($temp_arr)){
                    $start = ($pagenum - 1) * $pagesize;
                    $end = $start + $pagesize;
                    for($i = $start;$i < $end; $i++ ){
                        if(isset($temp_arr[$i])){
                            $return_temp_arr['matches'][] = $temp_arr[$i];
                        }
                    }
                }
            }

            return $return_temp_arr;
        }

        /**
         * @name: get_video_count
         * @description: 	获取用户视频数
         * @param: uid int 用户id
         * @return:	array
         * @author: Chen Zhong
         * @create: 2014-11-11 11:18:50
         **/
        private function get_video_count( $uid = 0 ,$source = 2,$username = ''){

            if( empty( $uid ) ){
                return 0;
            }

            $temp_where = ' WHERE `vvl_uid` = '.$uid;
            if($source == 3 && strstr($username,'k_')){
                $temp_where .= " AND `va_isshow` = 1";
            }

            //查询搜索结果中拥有关联游戏推荐的数据
            $sql = "SELECT count(1) AS num FROM video_video_list ".$temp_where;
            $res = $this->_conn->count( $sql );

            return $res;
        }

        /**
         * @name: 	video_tag_retrieve
         * @description: 	sphinx搜索视频标签数据
         * @param:	string	$key		对称加密key
         * @param:	string	$keyword	搜索关键词
         * @param:	string	$index_name	索引名称
         * @param:	array	$weight		字段权重
         * @param:	array	$filter		设置过滤(values为整数数组)		array( 'attr' => 'g_id', 'values'=>array(1,3), 'exclude'=>false );
         * @param:	int		$offset		起始
         * @param:	int		$limit		条数
         * @param:	array	$sph_config	自定义主机和端口配置参数，注意ip和端口需要对称加密	如array( 'sphinx_ip' => 'host', 'sphinx_port' => port, 'sphinx_match_mode' => ..., 'sphinx_sort_model'=>.. )
         * @return:	array
         * @author: chengdongcai
         * @create: 2014-11-11 16:26:50
         **/
        public function video_tag_retrieve($param){

            //定义初始返回内容
            $return = array(
                'status' => -1, //状态码
                'total' => 0, //数据总条数
                'pagecount' => 0, //总页数
                'pagesize' => intval($param['pagesize']), //每页显示数据
                'pagenum' => intval($param['pagenum']), //当前页
                'rows' => array() //数据数组
            );

            $keyword 	= isset($param['keyword']) ? filter_search($param['keyword']) : '';		//关键字
            $index_name	= isset($param['index_name']) ? $param['index_name'] : 'mzw_video_tag';		//索引名称
            $game_id	= isset($param['game_id']) ? $param['game_id'] : 0;		//游戏id
            $filter	= isset($param['filter']) ? $param['filter'] : 0;		//是否过滤隐藏视频标签
            $is_cache	= !is_empty( $this->mem );	//是否可以通过缓存存取数据

            //无该索引或搜索关键字为空时
            if( !isset( $this->_weight[$index_name] ) || is_empty( $keyword ) || is_empty($game_id)){
                exit( json_encode( $return ) );
            }

            //其他参数
            $limit		= $param['pagesize'];//查询条数
            $offset		= isset($param['pagenum']) ? (($param['pagenum'] - 1 ) * $limit) : 0;//起始查询位置
            $weight		= (isset($param['weight']) && !empty($param['weight'])) ? $param['weight'] : $this->_weight[$index_name];//字段权重
            $sph_config =  isset($param['sph_config']) ? $param['sph_config'] : null;	//自定义配置

            //初始化配置
            $this->load_sph_config( $sph_config );
            if( is_empty( $this->_sphinx_ip ) || is_empty( $this->_sphinx_port ) ){
                exit( json_encode( $return ) );
            }

            //搜索词计数,更新热词搜索次数
            $tmp_count_arr = array(
                'keyword'=>$keyword,//关键词
                'ok'=>2,//是否搜索成功（1是搜索成功，2搜索失败）
                'iscache'=>1,//是否缓存返回(1是缓存返回,2非缓存返回）
                'source'=>4 //搜索来源（1：游戏搜索 2：视频搜索 3：用户搜索 4：标签搜索）
            );
            $mem_count_key = $this->_search_tag_count_key.'_'.$keyword;
            $new_count = $this->search->count_word( $mem_count_key, $keyword,4,2 );

            //如果可以查缓存，则
            if($is_cache){
                //先查缓存服务器
                $mem_result_key = $this->_search_tag_result_key.'_'.md5($keyword.$index_name.$game_id.$filter.$param['pagenum'].$param['pagesize']);
                $tmp_info = $this->mem -> get($mem_result_key);
                if($tmp_info!==FALSE){//如果有找到，则直接返回缓存的内容
                    $tmp_info_arr = json_decode($tmp_info,true);

                    //判断是否有搜索到数据
                    if(isset($tmp_info_arr['rows']) && !is_empty($tmp_info_arr['rows'])){
                        $tmp_count_arr['ok'] = 1;//搜索 1成功
                    }else{
                        $tmp_count_arr['ok'] = 2;//搜索 2失败
                    }
                    $tmp_count_arr['iscache'] = 1;//缓存返回
                    $tmp_count_arr['source'] = 4;//搜索来源（1：游戏搜索 2：视频搜索 3：用户搜索 4：标签搜索）
                    $this->search->save_count_word( $tmp_count_arr );
                    unset($tmp_info_arr,$tmp_count_arr);

                    //返回数据
                    exit( $tmp_info );
                }
            }
            try{
                $this->spx -> setServer( $this->_sphinx_ip, $this->_sphinx_port );					//主机和端口
                $this->spx -> setArrayResult( TRUE );												//控制搜索结果集的返回格式为数组
                $this->spx -> setFieldWeights( $weight );											//设置权重
                $this->spx -> setLimits( $offset, $limit );											//分页
                $this->spx -> setMatchMode( $this->_sphinx_match_mode );							//匹配模式

                //游戏过滤模式匹配
                if($game_id == 2){ //我的世界匹配我的世界跟故事模式的视频
                    $this->spx -> setFilter( 'vtc_game_id',array(2,12) );
                }elseif($game_id > 0 && $game_id <> 18){
                    $this->spx -> setFilter( 'vtc_game_id',array($game_id) );
                }

                //显示属性过滤模式匹配
                if(!empty($filter)){
                    $this->spx -> setFilter( 'vtc_status',array($filter) );
                }

                /*  排序模式
                 *  有如下可选的匹配模式：
                    SPH_MATCH_ALL, 匹配所有查询词(默认模式);
                    SPH_MATCH_ANY, 匹配查询词中的任意一个;
                    SPH_MATCH_PHRASE, 将整个查询看作一个词组，要求按顺序完整匹配;
                    SPH_MATCH_BOOLEAN, 将查询看作一个布尔表达式 (参见 Section 4.2, “布尔查询语法”);
                    SPH_MATCH_EXTENDED, 将查询看作一个Sphinx/Coreseek内部查询语言的表达式 。
                    SPH_MATCH_EXTENDED2, 使用第二版的“扩展匹配模式”对查询进行匹配.
                    SPH_MATCH_FULLSCAN, 强制使用下文所述的“完整扫描”模式来对查询进行匹配。
                 */

                //根据权重排序
                $sphinx_sort_expr = '@relevance DESC'; //先按权重排序（具体的排序规则根据需求变动）
                $this->spx->setSortMode( $this->_sphinx_sort_mode, $sphinx_sort_expr );

                //搜索数据(加上了增量索引的查找)
                $result_rcm = $this->spx->query( $keyword, $index_name );

                $return["status"] = 200;
                if(!empty($result_rcm)){
                    $return['total'] = isset($result_rcm['total']) ? intval($result_rcm['total']) : 0;

                    //最大页数
                    $return['pagecount'] = ceil($return['total']/$param['pagesize']);

                    //视频数据
                    if(isset($result_rcm['matches']) && !empty($result_rcm['matches'])){
                        foreach($result_rcm['matches'] as $key => $val){
                            if(isset($val['attrs']) && !empty($val['attrs'])){

                                //频道分类字符串拼接
                                switch($val['attrs']['vtc_type']){
                                    case 1:
                                        $title = '频道：'.$val['attrs']['vtc_name'];
                                        $id_key = 'channelid';
                                        break;
                                    case 2:
                                        $title = '分类：'.$val['attrs']['vtc_name'];
                                        $id_key = 'categoryid';
                                        break;
                                    default:
                                        $title = '标签：'.$val['attrs']['vtc_name'];
                                        $id_key = 'tagid';
                                        break;
                                }

                                $return['rows'][$key] = array(
                                    'channelid' => ($id_key == 'channelid') ? intval($val['id']) : 0, //频道id
                                    'categoryid' => ($id_key == 'categoryid') ? intval($val['id']) : 0, //分类id
                                    'tagid' => ($id_key == 'tagid') ? intval($val['id']) : 0, //标签id
                                    'title' => $title, //频道视频标签标题
                                    'gameid' => intval($val['attrs']['vtc_game_id']) //游戏id
                                );
                            }
                        }
                    }
                }

                $return_json = json_encode( $return );
                //将搜索结果存入缓存服务器
                if( $is_cache ){
                    $this->mem -> set($mem_result_key,$return_json,300);//设置为300秒过期
                }

                //记录搜索关键词
                if(isset($return['rows']) && !is_empty($return['rows'])){
                    $tmp_count_arr['ok'] = 1;//搜索 1成功
                }else{
                    $tmp_count_arr['ok'] = 2;//搜索 2失败
                }
                $tmp_count_arr['iscache'] = 2;//非缓存返回(1是缓存返回,2非缓存返回）
                $tmp_count_arr['source'] = 4; //搜索来源（1：游戏搜索 2：视频搜索 3：用户搜索 4：标签搜索）
                $this->search->save_count_word( $tmp_count_arr );
                unset($return,$tmp_count_arr);

                //返回数据
                exit( $return_json );
            }catch( Exception $e ){
                //记录搜索关键词
                $tmp_count_arr['ok'] = 2;//搜索 1成功/2失败
                $tmp_count_arr['iscache'] = 2;//非缓存返回(1是缓存返回,2非缓存返回）
                $tmp_count_arr['source'] = 4; //搜索来源（1：游戏搜索 2：视频搜索 3：用户搜索 4：标签搜索）
                $this->search->save_count_word( $tmp_count_arr );

                //返回数据
                exit( json_encode( $return ) );
            }
        }

        /**
         * @name: 	video_game_retrieve
         * @description: 	sphinx搜索视频游戏数据
         * @param:	string	$key		对称加密key
         * @param:	string	$keyword	搜索关键词
         * @param:	string	$index_name	索引名称
         * @param:	array	$weight		字段权重
         * @param:	array	$filter		设置过滤(values为整数数组)		array( 'attr' => 'g_id', 'values'=>array(1,3), 'exclude'=>false );
         * @param:	int		$offset		起始
         * @param:	int		$limit		条数
         * @param:	array	$sph_config	自定义主机和端口配置参数，注意ip和端口需要对称加密	如array( 'sphinx_ip' => 'host', 'sphinx_port' => port, 'sphinx_match_mode' => ..., 'sphinx_sort_model'=>.. )
         * @return:	array
         * @author: chengdongcai
         * @create: 2014-11-11 16:26:50
         **/
        public function video_game_retrieve($param){

            //定义初始返回内容
            $return = array(
                'status' => -1, //状态码
                'total' => 0, //数据总条数
                'pagecount' => 0, //总页数
                'pagesize' => intval($param['pagesize']), //每页显示数据
                'pagenum' => intval($param['pagenum']), //当前页
                'rows' => array() //数据数组
            );

            $keyword 	= isset($param['keyword']) ? filter_search($param['keyword']) : '';		//关键字
            $index_name	= isset($param['index_name']) ? $param['index_name'] : 'mzw_video_game';		//索引名称
            $game_id	= isset($param['game_id']) ? $param['game_id'] : 0;		//游戏id
            $is_return	= isset($param['is_return']) ? intval($param['is_return']) : 0;		//是否返回数据（0：不返回 1:返回）
            $filter	= isset($param['filter']) ? $param['filter'] : 0;		//是否过滤隐藏视频
            $is_cache	= !is_empty( $this->mem );	//是否可以通过缓存存取数据

            //无该索引或搜索关键字为空时
            if( !isset( $this->_weight[$index_name] ) || is_empty( $keyword ) || is_empty($game_id)){
                exit( json_encode( $return ) );
            }

            //其他参数
            $limit		= $param['pagesize'];//查询条数
            $offset		= isset($param['pagenum']) ? (($param['pagenum'] - 1 ) * $limit) : 0;//起始查询位置
            $weight		= (isset($param['weight']) && !empty($param['weight'])) ? $param['weight'] : $this->_weight[$index_name];//字段权重
            $sph_config =  isset($param['sph_config']) ? $param['sph_config'] : null;	//自定义配置

            //初始化配置
            $this->load_sph_config( $sph_config );
            if( is_empty( $this->_sphinx_ip ) || is_empty( $this->_sphinx_port ) ){
                exit( json_encode( $return ) );
            }

            //搜索词计数,更新热词搜索次数
            $tmp_count_arr = array(
                'keyword'=>$keyword,//关键词
                'ok'=>2,//是否搜索成功（1是搜索成功，2搜索失败）
                'iscache'=>1,//是否缓存返回(1是缓存返回,2非缓存返回）
                'source'=>5 //搜索来源（1：游戏搜索 2：视频搜索 3：用户搜索 4：标签搜索 5:视频游戏搜索）
            );
            $mem_count_key = $this->_search_video_game_count_key.'_'.$keyword;
            $new_count = $this->search->count_word( $mem_count_key, $keyword,5,2 );

            //如果可以查缓存，则
            if($is_cache){
                //先查缓存服务器
                $mem_result_key = $this->_search_video_game_result_key.'_'.md5($keyword.$index_name.$game_id.$filter.$param['pagenum'].$param['pagesize']);
                $tmp_info = $this->mem -> get($mem_result_key);
                if($tmp_info!==FALSE){//如果有找到，则直接返回缓存的内容
                    $tmp_info_arr = json_decode($tmp_info,true);

                    //判断是否有搜索到数据
                    if(isset($tmp_info_arr['rows']) && !is_empty($tmp_info_arr['rows'])){
                        $tmp_count_arr['ok'] = 1;//搜索 1成功
                    }else{
                        $tmp_count_arr['ok'] = 2;//搜索 2失败
                    }
                    $tmp_count_arr['iscache'] = 1;//缓存返回
                    $tmp_count_arr['source'] = 5;//搜索来源（1：游戏搜索 2：视频搜索 3：用户搜索 4：标签搜索 5:视频游戏搜索）
                    $this->search->save_count_word( $tmp_count_arr );
                    unset($tmp_count_arr);

                    //返回数据
                    if(!empty($is_return)){
                        unset($tmp_info);
                        return $tmp_info_arr;
                    }else{
                        unset($tmp_info_arr);
                        exit( $tmp_info );
                    }
                }
            }
            try{
                $this->spx -> setServer( $this->_sphinx_ip, $this->_sphinx_port );					//主机和端口
                $this->spx -> setArrayResult( TRUE );												//控制搜索结果集的返回格式为数组
                $this->spx -> setFieldWeights( $weight );											//设置权重
                $this->spx -> setLimits( $offset, $limit );											//分页
                $this->spx -> setMatchMode( $this->_sphinx_match_mode );							//匹配模式

                //显示属性过滤模式匹配
                if(!empty($filter)){
                    $this->spx -> setFilter( 'gi_isshow',array($filter) );
                }

                $this->spx->SetFilterRange('gi_video_num',1,10000000);

                /*  排序模式
                 *  有如下可选的匹配模式：
                    SPH_MATCH_ALL, 匹配所有查询词(默认模式);
                    SPH_MATCH_ANY, 匹配查询词中的任意一个;
                    SPH_MATCH_PHRASE, 将整个查询看作一个词组，要求按顺序完整匹配;
                    SPH_MATCH_BOOLEAN, 将查询看作一个布尔表达式 (参见 Section 4.2, “布尔查询语法”);
                    SPH_MATCH_EXTENDED, 将查询看作一个Sphinx/Coreseek内部查询语言的表达式 。
                    SPH_MATCH_EXTENDED2, 使用第二版的“扩展匹配模式”对查询进行匹配.
                    SPH_MATCH_FULLSCAN, 强制使用下文所述的“完整扫描”模式来对查询进行匹配。
                 */

                //根据视频播放数排序
                $sphinx_sort_expr = '@relevance DESC,gi_video_num DESC'; //先按权重排序（具体的排序规则根据需求变动）
                $this->spx->setSortMode( $this->_sphinx_sort_mode, $sphinx_sort_expr );

                //搜索数据(加上了增量索引的查找)
                $result_rcm = $this->spx->query( $keyword, $index_name );

                $return["status"] = 200;
                if(!empty($result_rcm)){
                    $return['total'] = isset($result_rcm['total']) ? intval($result_rcm['total']) : 0;

                    //最大页数
                    $return['pagecount'] = intval(ceil($return['total']/$param['pagesize']));

                    //视频数据
                    if(isset($result_rcm['matches']) && !empty($result_rcm['matches'])){
                        foreach($result_rcm['matches'] as $key => $val){
                            if(isset($val['attrs']) && !empty($val['attrs'])){
                                $gameid = intval($val['id']);

                                //获取视频游戏下视频缓存数
                                $vgvn_key = 'video_game_video_num_'.$gameid; //视频游戏视频缓存数key
                                $video_num = $this->mem->get($vgvn_key); //获取视频游戏视频数

                                if($video_num === false){
                                    $video_num = $this->get_video_game_video_count($gameid);
                                    $this->mem -> set($vgvn_key,$video_num,7200);//设置2小时过期
                                }

                                $return['rows'][$key] = array(
                                    'id' => $gameid, //关联id
                                    'title' => $val['attrs']['gi_name'], //关联视频游戏标题
                                    'imgurl' => empty($val['attrs']['gi_logo']) ? '' : (LOCAL_URL_DOWN_IMG.$val['attrs']['gi_logo']), //视频游戏图片
                                    'type' => 1, //关联类型（1：游戏 2：游戏分类）
                                    'videonum' => $video_num, //视频游戏关联视频总数
                                    'desc' => filter_search(delete_html($val['attrs']['gi_intro'])) //视频游戏描述
                                );
                            }
                        }
                    }
                }

                $return_json = json_encode( $return );
                //将搜索结果存入缓存服务器
                if( $is_cache ){
                    $this->mem -> set($mem_result_key,$return_json,300);//设置为300秒过期
                }

                //记录搜索关键词
                if(isset($return['rows']) && !is_empty($return['rows'])){
                    $tmp_count_arr['ok'] = 1;//搜索 1成功
                }else{
                    $tmp_count_arr['ok'] = 2;//搜索 2失败
                }
                $tmp_count_arr['iscache'] = 2;//非缓存返回(1是缓存返回,2非缓存返回）
                $tmp_count_arr['source'] = 5; //搜索来源（1：游戏搜索 2：视频搜索 3：用户搜索 4：标签搜索 5：视频游戏搜索）
                $this->search->save_count_word( $tmp_count_arr );
                unset($tmp_count_arr);

                //返回数据
                if(!empty($is_return)){
                    unset($return_json);
                    return $return;
                }else{
                    unset($return);
                    exit( $return_json );
                }
            }catch( Exception $e ){
                //记录搜索关键词
                $tmp_count_arr['ok'] = 2;//搜索 1成功/2失败
                $tmp_count_arr['iscache'] = 2;//非缓存返回(1是缓存返回,2非缓存返回）
                $tmp_count_arr['source'] = 5; //搜索来源（1：游戏搜索 2：视频搜索 3：用户搜索 4：标签搜索 5：视频游戏搜索）
                $this->search->save_count_word( $tmp_count_arr );

                //返回数据
                if(!empty($is_return)){
                    return $return;
                }else{
                    exit( json_encode( $return ) );
                }
            }
        }

        /**
         * @name: get_video_game_video_count
         * @description: 	获取视频游戏视频数
         * @param: gameid int 视频游戏id
         * @return:	array
         * @author: Chen Zhong
         * @create: 2016-02-16 18:27:50
         **/
        private function get_video_game_video_count( $gameid = 0){

            if( empty( $gameid ) ){
                return 0;
            }

            //查询视频游戏关联视频数
            $sql = "SELECT count(1) AS num FROM video_video_list WHERE `va_isshow` = 1 AND `vvl_game_id` = ".$gameid;
            $res = $this->_conn->count( $sql );

            return $res;
        }


        /**
         * @name: 	hot_user_retrieve
         * @description: 	sphinx搜索用户数据（视频数最多的4个）
         * @param:	string	$key		对称加密key
         * @param:	string	$keyword	搜索关键词
         * @param:	string	$index_name	索引名称
         * @param:	array	$weight		字段权重
         * @param:	array	$filter		设置过滤(values为整数数组)		array( 'attr' => 'g_id', 'values'=>array(1,3), 'exclude'=>false );
         * @param:	int		$offset		起始
         * @param:	int		$limit		条数
         * @param:	array	$sph_config	自定义主机和端口配置参数，注意ip和端口需要对称加密	如array( 'sphinx_ip' => 'host', 'sphinx_port' => port, 'sphinx_match_mode' => ..., 'sphinx_sort_model'=>.. )
         * @return:	array
         * @author: chengdongcai
         * @create: 2014-11-11 16:26:50
         **/
        public function hot_user_retrieve($param){

            //定义初始返回内容
            $return = array();

            $keyword 	= isset($param['keyword']) ? filter_search($param['keyword']) : '';		//关键字
            $index_name	= isset($param['index_name']) ? $param['index_name'] : 'mzw_user';		//索引名称
            $game_id	= isset($param['game_id']) ? $param['game_id'] : 0;		//游戏id
            $is_cache	= !is_empty( $this->mem );	//是否可以通过缓存存取数据

            //无该索引或搜索关键字为空时
            if( !isset( $this->_weight[$index_name] ) || is_empty( $keyword )){
                return $return;
            }

            //其他参数
            $limit		= 4;//查询条数
            $offset		= 0;//起始查询位置
            $weight		= (isset($param['weight']) && !empty($param['weight'])) ? $param['weight'] : $this->_weight[$index_name];//字段权重
            $sph_config =  isset($param['sph_config']) ? $param['sph_config'] : null;	//自定义配置

            //初始化配置
            $this->load_sph_config( $sph_config );
            if( is_empty( $this->_sphinx_ip ) || is_empty( $this->_sphinx_port ) ){
                return $return;
            }

            //搜索词计数,更新热词搜索次数
            $tmp_count_arr = array(
                'keyword'=>$keyword,//关键词
                'ok'=>2,//是否搜索成功（1是搜索成功，2搜索失败）
                'iscache'=>1,//是否缓存返回(1是缓存返回,2非缓存返回）
                'source'=>3 //搜索来源（1：游戏搜索 2：视频搜索 3:用户搜索）
            );
            $mem_count_key = $this->_search_hot_user_count_key.'_'.$keyword;
            $new_count = $this->search->count_word( $mem_count_key, $keyword,3,2 );

            //如果可以查缓存，则
            if($is_cache){
                //先查缓存服务器
                $mem_result_key = $this->_search_hot_user_result_key.'_'.md5($keyword.$game_id.$index_name);
                $tmp_info = $this->mem -> get($mem_result_key);

                if($tmp_info!==false){
                    return $tmp_info;
                }
            }

            try{
                $this->spx -> setServer( $this->_sphinx_ip, $this->_sphinx_port );					//主机和端口
                $this->spx -> setArrayResult( TRUE );												//控制搜索结果集的返回格式为数组
                $this->spx -> setFieldWeights( $weight );											//设置权重
                $this->spx -> setLimits( $offset, $limit );											//分页
                $this->spx -> setMatchMode( $this->_sphinx_match_mode );							//匹配模式

                /*  排序模式
                 *  有如下可选的匹配模式：
                    SPH_MATCH_ALL, 匹配所有查询词(默认模式);
                    SPH_MATCH_ANY, 匹配查询词中的任意一个;
                    SPH_MATCH_PHRASE, 将整个查询看作一个词组，要求按顺序完整匹配;
                    SPH_MATCH_BOOLEAN, 将查询看作一个布尔表达式 (参见 Section 4.2, “布尔查询语法”);
                    SPH_MATCH_EXTENDED, 将查询看作一个Sphinx/Coreseek内部查询语言的表达式 。
                    SPH_MATCH_EXTENDED2, 使用第二版的“扩展匹配模式”对查询进行匹配.
                    SPH_MATCH_FULLSCAN, 强制使用下文所述的“完整扫描”模式来对查询进行匹配。
                 */

                //用户视频数过滤模式匹配
                $this->spx->SetFilterRange('video_num',1,10000000);

                //根据视频播放数排序
                $sphinx_sort_expr = '@relevance DESC,video_num DESC'; //先按权重排序（具体的排序规则根据需求变动）
                $this->spx->setSortMode( $this->_sphinx_sort_mode, $sphinx_sort_expr );

                //搜索数据(加上了增量索引的查找)
                $result_rcm = $this->spx->query( $keyword, $index_name );
                $return = array();
                if(isset($result_rcm['matches']) && !empty($result_rcm['matches'])){
                    foreach($result_rcm['matches'] as $kkey => $vval){
                        if(isset($vval['attrs']) && !empty($vval['attrs'])){
                            $return[] = array(
                                'uid' => intval($vval['id']), //用户id
                                'authorname' => $vval['attrs']['nickname'], //用户昵称
                                'gender' => intval($vval['attrs']['gender']), //用户性别（1：男 2：女）
                                'desc' => $vval['attrs']['desc'], //用户描述
                                'authorimg' => UC_API.'/avatar.php?uid='.intval($vval['id']).'&type=real&size=big',
                                'videocount' => intval($vval['video_num'])
                            );
                        }
                    }
                }

                //将搜索结果存入缓存服务器
                if( $is_cache ){
                    $this->mem -> set($mem_result_key,$return,1800);//设置为300秒过期
                }

                //记录搜索关键词
                if(isset($return) && !is_empty($return)){
                    $tmp_count_arr['ok'] = 1;//搜索 1成功
                }else{
                    $tmp_count_arr['ok'] = 2;//搜索 2失败
                }
                $tmp_count_arr['iscache'] = 2;//非缓存返回(1是缓存返回,2非缓存返回）
                $tmp_count_arr['source'] = 3; //搜索来源（1：游戏搜索 2：视频搜索）
                $this->search->save_count_word( $tmp_count_arr );
                unset($tmp_count_arr);

                //返回数据
                return $return;
            }catch( Exception $e ){
                //记录搜索关键词
                $tmp_count_arr['ok'] = 2;//搜索 1成功/2失败
                $tmp_count_arr['iscache'] = 2;//非缓存返回(1是缓存返回,2非缓存返回）
                $tmp_count_arr['source'] = 3; //搜索来源（1：游戏搜索 2：视频搜索）
                $this->search->save_count_word( $tmp_count_arr );

                //返回数据
                return $return;
            }
        }

	}