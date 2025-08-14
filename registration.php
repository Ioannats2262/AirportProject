<?php
session_start();
include 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $phoneNumber = trim($_POST['phone_number']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($password) || empty($phoneNumber)) {
        $errors[] = "Моля, попълнете всички полета.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Невалиден имейл адрес.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Паролата трябва да е поне 6 символа.";
    }

    if (!preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
        $errors[] = "Паролата трябва да съдържа поне една главна буква и една цифра.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Паролите не съвпадат.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM user_app WHERE email_user = ? OR username_user = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Имейлът или потребителското име вече са заети.";
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insert = $conn->prepare("INSERT INTO user_app (first_name_user, last_name_user, phone_user, username_user, email_user, password) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("ssssss", $firstName, $lastName, $phoneNumber, $username, $email, $hashedPassword);
        if ($insert->execute()) {
            $_SESSION['user_id'] = $insert->insert_id;
            $_SESSION['user_name'] = $firstName;
            header("Location: homepage.php");
            exit;
        } else {
            $errors[] = "Грешка при регистрация. Моля, опитайте отново.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Регистрация</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="register-form">
  <h2>Регистрация</h2>

  <?php if (!empty($errors)): ?>
    <div class="error-list">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST">
    <label>Име:</label>
    <input type="text" name="first_name" required>

    <label>Фамилия:</label>
    <input type="text" name="last_name" required>

    <label>Телефон:</label>
    <input type="number" name="phone_number" required>

    <label>Потребителско име:</label>
    <input type="text" name="username" required>

    <label>Имейл:</label>
    <input type="email" name="email" required>

    <label>Парола:</label>
    <input type="password" name="password" required>

    <label for="confirm_password">Потвърди паролата:</label>
    <input type="password" id="confirm_password" name="confirm_password" required>

    <button type="submit">Регистрация</button>
  </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>

