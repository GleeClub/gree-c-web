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
if (! $USER) err("You must be logged in to view member profiles.");
$email = $_GET['person'];

function basic_info($person)
{
	$info = memberInfo($person);
	$about = $info["quote"];
	if ($about == '') $about = "I don't have a quote";
	$html .= "<img class='profile' src='" . $info["picture"] . "'>";
	$html .= "<h3><span style='font-weight: normal; padding-right: 8pt'>" . implode(" and ", positions($person)) . " </span> " . memberName($person, "complete")."</h3>";
	$html .= "<div class='about'>\"$about\"</div>";
	$html .= "<table style='width: initial'><tr><td style='width: 40%; vertical-align: top'>";
	$html .= "<table>";
	$html .= "<tr><td class='key'>Email</td><td><a href='mailto:$person'>$person</a></td></tr>";
	$html .= "<tr><td class='key'>Phone</td><td><a href='tel:" . $info["phone"] . "'>" . $info["phone"] . "</a></td></tr>";
	$html .= "<tr><td class='key'>Section</td><td>" . $info["section"] . "</td></tr>";
	$html .= "<tr><td class='key'>Car</td><td>" . $info["car"] . "</td></tr>";
	$html .= "<tr><td class='key'>Major</td><td>" . $info["major"] . "</td></tr>";
	$html .= "<tr><td class='key'>Year at Tech</td><td>" . $info["techYear"] . "</td></tr>";
	$activeSemesters = '';
	if (hasPermission("view-user-private-details"))
	{
		foreach ($info["activeSemesters"] as $semester) $activeSemesters .= "<span class='label'>$semester</span> ";
		$html .= "<tr><td class='key'>Active</td><td>$activeSemesters</td></tr>";
		$html .= "</table></td><td style='width: 40%; vertical-align: top'><table>";
		$html .= "<tr><td class='key'>Enrollment</td><td>" . $info["enrollment"] . "</td></tr>";
		$html .= "<tr><td class='key'>Gigs</td><td>" . $info["gigs"] . "</td></tr>";
		$html .= "<tr><td class='key'>Score</td><td>" . $info["score"] . "</td></tr>";
		$html .= "<tr><td class='key'>Actions</td></tr>";
		if (hasPermission("view-transactions"))
		{
			$html .= "<tr><td class='key'>Balance</td><td>" . $info["Balance"] . "</td></tr>";
			$html .= "<tr><td class='key'>Dues</td><td>" . $info["Dues"] . "</td></tr>";
			//$html .= "<tr><td class='key'>Tie</td><td>" . rosterProp($member, "Tie") . "</td></tr>";
		}
		if (hasPermission("switch-user"))
		{
			$html .= "<tr><td><button class='btn action' onclick='chgusr(\"$person\")'>Log in as</button></td></tr>";
		}
		if (hasPermission("delete-user"))
		{
			$html .= "<tr><td><button class='btn action' style='color: red' onclick='delusr(\"$person\")'>Delete</button></td></tr>";
		}
	}
	$html .= "</table></td></tr></table>";
	return $html;
}

echo "<div class='section'>" . basic_info($email) . "</div>";
echo "<hr><table><tr>";
if (hasPermission("view-user-private-details")) echo "<td class='tab'><a href='#' class='info_toggle' data-tab='details'>Details</a></td>";
if (hasPermission("view-transactions")) echo "<td class='tab'><a href='#' class='info_toggle' data-tab='money'>Money</a></td>";
if (hasPermission("view-attendance")) echo "<td class='tab'><a href='#' class='info_toggle' data-tab='attendance'>Attendance</a></td>";
if (hasPermission("view-users")) echo "<td class='tab'><a href='#' class='info_toggle' data-tab='semesters'>Semesters</a></td>";
if (hasPermission("view-ties")) echo "<td class='tab'><a href='#' class='info_toggle' data-tab='tie'>Tie</a></td>";
echo "</tr></table><div id='tabbox'></div>";
?>

