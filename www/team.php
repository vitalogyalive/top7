<?php

	require_once 'common.inc';
    require_once 'src/Display/PageRenderer.php';

    use Top7\Display\PageRenderer;

	check_session();
	
    // Use modern header with Tailwind
    PageRenderer::header('top7 team', 'Top7 - Equipe');

	init_sql();


	if( isset( $_POST['team'])) {
		$name	= $_POST['team'];

		$errors = array();
		$min = c_min_team; $max = c_max_team;
		if( strlen( $name) < $min) 	$errors[] = "Le nom doit avoir au minimum $min caractres";
		if( strlen( $name) > $max) 	$errors[] = "Le nom doit avoir au maximum $max caractres";

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

    // Modern container wrapping legacy content
	echo "<div class=\"container mx-auto px-4 py-8 flex flex-col items-center space-y-6\">\n";
	put_player_link( $_SESSION);
	if( $top7team > 0) put_nav( $_SESSION);
	#display_team_choice( $_SESSION);
	display_info_team( $_SESSION);
	put_bottom_info( $_SESSION);
	echo "</div>\n";

    PageRenderer::footer();
?>