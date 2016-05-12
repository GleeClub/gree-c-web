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
div.about
{
	font-weight: bold;
	font-size: 12pt;
	margin-top: -8pt;
	margin-bottom: 8pt;
}
img.profile
{
	border: 1px solid #444;
	float: left;
	width: 256px;
	/*max-height: 256px;*/
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
button.action
{
	margin-right: 10px;
}
</style>

<?php
require_once('functions.php');
$userEmail = getuser();
if (! $userEmail) die("You must be logged in to view member profiles.");
$officer = isOfficer($userEmail);
$uber = isUber($userEmail);
$email = mysql_real_escape_string($_GET['person']);
$query = mysql_query("select `email` from `member` where `email` = '$email'");
if (mysql_num_rows($query) == 0) die("No such user");

$member_fields = array('firstName', 'prefName', 'lastName', 'section', 'tieNum', 'email', 'phone', 'picture', 'passengers', 'onCampus', 'location', 'about', 'major', 'minor', 'techYear', 'hometown', 'gChat', 'twitter', 'gatewayDrug', 'conflicts');

function basic_info($person)
{
	global $officer, $uber;
	$member = mysql_fetch_array(mysql_query("select * from `member` where `email` = '$person'"));
	$about = getMemberAttribute('about', $person);
	if ($about == '') $about = "I don't have a quote";
	$html .= "<img class='profile' src='" . profilePic($person) . "'>";
	$html .= "<h3><span style='font-weight: normal; padding-right: 8pt'>" . implode(" and ", positions($person)) . " </span> " . completeNameFromEmail($person)."</h3>";
	$html .= "<div class='about'>\"$about\"</div>";
	$html .= "<table style='width: initial'><tr><td style='width: 40%; vertical-align: top'>";
	$html .= "<table>";
	$html .= "<tr><td class='key'>Email</td><td><a href='mailto:$person'>$person</a></td></tr>";
	$html .= "<tr><td class='key'>Phone</td><td><a href='tel:" . phoneNumber($person) . "'>" . phoneNumber($person) . "</a></td></tr>";
	$html .= "<tr><td class='key'>Section</td><td>".sectionFromEmail($person, 1)."</td></tr>";
	$html .= "<tr><td class='key'>Car</td><td>".rosterProp($member, "Car")."</td></tr>";
	$html .= "<tr><td class='key'>Major</td><td>".getMemberAttribute('major', $person)."</td></tr>";
	$html .= "<tr><td class='key'>Year at Tech</td><td>".getMemberAttribute('techYear', $person)."</td></tr>";
	$sql = mysql_query("select `semester`.`semester` from `activeSemester`, `semester` where `activeSemester`.`member` = '$person' and `activeSemester`.`semester` = `semester`.`semester` order by `semester`.`beginning` desc");
	$activeSemesters = '';
	while ($row = mysql_fetch_array($sql)) $activeSemesters .= "<span class='label'>" . $row['semester'] . "</span> ";
	if ($officer)
	{
		$html .= "<tr><td class='key'>Active</td><td>$activeSemesters</td></tr>";
		$html .= "</table></td><td style='width: 40%; vertical-align: top'><table>";
		$html .= "<tr><td class='key'>Enrollment</td><td>" . rosterProp($member, "Enrollment") . "</td></tr>";
		if ($uber || hasPosition($userEmail, "Treasurer"))
		{
			$html .= "<tr><td class='key'>Balance</td><td>" . rosterProp($member, "Balance") . "</td></tr>";
			$html .= "<tr><td class='key'>Dues</td><td>" . rosterProp($member, "Dues") . "</td></tr>";
			$html .= "<tr><td class='key'>Tie</td><td>" . rosterProp($member, "Tie") . "</td></tr>";
		}
		if ($uber)
		{
			$html .= "<tr><td class='key'>Gigs</td><td>" . rosterProp($member, "Gigs") . "</td></tr>";
			$html .= "<tr><td class='key'>Score</td><td>" . rosterProp($member, "Score") . "</td></tr>";
			$html .= "<tr><td class='key'>Actions</td><td><button class='btn action' onclick='chgusr(\"$person\")'>Log in as</button><button class='btn action' style='color: red' onclick='delusr(\"$person\")'>Delete</button></td></tr>";
		}
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

