<?php
require_once('functions.php');
if (! hasPermission("edit-user")) err("DENIED");

$member = $_POST['email'];
$semester = $_POST['semester'];
if (! $CHOIR) err("No choir currently selected");
$wasactive = query("select `member` from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$member, $semester, $CHOIR], QCOUNT) > 0;
if (isset($_POST['confirmed']))
{
	$value = $_POST['confirmed'];
	if ($value == 0) // Inactive
		query("delete from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$member, $semester, $CHOIR]);
	else if ($value == 1 || $value == 2) // Club or class
	{
		$state = ($value == 1 ? 'club' : 'class');
		if ($wasactive) query("update `activeSemester` set `enrollment` = ? where `member` = ? and `semester` = ? and `choir` = ?", [$state, $member, $semester, $CHOIR]);
		else query("insert into `activeSemester` (`member`, `semester`, `choir`, `enrollment`) values (?, ?, ?, ?)", [$member, $semester, $CHOIR, $state]);
	}
	//if ($value == '1') $query = "insert into `activeSemester` (`member`, `semester`) values ('$member', '$semester')";
	//else if ($value == '0') $query = "delete from `activeSemester` where `member` = '$member' and `semester` = '$semester'";
	else err("BAD_VALUE $value");
}
if (isset($_POST['section']))
{
	$section = $_POST['section'];
	if (! $wasactive) err("Can't change section for inactive semester");
	$DB->begin_transaction();
	$err = updateSection($member, $semester, $CHOIR, $section);
	if ($err)
	{
		$DB->rollback();
		err("Error changing section: " . $err);
	}
	$DB->commit();
}
echo "OK";
?>
