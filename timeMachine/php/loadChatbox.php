<?php
require_once('functions.php');
$userEmail = $_COOKIE['email'];

$scroll = $_POST['scroll'];

loadChatbox($scroll);

?>
