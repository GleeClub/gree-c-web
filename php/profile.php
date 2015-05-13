<style>
div.section
{
	padding: 10px;
	margin: 10px;
}
div.section:after
{
	visibility: hidden;
	display: block;
	font-size: 0;
	content: " ";
	clear: both;
	height: 0;
}
img.profile
{
	border: 1px solid #444;
	float: left;
	max-width: 256px;
	max-height: 256px;
	margin: 10px;
}
table
{
	width: 100%;
}
td.key
{
	color: gray
}
td.tab
{
	border: 1px solid #888;
	padding: 20px;
	font-size: 12pt;
	text-align: center;
}
div#tabbox
{
	padding: 10px;
}
button
{
	margin-right: 10px;
}
</style>

<?php
require_once('functions.php');
$userEmail = getuser();
$officer = isOfficer($userEmail);
$uber = (positionFromEmail($userEmail) == 'President' || positionFromEmail($userEmail) == 'Vice President');
$email = mysql_real_escape_string($_GET['person']);
$query = mysql_query("select `email` from `member` where `email` = '$email'");
if (mysql_num_rows($query) == 0) die("No such user");

$member_fields = array('firstName', 'prefName', 'lastName', 'position', 'section', 'tieNum', 'email', 'phone', 'picture', 'passengers', 'onCampus', 'location', 'about', 'major', 'minor', 'techYear', 'hometown', 'gChat', 'twitter', 'gatewayDrug', 'conflicts');

function basic_info($person)
{
	global $officer, $uber;
	$html .= "<img class='profile' src='" . profilePic($person) . "'>";
	$html .= "<h3>" . completeNameFromEmail($person)."</h3>";
	$html .= "<table style='width: initial'><tr><td style='width: 40%; vertical-align: top'>";
	$html .= "<table>";
	$html .= "<tr><td class='key'>Email</td><td><a href='mailto:$person'>$person</a></td></tr>";
	$html .= "<tr><td class='key'>Phone</td><td><a href='tel:" . phoneNumber($person) . "'>" . phoneNumber($person) . "</a></td></tr>";
	$html .= "<tr><td class='key'>Section</td><td>".sectionNameFromEmail($person)."</td></tr>";
	$html .= "<tr><td class='key'>Position</td><td>".getMemberAttribute('position', $person)."</td></tr>";
	$html .= "<tr><td class='key'>Major</td><td>".getMemberAttribute('major', $person)."</td></tr>";
	$html .= "<tr><td class='key'>Year at Tech</td><td>".getMemberAttribute('techYear', $person)."</td></tr>";
	$sql = mysql_query("select `semester`.`semester` from `activeSemester`, `semester` where `activeSemester`.`member` = '$person' and `activeSemester`.`semester` = `semester`.`semester` order by `semester`.`beginning` desc");
	$activeSemesters = '';
	while ($row = mysql_fetch_array($sql)) $activeSemesters .= "<span class='label'>" . $row['semester'] . "</span> ";
	if ($officer)
	{
		$member = mysql_fetch_array(mysql_query("select * from `member` where `email` = '$person'"));
		$html .= "<tr><td class='key'>Active</td><td>$activeSemesters</td></tr>";
		$html .= "</table></td><td style='width: 40%; vertical-align: top'><table>";
		$html .= "<tr><td class='key'>Enrollment</td><td>" . rosterProp($member, "Enrollment") . "</td></tr>";
		$html .= "<tr><td class='key'>Balance</td><td>" . rosterProp($member, "Balance") . "</td></tr>";
		$html .= "<tr><td class='key'>Dues</td><td>" . rosterProp($member, "Dues") . "</td></tr>";
		$html .= "<tr><td class='key'>Tie</td><td>" . rosterProp($member, "Tie") . "</td></tr>";
		$html .= "<tr><td class='key'>Gigs</td><td>" . rosterProp($member, "Gigs") . "</td></tr>";
		$html .= "<tr><td class='key'>Score</td><td>" . rosterProp($member, "Score") . "</td></tr>";
		if ($uber) $html .= "<tr><td class='key'>Actions</td><td><button class='btn' onclick='chgusr(\"$person\")'>Log in as</button><button class='btn' style='color: red' onclick='delusr(\"$person\")'>Delete</button></td></tr>";
	}
	$html .= "</table></td></tr></table>";
	return $html;
}

echo "<div class='section'>" . basic_info($email) . "</div>";
echo "<hr>";
if ($officer)
{
	echo "<table><tr>";
		echo "<td class='tab'><a href='#' class='info_toggle' data-tab='details'>Details</a></td>";
		echo "<td class='tab'><a href='#' class='info_toggle' data-tab='money'>Money</a></td>";
		echo "<td class='tab'><a href='#' class='info_toggle' data-tab='attendance'>Attendance</a></td>";
		echo "<td class='tab'><a href='#' class='info_toggle' data-tab='semesters'>Semesters</a></td>";
		echo "<td class='tab'><a href='#' class='info_toggle' data-tab='tie'>Tie</a></td>";
	echo "</tr></table><div id='tabbox'></div>";
}
?>

