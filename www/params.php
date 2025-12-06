<?php


	include("common.inc");
	check_session();


	print_header();
	init_sql();

#echo "<pre>";print_r($_POST);echo "</pre>";

	// Helper function to validate CSRF for data modification operations
	$csrf_valid = function() {
		return isset($_POST['csrf_token']) && \Top7\Security\CsrfToken::validate($_POST['csrf_token']);
	};

	if( isset( $_POST['update'])) {
		// CSRF protection for season finalization
		if (!$csrf_valid()) {
			error_log('CSRF validation failed in params.php (update)');
			header('location: params');
			exit;
		}
		if( $_POST['update'] == c_phase_finale) update_rank_phase_finale( $_SESSION);
	}
	if( isset( $_POST['section'])) {
		// CSRF protection for date updates
		if (!$csrf_valid()) {
			error_log('CSRF validation failed in params.php (section)');
			header('location: params');
			exit;
		}
		$section = $_POST['section'];
		$name 	 = $_POST['name'];
		$date    = $_POST['date'];
		if( $section == "dates") update_season_dates( $_SESSION, $name, $date);
	}


	echo "<center>\n";
	params( $_SESSION);
	echo "<br>\n";
?>
</body>
</html>

