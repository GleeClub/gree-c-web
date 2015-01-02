<?php
	require_once('functions.php');
	$p1 = $_POST['p1'];
	$p2 = $_POST['p2'];
	if($p1 == $p2) {
		$sql = "update member set password='" . mysql_real_escape_string(md5($p1)) . "' where email='" . mysql_real_escape_string($_POST['email']) . "';";
		echo $sql;
		mysql_query($sql);
		header("Location: ../index.php");
	} else {
		echo '<script language="Javascript"> alert("Passwords did not agree, try again!"); </script>';
		echo '<script language="Javascript"> history.go(-1); </script>';
	}
?>
