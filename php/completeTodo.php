<?php
require_once('functions.php');
$id = $_POST['id'];
$status = $_POST['status'];

query("update `todo` set `completed` = ? where `id` = ?", [$status == "complete" ? 1 : 0, $id]);
?>
