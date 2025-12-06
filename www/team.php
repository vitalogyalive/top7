<?php

	include("common.inc");
	check_session();
	

	print_header();
	init_sql();


	if( isset( $_POST['team'])) {
		// CSRF protection
		if (!isset($_POST['csrf_token']) || !\Top7\Security\CsrfToken::validate($_POST['csrf_token'])) {
			error_log('CSRF validation failed in team.php');
			header('location: team');
			exit;
		}

		$name	= $_POST['team'];

		$errors = array();
		$min = c_min_team; $max = c_max_team;
		if( strlen( $name) < $min) 	$errors[] = "Le nom doit avoir au minimum $min caract�res";
		if( strlen( $name) > $max) 	$errors[] = "Le nom doit avoir au maximum $max caract�res";

		if( count( $errors)) {
			register_message( $errors);
		}
		else {
			$team = $_SESSION['top7team'];
			init_admin_sql();
			update_name_team( $name, $team);
		}
	}


	$top7team = 0;
	if( isset($_SESSION['top7team'])) $top7team = $_SESSION['top7team'];

	$_SESSION['display'] = c_info_team;

	echo "<center>\n";
	put_player_link( $_SESSION);
	if( $top7team > 0) put_nav( $_SESSION);
	#display_team_choice( $_SESSION);
	display_info_team( $_SESSION);
	put_bottom_info( $_SESSION);
	echo "</center>\n";

?>

</body>
</html>
