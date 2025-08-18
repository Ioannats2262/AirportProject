<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: homepage.php");
    exit;
}

if (isset($_GET['delete_id'])) {
    $delete_id = (int) $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM flights WHERE flight_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
}

if (isset($_POST['update_flight'])) {
    $flight_id = (int) $_POST['edit_flight_id'];
    $number = $_POST['flight_number'];
    $time = $_POST['departure_time'];
    $price = $_POST['price'];
    $dest_id = (int) $_POST['destination_id'];

    if ($number && $time && $price && $dest_id) {
        $stmt = $conn->prepare("UPDATE flights SET flight_number = ?, departure_time = ?, price = ?, destinations_destination_id = ? WHERE flight_id = ?");
        $stmt->bind_param("ssdii", $number, $time, $price, $dest_id, $flight_id);
        $stmt->execute();
        header("Location: manage_flights.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flight_id'], $_POST['new_status'])) {
    $flight_id = (int) $_POST['flight_id'];
    $new_status = $_POST['new_status'];
    $allowed = ['Scheduled', 'Departed', 'Arrived', 'Canceled'];
    if (in_array($new_status, $allowed)) {
        $stmt = $conn->prepare("UPDATE flights SET status = ? WHERE flight_id = ?");
        $stmt->bind_param("si", $new_status, $flight_id);
        $stmt->execute();
    }
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['flight_number'], $_POST['departure_time'], $_POST['price'], $_POST['destination_id']) &&
    !isset($_POST['edit_flight_id'])
) {
    $flight_number = $_POST['flight_number'];
    $departure_time = $_POST['departure_time'];
    $price = (float) $_POST['price'];
    $destination_id = (int) $_POST['destination_id'];

    if ($flight_number && $departure_time && $price && $destination_id) {
        $stmt = $conn->prepare("INSERT INTO flights (flight_number, departure_time, price, destinations_destination_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $flight_number, $departure_time, $price, $destination_id);
        $stmt->execute();
    }
}

$destQuery = $conn->query("SELECT destination_id, city FROM destinations");

$stmt = $conn->prepare("SELECT f.flight_id, f.flight_number, f.departure_time, f.price, f.status, d.city 
                        FROM flights f 
                        JOIN destinations d ON f.destinations_destination_id = d.destination_id");
$stmt->execute();
$flights = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Управление на полети</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="flights-container">
  <h2>Управление на полети</h2>

  <table>
    <tr>
      <th>Номер</th>
      <th>Час</th>
      <th>Цена (лв)</th>
      <th>Дестинация</th>
      <th>Статус</th>
      <th>Промяна</th>
    </tr>
    <?php while ($row = $flights->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['flight_number']) ?></td>
        <td><?= date("H:i", strtotime($row['departure_time'])) ?></td>
        <td><?= htmlspecialchars($row['price']) ?></td>
        <td><?= htmlspecialchars($row['city']) ?></td>
        <td>
          <form method="POST" style="display:inline-block;">
            <input type="hidden" name="flight_id" value="<?= $row['flight_id'] ?>">
            <select name="new_status" onchange="this.form.submit()">
              <?php
              $statuses = ['Scheduled', 'Departed', 'Arrived', 'Canceled'];
              foreach ($statuses as $status) {
                  $selected = $row['status'] === $status ? 'selected' : '';
                  echo "<option value=\"$status\" $selected>$status</option>";
              }
              ?>
            </select>
          </form>
        </td>
        <td>
          <a class="edit-btn" href="?edit_id=<?= $row['flight_id'] ?>">Редактирай</a>
          <a class="delete-btn" href="?delete_id=<?= $row['flight_id'] ?>" onclick="return confirm('Сигурни ли сте, че искате да изтриете този полет?')">Изтрий</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>

  <?php if (isset($_GET['edit_id'])):
    $edit_id = (int) $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT flight_number, departure_time, price, destinations_destination_id FROM flights WHERE flight_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($flight_number, $departure_time, $price, $destination_id);
    $stmt->fetch();
    $stmt->close();
  ?>
  <h3>Редакция на полет</h3>
  <form method="POST">
    <input type="hidden" name="edit_flight_id" value="<?= $edit_id ?>">
    <input type="text" name="flight_number" value="<?= htmlspecialchars($flight_number) ?>" placeholder="Номер на полета" required>
    <input type="time" name="departure_time" value="<?= date('H:i', strtotime($departure_time)) ?>" required>
    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($price) ?>" placeholder="Цена" required>
    <select name="destination_id" required>
      <?php
      $dest_query = $conn->query("SELECT destination_id, city FROM destinations ORDER BY city");
      while ($dest = $dest_query->fetch_assoc()) {
          $selected = $dest['destination_id'] == $destination_id ? 'selected' : '';
          echo "<option value=\"{$dest['destination_id']}\" $selected>{$dest['city']}</option>";
      }
      ?>
    </select>
    <button type="submit" name="update_flight">Запази</button>
  </form>
  <?php endif; ?>

  <h3>Добави нов полет</h3>
  <form class="add-form" method="POST">
    <input type="text" name="flight_number" placeholder="Номер на полет" required>
    <input type="datetime-local" name="departure_time" required>
    <input type="number" name="price" placeholder="Цена в лв" step="0.01" required>
    <select name="destination_id" required>
      <option value="">Избери дестинация</option>
      <?php while ($dest = $destQuery->fetch_assoc()): ?>
        <option value="<?= $dest['destination_id'] ?>"><?= htmlspecialchars($dest['city']) ?></option>
      <?php endwhile; ?>
    </select>
    <button type="submit">Добави</button>
  </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
