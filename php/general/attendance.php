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

function attendance($memberID, $mode, $semester = '', $media = 'normal')
{
	// Type:
	// 0 for grade
	// 1 for officer table
	// 2 for member table
	// 3 for gig count
	global $CUR_SEM;
	$WEEK = 604800;
	if ($semester == '') $semester = $CUR_SEM;
	$sql = "select attends.eventNo,shouldAttend,didAttend,minutesLate,confirmed,UNIX_TIMESTAMP(callTime) as time,name,typeName,points from attends,event,eventType where attends.memberID='$memberID' and event.eventNo=attends.eventNo and callTime<=current_timestamp and event.type=eventType.typeNo and `event`.`semester`='$semester' order by callTime asc";
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
			<th>Partial Score</th>
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
	$gigcount = 0;
	$lastRehearsal = 0;
	$lastAttendedRehearsal = 0;
	$result = mysql_fetch_array(mysql_query("select `gigreq` from `semester` where `semester` = '$CUR_SEM'"));
	$gigreq = $result['gigreq'];
	//make sure the member has some attends relationships
	if(mysql_num_rows($attendses) == 0)
	{
		if ($mode == 0) return $score;
		else if ($mode == 3) return $gigcount;
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
		$tip = "";
		$curgig = 0;
		$event = mysql_fetch_array(mysql_query("select * from `event` where `eventNo` = '$eventNo'"));

		if ($type == "Rehearsal")
		{
			$lastRehearsal = $time;
			if ($didAttend == 1 || $shouldAttend == 0) $lastAttendedRehearsal = $time;
		}
		$pointChange = 0;
		if($didAttend == '1')
		{
			$tip = "No point change for attending required event";
			// Get back points for volunteer gigs and missed sectionals and extra sectionals
			if(($type == "Volunteer Gig" || ($type == "Sectional" && $shouldAttend == '0')))
			{
				if ($score + $points > 100)
				{
					$pointChange += 100 - $score;
					$tip = "Event grants $points-point bonus, but grade is capped at 100%";
				}
				else
				{
					$pointChange += $points;
					$tip = "Full bonus awarded for attending volunteer event";
				}
			}
			// Get gig credit for volunteer gigs if they are applicable
			if ($type == "Volunteer Gig" && $event['gigcount'] == '1')
			{
				$gigcount += 1;
				$curgig = 1;
			}
			// Lose points equal to the percentage of the event missed, if they should have attended
			if ($minutesLate > 0)
			{
				if ($shouldAttend == '1')
				{
					if ($type == "Rehearsal") $delta = floatval($minutesLate) / 110 * 10;
					else if ($type == "Sectional") $delta = floatval($minutesLate) / 50 * 5;
					else
					{
						$sql = "select `callTime`, `releaseTime` from `event` where `eventNo` = '$eventNo'";
						$row = mysql_fetch_array(mysql_query($sql));
						$duration = floatval(strtotime($row['releaseTime']) - strtotime($row['callTime'])) / 60.0;
						$delta = floatval($minutesLate) / $duration * $points;
					}
					$delta = round($delta, 2);
					$pointChange -= $delta;
					if ($type == "Volunteer Gig") $tip = "Event would grant $points-point bonus, but $delta points deducted for lateness";
					else $tip = "$delta points deducted for lateness to required event";
				}
				else $tip = "No points deducted for coming late to an event with excused absence";
			}
			// If you haven't been to rehearsal in seven days, you can't get points or gig credit
			if ($lastRehearsal > $time - $WEEK && $lastAttendedRehearsal < $time - $WEEK)
			{
				if ($type == "Volunteer Gig")
				{
					$pointChange = 0;
					if ($curgig)
					{
						$gigcount -= 1;
						$curgig = 0;
					}
					$tip = "$points-point bonus denied because this week&apos;s rehearsal was missed";
				}
				else if ($type == "Tutti Gig")
				{
					$pointChange = -$points;
					$tip = "Full deduction for unexcused absence from this week&apos;s rehearsal";
				}
			}
		}
		// Lose the full point value if did not attend
		else if ($shouldAttend == '1')
		{
			$pointChange = -$points;
			$tip = "Full deduction for unexcused absence from event";
		}
		else $tip = "Did not attend and not expected to";
		$score += $pointChange;
		// Prevent the score from ever rising above 100
		if ($score > 100) $score = 100;
		if ($pointChange > 0) $pointChange = '+' . $pointChange;

		if ($mode == 1)
		{
			//name, date and type of the gig
			$date = date("D, M j, Y",intval($time));
			$eventRows .= "<tr id='$attendsID'><td class='data'><a href='#event:$eventNo'>$eventName</a></td><td class='data'>$date</td><td align='left' class='data'><span " . ($curgig ? "style='color: green'" : "") . ">$type</span></td>";
			
			if ($shouldAttend) $checked = 'checked';
			else $checked = '';
			$newval = ($shouldAttend + 1) % 2;
			if ($media == 'print') $eventRows .= "<td style='text-align: center' class='data'>" . ($shouldAttend ? "Y" : "N") . "</td>";
			else $eventRows .= "<td style='text-align: center' class='data'><input type='checkbox' class='attendbutton' data-mode='should' data-event='$eventNo' data-member='$memberID' data-val='$newval' $checked></td>";
			
			if ($didAttend) $checked = 'checked';
			else $checked = '';
			$newval = ($didAttend + 1) % 2;
			if ($media == 'print') $eventRows .= "<td style='text-align: center' class='data'>" . ($didAttend ? "Y" : "N") . "</td>";
			else $eventRows .= "<td style='text-align: center' class='data'><input type='checkbox' class='attendbutton' data-mode='did' data-event='$eventNo' data-member='$memberID' data-val='$newval' $checked></td>";

			if ($media == 'print') $eventRows .= "<td style='text-align: center'>$minutesLate</td>";
			else $eventRows .= "<td style='text-align: center'><input name='attendance-late' type='text' style='width:40px' value='$minutesLate'><button type='button' class='btn attendbutton' style='margin-top: -8px' data-mode='late' data-event='$eventNo' data-member='$memberID'>Go</button></td>";

			//make the point change red if it is negative
			if ($pointChange > 0) $eventRows .= "<td style='text-align: center' class='data' style='color: green'>";
			else if ($pointChange < 0) $eventRows .= "<td style='text-align: center'  class='data' style='color: red'>";
			else $eventRows .= "<td style='text-align: center' class='data'>";
			$eventRows .= "<a href='#' class='gradetip' data-toggle='tooltip' data-placement='right' style='color: inherit; text-decoration: none' onclick='return false' title='$tip'>$pointChange</a></td>";

			if ($pointChange != 0) $eventRows .= "<td style='text-align: center' class='data'>$score</td>";
			else $eventRows .= "<td style='text-align: center' class='data'></td>";

			$eventRows .= "</tr>";
		}
		else if ($mode == 2)
		{
			$eventRows .= "<tr align='center'><td><a href='#event:$eventNo'>$eventName</a></td><td>";
			if ($shouldAttend == "1") $eventRows .= "<i class='icon-ok'></i>";
			else $eventRows .= "<i class='icon-remove'></i>";
			$eventRows .= "</td><td>";
			if ($didAttend == "1") $eventRows .= "<i class='icon-ok'></i>";
			else $eventRows .= "<i class='icon-remove'></i>";
			$shouldAttend = ($shouldAttend == "0" ? "<i class='icon-remove'></i>" : "<i class='icon-ok'></i>");
			$eventRows .= "<td><a href='#' class='gradetip' data-toggle='tooltip' data-placement='right' style='color: inherit; text-decoration: none' onclick='return false' title='$tip'>$pointChange</a></td></tr>";
		}
	}
	if ($mode == 3) return $gigcount;
	$result = mysql_fetch_array(mysql_query("select `gigCheck` from `variables`"));
	// Multiply the top half of the score by the fraction of volunteer gigs attended, if enabled
	if ($result['gigCheck']) $score *= 0.5 + min(floatval($gigcount) * 0.5 / $gigreq, 0.5);
	// Bound the final score between 0 and 100
	if ($score > 100) $score = 100;
	if ($score < 0) $score = 0;
	$score = round($score, 2);
	if ($mode == 0) return $score;
	else return $tableOpen . $eventRows . $tableClose;
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
	$sect = mysql_query("select `typeNo`, `typeName` from `sectionType` where `typeNo` > '0' order by `typeNo` asc");
	while ($s = mysql_fetch_array($sect)) $sections[$s["typeNo"]] = $s["typeName"];
	$unassigned = mysql_num_rows(mysql_query("select * from `member` where `section` = 0"));
	if ($unassigned) $sections[0] = "<span style='color: red'>Not assigned to any section</span>";
	foreach ($sections as $num => $name)
	{
		$eventRows .= "<tr><td colspan=6><b>$name</b></td></tr>";
		$members = mysql_query("select `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = '$CUR_SEM' and `member`.`section` = '$num' order by `member`.`lastName` asc");
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
