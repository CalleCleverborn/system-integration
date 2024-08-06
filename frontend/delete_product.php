<?php
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['isAdmin']) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'DELETE',
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents("https://sysint-c4bc6h8gv-carl-cleverborns-projects.vercel.app/products/$product_id", false, $context);

    if ($result === FALSE) {
        echo "Error deleting product.";
    } else {
        header("Location: index.php");
        exit();
    }
} else {
    echo "Invalid product ID.";
}
?>

?>