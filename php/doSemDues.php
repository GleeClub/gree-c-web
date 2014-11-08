<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword") or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];

if (! isOfficer($userEmail))
{
	echo "DENIED";
	exit(1);
}

$type = $_POST['type'];
if ($type == "dues")
{
	$query = mysql_query("select `email` from `member` where `confirmed` = '1'");
	$dues = -1 * $DUES;
	while ($row = mysql_fetch_array($query))
	{
		$member = $row['email'];
		$query2 = mysql_query("select * from `transaction` where `memberID` = '$member' and `semester` = '$CUR_SEM' and `type` = 'dues' and `amount` < 0");
		if (mysql_num_rows($query2)) continue;
		mysql_query("insert into `transaction` (`memberID`, `amount`, `description`, `semester`, `type`) values ('$member', '$dues', '', '$CUR_SEM', 'dues')");
	}
}
else if ($type == "late")
{
	$fee = -1 * $LATEFEE;
	$query = mysql_query("select `email` from `member` where `confirmed` = '1'");
	while ($row = mysql_fetch_array($query))
	{
		$member = $row['email'];
		$result = mysql_fetch_array(mysql_query("select sum(`amount`) as amount from `transaction` where `memberID` = '$member' and `semester` = '$CUR_SEM' and `type` = 'dues'"));
		if ($result['amount'] >= 0) continue;
		if (! mysql_query("insert into `transaction` (`memberID`, `amount`, `description`, `semester`, `type`) values ('$member', '$fee', 'Late fee', '$CUR_SEM', 'dues')")) die("BAD_QUERY");
	}
}
else
{
	echo "BAD_TYPE";
	exit(1);
}

echo "OK";

?>
