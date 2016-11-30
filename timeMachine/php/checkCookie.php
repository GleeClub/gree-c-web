<?php

	print_r($_COOKIE);
	setcookie('email', '', time()+60*60*24*120, '/', false, false);
	
?>