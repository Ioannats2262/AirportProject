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
