<?php

	require_once('config.php');

	MYSQL_CONNECT($database_server, $database_user, $database_password) or die ( "<H3>Server unreachable</H3>");
        MYSQL_SELECT_DB($database_name) or die ( "<H3>Database non existent</H3>");

?>
