<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$product_id = isset($_GET['id']) ? $_GET['id'] : null;
$email = isset($_GET['email']) ? $_GET['email'] : null;
$phone = isset($_GET['phone']) ? $_GET['phone'] : null;

if ($product_id === null || $email === null || $phone === null) {
    echo "<p>Product ID, email, or phone is missing. Please go back and select a product to buy.</p>";
    exit();
}

$apiUrl = 'https://sysserver-olsm5c0q3-carl-cleverborns-projects.vercel.app';

$response = @file_get_contents("$apiUrl/products/$product_id");
if ($response === FALSE) {
    echo "<p>Failed to fetch product. Please check if the server is running.</p>";
    exit();
}
$product = json_decode($response, true);
if ($product === null) {
    echo "<p>Product not found. Please check if the product ID is correct.</p>";
    exit();
}
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
    <p>Product: <?php echo htmlspecialchars($product['name']); ?></p>
    <p>Price: $<?php echo htmlspecialchars($product['price']); ?></p>
    <img src="<?php echo htmlspecialchars($product['image']); ?>" width="100">
    <button id="checkout-button">Pay $<?php echo $product['price']; ?></button>

    <script>
    var stripe = Stripe(
        'pk_test_51PItI7Rxxg2rxu6vkw4GVJS5IOlzaBoifIk6h5pRdH9V5E2p7qFq1DDkxtc5TfXqFmARiwpb76fFFdhM3jxaIXgI00FxsZQSqW'
        );

    document.getElementById('checkout-button').addEventListener('click', async function() {
        console.log('Checkout button clicked');

        const productName = "<?php echo $product['name']; ?>";
        const productPrice = "<?php echo $product['price']; ?>";
        const productImage = "<?php echo $product['image']; ?>";
        const userEmail = "<?php echo $email; ?>";
        const userPhone = "<?php echo $phone; ?>";

        console.log('Sending data:', {
            name: productName,
            price: productPrice,
            image: productImage,
            email: userEmail,
            phone: userPhone
        });

        try {
            const response = await fetch('<?php echo $apiUrl; ?>/create-checkout-session', {
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

            console.log('Response received:', response);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const session = await response.json();
            console.log('Session:', session);

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