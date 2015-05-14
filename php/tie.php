<?
require_once('functions.php');

$member = mysql_real_escape_string($_POST['member']);
$tie = mysql_real_escape_string($_POST['tie']);
$action = mysql_real_escape_string($_POST['action']);
$newid = mysql_real_escape_string($_POST['newid']);
$status = mysql_real_escape_string($_POST['status']);
$comments = mysql_real_escape_string($_POST['comments']);

if (! isUber(getuser())) die('DENIED');
if (! isset($member) || ! isset($action)) die('MISSING_ARG');
if ($action == 'return')
{
	if (! isset($tie)) die('MISSING_ARG');
	$sql = "update `tie` set `status` = 'returned', `owner` = NULL  where `owner` = '$member'";
	if (mysql_query($sql)) echo 'OK';
	else die('ERR');
}
else if ($action == 'checkout')
{
	if (! isset($tie)) die('MISSING_ARG');
	if (mysql_num_rows(mysql_query("select * from `tie` where `id` = '$tie'")) == 0) die('NO_TIE');
	$sql = "update `tie` set `status` = 'borrowed', `owner` = '$member' where `id` = '$tie'";
	if (mysql_query($sql)) echo 'OK';
	else die('ERR');
}
else if ($action == 'status_dropdown')
{
	echo "<select name='tie_status'>";
	$sql = 'select `id`, `name` from `tieStatus`';
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
	echo "</select>";
}
else if ($action == 'add')
{
	if (! preg_match('/^\d+$/', $tie)) die("Invalid tie number \"$tie\"");
	if (mysql_num_rows(mysql_query("select * from `tie` where `id` = '$tie'")) > 0) die("That tie already exists");
	if (mysql_query("insert into `tie` set `id` = '$tie'")) echo 'OK';
	else die('ERR');
}
else if ($action == 'delete')
{
	$sql = "delete from `tie` where `id` = '$tie'";
	if (mysql_query($sql)) echo 'OK';
	else die('ERR');
}
else if ($action == 'update')
{
	$sql = "update `tie` set `id` = '$newid', `status` = '$status', `owner` = " . ($member == '' ? "NULL" : "'$member'" ) . ", `comments` = '$comments' where `id` = '$tie'";
	if (mysql_query($sql)) echo 'OK';
	else die('ERR');
}
else die('HUH?');
?>
