<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

$product_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($product_id === null) {
    echo "<p>Product ID is missing. Please go back and select a product to buy.</p>";
    exit();
}

$apiUrl = 'http://localhost:3000/products/' . $product_id;
$response = @file_get_contents($apiUrl);
if ($response === FALSE) {
    echo "<p>Failed to fetch product. Please check if the server is running.</p>";
    exit();
}
$product = json_decode($response, true);
if ($product === null) {
    echo "<p>Product not found. Please check if the product ID is correct.</p>";
    exit();
}

$usersFile = '../server/users.json';
$users = json_decode(file_get_contents($usersFile), true);
$user = array_filter($users, function ($u) {
    return $u['username'] === $_SESSION['username'];
});
$user = array_values($user)[0];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <script src="https://js.stripe.com/v3/"></script>
</head>

<body>
    <h1>Checkout</h1>
    <button id="checkout-button">Pay $<?php echo $product['price']; ?></button>


    <script>
    var stripe = Stripe(
        'pk_test_51PItI7Rxxg2rxu6vkw4GVJS5IOlzaBoifIk6h5pRdH9V5E2p7qFq1DDkxtc5TfXqFmARiwpb76fFFdhM3jxaIXgI00FxsZQSqW'
    );

    document.getElementById('checkout-button').addEventListener('click', async function() {
        const productName = "<?php echo $product['name']; ?>";
        const productPrice = "<?php echo $product['price']; ?>";
        const productImage = "<?php echo $product['image']; ?>";
        const userEmail = "<?php echo $user['email']; ?>";
        const userPhone = "<?php echo $user['phone']; ?>";

        console.log('Sending data:', {
            name: productName,
            price: productPrice,
            image: productImage,
            email: userEmail,
            phone: userPhone
        }); // Log the data being sent

        try {
            const response = await fetch('http://localhost:3000/create-checkout-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: productName,
                    price: productPrice,
                    image: productImage,
                    email: userEmail,
                    phone: userPhone
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const session = await response.json();
            console.log('Session:', session); // Log the session response

            const result = await stripe.redirectToCheckout({
                sessionId: session.id
            });

            if (result.error) {
                console.error(result.error.message);
            }
        } catch (error) {
            console.error('Error creating checkout session:', error);
        }
    });
    </script>
</body>

</html>