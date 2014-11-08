<?php
	require_once('./functions.php');
	$userEmail = $_COOKIE['email'];
	mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
	mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
	$sql = "select firstName,lastName,email from member";
	$result = mysql_query($sql);
	$arr = array();
	$temp = array();
	$count=0;
	while($resultArray = mysql_fetch_array($result)){
		$temp[0] = $resultArray["firstName"];
		$temp[1] = $resultArray["lastName"];
		$temp[2] = $resultArray["email"];
		$arr[$count] = json_encode($temp);
		$count++;
	}
	$jsonArr = json_encode($arr);
	echo $jsonArr;
?>