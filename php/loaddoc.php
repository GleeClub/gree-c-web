<?php
require_once('functions.php');

$name = mysql_real_escape_string($_POST['name']);
$query = mysql_query("select `url` from `gdocs` where `name` = '$name'");
if (mysql_num_rows($query) == 0) die("No such document");
$result = mysql_fetch_array($query);
echo "OK\n" . $result['url'];

?>
