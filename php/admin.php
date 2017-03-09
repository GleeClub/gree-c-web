<?php
require_once('functions.php');

if (! isUber($USER)) die("Go home, earthling.");
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
		echo "OK";
	}
	else if ($_POST["type"] == "dues")
	{
		$item = mysql_real_escape_string($_POST["item"]);
		$amount = mysql_real_escape_string($_POST["amount"]);
		if (! mysql_query("update `fee` set `amount` = '$amount' where `id` = '$item' and `choir` = '$CHOIR'")) die("Error: " . mysql_error());
		echo "OK";
	}
	exit(0);
}

echo "<div class='block span6'><style>th { text-align: left; } td { padding-right: 40px; }</style><h3>Positions</h3><table><tr><th>Position</th><th>Member</th></tr>";
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

echo "<div class='block span5'><style>input.dues-input { width: 5em; margin-bottom: 0px; }</style><h3>Dues</h3><table><tr><th>Item</th><th>Amount</th></tr>";
$query = mysql_query("select `id`, `name`, `amount` from `fee` where `choir` = '$CHOIR'");
while ($row = mysql_fetch_array($query)) echo "<tr><td>" . $row["name"] . "</td><td><input class='dues-input' type='number' data-item='" . $row["id"] . "' value='" . $row["amount"] . "'></input><button class='btn dues-submit'>Go</button></td></tr>";
echo "</table></div>";

echo "<style>table.docs th { text-align: left; } .docurl { width: 400px; margin-bottom: 0px !important; }</style>";
echo "<div class='block span11'><h3>Document Links</h3><table class='docs'><tr><th>Document</th><th>Location</th></tr>";
$query = mysql_query("select `name`, `url` from `gdocs`");
while ($row = mysql_fetch_array($query))
{
	echo "<tr><td>$row[name]</td><td><input type='text' class='docurl' name='$row[name]' value='$row[url]'></td><td><button type='button' class='btn urlchange'>Change</button><button type='button' class='btn urldel'><i class='icon-remove'></i></td></tr>";
}
echo "<tr><td><input class='docurl' id='newname' type='text' style='width: 10em'></td><td><button id='urladd' type='button' class='btn'><i class='icon-plus'></i></button></td></tr></table></div>";
?>
