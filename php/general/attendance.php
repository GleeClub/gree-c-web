<?php
/**** Attendance functions ****/

function balance($member)
{
	$balance = query("select sum(`amount`) as `balance` from `transaction` where `memberID` = ?", [$member], QONE)["balance"];
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
	foreach(query("select `id`, `name` from `sectionType` where `id` > '0' and `choir` = ? order by `id` asc", [$CHOIR], QALL) as $s) $sections[$s["id"]] = $s["name"];
	$unassigned = query("select * from `activeSemester` where `semester` = ? and `choir` = ? and `section` = ?", [$SEMESTER, $CHOIR, 0], QCOUNT);
	if ($unassigned) $sections[0] = "Not assigned to any section";
	foreach ($sections as $num => $name)
	{
		$eventRows .= "<tr><td colspan=6><b>$name</b></td></tr>";
		foreach(query("select `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = ? and `activeSemester`.`choir` = ? and `activeSemester`.`section` = ? order by `member`.`lastName` asc", [$SEMESTER, $CHOIR, $num], QALL) as $member)
			$eventRows .= '<tr id="attends_' . $member['email'] . '_' . $eventNo . '">' . getSingleEventAttendanceRow($eventNo, $member['email']) . '</tr>';
	}

	return $eventRows;
}

/**
* Returns attendance info about the event for one member in the style used on the "update attendance" form
**/
function getSingleEventAttendanceRow($eventNo, $memberID)
{
	$member = query("select * from `member`, `attends` where `email` = ? and `email` = `memberID` and `eventNo` = ?", [$memberID, $eventNo], QONE);
	if (! $member)
	{
		$member = query("select * from `member` where `email` = ?", [$memberID], QONE);
		if (! $member) die("No such member exists");
		$firstName = $member['firstName'];
		$lastName = $member['lastName'];
		$shouldAttend = 0;
		$didAttend = 0;
		$minutesLate = 0;
		$confirmed = 0;
	}
	else
	{
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
	$err = NULL;
	if (! $err) $err = query("update `activeSemester` set `section` = ? where `member` = ? and `semester` = ? and `choir` = ?", [$section, $member, $semester, $choir], QERR); // Change their section registration in activeSemester
	if (! $err) $err = query("delete from `attends` where `memberID` = ? and `eventNo` in (select `eventNo` from `event` where `type` = 'sectional' and `choir` = ? and `semester` = ?) and (select `callTime` from `event` where `event`.`eventNo` = `attends`.`eventNo`) > current_timestamp", [$member, $choir, $semester], QERR); // Delete attends for all future sectionals
	if (! $err) $err = query("insert into `attends` (`memberID`, `shouldAttend`, `confirmed`, `eventNo`) select ?, `defaultAttend`, '0', `eventNo` from `event` where `semester` = ? and `choir` = ? and `type` = 'sectional' and (`section` = ? or `section` = 0) and `callTime` > current_timestamp", [$member, $semester, $choir, $section], QERR); // Add attends for future sectionals in new section
	//if ($new)
	//{
	//	if (! $err) $err = query("insert into `attends` (`memberID`, `shouldAttend`, `confirmed`, `eventNo`) select ?, '1', '0', `eventNo` from `event` where `semester` = ? and `choir` = ? and (`type` = 'rehearsal' or `type` = 'volunteer' or `type` = 'tutti')", [$member, $semester, $choir], QERR); // Add attends for non-sectional events
	//	if (! $err) $err = query("insert into `attends` (`memberID`, `shouldAttend`, `confirmed`, `eventNo`) select ?, '1', '0', `eventNo` from `event` where `semester` = ? and `choir` = ? and `type` = 'sectional' and `section` = ? and `callTime` <= current_timestamp", [$member, $semester, $choir, $section], QERR); // Also add attends for past sectionals in current section
	//}
	return $err;
}

?>
