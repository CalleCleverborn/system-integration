<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phonenumber = $_POST['phonenumber'];
    $password = $_POST['password'];
    $isAdmin = isset($_POST['isAdmin']) ? true : false;

    $data = json_encode([
        'username' => $username,
        'email' => $email,
        'phonenumber' => $phonenumber,
        'password' => $password,
        'isAdmin' => $isAdmin
    ]);

    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => $data,
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents('https://sysint-callecleverborn-carl-cleverborns-projects.vercel.app/users', false, $context);

    if ($result === FALSE) {
        $error = "Error registering user.";
    } else {
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
</head>

<body>
    <h2>Register</h2>
    <form method="post" action="">
        Username: <input type="text" name="username" required><br>
        Email: <input type="email" name="email" required><br>
        Phone Number: <input type="text" name="phonenumber" required><br>
        Password: <input type="password" name="password" required><br>
        Admin: <input type="checkbox" name="isAdmin"><br>
        <input type="submit" value="Register">
    </form>
    <?php if (isset($error))
        echo $error; ?>
    <a href="login.php">Login</a>
</body>

</html>