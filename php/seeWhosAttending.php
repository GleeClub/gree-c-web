<?php
require_once('functions.php');
$eventNo = $_POST['eventNo'];
if (! $CHOIR) die("Choir is not set");

$shouldHtml='<table class="every-other" width="100%"><tr><td><h3>Should Attend</h3></td></tr>';
$shouldntHtml='<table class="every-other" width="100%"><tr><td><h3>Shouldn\'t Attend</h3></td></tr>';
$sql = 'SELECT prefName,firstName,lastName,memberID,`attends`.`confirmed` as confirmed,shouldAttend,section FROM `attends`,`member`,`activeSemester` WHERE eventNo='.$eventNo.' AND memberID=email and `activeSemester`.`semester` = "'.$SEMESTER.'" and `activeSemester`.`member` = `member`.`email` and `activeSemester`.`choir` = "'.$CHOIR.'" order by confirmed DESC,section ASC,lastName ASC,firstName ASC';
$results = mysql_query($sql);
while($row = mysql_fetch_array($results, MYSQL_ASSOC)){
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
