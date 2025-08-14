<?php
include 'config.php';

$category = isset($_GET['category']) ? $_GET['category'] : null;

if ($category) {
    $stmt = $conn->prepare("SELECT name_entertainment, type, terminal_id, opening_hours, description, image_path FROM entertainments WHERE type = ?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT name_entertainment, type, terminal_id, opening_hours, description, image_path FROM entertainments";
    $result = $conn->query($sql);
}

$entertainments = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $entertainments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Развлечения</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navigation.php'; ?>

<h2>Развлечения</h2>

<div class="category-buttons">
    <a href="entertainments.php?category=Кафенета" class="destination-button">Кафенета</a>
    <a href="entertainments.php?category=Магазини" class="destination-button">Магазини</a>
    <a href="entertainments.php?category=Ресторанти" class="destination-button">Ресторанти</a>
    <a href="entertainments.php?category=Зони за отдих" class="destination-button">Зони за отдих</a>
    <a href="entertainments.php" class="destination-button">Всички</a>
</div>

<div class="destinations-container">
    <?php foreach ($entertainments as $item): ?>
        <div class="destination-card">
            <img src="<?= $item['image_path'] ?>" alt="<?= $item['name_entertainment'] ?>" class="destination-image">
            <div class="destination-content">
                <h2 class="destination-title"><?= $item['name_entertainment'] ?></h2>
                <p><strong>Тип:</strong> <?= $item['type'] ?></p>
                <p><strong>Терминал:</strong> <?= $item['terminal_id'] ?></p>
                <p><strong>Работно време:</strong> <?= $item['opening_hours'] ?></p>
                <p><?= $item['description'] ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>

