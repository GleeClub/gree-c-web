<?php
/**** Event functions ****/

function getEventDetails($eventNo){
	$sql = "SELECT * FROM `event` WHERE eventNo=$eventNo;";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) != 1) return "NULL";
	$results = mysql_fetch_array($result);
	return $results;
}

function getGigDetails($eventNo){
	$sql = "SELECT * FROM `gig` WHERE eventNo=$eventNo;";
	$results = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $results;
}

function getEventType($number){
	$sql = "SELECT typeName FROM `eventType` WHERE typeNo=$number;";
	$results = mysql_fetch_array(mysql_query($sql));
	return $results['typeName'];
}

function getEventName($eventNo){
	$eventSql = "SELECT name from `event` where eventNo='$eventNo'";
	$eventResults = mysql_fetch_array(mysql_query($eventSql));
	return $eventResults['name'];
}

function getEventTypeLabelClass($number){
	if($number == '1') return 'label-info';
	if($number == '2') return 'label-success';
	if($number == '3') return 'label-warning';
	if($number == '4') return 'label-important';
}

function labelArea($type){
	$html = '<span class="label '.getEventTypeLabelClass($type).'">'.getEventType($type).'</span>';
	return $html;
}

function buttonArea($eventNo, $typeNumber)
{
	$sql = "SELECT `callTime` FROM `event` WHERE `eventNo` = $eventNo";
	$results = mysql_fetch_array(mysql_query($sql));
	$soon = 0;
	if (strtotime($results['callTime']) < time() + 86400) $soon = 1;
	
	$sql = mysql_query("SELECT * FROM `attends` WHERE eventNo=$eventNo AND memberID='".getuser()."';");
	if (mysql_num_rows($sql) == 0) $html = "<span class='label'>Not attending</span>";
	else
	{
		$results = mysql_fetch_array($sql);
		if ($results['shouldAttend'] == '0') $html = "<span class='label'>Not attending</span>";
		else if ($results['confirmed'] == '0')
		{
			if ($typeNumber == '3')
			{
				//not confirmed volunteer gig
				if ($soon) $html = "<span class='label'>Attending</span>"; //'<div class="btn btn-confirm">Confirm I\'ll Attend</div>';
				else $html = '<div class="btn btn-primary btn-confirm" style="width:90%;">I will attend</div> <div class="btn btn-warning btn-deny" style="width:90%;">I won\'t attend</div>';
			}
			else
			{
				//not confirmed, not volunteer gig
				$html = '<div class="btn btn-confirm">Confirm I\'ll Attend</div>';
			}
		}
		else $html = '<span class="label">Attending</span>';
	}
	return $html;
}

//function requestAbsenceButton($eventNo){
	//$absenceRequest = getAbsenceRequest($eventNo, getuser());
	//if($absenceRequest['state'] == 'pending'){
		//return '<td><span class="label label-warning">absence request '.$absenceRequest['state'].'</span></td><td><div class="btn">edit request</div></td>';
	//}
	//if($absenceRequest['state'] == 'confirmed'){
		//return '<td><span class="label label-success">absence request '.$absenceRequest['state'].'</span></td><td></td>';
	//}
	//if($absenceRequest['state'] == 'denied'){
		//return '<td><span class="label label-important">absence request '.$absenceRequest['state'].'</span></td><td><div class="btn">edit request</div></td>';
	//}
	//else{
		//$eventDetails = getEventDetails($_SESSION['eventNo']);
		//$callTime = strtotime($eventDetails['callTime']);
		//if($callTime > time()){
			//return '<div class="btn">request absence</div>';
		//}
	//}
	////print_r($absenceRequest);
//}

function getAbsenceRequest($eventNo, $person){
	$sql = "SELECT * FROM `absencerequest` WHERE eventNo=$eventNo AND memberID='$person';";
	$results = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $results;
}

function shouldAttend($email, $eventNo){
	$sql = "SELECT shouldAttend FROM attends WHERE memberID='$email' AND eventNo=$eventNo;";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['shouldAttend'] == 0 ? false : true;
}

function isConfirmed($email, $eventNo){
	$sql = "SELECT confirmed FROM attends WHERE memberID='$email' AND eventNo=$eventNo;";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['confirmed'] == 0 ? false : true;
}

/**** Event functions ****/

function getEventDetails($eventNo){
	$sql = "SELECT * FROM `event` WHERE eventNo=$eventNo;";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) != 1) return "NULL";
	$results = mysql_fetch_array($result);
	return $results;
}

function getGigDetails($eventNo){
	$sql = "SELECT * FROM `gig` WHERE eventNo=$eventNo;";
	$results = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $results;
}

function getEventType($number){
	$sql = "SELECT typeName FROM `eventType` WHERE typeNo=$number;";
	$results = mysql_fetch_array(mysql_query($sql));
	return $results['typeName'];
}

function getEventName($eventNo){
	$eventSql = "SELECT name from `event` where eventNo='$eventNo'";
	$eventResults = mysql_fetch_array(mysql_query($eventSql));
	return $eventResults['name'];
}

function getEventTypeLabelClass($number){
	if($number == '1') return 'label-info';
	if($number == '2') return 'label-success';
	if($number == '3') return 'label-warning';
	if($number == '4') return 'label-important';
}

function labelArea($type){
	$html = '<span class="label '.getEventTypeLabelClass($type).'">'.getEventType($type).'</span>';
	return $html;
}

function buttonArea($eventNo, $typeNumber)
{
	$sql = "SELECT `callTime` FROM `event` WHERE `eventNo` = $eventNo";
	$results = mysql_fetch_array(mysql_query($sql));
	$soon = 0;
	if (strtotime($results['callTime']) < time() + 86400) $soon = 1;
	
	$sql = mysql_query("SELECT * FROM `attends` WHERE eventNo=$eventNo AND memberID='".getuser()."';");
	if (mysql_num_rows($sql) == 0) $html = "<span class='label'>Not attending</span>";
	else
	{
		$results = mysql_fetch_array($sql);
		if ($results['shouldAttend'] == '0') $html = "<span class='label'>Not attending</span>";
		else if ($results['confirmed'] == '0')
		{
			if ($typeNumber == '3')
			{
				//not confirmed volunteer gig
				if ($soon) $html = "<span class='label'>Attending</span>"; //'<div class="btn btn-confirm">Confirm I\'ll Attend</div>';
				else $html = '<div class="btn btn-primary btn-confirm" style="width:90%;">I will attend</div> <div class="btn btn-warning btn-deny" style="width:90%;">I won\'t attend</div>';
			}
			else
			{
				//not confirmed, not volunteer gig
				$html = '<div class="btn btn-confirm">Confirm I\'ll Attend</div>';
			}
		}
		else $html = '<span class="label">Attending</span>';
	}
	return $html;
}

//function requestAbsenceButton($eventNo){
	//$absenceRequest = getAbsenceRequest($eventNo, getuser());
	//if($absenceRequest['state'] == 'pending'){
		//return '<td><span class="label label-warning">absence request '.$absenceRequest['state'].'</span></td><td><div class="btn">edit request</div></td>';
	//}
	//if($absenceRequest['state'] == 'confirmed'){
		//return '<td><span class="label label-success">absence request '.$absenceRequest['state'].'</span></td><td></td>';
	//}
	//if($absenceRequest['state'] == 'denied'){
		//return '<td><span class="label label-important">absence request '.$absenceRequest['state'].'</span></td><td><div class="btn">edit request</div></td>';
	//}
	//else{
		//$eventDetails = getEventDetails($_SESSION['eventNo']);
		//$callTime = strtotime($eventDetails['callTime']);
		//if($callTime > time()){
			//return '<div class="btn">request absence</div>';
		//}
	//}
	////print_r($absenceRequest);
//}

function getAbsenceRequest($eventNo, $person){
	$sql = "SELECT * FROM `absencerequest` WHERE eventNo=$eventNo AND memberID='$person';";
	$results = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $results;
}

function shouldAttend($email, $eventNo){
	$sql = "SELECT shouldAttend FROM attends WHERE memberID='$email' AND eventNo=$eventNo;";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['shouldAttend'] == 0 ? false : true;
}

function isConfirmed($email, $eventNo){
	$sql = "SELECT confirmed FROM attends WHERE memberID='$email' AND eventNo=$eventNo;";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['confirmed'] == 0 ? false : true;
}
?>
