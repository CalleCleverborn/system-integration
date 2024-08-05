<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Use the Vercel-deployed server URL
$apiUrl = 'https://sysint-callecleverborn-carl-cleverborns-projects.vercel.app/';

$products = json_decode(file_get_contents("$apiUrl/products"), true);
$user_email = $_SESSION['email'];
$user_phone = $_SESSION['phonenumber'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Product Management</title>
</head>

<body>
    <h2>Welcome, <?php echo $_SESSION['username']; ?></h2>
    <a href="logout.php">Logout</a>
    <h3>Products</h3>
    <?php if ($_SESSION['isAdmin']) { ?>
    <a href="add_product.php">Add New Product</a> |
    <a href="export_csv.php">Export to CSV</a> |
    <a href="export_xml.php">Export to XML</a>
    <?php } ?>
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Image</th>
            <th>Buy</th>
            <?php if ($_SESSION['isAdmin']) { ?>
            <th>Actions</th>
            <?php } ?>
        </tr>
        <?php foreach ($products as $product) { ?>
        <tr>
            <td><?php echo htmlspecialchars($product['name']); ?></td>
            <td><?php echo htmlspecialchars($product['price']); ?></td>
            <td><img src="<?php echo htmlspecialchars($product['image']); ?>" width="50"></td>
            <td>
                <a
                    href="checkout.php?id=<?php echo $product['_id']; ?>&email=<?php echo $user_email; ?>&phone=<?php echo $user_phone; ?>">Buy</a>
            </td>
            <?php if ($_SESSION['isAdmin']) { ?>
            <td>
                <a href="edit_product.php?id=<?php echo $product['_id']; ?>">Edit</a>
                <a href="delete_product.php?id=<?php echo $product['_id']; ?>">Delete</a>
            </td>
            <?php } ?>
        </tr>
        <?php } ?>
    </table>
</body>

</html>