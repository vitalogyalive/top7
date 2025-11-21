<?php
// Don't include common.inc, it calls session_start()
session_start();

echo "Checking session...\n\n";
echo "Session ID: " . session_id() . "\n";
echo "Session data:\n";
print_r($_SESSION);

echo "\n\nRequired for checkSession:\n";
echo "- login isset: " . (isset($_SESSION['login']) ? 'YES' : 'NO') . "\n";
echo "- token isset: " . (isset($_SESSION['token']) ? 'YES' : 'NO') . "\n";
?>
