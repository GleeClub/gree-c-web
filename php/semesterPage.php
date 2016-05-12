<?php
require('./functions.php');

/**
* Return a form with which a new semester can be added
*/
function newSemesterForm($btnClasses=''){
	$html = "
	<div id='newSemesterDiv' class=\"semesterDiv\">
		<form id='newSemesterForm'>
		  <fieldset>
		    <legend>New Semester Info</legend>
		    <label>New Semester Name (e.g. 'Fall 2012')</label>
		    <input type='text' class=\"semesterName invalid\" placeholder='Type something…' id='newSemesterName' name='name'>
		    <label>First day of the semester</label>
			<div class=\"input-append\">
				<input class=\"span1 invalid DD\" type=\"text\" placeholder=\"DD\" id='sDD' name='sDD'>
				<input class=\"span1 invalid MM\" type=\"text\" placeholder=\"MM\" id='sMM' name='sMM'>
				<input class=\"span1 invalid YYYY\" type=\"text\" placeholder=\"YYYY\" id='sYYYY' name='sYYYY'>
			</div>
			<label>Last day of the semester</label>
		    <div class=\"input-append\">
				<input class=\"span1 invalid DD\" type=\"text\" placeholder=\"DD\" id='eDD' name='eDD'>
				<input class=\"span1 invalid MM\" type=\"text\" placeholder=\"MM\" id='eMM' name='eMM'>
				<input class=\"span1 invalid YYYY\" type=\"text\" placeholder=\"YYYY\" id='eYYYY' name='eYYYY'>
			</div>
		    <br><button type='button' class=\"btn $btnClasses semesterSubmit\" id='semesterSubmit' disabled>Add the new semester!</button>
		  </fieldset>
		</form>
	</div>
	<script>checkAddSemesterFields();</script>";

	return $html;
}

/**
* Return a form with which a semester can be removed
*/
function removeSemesterForm($btnClasses=''){
	$html = "
	<div id='removeSemesterDiv' class=\"semesterDiv\">
		<form id='removeSemesterForm'>
			<fieldset>
			    <legend>Remove Semester</legend>
			    <select class=\"semesterSelect\" id=\"rmSemesterName\">
					<option value=''>Pick One</option>";

	$sql = "select semester from semester where 1";
	$result = mysql_query($sql);

	while($semesterInfo = mysql_fetch_array($result)){
		$semester = $semesterInfo['semester'];
		$html .= "
					<option value='$semester'>$semester</option>";
	}

	$html .= "</select>
				 <br><button type='button' class=\"btn $btnClasses semesterRemove\" id='semesterRemove' disabled>Remove this semester!</button>
			</fieldset>
		</form>
	</div>
	<script>checkRemoveSemesterFields();</script>";

	return $html;
}

/**
* Return a form with which a semester can be removed
*/
function changeSemesterForm(){
	$html = "
	<div id='changeSemesterDiv' class=\"semesterDiv\">
		<form id='changeSemesterForm'>
			<fieldset>
			    <legend>Change Semester</legend>
			    <select class=\"changeSemesterName\" id=\"changeSemesterName\">
					<option value=''>Pick One</option>";

	$sql = "select semester from semester where 1";
	$result = mysql_query($sql);

	while($semesterInfo = mysql_fetch_array($result)){
		$semester = $semesterInfo['semester'];
		$html .= "
					<option value='$semester'>$semester</option>";
	}

	$html .= "</select>
				 <br><button type='button' class=\"btn $btnClasses semesterChange\" id='semesterChange' disabled>Switch to this semester!</button>
			</fieldset>
		</form>
	</div>
	<script>checkChangeSemesterFields();</script>";

	return $html;
}

/**
* Return a page where the President can add a semester or change the current semester
*/
function semesterPage(){
	$html = "
	<div id='semester'>
		<div>
		    <h3>It Looks Like You Wanna Do Some Work With Semesters!</h3>
		</div>
		<div>
			<p>This is a form that will allow you to add and remove semesters in Gree-C-Web's database. It will also change the current semester.</p>
		    <p><strong>WARNING:</strong> Removing a semester will remove all related events and attendance info <strong>permanently</strong> and <strong>completely</strong> . And changing the current semester will change the entire face of the website.  Only stuff from the current semester is shown on the main website.  Changing the semester also changes every member's status to 'inactive' until he logs in and confirms himself.</p>
		    <p>To see old stuff, officers can click on the \"Actions &gt; Look Into The Past\" option.</p>
		    <p>With great power comes great potential to screw everyone over.  Use this feature wisely.</p>
			<p>Now, pick your poison:</p>
			<div id='semesterOptions'>
				<button type=\"button\" class=\"btn semesterChoice\" id=\"newSemester\" value=\"newSemesterDiv\">Add New Semester</button>
				<button type=\"button\" class=\"btn semesterChoice\" id=\"deleteSemester\" value=\"removeSemesterDiv\">Delete a Semester</button>
				<button type=\"button\" class=\"btn semesterChoice\" id=\"changeSemester\" value=\"changeSemesterDiv\">Change Current Semester</button>
			</div>
		</div>
		<br>
		".newSemesterForm()."
		".removeSemesterForm()."
		".changeSemesterForm()."
	</div>";

	return $html;
}

//if they aren't the President, lock 'em out
if(isUber($USER)) echo semesterPage();
else echo "<p id='title'><°o°> You're not supposed to be here, mate. <°o°></p>";

?>

