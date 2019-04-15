<?php
require_once('functions.php');
$email = $_POST['email'];
forgotPasswordEmail($email);
echo "Reset link sent to $email.";
?>
