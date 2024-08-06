<?php
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['isAdmin']) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];

    $data = json_encode([
        'name' => $name,
        'price' => $price,
        'image' => $image
    ]);

    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => $data,
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents('system-integration-2tdfecbgh-carl-cleverborns-projects.vercel.app/products', false, $context);

    if ($result === FALSE) {
        echo "Error adding product.";
    } else {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Add Product</title>
</head>

<body>
    <h2>Add Product</h2>
    <form method="post" action="">
        Name: <input type="text" name="name" required><br>
        Price: <input type="number" name="price" required><br>
        Image URL: <input type="text" name="image" required><br>
        <input type="submit" value="Add Product">
    </form>
</body>

</html>