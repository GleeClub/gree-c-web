<?php
require_once('functions.php');
$userEmail = $_COOKIE['email'];

$sql = "select * from validSemester where 1 order by beginning desc";
$semesters = mysql_query($sql);

$sql = "select * from eventType where 1 order by typeName asc";
$types = mysql_query($sql);

$html = "
<div class='block span6' id='filters'>
	<div class='span6' id='semesterFilters'>
		<form class='form-inline'>
		  <fieldset>
			<h3>Choose the Event's Semester</h3><br>
			<div class='span6'>";

while($row = mysql_fetch_array($semesters)){
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

while($row = mysql_fetch_array($types)){
	$typeName = $row['typeName'];
	$typeNo = $row['typeNo'];
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
