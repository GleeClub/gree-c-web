<?
require_once('functions.php');

$member = mysql_real_escape_string($_POST['member']);
$tie = mysql_real_escape_string($_POST['tie']);
$action = mysql_real_escape_string($_POST['action']);
$newid = mysql_real_escape_string($_POST['newid']);
$status = mysql_real_escape_string($_POST['status']);
$comments = mysql_real_escape_string($_POST['comments']);
$id = mysql_real_escape_string($_POST['id']);

if (! isUber(getuser())) die('DENIED');
if (! isset($_POST['action'])) die('MISSING_ARG');
if ($action == 'return')
{
	if(! isset($_POST['member'])) die('MISSING_ARG');
	if (mysql_num_rows(mysql_query("select * from `tieBorrow` where `member` = '$member' and `dateIn` is null")) != 1) die("$member does not have a tie out");
	if (! mysql_query("update `tieBorrow` set `dateIn` = curdate() where `member` = '$member' and `dateIn` is null")) die(mysql_error());
	echo "OK";
}
else if ($action == 'checkout')
{
	if (! isset($_POST['tie']) || ! isset($_POST['member'])) die('MISSING_ARG');
	if (mysql_num_rows(mysql_query("select * from `tie` where `id` = '$tie'")) == 0) die("Tie $tie does not exist");
	$query = mysql_query("select * from `tieBorrow` where `member` = '$member' and `dateIn` is null");
	if (mysql_num_rows($query) != 0)
	{
		$result = mysql_fetch_array($query);
		$oldtie = $result['tie'];
		die("$member already has tie $oldtie out");
	}
	if (! mysql_query("insert into `tieBorrow` (`member`, `tie`, `dateOut`) values ('$member', '$tie', curdate())")) die(mysql_error());
	echo "OK";
}
else if ($action == 'add')
{
	if (! isset($_POST['tie'])) die('MISSING_ARG');
	if (! preg_match('/^\d+$/', $tie)) die("Invalid tie number \"$tie\"");
	if (mysql_num_rows(mysql_query("select * from `tie` where `id` = '$tie'")) > 0) die("Tie $tie already exists");
	if (! mysql_query("insert into `tie` set `id` = '$tie'")) die(mysql_error());
	echo "OK";
}
else if ($action == 'delete')
{
	if (! isset($_POST['tie'])) die('MISSING_ARG');
	if (! mysql_query("delete from `tie` where `id` = '$tie'")) die(mysql_error());
	echo "OK";
}
else if ($action == 'update')
{
	if (! isset($_POST['newid']) || ! isset($_POST['status']) || ! isset($_POST['comments']) || ! isset($_POST['tie'])) die('MISSING_ARG');
	if (! mysql_query("update `tie` set `id` = '$newid', `status` = '$status', `comments` = '$comments' where `id` = '$tie'")) die(mysql_error());
	echo "OK";
}
else if ($action == 'histdel')
{
	if (! isset($_POST['id'])) die('MISSING_ARG');
	if (! mysql_query("delete from `tieBorrow` where `id` = '$id'")) die(mysql_error());
	echo "OK";
}
else if ($action == 'history')
{
	if (! isset($_POST['tie'])) die('MISSING_ARG');
	$results = mysql_query("select `id`, `member`, `dateOut`, `dateIn` from `tieBorrow` where `tie` = '$tie' order by `dateOut` asc");
	if (! $results) die(mysql_error());
	echo "<table><tr><th></th><th>Member</th><th>Date Borrowed</th><th>Date Returned</th></tr>";
	while ($row = mysql_fetch_array($results)) echo "<tr><td><button type='button' class='btn btn-link hist_del' data-id='$row[id]'><i class='icon-remove'></i></button></td><td>" . fullNameFromEmail($row['member']) . "</td><td>$row[dateOut]</td><td>" . ($row['dateIn'] == '' ? '--' : $row['dateIn']) . "</td></tr>";
	echo "</table>";
}
else if ($action == 'editform')
{
	if (! isset($_POST['tie'])) die('MISSING_ARG');
	$tiearr = mysql_fetch_array(mysql_query("select * from `tie` where `id` = '$tie'"));
	echo "<form class='form-horizontal' id='tie_form'>";
	echo "<div class='control-group'><label class='control-label'>Number</label><div class='controls'><input type='number' id='tie_num' value='$tiearr[id]'></div></div>";
	echo "<div class='control-group'><label class='control-label'>Status</label><div class='controls'>";
	echo "<select id='tie_status'>";
	$result = mysql_query("select `name` from `tieStatus`");
	while ($row = mysql_fetch_array($result)) echo "<option value='$row[name]'" . ($tiearr['status'] == $row['name'] ? " selected" : "") . ">$row[name]</option>";
	echo "</select></div></div>";
	echo "<div class='control-group'><label class='control-label'>Comments</label><div class='controls'><textarea id='tie_comments'>" . htmlspecialchars($tiearr['comments']) . "</textarea></div></div>";
	echo "<div class='control-group'><div class='controls'><button type='submit' class='btn'>Submit</button><span style='margin-right: 10px'></span><button type='button' class='btn tie_delete' data-tie='$tie'>Delete</button></div></div></form>";
}
else die('HUH?');
?>
