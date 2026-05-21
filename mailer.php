<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$owner   = 'nandhudev804@gmail.com';
$name    = htmlspecialchars(strip_tags(trim($_POST['name']    ?? '')));
$phone   = htmlspecialchars(strip_tags(trim($_POST['phone']   ?? '')));
$email   = htmlspecialchars(strip_tags(trim($_POST['email']   ?? '')));
$message = htmlspecialchars(strip_tags(trim($_POST['message'] ?? '')));
$page    = htmlspecialchars(strip_tags(trim($_POST['page']    ?? 'Website')));

if (empty($name) || empty($phone) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Name, phone and email are required.']);
    exit;
}

// ── 1. Notify you ────────────────────────────────────────────────────────────
$subject1 = "New Contact Form Submission — NexInnovators ($page)";
$body1    = "New enquiry from $page\r\n\r\n"
          . "Name   : $name\r\n"
          . "Phone  : $phone\r\n"
          . "Email  : $email\r\n\r\n"
          . "Message:\r\n$message";
$headers1 = "From: noreply@nexinnovators.com\r\n"
          . "Reply-To: $email\r\n"
          . "X-Mailer: PHP/" . phpversion();

mail($owner, $subject1, $body1, $headers1);

// ── 2. Auto-reply to the client ──────────────────────────────────────────────
$subject2 = "Thank you for contacting NexInnovators!";
$body2    = "Hi $name,\r\n\r\n"
          . "Thank you for reaching out to us!\r\n\r\n"
          . "We have received your enquiry and our team will contact you soon.\r\n\r\n"
          . "Best regards,\r\n"
          . "NexInnovators Team\r\n"
          . "nandhudev804@gmail.com\r\n"
          . "+91 95858 33569";
$headers2 = "From: NexInnovators <noreply@nexinnovators.com>\r\n"
          . "Reply-To: $owner\r\n"
          . "X-Mailer: PHP/" . phpversion();

mail($email, $subject2, $body2, $headers2);

echo json_encode(['success' => true, 'message' => 'Successfully submitted! We will contact you soon.']);
