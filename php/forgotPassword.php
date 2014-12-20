<html>
<body>
<?php 
require_once('functions.php');
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