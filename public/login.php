<?php
session_start();
include '../app/config/config.php';

$email_error = $password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id; 
            header("Location: /AgendaApp/public/index.php");
            exit();
        } else {
            $password_error = "Invalid password.";
        }
    } else {
        $email_error = "Email not found.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.png">
</head>
<body>
    <h1 class="title">Login</h1>

    <?php if(!empty($email_error)): ?>
        <p><?php echo $email_error; ?></p>
    <?php endif; ?>
    <?php if(!empty($password_error)): ?>
        <p><?php echo $password_error; ?></p>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>

    <a href="/AgendaApp/public/homepage.html" class="button">Back to Homepage</a>
</body>
</html>
