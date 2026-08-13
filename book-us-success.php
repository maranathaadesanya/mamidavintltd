<?php
session_start();
$ref = htmlspecialchars($_GET['ref'] ?? ($_SESSION['booking_success_ref'] ?? ''), ENT_QUOTES);
$pkg = htmlspecialchars($_SESSION['booking_success_pkg'] ?? '', ENT_QUOTES);
$price = isset($_SESSION['booking_success_price']) ? number_format((int)$_SESSION['booking_success_price'],0,'.',',') : '';
// clear success info
unset($_SESSION['booking_success_ref'], $_SESSION['booking_success_pkg'], $_SESSION['booking_success_price']);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Booking Request Received — Mamidav</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header><div class="header-inner"><a href="index.html"><img src="logo.png" class="logo" alt="Mamidav logo"></a></div></header>
<main class="container" style="padding:48px 20px;max-width:800px">
  <div class="form-card">
    <h1>Booking Request Received</h1>
    <p>Thank you for contacting Mamidav International Limited.</p>
    <?php if ($ref): ?>
      <p><strong>Booking Reference:</strong> <?php echo $ref; ?></p>
    <?php endif; ?>
    <?php if ($pkg): ?>
      <p><strong>Selected Package:</strong> <?php echo $pkg; ?> — ₦<?php echo $price; ?></p>
    <?php endif; ?>
    <p>Our team will review your request and contact you regarding availability and next steps.</p>
    <p style="margin-top:18px"><a class="btn" href="index.html">Return to Home</a> <a class="btn" href="services.html">View Services</a></p>
  </div>
</main>
<footer class="site-footer"><div class="container footer-bottom"><span>© 2026 Mamidav International Limited</span></div></footer>
</body>
</html>
