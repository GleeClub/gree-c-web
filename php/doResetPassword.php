<?php
	require_once('variables.php');
	require_once('functions.php');
	mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
	mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
	$p1 = $_POST['p1'];
	$p2 = $_POST['p2'];
	if($p1 == $p2) {
		$sql = "update member set password='" . mysql_real_escape_string(md5($p1)) . "' where email='" . mysql_real_escape_string($_POST['email']) . "';";
		echo $sql;
		mysql_query($sql);
		header("location:../index.php");
	} else {
		echo '<script language="Javascript"> alert("Passwords did not agree, try again!"); </script>';
		echo '<script language="Javascript"> history.go(-1); </script>';
	}
?>