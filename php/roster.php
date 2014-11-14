<style>
table
{
	width: 100%;
}
th
{
	text-align: left;
}
th, td
{
	vertical-align: top;
	padding-right: 10px;
}
div.tabbox
{
	margin-bottom: 20px;
	padding: 10px;
}
span.spacer
{
	display: inline-block;
	width: 20px;
}
</style>

<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword") or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase") or die("cannot select DB");
$userEmail = $_COOKIE['email'];

function member_table($conditions, $type = 'normal')
{
	global $CUR_SEM, $DEPOSIT, $GIG_REQ;
	$userEmail = $_COOKIE['email'];
	$role = positionFromEmail($userEmail);
	$officer = isOfficer($userEmail);
	$showDetails = 0;
	$showMoney = 0;
	$showAttendance = 0;
	$cols = array("#" => 10, "Name" => 260, "Section" => 80, "Contact" => 180, "Location" => 200);
	if ($officer)
	{
		$cols["Class"] = 40;
	}
	if ($role == "Treasurer" || $role == "VP" || $role == "President")
	{
		$showMoney = true;
		$cols["Balance"] = 60;
		$cols["Dues"] = 60;
	}
	if ($role == "VP" || $role == "President")
	{
		$showAttendance = true;
		$showDetails = true;
		$cols["Tie"] = 40;
		$cols["Gigs"] = 40;
		$cols["Grade"] = 60;
	}
	if ($type == 'print')
	{
		unset($cols["Contact"]);
		unset($cols["Location"]);
		unset($cols["Balance"]);
	}

	$sql = 'SELECT * FROM `member` ORDER BY confirmed desc, lastName asc, firstName asc';
	if ($conditions != '') $sql = 'SELECT * FROM `member` where ' . $conditions . ' ORDER BY confirmed desc, lastName asc, firstName asc';
	$members = mysql_query($sql);

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
					$html .= " data-tab=''>" . completeNameFromEmail($member["email"]);
					if ($type == 'print' || ! $showDetails && ! $showMoney && ! $showAttendance) continue;
					$html .= "<br>";
					if ($showDetails) $html .= "<a href='#' class='roster_toggle' data-tab='details'>Details</a><span class=spacer></span>";
					if ($showMoney) $html .= "<a href='#' class='roster_toggle' data-tab='money'>Money</a><span class=spacer></span>";
					if ($showAttendance) $html .= "<a href='#' class='roster_toggle' data-tab='attendance'>Attendance</a><span class=spacer></span><a href='#' class='roster_toggle' data-tab='tie'>Tie</a><span class=spacer></span>";
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

if (isset($_POST['type']) && $_POST['type'] == "print")
{
	echo "<html><head><meta charset='UTF-8'><title>Glee Club Roster</title></head><body>";
	echo member_table("`confirmed` = '1'", "print");
	echo "</body></html>";
	exit(0);
}

$role = positionFromEmail($userEmail);
echo member_table("`confirmed` = '1'");
if (isOfficer($userEmail))
{
	echo "<a href='#' onclick='$(\"#inactive_area\").toggle(); return false'>Inactive members</a>";
	echo "<div id='inactive_area' style='display: none'>" . member_table("`confirmed` = '0'") . "</div>";
}
if ($role == "Treasurer" || $role == "VP" || $role == "President")
{
	echo "<br><br><table id='transac'></table>";
	$result = mysql_fetch_array(mysql_query("select `gigRequirement` from `variables`"));
	$gigreq = $result['gigRequirement'];
	echo "<span class='pull-right' id='roster_ops'>Volunteer gig requirement:  <input type='text' id='gigreq' style='width: 20px; margin-bottom: 0px' value='$gigreq'><button class='btn' onclick='setGigReq($(\"#gigreq\").attr(\"value\"))'>Go</button><span class='spacer'></span><div style='display: inline-block'><input type='checkbox' style='margin-top: -16px' name='gigcheck' onclick='setGigCheck($(this).attr(\"checked\"))'";
	$result = mysql_fetch_array(mysql_query("select `gigCheck` from `variables`"));
	if ($result['gigCheck']) echo " checked";
	echo "> <div style='display: inline-block'>Include gig requirement<br>in grade calculation</div></div><span class='spacer'></span><div class='btn-group'><button class='btn dropdown-toggle' data-toggle='dropdown' href='#'>Dues <span class='caret'></span></button><ul class='dropdown-menu'>";
	echo "<li><a href='#' id='semdues' onclick='addDues(); return false;' data-placement='right' data-toggle='tooltip' title='Adds a $20 fee to the account of every active member who does not yet have a dues charge for this semester'>Apply semester dues</a></li>";
	echo "<li><a href='#' id='latefee' onclick='addLateFee(); return false;' data-placement='right' data-toggle='tooltip' title='Adds a $5 fee to the account of every active member whose dues balance for this semester is not $0'>Add late fee</a></li>";
	echo "</ul></div><span class='spacer'></span><button type='button' class='btn' onclick='addMoneyForm()'>Add Transaction</button></span>";
}

