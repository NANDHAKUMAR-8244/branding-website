<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$to      = 'nandhudev804@gmail.com';
$name    = htmlspecialchars(strip_tags(trim($_POST['name']    ?? '')));
$phone   = htmlspecialchars(strip_tags(trim($_POST['phone']   ?? '')));
$message = htmlspecialchars(strip_tags(trim($_POST['message'] ?? '')));

if (empty($name) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Name and phone are required.']);
    exit;
}

$subject = 'New Contact Form Submission — NexInnovators';

$body = "Name: {$name}\r\nPhone: {$phone}\r\n\r\nMessage:\r\n{$message}";

$headers = "From: noreply@nexinnovators.com\r\n" .
           "Reply-To: {$to}\r\n" .
           "X-Mailer: PHP/" . phpversion();

if (mail($to, $subject, $body, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Successfully submitted! We will contact you soon.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send. Please try again later.']);
}
