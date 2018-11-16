<?php
require_once('functions.php');
if (! $CHOIR) die("Choir is not set");

$shouldHtml='<table class="every-other" width="100%"><tr><td><h3>Should Attend</h3></td></tr>';
$shouldntHtml='<table class="every-other" width="100%"><tr><td><h3>Shouldn\'t Attend</h3></td></tr>';
foreach(query("select `prefName`, `firstName`, `lastName`, `memberID`, `attends`.`confirmed` as `confirmed`, `shouldAttend`, `section` from `attends`, `member`, `activeSemester` where `eventNo` = ? and `memberID` = `email` and `activeSemester`.`semester` = ? and `activeSemester`.`member` = `member`.`email` and `activeSemester`.`choir` = ? order by `confirmed` desc, `section` asc, `lastName` asc, `firstName` asc", [$_POST["eventNo"], $SEMESTER, $CHOIR], QALL) as $row)
{
	$section = $row['section'];
	$prefName = $row['prefName'];
	$firstName = $row['firstName'];
	$lastName = $row['lastName'];
	$confirmed = $row['confirmed'];
	$email = $row['memberID'];

	if ($section == '4') $section = 'T1';
	else if ($section == '3') $section = 'T2';
	else if ($section == '2') $section = 'B1';
	else if ($section == '1') $section = 'B2';
	else if ($section == '0') $section = '--';

	$current = '<tr>
		<td><a href="#profile:' . $email . '">' . ($prefName == '' ? $firstName : $prefName) . '  ' .$lastName . '</a></td>
		<td style="text-align:center;width: 40px;">'.$section.'</td>
		<td style="text-align:center">'.($confirmed == '0' ? '<span class="label label-warning">not confirmed</span>' : '<span class="label label-success">confirmed</span>').'</td>
	</tr>';
	if($row['shouldAttend'] == '1') $shouldHtml .= $current;
	else $shouldntHtml .= $current;
}

$shouldntHtml .= '</table>';
$shouldHtml .= '</table>';

echo $shouldHtml.$shouldntHtml;
?>
