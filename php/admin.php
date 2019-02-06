<?php
require_once('functions.php');

if (! hasPosition($USER, "President") && ! hasPosition($USER, "Webmaster")) err("You do not have permission to access this page");
if (! $CHOIR) err("Choir is not set");

function uniformRow($id, $name, $desc)
{
	return "<tr><td>$id</td><td><input name='name' class='uniform-name' type='text' style='width: 10em' value='$name'></td><td><textarea name='desc' class='uniform-desc' style='width: 30em; height: 4em'>$desc</textarea></td><td data-uniform='$id'><button type='button' class='btn uniform-change'>Change</button><button type='button' class='btn uniform-delete'><i class='icon-remove'></i></button></td></tr>";
}

if (isset($_POST["type"]))
{
	// TODO Validate that necessary parameters are present
	if ($_POST["type"] == "doclink")
	{
		$name = $_POST['name'];
		$url = $_POST['url'];
		if (preg_match("[^A-Za-z0-9 _-]")) err("Permitted characters in name: A-Z a-z 0-9 underscore hyphen space");
		if ($_POST['action'] == "delete")
			query("delete from `gdocs` where `name` = ? and `choir` = ?", [$name, $choir]);
		else if (query("select * from `gdocs` where `name` = ?", [$name], QCOUNT) > 0)
			query("update `gdocs` set `url` = ? where `name` = ? and `choir` = ?", [$url, $name, $CHOIR]);
		else
			query("insert into `gdocs` (`name`, `choir`, `url`) values (?, ?, ?)", [$name, $CHOIR, $url]);
	}
	else if ($_POST["type"] == "dues")
	{
		$item = $_POST["item"];
		$amount = $_POST["amount"];
		query("update `fee` set `amount` = ? where `id` = ? and `choir` = ?", [$amount, $item, $CHOIR]);
	}
	else if ($_POST["type"] == "perm")
	{
		#if (! hasPermission("edit-permissions")) err("Error: You cannot edit permissions");
		$role = $_POST["role"];
		$perm = $_POST["perm"];
		$evtype = false;
		if (isset($_POST["evtype"])) $evtype = $_POST["evtype"];
		$value = $_POST["enable"] == "true" ? true : false;
		$sqlbase = "select * from `rolePermission` where `role` = ? and `permission` = ?";
		$current = ($evtype ? query($sqlbase . " and `eventType` = ?", [$role, $perm, $evtype], QCOUNT) : query($sqlbase . " and `eventType` is null", [$role, $perm], QCOUNT)) > 0;
		if ($value && ! $current)
		{
			$sqlbase = "insert into `rolePermission` set `role` = ?, `permission` = ?";
			if ($evtype) query($sqlbase . ", `eventType` = ?", [$role, $perm, $evtype]);
			else query($sqlbase, [$role, $perm]);
		}
		else if (! $value && $current)
		{
			$sqlbase = "delete from `rolePermission` where `role` = ? and `permission` = ?";
			if ($evtype) query($sqlbase . " and `eventType` = ?", [$role, $perm, $evtype]);
			else query($sqlbase . " and `eventType` is null", [$role, $perm]);
		}
	}
	else if ($_POST["type"] == "uniform")
	{
		#if (! hasPermission("edit-uniforms")) err("Error: You cannot edit uniforms");
		$action = $_POST["action"];
		if (! isset($_POST["id"])) err("Missing event ID");
		$id = $_POST["id"];
		$name = $_POST["name"];
		$desc = $_POST["desc"];
		if ($action == "add")
		{
			if (! isset($_POST["name"])) err("Missing name parameter");
			if (! isset($_POST["desc"])) err("Missing desc parameter");
			query("insert into `uniform` (`id`, `choir`, `name`, `description`) values (?, ?, ?, ?)", [$id, $CHOIR, $name, $desc]);
			echo "OK\n" . uniformRow($id, $name, $desc);
			exit(0);
		}
		else if ($action == "delete")
			query("delete from `uniform` where `choir` = ? and `id` = ?", [$CHOIR, $id]);
		else if ($action == "edit")
		{
			if (! isset($_POST["name"])) err("Missing name parameter");
			if (! isset($_POST["desc"])) err("Missing desc parameter");
			query("update `uniform` set `name` = ?, `description` = ? where `choir` = ? and `id` = ?", [$name, $desc, $CHOIR, $id]);
		}
		else err("Invalid action " . $action);
	}
	else err("Invalid update type " . $_POST["type"]);
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
foreach (query("select * from `role` where `rank` > 0 and `choir` = ? order by `rank` asc", [$CHOIR], QALL) as $row)
{
	$subq = query("select `member`.`email` from `member`, `memberRole` where `memberRole`.`role` = ? and `memberRole`.`member` = `member`.`email`", [$row["id"]], QALL); // TODO Filter by semester
	for ($i = 0; $i < $row["quantity"]; $i++)
	{
		$member = ($i >= count($subq)) ? "" : $subq[$i]["email"];
		echo "<tr><td class='position'>$row[name]</td><td data-old='$member'>" . memberDropdown($member) . "</td></tr>";
	}
}
echo "</table></div>";

echo "<div class='block span4'><h3>Dues</h3><table><tr><th>Item</th><th>Amount</th></tr>";
foreach(query("select `id`, `name`, `amount` from `fee` where `choir` = ?", [$CHOIR], QALL) as $row)
	echo "<tr><td class=wtbl>" . $row["name"] . "</td><td><input class='dues-input' type='number' data-item='" . $row["id"] . "' value='" . $row["amount"] . "'></input><button class='btn dues-submit'>Go</button></td></tr>";
echo "</table></div>";

$roles = [];
$roleorder = [];
foreach (query("select `id`, `name` from `role` where `choir` = ? and `rank` > 0 order by `rank` asc", [$CHOIR], QALL) as $row)
{
	$roles[$row["id"]] = $row["name"];
	$roleorder[] = $row["id"];
}
$evtypes = [];
foreach (query("select `id` from `eventType` order by `weight` asc", [], QALL) as $row) $evtypes[] = $row["id"];
$perms = [];
foreach (query("select * from `permission`", [], QALL) as $row)
{
	if ($row["type"] == "event")
	{
		$perms[] = array($row["name"], false);
		foreach ($evtypes as $type) $perms[] = array($row["name"], $type);
	}
	else $perms[] = array($row["name"], false);
}
$roleperms = [];
foreach ($roles as $id => $name) $roleperms[$id] = [];
foreach (query("select * from `rolePermission`", [], QALL) as $row) $roleperms[$row["role"]][] = array($row["permission"], $row["eventType"]);
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
foreach (query("select * from `uniform` where `choir` = ?", [$CHOIR], QALL) as $row) echo uniformRow($row["id"], $row["name"], $row["description"]);
echo "<tr><td><input name='id' id='new-uniform-id' type='text' style='width: 6em'></td><td><input name='name' id='new-uniform-name' type='text' style='width: 10em'></td><td><textarea name='desc' id='new-uniform-desc' style='width: 30em; height: 4em'></textarea></td><td><button type='button' class='btn uniform-add'><i class='icon-plus'></i></button></td></tr>";
echo "</table></div>";

echo "<div class='block span7'><h3>Document Links</h3><table class='docs'><tr><th>Document</th><th>Location</th></tr>";
foreach (query("select `name`, `url` from `gdocs`", [], QALL) as $row)
	echo "<tr><td class='wtbl'>$row[name]</td><td class='wtbl'><input type='text' class='docurl' name='$row[name]' value='$row[url]'></td><td><button type='button' class='btn urlchange'>Change</button><button type='button' class='btn urldel'><i class='icon-remove'></i></td></tr>";
echo "<tr><td class='wtbl'><input class='docurl' id='newname' type='text' style='width: 10em'></td><td><button id='urladd' type='button' class='btn'><i class='icon-plus'></i></button></td></tr></table></div>";
?>
