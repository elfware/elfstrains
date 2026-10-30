<?php

$dbuser = 'username'; // enter the username here
$dbpassword = 'passwd'; //enter the password here
$dbname = 'elfstrains';
$hostname = 'localhost';
$port = 8889;

// Open database connection
try {
	$dbh = new PDO("mysql:host=$hostname;port=$port;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpassword);
	$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	//$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e) {
	echo $e->getMessage();
	die();
}
$sql = "SET SESSION sql_mode=REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY','')";
$stmt = $dbh->query($sql);

?>
