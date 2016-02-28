<?php
require_once('functions.php');
if (! isOfficer(getuser())) die("DENIED");

$member = mysql_real_escape_string($_POST['email']);
$semester = mysql_real_escape_string($_POST['semester']);
$value = $_POST['value'];
$choir = getchoir();
if (! $choir) die("No choir currently selected");
$wasactive = mysql_num_rows(mysql_query("select `member` from `activeSemester` where `member` = '$member' and `semester` = '$semester' and `choir` = '$choir'"));
if ($value == 0) // Inactive
{
	if (! mysql_query("delete from `activeSemester` where `member` = '$member' and `semester` = '$semester' and `choir` = '$choir'")) die("Error: " . mysql_error());

}
else if ($value == 1 || $value == 2) // Club or class
{
	$state = ($value == 1 ? 'club' : 'class');
	if ($wasactive) $query = "update `activeSemester` set `enrollment` = '$state' where `member` = '$member' and `semester` = '$semester'";
	else $query = "insert into `activeSemester` (`member`, `semester`, `choir`, `enrollment`) values ('$member', '$semester', '$choir', '$state')";
	if (! mysql_query($query)) die("Error: " . mysql_error());
}
//if ($value == '1') $query = "insert into `activeSemester` (`member`, `semester`) values ('$member', '$semester')";
//else if ($value == '0') $query = "delete from `activeSemester` where `member` = '$member' and `semester` = '$semester'";
else die("BAD_VALUE $value");
echo "OK";
?>
