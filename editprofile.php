<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$errors = [];

$user = [
    'username_user' => '',
    'email_user' => '',
    'phone_user' => '',
    'password' => ''
];

$stmt = $conn->prepare("SELECT username_user, email_user, phone_user, password FROM user_app WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    $errors[] = "Неуспешно зареждане на профилните данни.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    if (empty($email) || empty($current_password)) {
        $errors[] = "Имейлът и текущата парола са задължителни.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Невалиден имейл адрес.";
    }

    if (!password_verify($current_password, $user['password'])) {
        $errors[] = "Грешна текуща парола.";
    }

    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            $errors[] = "Новата парола трябва да е поне 6 символа.";
        }
        if (!preg_match("/[A-Z]/", $new_password) || !preg_match("/[0-9]/", $new_password)) {
            $errors[] = "Паролата трябва да съдържа поне една главна буква и една цифра.";
        }
    }

    if (empty($errors)) {
        if (!empty($new_password)) {
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE usep_app SET email_user = ?, phone_user = ?, password = ? WHERE user_id = ?");
            $update->bind_param("sssi", $email, $phone, $hashed_new_password, $user_id);
        } else {
            $update = $conn->prepare(query: "UPDATE user_app SET email_user = ?, phone_user = ? WHERE user_id = ?");
            $update->bind_param("ssi", $email, $phone, $user_id);
        }

        if ($update->execute()) {
            $message = "✅ Профилът е успешно обновен.";
            $user['email_user'] = $email;
            $user['phone_user'] = $phone;
        } else {
            $errors[] = "Грешка при запис. Опитайте отново.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Редакция на профил</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="edit-form">
  <h2>Редакция на профил</h2>

  <?php if (!empty($errors)): ?>
    <div class="error">
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (!empty($message)): ?>
    <div class="success"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="POST">
    <label>Потребителско име (не се променя):</label>
    <input type="text" value="<?= htmlspecialchars($user['username_user']) ?>" readonly>

    <label>Имейл:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email_user']) ?>" required>

    <label>Телефон:</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone_user']) ?>">

    <label>Нова парола (по избор):</label>
    <input type="password" name="new_password" placeholder="Остави празно, ако няма промяна">

    <label>Текуща парола (задължително):</label>
    <input type="password" name="current_password" required>

    <button type="submit">Запази промените</button>
  </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>

