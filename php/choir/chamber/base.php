<?php
function attendance($email, $mode, $semester = '', $media = 'normal')
{
	// Type:
	// 0 for grade
	// 1 for officer table
	// 2 for member table
	// 3 for gig count
	global $SEMESTER;
	$WEEK = 604800;
	if ($semester == '') $semester = $SEMESTER;
	
	$score = 100;
	$gigcount = 0;
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
	if ($mode == 3) return $gigcount;
	// Bound the final score between 0 and 100
	if ($score > 100) $score = 100;
	if ($score < 0) $score = 0;
	$score = round($score, 2);
	if ($mode == 0) return $score;
	else return $tableOpen . $eventRows . $tableClose;
}

function rosterPropList($type)
{
	global $USER;
	$uber = isUber($USER);
	$cols = array("#" => 10, "Name" => 260, "Section" => 80, "Contact" => 180, "Location" => 200);
	if ($uber || hasPosition($USER, "Treasurer"))
	{
		$cols["Balance"] = 60;
		$cols["Dues"] = 60;
	}
	if ($uber) $cols["Score"] = 60;
	return $cols;
}

function rosterProp($member, $prop)
{
	global $SEMESTER, $CHOIR;
	if (! $CHOIR) die("No choir selected");
	$html = '';
	switch ($prop)
	{
		case "Section":
			$section = mysql_fetch_array(mysql_query("select `sectionType`.`name` from `sectionType`, `activeSemester` where `sectionType`.`id` = `activeSemester`.`section` and `activeSemester`.`choir` = '$CHOIR' and `activeSemester`.`semester` = '$SEMESTER' and `activeSemester`.`member` = '" . $member["email"] . "'"));
			$html .= $section['name'];
			break;
		case "Contact":
			$html .= "<a href='tel:" . $member["phone"] . "'>" . $member["phone"] . "</a><br><a href='mailto:" . $member['email'] . "'>" . $member["email"] . "</a>";
			break;
		case "Location":
			$html .= $member["location"];
			break;
		case "Balance":
			$balance = balance($member['email']);
			if ($balance < 0) $html .= "<span class='moneycell' style='color: red'>$balance</span>";
			else $html .= "<span class='moneycell'>$balance</span>";
			break;
		case "Dues":
			$result = mysql_fetch_array(mysql_query("select sum(`amount`) as `balance` from `transaction` where `memberID` = '" . $member['email'] . "' and `type` = 'dues' and `semester` = '$SEMESTER'"));
			$balance = $result['balance'];
			if ($balance == '') $balance = 0;
			if ($balance >= 0) $html .= "<span class='duescell' style='color: green'>$balance</span>";
			else $html .= "<span class='duescell' style='color: red'>$balance</span>";
			break;
		case "Score":
			if (enrollment($member["email"]) == 'inactive') $grade = "--";
			else $grade = attendance($member["email"], 0);
			$html .= "<span class='gradecell'";
			if (enrollment($member["email"]) == "class" && $grade < 80) $html .= " style=\"color: red\"";
			$html .= ">$grade</span>";
			break;
		default:
			$html .= "???";
			break;
	}
	return $html;
}
?>
