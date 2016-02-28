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

function rosterProp($member, $prop)
{
	global $CUR_SEM;
	$html = '';
	switch ($prop)
	{
		case "Section":
			$section = mysql_fetch_array(mysql_query("select `typeName` from `sectionType` where `typeNo` = '" . $member["section"] . "'"));
			$html .= $section['typeName'];
			break;
		case "Contact":
			$html .= "<a href='tel:" . $member["phone"] . "'>" . $member["phone"] . "</a><br><a href='mailto:" . $member['email'] . "'>" . $member["email"] . "</a>";
			break;
		case "Location":
			$html .= $member["location"];
			break;
		case "Enrollment":
			$enr = enrollment($member["email"]);
			if ($enr == "class") $html .= "<span style=\"color: blue\">class</span>";
			else if ($enr == "club") $html .= "club";
			else $html .= "<span style=\"color: gray\">inactive</span>";
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
			$gigcount = attendance($member["email"], 3);
			$result = mysql_fetch_array(mysql_query("select `gigreq` from `semester` where `semester` = '$CUR_SEM'"));
			$gigreq = $result['gigreq'];
			if ($gigcount >= $gigreq) $html .= "<span class='gigscell' style='color: green'>";
			else $html .= "<span class='gigscell' style='color: red'>";
			$html .= "$gigcount</span>";
			break;
		case "Score":
			if (enrollment($member["email"]) == 'inactive') $grade = "--";
			else $grade = attendance($member["email"], 0);
			$html .= "<span class='gradecell'";
			if (enrollment($member["email"]) == "class" && $grade < 80) $html .= " style=\"color: red\"";
			$html .= ">$grade</span>";
			break;
		case "Tie":
			$html .= "<span class='tiecell' ";
			$result = mysql_fetch_array(mysql_query("select sum(`amount`) as `amount` from `transaction` where `memberID` = '" . $member['email'] . "' and `type` = 'deposit'"));
			$tieamount = $result['amount'];
			if ($tieamount == '') $tieamount = 0;
			if ($tieamount >= fee("tie")) $html .= "style='color: green'";
			else $html .= "style='color: red'";
			$html .= ">";
			$query = mysql_query("select `tie` from `tieBorrow` where `member` = '" . $member['email'] . "' and `dateIn` is null");
			if (mysql_num_rows($query) != 0)
			{
				$result = mysql_fetch_array($query);
				$html .= $result['tie'];
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
	$sect = mysql_query("select `typeNo`, `typeName` from `sectionType` where `typeNo` > '0' order by `typeNo` asc");
	while ($s = mysql_fetch_array($sect)) $sections[$s["typeNo"]] = $s["typeName"];
	$unassigned = mysql_num_rows(mysql_query("select * from `member` where `section` = 0"));
	if ($unassigned) $sections[0] = "<span style='color: red'>Not assigned to any section</span>";
	foreach ($sections as $num => $name)
	{
		$eventRows .= "<tr><td colspan=6><b>$name</b></td></tr>";
		$members = mysql_query("select `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = '$CUR_SEM' and `choir` = '$choir' and `member`.`section` = '$num' order by `member`.`lastName` asc");
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
