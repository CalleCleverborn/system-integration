<?php
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['isAdmin']) {
    header("Location: login.php");
    exit();
}

$product_id = $_GET['id'];
$product = json_decode(file_get_contents("https://sysint-callecleverborn-carl-cleverborns-projects.vercel.app/products/$product_id"), true);

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
            'method' => 'PUT',
            'content' => $data,
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents("https://sysint-callecleverborn-carl-cleverborns-projects.vercel.app/products/$product_id", false, $context);

    if ($result === FALSE) {
        echo "Error updating product.";
    } else {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Product</title>
</head>

<body>
    <h2>Edit Product</h2>
    <form method="post" action="">
        Name: <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required><br>
        Price: <input type="number" name="price" value="<?php echo htmlspecialchars($product['price']); ?>"
            required><br>
        Image URL: <input type="text" name="image" value="<?php echo htmlspecialchars($product['image']); ?>"
            required><br>
        <input type="submit" value="Update Product">
    </form>
</body>

</html>