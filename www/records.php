<?php

	require_once 'common.inc';
    require_once 'src/Display/PageRenderer.php';
    use Top7\Display\PageRenderer;

	check_session();

    PageRenderer::header('top7 records', 'Top7 - Records');
	init_sql();


	if( isset( $_POST['display_stats'])) 	$_SESSION['display_stats'] = $_POST['display_stats'];
	$_SESSION['display'] = c_top7;


	echo "<div class=\"container mx-auto px-4 py-8 flex flex-col items-center space-y-6\">\n";
	put_player_link( $_SESSION);
	put_nav_records( $_SESSION);
	records( $_SESSION);

	echo "<br><br>\n";
	palmares( $_SESSION);

	echo "</div>\n";

    PageRenderer::footer();
?>