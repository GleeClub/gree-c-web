<?php
require_once('functions.php');

$style = '<style>
table { width: 100%; }
th { text-align: left; }
th, td { vertical-align: top; padding-right: 10px; }
div.tabbox { margin-bottom: 20px; padding: 10px; }
span.spacer { display: inline-block; width: 20px; }
</style>';

function member_table($conditions, $type = 'normal')
{
	global $SEMESTER;
	
	$cols = rosterPropList($type);
	$sql = 'select * from `member` order by `lastName` asc, `firstName` asc';
	if ($conditions != '' && $conditions != '()') $sql = 'select * from `member` where ' . $conditions . ' order by `lastName` asc, `firstName` asc';
	$members = mysql_query($sql);
	if (! $members) die(mysql_error());

	$html = "<table class='no-highlight' id='roster_table'><thead><tr>";
	foreach ($cols as $col => $width) $html .= "<th style='width: $width'>$col</th>";
	$html .= "</tr></thead><tbody>";
	$i = 1;
	while ($member = mysql_fetch_array($members, MYSQL_ASSOC))
	{
		$html .= "<tr data-member='" . $member["email"] . "'>";
		foreach ($cols as $col => $width)
		{
			$html .= "<td style='width: ${width}px'";
			switch ($col)
			{
				case "#":
					$html .= ">$i";
					break;
				case "Name":
					$html .= " data-tab=''><a href='#profile:" . $member["email"] . "'>" . completeNameFromEmail($member["email"]) . "</a>";
					break;
				default:
					$html .= ">" . rosterProp($member, $col);
					break;
			}
			$html .= "</td>";
		}
		$html .= "</tr>";
		if ($type == "normal") $html .= "<tr><td colspan=" . count($cols) . "><div class=tabbox></div></td></tr>";
		$i++;
	}
	$html .= "</tbody></table>";
	return $html;
}

function member_csv($conditions)
{
	# FIXME No support for conditions right now
	global $SEMESTER, $CHOIR, $USER;
	if (! isOfficer($USER)) die("Access denied");
	$cols = array("firstName", "prefName", "lastName", "email", "phone", "section", "location", "major", "hometown", "section");
	if ($conditions != '()') $conditions = ' and ' . $conditions;
	$sql = "SELECT `member`.`lastName`, `member`.`firstName`, `member`.`prefName`, `member`.`email`, `member`.`phone`, `member`.`location`, `member`.`major`, `member`.`hometown`, `sectionType`.`name` as `section` FROM `member`, `activeSemester`, `sectionType` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = '$SEMESTER' and `activeSemester`.`choir` = '$CHOIR' and `sectionType`.`id` = `activeSemester`.`section`  ORDER BY `member`.`lastName` asc, `member`.`firstName` asc";
	$members = mysql_query($sql);

	$ret = '"' . join('","', $cols) . "\"<br>";
	while ($row = mysql_fetch_array($members))
	{
		$vals = array();
		foreach ($cols as $col) array_push($vals, addslashes($row[$col]));
		$ret .= '"' . join('","', $vals) . "\"<br>";
	}
	return $ret;
}

if (! $CHOIR) die("Choir not set");
$conds = split(';', $_POST['cond']);
$condarr = array();
foreach ($conds as $cond)
{
	if ($cond == '') continue;
	$subcondarr = array();
	$subconds = split(',', $cond);
	foreach ($subconds as $subcond)
	{
		if ($subcond == '') continue;
		else if ($subcond == 'active') $subcondarr[] = "exists (select * from `activeSemester` where `activeSemester`.`semester` = '$SEMESTER' and `activeSemester`.`choir` = '$CHOIR' and `activeSemester`.`member` = `member`.`email`)";
		else if ($subcond == 'inactive') $subcondarr[] = "not exists (select * from `activeSemester` where `activeSemester`.`semester` = '$SEMESTER' and `activeSemester`.`choir` = '$CHOIR' and `activeSemester`.`member` = `member`.`email`)";
		else if ($subcond == 'class') $subcondarr[] = "(select `enrollment` from `activeSemester` where `activeSemester`.`semester` = '$SEMESTER' and `activeSemester`.`choir` = '$CHOIR' and `activeSemester`.`member` = `member`.`email`) = 'class'";
		else if ($subcond == 'club') $subcondarr[] = "(select `enrollment` from `activeSemester` where `activeSemester`.`semester` = '$SEMESTER' and `activeSemester`.`choir` = '$CHOIR' and `activeSemester`.`member` = `member`.`email`) = 'club'";
		else if ($subcond == 'dues') $subcondarr[] = "(select sum(`transaction`.`amount`) from `transaction` where `transaction`.`semester` = '$SEMESTER' and `transaction`.`type` = 'dues' and `transaction`.`memberID` = `member`.`email`) < 0";
	}
	$condarr[] = join(' or ', $subcondarr);
}
$condstr = '(' . join(") and (", $condarr) . ')';

if (! isOfficer($USER)) $condstr = "exists (select * from `activeSemester` where `activeSemester`.`semester` = '$SEMESTER' and `activeSemester`.`member` = `member`.`email`)";

if ($_POST['type'] == "print")
{
	$choirname = choirname($CHOIR);
	echo "<html><head><meta charset='UTF-8'><title>$choirname Roster</title></head><body>$style";
	echo member_table($condstr, "print");
	echo "</body></html>";
}
else if ($_POST['type'] == "csv")
{
	//header("Content-type: text/csv");
	echo member_csv($condstr);
}
else if ($_POST['type'] == "normal")
{
	echo $style;
	echo member_table($condstr, "normal");
}
else die("Unknown type");

?>
