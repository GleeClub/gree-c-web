
<?php
require_once('functions.php');

function eventFilters()
{
	$userEmail = getuser();

	$sql = "select * from semester where 1 order by beginning desc";
	$semesters = mysql_query($sql);

	$sql = "select * from eventType where 1 order by name asc";
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
		$typeName = $row['name'];
		$typeNo = $row['id'];
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
