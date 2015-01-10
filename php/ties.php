<?
require_once('functions.php');
$userEmail = $_COOKIE['email'];

function stat2name($stat)
{
	$sql = "select `name` from `tieStatus` where `id` = '$stat'";
	$result = mysql_fetch_array(mysql_query($sql));
	return $result['name'];
}

$role = positionFromEmail($userEmail);
if ($role != 'President' && $role != 'VP')
{
	echo "DENIED";
	exit(1);
}
echo "<style>table { width: 100%; } th { text-align: left; }</style><table><tr><th>#</th><th>Status</th><th>Borrower</th><th>Comments</th><th></th></tr>";
$sql = "select * from `tie`";
$result = mysql_query($sql);
while ($row = mysql_fetch_array($result))
{
	$owner = '';
	if ($row['owner'])
	{
		$member = mysql_fetch_array(mysql_query("select `firstName`, `lastName`, `confirmed` from `member` where `email` = '" . $row['owner'] . "'"));
		$owner = "<span style='color: " . ($member['confirmed'] ? 'green' : 'red') . "'>" . $member['firstName'] . " " . $member['lastName'] . "</span>";
	}
	echo "<tr><td class='tie_id'>" . $row['id'] . "</td><td class='tie_status' data-status='" . $row['status'] . "'>" . stat2name($row['status']) . "</td><td class='tie_owner' data-member='" . $row['owner'] . "'>$owner</td><td class='tie_comments'>" . $row['comments'] . "</td><td><button type='button' class='btn tie_edit'>Edit</button></td></tr>";
}
echo "</table><span class='pull-right'><button type='button' class='btn' id='tie_add'>Add tie</button></span>";

?>
