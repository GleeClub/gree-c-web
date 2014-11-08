<?
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
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
	echo "<tr><td class='tie_id'>" . $row['id'] . "</td><td class='tie_status' data-status='" . $row['status'] . "'>" . stat2name($row['status']) . "</td><td class='tie_owner' data-member='" . $row['owner'] . "'>" . fullNameFromEmail($row['owner']) . "</td><td class='tie_comments'>" . $row['comments'] . "</td><td><button type='button' class='btn tie_edit'>Edit</button></td></tr>";
}
echo "</table><span class='pull-right'><button type='button' class='btn' id='tie_add'>Add tie</button></span>";

?>
