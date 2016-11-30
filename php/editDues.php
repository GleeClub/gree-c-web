<?php
require_once("functions.php");
if (! $USER || ! isOfficer($USER)) die("Access denied");
if (isset($_POST["item"]))
{
	$item = mysql_real_escape_string($_POST["item"]);
	$amount = mysql_real_escape_string($_POST["amount"]);
	if (! mysql_query("update `fee` set `amount` = '$amount' where `id` = '$item' and `choir` = '$CHOIR'")) die("Error: " . mysql_error());
	echo "OK";
	exit(0);
}

echo "<style>input.dues-input { width: 5em; margin-bottom: 0px; }</style><table><tr><th>Item</th><th>Amount</th></tr>";
$query = mysql_query("select `id`, `name`, `amount` from `fee` where `choir` = '$CHOIR'");
while ($row = mysql_fetch_array($query)) echo "<tr><td>" . $row["name"] . "</td><td><input class='dues-input' type='number' data-item='" . $row["id"] . "' value='" . $row["amount"] . "'></input><button class='btn dues-submit'>Go</button></td></tr>";
?>

