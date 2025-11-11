<?php

	include("common.inc");

	// Start session FIRST - before accessing $_SESSION
	session_start();

	printr_log("login.php", "_POST", $_POST);
	printr_log("login.php", "_SESSION", $_SESSION);

	# TODO See how to use the flag $captcha
	if (isset($_POST['g-recaptcha-response'])) {
		$captcha = $_POST['g-recaptcha-response'];
		print_log("login.php", "captcha", $captcha);
		print_log("login.php", "request", "https://www.google.com/recaptcha/api/siteverify?secret=" . c_recaptcha_secret_key . "&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
		$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . c_recaptcha_secret_key . "&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
		$captcha_response = json_decode($response, true);
		printr_log("login.php", "captcha_response", $captcha_response);
	} else {
		$captcha = false;
	}

	// CSRF Protection (Phase 1, Task 1.1.2) - Only validate on POST requests
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if (!isset($_POST['csrf_token']) || !\Top7\Security\CsrfToken::validate($_POST['csrf_token'])) {
			error_log('CSRF token validation failed in login.php');
			echo '<meta http-equiv="refresh" content="0;URL=index">';
			exit;
		}
	}

	if( isset( $_POST['login']) and isset( $_POST['password'])) {


			if( $_POST['login'] == c_admin_login and $_POST['password'] == c_admin_password) {

				init_sql();
				init_time_session();
				$_SESSION['login'] 		= $_POST['login'];
				$_SESSION['pseudo'] 	= "";
				$_SESSION['mode'] 		= c_admin;
				$_SESSION['display'] 	= c_top14;
				$_SESSION['captain'] 	= "";

				header( 'location: update_day');
			}

			if( $player = check_player( $_POST)) {

				init_time_session();
				$_SESSION['login'] 	    = $player['email'];
				$_SESSION['pseudo'] 	= $player['pseudo'];
				$_SESSION['player'] 	= $player['player'];
				$_SESSION['captain'] 	= $player['captain'];
				$_SESSION['top7team'] 	= $player['team'];
				$_SESSION['display'] 	= c_top7;
				$_SESSION['mode'] 		= c_guest;
				$_SESSION['game']       = c_disable;
				$_SESSION['register_new_season'] = 0;
				$_SESSION['top7team_to_register'] = $player['team'];
				$top7_season = get_top7_season();
				$_SESSION['season'] = $top7_season['Id'];
				if( $player['season'] != $top7_season['Id']) { # player is not registered for current season
					$_SESSION['top7team'] = 0;
					if( $player['captain']) $_SESSION['register_new_season'] = true; # captain can register same team
					else $_SESSION['register_new_season'] = false;
					$_SESSION['top7team_to_register'] = $player['team'];
					$status = array("team" => c_team_disable, "player" => c_player_disable);
				} else {
					$_SESSION['register_new_season'] = false; 
					$status = check_status_player( $_SESSION);
				}

				if( $status['team'] == c_team_waiting) {
					$_SESSION['game']   = c_not_opened;
					header( 'location: team');
				}
				else {

					if( $status['team'] == c_team_enable and $status['player'] == c_player_enable) $game = check_date_player( $_SESSION);
					else $game = c_not_opened;

					$_SESSION['game'] = $game;

					if( $game == c_enable)	{
						$_SESSION['display'] = c_top7_player;
						$_SESSION['mode']    = c_player;
					}
					if( $game == c_season_over)	{
						$_SESSION['display'] = c_top_final;
						$_SESSION['mode']    = c_player;
						$_SESSION['status']  = c_cannot_play;
					}
	#header( 'location: display');
					echo "<script>document.location.replace('display'); </script>";
				}

			}
			else {
				$msg = $_POST['login'] . " est inconnu au bataillon !"; 
	#echo "<script>alert(\"$msg\");</script>\n";
		}

	}

	echo '<meta http-equiv="refresh" content="0;URL=index">';
	
?>