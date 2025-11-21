<?php
// Test the agenda API directly
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['action'] = 'list_events';
$_GET['month'] = date('Y-m');

// Simulate a logged-in session
session_start();
$_SESSION['season'] = 1;
$_SESSION['pseudo'] = 'test';
$_SESSION['player'] = 2;
$_SESSION['top7team'] = 1;
$_SESSION['captain'] = 0;

echo "Testing agenda API with session data:\n";
echo "Player: " . $_SESSION['player'] . "\n";
echo "Team: " . $_SESSION['top7team'] . "\n";
echo "Month: " . $_GET['month'] . "\n\n";

// Include and run the API
ob_start();
include('agenda_api.php');
$output = ob_get_clean();

echo "API Response:\n";
echo $output;
?>
