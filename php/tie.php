<?
require_once('functions.php');

$member = $_POST['member'];
$tie = $_POST['tie'];
$action = $_POST['action'];
$newid = $_POST['newid'];
$status = $_POST['status'];
$comments = $_POST['comments'];
$id = $_POST['id'];

if (! hasPermission("edit-tie")) die('DENIED');
if (! isset($_POST['action'])) die('MISSING_ARG');
if ($action == 'return')
{
	if(! isset($_POST['member'])) die('MISSING_ARG');
	if (query("select * from `tieBorrow` where `member` = ? and `dateIn` is null", [$member], QCOUNT) == 0) die("$member does not have a tie out");
	query("update `tieBorrow` set `dateIn` = curdate() where `member` = ? and `dateIn` is null", [$member]);
	echo "OK";
}
else if ($action == 'checkout')
{
	if (! isset($_POST['tie']) || ! isset($_POST['member'])) die('MISSING_ARG');
	if (query("select * from `tie` where `id` = ?", [$tie], QCOUNT) == 0) die("Tie $tie does not exist");
	$ties = query("select * from `tieBorrow` where `member` = ? and `dateIn` is null", [$member], QONE);
	if ($ties)
	{
		$oldtie = $ties["tie"];
		die(fullNameFromEmail($member) . " already has tie $oldtie out");
	}
	$result = query("select * from `tieBorrow` where `tie` = ? and `dateIn` is null", [$tie], QONE);
	if ($result) die("Tie $tie is already checked out to " . fullNameFromEmail($result["member"]));
	query("insert into `tieBorrow` (`member`, `tie`, `dateOut`) values (?, ?, curdate())", [$member, $tie]);
	echo "OK";
}
else if ($action == 'add')
{
	if (! isset($_POST['tie'])) die('MISSING_ARG');
	if (! preg_match('/^\d+$/', $tie)) die("Invalid tie number \"$tie\"");
	if (query("select * from `tie` where `id` = ?", [$tie], QCOUNT) > 0) die("Tie $tie already exists");
	query("insert into `tie` set `id` = ?", [$tie]);
	echo "OK";
}
else if ($action == 'delete')
{
	if (! isset($_POST['tie'])) die('MISSING_ARG');
	query("delete from `tie` where `id` = ?", [$tie]);
	echo "OK";
}
else if ($action == 'update')
{
	if (! isset($_POST['newid']) || ! isset($_POST['status']) || ! isset($_POST['comments']) || ! isset($_POST['tie'])) die('MISSING_ARG');
	query("update `tie` set `id` = ?, `status` = ', `comments` = ? where `id` = ?", [$newid, $status, $comments, $tie]);
	echo "OK";
}
else if ($action == 'histdel')
{
	if (! isset($_POST['id'])) die('MISSING_ARG');
	query("delete from `tieBorrow` where `id` = ?", [$id]);
	echo "OK";
}
else if ($action == 'history')
{
	if (! isset($_POST['tie'])) die('MISSING_ARG');
	echo "<table><tr><th></th><th>Member</th><th>Date Borrowed</th><th>Date Returned</th></tr>";
	foreach (query("select `id`, `member`, `dateOut`, `dateIn` from `tieBorrow` where `tie` = ? order by `dateOut` asc", [$tie], QALL) as $row)
		echo "<tr><td><button type='button' class='btn btn-link hist_del' data-id='$row[id]'><i class='icon-remove'></i></button></td><td>" . fullNameFromEmail($row['member']) . "</td><td>$row[dateOut]</td><td>" . ($row['dateIn'] == '' ? '--' : $row['dateIn']) . "</td></tr>";
	echo "</table>";
}
else if ($action == 'editform')
{
	if (! isset($_POST['tie'])) die('MISSING_ARG');
	$tiearr = query("select * from `tie` where `id` = ?", [$tie], QONE);
	echo "<form class='form-horizontal' id='tie_form'>";
	echo "<div class='control-group'><label class='control-label'>Number</label><div class='controls'><input type='number' id='tie_num' value='$tiearr[id]'></div></div>";
	echo "<div class='control-group'><label class='control-label'>Status</label><div class='controls'>";
	echo "<select id='tie_status'>";
	foreach (query("select `name` from `tieStatus`", [], QALL) as $row) echo "<option value='$row[name]'" . ($tiearr['status'] == $row['name'] ? " selected" : "") . ">$row[name]</option>";
	echo "</select></div></div>";
	echo "<div class='control-group'><label class='control-label'>Comments</label><div class='controls'><textarea id='tie_comments'>" . htmlspecialchars($tiearr['comments']) . "</textarea></div></div>";
	echo "<div class='control-group'><div class='controls'><button type='submit' class='btn'>Submit</button><span style='margin-right: 10px'></span><button type='button' class='btn tie_delete' data-tie='$tie'>Delete</button></div></div></form>";
}
else die('HUH?');
?>
