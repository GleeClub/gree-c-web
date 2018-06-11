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
	global $SEMESTER, $CHOIR;
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
	$sect = mysql_query("select `id`, `name` from `sectionType` where `id` > '0' and `choir` = '$CHOIR' order by `id` asc");
	while ($s = mysql_fetch_array($sect)) $sections[$s["id"]] = $s["name"];
	$unassigned = mysql_num_rows(mysql_query("select * from `activeSemester` where `semester` = '$SEMESTER' and `choir` = '$CHOIR' and `section` = 0"));
	if ($unassigned) $sections[0] = "Not assigned to any section";
	foreach ($sections as $num => $name)
	{
		$eventRows .= "<tr><td colspan=6><b>$name</b></td></tr>";
		$members = mysql_query("select `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = '$SEMESTER' and `activeSemester`.`choir` = '$CHOIR' and `activeSemester`.`section` = '$num' order by `member`.`lastName` asc");
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

	$editable = " disabled";
	if (hasEventPermission("edit-attendance", $eventNo)) $editable = "";

	//minutes late
	$eventRow .= "<td align='center' class='data'><input type='text' placeholder='$minutesLate' class='input-mini' id='$memberID-minutesLate' style='width: 40px; margin-bottom: 0px' /><button type='button' class='btn' onclick='setMinutesLate(\"$eventNo\", \"$memberID\", \$(this).parent().children(\".input-mini\").prop(\"value\"))' $editable>Go</button></td>";

	//add a button to change the whether the person should attend
	$eventRow .= "<td align='center' class='data'><input type='checkbox' onclick='setShouldAttendEvent(\"$eventNo\", \"$memberID\", " . (($shouldAttend + 1) % 2) . ")'" . ($shouldAttend ? ' checked' : '') . "$editable></td>";

	//add a button to change whether the person did attend
	$eventRow .= "<td align='center' class='data'><input type='checkbox' onclick='setDidAttendEvent(\"$eventNo\", \"$memberID\", " . (($didAttend + 1) % 2) . ")'" . ($didAttend ? ' checked' : '') . "$editable></td>";

	//confirmed
	$eventRow .= "<td align='center' class='data'><input type='checkbox' onclick='setConfirmed(\"$eventNo\", \"$memberID\", " . (($confirmed + 1) % 2) . ")'" . ($confirmed ? ' checked' : '') . "$editable></td>";

	return $eventRow;
}

// Takes care of updating sectional attendance when section is changed
// Assumes that the member has been confirmed active
function updateSection($member, $semester, $choir, $section, $new = false)
{
	if (! mysql_query("update `activeSemester` set `section` = '$section' where `member` = '$member' and `semester` = '$semester' and `choir` = '$choir'")) goto fail; // Change their section registration in activeSemester
	if (! mysql_query("delete from `attends` where `memberID` = '$member' and `eventNo` in (select `eventNo` from `event` where `type` = 'sectional' and `choir` = '$choir' and `semester` = '$semester') and (select `callTime` from `event` where `event`.`eventNo` = `attends`.`eventNo`) > current_timestamp")) goto fail; // Delete attends for all future sectionals
	if (! mysql_query("insert into `attends` (`memberID`, `shouldAttend`, `confirmed`, `eventNo`) select '$member', '1', '0', `eventNo` from `event` where `semester` = '$semester' and `choir` = '$choir' and `type` = 'sectional' and (`section` = '$section' or `section` = 0) and `callTime` > current_timestamp")) goto fail; // Add attends for future sectionals in new section
	//if ($new)
	//{
	//	if (! mysql_query("insert into `attends` (`memberID`, `shouldAttend`, `confirmed`, `eventNo`) select '$member', '1', '0', `eventNo` from `event` where `semester` = '$semester' and `choir` = '$choir' and (`type` = 'rehearsal' or `type` = 'volunteer' or `type` = 'tutti')")) goto fail; // Add attends for non-sectional events
	//	if (! mysql_query("insert into `attends` (`memberID`, `shouldAttend`, `confirmed`, `eventNo`) select '$member', '1', '0', `eventNo` from `event` where `semester` = '$semester' and `choir` = '$choir' and `type` = 'sectional' and `section` = '$section' and `callTime` <= current_timestamp")) goto fail; // Also add attends for past sectionals in current section
	//}
	return "";
fail:	return mysql_error();
}

?>
