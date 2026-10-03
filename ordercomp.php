<?php require('nav.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Order Processing</title>
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="ordercompStyle.css">
</head>

<body>
  <?php require('header.php'); ?>
  <div class="order">
    <span class="default">Processing Order</span>

    <span class="success">
      Order Confirmed <br>check your mail
      <svg viewBox="0 0 12 10">
        <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
      </svg>
    </span>

    <div class="box-wrap">
      <div class="box"></div>
    </div>

    <div class="truck-wrap">
      <div class="truck">
        <div class="back"></div>
        <div class="front">
          <div class="window"></div>
        </div>
      </div>
    </div>

    <div class="lines"></div>

    <a href="revZone.php" class="dropped-button">
      <span>Back to Main Page</span>
    </a>
  </div>

  <script>
    window.addEventListener("load", () => {
      document.querySelector(".order").classList.add("animate");
    });
  </script>

</body>

</html>