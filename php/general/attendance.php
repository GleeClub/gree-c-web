<?php
/**** Attendance functions ****/

function balance($member)
{
	$sql = "select sum(amount) as balance from transaction where memberID='$member'";
	$result = mysql_fetch_array(mysql_query($sql));
	$balance = $result['balance'];
	if ($balance == '') $balance = 0;
	return $balance;
}

/**
* Returns attendance info about the event whose eventNo matches $eventNo in the form of rows
**/
function getEventAttendanceRows($eventNo)
{
	global $CUR_SEM;
	$eventRows = "
	<tr class='topRow'>
		<td class='cellwrap'>Name</td>
		<td class='cellwrap'>Attended</td>
		<td class='cellwrap'>Minutes Late</td>
		<td class='cellwrap'>Should Attend</td>
		<td class='cellwrap'>Did Attend</td>
		<td class='cellwrap'>Confirmed</td>
	</tr>";

	$sections = array();
	$choir = getchoir();
	$sect = mysql_query("select `id`, `name` from `sectionType` where `id` > '0' order by `id` asc");
	while ($s = mysql_fetch_array($sect)) $sections[$s["id"]] = $s["name"];
	$unassigned = mysql_num_rows(mysql_query("select * from `member` where `section` = 0"));
	if ($unassigned) $sections[0] = "<span style='color: red'>Not assigned to any section</span>";
	foreach ($sections as $num => $name)
	{
		$eventRows .= "<tr><td colspan=6><b>$name</b></td></tr>";
		$members = mysql_query("select `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = '$CUR_SEM' and `activeSemester`.`choir` = '$choir' and `activeSemester`.`section` = '$num' order by `member`.`lastName` asc");
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
?>
