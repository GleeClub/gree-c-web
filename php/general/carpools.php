<?php
/**** Carpool functions ****/

function passengerSpots($email){
	$sql = "SELECT passengers FROM member WHERE email='$email';";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['passengers'];
}

function livesAt($email){
	$sql = "SELECT location FROM member WHERE email='$email';";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['location'];
}

function phoneNumber($email){
	$sql = "SELECT phone FROM member WHERE email='$email';";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['phone'];
}

function getSectionTypes() {
	$sql = "select * from sectionType";
	return mysql_query($sql);
}

function getCarpoolDetails($carpoolId){
	$sql = "SELECT * FROM `ridesin` WHERE carpoolID=$carpoolId;";
	$result = mysql_query($sql);
	return $result;
}
?>
