<?php
session_start();
include 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']); 
    $password = $_POST['password'];

    if (empty($login) || empty($password)) {
        $errors[] = "Моля, попълнете всички полета.";
    }

    if (strpos($login, '@') !== false && !filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Невалиден имейл формат.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM user_app WHERE email_user = ? OR username_user = ?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['user_name'] = $row['first_name_user'];
                $_SESSION['role'] = $row['role']; 

                header("Location: homepage.php");
                exit;
            } else {
                $errors[] = "Невалидна парола. Моля опитайте отново.";
            }
        } else {
            $errors[] = "Потребителят не е намерен. <a href='registration.php' style='color: #003366; font-weight: bold;'>Регистрация?</a>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Вход</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .login-form {
      max-width: 400px;
      margin: 40px auto;
      padding: 30px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .login-form h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #003366;
    }
    .login-form input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    .login-form button {
      width: 100%;
      padding: 10px;
      background-color: #2C7A4B;
      color: white;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
    }
    .login-form ul {
      color: red;
      padding-left: 20px;
    }
  </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="login-form">
  <h2>Вход в системата</h2>

  <?php if (!empty($errors)): ?>
    <ul>
      <?php foreach ($errors as $e): ?>
        <li><?= $e ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form method="POST">
    <label for="login">Имейл или потребителско име:</label>
    <input type="text" name="login" id="login" required>

    <label for="password">Парола:</label>
    <input type="password" name="password" id="password" required>

    <button type="submit">Вход</button>
  </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
