<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $url = 'https://sysint-callecleverborn-carl-cleverborns-projects.vercel.app/users';
    $response = file_get_contents($url);
    $users = json_decode($response, true);

    foreach ($users as $user) {
        if ($user['email'] == $email && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['isAdmin'] = $user['isAdmin'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['phonenumber'] = $user['phonenumber'];
            header("Location: index.php");
            exit();
        }
    }
    $error = "Invalid email or password";
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
</head>

<body>
    <h2>Login</h2>
    <form method="post" action="">
        Email: <input type="email" name="email" required><br>
        Password: <input type="password" name="password" required><br>
        <input type="submit" value="Login">
    </form>
    <?php if (isset($error))
        echo $error; ?>
    <a href="register.php">Register</a>
</body>

</html>