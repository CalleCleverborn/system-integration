<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];


    if (!is_numeric($price)) {
        echo "<p>Price must be a number.</p>";
    } elseif (!is_string($name) || empty($name) || is_numeric($name)) {
        echo "<p>Name must be a valid string and cannot be numeric.</p>";
    } else {
        $product = [
            'name' => $name,
            'price' => (float) $price,
            'image' => $image
        ];

        $apiUrl = 'http://localhost:3000/products';
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($product),
            ],
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($apiUrl, false, $context);

        if ($result === FALSE) {
            echo "<p>Failed to create product. Please check if the server is running and the data is correct.</p>";
        } else {
            echo "<p>Product created successfully!</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
</head>

<body>
    <h1>Add Product</h1>
    <form action="create_product.php" method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" required step="0.01">
        <br>
        <label for="image">Image URL:</label>
        <input type="text" id="image" name="image">
        <br>
        <button type="submit">Add Product</button>
    </form>
</body>

</html>