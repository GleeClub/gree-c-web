<?php
require_once('functions.php');
$userEmail = getuser();

function transacTypes()
{
	$html = "<select class='ttype' style='width: 140px'>";
	$sql = "select `id`, `name` from `transacType` order by `name` asc";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result))
	{
		$html .= "<option value='" . $row['id'] . "'";
		if ($row['id'] == 'other') $html .= " selected";
		$html .= ">" . $row['name'] . "</option>";
	}
	$html .= "</select>";
	return $html;
}

echo "<tr class='trans_row'><td>" . memberDropdown() . "</td><td>" . transacTypes() . "</td><td>" . semesterDropdown() . "</td><td><input type='text' class='amount' placeholder='Amount' style='width: 60px'></input></td><td><input type='text' class='description' placeholder='Description' maxlength='500'></input></td><td><input type='checkbox' class='receipt'> Send receipt</td><td><button type='button' class='btn cancel'><i class='icon-remove'></i></button></td></tr>";
?>
