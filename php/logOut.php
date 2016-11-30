<?php

setcookie('email', '', time()-60*60*24*120, '/', false, false);
header("location:../index.php");

?>
