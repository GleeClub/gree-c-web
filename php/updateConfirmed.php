<?php
require_once('functions.php');
if (! isOfficer($USER)) die("DENIED");

$member = mysql_real_escape_string($_POST['email']);
$semester = mysql_real_escape_string($_POST['semester']);
if (! $CHOIR) die("No choir currently selected");
$wasactive = mysql_num_rows(mysql_query("select `member` from `activeSemester` where `member` = '$member' and `semester` = '$semester' and `choir` = '$CHOIR'"));
if (isset($_POST['confirmed']))
{
	$value = mysql_real_escape_string($_POST['confirmed']);
	if ($value == 0) // Inactive
	{
		if (! mysql_query("delete from `activeSemester` where `member` = '$member' and `semester` = '$semester' and `choir` = '$CHOIR'")) die("Error: " . mysql_error());

	}
	else if ($value == 1 || $value == 2) // Club or class
	{
		$state = ($value == 1 ? 'club' : 'class');
		if ($wasactive) $query = "update `activeSemester` set `enrollment` = '$state' where `member` = '$member' and `semester` = '$semester' and `choir` = '$CHOIR'";
		else $query = "insert into `activeSemester` (`member`, `semester`, `choir`, `enrollment`) values ('$member', '$semester', '$CHOIR', '$state')";
		if (! mysql_query($query)) die("Error: " . mysql_error());
	}
	//if ($value == '1') $query = "insert into `activeSemester` (`member`, `semester`) values ('$member', '$semester')";
	//else if ($value == '0') $query = "delete from `activeSemester` where `member` = '$member' and `semester` = '$semester'";
	else die("BAD_VALUE $value");
}
if (isset($_POST['section']))
{
	$section = mysql_real_escape_string($_POST['section']);
	if (! $wasactive) die("Can't change section for inactive semester");
	$msg = updateSection($member, $semester, $CHOIR, $section);
	if ($msg != "") die("Error changing section: " . $msg);
}
echo "OK";
?>
