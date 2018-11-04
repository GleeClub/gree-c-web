<?php
require_once('functions.php');

$eventNo = $_POST['id'];
$event = array();
$hasValue = false;
if ($eventNo)
{
	if (! hasEventPermission("modify", $eventNo)) die("DENIED");
	$hasValue = true;
	$eventresult = query("select * from `event` where `eventNo` = ?", [$eventNo], QONE);
	if (! $eventresult) die("No such event exists");
	$gigresult = query("select * from `gig` where `eventNo` = ?", [$eventNo], QONE);
	// if (! $gigresult) die("That event is not a gig"); // FIXME Should we check for a null gig result?
	$event = (array) $eventresult + (array) $gigresult;
}
else if (! hasEventTypePermission("create")) die("DENIED");
else if (isset($_POST["gigreq"]))
{
	$hasValue = true;
	$event = query("select * from `gigreq` where `id` = ?", [$_POST["gigreq"]], QONE);
	if (! $event) die("No such gig request exists");
	$event["callTime"] = date("Y-m-d H:i:s", strtotime($row["startTime"]) - 30 * 60);
	$event["releaseTime"] = date("Y-m-d H:i:s", strtotime($row["startTime"]) + 60 * 60);;
	$event["performanceTime"] = $row["startTime"];
	$event["points"] = "10";
	$event["type"] = "volunteer";
	$event["semester"] = $SEMESTER;
	$event["gigcount"] = 1;
	$event["section"] = 0;
	$event["uniform"] = "jeans";
	$event["price"] = 0;
	$event["public"] = 0;
	$event["summary"] = "";
	$event["description"] = "";
}

function value($field)
{
	GLOBAL $event;
	if ($field == 'name' || $field == 'type' || $field == 'location' || $field == 'semester' || $field == 'comments' || $field == 'points' || $field == 'uniform' || $field == 'price' || $field == 'public' || $field == 'summary' || $field == 'description' || $field == 'cname' || $field == 'cphone' || $field == 'cemail' || $field == 'gigcount' || $field == 'defaultAttend') return $event[$field];
	if ($field == 'repeat' || $field == 'until') return '';
	$call = strtotime($event['callTime']);
	$done = strtotime($event['releaseTime']);
	$perf = strtotime($event['performanceTime']);
	if ($field == 'calldate') return date("Y-m-d", $call);
	if ($field == 'calltime') return date("h:i A", $call);
	if ($field == 'donedate') return date("Y-m-d", $done);
	if ($field == 'donetime') return date("h:i A", $done);
	if ($field == 'perftime') return date("h:i A", $perf);
	return '???';
}

// Fear my wrath, for I am lord of the data structures.
$fields = array(
	'general' => array(
		array('name', 'Event Name', 'text'),
		array('type', 'Event Type', '.type'),
		array('calldate', 'Call Date', 'date'),
		array('calltime', 'Call Time', 'time'),
		array('donedate', 'Release Date', 'date'),
		array('donetime', 'Release Time', 'time'),
		array('location', 'Location', 'text'),
		array('points', 'Points', 'number'),
		array('semester', 'Semester', 'select', semesters(), $SEMESTER, false),
		array('comments', 'Comments', 'textarea'),
		array('repeat', 'Repeat', 'select', array('no' => 'no', 'daily' => 'daily', 'weekly' => 'weekly', 'biweekly' => 'biweekly', 'monthly' => 'monthly', 'yearly' => 'yearly'), 'no', $eventNo),
		array('until', 'Repeat Until', 'date'),
		array('defaultAttend', 'Require members to attend by default', 'bool', true),
	),
	'gig' => array(
		array('uniform', 'Uniform', 'select', uniforms(), value('uniform'), false),
		array('perftime', 'Performance Time', 'time'),
		array('cname', 'Contact Name', 'text'),
		array('cemail', 'Contact Email', 'text'),
		array('cphone', 'Contact Phone', 'text'),
		array('price', 'Price', 'number'),
		array('gigcount', 'Count Toward Volunteer Gig Requirement', 'bool'),
		array('public', 'Public Event', 'bool'),
		array('summary', 'Public Summary', 'textarea'),
		array('description', 'Public Description', 'textarea'),
	),
	'rehearsal' => array(
		array('section', 'Section', 'select', sections(), value('section'), $eventNo),
	),
);
	
$html = '';
foreach ($fields as $category => $catfields)
{
	$html .= "<table id='event_$category' class='table no-highlight no-border' style='width: 100%; display: none'><style>select { width: 200px; }</style>";
	foreach ($catfields as $field)
	{
		$html .= "<tr id='event_row_" . $field[0] . "'><td>" . $field[1] . "</td><td style='text-align: right'>";
		$value = '';
		if ($hasValue) $value = htmlspecialchars(value($field[0]), ENT_QUOTES);
		switch ($field[2])
		{
			case 'text':
				$html .= "<input type='text' name='$field[0]' value='$value' style='width: 200px'>";
				break;
			case 'textarea':
				$html .= "<textarea name='$field[0]' style='width: 200px; height: 60px'>$value</textarea>";
				break;
			case 'number':
				$html .= "<input type='text' name='$field[0]' style='width: 60px' value='$value' onkeyup='$(this).prop(\"value\", $(this).prop(\"value\").replace(/[^0-9]/g, \"\"))'>";
				break;
			case 'bool':
				$html .= "<input type='checkbox' name='$field[0]'";
				if ($hasValue ? $value : $field[3]) $html .= ' checked';
				$html .= ">";
				break;
			case 'date':
				if ($value == '') $value = date('Y-m-d');
				$html .= "<input type='text' name='$field[0]' value='$value' style='width: 100px' data-date-format='yyyy-mm-dd' data-date='$value'>";
				break;
			case 'time':
				$html .= "<input type='text' name='$field[0]' value='$value' style='width: 80px' placeholder='0:00 PM'>";
				break;
			case 'select':
				$html .= dropdown($field[3], $field[0], $field[4], $field[5]);
				break;
			// The special cases
			case '.type':
				$html .= "<select name='type' style='width: 200px'";
				if ($eventNo && $value != 'volunteer' && $value != 'tutti') $html .= ' disabled';
				$html .= ">";
				foreach (eventTypes() as $id => $name)
				{
					if ($eventNo && ($value == 'volunteer' || $value == 'tutti') && $id != 'volunteer' && $id != 'tutti') continue;
					$html .= "<option value='" . $id . "'";
					if ($id == $value) $html .= ' selected';
					$html .= ">" . $name . "</option>";
				}
				$html .= "</select>";
				break;
			default:
				$html .= "????";
				break;
		}
		$html .= "</td></tr>";
	}
	$html .= "</table>";
}
echo $html;

