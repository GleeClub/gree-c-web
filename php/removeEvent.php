
<?php
session_start();
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

function eventFilters()
{
	$userEmail = $_COOKIE['email'];

	$sql = "select * from validSemester where 1 order by beginning desc";
	$semesters = mysql_query($sql);

	$sql = "select * from eventType where 1 order by typeName asc";
	$types = mysql_query($sql);

	$html = "
	<div id='filters'>
		<div id='semesterFilters'>
			<form class='form-inline'>
			  <fieldset>
				<h3>Choose the Event's Semester</h3><br>";

	while($row = mysql_fetch_array($semesters)){
		$semester = $row['semester'];
		$html .= "
				<button type='button' class='btn removeFilterBtn' name='semester' id='$semester'>$semester</button>";
	}

	$html .= "
			</fieldset><br>
		  	<fieldset>
				<h3>Choose the Event's Type</h3><br>";

	while($row = mysql_fetch_array($types)){
		$typeName = $row['typeName'];
		$typeNo = $row['typeNo'];
		$html .= "
				<button type='button' class='btn removeFilterBtn' name='type' id='$typeNo'>$typeName</button>";
	}

	$html .= "
				</fieldset>
			</form>
		</div>
	</div>";

	return $html;
}
?>

<div class="span6 block" id="removeEventDiv">
	<legend>Remove Events</legend>
	<?php
		echo eventFilters();
	?>
</div>
