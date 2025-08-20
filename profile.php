<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username_user, email_user, phone_user FROM user_app WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Профил</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="profile-container">
  <h2>Профил на потребителя</h2>
  <p><strong>Потребителско име:</strong> <?php echo htmlspecialchars($user['username_user']); ?></p>
  <p><strong>Имейл:</strong> <?php echo htmlspecialchars($user['email_user']); ?></p>
  <p><strong>Телефон:</strong> <?php echo htmlspecialchars($user['phone_user']); ?></p>

  <a href="editprofile.php" class="edit-btn">Редактирай профила</a>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
