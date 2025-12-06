<?php


	include("common.inc");
	check_session();

	// CSRF protection
	if (!isset($_POST['csrf_token']) || !\Top7\Security\CsrfToken::validate($_POST['csrf_token'])) {
		error_log('CSRF validation failed in update_forum.php');
		header('location: display');
		exit;
	}

	init_admin_sql();
	update_forum( $_POST, $_SESSION);
	send_email_forum( $_POST, $_SESSION);

	header( 'location: display');
?>
