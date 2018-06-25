<?php
require_once('functions.php');

if (! hasPermission("uber")) die("Go home, earthling.");
if (! $CHOIR) die("Choir is not set");

if (isset($_POST["type"]))
{
	// TODO Validate that necessary parameters are present
	if ($_POST["type"] == "doclink")
	{
		$name = mysql_real_escape_string($_POST['name']);
		$url = mysql_real_escape_string($_POST['url']);
		if (preg_match("[^A-Za-z0-9 _-]")) die("Permitted characters in name: A-Z a-z 0-9 underscore hyphen space");
		if ($_POST['action'] == "delete")
		{
			if (! mysql_query("delete from `gdocs` where `name` = '$name' and `choir` = '$CHOIR'")) die("Couldn't delete $name link: " . mysql_error());
		}
		else if (mysql_num_rows(mysql_query("select * from `gdocs` where `name` = '$name'")) > 0)
		{
			if (! mysql_query("update `gdocs` set `url` = '$url' where `name` = '$name' and `choir` = '$CHOIR'")) die("Couldn't update $name link: " . mysql_error());
		}
		else
		{
			if (! mysql_query("insert into `gdocs` (`name`, `choir`, `url`) values ('$name', '$CHOIR', '$url')")) die("Couldn't create $name link: " . mysql_error());
		}
	}
	else if ($_POST["type"] == "dues")
	{
		$item = mysql_real_escape_string($_POST["item"]);
		$amount = mysql_real_escape_string($_POST["amount"]);
		if (! mysql_query("update `fee` set `amount` = '$amount' where `id` = '$item' and `choir` = '$CHOIR'")) die("Error: " . mysql_error());
	}
	else if ($_POST["type"] == "perm")
	{
		$role = mysql_real_escape_string($_POST["role"]);
		$perm = mysql_real_escape_string($_POST["perm"]);
		$evtype = false;
		if (isset($_POST["evtype"])) $evtype = mysql_real_escape_string($_POST["evtype"]);
		$value = $_POST["enable"] == "true" ? true : false;
		$query = mysql_query("select * from `rolePermission` where `role` = '$role' and `permission` = '$perm' and `eventType` " . ($evtype ? "= '$evtype'" : "is null"));
		if (! $query) die("Error: " . mysql_error());
		$current = mysql_num_rows($query) > 0 ? 1 : 0;
		if (! $query) die("Error: " . mysql_error());
		if ($value && ! $current)
		{
			if (! mysql_query("insert into `rolePermission` set `role` = '$role', `permission` = '$perm'" . ($evtype ? ", `eventType` = '$evtype'" : ""))) die("Error: " . mysql_error());
		}
		else if (! $value && $current)
		{
			if (! mysql_query("delete from `rolePermission` where `role` = '$role' and `permission` = '$perm'" . ($evtype ? " and `eventType` = '$evtype'" : " and `eventType` is null"))) die("Error: " . mysql_error());
		}
	}
	else if ($_POST["type"] == "uniform")
	{
		$action = $_POST["action"];
		if (! isset($_POST["id"])) die("Missing event ID");
		$id = mysql_real_escape_string($_POST["id"]);
		$name = mysql_real_escape_string($_POST["name"]);
		$desc = mysql_real_escape_string($_POST["desc"]);
		if ($action == "new")
		{
			if (! isset($_POST["name"])) die("Missing name parameter");
			if (! isset($_POST["desc"])) die("Missing desc parameter");
			if (! mysql_query("insert into `uniform` (`id`, `choir`, `name`, `description`) values ('$id', '$CHOIR', '$name', '$desc')")) die("Uniform creation failed: " . mysql_error());
		}
		else if ($action == "delete")
		{
			if (! mysql_query("delete from `uniform` where `choir` = '$CHOIR' and `id` = '$id'")) die("Uniform deletion failed: " . mysql_error());
		}
		else if ($action == "edit")
		{
			if (! isset($_POST["name"])) die("Missing name parameter");
			if (! isset($_POST["desc"])) die("Missing desc parameter");
			if (! mysql_query("update `uniform` set `name` = '$name', `description` = '$desc' where `choir` = '$CHOIR' and `id` = '$id'")) die("Uniform update failed: " . mysql_error());
		}
		else die("Invalid action " . $action);
	}
	else die("Invalid update type " . $_POST["type"]);
	echo "OK";
	exit(0);
} ?>
<style>
table.docs th
{
	text-align: left;
}
.docurl
{
	width: 400px;
	margin-bottom: 0px !important;
}
input.dues-input
{
	width: 5em;
	margin-bottom: 0px;
}
th
{
	text-align: left;
}
td.wtbl
{
	padding-right: 40px;
}
td.vertheader
{
	vertical-align: top;
	white-space: nowrap;
}
td.vertheader div
{
	writing-mode: tb-rl;
}
</style>
<?php
echo "<div class='block span6'><h3>Positions</h3><table><tr><th>Position</th><th>Member</th></tr>";
$query = mysql_query("select * from `role` where `rank` > 0 and `choir` = '$CHOIR' order by `rank` asc");
while ($row = mysql_fetch_array($query))
{
	$subq = mysql_query("select `member`.`email` from `member`, `memberRole` where `memberRole`.`role` = '$row[id]' and `memberRole`.`member` = `member`.`email`"); // TODO Filter by semester
	for ($i = 0; $i < $row['quantity']; $i++)
	{
		$member = mysql_fetch_array($subq);
		echo "<tr><td class='position'>$row[name]</td><td data-old='$member[email]'>" . ($member ? memberDropdown($member['email']) : memberDropdown("")) . "</td></tr>";
	}
}
echo "</table></div>";

echo "<div class='block span4'><h3>Dues</h3><table><tr><th>Item</th><th>Amount</th></tr>";
$query = mysql_query("select `id`, `name`, `amount` from `fee` where `choir` = '$CHOIR'");
while ($row = mysql_fetch_array($query)) echo "<tr><td class=wtbl>" . $row["name"] . "</td><td><input class='dues-input' type='number' data-item='" . $row["id"] . "' value='" . $row["amount"] . "'></input><button class='btn dues-submit'>Go</button></td></tr>";
echo "</table></div>";

$query = mysql_query("select `id`, `name` from `role` where `choir` = '$CHOIR' and `rank` > 0 order by `rank` asc");
if (! $query) die("Couldn't fetch roles: " . mysql_error());
$roles = [];
$roleorder = [];
while ($row = mysql_fetch_array($query))
{
	$roles[$row["id"]] = $row["name"];
	$roleorder[] = $row["id"];
}
$evtypes = [];
$query = mysql_query("select `id` from `eventType` order by `weight` asc");
if (! $query) die("Couldn't fetch event types: " . mysql_error());
while ($row = mysql_fetch_array($query)) $evtypes[] = $row["id"];
$query = mysql_query("select * from `permission`");
if (! $query) die("Couldn't fetch permissions: " . mysql_error());
$perms = [];
while ($row = mysql_fetch_array($query))
{
	if ($row["type"] == "event")
	{
		$perms[] = array($row["name"], false);
		foreach ($evtypes as $type) $perms[] = array($row["name"], $type);
	}
	else $perms[] = array($row["name"], false);
}
$query = mysql_query("select * from `rolePermission`");
if (! $query) die("Couldn't fetch role permissions: " . mysql_error());
$roleperms = [];
foreach ($roles as $id => $name) $roleperms[$id] = [];
while ($row = mysql_fetch_array($query)) $roleperms[$row["role"]][] = array($row["permission"], $row["eventType"]);
echo "<div class='block span5'><h3>Permissions</h3>";
echo "<table><th>";
foreach ($roleorder as $id) echo("<td class='vertheader'><div>" . $roles[$id] . "</div></th>");
echo "</td>";
foreach ($perms as $perm)
{
	echo "<tr><td style='white-space: nowrap'>" . $perm[0] . ($perm[1] ? ":" . $perm[1] : "") . "</td>";
	foreach ($roleorder as $id)
	{
		$name = $roles[$id];
		$hasperm = in_array($perm, $roleperms[$id]);
		echo "<td><input type='checkbox' data-role='$id' data-perm='" . $perm[0] . "'" . ($perm[1] ? " data-evtype='" . $perm[1] : "") . "' onclick='updateRolePerm($(this))'" . ($hasperm ? " checked" : "") . "></td>";
	}
	echo "</tr>";
}
echo "</table></div>";

echo "<div class='block span6'><h3>Uniforms</h3><table style='width: 100%'><tr><th>ID</th><th>Name</th><th>Description</th><th></th></tr>";
$query = mysql_query("select * from `uniform` where `choir` = '$CHOIR'");
if (! $query) die("Couldn't fetch uniforms: " . mysql_error());
while ($row = mysql_fetch_array($query))
{
	echo "<tr><td>" . $row["id"] . "</td><td><input name='name' type='text' style='width: 10em' value='" . $row["name"] . "'></td><td><textarea name='desc' style='width: 30em; height: 4em'>" . $row["description"] . "</textarea></td><td><button type='button' class='btn'>Change</button><button tpye='button' class='btn'><i class='icon-remove'></i></button></td></tr>";
}
echo "</table></div>";

echo "<div class='block span7'><h3>Document Links</h3><table class='docs'><tr><th>Document</th><th>Location</th></tr>";
$query = mysql_query("select `name`, `url` from `gdocs`");
while ($row = mysql_fetch_array($query))
{
	echo "<tr><td class='wtbl'>$row[name]</td><td class='wtbl'><input type='text' class='docurl' name='$row[name]' value='$row[url]'></td><td><button type='button' class='btn urlchange'>Change</button><button type='button' class='btn urldel'><i class='icon-remove'></i></td></tr>";
}
echo "<tr><td class='wtbl'><input class='docurl' id='newname' type='text' style='width: 10em'></td><td><button id='urladd' type='button' class='btn'><i class='icon-plus'></i></button></td></tr></table></div>";
?>
