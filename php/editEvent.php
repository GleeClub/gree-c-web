<?php
require_once('functions.php');
$userEmail = getuser();

if (! isOfficer($userEmail)) die("DENIED");

$eventNo = mysql_real_escape_string($_POST['id']);
$event = array();
// TODO validate eventNo
if ($eventNo)
{
	$eventresult = mysql_fetch_array(mysql_query("select * from `event` where `eventNo` = '$eventNo'"));
	$gigresult = mysql_fetch_array(mysql_query("select * from `gig` where `eventNo` = '$eventNo'"));
	$event = (array) $eventresult + (array) $gigresult;
}

function value($field)
{
	GLOBAL $event;
	if ($field == 'name' || $field == 'type' || $field == 'location' || $field == 'semester' || $field == 'comments' || $field == 'points' || $field == 'uniform' || $field == 'price' || $field == 'public' || $field == 'summary' || $field == 'description' || $field == 'cname' || $field == 'cphone' || $field == 'cemail' || $field == 'gigcount') return $event[$field];
	if ($field == 'repeat' || $field == 'until') return '';
	$call = strtotime($event['callTime']);
	$done = strtotime($event['releaseTime']);
	$perf = strtotime($event['performanceTime']);
	if ($field == 'calldate') return date("Y-m-d", $call);
	if ($field == 'calltime') return date("h:i A", $call);
	if ($field == 'donedate') return date("Y-m-d", $done);
	if ($field == 'donetime') return date("h:i A", $done);
	if ($field == 'perftime') return date("h:i A", $perf);
	return '????';
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
		array('semester', 'Semester', '.semester'),
		array('comments', 'Comments', 'textarea'),
		array('repeat', 'Repeat', '.repeat'),
		array('until', 'Repeat Until', 'date')
	),
	'gig' => array(
		array('uniform', 'Uniform', '.uniform'),
		array('perftime', 'Performance Time', 'time'),
		array('cname', 'Contact Name', 'text'),
		array('cemail', 'Contact Email', 'text'),
		array('cphone', 'Contact Phone', 'text'),
		array('price', 'Price', 'number'),
		array('gigcount', 'Count Toward Volunteer Gig Requirement', 'bool'),
		array('public', 'Public Event', 'bool'),
		array('summary', 'Public Summary', 'textarea'),
		array('description', 'Public Description', 'textarea')
	),
	'rehearsal' => array(
		array('section', 'Section', '.section')
	)
);
	
$html = '';
foreach ($fields as $category => $catfields)
{
	$html .= "<table id='event_$category' class='table no-highlight no-border' style='width: 100%; display: none'>";
	foreach ($catfields as $field)
	{
		$html .= "<tr id='event_row_" . $field[0] . "'><td>" . $field[1] . "</td><td style='text-align: right'>";
		if (gettype($field[2]) == 'array')
		{
			$html .= "<select name='$field[0]' style='width: 200px'>";
			foreach ($field[2] as $value => $name) $html .= "<option value='$value'>$name</option>";
			$html .= "</select>";
		}
		else if (gettype($field[2]) == 'string')
		{
			$value = '';
			if ($eventNo) $value = htmlspecialchars(value($field[0]), ENT_QUOTES);
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
					if ($value) $html .= ' checked';
					$html .= ">";
					break;
				case 'date':
					if ($value == '') $value = date('Y-m-d');
					$html .= "<input type='text' name='$field[0]' value='$value' style='width: 100px' data-date-format='yyyy-mm-dd' data-date='$value'>";
					break;
				case 'time':
					$html .= "<input type='text' name='$field[0]' value='$value' style='width: 80px' placeholder='0:00 PM'>";
					break;
				// The special cases
				case '.type':
					$html .= "<select name='type' style='width: 200px'";
					if ($eventNo && $value <= 2) $html .= ' disabled';
					$html .= ">";
					$sql = "select * from `eventType`";
					$result = mysql_query($sql);
					while ($row = mysql_fetch_array($result))
					{
						if ($eventNo && $value > 2 && $row['typeNo'] <= 2) continue;
						$html .= "<option value='" . $row['typeNo'] . "'";
						if ($row['typeNo'] == $value) $html .= ' selected';
						$html .= ">" . $row['typeName'] . "</option>";
					}
					$html .= "</select>";
					break;
				case '.repeat':
					$html .= "<select name='repeat' style='width: 200px'";
					if ($eventNo) $html .= ' disabled';
					$html .= ">";
					foreach (array('no', 'daily', 'weekly', 'biweekly', 'monthly', 'yearly') as $option) $html .= "<option value='" . $option . "'>" . $option . "</option>";
					$html .= "</select>";
					break;
				case '.uniform':
					$html .= "<select name='uniform' style='width: 200px'>";
					$sql = "select * from `uniform`";
					$result = mysql_query($sql);
					while ($row = mysql_fetch_array($result))
					{
						$html .= "<option value='" . $row['id'] . "'";
						if ($row['id'] == $value) $html .= ' selected';
						$html .= ">" . $row['name'] . "</option>";
					}
					$html .= "</select>";
					break;
				case '.section':
					$html .= "<select name='section' style='width: 200px'";
					if ($eventNo) $html .= ' disabled';
					$html .= ">";
					$sql = "select * from `sectionType` order by `typeNo` asc";
					$result = mysql_query($sql);
					while ($row = mysql_fetch_array($result)) $html .= "<option value='" . $row['typeNo'] . "'>" . $row['typeName'] . "</option>";
					$html .= "</select>";
					break;
				case '.semester':
					if ($value == '')
					{
						$sql = "select `semester` from `variables`";
						$row = mysql_fetch_array(mysql_query($sql));
						$value = $row['semester'];
					}
					$html .= "<select name='semester' style='width: 200px'>";
					$sql = "select `semester` from `semester` order by `beginning` desc";
					$result = mysql_query($sql);
					while ($row = mysql_fetch_array($result))
					{
						$html .= "<option value='" . $row['semester'] . "'";
						if ($row['semester'] == $value) $html .= " selected";
						$html .= ">" . $row['semester'] . "</option>";
					}
					$html .= "</select>";
					break;
				default:
					$html .= "????";
					break;
			}
		}
		else $html .= "????";
		$html .= "</td></tr>";
	}
	$html .= "</table>";
}
echo $html;

