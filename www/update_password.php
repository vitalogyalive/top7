<?php

	include("common.inc");
	print_header_password();


	init_sql();

	// CSRF Protection (Phase 1, Task 1.1.2) - Validate only if form is submitted
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['csrf_token']) || !\Top7\Security\CsrfToken::validate($_POST['csrf_token']))) {
		error_log('CSRF token validation failed in update_password.php');
		header('Location: password');
		exit;
	}

	$key = $status = $pseudo = $email = $password1 = $password2 = 0;
	if( isset( $_POST['pseudo'])) 		$pseudo		= $_POST['pseudo'];
	if( isset( $_POST['email'])) 		$email		= $_POST['email'];
	if( isset( $_POST['password1'])) 	$password1	= $_POST['password1'];
	if( isset( $_POST['password2'])) 	$password2	= $_POST['password2'];
	if( isset( $_POST['status'])) 	    	$status	= $_POST['status'];
	if( isset( $_POST['key'])) 	        $key	= $_POST['key'];

	if( $pseudo and $email and $password1 and $password2) {

		$errors = array();
		$min = 8; $max = 20;
		if( strlen( $password1) < $min) $errors[] = "Le mot de passe doit avoir au minimum $min caract�res";
		if( strlen( $password1) > $max) $errors[] = "Le mot de passe doit avoir au maximum $max caract�res";
		if( $password1 <> $password2)   $errors[] = "Les 2 mots de passe ne sont pas identiques";
		if( count( $errors)) {
            		put_new_password_form( $key, $status, $pseudo, $email, $errors);
			//register_message( $errors);
		}
		else {
			init_admin_sql();
			$player = update_register( $pseudo, $email, $password1); 
            		check_last_registered_player( $player);

	        	#header( 'location: index');
			#
			# see ticket 81
			echo "<form id=\"try_to_login\" method=\"post\" action=\"login\">";	
			echo "<input type=\"hidden\" name=\"login\" value=\"$email\">";
			echo "<input type=\"hidden\" name=\"password\" value=\"$password1\">";
			echo "</form>";
			echo "<script type=\"text/javascript\">document.getElementById('try_to_login').submit();</script>";

		}
	}

?>

</body>
</html>


