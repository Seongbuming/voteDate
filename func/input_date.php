<?php
session_start();

require("mysqli.php");

$dates = split(",", $_POST['Dates']);
$user = $mysqli->real_escape_string($_POST['Name']);
$reason = $mysqli->real_escape_string($_POST['Reason']);
$ip = $_SERVER['REMOTE_ADDR'];

$_SESSION['User'] = $user;

foreach($dates as $date) {
	$date = $mysqli->real_escape_string($date);

	$sql = $mysqli->query(sprintf("SELECT * FROM date_data WHERE Date = '%s' AND User = '%s' AND Reason = '%s'",
		$date, $user, $reason));
	if($sql->num_rows == 0) {
		$sql = $mysqli->query(sprintf("INSERT INTO date_data (Date, User, Reason, IP) VALUES ('%s', '%s', '%s', '%s')",
			$date, $user, $reason, $ip));
		if($sql)
			printf("1");
		else
			die("0|데이터를 입력하지 못하였습니다.");
	}
	else
		die("0|이미 등록한 내용입니다.");
}
?>