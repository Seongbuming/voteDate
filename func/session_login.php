<?php
session_start();

require("mysqli.php");

$_SESSION['User'] = $mysqli->real_escape_string($_POST['Name']);
printf("1");
?>