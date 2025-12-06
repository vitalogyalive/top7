<?php


	include("common.inc");
	check_session();

	// CSRF protection
	if (!isset($_POST['csrf_token']) || !\Top7\Security\CsrfToken::validate($_POST['csrf_token'])) {
		error_log('CSRF validation failed in edit_forum.php');
		header('location: display');
		exit;
	}

	init_admin_sql();
	$success = edit_forum( $_POST, $_SESSION);

	// Redirect back to display page
	header( 'location: display');
?>
