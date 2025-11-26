<?php


	require_once 'common.inc';
    require_once 'src/Display/PageRenderer.php';
    use Top7\Display\PageRenderer;

	check_session();


    PageRenderer::header('top7 params', 'Top7 - Params');
	init_sql();

#echo "<pre>";print_r($_POST);echo "</pre>";
	if( isset( $_POST['update'])) {
		if( $_POST['update'] == c_phase_finale) update_rank_phase_finale( $_SESSION);
	}
	if( isset( $_POST['section'])) {
		$section = $_POST['section'];
		$name 	 = $_POST['name'];
		$date    = $_POST['date'];
		if( $section == "dates") update_season_dates( $_SESSION, $name, $date); 
	}


	echo "<div class=\"container mx-auto px-4 py-8 flex flex-col items-center space-y-6\">\n";
	params( $_SESSION);
	echo "<br>\n";
    echo "</div>\n";
    
    PageRenderer::footer();
?>