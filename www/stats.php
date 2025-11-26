<?php

	require_once 'common.inc';
    require_once 'src/Display/PageRenderer.php';
    use Top7\Display\PageRenderer;

	check_session();

    PageRenderer::header('top7 stats', 'Top7 - Stats');
	init_sql();


	if( isset( $_POST['display_stats'])) 	$_SESSION['display_stats'] = $_POST['display_stats'];
	$_SESSION['display'] = c_top7;


	echo "<div class=\"container mx-auto px-4 py-8 flex flex-col items-center space-y-6\">\n";
	put_player_link( $_SESSION);
	put_nav_stats( $_SESSION);

	// Lien vers les graphiques
	echo "<div style='margin: 20px 0; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>\n";
	echo "<a href='stats_graphs.php' style='color: white; text-decoration: none; font-size: 16px; font-weight: bold; display: flex; align-items: center; justify-content: center; gap: 10px;'>\n";
	echo "📊 Voir les Graphiques d\'Évolution\n";
	echo "<span style='font-size: 12px; background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px;'>Nouveau !</span>\n";
	echo "</a>\n";
	echo "</div>\n";

	stats( $_SESSION);
	put_stats_info( $_SESSION);
	echo "</div>\n";

    PageRenderer::footer();
?>