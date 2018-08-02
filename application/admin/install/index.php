<?php
$root_path = dirname(dirname(__DIR__));
if (file_exists("../public/install.lock")) {
	require './step/grant.php';
	exit();
}
$action = isset($_GET['action']) ? $_GET['action'] : 'licence';

function mysql_check($db_config) {
	$link = mysqli_connect($db_config['DB_HOST'], $db_config['DB_USER'], $db_config['DB_PWD'], $db_config['DB_NAME'], $db_config['DB_PORT']) or die ('Not connected : ' . mysqli_connect_error());
	if (mysqli_connect_error()) {
		echo '<p class="text-danger">Mysql链接错误 (' . mysqli_connect_errno() . ') ' . mysqli_connect_error() . '</p>';
		return false;
	}
	$version = mysqli_get_server_info($link);
	if ($version < '5.5') {
		echo '<p class="text-danger">Mysql 版本过低！你升级MySQL版本到5.5或者更高</p>';
		return false;
	}
	return $link;
}

function _sql_execute($link, $sql, $r_tablepre = '', $s_tablepre = 'clt_') {
	$sqls = _sql_split($sql, $r_tablepre, $s_tablepre);
	if (is_array($sqls)) {
		foreach ($sqls as $sql) {
			if (trim($sql) != '') {
				if (mysqli_query($link, $sql) == false) {
					echo "<p class='text-danger'>SQL: " . $sql . " 执行失败！</p>";
				}
			}
		}
	} else {
		if (mysqli_query($link, $sqls) == false) {
			echo "<p class='text-danger'>SQL: " . $sqls . " 执行失败！</p>";
		}
	}
	return true;
}

function _sql_split($sql, $r_tablepre = '', $s_tablepre = 'clt_') {
	if ($r_tablepre != $s_tablepre) {
		$sql = str_replace($s_tablepre, $r_tablepre, $sql);
	}

	$sql          = str_replace("\r", "\n", $sql);
	$ret          = [];
	$num          = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach ($queriesarray as $query) {
		$ret[$num] = '';
		$queries   = explode("\n", trim($query));
		$queries   = array_filter($queries);
		foreach ($queries as $query) {
			$str1 = substr($query, 0, 1);
			if ($str1 != '#' && $str1 != '-') {
				$ret[$num] .= $query;
			}
		}
		$num++;
	}
	return $ret;
}

function parse_host($httpurl=false)
{
	$httpurl = $httpurl ? $httpurl : 'http://' . $_SERVER['HTTP_HOST'];
	$httpurl = strtolower( trim($httpurl) );
	if(empty($httpurl)) return ;
	$regx1 = '/https?:\/\/(([^\/\?#]+\.)?([^\/\?#-\.]+\.)(com\.cn|org\.cn|net\.cn|com\.jp|co\.jp|com\.kr|com\.tw)(\:[0-9]+)?)/i';
	$regx2 = '/https?:\/\/(([^\/\?#]+\.)?([^\/\?#-\.]+\.)(cn|com|org|net|cc|biz|hk|jp|kr|name|me|tw|la)(\:[0-9]+)?)/i';
	$host = $tophost = '';
	if(preg_match($regx1,$httpurl,$matches))
	{
		$host = $matches[1];
	} elseif(preg_match($regx2, $httpurl, $matches)) {
		$host = $matches[1];
	}
	if($matches) $tophost = $matches[2] == 'www.' ? ltrim($host,'www.'):$matches[3].$matches[4];
	return $tophost;
}

switch ($action) {
	case 'requirement':
		require './step/requirement.php';
		break;
	case 'configure':
		$database_info = require '../app/database.php';
		require './step/configure.php';
		break;
	case 'database-check':
		extract($_POST);
		$link = @mysqli_connect($DB_HOST, $DB_USER, $DB_PWD, null, $DB_PORT);
		if (!$link) {
			exit(json_encode(['code' => 2, 'message' => '无法连接数据库服务器，请检查配置！']));
		}
		$server_info = mysqli_get_server_info($link);
		if ($server_info < '5.5') {
			exit(json_encode(['code' => 6, 'message' => 'CLTPHP 要求MySQL版本大于5.5，请升级你的MySQL版本']));
		}
		if (!mysqli_select_db($link, $DB_NAME)) {
			if (!@mysqli_query($link, "CREATE DATABASE `$DB_NAME`")) {
				exit(json_encode(['code' => 3, 'message' => '成功连接数据库，但是指定的数据库不存在并且无法自动创建，请先通过其他方式建立数据库！']));
			}
			mysqli_select_db($link, $DB_NAME);
		}
		$tables = [];
		$query  = mysqli_query($link, "SHOW TABLES FROM `$DB_NAME`");
		while ($r = mysqli_fetch_row($query)) {
			$tables[] = $r[0];
		}
		if ($tables && in_array($DB_PREFIX . 'model', $tables)) {
			exit(json_encode(['code' => 0, 'message' => '您已经安装过CLTPHP，系统会自动删除老数据！是否继续？']));
		} else {
			exit(json_encode(['code' => 200, 'message' => '']));
		}
		break;
	case 'install':
		$error       = false;
		$db_config  = $_POST['db'];
		$config="<?php
return [
    // 数据库类型
    'type' => 'mysql',
    // 服务器地址
    'hostname' => '$db_config[DB_HOST]',
    // 数据库名
    'database' => '$db_config[DB_NAME]',
    // 用户名
    'username' => '$db_config[DB_USER]',
    // 密码
    'password' => '$db_config[DB_PWD]',
    // 端口
    'hostport' => '$db_config[DB_PORT]',
    // 连接dsn
    'dsn' => '',
    // 数据库连接参数
    'params' => [],
    // 数据库编码默认采用utf8
    'charset' => 'utf8',
    // 数据库表前缀
    'prefix' => '$db_config[DB_PREFIX]',
    // 数据库调试模式
    'debug' => true,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy' => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate' => false,
    // 读写分离后 主服务器数量
    'master_num' => 1,
    // 指定从服务器序号
    'slave_no' => '',
    // 是否严格检查字段是否存在
    'fields_strict' => false,
    // 数据集返回类型 array 数组 collection Collection对象
    'resultset_type' => 'array',
    // 是否自动写入时间戳字段
    'auto_timestamp' => false,
    // 是否需要进行SQL性能分析
    'sql_explain' => false,
];
?>";
		// 写数据库配置 && 基础配置
		$fp=fopen("../app/database.php",'w+');
		fputs($fp,$config);
		fclose($fp);
		require './step/install.php';
		break;
	case 'grant':
		require './step/grant.php';
		file_put_contents('../public/install.lock', 'true');
		break;
	case 'licence':
	default:
		require './step/licence.php';
		break;
}
