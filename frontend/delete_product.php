<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    $apiUrl = 'http://localhost:3000/products/' . $id;
    $options = [
        'http' => [
            'method' => 'DELETE'
        ],
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($apiUrl, false, $context);

    if ($result === FALSE) {
        echo "<p>Failed to delete product. Please check if the server is running.</p>";
    } else {
        echo "<p>Product deleted successfully!</p>";
    }
}
?>