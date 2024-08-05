<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $file = '../server/users.json';
    if (file_exists($file)) {
        $users = json_decode(file_get_contents($file), true);

        foreach ($users as $user) {
            if ($user['username'] == $username && password_verify($password, $user['password'])) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_id'] = $user['id'];
                header('Location: index.php');
                exit();
            }
        }
    }

    $error = "Incorrect username or password!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>

<body>
    <h1>Login</h1>
    <form action="login.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Login</button>
    </form>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</body>

</html>