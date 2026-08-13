<?php
// submit-booking.php
// Processes booking form submissions from book-us.php

error_reporting(0);
ini_set('display_errors', '0');
session_start();

// Allowed packages and server-side prices (in Naira integer)
$packages = [
    'basic' => ['name' => 'Basic Event Package', 'price' => 150000],
    'standard' => ['name' => 'Standard Event Package', 'price' => 350000],
    'premium' => ['name' => 'Premium Event Package', 'price' => 750000],
];

// Business recipient (site uses this address across pages)
$business_email = 'mail@mamidavintltd.com';

// Basic helpers
function safe_text($s) { return trim((string)$s); }
function sanitize_header($s) { return preg_replace('/[\r\n]+/', ' ', trim((string)$s)); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

$errors = [];

// CSRF token check
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $errors[] = 'Invalid form submission (CSRF). Please try again.';
}

$full_name = safe_text($_POST['full_name'] ?? '');
$email = safe_text($_POST['email'] ?? '');
$phone = safe_text($_POST['phone'] ?? '');
$event_type = safe_text($_POST['event_type'] ?? '');
$event_date = safe_text($_POST['event_date'] ?? '');
$event_location = safe_text($_POST['event_location'] ?? '');
$expected_guests = safe_text($_POST['expected_guests'] ?? '');
$message = safe_text($_POST['message'] ?? '');
$package_submitted = strtolower(safe_text($_POST['package'] ?? ''));
$package_choice = strtolower(safe_text($_POST['package_choice'] ?? ''));

// Required fields
if ($full_name === '') $errors[] = 'Full name is required.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
if ($phone === '' ) $errors[] = 'Phone number is required.';

// Phone simple validation
if ($phone !== '' && !preg_match('/^[0-9 \+\-()]{6,30}$/', $phone)) $errors[] = 'Phone number format looks invalid.';

// Event date validation (optional but if present must parse)
if ($event_date !== '') {
    $ts = strtotime($event_date);
    if ($ts === false) $errors[] = 'Event date is not a valid date.';
}

// Determine package: priority to package_choice if provided
$package_key = '';
if ($package_choice !== '' && array_key_exists($package_choice, $packages)) {
    $package_key = $package_choice;
} elseif ($package_submitted !== '' && array_key_exists($package_submitted, $packages)) {
    $package_key = $package_submitted;
} else {
    $errors[] = 'Please select a valid package.';
}

// If there are validation errors, preserve input and redirect back
if (!empty($errors)) {
    $_SESSION['booking_old'] = $_POST;
    $_SESSION['booking_errors'] = $errors;
    header('Location: book-us.php');
    exit;
}

// Compute server-side price and package name
$pkgInfo = $packages[$package_key];
$package_name = $pkgInfo['name'];
$package_price = $pkgInfo['price'];

// Booking reference
$ref = 'MIL-EVT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

// Build email bodies
$subject_admin = "New Event Booking Request — $ref";
$body_lines = [];
$body_lines[] = "New Event Booking Request";
$body_lines[] = "Reference: $ref";
$body_lines[] = "";
$body_lines[] = "Customer Information:";
$body_lines[] = "Name: $full_name";
$body_lines[] = "Email: $email";
$body_lines[] = "Phone: $phone";
$body_lines[] = "";
$body_lines[] = "Event Information:";
$body_lines[] = "Event Type: $event_type";
$body_lines[] = "Event Date: $event_date";
$body_lines[] = "Venue: $event_location";
$body_lines[] = "Expected Guests: $expected_guests";
$body_lines[] = "";
$body_lines[] = "Package:";
$body_lines[] = "Package Name: $package_name";
$body_lines[] = "Package Price: ₦" . number_format($package_price, 0, '.', ',');
$body_lines[] = "";
$body_lines[] = "Additional Requirements:";
$body_lines[] = $message ?: '-';

$body_admin = implode("\n", $body_lines);

// Headers (prevent header injection)
$safe_reply_to = sanitize_header($email);
$safe_name = sanitize_header($full_name);
$from_address = 'no-reply@mamidavintltd.com';
$headers = [];
$headers[] = 'From: Mamidav Website <' . $from_address . '>';
if ($safe_reply_to) $headers[] = 'Reply-To: ' . $safe_reply_to;
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

$mail_ok = @mail($business_email, $subject_admin, $body_admin, implode("\r\n", $headers));

if (!$mail_ok) {
    // Try to fall back: store error and redirect back
    $_SESSION['booking_old'] = $_POST;
    $_SESSION['booking_errors'] = ['Failed to send booking notification. Please try again later.'];
    header('Location: book-us.php');
    exit;
}

// Send confirmation to customer
$subject_cust = "Your Mamidav booking request has been received — $ref";
$cust_lines = [];
$cust_lines[] = "Hello $safe_name,";
$cust_lines[] = "";
$cust_lines[] = "Your booking request has been received. Reference: $ref";
$cust_lines[] = "";
$cust_lines[] = "Selected Package: $package_name";
$cust_lines[] = "Package Price: ₦" . number_format($package_price, 0, '.', ',');
$cust_lines[] = "Event Date: $event_date";
$cust_lines[] = "";
$cust_lines[] = "Our team will review your request and contact you regarding availability and next steps.";
$cust_lines[] = "";
$cust_lines[] = "Thank you,\nMamidav International Limited";

$body_cust = implode("\n", $cust_lines);

// mail() may fail silently; ignore result but continue to success page
@mail($email, $subject_cust, $body_cust, implode("\r\n", $headers));

// On success, clear any old data and redirect to confirmation
unset($_SESSION['booking_old']);
unset($_SESSION['booking_errors']);
$_SESSION['booking_success_ref'] = $ref;
$_SESSION['booking_success_pkg'] = $package_name;
$_SESSION['booking_success_price'] = $package_price;

header('Location: book-us-success.php?ref=' . urlencode($ref));
exit;
