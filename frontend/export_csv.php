<?php
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['isAdmin']) {
    header("Location: login.php");
    exit();
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="products.csv"');

$apiUrl = 'https://sysserver-olsm5c0q3-carl-cleverborns-projects.vercel.app';

$csvContent = file_get_contents("$apiUrl/export/csv");
if ($csvContent === FALSE) {
    $error = error_get_last();
    echo "Error fetching CSV data: " . $error['message'];
    exit();
}

echo $csvContent;

exit();
?>