<?php


	include("common.inc");
	check_session();

	// Only accept POST requests for delete operations
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		error_log('DELETE request must be POST in delete_forum.php');
		header('location: display');
		exit;
	}

	// CSRF protection
	if (!isset($_POST['csrf_token']) || !\Top7\Security\CsrfToken::validate($_POST['csrf_token'])) {
		error_log('CSRF validation failed in delete_forum.php');
		header('location: display');
		exit;
	}

	init_admin_sql();
	$success = delete_forum( $_POST, $_SESSION);

	// Redirect back to display page
	header( 'location: display');
?>
