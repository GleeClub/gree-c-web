<?php
require_once('variables.php');


function getInbox($email) {
	$sql = "select * from convoMaster left join convoMembers on convoMaster.id=convoMembers.id where convoMembers.email='$email' order by convoMaster.modified desc";
	return mysql_query($sql);
}

function getConvoTitle($id) {
	$sql = "select title from convoMaster where id=$id";
	$arr = mysql_fetch_array(mysql_query($sql));
	return $arr['title'];
}

function getConvoMembers($id, $email) {
	$sql = "select distinct member.prefName, member.lastName from convoMembers left join member on member.email=convoMembers.email where convoMembers.id='$id' and convoMembers.email<>'" . mysql_real_escape_string($_COOKIE['email']) ."'";
	return mysql_query($sql);
}

function getConvoMessages($id) {
	$sql = "select message, timestamp, member.prefName, member.lastName from convoMessages left join member on member.email=convoMessages.sender where id='$id' order by timestamp asc";
	return mysql_query($sql);	
}

function createGig($name, $callTime, $releaseTime, $pointValue, $comments, $type, $location, $perfTime, $uni, $contName, $contEmail, $contPhone, $price, $sem) {
	//Add the gig to 'event'
	$sql = "insert into event (name, callTime, releaseTime, pointValue, comments, type, location, uniform, semester) values ('$name', '$callTime', '$releaseTime', '$pointValue', '$comments', '$type', '$location', '$uni', '$sem');";
	mysql_query($sql);

	//Get the autogenerated event id
	$eventNo = mysql_insert_id();

	//Add the gig to 'gig'
	$sql = "insert into gig values ('$eventNo', '$location', '$perfTime', '$uni', '$contName', '$contEmail', '$contPhone', '$price');";
	mysql_query($sql);

	$sql = "INSERT INTO `attends` (`memberID`, `eventNo`, `eventID`) select email, $eventNo, '$name' from member;";
	mysql_query($sql);

	return $eventNo;
}

function createEvent($name, $callTime, $releaseTime, $pointValue, $comments, $type, $location, $uni, $sem) {
	//Add the gig to 'event'
	$sql = "insert into event (name, callTime, releaseTime, pointValue, comments, type, location, uniform, semester) values ('$name', '$callTime', '$releaseTime', '$pointValue', '$comments', '$type', '$location', '$uni', '$sem');";
	mysql_query($sql);

	$eventNo = mysql_insert_id();
	$sql = "INSERT INTO `attends` (`memberID`, `eventNo`, `eventID`) select email, $eventNo, '$name' from member;";
	mysql_query($sql);
	return $eventNo;
}

function getEventTypes() {
	$sql = "select * from eventType";
	return mysql_query($sql);
}



?>