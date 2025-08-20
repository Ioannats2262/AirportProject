<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$destination = '';
$message = '';
$errors = [];
$destination_id = null;
$flights = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $destination = trim($_POST['destination'] ?? '');
    $baggage_price = (int)($_POST['baggage'] ?? 0);
    $flight_id = $_POST['flight_id'] ?? null;
    $flight_date = $_POST['flight_date'] ?? '';
    $flight_price = (float)($_POST['flight_price'] ?? 0);
    $count = isset($_POST['first_name']) ? count($_POST['first_name']) : 0;

    if (empty($destination) || !$flight_id || !$flight_date || $count === 0) {
        $errors[] = "Моля, попълнете всички задължителни полета.";
    }

    for ($i = 0; $i < $count; $i++) {
        $fn = trim($_POST['first_name'][$i]);
        $ln = trim($_POST['last_name'][$i]);
        $pass = trim($_POST['passport'][$i]);
        $em = trim($_POST['email'][$i]);
        $ph = trim($_POST['phone'][$i]);
        $seat = trim($_POST['seat_number'][$i]);

        if (empty($fn) || empty($ln) || empty($pass) || empty($em) || empty($seat)) {
            $errors[] = "Моля, попълнете всички данни за пътник #" . ($i + 1);
        }

        if (!filter_var($em, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Невалиден имейл за пътник #" . ($i + 1);
        }

        if (!preg_match("/^[A-F][0-9]{1,2}$/", $seat)) {
            $errors[] = "Невалиден формат на място за пътник #" . ($i + 1);
        }
//zaqvka za zaeto mqsto, vzimame ot reservations 
        $stmt = $conn->prepare("SELECT 1 FROM reservations WHERE flight_id = ? AND seat_number = ?");
        $stmt->bind_param("is", $flight_id, $seat);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $errors[] = "Мястото $seat за пътник #" . ($i + 1) . " вече е заето.";
        }
    }

    if (empty($errors)) {
        for ($i = 0; $i < $count; $i++) {
            $fn = $_POST['first_name'][$i];
            $ln = $_POST['last_name'][$i];
            $pass = $_POST['passport'][$i];
            $em = $_POST['email'][$i];
            $ph = $_POST['phone'][$i];
            $seat = $_POST['seat_number'][$i];

            $stmt = $conn->prepare("INSERT INTO passengers (firstName, lastName, passport_number, email, phone_number, User_user_id)
                                    VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $fn, $ln, $pass, $em, $ph, $user_id);
            $stmt->execute();
            $pid = $stmt->insert_id;

            $stmt2 = $conn->prepare("INSERT INTO reservations (flight_id, passenger_id, seat_number, booking_status, booking_date)
                                     VALUES (?, ?, ?, NOW(), ?)");
            $stmt2->bind_param("iiss", $flight_id, $pid, $seat, $flight_date);
            $stmt2->execute();
        }

        $total_price = $flight_price * $count + $baggage_price;
        $message = "✅ Успешно резервирани $count билет(а) с багаж {$baggage_price} лв. Обща цена: {$total_price} лв.";
    }
}

if (isset($_GET['destination'])) {
    $destination = trim($_GET['destination']);
}

if (!empty($destination)) {
    $stmt = $conn->prepare("SELECT destination_id FROM destinations WHERE city = ?");
    $stmt->bind_param("s", $destination);
    $stmt->execute();
    $dest_result = $stmt->get_result();
    $dest = $dest_result->fetch_assoc();
    $destination_id = $dest ? $dest['destination_id'] : null;

    if ($destination_id) {
        $sql = "SELECT flight_id, flight_number, departure_time, price FROM flights WHERE destinations_destination_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $destination_id);
        $stmt->execute();
        $flights = $stmt->get_result();
    }
}

$bookedSeats = [];
if (isset($_GET['flight_id']) || isset($_POST['flight_id'])) {
    $flight_id = $_GET['flight_id'] ?? $_POST['flight_id'];
    if ($flight_id) {
        $stmt = $conn->prepare("SELECT seat_number FROM reservations WHERE flight_id = ?");
        $stmt->bind_param("i", $flight_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $bookedSeats[] = $row['seat_number'];
        }
    }
}
?>
    
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Купи билет</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="ticket-form">
  <h2>Купи билет до <?php echo htmlspecialchars($destination); ?></h2>
    <?php if (!empty($errors)): ?>
      <div style="color: red;"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if ($message): ?>
      <div style="color: green; font-weight: bold; padding: 10px 0;"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

  <?php if ($destination_id && $flights->num_rows > 0): ?>
    <form method="POST">
      <input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">

      <label>Избери полет:</label>
      <select name="flight_id" required onchange="updateFlightPrice(this); calculateTotalPrice()">
<?php while ($row = $flights->fetch_assoc()): ?>
  <option value="<?php echo $row['flight_id']; ?>" data-price="<?php echo $row['price']; ?>">
    <?php echo $row['flight_number'] . " – " . $row['departure_time'] . " – " . $row['price'] . " лв."; ?>
  </option>
<?php endwhile; ?>
</select>

<input type="hidden" name="flight_price" id="flight_price" value="">

      <label>Дата на полета:</label>
      <input type="date" name="flight_date" required>

      <label>Брой билети:</label>
      <select name="ticket_count" id="ticket_count" onchange="generateForms(); calculateTotalPrice()" required>
        <option value="">-- Избери --</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
      </select>

      <label for="baggage">Избери багаж:</label>
      <select name="baggage" id="baggage" required onchange="calculateTotalPrice()">
          <option value="">-- Моля, избери --</option>
          <option value="0">Без багаж (0 лв.)</option>
          <option value="10">Ръчен багаж (10 лв.)</option>
          <option value="20">Регистриран багаж (20 лв.)</option>
          <option value="30">Два броя багаж (30 лв.)</option>
      </select>

      <div id="totalPriceDisplay" style="font-weight: bold; color: green; margin-top: 10px;">Обща цена: -- лв.</div>
      <div id="passenger_forms"></div>
      <button type="submit">Потвърди покупката</button>
    </form>
  <?php else: ?>
    <p>Няма налични полети към тази дестинация.</p>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

<script>
let bookedSeats = [];
const seatLetters = ['A','B','C','D','E','F','G','H','I','J','K','L'];
const seatNumbers = [1,2,3,4,5,6];

function generateForms() {
  const count = parseInt(document.getElementById('ticket_count').value);
  const container = document.getElementById('passenger_forms');
  container.innerHTML = '';

  for (let i = 1; i <= count; i++) {
    const formHTML = `
      <hr>
      <h4>Пътник ${i}</h4>
      <label>Име:</label><input type="text" name="first_name[]" required>
      <label>Фамилия:</label><input type="text" name="last_name[]" required>
      <label>Паспорт:</label><input type="text" name="passport[]" required>
      <label>Имейл:</label><input type="email" name="email[]" required>
      <label>Телефон:</label><input type="text" name="phone[]">
      <label>Място:</label>
      <div class="seat-map" id="seat-map-${i}"></div>
      <input type="hidden" name="seat_number[]" id="seat_number_${i}" required>
    `;
    container.insertAdjacentHTML('beforeend', formHTML);
    generateSeatMap(`seat-map-${i}`, `seat_number_${i}`);
  }
}

function generateSeatMap(containerId, inputId) {
  const container = document.getElementById(containerId);
  container.innerHTML = '';
  seatLetters.forEach(letter => {
    seatNumbers.forEach(num => {
      const seat = letter + num;
      const div = document.createElement('div');
      div.classList.add('seat');
      div.textContent = seat;
      if (bookedSeats.includes(seat)) {
        div.classList.add('booked');
      } else {
        div.addEventListener('click', () => {
          container.querySelectorAll('.seat').forEach(s => s.classList.remove('selected'));
          div.classList.add('selected');
          document.getElementById(inputId).value = seat;
        });
      }
      container.appendChild(div);
    });
  });
}

document.querySelector('select[name="flight_id"]').addEventListener('change', function() {
  const flightId = this.value;
  fetch(`get_booked_seats.php?flight_id=${flightId}`)
    .then(response => response.json())
    .then(data => {
      bookedSeats = data;
      generateForms();
    });
});

function updateFlightPrice(selectElement) {
  const selectedOption = selectElement.options[selectElement.selectedIndex];
  const price = selectedOption.getAttribute('data-price');
  document.getElementById('flight_price').value = price;
  calculateTotalPrice();
}

function calculateTotalPrice() {
  const priceInput = document.getElementById('flight_price');
  const baggageSelect = document.getElementById('baggage');
  const ticketCount = parseInt(document.getElementById('ticket_count')?.value || "0");
  const flightPrice = parseFloat(priceInput.value) || 0;
  const baggagePrice = parseFloat(baggageSelect.value) || 0;
  const totalPrice = (flightPrice * ticketCount) + baggagePrice;
  const totalPriceElement = document.getElementById("totalPriceDisplay");
  if (totalPriceElement) {
    totalPriceElement.textContent = `Обща цена: ${totalPrice.toFixed(2)} лв.`;
  }
}

window.addEventListener('DOMContentLoaded', () => {
  const initialSelect = document.querySelector('select[name="flight_id"]');
  if (initialSelect && initialSelect.value) {
    updateFlightPrice(initialSelect); 
    fetch(`get_booked_seats.php?flight_id=${initialSelect.value}`)
      .then(response => response.json())
      .then(data => {
        bookedSeats = data;
        generateForms();
        calculateTotalPrice();
      });
  }
});
</script>
</body>
</html>

