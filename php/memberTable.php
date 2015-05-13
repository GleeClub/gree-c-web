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
	global $CUR_SEM, $DEPOSIT;
	$userEmail = getuser();
	$role = positionFromEmail($userEmail);
	$officer = isOfficer($userEmail);
	$cols = array("#" => 10, "Name" => 260, "Section" => 80, "Contact" => 180, "Location" => 200);
	if ($officer)
	{
		$cols["Enrollment"] = 40;
	}
	if ($role == "Treasurer" || $role == "Vice President" || $role == "President")
	{
		$cols["Balance"] = 60;
		$cols["Dues"] = 60;
	}
	if ($role == "Vice President" || $role == "President")
	{
		$cols["Tie"] = 40;
		$cols["Gigs"] = 40;
		$cols["Score"] = 60;
	}
	if ($type == 'print')
	{
		unset($cols["Contact"]);
		unset($cols["Location"]);
		unset($cols["Balance"]);
	}

	$sql = 'select * from `member` order by `lastName` asc, `firstName` asc';
	if ($conditions != '' && $conditions != '()') $sql = 'select * from `member` where ' . $conditions . ' order by `lastName` asc, `firstName` asc';
	$members = mysql_query($sql);
	if (! $members) die(mysql_error());

	$html = "<table class='no-highlight' id='roster_table'><thead><tr>";
	foreach ($cols as $col => $width)
	{
		$html .= "<th style='width: $width'>$col</th>";
	}
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
	$userEmail = getuser();
	$role = positionFromEmail($userEmail);
	if (! isOfficer($userEmail)) die("Access denied");
	$cols = array("firstName", "prefName", "lastName", "email", "phone", "section", "location", "major");

	$sql = 'SELECT * FROM `member` ORDER BY lastName asc, firstName asc';
	if ($conditions != '') $sql = 'SELECT * FROM `member` where ' . $conditions . ' ORDER BY lastName asc, firstName asc';
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

global $CUR_SEM;
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
		else if ($subcond == 'active') $subcondarr[] = "exists (select * from `activeSemester` where `activeSemester`.`semester` = '$CUR_SEM' and `activeSemester`.`member` = `member`.`email`)";
		else if ($subcond == 'inactive') $subcondarr[] = "not exists (select * from `activeSemester` where `activeSemester`.`semester` = '$CUR_SEM' and `activeSemester`.`member` = `member`.`email`)";
		else if ($subcond == 'class') $subcondarr[] = "(select `enrollment` from `activeSemester` where `activeSemester`.`semester` = '$CUR_SEM' and `activeSemester`.`member` = `member`.`email`) = 'class'";
		else if ($subcond == 'club') $subcondarr[] = "(select `enrollment` from `activeSemester` where `activeSemester`.`semester` = '$CUR_SEM' and `activeSemester`.`member` = `member`.`email`) = 'club'";
		else if ($subcond == 'dues') $subcondarr[] = "(select sum(`transaction`.`amount`) from `transaction` where `transaction`.`semester` = '$CUR_SEM' and `transaction`.`type` = 'dues' and `transaction`.`memberID` = `member`.`email`) < 0";
		//else if ($subcond == 'fail') $subcondarr[] = "`` = ''";
		else if ($subcond == 'b2') $subcondarr[] = "`section` = '1'";
		else if ($subcond == 'b1') $subcondarr[] = "`section` = '2'";
		else if ($subcond == 't2') $subcondarr[] = "`section` = '3'";
		else if ($subcond == 't1') $subcondarr[] = "`section` = '4'";
		
	}
	$condarr[] = join(' or ', $subcondarr);
}
$condstr = '(' . join(") and (", $condarr) . ')';

if (! isOfficer(getuser())) $condstr = "exists (select * from `activeSemester` where `activeSemester`.`semester` = '$CUR_SEM' and `activeSemester`.`member` = `member`.`email`)";

if ($_POST['type'] == "print")
{
	echo "<html><head><meta charset='UTF-8'><title>Glee Club Roster</title></head><body>$style";
	echo member_table($condstr, "print");
	echo "</body></html>";
}
else if ($_POST['type'] == "csv")
{
	header("Content-type: text/csv");
	echo member_csv($condstr);
}
else if ($_POST['type'] == "normal")
{
	echo $style;
	echo member_table($condstr, "normal");
}
else die("Unknown type");

?>
