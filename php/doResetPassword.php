<?php
	require_once('functions.php');
	$p1 = $_POST['p1'];
	$p2 = $_POST['p2'];
	$email = decrypt2($_POST['token']);
	$arr = explode(" ", $email);
	$email = $arr[0];
	$time = intval($arr[1]);
	if (time() - $time > 1800) die("Link has expired, please request a reset again.");
	if ($p1 == $p2) {
		$pass = md5($p1);
		query("update `member` set `password` = ? where `email` = ?", [$pass, $email]);
		header("Location: ../index.php");
	} else {
		echo '<script language="Javascript"> alert("Passwords did not agree, try again!"); </script>';
		echo '<script language="Javascript"> history.go(-1); </script>';
	}
?>
