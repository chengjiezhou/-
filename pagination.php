<?php

include './DefineClass/mysql.class.php';

$config = array (

			// 'hostname' => '101.200.182.5:3316',
			'hostname' => '127.0.0.1',

			'database' => 'test',
			// 'database' => 'iso',

			'username' => 'root',

			'password' => 'root',

			'tablepre' => '',

			'charset' => 'utf8',

			'type' => 'mysql',

			'debug' => true,

			'pconnect' => 0,

			'autoconnect' => 0

			);

$db = new mysql;
$db->open($config);

function getTotal() {
	global $db;

	$table = 'a';
	$i = 1;
	$tablename = $table.$i;
	$total = 0;

	while ($db->table_exists($tablename)) {
		
		$total += _queryTotal($tablename);

		$i++;
		$tablename = $table.$i;
	}

	return $total;
}

function _queryTotal($tablename, $where='') {
	global $db;

	$sql = 'select count(*) total from '.$tablename;

	!empty($where) && $sql .= ' WHERE '. $where;

	$res = current($db->fetch_data($sql));
	return $res['total'];
}

function _queryData($tablename, $where='', $fields='*', $offset = 0, $limit = 1) {
	global $db;

	$sql = 'SELECT '.$fields.' FROM '.$tablename;

	!empty($where) && $sql .= ' WHERE '.$where;

	$sql .= ' LIMIT '.$limit.' OFFSET '.$offset;

	return $db->fetch_data($sql);
}

function getData($page, $pagesize) {
	global $db;

	$i = 1;
	$data_tmp = array();
	$page = (int)$page;
	$offset_lock = true;
	$last_count = ($page-1)*$pagesize;

	do {
		$tablename = 'a'.$i;
		$_data = array();
		static $ctt = 0;

		if(!$db->table_exists($tablename)) 
			break;

		if($page == 1) {
			$offset = 0;
		} else if($page > 1 && $offset_lock){
			$_ctt = $ctt;
			$ctt += _queryTotal($tablename);
			
			if($ctt < $last_count)
				continue;

			if($ctt > $last_count && $i==1) {
				$offset = $last_count;
			} else {
				$offset = abs($last_count-$_ctt);
			}

			$offset_lock = false;
		} else {
			$offset = 0;
		}

		$limit = max($pagesize-count($data_tmp), 0);

		$_data = _queryData($tablename, '', 'id', $offset, $limit);
		$data_tmp = array_merge($data_tmp, $_data);

	}while(count($data_tmp) < $pagesize && $i++);

	return $data_tmp;
}

// 总数
echo $total = getTotal();

// 获取数据
$page = isset($_GET['p']) ? $_GET['p'] : 1;
$pagesize = isset($_GET['ps']) ? $_GET['ps'] : 10;

$data = getData($page, $pagesize);

var_dump($data);