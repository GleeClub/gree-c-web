<?php
require_once('functions.php');

function csvencode($row)
{
	$fields = [];
	foreach ($row as $field) $fields[] = '"' . addslashes($field) . '"';
	return implode(",", $fields) . "\r\n";
}

$filters = [];
if (isset($_GET["filter"]) && $_GET["filter"] != "") $filters = explode(",", $_GET["filter"]);
$data = [];
foreach(listMembers($filters) as $email => $name) $data[] = memberInfo($email);
$cols = ["#", "name", "section", "email", "phone", "location"];
if (hasPermission("view-user-private-details")) array_push($cols, "enrollment");
if (hasPermission("view-transactions")) array_push($cols, "balance", "dues");
if (hasPermission("view-user-private-details")) array_push($cols, "gigs", "score");

if (! isset($_GET["format"]) || $_GET["format"] == "normal")
{
	$gigreq = query("select `gigreq` from `semester` where `semester` = ?", [$SEMESTER], QONE);
	if (! $gigreq) err("Bad semester");
	$gigreq = $gigreq["gigreq"];

	echo '<style>
	table { width: 100%; }
	th { text-align: left; }
	th, td { vertical-align: top; padding-right: 10px; }
	div.tabbox { margin-bottom: 20px; padding: 10px; }
	span.spacer { display: inline-block; width: 20px; }
	</style>';

	echo "<table class='no-highlight' id='roster_table'><thead><tr>";
	foreach ($cols as $col) echo "<th>" . ucfirst($col) . "</th>";
	echo "</tr></thead><tbody>";
	$i = 1;
	foreach ($data as $member)
	{
		$email = $member["email"];
		echo "<tr data-member='$email'>";
		foreach ($cols as $col)
		{
			$value = $member[$col];
			echo "<td>";
			switch ($col)
			{
			case "#":
				echo $i;
				break;
			case "name":
				echo "<a href='#profile:$email'>" . $value["full"] . "</a>";
				break;
			case "email":
				echo "<a href='mailto:$value'>$value</a>";
				break;
			case "phone":
				echo "<a href='tel:$value'>$value</a>";
				break;
			case "enrollment":
				$colors = array("club" => "black", "class" => "blue", "inactive" => "gray");
				$color = $colors[$value];
				echo "<span style='color: $color'>$value</span>";
				break;
			case "balance":
			case "dues":
			case "gigs":
			case "score":
				$cutoffs = array("balance" => 0, "dues" => 0, "gigs" => $gigreq, "score" => 80);
				$color = "green";
				if ($value < $cutoffs[$col]) $color = "red";
				echo "<span style='color: $color'>$value</span>";
				break;
			case "section":
				echo(query("select `name` from `sectionType` where `id` = ? and `choir` = ?", [$value, $CHOIR], QONE)["name"]);
				break;
			case "location":
				echo $value;
				break;
			}
			echo "</td>";
		}
		echo "</tr>";
		$i++;
	}
	echo "</tbody></table>";
}
else if ($_GET["format"] == "csv")
{
	array_push($cols, "car", "major", "techYear", "hometown");
	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=\"members.csv\"");
	$uccols = [];
	foreach ($cols as $col) $uccols[] = ucfirst($col);
	echo csvencode($uccols);
	$i = 1;
	foreach ($data as $member)
	{
		$row = [];
		foreach ($cols as $col)
		{
			if ($col == "#") $row[] = "$i";
			else $row[] = $member[$col];
		}
		echo csvencode($row);
		$i++;
	}
}
else err("Unknown format \"" . $_GET["format"] . "\"");
?>
