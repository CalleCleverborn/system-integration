<?php
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['isAdmin']) {
    header("Location: login.php");
    exit();
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="products.csv"');

$csvContent = file_get_contents('http://localhost:3000/export/csv');
if ($csvContent === FALSE) {
    echo "Error fetching CSV data.";
    exit();
}

echo $csvContent;

exit();
?>