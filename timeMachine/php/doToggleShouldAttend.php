<?php
	require_once('functions.php');
	$userEmail = $_COOKIE['email'];

	if(isset($_POST['eventNo']))
		$eventNo = $_POST['eventNo'];
	else
		$eventNo = $_GET['eventNo'];

	$memberID = $userEmail;
	

	$sql = "select shouldAttend from attends where memberID='$memberID' and eventNo='$eventNo'";
	$result = mysql_fetch_array(mysql_query($sql));
	$shouldAttend = $result['shouldAttend'];

	if($shouldAttend=='1')
		$newVal = '0';
	else
		$newVal = '1';

	$sql = "update attends set shouldAttend='$newVal' where memberID='$memberID' and eventNo='$eventNo'";
	mysql_query($sql);

	echo $newVal;


?>
