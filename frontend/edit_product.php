<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];

    $product = [
        'name' => $name,
        'price' => $price,
        'image' => $image
    ];

    $apiUrl = 'http://localhost:3000/products/' . $id;
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'PUT',
            'content' => json_encode($product),
        ],
    ];

    $context  = stream_context_create($options);
    $result = @file_get_contents($apiUrl, false, $context);

    if ($result === FALSE) {
        echo "<p>Failed to update product. Please check if the server is running.</p>";
    } else {
        echo "<p>Product updated successfully!</p>";
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $apiUrl = 'http://localhost:3000/products/' . $id;
    $response = @file_get_contents($apiUrl);
    $product = json_decode($response, true);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
</head>

<body>
    <h1>Edit Product</h1>
    <form action="edit_product.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo $product['name']; ?>" required>
        <br>
        <label for="price">Price:</label>
        <input type="text" id="price" name="price" value="<?php echo $product['price']; ?>" required>
        <br>
        <label for="image">Image URL:</label>
        <input type="text" id="image" name="image" value="<?php echo $product['image']; ?>">
        <br>
        <button type="submit">Update Product</button>
    </form>
</body>

</html>