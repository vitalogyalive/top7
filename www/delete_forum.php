<?php


	include("common.inc");
	check_session();

	init_admin_sql();
	$success = delete_forum( $_GET, $_SESSION);

	// Redirect back to display page
	header( 'location: display');
?>
