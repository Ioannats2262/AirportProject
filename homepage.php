<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Летище Варна</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php include 'navigation.php'; ?>
<div class="carousel-container">
  <img class="carousel-image active" src="images/varna.jpg">
  <img class="carousel-image" src="images/airrelay.jpg">
  <img class="carousel-image" src="images/varnaair.jpg">
</div>

  <div class="content">
  <h1>Добре дошли на Летище Варна</h1>
  <p style="font-size: 18px; line-height: 1.6;">
    Разположено на брега на Черно море, <strong>Летище Варна</strong> е въздушната врата към Североизточна България.<br>
    Съчетавайки модерна инфраструктура с гостоприемна атмосфера, <br>
    летището обслужва над <strong>1 милион пътници годишно</strong> <br>
    и свързва Варна с над <strong>10 дестинации в Европа и Близкия изток</strong>.
  </p>
  <p style="font-size: 18px; line-height: 1.6;">
    Независимо дали пристигате за лятна почивка, <br>
    бизнес среща или културно приключение, Летище Варна <br>
    ще ви посрещне с удобства, които включват<br>
    комфортни зони за отдих, разнообразни заведения <br>
    и отлична транспортна свързаност до града и курортите.<br>
  </p>
  <p style="font-size: 18px; line-height: 1.6;">
    Тук всяко пътуване започва с усмивка – <em>а Варна ви очаква на само няколко минути от терминала</em>.
  </p>
</div>


  <?php include 'footer.php'; ?>

  <script>
  const images = document.querySelectorAll('.carousel-image');
  let index = 0;

  setInterval(() => {
    images[index].classList.remove('active');
    index = (index + 1) % images.length;
    images[index].classList.add('active');
  }, 4000);
</script>

</body>
</html>

