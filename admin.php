<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: homepage.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Админ панел</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="admin-panel">
  <div class="admin-card">
    <h2>Админ панел</h2>
    <ul class="admin-menu">
      <li><a href="manage_users.php">Потребители</a></li>
      <li><a href="manage_flights.php">Полети</a></li>
      <li><a href="manage_destinations.php">Дестинации</a></li>
      <li><a href="reports.php">Справки</a></li>
    </ul>
  </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
