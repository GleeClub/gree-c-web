<html>
<body>
<?php 
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];
echo '<div class="block span3">';
echo '
	Enter your email below:<br />
	<form class="form-inline" id="forgotPasswordForm" name="resetform" method="post">
	<input type="text" name="email" class="input-medium" /> <br />
	<button type="button" class="btn" id="sendLinkButton">Send Link</button>
	<a type="button" class="btn" href="#stats">Back</a>
	</form>';
echo '</div>';
?>
</body>
</html>