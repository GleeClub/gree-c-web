<?php
require_once('variables.php');
//it would seem you have to connect to the DB from where the function is called, because the variables in variables.php don't get read into the functions here 
//maybe they should be global or something?

/**** Utility functions ****/

function encrypt2($string)
{
	return base64_encode($string ^ "12345678900987654321qwertyuiopasdfghjklzxcvbnm,.");
}

function decrypt2($string)
{
	return base64_decode($string) ^ "12345678900987654321qwertyuiopasdfghjklzxcvbnm,.";
}

function valid_date($string)
{
	$re_date = '/^\s*\d\d\d\d-\d\d-\d\d\s*$/';
	if (! preg_match($re_date, $string)) return false;
	$arr = preg_split('/-/', $string);
	return checkdate($arr[1], $arr[2], $arr[0]);
	return true;
}

function valid_time($string)
{
	$re_time = '/^\s*(1[012]|0?[0-9])(:[0-5][0-9])?\s*[AaPp][Mm]\s*$/';
	return preg_match($re_time, $string);
}

/**** Semester and member info functions ****/

function getCurrentSemester() {
	$sql = "SELECT semester FROM variables";
	$arr = mysql_fetch_array(mysql_query($sql));
	return $arr['semester'];
}

// First "Pref" Last if pref exists && pref != first
function completeNameFromEmail($email) {
	if ($email == '') return '';
	$sql = "SELECT firstName, lastName, prefName FROM `member` WHERE email='$email';";
	$res = mysql_fetch_array(mysql_query($sql));
	if(!empty($res['prefName']) && $res['firstName'] != $res['prefName'])
		return $res['firstName'] . ' "' . $res['prefName'] . '" ' . $res['lastName'];
	else
		return $res['firstName'] . " " . $res['lastName'];
}

function fullNameFromEmail($email) {
	return firstNameFromEmail($email) . " " . lastNameFromEmail($email);
}

function firstNameFromEmail($email){
	if ($email == '') return '';
	$sql = "SELECT firstName FROM `member` WHERE email='$email';";
	$result= mysql_fetch_array(mysql_query($sql));
	return $result["firstName"];
}

function prefNameFromEmail($email){
	if ($email == '') return '';
	$sql = "SELECT prefName FROM `member` WHERE email='$email';";
	$result= mysql_fetch_array(mysql_query($sql));
	if ($result["prefName"] == '') return firstNameFromEmail($email);
	return $result["prefName"];
}

function lastNameFromEmail($email){
	if ($email == '') return '';
	$sql = "SELECT lastName FROM `member` WHERE email='$email';";
	$result= mysql_fetch_array(mysql_query($sql));
	return $result["lastName"];
}

function prefFullNameFromEmail($email){
	return prefNameFromEmail($email).' '.lastNameFromEmail($email);
}

function positionFromEmail($userEmail){
	if ($userEmail == '') return 'None';
	$sql = "SELECT * FROM `member` WHERE email='$userEmail';";
	$result= mysql_fetch_array(mysql_query($sql));
	//print_r($result);
	$type = $result["position"];
	return $type;
}

function emailFromPosition($position){
	//this should be done with numbers!
	$sql = "SELECT email FROM member WHERE position='$position';";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	return $result['email'];
}

function profilePic($email){
	if ($email == '') return '';
        $sql = "SELECT picture FROM member WHERE email='$email';";
        $result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
        return $result['picture'];
}

function sectionFromEmail($email){
	if ($email == '') return 'None';
        $sql = "SELECT section FROM member WHERE email='$email';";
        $result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
        return $result['section'];
}

function randomProfilePic(){
        return "http://placekitten.com/500/400";
}

function isInClass($email){
	if ($email == '') return false;
	$sql = "SELECT registration FROM member WHERE email='$email';";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	if($result['registration'] == '1'){
		return true;
	}
	else{
		return false;
	}
}

function isOfficer($email){
	//this should be done with numbers...
	if(positionFromEmail($email) == "Manager" ||
	positionFromEmail($email) == "VP" ||
	positionFromEmail($email) == "Treasurer" ||
	positionFromEmail($email) == "President" || 
	positionFromEmail($email) == "Liaison" || 
	positionFromEmail($email) == "Webmaster") return true; // Webmaster needs access for debugging
	else return false;
}

function getMemberAttribute($attribute, $email){
        $sql = "SELECT $attribute FROM member WHERE email='$email';";
        $result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
        return $result[$attribute];
}

function memberDropdown()
{
	$html = "<select class='name'><option value=''>(nobody)</option>";
	$sql = "select `firstName`, `lastName`, `email` from `member` order by `lastName` asc";
	$results = mysql_query($sql);
	while ($row = mysql_fetch_array($results))
	{
		$html .= "<option value='" . $row['email'] . "'>" . $row['lastName'] . ", " . $row['firstName'] . "</option>";
	}
	$html .= "</select>";
	return $html;
}

function semesterDropdown()
{
	GLOBAL $CUR_SEM;
	$html = "<select class='semester' style='width: 140px'>";
	$sql = "select `semester` from `validSemester` order by `beginning` desc";
	$results = mysql_query($sql);
	while ($row = mysql_fetch_array($results))
	{
		$html .= "<option value='" . $row['semester'] . "'";
		if ($row['semester'] == $CUR_SEM) $html .= " selected";
		$html .= ">" . $row['semester'] . "</option>";
	}
	$html .= "</select>";
	return $html;
}

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
	if($number == '1'){
		//rehearsal
		$class = 'label-info';
		return $class;
	}
	if($number == '2'){
		//sectional
		$class = 'label-success';
		return $class;
	}
	if($number == '3'){
		//tutti
		$class = 'label-warning';
		return $class;
	}
	if($number == '4'){
		//volunteer
		$class = 'label-important';
		return $class;
	}
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
	
	$sql = "SELECT * FROM `attends` WHERE eventNo=$eventNo AND memberID='".$_COOKIE['email']."';";
	$results = mysql_fetch_array(mysql_query($sql));
	if($results['confirmed'] == '0')
	{
		if($typeNumber == '3')
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
	else
	{
		//not confirmed
		$html = (($results['shouldAttend'] == '1') ? "<span class='label'>Attending</span>" : '<span class="label">Not attending</span>');

		//if it s a volunteer gig, give them the opportunity to change their choice later
		//if($typeNumber == '3' && ! $soon) $html .="<div><br><div class='btn btn-toggle'>I changed my mind</div></div>";
	}
	return $html;
}

function requestAbsenceButton($eventNo){
	$absenceRequest = getAbsenceRequest($eventNo, $_COOKIE['email']);
	if($absenceRequest['state'] == 'pending'){
		return '<td><span class="label label-warning">absence request '.$absenceRequest['state'].'</span></td><td><div class="btn">edit request</div></td>';
	}
	if($absenceRequest['state'] == 'confirmed'){
		return '<td><span class="label label-success">absence request '.$absenceRequest['state'].'</span></td><td></td>';
	}
	if($absenceRequest['state'] == 'denied'){
		return '<td><span class="label label-important">absence request '.$absenceRequest['state'].'</span></td><td><div class="btn">edit request</div></td>';
	}
	else{
		$eventDetails = getEventDetails($_SESSION['eventNo']);
		$callTime = strtotime($eventDetails['callTime']);
		if($callTime > time()){
			return '<div class="btn">request absence</div>';
		}
	}
	//print_r($absenceRequest);
}

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

/**** Message functions ****/

function sendMessageEmail($to, $from, $message, $subjectField = "New Absence Request"){
	$fromEmail = $from;
	$from = prefNameFromEmail($from)." ".lastNameFromEmail($from);
	if(strpos($to, ',')){//being sent to multiple people
		$toField = $to;
	}
	else{//being sent to one person
		$toField = prefNameFromEmail($to)." ".lastNameFromEmail($to)."<".$to.">"; //make it format: Chris Ernst <cernst3@gatech.edu>
	}
	if(!$subjectField){$subjectField = 'Message from '.$from.'!';} //if there's no subject, it's a message...maybe.
	$messageField = '
	<html>
	<head>
		<style>
			.container{
				font-family:"lucida grande",tahoma,verdana,arial,sans-serif;
				height:100%;
				padding:5px;
				margin-bottom:3px;
			}
			body{
				background:rgb(179,179,179);
			}
			h1{
				width:100%;
				margin:auto;
				text-align:center;
				text-decoration:underline;
				margin-bottom:3px;
			}
			.from{
				width:100%;
				margin:auto;
				text-align:center;
			}
			.message{
				border-radius:2px;
				background-color:rgba(255,255,255, .7);
				padding:5px;
				border:1px solid rgba(0, 0, 0, 0.1);
				box-shadow:0 4px 7px rgba(0,0,0, .4);
				margin:5px;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<p class="message">'.$message.'</p>
			<p style="float:right;">-'.$from.'</p>
		</div>
	</body>
	</html>
	';
	//reply-to isn't working, but that seems to be alright because the form field is working.
	$headers = 'Content-type:text/html;\n
				Reply-To: '.$from.' <'.$fromEmail.'>' . "\n" .
				'From: '.$from.' <'.$fromEmail.'>' . "\n" .
				'X-Mailer: PHP/' . phpversion();
	mail($toField, $subjectField, $messageField, $headers);
	//echo $toField." ".$subjectField." ".$messageField." ".$headers;
}

function getNumUnreadMessages($email) {
	$sql = "select sum(newMessages) from convoMembers where email='$email'";
	$res = mysql_fetch_array(mysql_query($sql));
	$newMessages = $res['sum(newMessages)'];
	return $newMessages;
}

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

function todoBlock($userEmail, $form, $list)
{
	$html = '';
	if ($form)
	{
		if(isOfficer($userEmail))
		{
			$html .= "<p>
				Names: <input id='multiTodo'>
				Todo: <br /><input id='todoText'>
				<br /><button class='btn' id='multiTodoButton'>Add Todo</button>
			</p>";

		}
		else
		{
			$html .= "<p>
				<input id='newTodo'>
				<button class='btn' id='newTodoButton'>Add Todo</button>
			</p>";
		}
	}
	if ($list)
	{
		$html .= "<div id='todos'>";
		//$sql = "SELECT * FROM `todoMembers` where memberID='$userEmail' ORDER BY todoID ASC;";
		$sql = "select todo.id, todo.text from `todo`, `todoMembers` where todo.id = todoMembers.todoID and todo.completed = '0' and todoMembers.memberID = '$userEmail' order by todo.id asc";
		$todos = mysql_query($sql);
		while ($row = mysql_fetch_array($todos, MYSQL_ASSOC)){
			$id = $row['id']; //$row['todoID'];
			$text = $row['text']; //$text['text'];
			$html .= "<div class='block'><label class='checkbox'><input type='checkbox' id='$id'> $text</label></div>";
		}
		$html .= "</div>";
	}
	return $html;
}

/**** Attendance functions ****/

function balance($member)
{
	$sql = "select sum(amount) as balance from transaction where memberID='$member'";
	$result = mysql_fetch_array(mysql_query($sql));
	$balance = $result['balance'];
	if ($balance == '') $balance = 0;
	return $balance;
}

function attendance($memberID, $mode)
{
	// Type:
	// 0 for grade
	// 1 for officer table
	// 2 for member table
	global $CUR_SEM, $GIG_REQ;
	$sql = "select attends.eventNo,shouldAttend,didAttend,minutesLate,confirmed,UNIX_TIMESTAMP(callTime) as time,name,typeName,points from attends,event,eventType where attends.memberID='$memberID' and event.eventNo=attends.eventNo and callTime<=current_timestamp and event.type=eventType.typeNo and `event`.`semester`='".getCurrentSemester()."' order by callTime asc";
	$attendses = mysql_query($sql);

	$eventRows = '';
	$tableOpen = '<table>';
	$tableClose = '</table>';
	if ($mode == 1)
	{
		$eventRows = '<thead>
			<th>Event</th>
			<th>Date</th>
			<th>Type</th>
			<th>Should Have<br>Attended</th>
			<th>Did Attend</th>
			<th>Minutes Late</th>
			<th>Point Change</th>
		</thead>';
	}
	else if ($mode == 2)
	{
		$tableOpen = '<table width="100%" id="defaultSidebar" class="table no-highlight table-bordered every-other">';
		$eventRows = '<thead>
			<th><span class="heading">Event</span></th>
			<th><span class="heading">Should have attended?</span></th>
			<th><span class="heading">Did attend?</span></th>
			<th><span class="heading">Point Change</span></th>
		</thead>';
	}
	$score = 100;
	$lastRehearsal = 0;
	//make sure the member has some attends relationships
	if(mysql_num_rows($attendses) == 0)
	{
		if ($mode == 0) return $score;
		else return $tableOpen . $eventRows . $tableClose;
	}
	while($attends = mysql_fetch_array($attendses))
	{
		$eventNo = $attends['eventNo'];
		$eventName = $attends['name'];
		$type = $attends['typeName'];
		$points = $attends['points'];
		$shouldAttend = $attends['shouldAttend'];
		$didAttend = $attends['didAttend'];
		$minutesLate = $attends['minutesLate'];
		$confirmed = $attends['confirmed'];
		$time = $attends['time'];
		$attendsID = "attends_".$memberID."_$eventNo";

		if ($type == "Rehearsal" && $didAttend == 1) $lastRehearsal = $time;
		$pointChange = 0;
		if($didAttend == '1')
		{
			// Get back points for volunteer gigs and missed sectionals and extra sectionals
			if(($type == "Volunteer Gig" || ($type == "Sectional" && $shouldAttend == '0')) && $score < 100)
			{
				if ($score + $points > 100) $pointChange += 100 - $score;
				else $pointChange += $points;
			}
			// If you haven't been to rehearsal in seven days, you can't get points for a volunteer gig
			if (($type == "Volunteer Gig" || $type == "Tutti Gig") && $lastRehearsal != 0 && $lastRehearsal < $time - 604800) $pointChange = 0;
			// Lose points equal to the percentage of the event missed, if they should attend
			if ($minutesLate > 0 && $shouldAttend == '1')
			{
				if ($type == "Rehearsal") $delta = floatval($minutesLate) / 11.0;
				else if ($type == "Sectional") $delta = floatval($minutesLate) / 5.0;
				else
				{
					$sql = "select `callTime`, `releaseTime` from `event` where `eventNo` = '$eventNo'";
					$row = mysql_fetch_array(mysql_query($sql));
					$duration = floatval(strtotime($row['releaseTime']) - strtotime($row['callTime'])) / 60.0;
					$delta = floatval($minutesLate) / $duration;
					if ($type == "Volunteer Gig") $delta *= 10.0;
					else if ($type == "Tutti Gig") $delta *= 35.0;
				}
				$delta = round($delta, 2);
				$pointChange -= $delta;
			}
		}
		// Lose the full point value if did not attend
		else if ($shouldAttend == '1') $pointChange -= $points;
		$score += $pointChange;
		// Prevent the score from ever rising above 100
		if ($score > 100) $score = 100;

		if ($mode == 1)
		{
			//name, date and type of the gig
			$date = date("D, M j, Y",intval($time));
			$eventRows .= "<tr id='$attendsID'><td class='data'>$eventName</td><td class='data'>$date</td><td align='left' class='data'>$type</td>";
			
			if ($shouldAttend) $checked = 'checked';
			else $checked = '';
			$newval = ($shouldAttend + 1) % 2;
			$eventRows .= "<td align='left' class='data'><input type='checkbox' class='attendbutton' data-mode='should' data-event='$eventNo' data-member='$memberID' data-val='$newval' $checked></td>";
			
			if ($didAttend) $checked = 'checked';
			else $checked = '';
			$newval = ($didAttend + 1) % 2;
			$eventRows .= "<td align='left' class='data'><input type='checkbox' class='attendbutton' data-mode='did' data-event='$eventNo' data-member='$memberID' data-val='$newval' $checked></td>";

			$eventRows .= "<td align='left'><input name='attendance-late' type='text' style='width:40px' value='$minutesLate'><button type='button' class='btn attendbutton' style='margin-top: -8px' data-mode='late' data-event='$eventNo' data-member='$memberID'>Go</button></td>";

			//make the point change red if it is negative
			if ($pointChange > 0) $eventRows .= "<td align='left' class='data' style='color: green'>+$pointChange</td>";
			else if ($pointChange < 0) $eventRows .= "<td align='left'  class='data' style='color: red'>$pointChange</td>";
			else $eventRows .= "<td align='left'  class='data'>$pointChange</td>";
			
			$eventRows .= "</tr>";
		}
		else if ($mode == 2)
		{
			$eventRows .= "<tr align='center'><td>$eventName</td><td>";
			if ($shouldAttend == "1") $eventRows .= "<i class='icon-ok'></i>";
			else $eventRows .= "<i class='icon-remove'></i>";
			$eventRows .= "</td><td>";
			if ($didAttend == "1") $eventRows .= "<i class='icon-ok'></i>";
			else $eventRows .= "<i class='icon-remove'></i>";
			$shouldAttend = ($row["shouldAttend"] == "0" ? "<i class='icon-remove'></i>" : "<i class='icon-ok'></i>");
			$eventRows .= "<td>$pointChange</td></tr>";
		}
	}
	$result = mysql_fetch_array(mysql_query("select `gigCheck` from `variables`"));
	if ($result['gigCheck'])
	{
		// Multiply the top half of the score by the fraction of volunteer gigs attended, if enabled
		$query = mysql_query("select `event`.`eventNo` from `attends`, `event` where `attends`.`memberID` = '" . $memberID . "' and `event`.`type` = '3' and `event`.`semester` = '$CUR_SEM' and `attends`.`didAttend` = '1' and `attends`.`eventNo` = `event`.`eventNo`");
		$gigcount = mysql_num_rows($query);
		$score *= 0.5 + min(floatval($gigcount) * 0.5 / $GIG_REQ, 0.5);
	}
	// Bound the final score between 0 and 100
	if ($score > 100) $score = 100;
	if ($score < 0) $score = 0;
	$score = round($score, 2);
	if ($mode == 0) return $score;
	else return $tableOpen . $eventRows . $tableClose;
}

function rosterProp($member, $prop)
{
	global $CUR_SEM, $GIG_REQ, $DEPOSIT;
	$html = '';
	switch ($prop)
	{
		case "Section":
			$html .= $member["section"];
			break;
		case "Contact":
			$html .= $member["phone"] . "<br>" . $member["email"];
			break;
		case "Location":
			$html .= $member["location"];
			break;
		case "Class":
			$html .= ($member["registration"] == 1) ? "<span style=\"color: blue\">class</span>" : "club";
			break;
		case "Balance":
			$balance = balance($member['email']);
			if ($balance < 0) $html .= "<span class='moneycell' style='color: red'>$balance</span>";
			else $html .= "<span class='moneycell'>$balance</span>";
			break;
		case "Dues":
			$result = mysql_fetch_array(mysql_query("select sum(`amount`) as `balance` from `transaction` where `memberID` = '" . $member['email'] . "' and `type` = 'dues' and `semester` = '$CUR_SEM'"));
			$balance = $result['balance'];
			if ($balance == '') $balance = 0;
			if ($balance >= 0) $html .= "<span class='duescell' style='color: green'>$balance</span>";
			else $html .= "<span class='duescell' style='color: red'>$balance</span>";
			break;
		case "Gigs":
			$query = mysql_query("select `event`.`eventNo` from `attends`, `event` where `attends`.`memberID` = '" . $member['email'] . "' and `event`.`type` = '3' and `event`.`semester` = '$CUR_SEM' and `attends`.`didAttend` = '1' and `attends`.`eventNo` = `event`.`eventNo`");
			$gigcount = mysql_num_rows($query);
			if ($gigcount >= $GIG_REQ) $html .= "<span class='gigscell' style='color: green'>";
			else $html .= "<span class='gigscell' style='color: red'>";
			$html .= "$gigcount</span>";
			break;
		case "Grade":
			$grade = attendance($member["email"], 0);
			$html .= "<span class='gradecell'";
			if ($member["registration"] == 1 && $grade < 80) $html .= " style=\"color: red\"";
			$html .= ">$grade</span>";
			break;
		case "Tie":
			$html .= "<span class='tiecell' ";
			$result = mysql_fetch_array(mysql_query("select sum(`amount`) as `amount` from `transaction` where `memberID` = '" . $member['email'] . "' and `type` = 'deposit'"));
			$tieamount = $result['amount'];
			if ($tieamount == '') $tieamount = 0;
			if ($tieamount >= $DEPOSIT) $html .= "style='color: green'";
			else $html .= "style='color: red'";
			$html .= ">";
			$query = mysql_query("select `id` from `tie` where `owner` = '" . $member['email'] . "'");
			if (mysql_num_rows($query) != 0)
			{
				$result = mysql_fetch_array($query);
				$html .= $result['id'];
			}
			else $html .= "•";
			$html .= "</span>";
			break;
		default:
			$html .= "???";
			break;
	}
	return $html;
}

/**
* Returns attendance info about the event whose eventNo matches $eventNo in the form of rows
**/
function getEventAttendanceRows($eventNo)
{
	$eventRows = "
	<tr class='topRow'>
		<td class='cellwrap'>Name</td>
		<td class='cellwrap'>Attended</td>
		<td class='cellwrap'>Minutes Late</td>
		<td class='cellwrap'>Should Attend</td>
		<td class='cellwrap'>Did Attend</td>
		<td class='cellwrap'>Confirmed</td>
	</tr>";

	$sections = mysql_query("select `typeName` from `sectionType` order by `typeNo` asc");
	while ($sect = mysql_fetch_array($sections))
	{
		$section = $sect['typeName'];
		$eventRows .= "<tr><td colspan=6><b>$section</b></td></tr>";
		$members = mysql_query("select `email` from `member` where `confirmed` = '1' and `section` = '$section' order by `lastName` asc");
		while ($member = mysql_fetch_array($members)) $eventRows .= '<tr id="attends_' . $member['email'] . '_' . $eventNo . '">' . getSingleEventAttendanceRow($eventNo, $member['email']) . '</tr>';
	}

	return $eventRows;
}

function getEventTypes()
{
	$sql = "select * from eventType";
	return mysql_query($sql);
}

/**
* Returns attendance info about the event for one member in the style used on the "update attendance" form
**/
function getSingleEventAttendanceRow($eventNo, $memberID)
{
	$query = mysql_query("select * from `member`, `attends` where `email` = '$memberID' and `email` = `memberID` and `eventNo` = '$eventNo'");
	if (mysql_num_rows($query) != 1)
	{
		$member = mysql_fetch_array(mysql_query("select * from `member` where `email` = '$memberID'"));
		$firstName = $member['firstName'];
		$lastName = $member['lastName'];
		$shouldAttend = 0;
		$didAttend = 0;
		$minutesLate = 0;
		$confirmed = 0;
	}
	else
	{
		$member = mysql_fetch_array($query);
		$firstName = $member['firstName'];
		$lastName = $member['lastName'];
		$shouldAttend = $member['shouldAttend'];
		$didAttend = $member['didAttend'];
		$minutesLate = $member['minutesLate'];
		$confirmed = $member['confirmed'];
	}

	//the member's name
	$eventRow .= "<td class='data'>$firstName $lastName</td>";

	//did the person attend
	if ($didAttend == "1") $eventRow .= "<td id='$attendsID_did' align='center' class='data'><font color='green'>Yes</font></td>";
	else if ($shouldAttend=="1") $eventRow .= "<td id='$attendsID_did' align='center' class='data'><font color='red'>No</font></td>";
	else $eventRow .= "<td align='center' class='data'>No</td>";

	//minutes late
	$eventRow .= "<td align='center' class='data'><input type='text' placeholder='$minutesLate' class='input-mini' id='$memberID-minutesLate' style='width: 40px; margin-bottom: 0px' /><button type='button' class='btn' onclick='setMinutesLate(\"$eventNo\", \"$memberID\", \$(this).parent().children(\".input-mini\").prop(\"value\"))'>Go</button></td>";

	//add a button to change the whether the person should attend
	$eventRow .= "<td align='center' class='data'><input type='checkbox' onclick='setShouldAttendEvent(\"$eventNo\", \"$memberID\", " . (($shouldAttend + 1) % 2) . ")'" . ($shouldAttend ? ' checked' : '') . "></td>";

	//add a button to change whether the person did attend
	$eventRow .= "<td align='center' class='data'><input type='checkbox' onclick='setDidAttendEvent(\"$eventNo\", \"$memberID\", " . (($didAttend + 1) % 2) . ")'" . ($didAttend ? ' checked' : '') . "></td>";

	//confirmed
	$eventRow .= "<td align='center' class='data'><input type='checkbox' onclick='setConfirmed(\"$eventNo\", \"$memberID\", " . (($confirmed + 1) % 2) . ")'" . ($confirmed ? ' checked' : '') . "></td>";

	return $eventRow;
}

/**** Misc ****/

// Delete the file with a given ID from the repertoire repository
function repertoire_delfile($id)
{
	GLOBAL $docroot;
	GLOBAL $musicdir;
	$query = "select `target`, `type` from `songLink` where `id` = '$id'";
	$result = mysql_fetch_array(mysql_query($query));
	$file = urldecode($result[0]);
	if ($file == '') return true;
	$type = $result[1];
	$query = "select `storage` from `mediaType` where `typeid` = '$type'";
	$result = mysql_fetch_array(mysql_query($query));
	if ($result[0] != 'local');
	if (! preg_match('/^' . $musicdir . '/', $file) || preg_match('/\/\.\./', $file)); // FIXME
	unlink($docroot . $file);
	return true;
}

function loginBlock(){
$html = '
	<div class="span3 block">
		<form class="form-inline" action="php/checkLogin.php" method="post">
		  <input type="text" class="input-medium" id="signInEmail" placeholder="gburdell3@gatech.edu" name="email" />
		  <input type="password" class="input-medium" id="signInPassword" placeholder="password" name="password" />
		  <button type="submit" value="Sign In" class="btn">Sign in</button>
		</form>
		<a href="#forgotPassword">Forgot Password?</a>
	</div>
';
echo $html;
}
?>
