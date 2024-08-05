<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $isAdmin = isset($_POST['isAdmin']) ? true : false;

    $usersFile = '../server/users.json';
    $users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

    // Generate a new unique ID for the user
    $newId = count($users) > 0 ? max(array_column($users, 'id')) + 1 : 1;

    $user = [
        'id' => $newId,
        'username' => $username,
        'password' => $password,
        'email' => $email,
        'phone' => $phone,
        'role' => $isAdmin ? 'admin' : 'user'
    ];

    $users[] = $user;
    file_put_contents($usersFile, json_encode($users));

    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $user['role'];

    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>

<body>
    <h1>Register</h1>
    <form action="register.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>

        <label for="phone">Phone Number:</label>
        <input type="text" id="phone" name="phone" required><br>

        <label for="isAdmin">Admin:</label>
        <input type="checkbox" id="isAdmin" name="isAdmin"><br>

        <button type="submit">Register</button>
    </form>
</body>

</html>