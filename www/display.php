<?php

	require_once 'common.inc';
    require_once 'src/Display/PageRenderer.php';
    use Top7\Display\PageRenderer;

	check_session();

	init_sql();


	$alert = 0;
	if( isset( $_SESSION['alert'])) {
		$alert = $_SESSION['alert'];
		unset( $_SESSION['alert']);
	}




	if( isset( $_SESSION['display'])) 	$display = $_SESSION['display'];
	if( isset( $_POST['display'])) 		$display = $_POST['display'];

	if( isset( $_SESSION['day'])) 	$day = $_SESSION['day'];
	else $day = get_day_from_date();

	if( isset( $_SESSION['next'])) 	$next = $_SESSION['next'];
	if( isset( $_POST['top'])) 	$display=$next;

	$game = 0;
	if( isset( $_SESSION['game'])) 	$game = $_SESSION['game'];
	#if( isset( $_POST['game'])) 	$game = mysql_real_escape_string( $_POST['game']);

	if( isset( $_SESSION['mode'])) 	$mode = $_SESSION['mode'];

	if( isset( $_POST['selected_team'])) 	$_SESSION['selected_team'] = $_POST['selected_team'];
	if( isset( $_POST['contested_team'])) 	$_SESSION['contested_team'] = $_POST['contested_team'];

	if( isset( $_POST['next'])) $day=$day+1;
	if( isset( $_POST['prev'])) $day=$day-1;
	$min_day	= 1;
	$max_day	= c_finale_day;
	if( $day > $max_day) $day = $max_day;
	if( $day < $min_day) $day = $min_day;

	if( $mode == c_player and $game == c_enable and $display == c_top7_player) $day = get_day_from_date();
	$_SESSION['day'] 	= $day;
	$_SESSION['display'] 	= $display;
	$_SESSION['game'] 	= $game;
	$_SESSION['top14team'] 	= 0;

	if(!isset($_SESSION['display_stats'])) $_SESSION['display_stats'] = c_stats_by_player;

	session_write_close();

    // Modern Header replacement for init_deadline()
    $bodyAttributes = '';
    if (isset($_SESSION['deadline'])) {
        $deadline = $_SESSION['deadline'];
        // Ensure now() is available, it should be via common.inc
        $t = $deadline - now();
        if ($t > 0) {
             $bodyAttributes = 'onload="display_c(' . $t . ');"';
        }
    }
    
    PageRenderer::header('top7 display', 'Top7 - Jeu', $bodyAttributes);

	echo "<div class=\"container mx-auto px-4 py-8 flex flex-col items-center space-y-4\">
";
	put_player_link( $_SESSION);
	put_status( $_SESSION);
	put_nav( $_SESSION);
   	if( $display == c_top_final) display_final( $_SESSION);
   	else display( $_SESSION);
	put_bottom_info( $_SESSION);
	put_forum( $_SESSION);
	echo "</div>\n";

	if( $alert) print_alert( $alert);
	
    PageRenderer::footer();
?>