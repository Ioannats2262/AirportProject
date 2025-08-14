<?php
session_start();
include 'config.php';

// за админ панела
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: homepage.php");
    exit;
}

if (isset($_GET['delete_id'])) {
    $delete_id = (int) $_GET['delete_id'];
    if ($delete_id !== $_SESSION['user_id']) { 
        $stmt = $conn->prepare("DELETE FROM user_app WHERE user_id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
    }
}

$stmt = $conn->prepare("SELECT user_id, first_name_user, last_name_user, username_user, email_user, role FROM user_app WHERE role != 'admin'");
$stmt->execute();
$users = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Управление на потребители</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navigation.php'; ?>

<h2 style="text-align:center;">Управление на потребители</h2>

<table>
  <tr>
    <th>ID</th>
    <th>Име</th>
    <th>Потребителско име</th>
    <th>Имейл</th>
    <th>Действие</th>
  </tr>
  <?php while ($row = $users->fetch_assoc()): ?>
    <tr>
      <td><?= $row['user_id'] ?></td>
      <td><?= htmlspecialchars($row['first_name_user'] . ' ' . $row['last_name_user']) ?></td>
      <td><?= htmlspecialchars($row['username_user']) ?></td>
      <td><?= htmlspecialchars($row['email_user']) ?></td>
      <td>
        <a class="delete-btn" href="?delete_id=<?= $row['user_id'] ?>" onclick="return confirm('Сигурни ли сте, че искате да изтриете този потребител?')">Изтрий</a>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

<?php include 'footer.php'; ?>
</body>
</html>
