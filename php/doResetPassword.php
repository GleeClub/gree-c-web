<?php
	require_once('functions.php');
	$p1 = $_POST['p1'];
	$p2 = $_POST['p2'];
	if ($p1 == $p2) {
		$pass = md5($p1);
		query("update `member` set `password` = ? where `email` = ?", [$pass, $_POST["email"]]);
		header("Location: ../index.php");
	} else {
		echo '<script language="Javascript"> alert("Passwords did not agree, try again!"); </script>';
		echo '<script language="Javascript"> history.go(-1); </script>';
	}
?>
