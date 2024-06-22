<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product CRUD</title>
</head>

<body>
    <h1>Product List</h1>
    <p>Inloggad som: <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>
        (<?php echo isset($_SESSION['role']) ? $_SESSION['role'] : ''; ?>)</p>
    <form action="index.php" method="POST">
        <input type="hidden" name="logout" value="1">
        <button type="submit">Logga ut</button>
    </form>
    <div id="product-list">
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product']) && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
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
                echo "<meta http-equiv='refresh' content='0'>";
            }
        }

        $apiUrl = 'http://localhost:3000/products';
        $response = @file_get_contents($apiUrl);
        if ($response === FALSE) {
            echo "<p>Failed to fetch products. Please check if the server is running.</p>";
        } else {
            $products = json_decode($response, true);
            if (is_array($products)) {
                foreach ($products as $product) {
                    if (isset($product['id'])) {
                        echo "<div id='product-{$product['id']}'>
                            <h2>{$product['name']}</h2>
                            <p>Price: \${$product['price']}</p>
                            <img src='{$product['image']}' alt='{$product['name']}' width='100'>";
                        if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
                            echo "<button onclick=\"location.href='edit_product.php?id={$product['id']}'\">Edit</button>
                            <form action='index.php' method='POST' style='display:inline;'>
                                <input type='hidden' name='id' value='{$product['id']}'>
                                <input type='hidden' name='delete_product' value='1'>
                                <button type='submit'>Delete</button>
                            </form>";
                        }
                        echo "<button onclick=\"location.href='checkout.php?id={$product['id']}'\">Buy</button>";
                        echo "</div>";
                    } else {
                        echo "<div>
                            <h2>{$product['name']}</h2>
                            <p>Price: \${$product['price']}</p>
                            <img src='{$product['image']}' alt='{$product['name']}' width='100'>
                            <p>Error: Missing product ID</p>
                          </div>";
                    }
                }
            } else {
                echo "<p>Failed to decode products data.</p>";
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_product']) && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
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
                    echo "<meta http-equiv='refresh' content='0'>";
                }
            }
        }
        ?>
    </div>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
    <h2>Add New Product</h2>
    <form action="index.php" method="POST">
        <input type="hidden" name="create_product" value="1">
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
    <h2>Export Products</h2>
    <button onclick="location.href='http://localhost:3000/export/csv'">Export to CSV</button>
    <button onclick="location.href='http://localhost:3000/export/xml'">Export to XML</button>
    <?php } ?>
</body>

</html>