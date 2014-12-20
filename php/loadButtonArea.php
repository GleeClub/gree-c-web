<?php
	require_once('functions.php');
	$userEmail = $_COOKIE['email'];

	if(isset($_POST['eventNo'])){
		$eventNo = $_POST['eventNo'];

		//get the event type
		$sql = "select * from event where eventNo='$eventNo'";
		$event = mysql_fetch_array(mysql_query($sql));
		$typeNumber = $event['type'];

		//determine whether the user said they were attending or not attending
		$attending = $_POST['attending'];

		$sql = "SELECT `callTime` FROM `event` WHERE `eventNo` = $eventNo";
		$results = mysql_fetch_array(mysql_query($sql));
		if (strtotime($results['callTime']) < time() + 86400 && $attending != 1)
		{
			// Prevent changing to not attending less than 24 hours before call
			echo '<span class="label label-important">Deadline is past</span>' . buttonArea($eventNo, $typeNumber);
			return;
		}
		
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
