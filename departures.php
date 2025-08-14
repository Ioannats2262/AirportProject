<?php
include 'config.php';
include 'navigation.php';

$limit = 100;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$safeSearch = $conn->real_escape_string($search);
$current_date = $_GET['date'] ?? date('Y-m-d');
$day_number = date('j', strtotime($current_date));

$count_sql = "SELECT COUNT(*) AS total FROM flights WHERE departure_airport = 'VAR'";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);


$sql = "SELECT f.flight_id, f.flight_number, a.name AS airline, d.city AS destination,
               f.departure_time, t.name_terminal, f.status, f.gate
        FROM flights f
        JOIN airlines a ON f.airline_id = a.airline_id
        JOIN destinations d ON f.destinations_destination_id = d.destination_id
        JOIN terminals t ON f.terminal_id = t.terminal_id
        WHERE f.departure_airport = 'VAR'
          AND f.airline_id IN (
                SELECT airline_id
                FROM flights
                GROUP BY airline_id
                HAVING COUNT(*) > 2
            )";

if (!empty($safeSearch)) {
    $sql .= " AND d.city LIKE '%$safeSearch%'";
}

$sql .= " ORDER BY f.departure_time ASC";

if (empty($safeSearch)) {
    $sql .= " LIMIT $limit OFFSET $offset";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Заминаващи полети</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Заминаващи</h1>

<form method="GET" action="departures.php" style="margin: 20px auto; text-align: center;">
    <input type="text" name="search" placeholder="Въведи град (напр. Лондон)"
           value="<?= htmlspecialchars($search) ?>"
           style="padding: 8px; width: 250px; border-radius: 5px; border: 1px solid #ccc;">
    <button type="submit" style="padding: 8px 12px; background-color: #2C7A4B; color: white; border: none; border-radius: 5px;">Търси</button>
</form>

<div style="display: flex; justify-content: center; margin-top: 20px;">
    <div id="date-selector" style="display: flex; align-items: center; gap: 15px;">
        <button type="button" onclick="changeDate(-1)">←</button>
        <span id="current-date" style="font-weight: bold;"></span>
        <button type="button" onclick="changeDate(1)">→</button>
    </div>
</div>
<input type="hidden" name="date" id="date-input" value=""> <br>

<table>
  <tr>
    <th>Номер на полет</th>
    <th>Авиокомпания</th>
    <th>Дестинация</th>
    <th>Час на заминаване</th>
    <th>Терминал</th>
    <th>Гейт</th>
    <th>Статус</th>
  </tr>

<?php
$found = false;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($day_number % 2 == 0 && $row['flight_id'] % 2 != 0) continue;
        if ($day_number % 2 != 0 && $row['flight_id'] % 2 == 0) continue;

        $found = true;
        echo "<tr>
            <td>{$row['flight_number']}</td>
            <td>{$row['airline']}</td>
            <td>{$row['destination']}</td>
            <td>" . date('H:i', strtotime($row['departure_time'])) . "</td>
            <td>{$row['name_terminal']}</td>
            <td>{$row['gate']}</td>
            <td>{$row['status']}</td>
        </tr>";
    }
}
if (!$found) {
    echo "<tr><td colspan='7'>Няма налични заминаващи полети за избраната дата.</td></tr>";
}
?>
</table>

<div class="pagination">
<?php
for ($i = 1; $i <= $total_pages; $i++) {
    $active = ($i == $page) ? 'active' : '';
    echo "<a class='$active' href='?page=$i&date=$current_date&search=" . urlencode($search) . "'>$i</a>";
}
$conn->close();
?>
</div>

<script>
function formatDate(date) {
  return date.toISOString().split('T')[0];
}

let selectedDate = new Date();
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('date')) {
  selectedDate = new Date(urlParams.get('date'));
}

function updateDateUI() {
  document.getElementById('current-date').textContent = formatDate(selectedDate);
  document.getElementById('date-input').value = formatDate(selectedDate);
}

function changeDate(days) {
  selectedDate.setDate(selectedDate.getDate() + days);
  updateDateUI();
  const baseUrl = "departures.php";
  const search = "<?= urlencode($search) ?>";
  const page = "<?= $page ?>";
  const date = formatDate(selectedDate);
  window.location.href = `${baseUrl}?date=${date}&search=${search}&page=${page}`;
}

updateDateUI();
</script>

<?php include 'footer.php'; ?>
</body>
</html>

