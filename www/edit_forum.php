<?php


	include("common.inc");
	check_session();

	init_admin_sql();
	$success = edit_forum( $_POST, $_SESSION);

	// Redirect back to display page
	header( 'location: display');
?>
