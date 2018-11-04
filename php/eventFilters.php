<?php
require_once('functions.php');

$html = "
<div class='block span6' id='filters'>
	<div class='span6' id='semesterFilters'>
		<form class='form-inline'>
		  <fieldset>
			<h3>Choose the Event's Semester</h3><br>
			<div class='span6'>";

foreach (query("select * from 'semester' order by `beginning` desc", [], QALL) as $row)
{
	$semester = $row['semester'];
	$html .= "
					<button type='button' class='btn' name='semester' id='$semester'>$semester</button>";
}

$html .= "
			</div>
		</fieldset><br>
		<fieldset>
			<h3>Choose the Event's Type</h3><br>
			<div class='span6'>";

foreach (query("select * from `eventType` order by `name` asc", [], QALL) as $row)
{
	$typeName = $row['name'];
	$typeNo = $row['id'];
	$html .= "
					<button type='button' class='btn' name='type' id='$typeNo'>$typeName</button>";
}

$html .= "
				</div>
			</fieldset>
		</form>
		<div id='loadingBackground' style=\"visibility:hidden;\">
		</div>
		<div id='loadingImage' style=\"visibility:hidden;\">
			<img src='/images/link_running.gif' width=\"100\">
		</div>
	</div>
</div>";

echo $html;

?>
