<?php
function attendance($member, $semester = "")
{
	global $SEMESTER, $CHOIR;
	if ($semester == '') $semester = $SEMESTER;
	if (! $CHOIR) die("No choir selected");

	$retarr = [];
	$score = 100;
	$gigcount = 0;
	$result = query("select `gigreq` from `semester` where `semester` = ?", [$semester], QONE);
	if (! $result) die("Invalid semester");
	$gigreq = $result['gigreq'];

	$query = query("select `attends`.`eventNo`, `attends`.`shouldAttend`, `attends`.`didAttend`, `attends`.`minutesLate`, `attends`.`confirmed`, UNIX_TIMESTAMP(`event`.`callTime`) as `call`, UNIX_TIMESTAMP(`event`.`releaseTime`) as `release`, `event`.`name`, `event`.`type`, `eventType`.`name` as `typeName`, `event`.`points`, `event`.`gigcount` from `attends`, `event`, `eventType` where `attends`.`memberID` = ? and `event`.`eventNo` = `attends`.`eventNo` and `event`.`releaseTime` <= (current_timestamp - interval 1 day) and `event`.`type` = `eventType`.`id` and `event`.`semester` = ? and `event`.`choir` = ? order by `event`.`callTime` asc", [$member, $semester, $CHOIR], QALL);
	if (count($query) == 0) return array("attendance" => $retarr, "finalScore" => $score, "gigCount" => 0, "gigReq" => $gigreq);

	$allEvents = [];
	foreach ($query as $row)
	{
		// FIXME This method will fail if a semester lasts more than a year.
		$week = intval(date("W", $row["call"]));
		if (! array_key_exists($week, $allEvents)) $allEvents[$week] = [];
		$allEvents[$week][] = $row;
	}
	foreach ($allEvents as $week => $events)
	{
		$reqRehearsals = 0;
		$attRehearsals = 0;
		$reqSectionals = 0;
		$attSectionals = 0;
		foreach ($events as $event)
		{
			$type = $event["type"];
			if (! $type == "rehearsal" && ! $type == "sectional") continue;
			if ($event["shouldAttend"] == "1")
			{
				if ($type == "rehearsal") $reqRehearsals += 1;
				if ($type == "sectional") $reqSectionals += 1;
			}
			if ($event["didAttend"] == "1")
			{
				if ($type == "rehearsal") $attRehearsals += 1;
				if ($type == "sectional") $attSectionals += 1;
			}
		}
		$sectDiff = $attSectionals - $reqSectionals;
		foreach ($events as $event)
		{
			$type = $event["type"];
			$points = $event["points"];
			$shouldAttend = $event["shouldAttend"] == "1";
			$didAttend = $event["didAttend"] == "1";
			$minutesLate = intval($event["minutesLate"]);
			$call = intval($event["call"]);
			$release = intval($event["release"]);
			$tip = "";
			$curgig = false;
			$pointChange = 0;
			if ($didAttend)
			{
				$tip = "No point change for attending required event";
				$bonusEvent = ($type == "volunteer" || $type == "ombuds" || ($type == "other" && ! $shouldAttend) || ($type == "sectional" && $sectDiff > 0));
				// Get back points for volunteer gigs and and extra sectionals and ombuds events
				if ($bonusEvent)
				{
					if ($score + $points > 100)
					{
						$pointChange += 100 - $score;
						$tip = "Event grants $points-point bonus, but grade is capped at 100%";
					}
					else
					{
						$pointChange += $points;
						$tip = "Full bonus awarded for attending volunteer or extra event";
					}
					if ($type == "sectional") $sectDiff -= 1;
				}
				// Lose points equal to the percentage of the event missed, if they should have attended
				if ($minutesLate > 0)
				{
					$effectiveValue = $points;
					if ($pointChange > 0) $effectiveValue = $pointChange;
					$duration = floatval($release - $call) / 60.0;
					$delta = round(floatval($minutesLate) / $duration * $effectiveValue, 2);
					$pointChange -= $delta;
					if ($type == "ombuds") { }
					else if ($bonusEvent)
					{
						$pointChange -= $delta;
						$tip = "Event would grant $effectiveValue-point bonus, but $delta points deducted for lateness";
					}
					else if ($shouldAttend)
					{
						$pointChange -= $delta;
						$tip = "$delta points deducted for lateness to required event";
					}
				}
				// Get gig credit for volunteer gigs if they are applicable
				if ($type == "volunteer" && $event['gigcount'] == '1')
				{
					$gigcount += 1;
					$curgig = true;
				}
				// If you haven't been to rehearsal this week, you can't get points or gig credit
				if ($attRehearsals < $reqRehearsals)
				{
					if ($type == "volunteer")
					{
						$pointChange = 0;
						if ($curgig)
						{
							$gigcount -= 1;
							$curgig = false;
						}
						$tip = "$points-point bonus denied because this week&apos;s rehearsal was missed";
					}
					else if ($type == "tutti")
					{
						$pointChange = -$points;
						$tip = "Full deduction for unexcused absence from this week&apos;s rehearsal";
					}
				}
			}
			// Lose the full point value if did not attend
			else if ($shouldAttend)
			{
				if ($type == "ombuds") $tip = "You do not lose points for missing an ombuds event";
				else if ($type == "sectional" && $sectDiff >= 0) $tip = "No deduction because you attended a different sectional this week";
				else
				{
					$pointChange = -$points;
					$tip = "Full deduction for unexcused absence from event";
					if ($type == "sectional") $sectDiff += 1;
				}
			}
			else $tip = "Did not attend and not expected to";
			$score += $pointChange;
			// Prevent the score from ever rising above 100
			if ($score > 100) $score = 100;
			if ($pointChange > 0) $pointChange = '+' . $pointChange;

			$retarr[] = array("eventNo" => $event["eventNo"], "name" => $event["name"], "date" => $call, "type" => $type, "typeName" => $event["typeName"], "shouldAttend" => $shouldAttend, "didAttend" => $didAttend, "late" => $minutesLate, "pointChange" => $pointChange, "partialScore" => $score, "explanation" => $tip, "gigCount" => $curgig);
		}
		if ($sectDiff != 0) die("Error: sectional offset was $sectDiff");
	}
	// Multiply the top half of the score by the fraction of volunteer gigs attended, if enabled
	$result = query("select `gigCheck` from `variables`", [], QONE);
	if (! $result) die("Could not retrieve variables");
	if ($result['gigCheck']) $score *= 0.5 + min(floatval($gigcount) * 0.5 / $gigreq, 0.5);
	// Bound the final score between 0 and 100
	if ($score > 100) $score = 100;
	if ($score < 0) $score = 0;
	$score = round($score, 2);
	return array("attendance" => $retarr, "finalScore" => $score, "gigCount" => $gigcount, "gigReq" => $gigreq);
}

function attendanceTable($memberID, $officer = false, $semester = "", $media = "normal")
{
	$res = attendance($memberID, $semester);
	/*if ($mode == 0) return $res["finalScore"];
	if ($mode == 3) return $res["gigCount"];
	if ($mode == 4) return $res;*/
	$eventRows = '';
	$tableOpen = '<table>';
	$tableClose = '</table>';
	if ($officer)
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
	else
	{
		$tableOpen = '<table width="100%" id="defaultSidebar" class="table no-highlight table-bordered every-other">';
		$eventRows = '<thead>
			<th><span class="heading">Event</span></th>
			<th><span class="heading">Should have attended?</span></th>
			<th><span class="heading">Did attend?</span></th>
			<th><span class="heading">Point Change</span></th>
		</thead>';
	}
	foreach ($res["attendance"] as $event)
	{
		$eventNo = $event["eventNo"];
		$date = date("D, M j, Y", $event["date"]);
		$eventName = $event["name"];
		$typeName = $event["typeName"];
		$curgig = $event["gigCount"];
		$shouldAttend = $event["shouldAttend"];
		$didAttend = $event["didAttend"];
		$minutesLate = $event["late"];
		$pointChange = $event["pointChange"];
		$tip = $event["tip"];
		$score = $event["partialScore"];
		if ($officer)
		{
			$eventRows .= "<tr><td class='data'><a href='#event:$eventNo'>$eventName</a></td><td class='data'>$date</td><td align='left' class='data'><span " . ($curgig ? "style='color: green'" : "") . ">$typeName</span></td>";
			
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
		else
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
	return $tableOpen . $eventRows . $tableClose;
}
?>
