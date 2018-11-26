<?php
require_once('functions.php');

if (! hasPermission("view-transactions")) err("Access denied");

function transacTypes()
{
	$html = "<select class='ttype' style='width: 140px'>";
	foreach (query("select `id`, `name` from `transacType` order by `name` asc", [], QALL) as $row)
	{
		$html .= "<option value='" . $row['id'] . "'";
		if ($row['id'] == 'other') $html .= " selected";
		$html .= ">" . $row['name'] . "</option>";
	}
	$html .= "</select>";
	return $html;
}

if (! isset($_POST['action']) || $_POST['action'] == "none")
{
	echo "<style>button { margin-left: 10px; }</style><table id='transac' class='table'></table>";
	if (hasPermission("edit-transaction")) echo "<div id='roster_ops' class='pull-right'><button type='button' class='btn' onclick='addMoneyForm()'>Add Transaction</button></div>";
	exit(0);
}

switch ($_POST['action'])
{
case 'values':
	$member = $_POST['member'];
	$total = query("select sum(`amount`) as `total` from `transaction` where `memberID` = ? and `type` = 'deposit'", [$member], QONE)["total"];
	$deposit = fee("tie");
	$dues = fee("dues");
	if ($total >= $deposit) $dep = -1 * $deposit;
	else $dep = $deposit;
	echo "$dep";
	break;
case 'row':
	echo "<tr class='trans_row'><td>" . memberDropdown() . "</td><td>" . transacTypes() . "</td><td>" . semesterDropdown() . "</td><td><input type='text' class='amount' data-amount-dues='$dues' data-amount-deposit='$deposit' placeholder='Amount' style='width: 60px'></input></td><td><input type='text' class='description' placeholder='Description' maxlength='500'></input></td><td><input type='checkbox' class='receipt'> Send receipt</td><td><button type='button' class='btn cancel'><i class='icon-remove'></i></button></td></tr>";
	break;
default:
	err("Unknown action $_POST[action]");
}

?>
