<?PHP
//error_reporting (0);	
error_reporting(E_ALL & E_NOTICE);
ini_set('default_charset', "utf-8");
date_default_timezone_set('Asia/Shanghai');//定义时区
define('DS', DIRECTORY_SEPARATOR);
define('WEBPATH_DIR', dirname(__FILE__) . DS); //整站系统路径
define('WEBPATH_DIR_INC', 'http://' . $_SERVER['HTTP_HOST'].DS.'kuaiyouxi'.DS.'api.kuaiyouxi.com'.DS); //整站网页路径
define('THIS_DATETIME',time());//系统中用入插入数据库的日期时间格式
define('URL_KYX_KEY','api.kuaiyouxi.com');//外部接口加密key

define('SYS_URL_KYX_KEY','m#>23.fewr.kyx.sys@');//内部接口加密key

//图片在服务器磁盘上的路径
define('LOCAL_IMG_PATH','D:\xampp\htdocs\kuaiyouxi\admin.kuaiyouxi.com\uploads\img');

//网络通过我们的服务器下载APK的地址
//define('LOCAL_URL_DOWN','http://down.cdn.youxilaile.com');
define('LOCAL_URL_DOWN','http://down.cdn.gugeanzhuangqi.com');
//网络通过我们的服务器下载图片源的地址
//define('LOCAL_URL_DOWN_IMG','http://res.youxilaile.com');
define('LOCAL_URL_DOWN_IMG','http://res.gugeanzhuangqi.com');
//主站的域名
define('LOCAL_URL_WWW','http://test.www.kuaiyouxi.com');

//网络通过蓝汛cdn的下载APK的址（临时先改为乐视的先）
//define('CDN_LANXUN_URL_DOWN','http://cc.cdn.youxilaile.com');
define('CDN_LANXUN_URL_DOWN','http://letv.cdn.gugeanzhuangqi.com');

//网络通过乐视cdn的下载APK的址
//define('CDN_LESHI_URL_DOWN','http://letv.cdn.youxilaile.com');
define('CDN_LESHI_URL_DOWN','http://letv.cdn.gugeanzhuangqi.com');

//===============begin memcache服务器配置=======
//===========================================
define('MEMCACHE_HOST', '192.168.0.196');//memcache服务器地址
define('MEMCACHE_PORT', 11211);//memcache服务器端口
define('MEMCACHE_EXPIRATION', 5);//缓存时间(0表示永远不会过期)
define('MEMCACHE_PREFIX', 'kyx');//KEY的MD5前辍
define('MEMCACHE_COMPRESSION', FALSE);//是否对数据进行压缩
//===========================================
//===============end memcache 服务器配置========
//===============begin coreseek服务器配置=======
//===========================================
define('SPHINX_HOST', '192.168.0.206');//coreseek服务器地址
define('SPHINX_PORT', 9312);//coreseek服务器端口
//===========================================
//===============end coreseek 服务器配置========



//设定可以访问的IP
$GLOBALS['SYS_AUTO_ACTION_IP'] = array('10.140.2.180','10.140.6.31','10.140.38.67','127.0.0.1','10.140.34.228');

define('SYS_LOG_MAX_SECOND',1);//系统运行时间大于这个的要记录运行页面的情况

include_once(WEBPATH_DIR."include".DS."public_functions_helper.php");//公共函数
include_once(WEBPATH_DIR."include".DS."mysql.class.php");//mysql 操作类
include_once(WEBPATH_DIR."include".DS."img.class.php");//图片处理类
include_once(WEBPATH_DIR."include".DS."memcache.class.php");//缓存处理类
include_once(WEBPATH_DIR."include".DS."sphinxclient.php");//搜索处理类

//include_once(WEBPATH_DIR."include".DS."zdefile.class.php");//文件处理类
//include_once(WEBPATH_DIR."include".DS."smtp.class.php");//邮件处理类
//页面开始运行时间
if( !isset($GLOBALS['SYS_START_TIME']) ){$GLOBALS['SYS_START_TIME'] = @microtime_float();}

// 注册页面完成时处理函数
register_shutdown_function('sys_log_shutdown_handler');

//设定手柄的模式键值
//1、BFM,2、360,3、数字,4、神鹰
$GLOBALS['SYS_HANDLE_PATTERN'] = array(
	1=>array('val'=>1,'name'=>'BFM','keys'=>'[{"name":"B","motion":-1000,"key":97},{"name":"A","motion":-1000,"key":96},{"name":"X","motion":-1000,"key":99},{"name":"Y","motion":-1000,"key":100},{"name":"L1","motion":-1000,"key":102},{"name":"R1","motion":-1000,"key":103},{"name":"L2","motion":17,"key":104},{"name":"R2","motion":18,"key":105}]'),
	2=>array('val'=>2,'name'=>'360','keys'=>'[{"name":"Y","motion":-1000,"key":100},{"name":"B","motion":-1000,"key":97},{"name":"A","motion":-1000,"key":96},{"name":"X","motion":-1000,"key":99},{"name":"L1","motion":-1000,"key":102},{"name":"R1","motion":-1000,"key":103},{"name":"L2","motion":17,"key":0},{"name":"R2","motion":18,"key":0}]'),
	3=>array('val'=>3,'name'=>'数字','keys'=>'[{"name":"Y","motion":-1000,"key":96},{"name":"B","motion":-1000,"key":97},{"name":"A","motion":-1000,"key":98},{"name":"X","motion":-1000,"key":99},{"name":"L1","motion":-1000,"key":100},{"name":"R1","motion":-1000,"key":101},{"name":"L2","motion":-1000,"key":102},{"name":"R2","motion":-1000,"key":103}]'),
	4=>array('val'=>4,'name'=>'神鹰','keys'=>'[{"name":"Y","motion":-1000,"key":188},{"name":"B","motion":-1000,"key":189},{"name":"A","motion":-1000,"key":190},{"name":"X","motion":-1000,"key":191},{"name":"L1","motion":-1000,"key":192},{"name":"R1","motion":-1000,"key":193},{"name":"L2","motion":-1000,"key":194},{"name":"R2","motion":-1000,"key":195}]')
);


?>