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

// Include and run the API
include(__DIR__ . '/www/agenda_api.php');
?>
