<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

// --------------------------------------------------
// Configuration
// --------------------------------------------------

$recipientEmail = 'mail@mamidavintltd.com';
$siteName       = 'Mamidav International Limited';
$siteDomain     = 'mamidavintltd.com';

// --------------------------------------------------
// Only allow POST requests
// --------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'error'   => 'Method not allowed.'
    ]);

    exit;
}

// --------------------------------------------------
// Read JSON request
// --------------------------------------------------

$rawInput = file_get_contents('php://input');

if ($rawInput === false || trim($rawInput) === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'error'   => 'No form data was received.'
    ]);

    exit;
}

$data = json_decode($rawInput, true);

if (!is_array($data)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'error'   => 'Invalid request data.'
    ]);

    exit;
}

// --------------------------------------------------
// Helper functions
// --------------------------------------------------

function cleanText($value, int $maxLength = 500): string
{
    if (!is_string($value)) {
        return '';
    }

    $value = trim($value);

    // Remove null bytes
    $value = str_replace("\0", '', $value);

    // Normalize line endings
    $value = str_replace(["\r\n", "\r"], "\n", $value);

    if (mb_strlen($value) > $maxLength) {
        $value = mb_substr($value, 0, $maxLength);
    }

    return $value;
}

function cleanHeaderValue($value): string
{
    $value = cleanText($value, 200);

    // Prevent email header injection
    $value = str_replace(["\r", "\n"], '', $value);

    return $value;
}

// --------------------------------------------------
// Get submitted fields
// --------------------------------------------------

$fullName = cleanText(
    $data['full_name'] ?? '',
    150
);

$email = cleanHeaderValue(
    $data['email'] ?? ''
);

$phone = cleanText(
    $data['phone'] ?? '',
    50
);

$areaOfInterest = cleanText(
    $data['area_of_interest'] ?? '',
    100
);

$investmentAmount = cleanText(
    $data['investment_amount'] ?? '',
    100
);

$message = cleanText(
    $data['message'] ?? '',
    3000
);

// Honeypot field
$website = cleanText(
    $data['website'] ?? '',
    100
);

// --------------------------------------------------
// Basic anti-bot honeypot
// --------------------------------------------------

if ($website !== '') {
    // Pretend the submission was successful.
    // This prevents bots from learning that they were blocked.

    echo json_encode([
        'success' => true,
        'message' => 'Your inquiry has been submitted successfully.'
    ]);

    exit;
}

// --------------------------------------------------
// Validate required fields
// --------------------------------------------------

if ($fullName === '') {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'error'   => 'Please enter your full name.'
    ]);

    exit;
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'error'   => 'Please enter a valid email address.'
    ]);

    exit;
}

if ($phone === '') {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'error'   => 'Please enter your phone number.'
    ]);

    exit;
}

if ($areaOfInterest === '') {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'error'   => 'Please select an area of interest.'
    ]);

    exit;
}

// --------------------------------------------------
// Format investment amount
// --------------------------------------------------

$formattedAmount = 'Not provided';

if ($investmentAmount !== '') {

    $numericAmount = preg_replace(
        '/[^0-9.]/',
        '',
        $investmentAmount
    );

    if ($numericAmount !== '' && is_numeric($numericAmount)) {

        $formattedAmount =
            '₦' .
            number_format(
                (float) $numericAmount,
                0,
                '.',
                ','
            );
    }
}

// --------------------------------------------------
// Log for sales/business record (CSV export)
// --------------------------------------------------

require_once __DIR__ . '/bootstrap.php';

try {
    $logAmount = (isset($numericAmount) && $numericAmount !== '' && is_numeric($numericAmount)) ? (int) $numericAmount : null;
    $summary = "Area: {$areaOfInterest}; Amount: {$formattedAmount}" . ($message !== '' ? "; Message: {$message}" : '');
    $insert = get_db()->prepare(
        'INSERT INTO purchase_log (type, customer_name, customer_email, customer_phone, summary, amount, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $insert->execute(['investment_inquiry', $fullName, $email, $phone, $summary, $logAmount, current_user_id()]);
} catch (Throwable $e) {
    error_log('Mamidav purchase_log insert failed (investment_inquiry): ' . $e->getMessage());
}

// --------------------------------------------------
// Prepare email subject
// --------------------------------------------------

$subject =
    'Investment Inquiry - ' .
    $siteName;

// --------------------------------------------------
// Prepare email body
// --------------------------------------------------

$emailBody =
    "Dear {$siteName},\n\n" .

    "A new investment inquiry has been submitted through the website.\n\n" .

    "INVESTMENT INQUIRY DETAILS\n" .
    "==========================\n\n" .

    "Full Name: {$fullName}\n" .
    "Email: {$email}\n" .
    "Phone: {$phone}\n" .
    "Area of Interest: {$areaOfInterest}\n" .
    "Investment Amount: {$formattedAmount}\n\n" .

    "MESSAGE\n" .
    "=======\n" .
    ($message !== '' ? $message : 'Not provided') .
    "\n\n" .

    "----------------------------------------\n" .
    "Submitted through {$siteDomain}\n" .
    "----------------------------------------\n";

// --------------------------------------------------
// Email headers
// --------------------------------------------------

$headers = [];

$headers[] =
    'From: ' .
    $siteName .
    ' <' .
    $recipientEmail .
    '>';

$headers[] =
    'Reply-To: ' .
    $email;

$headers[] =
    'MIME-Version: 1.0';

$headers[] =
    'Content-Type: text/plain; charset=UTF-8';

$headers[] =
    'X-Mailer: PHP/' .
    phpversion();

$headerString =
    implode("\r\n", $headers);

// --------------------------------------------------
// Send email to Mamidav
// --------------------------------------------------

$sent = mail(
    $recipientEmail,
    $subject,
    $emailBody,
    $headerString
);

// --------------------------------------------------
// Handle result
// --------------------------------------------------

if (!$sent) {

    error_log(
        'Mamidav investment inquiry email could not be sent.'
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error'   =>
            'We could not send your inquiry at this time. Please try again later.'
    ]);

    exit;
}

// --------------------------------------------------
// Optional automatic confirmation to investor
// --------------------------------------------------

$confirmationSubject =
    'We Received Your Investment Inquiry - ' .
    $siteName;

$confirmationBody =
    "Dear {$fullName},\n\n" .

    "Thank you for your investment inquiry with {$siteName}.\n\n" .

    "We have successfully received your inquiry and our team will review the information provided.\n\n" .

    "Your inquiry details:\n" .
    "----------------------------------------\n" .
    "Area of Interest: {$areaOfInterest}\n" .
    "Investment Amount: {$formattedAmount}\n" .
    "----------------------------------------\n\n" .

    "We will contact you using the email address or phone number you provided.\n\n" .

    "Thank you for your interest in {$siteName}.\n\n" .

    "Kind regards,\n" .
    "{$siteName}\n" .
    "Website: https://{$siteDomain}";

$confirmationHeaders = [];

$confirmationHeaders[] =
    'From: ' .
    $siteName .
    ' <' .
    $recipientEmail .
    '>';

$confirmationHeaders[] =
    'Reply-To: ' .
    $recipientEmail;

$confirmationHeaders[] =
    'MIME-Version: 1.0';

$confirmationHeaders[] =
    'Content-Type: text/plain; charset=UTF-8';

@mail(
    $email,
    $confirmationSubject,
    $confirmationBody,
    implode("\r\n", $confirmationHeaders)
);

// --------------------------------------------------
// Success response
// --------------------------------------------------

echo json_encode([
    'success' => true,
    'message' =>
        'Your investment inquiry has been submitted successfully.'
]);

exit;