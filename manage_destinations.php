<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: homepage.php");
    exit;
}

if (isset($_GET['delete_id'])) {
    $delete_id = (int) $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM destinations WHERE destination_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
}

$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT city, destination_country, airport_code, description FROM destinations WHERE destination_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($city, $country, $code, $description);
    if ($stmt->fetch()) {
        $edit_data = ['id' => $edit_id, 'city' => $city, 'country' => $country, 'code' => $code, 'description' => $description];
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $city = trim($_POST['city']);
    $country = trim($_POST['destination_country']);
    $code = strtoupper(trim($_POST['airport_code']));
    $description = trim($_POST['description']);

    if ($city && $country && $code && $description) {
        if (isset($_POST['destination_id'])) {
            $id = (int) $_POST['destination_id'];
            $stmt = $conn->prepare("UPDATE destinations SET city = ?, destination_country = ?, airport_code = ?, description = ? WHERE destination_id = ?");
            $stmt->bind_param("ssssi", $city, $country, $code, $description, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO destinations (city, destination_country, airport_code, description) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $city, $country, $code, $description);
        }
        $stmt->execute();
    }
}

$result = $conn->query("SELECT * FROM destinations ORDER BY city");
?>

<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Управление на дестинации</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="dest-container">
  <h2>Управление на дестинации</h2>

  <table>
    <tr>
      <th>Град</th>
      <th>Държава</th>
      <th>Код</th>
      <th>Описание</th>
      <th>Опции</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['city']) ?></td>
        <td><?= htmlspecialchars($row['destination_country']) ?></td>
        <td><?= htmlspecialchars($row['airport_code']) ?></td>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td>
          <div class="action-buttons">
            <a class="edit-btn" href="?edit_id=<?= $row['destination_id'] ?>">Редактирай</a>
            <a class="delete-btn" href="?delete_id=<?= $row['destination_id'] ?>" onclick="return confirm('Наистина ли искаш да изтриеш тази дестинация?')">Изтрий</a>
          </div>
        </td>
    <?php endwhile; ?>
  </table>

  <h3><?= $edit_data ? "Редактирай дестинация" : "Добави нова дестинация" ?></h3>
  <form method="POST" class="add-form">
    <?php if ($edit_data): ?>
      <input type="hidden" name="destination_id" value="<?= $edit_data['id'] ?>">
    <?php endif; ?>
    <input type="text" name="city" placeholder="Град" value="<?= $edit_data['city'] ?? '' ?>" required>
    <input type="text" name="destination_country" placeholder="Държава" value="<?= $edit_data['country'] ?? '' ?>" required>
    <input type="text" name="airport_code" placeholder="Код на летището" maxlength="3" value="<?= $edit_data['code'] ?? '' ?>" required>
    <textarea name="description" placeholder="Описание" required><?= $edit_data['description'] ?? '' ?></textarea>
    <button type="submit"><?= $edit_data ? "Запази промените" : "Добави" ?></button>
  </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
