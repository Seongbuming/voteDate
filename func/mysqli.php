<?php
require($_SERVER['DOCUMENT_ROOT']."/voteDate/config/db.php");

$mysqli = new mysqli($DB['host'], $DB['id'], $DB['pw'], $DB['db']);
if($mysqli->connect_error)
	die('DB 접속 오류 ('.$mysqli->connect_errno.') '.$mysqli->connect_error);

$mysqli->select_db('votedate');
$mysqli->query("SET SESSION CHARACTER_SET_CONNECTION = UTF8");
$mysqli->query("SET SESSION CHARACTER_SET_RESULTS = UTF8");
$mysqli->query("SET SESSION CHARACTER_SET_CLIENT = UTF8");
?>