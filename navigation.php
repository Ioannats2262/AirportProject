<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
<nav class="nav-bar">
  <div class="nav-left">
    <a href="homepage.php" class="logo">
      <img src="images/logo.png" alt="Лого">
    </a>
  </div>

  <div class="nav-center">
    <ul class="nav-links">
      <li class="dropdown">
        <a href="#">Полети</a>
        <ul class="dropdown-menu">
          <li><a href="departures.php">Заминаващи</a></li>
          <li><a href="arrivals.php">Пристигащи</a></li>
        </ul>
      </li>
      <li><a href="destination.php">Дестинации</a></li>
      <li><a href="entertainments.php">Развлечения</a></li>
      <li><a href="airlines.php">Авиокомпании</a></li>
      <?php if (isset($_SESSION['user_id'])): ?>
      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <li><a href="admin.php">Админ</a></li>
      <?php endif; ?>
      <li><a href="profile.php">Профил</a></li>
      <li><a href="logout.php">Изход</a></li>
    <?php else: ?>
      <li><a href="login.php">Вход</a></li>
      <li><a href="registration.php">Регистрация</a></li>
    <?php endif; ?>
    </ul>
  </div>
</nav>
</header>
