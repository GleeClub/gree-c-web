<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];

$html = "
<div class='block span12' id='filters'>
	<div class='span12' id='memberTypeFilters'>
		<form class='form-inline'>
		  <fieldset>
			<legend>Members</legend>
				<div class='span12'>
					<button type='button' class='btn' name='member' id='active'>Active Members</button>
					<button type='button' class='btn' name='member' id='inactive'>Inactive Members</button>
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
