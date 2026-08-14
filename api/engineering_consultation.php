<?php

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);

    exit;
}

$rawInput = file_get_contents('php://input');

$data = json_decode($rawInput, true);

if (!is_array($data)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid form data.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Get submitted fields
|--------------------------------------------------------------------------
*/

$fullName = trim($data['Full Name'] ?? '');
$company = trim($data['Company'] ?? '');
$email = trim($data['Email'] ?? '');
$phone = trim($data['Phone'] ?? '');
$projectType = trim($data['Project Type'] ?? '');
$projectDescription = trim($data['Project Description'] ?? '');


/*
|--------------------------------------------------------------------------
| Validate required fields
|--------------------------------------------------------------------------
*/

if ($fullName === '' || $email === '' || $phone === '') {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Please complete all required fields.'
    ]);

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Please provide a valid email address.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Email settings
|--------------------------------------------------------------------------
*/

$recipient = 'mail@mamidavintltd.com';

$subject = 'Engineering Consultation Request - Mamidav International Limited';


/*
|--------------------------------------------------------------------------
| Build email
|--------------------------------------------------------------------------
*/

$emailBody = "A new Engineering Consultation Request has been submitted.\n\n";

$emailBody .= "FULL NAME\n";
$emailBody .= $fullName . "\n\n";

$emailBody .= "COMPANY\n";
$emailBody .= ($company !== '' ? $company : 'Not provided') . "\n\n";

$emailBody .= "EMAIL\n";
$emailBody .= $email . "\n\n";

$emailBody .= "PHONE\n";
$emailBody .= $phone . "\n\n";

$emailBody .= "PROJECT TYPE\n";
$emailBody .= ($projectType !== '' ? $projectType : 'Not specified') . "\n\n";

$emailBody .= "PROJECT DESCRIPTION\n";
$emailBody .= ($projectDescription !== '' ? $projectDescription : 'Not provided') . "\n\n";

$emailBody .= "----------------------------------------\n";
$emailBody .= "Mamidav International Limited\n";
$emailBody .= "Engineering Consultation Form\n";


/*
|--------------------------------------------------------------------------
| Email headers
|--------------------------------------------------------------------------
*/

$headers = [];

$headers[] = 'From: Mamidav International Limited <mail@mamidavintltd.com>';
$headers[] = 'Reply-To: ' . $email;
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';


/*
|--------------------------------------------------------------------------
| Send email
|--------------------------------------------------------------------------
*/

$sent = mail(
    $recipient,
    $subject,
    $emailBody,
    implode("\r\n", $headers)
);


/*
|--------------------------------------------------------------------------
| Return response
|--------------------------------------------------------------------------
*/

if ($sent) {

    echo json_encode([
        'success' => true,
        'message' => 'Your consultation request has been sent successfully. Mamidav International Limited will contact you shortly.'
    ]);

    exit;
}


http_response_code(500);

echo json_encode([
    'success' => false,
    'message' => 'We could not send your consultation request at this time. Please try again later.'
]);

exit;