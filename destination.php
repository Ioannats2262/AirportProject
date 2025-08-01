<?php
include 'config.php';

$sql = "SELECT city, destination_country, airport_code, description, city_image, country_image FROM destinations";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Дестинации</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .destination-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      padding: 40px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .destination-card {
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      text-align: center;
      padding-bottom: 20px;
    }

    .destination-card img.city {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }

    .destination-card .flag {
      width: 30px;
      height: 20px;
      vertical-align: middle;
      margin-left: 5px;
    }

    .destination-card h3 {
      margin: 15px 0 5px;
      color: #003366;
    }

    .destination-card p {
      padding: 0 15px;
      font-size: 14px;
      color: #333;
    }

    .destination-card button {
      margin-top: 15px;
      background-color: #2C7A4B;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
    }

    .destination-card button:hover {
      background-color: #1B4332;
    }
  </style>
</head>
<body>
<?php include 'navigation.php'; ?>

    <h2 style="text-align: center; padding: 20px; text-size: 32px; color: #003366;" >Дестинации</h2>
<div class="destination-container">
<?php while($row = $result->fetch_assoc()): ?>
  <div class="destination-card">
    <img src="<?php echo htmlspecialchars($row['city_image']); ?>" alt="Снимка на <?php echo htmlspecialchars($row['city']); ?>" class="city">
    <h3>
      <?php echo htmlspecialchars($row['city']); ?>
      <?php if (!empty($row['country_flag'])): ?>
        <img src="<?php echo htmlspecialchars($row['country_image']); ?>" alt="Флаг" class="flag">
      <?php endif; ?>
    </h3>
    <p><?php echo htmlspecialchars($row['description']); ?></p>
      <form action="buyticket.php" method="GET">
        <input type="hidden" name="destination" value="<?php echo htmlspecialchars($row['city']); ?>">
      <?php if (isset($_SESSION['user_id'])): ?>
          <button type="submit">Купи билет</button>
      <?php else: ?>
          <a href="login.php" class="buy-btn" onclick="return confirm('Моля, влезте в профила си, за да закупите билет.')">Купи билет</a>
      <?php endif; ?>
          </form>
        </div>
<?php endwhile; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
