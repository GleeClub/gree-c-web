<?php
require_once('functions.php');
$userEmail = getuser();

if (! isOfficer($userEmail))
{
	echo "DENIED";
	exit(1);
}

$type = $_POST['type'];
$choir = getchoir();
if (! $choir) die("Choir is not set");
if ($type == "dues")
{
	$query = mysql_query("select `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = '$CUR_SEM'");
	$dues = -1 * fee("dues");
	while ($row = mysql_fetch_array($query))
	{
		$member = $row['email'];
		$query2 = mysql_query("select * from `transaction` where `memberID` = '$member' and `semester` = '$CUR_SEM' and `type` = 'dues' and `amount` < 0");
		if (mysql_num_rows($query2)) continue;
		mysql_query("insert into `transaction` (`memberID`, `choir`, `amount`, `description`, `semester`, `type`) values ('$member', '$choir', '$dues', '', '$CUR_SEM', 'dues')");
	}
}
else if ($type == "late")
{
	$fee = -1 * fee("latedues");
	$query = mysql_query("select `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = '$CUR_SEM'");
	while ($row = mysql_fetch_array($query))
	{
		$member = $row['email'];
		$result = mysql_fetch_array(mysql_query("select sum(`amount`) as amount from `transaction` where `memberID` = '$member' and `semester` = '$CUR_SEM' and `type` = 'dues'"));
		if ($result['amount'] >= 0) continue;
		if (! mysql_query("insert into `transaction` (`memberID`, `choir` `amount`, `description`, `semester`, `type`) values ('$member', '$choir', '$fee', 'Late fee', '$CUR_SEM', 'dues')")) die("BAD_QUERY");
	}
}
else
{
	echo "BAD_TYPE";
	exit(1);
}

echo "OK";

?>
