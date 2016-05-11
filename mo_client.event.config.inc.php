<?php
//公共字段
$arr_public_fields = array(
        'in_date'=>array('type'=>'int','len'=>11,'comment'=>'服务器端的记录日期(Ymd)'),  
        'intime'=>array('type'=>'int','len'=>11,'comment'=>'服务器接收时间戳'),
        'rooted'=>array('type'=>'int','len'=>3,'comment'=>'是否有root'),
        'width'=>array('type'=>'int','len'=>10,'comment'=>'屏幕宽'),
        'height'=>array('type'=>'int','len'=>100,'comment'=>'屏幕高'),
        'model'=>array('type'=>'varchar','len'=>100,'comment'=>'型号名称'),
        'brand'=>array('type'=>'varchar','len'=>100,'comment'=>'品牌名称'),
        'density'=>array('type'=>'int','len'=>10,'comment'=>'密度'),
        'gpu'=>array('type'=>'varchar','len'=>100,'comment'=>'GPU信息'),
        'systemversion'=>array('type'=>'int','len'=>10,'comment'=>'系统版本'),
        'softwareversion'=>array('type'=>'int','len'=>10,'comment'=>'软件版本(自身)'),
        'cpu'=>array('type'=>'varchar','len'=>50,'comment'=>'CPU信息'),
        'firmwire'=>array('type'=>'varchar','len'=>100,'comment'=>'固件信息'),
        'mac'=>array('type'=>'varchar','len'=>100,'comment'=>'MAC地址'),
        'time'=>array('type'=>'bigint','len'=>20,'comment'=>'客户端时间戳(毫秒级别)'),
        'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
        'number'=>array('type'=>'int','len'=>10,'comment'=>'统计数据类型id'),
        'timeStr'=>array('type'=>'varchar','len'=>100,'comment'=>'客户端格式化时间'),
        'memorysize'=>array('type'=>'bigint','len'=>10,'comment'=>'内存大小'),
        'insdcardsize'=>array('type'=>'bigint','len'=>10,'comment'=>'存储空间'),
        'issupportexsdcard'=>array('type'=>'int','len'=>1,'comment'=>'是否支持存储卡,值：1，0'),
);

//模块配置
$arr_event_config = array(
    /**
     * @name:mo_install
     * @description: 安装模块
     * 安装模块（number：70001）
	      事件id（eventid）：
		1、1701：安装成功
		2、1702：安装失败
		3、1703：点击安装

     * @author: Xiong Jianbang
     * @create: 2015-9-6 上午10:28:12
     **/
	'mo_install' => array(
	        'dir_name'     => 'mo_install', //文件目录名称
	        'import_table_name' => 'kyx_mo_install_log', //创建的导入数据表名称
	        'statistics_table_name' => 'kyx_mo_install', //创建的统计数据表名称
	        'event_name' => '手机客户端安装模块',//事件描述
	        'event_id' => array(1701,1702,1703),//事件ID号
	        'log_files_count' => 10,  //随机读取写入的日志文件数量
	        'import_prefix' => 'mi_',  //字段的前缀
	        'import_fields' => array(//表字段定义
	                'packagename'=>array('type'=>'varchar','len'=>200,'comment'=>'app包名'),
	                'versionname'=>array('type'=>'varchar','len'=>100,'comment'=>'app版本名称'),
	                'versioncode'=>array('type'=>'int','len'=>10,'comment'=>'版本号'),
	                'code'=>array('type'=>'int','len'=>3,'comment'=>'错误码'),
	                'title'=>array('type'=>'varchar','len'=>200,'comment'=>'标题'),
	                'path'=>array('type'=>'varchar','len'=>255,'comment'=>'路径'),
	        ),
	        'statistics_fields' => array(//统计表字段定义
	                'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
	                'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
	                'title'=>array('type'=>'varchar','len'=>100,'comment'=>'游戏名称'),//游戏名称
	                'packagename'=>array('type'=>'varchar','len'=>100,'comment'=>'包名'),//包名
	                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),//游戏名称
	        ),
    ),
    /**
     * @name:mo_uninstall
     * @description: 卸载模块
     * 卸载（number：70003）
	     事件id（eventid）：
		1、1801： 卸载
     * @author: Xiong Jianbang
     * @create: 2015-9-6 上午10:41:54
     **/
    'mo_uninstall' => array(
            'dir_name'     => 'mo_uninstall', //文件目录名称
            'import_table_name' => 'kyx_mo_uninstall_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_mo_uninstall', //创建的统计数据表名称
            'event_name' => '卸载模块',//事件描述
            'event_id' => array(1801),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'mu_',
            'import_fields' => array(//表字段定义
                    'packagename'=>array('type'=>'varchar','len'=>200,'comment'=>'app包名'),
                    'versionname'=>array('type'=>'varchar','len'=>100,'comment'=>'app版本名称'),
                    'versioncode'=>array('type'=>'int','len'=>10,'comment'=>'版本号'),
                    'title'=>array('type'=>'varchar','len'=>200,'comment'=>'标题'),
                    'installDate'=>array('type'=>'bigint','len'=>10,'comment'=>'安装日期'),
            ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
	                'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
	                'title'=>array('type'=>'varchar','len'=>100,'comment'=>'游戏名称'),//游戏名称
	                'packagename'=>array('type'=>'varchar','len'=>100,'comment'=>'包名'),//包名
	                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),//游戏名称
            ),
    ),
    
    
    /**
     * @name:mo_home_page
     * @description: 首页页面
     * 首页页面（number：70004）
         事件id（eventid）：
		1、1101：首页访问量
		2、1102：首页广告
		3、1103：二维码访问量

     * @author: Xiong Jianbang
     * @create: 2015-9-6 上午10:52:00
     **/
    'mo_home_page' => array(
            'dir_name'     => 'mo_home_page', //文件目录名称
            'import_table_name' => 'kyx_mo_home_page_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_mo_home_page', //创建的统计数据表名称
            'event_name' => '首页页面',//事件描述
            'event_id' => array(1101,1102,1103),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'mhp_',
            'import_fields' => array(//表字段定义
                    'appid'=>array('type'=>'int','len'=>10,'comment'=>'游戏唯一id'),
                    'type'=>array('type'=>'int','len'=>2,'comment'=>'首页类型id,1详情，2专题'),
                    'title'=>array('type'=>'varchar','len'=>200,'comment'=>'标题'),
            ),
            'statistics_fields' => array(//统计表字段定义
                'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
                'type'=>array('type'=>'int','len'=>2,'comment'=>'首页类型id,1详情，2专题'),
                'title'=>array('type'=>'varchar','len'=>200,'comment'=>'标题'),
                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),//数量
            ),
    ),
    /**
     * @name:mo_category_page
     * @description: 分类页面
     *  分类页面（number：70005）
          事件id（eventid）：
		1、1201：分类访问量
		2、1202：具体分类点击
		3、1203：分类更新排序
		4、1204：分类下载排序
		4、1205：二级分类点击

     * @author: Xiong Jianbang
     * @create: 2015-9-6 下午12:07:39
     **/
    'mo_category_page' => array(
            'dir_name'     => 'mo_category_page', //文件目录名称
            'import_table_name' => 'kyx_mo_category_page_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_mo_category_page', //创建的统计数据表名称
            'event_name' => '分类页面',//事件描述
            'event_id' => array(1201,1202,1203,1204,1205),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'mcp_',
            'import_fields' => array(//表字段定义
                    'categoryId'=>array('type'=>'int','len'=>10,'comment'=>'游戏分类id'),
                    'categoryName'=>array('type'=>'varchar','len'=>20,'comment'=>'分类名称'),
            ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                    'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
                    'cateogryid'=>array('type'=>'int','len'=>10,'comment'=>'游戏分类id'),
                    'categoryname'=>array('type'=>'varchar','len'=>20,'comment'=>'游戏分类名称'),
                    'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),//数量
            ),
    ),
    /**
     * @name:mo_detail_page
     * @description: 详情页面
     * 详情页面（number：70006）
          事件id（eventid）：
		1、2001：详情页面访问量
     * @author: Xiong Jianbang
     * @create: 2015-9-6 下午12:11:00
     **/
    'mo_detail_page' => array(
            'dir_name'     => 'mo_detail_page', //文件目录名称
            'import_table_name' => 'kyx_mo_detail_page_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_mo_detail_page', //创建的统计数据表名称
            'event_name' => '详情页面',//事件描述
            'event_id' => array(2001),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'mdp_',
            'import_fields' => array(//表字段定义
                    'packagename'=>array('type'=>'varchar','len'=>200,'comment'=>'app包名'),
                    'versionname'=>array('type'=>'varchar','len'=>100,'comment'=>'app版本名称'),
                    'versioncode'=>array('type'=>'int','len'=>10,'comment'=>'版本号'),
                    'title'=>array('type'=>'varchar','len'=>200,'comment'=>'标题'),
                    'appid'=>array('type'=>'int','len'=>5,'comment'=>'游戏id'),
            ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                    'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
                    'title'=>array('type'=>'varchar','len'=>100,'comment'=>'游戏名称'),//游戏名称
                    'packagename'=>array('type'=>'varchar','len'=>100,'comment'=>'包名'),//包名
                    'appid'=>array('type'=>'int','len'=>5,'comment'=>'游戏id'),
                    'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
            ),
    ),
    /**
     * @name:mo_set_page
     * @description: 设置页面
     * 设置页面（number：70011）
         事件id（eventid）：
		1、1901：下载完成自动安装
		2、1902：自动清除安装包
		3、1903：自动下载未完成的任务
		4、1904：检查更新
		5、1905：清楚缓存
		6、1906：反馈
		7、1907：默认下载方式
		8、1908：百度下载方式
		9、1909：保存路径
		10、1911：仅wifi下载
     * @author: Xiong Jianbang
     * @create: 2015-9-6 下午12:13:39
     **/
    'mo_set_page' => array(
            'dir_name'     => 'mo_set_page', //文件目录名称
            'import_table_name' => 'kyx_mo_set_page_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_mo_set_page', //创建的统计数据表名称
            'event_name' => '设置页面',//事件描述
            'event_id' => array(1901,1902,1903,1904,1905,1906,1907,1908,1909,1911),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'msp_',
            'import_fields' => array(//表字段定义
                    'status'=>array('type'=>'int','len'=>1,'comment'=>'开关状态'),
                    'path'=>array('type'=>'varchar','len'=>255,'comment'=>'路径'),
            ),
             'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
	                'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
	                'status'=>array('type'=>'int','len'=>1,'comment'=>'开关状态'),
	                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
            ),
    ),
    /**
     * @name:mo_search
     * @description: 搜索模块（number：70009）
          事件id（eventid）：
		1、1501：搜索访问量
		2、1502：搜索热词
     * @author: Xiong Jianbang
     * @create: 2015-9-6 上午10:06:57
     **/
    'mo_search' => array(
            'dir_name'     => 'mo_search', //文件目录名称
            'import_table_name' => 'kyx_mo_search_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_mo_search', //创建的统计数据表名称
            'event_name' => '搜索模块',//事件描述
            'event_id' => array(1501,1502),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'ms_',
            'import_fields' => array(//表字段定义
                         'hotWords'=>array('type'=>'varchar','len'=>30,'comment'=>'热词名称'),
            ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                    'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
                    'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
                    'hotwords'=>array('type'=>'varchar','len'=>30,'comment'=>'热词名称'),
            ),
    ),
    /**
     * @name:mo_sort_rank
     * @description: 排行模块（number：70007）
         事件id（eventid）：
		1、1301：排行访问量
		2、1302：周排行
		3、1303：月排行
		4、1304：总排行
     * @author: Xiong Jianbang
     * @create: 2015-9-6 上午10:06:57
     **/
    'mo_sort_rank' => array(
            'dir_name'     => 'mo_sort_rank', //文件目录名称
            'import_table_name' => 'kyx_mo_sort_rank_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_mo_sort_rank', //创建的统计数据表名称
            'event_name' => '排行模块',//事件描述
            'event_id' => array(1301,1302,1303,1304),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'msr_',
            'import_fields' => array(//表字段定义
            ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                    'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
                    'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
            ),
    ),
    /**
     * @name:mo_manage_access
     * @description: 管理模块（number：70008）
            事件id（eventid）：
		 1、1401：管理访问量
     * @author: Xiong Jianbang
     * @create: 2015-9-6 上午10:06:57
     **/
    'mo_manage_access' => array(
            'dir_name'     => 'mo_manage_access', //文件目录名称
            'import_table_name' => 'kyx_mo_manage_access_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_mo_manage_access', //创建的统计数据表名称
            'event_name' => '管理访问量模块',//事件描述
            'event_id' => array(1401),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'mma_',
            'import_fields' => array(//表字段定义
            ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                    'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
                    'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
            ),
    ),
    
    /**
     * @name:mo_download
     * @description: 下载模块（number：70002）
         事件id（eventid）：
		1、1601：下载成功
		2、1602：下载失败
		3、1603：点击下载
		4、1604：第一次百度下载
		5、1605：第一次默认下载
		6、1606：下载暂停
		7、1607：下载取消
		8、1608：下载完成
		9、1609：下载继续
		10、1610：重新下载
		11、1611：服务器返回第一个数据
		12、1612：跳转
		13、1613：用户点击继续
		14、1614：下载管理访问量
     * @author: Xiong Jianbang
     * @create: 2015-9-6 下午12:16:58
     **/
    'mo_download' => array(
            'dir_name'     => 'mo_download', //文件目录名称
            'import_table_name' => 'kyx_mo_download_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_mo_download', //创建的统计数据表名称
            'event_name' => '下载模块',//事件描述
            'event_id' => array(1601,1602,1603,1604,1605,1606,1607,1608,1609,1610,1611,1612,1613,1614),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'md_',
            'import_fields' => array(//表字段定义
                    'packagename'=>array('type'=>'varchar','len'=>200,'comment'=>'app包名'),
                    'versionname'=>array('type'=>'varchar','len'=>100,'comment'=>'app版本名称'),
                    'versioncode'=>array('type'=>'int','len'=>10,'comment'=>'版本号'),
                    'code'=>array('type'=>'int','len'=>3,'comment'=>'错误码'),
                    'title'=>array('type'=>'varchar','len'=>200,'comment'=>'标题'),
                    'logSessionId'=>array('type'=>'varchar','len'=>200,'comment'=>'下载会话id'),
                    'downloadpath'=>array('type'=>'varchar','len'=>200,'comment'=>'下载保存地址'),
                    'storageSize'=>array('type'=>'bigint','len'=>10,'comment'=>'剩余空间大小'),
                    'downloadPoint'=>array('type'=>'int','len'=>10,'comment'=>'下载点类型'),
                    'downloadUrl'=>array('type'=>'varchar','len'=>255,'comment'=>'下载地址'),
                    'backupUrl'=>array('type'=>'varchar','len'=>255,'comment'=>'备份下载url'),
                    'gameSize'=>array('type'=>'bigint','len'=>20,'comment'=>'游戏大小'),
                    'statusCode'=>array('type'=>'int','len'=>5,'comment'=>'服务器返回状态码'),
                    'serverIp'=>array('type'=>'varchar','len'=>100,'comment'=>'服务器ip'),
                    'errorMsg'=>array('type'=>'varchar','len'=>500,'comment'=>'错误描述'),
            ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
	                'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
	                'title'=>array('type'=>'varchar','len'=>100,'comment'=>'游戏名称'),//游戏名称
	                'packagename'=>array('type'=>'varchar','len'=>100,'comment'=>'包名'),//包名
	                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
	                'gameSize'=>array('type'=>'bigint','len'=>20,'comment'=>'游戏大小'),
            ),
    ),
);


