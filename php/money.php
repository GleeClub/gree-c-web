<?php
require_once('functions.php');
$userEmail = getuser();

if (! isUber($userEmail) && positionFromEmail($userEmail) != "Treasurer") die("Access denied");

function transacTypes()
{
	$html = "<select class='ttype' style='width: 140px'>";
	$result = mysql_query("select `id`, `name` from `transacType` order by `name` asc");
	while ($row = mysql_fetch_array($result))
	{
		$html .= "<option value='" . $row['id'] . "'";
		if ($row['id'] == 'other') $html .= " selected";
		$html .= ">" . $row['name'] . "</option>";
	}
	$html .= "</select>";
	return $html;
}

if (! isset($_POST['action']) || $_POST['action'] == "none")
{ ?>
<style>
button { margin-left: 10px; }
</style>
<table id='transac' class='table'></table>
<div id='roster_ops' class='pull-right'><button type='button' class='btn' onclick='addMoneyForm()'>Add Transaction</button></div>
<?php exit(0); }

switch ($_POST['action'])
{
case 'values':
	$member = mysql_real_escape_string($_POST['member']);
	$result = mysql_fetch_array(mysql_query("select sum(`amount`) as `total` from `transaction` where `memberID` = '$member' and `type` = 'deposit'"));
	$total = $result['total'];
	if ($total >= $DEPOSIT) $dep = -1 * $DEPOSIT;
	else $dep = $DEPOSIT;
	echo "$dep";
	break;
case 'row':
	echo "<tr class='trans_row'><td>" . memberDropdown() . "</td><td>" . transacTypes() . "</td><td>" . semesterDropdown() . "</td><td><input type='text' class='amount' data-amount-dues='$DUES' data-amount-deposit='$DEPOSIT' placeholder='Amount' style='width: 60px'></input></td><td><input type='text' class='description' placeholder='Description' maxlength='500'></input></td><td><input type='checkbox' class='receipt'> Send receipt</td><td><button type='button' class='btn cancel'><i class='icon-remove'></i></button></td></tr>";
	break;
default:
	die("Unknown action $_POST[action]");
}

?>
