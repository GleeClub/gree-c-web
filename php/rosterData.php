<?php
require_once('functions.php');

function member_fields($email)
{
	global $SEMESTER, $CHOIR;
	$fieldnames = array('firstName', 'prefName', 'lastName', 'email', 'phone', 'picture', 'passengers', 'onCampus', 'location', 'about', 'major', 'minor', 'techYear', 'hometown', 'gChat', 'twitter', 'gatewayDrug', 'conflicts');
	$ret = array();
	$member = query("select * from `member` where `email` = ?", [$email], QONE);
	if (! $member) die("No such member");
	foreach ($fieldnames as $field) $ret[$field] = $member[$field];
	$result = query("select `enrollment`, `section` from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$email, $SEMESTER, $CHOIR], QONE);
	if (! $result)
	{
		$ret["registration"] = "inactive";
		$ret["section"] = "";
	}
	else
	{
		$ret["registration"] = $result["enrollment"];
		$ret["section"] = $result["section"];
	}
	return $ret;
}

function member_details($email)
{
	if (hasPermission("edit-user")) $html = "<span class='pull-right'><button type='button' class='btn edit_member'>Edit</button></span>";
	else $html = "";
	$html .= "<div class='detail_table'><table>";
	foreach (member_fields($email) as $field => $value) $html .= "<tr><td><b>$field</b></td><td>$value</td></tr>";
	$html .= "</table></div>";
	return $html;
}

function member_edit($email)
{
	$html = "<input type='hidden' name='user' value='$email'><table>";
	foreach (member_fields($email) as $field => $value) $html .= "<tr><td><b>$field</b></td><td><input type='text' name='$field' value='$value'></td></tr>";
	$html .= "</table>";
	return $html;
}

function basic_money_table($memberID, $resolved)
{
	global $CHOIR;
	if (! $CHOIR) die("Choir is not set");
	$transactions = query("select * from `transaction` where `memberID` = ? and `choir` = ? and `resolved` = ? order by `time` desc", [$memberID, $CHOIR, $resolved], QALL);
	if (count($transactions) == 0) return "<span style='color: gray'>(No transactions)</span><br>";
	$html = "<table>";
	$count = 0;
	foreach ($transactions as $transaction)
	{
		$time = $transaction['time'];
		$amount = $transaction['amount'];
		$desc = $transaction['description'];
		$id = $transaction['transactionID'];
		$type = $transaction['type'];
		$sem = $transaction['semester'];
		$time = strftime("%b %d, %Y", strtotime($time));
		$html .= "<tr data-id='$id'><td><a href='#' class='transac_edit' data-action='remove'><i class='icon-remove'></i></a><span class='spacer'></span>";
		$html .= $resolved ? "<a href='#' class='transac_edit' data-action='unresolve'><i class='icon-remove-sign'></i></a>" : "<a href='#' class='transac_edit' data-action='resolve'><i class='icon-ok-sign'></i></a>";
		$html .= "</td><td>$time</td>";
		//make the amount number red if it is negative
		if ($amount >= 0) $html .= "<td class='center'>$amount</td>";
		else $html.="<td class='center' style='color: red'>$amount</td>";
		$html .= "<td>";
		$result = query("select `name` from `transacType` where `id` = ?", [$type], QONE);
		if (! $result) die("Invalid transaction type");
		$typename = $result['name'];
		if ($type == 'dues' || $type == 'deposit')
		{
			$html .= "$sem $typename";
			if ($desc != '') $html .= " <span style='color: gray'>($desc)</span>";
		}
		else if ($type == 'other')
		{
			$html .= "$desc";
		}
		else
		{
			$html .= "$typename";
			if ($desc != '') $html .= " ($desc)";
		}
		$html .= "</td></tr>";
		$count++;
	}
	$html .= "</table>";
	return $html;
}

function money_table($memberID)
{
	$html .= basic_money_table($memberID, 0);
	$html .= "<a href='#' onclick='$(this).parent().children(\".money_resolved\").toggle(); return false'>Resolved transactions</a><div class='money_resolved' style='display: none'>";
	$html .= basic_money_table($memberID, 1);
	$html .= "</div>";
	return $html;
}

function tie_form($memberID)
{
	GLOBAL $SEMESTER;
	$tie = 0;
	$result = query("select `tie` from `tieBorrow` where `member` = ? and `dateIn` is null", [$memberID], QONE);
	if ($result) $tie = $result['tie'];
	$head = memberName($memberID, "real") . ' ';
	$form = '';
	if ($tie == 0)
	{
		$head .= "does not have a tie checked out.";
		$form = "Check out tie number <input type='text' class='tienum' style='width: 40px; margin-bottom: 1px'><span class='spacer'></span><button type='button' class='btn tie_checkout' data-member='$memberID'>Submit</button>";
	}
	elseif ($tie > 0)
	{
		$head .= "has tie <span style='font-weight: bold'>$tie</span> checked out.";
		$form = "<button type='button' class='btn tie_return' data-member='$memberID'>Return</button>";
	}
	elseif ($tie < 0)
	{
		$head .= "is a tie thief.";
		$form = "<button type='button' class='btn tie_return' data-member='$memberID'>Resolve</button>";
	}
	$balance = query("select sum(`amount`) as `balance` from `transaction` where `memberID` = ? and `type` = 'deposit'", [$memberID], QONE)["balance"];
	if ($balance == '') $balance = 0;
	$deposit = "<span style='color: red'>unpaid</span>";
	if ($balance >= fee("tie")) $deposit = "<span style='color: green'>paid</span>";
	return "$head<br>Tie deposit:  $deposit<br><br>$form";
}

function active_semesters($memberID)
{
	global $CHOIR;
	if (! $CHOIR) die("Choir is not set");
	$table = "<style>table.semesters { width: auto; } table.semesters td { padding: 2px 10px; } select.section { margin-bottom: 0px; width: 10em; }</style><table class='semesters'><tr><th>Semester</th><th>Status</th><th>Section</th><th>Score</th></tr>";
	foreach (query("select `semester` from `semester` order by `beginning` asc", [], QALL) as $result)
	{
		$activebtn = 0;
		$semester = $result['semester'];
		$active = query("select `enrollment`, `section` from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$memberID, $semester, $CHOIR], QONE);
		$enrollment = "inactive";
		$section = $active ? $active["section"] : 0;
		if ($active)
		{
			$enrollment = $active['enrollment'];
			if ($enrollment == "club") $activebtn = 1;
			else if ($enrollment == "class") $activebtn = 2;
			else die("Invalid enrollment state");
		}
		if (hasPermission("edit-user")) $table .= "<tr data-semester='$semester'><td>$semester</td><td><div class='btn-group' data-toggle='buttons-radio'>" .
			"<button class='btn btn-small semesterbutton" . ($activebtn == 0 ? ' active' : '') . "' data-val='0'>Inactive</button>" .
			"<button class='btn btn-small semesterbutton" . ($activebtn == 1 ? ' active' : '') . "' data-val='1'>Club</button>" .
			"<button class='btn btn-small semesterbutton" . ($activebtn == 2 ? ' active' : '') . "' data-val='2'>Class</button>" .
			"</div></td><td>" . dropdown(sections(), "section", $section, ! hasPermission("edit-user") || ! $active) . "</td>" .
			"<td>" . ($active ? "<span>" : "<span style='color: gray'>") . attendance($memberID, $semester)["finalScore"] . "</span></td></tr>";
		else $table .= "<tr data-semester='$semester'><td>$semester</td><td>$enrollment</td><td>$section</td><td>" . ($active ? "<span>" : "<span style='color: gray'>") . attendance($memberID, $semester)["finalScore"] . "</span></td></tr>";
	}
	$table .= "</table>";
	return $table;
}

$denied = "You do not have access to this functionality.";
if (! isset($_POST['email'])) die("Missing email parameter");

switch ($_POST['tab'])
{
	case 'details':
		if (! hasPermission("view-user-private-details")) die($denied);
		echo member_details($_POST['email']);
		break;
	case 'details_edit':
		if (! hasPermission("edit-user")) die($denied);
		echo member_edit($_POST['email']);
		break;
	case 'money':
		if (! hasPermission("view-transactions")) die($denied);
		echo money_table($_POST['email']);
		break;
	case 'attendance':
		if (! hasPermission("view-attendance")) die($denied);
		echo attendanceTable($_POST['email'], true);
		echo "<div style='text-align: right'><a href='php/memberAttendance.php?id=" . $_POST['email'] . "'>Print view</a></div>";
		break;
	case 'tie':
		if (! hasPermission("view-ties")) die($denied);
		echo tie_form($_POST['email']);
		break;
	case 'semesters':
		if (! hasPermission("view-users")) die($denied);
		echo active_semesters($_POST['email']);
		break;
	case 'col':
		echo memberInfo($_POST['email'])[$_POST["col"]];
		break;
	default:
		echo "???";
		break;
}

?>
