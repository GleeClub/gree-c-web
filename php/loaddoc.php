<?php
require_once('functions.php');

$res = query("select `url` from `gdocs` where `name` = ?", [$name], QONE);
if (! $res) die("No such document");
echo "OK\n" . $res['url'];
?>
