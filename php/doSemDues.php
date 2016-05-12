<?php
require_once('functions.php');

if (! isOfficer($USER))
{
	echo "DENIED";
	exit(1);
}

$type = $_POST['type'];
if (! $CHOIR) die("Choir is not set");
if ($type == "dues")
{
	$query = mysql_query("select `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = '$SEMESTER'");
	$dues = -1 * fee("dues");
	while ($row = mysql_fetch_array($query))
	{
		$member = $row['email'];
		$query2 = mysql_query("select * from `transaction` where `memberID` = '$member' and `semester` = '$SEMESTER' and `type` = 'dues' and `amount` < 0");
		if (mysql_num_rows($query2)) continue;
		mysql_query("insert into `transaction` (`memberID`, `choir`, `amount`, `description`, `semester`, `type`) values ('$member', '$CHOIR', '$dues', '', '$SEMESTER', 'dues')");
	}
}
else if ($type == "late")
{
	$fee = -1 * fee("latedues");
	$query = mysql_query("select `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = '$SEMESTER'");
	while ($row = mysql_fetch_array($query))
	{
		$member = $row['email'];
		$result = mysql_fetch_array(mysql_query("select sum(`amount`) as amount from `transaction` where `memberID` = '$member' and `semester` = '$SEMESTER' and `type` = 'dues'"));
		if ($result['amount'] >= 0) continue;
		if (! mysql_query("insert into `transaction` (`memberID`, `choir` `amount`, `description`, `semester`, `type`) values ('$member', '$CHOIR', '$fee', 'Late fee', '$SEMESTER', 'dues')")) die("BAD_QUERY");
	}
}
else
{
	echo "BAD_TYPE";
	exit(1);
}

echo "OK";

?>
