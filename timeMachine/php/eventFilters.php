<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];

$sql = "select * from validSemester where 1 order by beginning desc";
$semesters = mysql_query($sql);

$sql = "select * from eventType where 1 order by typeName asc";
$types = mysql_query($sql);

$html = "
<div class='block span12' id='filters'>
	<div class='span12' id='semesterFilters'>
		<form class='form-inline'>
		  <fieldset>
			<legend>Semesters</legend>
			<div class='span12'>";

while($row = mysql_fetch_array($semesters)){
	$semester = $row['semester'];
	$html .= "
					<button type='button' class='btn' name='semester' id='$semester'>$semester</button>";
}

$html .= "
			</div>
		</fieldset><br>
	  	<fieldset>
			<legend>Types</legend>
			<div class='span12'>";

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