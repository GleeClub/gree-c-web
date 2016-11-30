<?
require_once('functions.php');

function member_fields($email)
{
	$fieldnames = array('firstName', 'prefName', 'lastName', 'email', 'phone', 'picture', 'passengers', 'onCampus', 'location', 'about', 'major', 'minor', 'techYear', 'hometown', 'gChat', 'twitter', 'gatewayDrug', 'conflicts');
	$ret = array();
	$member = mysql_fetch_array(mysql_query("select * from member where email = '$email'"), MYSQL_ASSOC);
	foreach ($fieldnames as $field) $ret[$field] = $member[$field];
	$ret["registration"] = enrollment($email);
	return $ret;
}

function member_details($email)
{
	$html = "<span class='pull-right'><button type='button' class='btn edit_member'>Edit</button></span><div class='detail_table'><table>";
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
	$sql = "select * from transaction where memberID = '$memberID' and `choir` = '$CHOIR' and `resolved` = '$resolved' order by time desc";
	$transactions = mysql_query($sql);
	if (mysql_num_rows($transactions) == 0) return "<span style='color: gray'>(No transactions)</span><br>";
	$html = "<table>";
	$count = 0;
	while($transaction = mysql_fetch_array($transactions))
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
		if($amount>=0) $html.="<td class='center'>$amount</td>";
		else $html.="<td class='center' style='color: red'>$amount</td>";
		$html .= "<td>";
		$sql = "select `name` from `transacType` where `id` = '$type'";
		$result = mysql_fetch_array(mysql_query($sql));
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
	$query = mysql_query("select `tie` from `tieBorrow` where `member` = '$memberID' and `dateIn` is null");
	$result = mysql_fetch_array($query);
	if (mysql_num_rows($query) != 0) $tie = $result['tie'];
	$head = fullNameFromEmail($memberID) . ' ';
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
	$sql = "select sum(`amount`) as `balance` from `transaction` where `memberID` = '$memberID' and `type` = 'deposit'";
	$result = mysql_fetch_array(mysql_query($sql));
	$balance = $result['balance'];
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
	$query = mysql_query("select `semester` from `semester` order by `beginning` asc");
	while ($result = mysql_fetch_array($query))
	{
		$activebtn = 0;
		$semester = $result['semester'];
		$query1 = mysql_query("select `enrollment` from `activeSemester` where `member` = '$memberID' and `semester` = '$semester' and `choir` = '$CHOIR'");
		$active = mysql_num_rows($query1);
		if ($active)
		{
			$result1 = mysql_fetch_array($query1);
			$enrollment = $result1['enrollment'];
			if ($enrollment == "club") $activebtn = 1;
			else if ($enrollment == "class") $activebtn = 2;
			else die("Invalid enrollment state");
		}
		$table .= "<tr data-semester='$semester'><td>$semester</td><td><div class='btn-group' data-toggle='buttons-radio'>" .
			"<button class='btn btn-small semesterbutton" . ($activebtn == 0 ? ' active' : '') . "' data-val='0'>Inactive</button>" .
			"<button class='btn btn-small semesterbutton" . ($activebtn == 1 ? ' active' : '') . "' data-val='1'>Club</button>" .
			"<button class='btn btn-small semesterbutton" . ($activebtn == 2 ? ' active' : '') . "' data-val='2'>Class</button>" .
			"</div></td><td>" . dropdown(sections(), "section", $active ? sectionFromEmail($memberID, false, $semester) : 0, ! $active) . "</td>" .
			"<td>" . ($active ? "<span>" : "<span style='color: gray'>") . attendance($memberID, 0, $semester) . "</span></td></tr>";
	}
	$table .= "</table>";
	return $table;
}

$officer = isOfficer($USER);
$uber = isUber($USER);
$denied = "You do not have access to this functionality.";

switch ($_POST['tab'])
{
	case 'details':
		if (! $officer) die($denied);
		echo member_details(mysql_real_escape_string($_POST['email']));
		break;
	case 'details_edit':
		if (! $uber) die($denied);
		echo member_edit(mysql_real_escape_string($_POST['email']));
		break;
	case 'money':
		if (! $uber && ! hasPosition($USER, "Treasurer")) die($denied);
		echo money_table(mysql_real_escape_string($_POST['email']));
		break;
	case 'attendance':
		if (! $uber) die($denied);
		echo attendance(mysql_real_escape_string($_POST['email']), 1);
		echo "<div style='text-align: right'><a href='php/memberAttendance.php?id=" . $_POST['email'] . "'>Print view</a></div>";
		break;
	case 'tie':
		if (! $uber) die($denied);
		echo tie_form(mysql_real_escape_string($_POST['email']));
		break;
	case 'semesters':
		if (! $uber) die($denied);
		echo active_semesters(mysql_real_escape_string($_POST['email']));
		break;
	case 'col':
		if (! isset($_POST['email'])) die("BAD_ACTION");
		$sql = "select * from `member` where `email` = '" . mysql_real_escape_string($_POST['email']) . "'";
		echo rosterProp(mysql_fetch_array(mysql_query($sql)), mysql_real_escape_string($_POST['col']));
		break;
	default:
		echo "???";
		break;
}

?>
