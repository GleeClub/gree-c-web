<?php
require_once('functions.php');

$res = query("select `url` from `gdocs` where `name` = ?", [$_POST["name"]], QONE);
if (! $res) err("No such document");
echo "OK\n" . $res['url'];
?>
