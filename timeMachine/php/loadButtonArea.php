<?php
	require_once('variables.php');
	require_once('functions.php');
	mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
	mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
	$userEmail = $_COOKIE['email'];

	if(isset($_POST['eventNo'])){
		$eventNo = $_POST['eventNo'];

		//get the event type
		$sql = "select * from event where eventNo='$eventNo'";
		$event = mysql_fetch_array(mysql_query($sql));
		$typeNumber = $event['type'];

		//determine whether the user said they were attending or not attending
		$attending = $_POST['attending'];

		//update the attends relationship
		$sql = "update `attends` set shouldAttend='$attending', confirmed='1' where memberID='$userEmail' and eventNo='$eventNo'";
		mysql_query($sql);

		//then echo the new buttons based on the new attends relationship
		echo buttonArea($eventNo, $typeNumber);
	}
	else{
		echo "Something went wrong.  Let a developer know.";
	}

?>