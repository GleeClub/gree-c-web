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
		</fieldset>
		<fieldset>
			<legend>Members</legend>
				<div class='span12'>
					<button type='button' class='btn' name='member' id='active'>Active Members</button>
					<button type='button' class='btn' name='member' id='inactive'>Inactive Members</button>
				</div>";

$html .= "
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
