<?php
session_start();
// Read any preserved form data or errors from session (set by submit-booking.php)
$old = $_SESSION['booking_old'] ?? [];
$errors = $_SESSION['booking_errors'] ?? [];
unset($_SESSION['booking_old'], $_SESSION['booking_errors']);

// Simple CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

function old_val($k, $default='') {
    global $old;
    if (isset($old[$k])) return htmlspecialchars($old[$k], ENT_QUOTES);
    return $default;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mamidav International Limited | Book Us</title>
<meta name="description" content="Book Mamidav for event management. Select your package and submit a booking enquiry.">
<link rel="icon" href="logo.png">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="site-topbar"><div class="container"><span>Integrated Agribusiness, Engineering &amp; Services</span><span class="secondary">Reliable. Practical. Professional.</span></div></div>
<header><div class="header-inner"><a href="index.html"><img src="logo.png" class="logo" alt="Mamidav International Limited logo"></a><div class="brand-title"><strong>Mamidav International Limited</strong><small>INTEGRATED SOLUTIONS &amp; SERVICES</small></div><button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false"><span></span><span></span><span></span></button><nav aria-label="Main navigation"><a href="index.html">Home</a><a href="about.html">About</a><a href="services.html">Services</a><a href="investors.html">Investors</a><a href="contact.html">Contact Us</a><a href="cart.html" class="cart-link">Cart<span class="cart-badge">0</span></a></nav></div></header>

<main class="container" style="padding:28px 20px">
  <div style="display:grid;grid-template-columns:1fr 320px;gap:28px">
    <section>
      <h1>Book Our Event Services</h1>
      <p class="lead">Please provide some details about your event and selected package. We'll review and get back to you with availability and next steps.</p>

      <?php if (!empty($errors)): ?>
        <div class="form-card" role="status" aria-live="polite">
          <h3>There were problems with your submission</h3>
          <ul class="form-errors">
            <?php foreach ($errors as $e): ?>
              <li><?php echo htmlspecialchars($e, ENT_QUOTES); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form id="booking-form" class="form-card" action="submit-booking.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
        <fieldset>
          <legend>Client information</legend>
          <label>Full Name<input type="text" name="full_name" required value="<?php echo old_val('full_name'); ?>"></label>
          <label>Email Address<input type="email" name="email" required value="<?php echo old_val('email'); ?>"></label>
          <label>Phone Number<input type="tel" name="phone" required value="<?php echo old_val('phone'); ?>"></label>
        </fieldset>

        <fieldset>
          <legend>Event information</legend>
          <label>Event Type<input type="text" name="event_type" placeholder="e.g. Wedding, Conference" value="<?php echo old_val('event_type'); ?>"></label>
          <label>Preferred Event Date<input type="date" name="event_date" value="<?php echo old_val('event_date'); ?>"></label>
          <label>Event Location / Venue<input type="text" name="event_location" value="<?php echo old_val('event_location'); ?>"></label>
          <label>Expected Number of Guests<input type="number" name="expected_guests" min="1" value="<?php echo old_val('expected_guests'); ?>"></label>
        </fieldset>

        <fieldset>
          <legend>Package</legend>
          <label>Selected Package<input type="text" id="selected-package" name="selected_package_readonly" readonly value="<?php echo old_val('Selected Package'); ?>"></label>
          <label>Package Price<input type="text" id="selected-price" name="selected_price_readonly" readonly value="<?php echo old_val('Package Price'); ?>"></label>
          <input type="hidden" id="package-hidden" name="package" value="<?php echo old_val('package'); ?>">
          <label>Change package
            <select id="package-select" name="package_choice">
              <option value="">(keep selected)</option>
              <option value="basic">Basic - ₦150,000</option>
              <option value="standard">Standard - ₦350,000</option>
              <option value="premium">Premium - ₦750,000</option>
            </select>
          </label>
        </fieldset>

        <label>Tell us about your event<textarea name="message" placeholder="Tell us about your event, special requests, or questions"><?php echo old_val('message'); ?></textarea></label>

        <button type="submit" class="btn">Submit Booking Request</button>
      </form>
    </section>

    <aside>
      <div class="form-card">
        <h3>Your Selected Package</h3>
        <div id="package-summary">
          <p id="summary-name"><strong>Select a package</strong></p>
          <p id="summary-price"></p>
          <div id="summary-features"></div>
        </div>
        <p style="margin-top:12px"><a id="view-package-link" href="#">← View Package Details</a></p>
      </div>

      <div class="form-card" style="margin-top:16px">
        <h4>Need help?</h4>
        <p>Email us at <a href="mailto:mail@mamidavintltd.com">mail@mamidavintltd.com</a> or call us for a quick discussion.</p>
      </div>
    </aside>
  </div>
</main>

<footer class="site-footer"><div class="container footer-grid"><div class="footer-brand"><img src="logo.png" class="footer-logo" alt="Mamidav logo"><p>Integrated operational execution across agriculture, events, culinary services and power engineering.</p></div><div><h4>Company</h4><a href="index.html">Home</a><a href="about.html">About</a><a href="agriculture.html">Agriculture</a><a href="engineering.html">Engineering</a><a href="services.html">Services</a></div><div><h4>Quick links</h4><a href="investors.html">Investors</a><a href="dashboard.html">Dashboard</a><a href="cart.html">Cart</a><a href="contact.html">Contact Us</a></div><div><h4>Contact Us</h4><a href="#" class="copy-email" data-email="mail@mamidavintltd.com">mail@mamidavintltd.com</a><span>8:00 AM - 6:00 PM Monday - Saturday</span><span>Anuoluwapo street, Omusoko, Off Arigbawonwo, Mowe, Nigeria, 110115</span></div></div><div class="container footer-bottom"><span>© 2026 Mamidav International Limited</span><span>Integrated solutions. Reliable execution.</span></div></footer>

<script>
(function(){
  const pkgMap = {
    basic: {name:'Basic Event Package', price:'₦150,000', features:['Event day coordination','Vendor liaison','Setup & breakdown supervision'], page:'basic-event-package.html'},
    standard: {name:'Standard Event Package', price:'₦350,000', features:['Everything in Basic','Full event planning from concept','Decor coordination','Dedicated event lead'], page:'standard-event-package.html'},
    premium: {name:'Premium Event Package', price:'₦750,000', features:['Everything in Standard','Guest management & RSVP tracking','On-site team for full duration','Post-event report'], page:'premium-event-package.html'},
  };
  function setPackage(key){
    const sel = pkgMap[key];
    const nameEl = document.getElementById('selected-package');
    const priceEl = document.getElementById('selected-price');
    const hidden = document.getElementById('package-hidden');
    const summaryName = document.getElementById('summary-name');
    const summaryPrice = document.getElementById('summary-price');
    const featuresEl = document.getElementById('summary-features');
    const viewLink = document.getElementById('view-package-link');
    if(!sel){
      nameEl.value = '';
      priceEl.value = '';
      hidden.value = '';
      summaryName.innerHTML = '<strong>No package selected</strong>';
      summaryPrice.textContent = '';
      featuresEl.innerHTML = '<p>Please choose a package above or <a href="event-packages.html">view all packages</a>.</p>';
      viewLink.href = 'event-packages.html';
      return;
    }
    nameEl.value = sel.name;
    priceEl.value = sel.price;
    hidden.value = key;
    summaryName.innerHTML = '<strong>'+sel.name+'</strong>';
    summaryPrice.textContent = sel.price;
    featuresEl.innerHTML = '<ul>' + sel.features.map(f=>'<li>'+f+'</li>').join('') + '</ul>';
    viewLink.href = sel.page;
  }
  document.addEventListener('DOMContentLoaded', function(){
    const params = new URLSearchParams(location.search);
    const pkg = (params.get('package')||'').toLowerCase();
    if(pkg && pkgMap[pkg]){
      setPackage(pkg);
      const ps = document.getElementById('package-select'); if(ps) ps.value='';
    } else if(pkg) {
      setPackage(null);
    }
    const ps = document.getElementById('package-select');
    if(ps) ps.addEventListener('change', function(e){
      const v = e.target.value;
      if(v && pkgMap[v]) setPackage(v);
    });
    // If server provided old values for selected package, reflect them
    try{
      const oldPkg = '<?php echo isset($old['package']) ? addslashes($old['package']) : ''; ?>';
      if(oldPkg && pkgMap[oldPkg]) setPackage(oldPkg);
    }catch(e){}
  });
})();
</script>
<script src="nav.js"></script>
<script src="cart.js"></script>
</body>
</html>
