<?php
require_once('functions.php');

if (! hasPermission("edit-transaction"))
{
	echo "DENIED";
	exit(1);
}

$type = $_POST["type"];
if (! $CHOIR) err("Choir is not set");
if ($type == "dues")
{
	$dues = -1 * fee("dues");
	foreach (query("select `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = ?", [$SEMESTER], QALL) as $row)
	{
		$member = $row["email"];
		if (query("select * from `transaction` where `memberID` = ? and `semester` = ? and `type` = 'dues' and `amount` < 0", [$member, $SEMESTER], QCOUNT) > 0) continue;
		query("insert into `transaction` (`memberID`, `choir`, `amount`, `description`, `semester`, `type`) values (?, ?, ?, ?, ?, ?)", [$member, $CHOIR, $dues, "", $SEMESTER, "dues"]);
	}
}
else if ($type == "late")
{
	$fee = -1 * fee("latedues");
	foreach (query("select `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = ?", [$SEMESTER], QALL) as $row)
	{
		$member = $row["email"];
		$amount = query("select sum(`amount`) as `amount` from `transaction` where `memberID` = ? and `semester` = ? and `type` = 'dues'", [$member, $SEMESTER], QONE)["amount"];
		if ($amount >= 0) continue;
		query("insert into `transaction` (`memberID`, `choir` `amount`, `description`, `semester`, `type`) values (?, ?, ?, ?, ?, ?)", [$member, $CHOIR, $fee, "Late fee", $SEMESTER, "dues"]);
	}
}
else err("Bad request type");
echo "OK";
?>
