<?
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];

$member_fields = array('firstName', 'prefName', 'lastName', 'position', 'section', 'tieNum', 'confirmed', 'email', 'phone', 'picture', 'registration', 'passengers', 'onCampus', 'location', 'about', 'major', 'minor', 'techYear', 'clubYear', 'hometown', 'gChat', 'twitter', 'gatewayDrug', 'conflicts', 'sectional');

function member_details($email)
{
	GLOBAL $member_fields;
	$sql = "select * from member where email = '$email'";
	$member = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	$html = "<span class='pull-right'><button type='button' class='btn edit_member'>Edit</button></span><div class='detail_table'><table>";
	foreach ($member_fields as $field)
	{
		$html .= "<tr><td><b>$field</b></td><td>" . $member[$field] . "</td></tr>";
	}
	$html .= "</table></div>";
	return $html;
}

function member_edit($email)
{
	GLOBAL $member_fields;
	$sql = "select * from member where email = '$email'";
	$member = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	$html = "<input type='hidden' name='user' value='$email'><table>";
	foreach ($member_fields as $field)
	{
		$html .= "<tr><td><b>$field</b></td><td><input type='text' name='$field' value='" . $member[$field] . "'></td></tr>";
	}
	$html .= "</table>";
	return $html;
}

function basic_money_table($memberID, $resolved)
{
	$sql = "select * from transaction where memberID = '$memberID' and `resolved` = '$resolved' order by time desc";
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
	GLOBAL $CUR_SEM;
	GLOBAL $DEPOSIT;
	$tie = 0;
	$sql = "select `id` from `tie` where `owner` = '$memberID'";
	$query = mysql_query($sql);
	$result = mysql_fetch_array($query);
	if (mysql_num_rows($query) != 0) $tie = $result['id'];
	$head = fullNameFromEmail($memberID) . ' ';
	$form = '';
	if ($tie == 0)
	{
		$head .= "does not have a tie checked out.";
		$form = "Check out tie number <input type='text' class='tienum' style='width: 40px; margin-bottom: 1px'><span class='spacer'></span><button type='button' class='btn tie_checkout' data-member='$memberID'>Submit</button>";
	}
	elseif ($tie > 0)
	{
		$head .= "has tie <span style='color: red'>$tie</span> checked out.";
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
	if ($balance >= $DEPOSIT) $deposit = "<span style='color: green'>paid</span>";
	return "$head<br>Tie deposit:  $deposit<br><br>$form";
}

$role = positionFromEmail($userEmail);

switch ($_POST['tab'])
{
	case 'details':
		if ($role != "President" && $role != "VP")
		{
			echo "DENIED";
			exit;
		}
		echo member_details($_POST['email']);
		break;
	case 'details_edit':
		if ($role != "President" && $role != "VP")
		{
			echo "DENIED";
			exit;
		}
		echo member_edit($_POST['email']);
		break;
	case 'money':
		if ($role != "President" && $role != "VP" && $role != "Treasurer")
		{
			echo "DENIED";
			exit;
		}
		echo money_table($_POST['email']);
		break;
	case 'attendance':
		if ($role != "President" && $role != "VP")
		{
			echo "DENIED";
			exit;
		}
		echo attendance($_POST['email'], 1);
		break;
	case 'tie':
		if ($role != "President" && $role != "VP")
		{
			echo "DENIED";
			exit;
		}
		echo tie_form($_POST['email']);
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
