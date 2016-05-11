<?php
/**
 * UCenter配置
 */
define('UC_HOME_DIR', '/mnt/hgfs/api.kuaiyouxi.com');
define('UC_CONNECT', 'mysql');
define('UC_DBHOST', '192.168.0.241');
define('UC_DBUSER', 'muzhiwangz');
define('UC_DBPW', 'muzhiwan@#$%^^&');
define('UC_DBNAME', 'kyx_ucenter');
define('UC_DBCHARSET', 'utf8');
define('UC_DBTABLEPRE', '`kyx_ucenter`.uc_');
define('UC_DBCONNECT', '0');
define('UC_KEY', '4jkkrt465tregfozadkcvne90832kkeolapo');// 与 UCenter 的通信密钥, 要与 UCenter 保持一致
define('UC_API', 'http://ucenter.kuaiyouxi.test');// UCenter 的 URL 地址, 在调用头像时依赖此常量
define('UC_CHARSET', 'utf-8');// UCenter 的字符集
define('UC_IP', '127.0.0.1');// UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
define('UC_APPID', '4');// 当前应用的 ID
define('UC_PPP', '20');

define('UC_VALID_TIME',6000);

