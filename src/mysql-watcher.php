<?php

	// USAGE: mysql-watcher.php "query"

	$USER = "root";
	$PASS = "";
	$HOST = "localhost";
	$DB = "test";

	require "Watcher.php";
	$w = new Watcher();
	$w->set($USER, $PASS, $DB, $HOST);
	$w->watch($argv[1]);

?>
