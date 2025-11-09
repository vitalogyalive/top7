<?php

include("common.inc");

printr_log("login.php", "_POST", $_POST);
printr_log("login.php", "_SESSION", $_SESSION);

# TODO See how to use the flag $captcha
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

SessionManager::start();

// Validate CSRF token (new secure method) or legacy token
$csrf_valid = false;
if (isset($_POST['csrf_token'])) {
	$csrf_valid = CsrfToken::validate($_POST['csrf_token']);
	printr_log("login.php", "csrf_token_validation", $csrf_valid);
} elseif (isset($_POST['token']) && isset($_SESSION['token'])
	&& !empty($_POST['token']) && !empty($_SESSION['token'])
	&& $_POST['token'] == $_SESSION['token']) {
	// Legacy token check for backward compatibility
	$csrf_valid = true;
	printr_log("login.php", "legacy_token_validation", true);
}

if ($csrf_valid) {

	if( isset( $_POST['login']) and isset( $_POST['password'])) {

		// Admin login
		if( $_POST['login'] == c_admin_login and $_POST['password'] == c_admin_password) {

			init_sql();
			init_time_session();

			// Create admin session
			$adminData = [
				'id' => 0,
				'login' => $_POST['login'],
				'mode' => c_admin,
				'season' => TOP7_SEASON
			];
			SessionManager::login(0, $adminData);

			$_SESSION['pseudo'] 	= "";
			$_SESSION['display'] 	= c_top14;
			$_SESSION['captain'] 	= "";

			header( 'location: update_day');
		}

		// Player login
		if( $player = check_player( $_POST)) {

			init_time_session();

			// Create player session using new system
			$playerData = [
				'id' => $player['player'],
				'login' => $player['email'],
				'mode' => c_guest,
				'season' => $player['season']
			];
			SessionManager::login($player['player'], $playerData);

			// Keep legacy session variables for backward compatibility
			$_SESSION['pseudo'] 	= $player['pseudo'];
			$_SESSION['player'] 	= $player['player'];
			$_SESSION['captain'] 	= $player['captain'];
			$_SESSION['top7team'] 	= $player['team'];
			$_SESSION['display'] 	= c_top7;
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
} else {
	printr_log("login.php", "csrf_token_validation_failed", true);
}

echo '<meta http-equiv="refresh" content="0;URL=index">';

?>
