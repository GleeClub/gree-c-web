<?php

require_once('./functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

$userEmail = $_COOKIE['email'];
$position = positionFromEmail($userEmail);

if(isset($_POST['eventNo']) && $position!="Member"){
	$eventNo = $_POST['eventNo'];
	$memberID = $_POST['email'];
	$shouldAttend = $_POST['shouldAttend'];

	//get the current attends info
	$sql = "select shouldAttend from attends where memberID='$memberID' and eventNo='$eventNo'";
	$attendses = mysql_query($sql);

	//make a new attends relationship, if it doesn't already exist
	if(mysql_num_rows($attendses)==0){
		$sql = "INSERT INTO attends (memberID, shouldAttend, didAttend, eventNo, minutesLate, confirmed) VALUES ('$memberID', '$shouldAttend', '0', '$eventNo', '0', '1')";
		mysql_query($sql);
	}
	//otherwise, update the existinf relationship
	else{
		$sql = "update attends set confirmed='1', shouldAttend='$shouldAttend' where memberID='$memberID' and eventNo='$eventNo'";
		mysql_query($sql);
	}

	//get the user's first and last name
	$sql = "select * from member where email='$memberID'";
	$member = mysql_fetch_array(mysql_query($sql));
	$firstName = $member['firstName'];
	$lastName = $member['lastName'];

	//get the updated attendance info for this one attends relationship
	echo getSingleEventAttendanceRow($eventNo,$memberID);
}
else
	echo "Something went wrong :/";

?>