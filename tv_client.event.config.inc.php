<?php
//公共字段
$arr_public_fields = array(
        'in_date'=>array('type'=>'int','len'=>11,'comment'=>'服务器端的记录日期(Ymd)'),  
        'intime'=>array('type'=>'int','len'=>11,'comment'=>'服务器接收时间戳'),
        'ip'=>array('type'=>'varchar','len'=>100,'comment'=>'客户端IP地址'),
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
        'time'=>array('type'=>'bigint','len'=>20,'comment'=>'客户端时间戳'),
        'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
        'number'=>array('type'=>'int','len'=>10,'comment'=>'统计数据类型id(当值为60010表示手柄配置的日志)'),
        'timeStr'=>array('type'=>'varchar','len'=>100,'comment'=>'客户端格式化时间'),
        'memorysize'=>array('type'=>'bigint','len'=>10,'comment'=>'内存大小'),
        'insdcardsize'=>array('type'=>'bigint','len'=>10,'comment'=>'存储空间'),
        'issupportexsdcard'=>array('type'=>'int','len'=>1,'comment'=>'是否支持存储卡,值：1，0'),
);

//模块配置
$arr_event_config = array(
    /**
     * @name:tv_install
     * @description: 安装模块
     * 安装模块（number：60001）
	      事件id（eventid）：
		1、1012：安装成功
		2、1013：安装失败
		3、1014：点击安装

     * @author: Xiong Jianbang
     * @create: 2015-7-30 上午10:28:12
     **/
	'tv_install' => array(
	        'dir_name'     => 'tv_install', //文件目录名称
	        'import_table_name' => 'kyx_tv_install_log', //创建的导入数据表名称
	        'statistics_table_name' => 'kyx_tv_install', //创建的统计数据表名称
	        'event_name' => '安装模块',//事件描述
	        'event_id' => array(1012,1013,1014),
	        'log_files_count' => 10,  //随机读取写入的日志文件数量
	        'import_prefix' => 'ti_',
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
	                'mac'=>array('type'=>'varchar','len'=>100,'comment'=>'手机mac'),//手机mac
	                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),//游戏名称
	        ),
    ),
    /**
     * @name:tv_uninstall
     * @description: 卸载模块
     * 卸载（number：60003）
	     事件id（eventid）：
		1、1033：管理页面，卸载
		2、1034：我的游戏页面，卸载
     * @author: Xiong Jianbang
     * @create: 2015-7-30 上午10:41:54
     **/
    'tv_uninstall' => array(
            'dir_name'     => 'tv_uninstall', //文件目录名称
            'import_table_name' => 'kyx_tv_uninstall_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_tv_uninstall', //创建的统计数据表名称
            'event_name' => '卸载模块',//事件描述
            'event_id' => array(1033,1034),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'tu_',
            'import_fields' => array(//表字段定义
                    'packagename'=>array('type'=>'varchar','len'=>200,'comment'=>'app包名'),
                    'versionname'=>array('type'=>'varchar','len'=>100,'comment'=>'app版本名称'),
                    'versioncode'=>array('type'=>'int','len'=>10,'comment'=>'版本号'),
                    'title'=>array('type'=>'varchar','len'=>200,'comment'=>'标题'),
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
     * @name:tv_boot
     * @description: 启动模块
     * 启动（number：60004）
    事件id（eventid）：
    		1、1031：我的游戏页面，打开
    		2、1032：管理页面，打开
     * @author: Xiong Jianbang
     * @create: 2015-8-5 上午10:57:49
     **/
    'tv_boot' => array(
            'dir_name'     => 'tv_boot', //文件目录名称
            'import_table_name' => 'kyx_tv_boot_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_tv_boot', //创建的统计数据表名称
            'event_name' => '启动模块',//事件描述
            'event_id' => array(1031,1032),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'tb_',
            'import_fields' => array(//表字段定义
                    'packagename'=>array('type'=>'varchar','len'=>200,'comment'=>'app包名'),
                    'versionname'=>array('type'=>'varchar','len'=>100,'comment'=>'app版本名称'),
                    'versioncode'=>array('type'=>'int','len'=>10,'comment'=>'版本号'),
                    'title'=>array('type'=>'varchar','len'=>200,'comment'=>'标题'),
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
     * @name:tv_home_page
     * @description: 首页页面
     * 首页页面（number：60005）
        事件id（eventid）：
        		1、1035：首页点击搜索按钮
        		2、1037：首页广告位置游戏点击
     * @author: Xiong Jianbang
     * @create: 2015-7-30 上午10:52:00
     **/
    'tv_home_page' => array(
            'dir_name'     => 'tv_home_page', //文件目录名称
            'import_table_name' => 'kyx_tv_home_page_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_tv_home_page', //创建的统计数据表名称
            'event_name' => '首页页面',//事件描述
            'event_id' => array(1035,1037),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'thp_',
            'import_fields' => array(//表字段定义
                    'appid'=>array('type'=>'int','len'=>10,'comment'=>'游戏唯一id'),
                    'location'=>array('type'=>'int','len'=>2,'comment'=>'首页类型id,值（1，2，3，4）'),
                    'title'=>array('type'=>'varchar','len'=>200,'comment'=>'标题'),
            ),
            'statistics_fields' => array(//统计表字段定义
                'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
                'location'=>array('type'=>'int','len'=>2,'comment'=>'首页类型id,值（1，2，3，4）'),
                'title'=>array('type'=>'varchar','len'=>200,'comment'=>'标题'),
                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),//数量
            ),
    ),
    /**
     * @name:tv_category_page
     * @description: 分类页面
     *  分类页面（number：60006）
           事件id（eventid）：
           1、1004：游戏分类
     * @author: Xiong Jianbang
     * @create: 2015-7-30 下午12:07:39
     **/
    'tv_category_page' => array(
            'dir_name'     => 'tv_category_page', //文件目录名称
            'import_table_name' => 'kyx_tv_category_page_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_tv_category_page', //创建的统计数据表名称
            'event_name' => '分类页面',//事件描述
            'event_id' => array(1004),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'tcp_',
            'import_fields' => array(//表字段定义
                    'cateogryid'=>array('type'=>'int','len'=>10,'comment'=>'游戏分类id'),
                    'location'=>array('type'=>'int','len'=>2,'comment'=>'广告类型id,值（1，2，3，4）'),
            ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                    'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
                    'cateogryid'=>array('type'=>'int','len'=>10,'comment'=>'游戏分类id'),
                    'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),//数量
            ),
    ),
    /**
     * @name:tv_detail_page
     * @description: 详情页面
     * 详情页面（number：60007）
        事件id（eventid）：
        		1、1005：游戏截图点击
        		2、1027：游戏详情页面加载
        		3、1038：详情页面广告位
     * @author: Xiong Jianbang
     * @create: 2015-7-30 下午12:11:00
     **/
    'tv_detail_page' => array(
            'dir_name'     => 'tv_detail_page', //文件目录名称
            'import_table_name' => 'kyx_tv_detail_page_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_tv_detail_page', //创建的统计数据表名称
            'event_name' => '详情页面',//事件描述
            'event_id' => array(1005,1027,1038),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'tdp_',
            'import_fields' => array(//表字段定义
                    'packagename'=>array('type'=>'varchar','len'=>200,'comment'=>'app包名'),
                    'versionname'=>array('type'=>'varchar','len'=>100,'comment'=>'app版本名称'),
                    'versioncode'=>array('type'=>'int','len'=>10,'comment'=>'版本号'),
                    'title'=>array('type'=>'varchar','len'=>200,'comment'=>'标题'),
                    'location'=>array('type'=>'int','len'=>5,'comment'=>'广告位置'),
            ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                    'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
                    'title'=>array('type'=>'varchar','len'=>100,'comment'=>'游戏名称'),//游戏名称
                    'packagename'=>array('type'=>'varchar','len'=>100,'comment'=>'包名'),//包名
                    'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
            ),
    ),
    /**
     * @name:tv_set_page
     * @description: 设置页面
     * 设置页面（number：60011）
        事件id（eventid）：
        		1、1036：点击下载和安装路径选择
        		2、1039：下载点更换点击
        		3、1023：下载完成，自动安装
        		4、1025：自动下载未完成的任务
        		5、1024：自动清除安装包
        		6、1026：自动更新
     * @author: Xiong Jianbang
     * @create: 2015-7-30 下午12:13:39
     **/
    'tv_set_page' => array(
            'dir_name'     => 'tv_set_page', //文件目录名称
            'import_table_name' => 'kyx_tv_set_page_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_tv_set_page', //创建的统计数据表名称
            'event_name' => '设置页面',//事件描述
            'event_id' => array(1036,1039,1023,1025,1024,1026),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'tsp_',
            'import_fields' => array(//表字段定义
                    'status'=>array('type'=>'int','len'=>1,'comment'=>'开关状态'),
            ),
             'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
	                'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
	                'status'=>array('type'=>'int','len'=>1,'comment'=>'开关状态'),
	                'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
            ),
    ),
    /**
     * @name:tv_select_order
     * @description: 游戏筛选排序模块（number：60012）
            事件id（eventid）：
            		1、1028：默认
            		2、1029：更新
            		3、1030：热度
     * @author: Xiong Jianbang
     * @create: 2015-8-6 上午10:06:57
     **/
    'tv_select_order' => array(
            'dir_name'     => 'tv_select_order', //文件目录名称
            'import_table_name' => 'kyx_tv_select_order_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_tv_select_order', //创建的统计数据表名称
            'event_name' => '游戏筛选排序',//事件描述
            'event_id' => array(1028,1029,1030),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'tso_',
            'import_fields' => array(//表字段定义
            ),
            'statistics_fields' => array(//统计表字段定义
                    'in_date'=>array('type'=>'int','len'=>11,'comment'=>'记录日期(Ymd)'),
                    'eventid'=>array('type'=>'int','len'=>10,'comment'=>'事件id'),
                    'num'=>array('type'=>'int','len'=>10,'comment'=>'数量'),
            ),
    ),
    
    /**
     * @name:tv_download
     * @description: 下载模块
     * 下载模块（number：60002）
        事件id（eventid）：
        		1、2006：选择存储卡，空间不足
        		2、2001：详情页面点击下载
        		3、2003：详情页面选择下载点
        		4、2008：重新下载
        		5、2011：用户点击继续
        		6、2002：选择下载空间，足够的存储卡
        		7、2005：取消下载
        		8、1016：下载完成
        		9、1017：下载失败
        		10、2009：服务器返回第一个数据
        		11、2010：302跳转
        		12、2004：暂停下载
        		13、2007：继续下载( core:onUrlLoaded )
     * @param: 
     * @return: 
     * @author: Xiong Jianbang
     * @create: 2015-7-30 下午12:16:58
     **/
    'tv_download' => array(
            'dir_name'     => 'tv_download', //文件目录名称
            'import_table_name' => 'kyx_tv_download_log', //创建的导入数据表名称
            'statistics_table_name' => 'kyx_tv_download', //创建的统计数据表名称
            'event_name' => '下载模块',//事件描述
            'event_id' => array(1016,1017,2001,2002,2003,2004,2005,2006,2007,2008,2009,2010,2011),
            'log_files_count' => 10,  //随机读取写入的日志文件数量
            'import_prefix' => 'td_',
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


