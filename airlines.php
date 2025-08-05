<?php
include 'config.php';

$sql = "SELECT name, country, contact_info, website, image_path FROM airlines";
$result = $conn->query($sql);

$airlines = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $airlines[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Авиокомпании</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .airlines-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
      padding: 40px 20px;
    }

    .airline-card {
      width: 300px;
      background-color: #fff;
      border-radius: 15px;

      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: transform 0.3s ease;
      text-align: center;
      display: flex;
      flex-direction: column;
      padding-bottom: 20px;
    }

    .airline-card:hover {
      transform: scale(1.03);
    }

    .airline-content {
      padding: 20px;
      flex-grow: 1;
    }

    .airline-title {
      font-size: 20px;
      font-weight: bold;
      color: #0d2d50;
      margin-bottom: 10px;
    }

    .airline-button {
      display: inline-block;
      margin-top: 10px;
      padding: 8px 16px;
      color: white;
      background-color: #2C6E49;
      font-weight: bold;
      text-decoration: none;
      font-size: 14px;
      border-radius: 8px;
    }

    .airline-button:hover {
      background-color: #1B4332;
    }

    .airline-info p {
      margin: 5px 0;
      font-size: 14px;
      color: #333;
    }

    strong {
      color: #2c3e50;
    }
    
    .airline-image {
        width: 100%;
        height: 160px;
        object-fit: contain;
        background-color: #f9f9f9;
        border-bottom: 1px solid #ddd;
        padding: 10px;
    }
  </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<h2 style="text-align:center; margin-top: 30px;">Авиокомпании</h2>

<div class="airlines-container">
    <?php foreach ($airlines as $airline): ?>
        <div class="airline-card">
            <?php if (!empty($airline['image_path'])): ?>
                <img src="<?= $airline['image_path'] ?>" alt="<?= $airline['name'] ?>" class="airline-image">
            <?php endif; ?>
            <div class="airline-content">
                <h2 class="airline-title"><?= $airline['name'] ?></h2>
                <div class="airline-info">
                    <p><strong>Държава:</strong> <?= $airline['country'] ?></p>
                    <p><strong>Контакт:</strong> <?= $airline['contact_info'] ?></p>
                </div>
                <?php if (!empty($airline['website'])): ?>
                    <a href="<?= $airline['website'] ?>" target="_blank" class="airline-button">Посети сайт</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
