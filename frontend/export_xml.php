<?php
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['isAdmin']) {
    header("Location: login.php");
    exit();
}

header('Content-Type: application/xml');
header('Content-Disposition: attachment; filename="products.xml"');

$xmlContent = @file_get_contents('http://localhost:3000/export/xml');
if ($xmlContent === FALSE) {
    $error = error_get_last();
    echo "Error fetching XML data: " . $error['message'];
    exit();
}

echo $xmlContent;

exit();
?>