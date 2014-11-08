<?php

require_once('./functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];

if(isset($_POST['eventNo'])){
	$eventNo = $_POST['eventNo'];
	$memberID = $_POST['email'];
	$didAttend = $_POST['didAttend'];

	//update the attends info
	$sql = "update attends set confirmed='1', didAttend='$didAttend' where memberID='$memberID' and eventNo='$eventNo'";
	mysql_query($sql);

	//get the user's first and last name
	$sql = "select * from member where email='$memberID'";
	$member = mysql_fetch_array(mysql_query($sql));
	$firstName = $member['firstName'];
	$lastName = $member['lastName'];

	//get the updated attendance info and th recalculated grade
	echo getMemberAttendanceRows($memberID,$firstName,$lastName,true);
}
else
	echo "Something went wrong :/";

?>