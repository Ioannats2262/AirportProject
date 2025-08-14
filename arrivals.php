<?php
include 'config.php';

// лимит за страница
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_sql = "SELECT COUNT(*) as total FROM flights WHERE arrival_airport = 'VAR'";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Пристигащи полети</title>

</head>
<body>
  <?php include 'navigation.php'; ?>
  <h1>Пристигащи</h1>
  <form method="GET" action="arrivals.php" style="margin: 20px auto; text-align: center;">
    <input type="text" name="search" placeholder="Въведи град (напр. София)" 
           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
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
      <th>Пристига от</th>
      <th>Час на пристигане</th>
      <th>Терминал</th>
      <th>Статус</th>
    </tr>
<?php
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$safeSearch = $conn->real_escape_string($search);

$sql = "SELECT f.flight_number, a.name AS airline, d.city AS origin,
               f.arrival_time, t.name_terminal, f.status
        FROM flights f
        JOIN airlines a ON f.airline_id = a.airline_id
        JOIN destinations d ON f.destinations_destination_id = d.destination_id
        JOIN terminals t ON f.terminal_id = t.terminal_id
        WHERE f.arrival_airport = 'VAR'";

if (!empty($safeSearch)) {
    $sql .= " AND d.city LIKE '%$safeSearch%'";
}

$sql .= " ORDER BY f.arrival_time ASC";

if (empty($safeSearch)) {
    $sql .= " LIMIT $limit OFFSET $offset";
}
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['flight_number']}</td>
                <td>{$row['airline']}</td>
                <td>{$row['origin']}</td>
                <td>{$row['arrival_time']}</td>
                <td>{$row['name_terminal']}</td>
                <td>{$row['status']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='6'>Няма налични пристигащи полети.</td></tr>";
}
?>
</table>
  <div class="pagination">
    <?php
    for ($i = 1; $i <= $total_pages; $i++) {
      $active = ($i == $page) ? 'active' : '';
      echo "<a class='$active' href='?page=$i'>$i</a>";
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
}

updateDateUI();
</script>

  <?php include 'footer.php'; ?>
</body>
</html>
