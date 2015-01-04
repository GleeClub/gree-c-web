<?php
require_once('functions.php');

if (! isOfficer($_COOKIE['email'])) die("Access denied");
setcookie('email', $_POST['user'], time()+60*60*24*120, '/', false, false);
header("Location: /buzz");
?>

