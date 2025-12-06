<?php


	include("common.inc");
	check_session();

	if( $_SERVER['REQUEST_METHOD'] == 'POST') {

#echo "<pre>"; print_r($_POST); echo "</pre>";
		$p = $_POST;
		if( isset( $_POST['button'])) {
			if( $_POST['button'] == "Entrer" ) {
				// CSRF protection
				if (!isset($_POST['csrf_token']) || !\Top7\Security\CsrfToken::validate($_POST['csrf_token'])) {
					error_log('CSRF validation failed in update_match.php');
					header('location: update_day');
					exit;
				}
				init_admin_sql();
				update_match( $_POST);
			}
		}
	}

	if( $_SERVER['REQUEST_METHOD'] == 'GET') {

#echo "<pre>"; print_r($_GET); echo "</pre>";
		$p = $_GET;
	}

	header( 'location: update_day');

?>
