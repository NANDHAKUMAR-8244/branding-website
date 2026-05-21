<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// ── Config ────────────────────────────────────────────────────────────────────
$SMTP_HOST = 'smtp.gmail.com';
$SMTP_PORT = 587;
$SMTP_USER = 'nandhudev804@gmail.com';
$SMTP_PASS = 'mpbxzlqahzjhzxfu';
$OWNER     = 'nandhudev804@gmail.com';

// ── Input ─────────────────────────────────────────────────────────────────────
$name    = htmlspecialchars(strip_tags(trim(isset($_POST['name'])    ? $_POST['name']    : '')));
$phone   = htmlspecialchars(strip_tags(trim(isset($_POST['phone'])   ? $_POST['phone']   : '')));
$email   = filter_var(trim(isset($_POST['email'])   ? $_POST['email']   : ''), FILTER_SANITIZE_EMAIL);
$message = htmlspecialchars(strip_tags(trim(isset($_POST['message']) ? $_POST['message'] : '')));
$page    = htmlspecialchars(strip_tags(trim(isset($_POST['page'])    ? $_POST['page']    : 'Website')));

if (empty($name) || empty($phone) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Name, phone and a valid email are required.']);
    exit;
}

// ── Minimal SMTP sender ───────────────────────────────────────────────────────
function smtp_send($host, $port, $user, $pass, $from, $fromName, $to, $toName, $subject, $body) {
    $sock = fsockopen($host, $port, $errno, $errstr, 10);
    if (!$sock) return false;

    function smtp_cmd($sock, $cmd, $expect) {
        if ($cmd) fwrite($sock, $cmd . "\r\n");
        $resp = '';
        while (($line = fgets($sock, 512)) !== false) {
            $resp .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return (int)substr($resp, 0, 3) === (int)$expect ? $resp : false;
    }

    if (!smtp_cmd($sock, null, 220))         { fclose($sock); return false; }
    if (!smtp_cmd($sock, "EHLO localhost", 250)) { fclose($sock); return false; }
    if (!smtp_cmd($sock, "STARTTLS", 220))   { fclose($sock); return false; }

    stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

    if (!smtp_cmd($sock, "EHLO localhost", 250))                         { fclose($sock); return false; }
    if (!smtp_cmd($sock, "AUTH LOGIN", 334))                              { fclose($sock); return false; }
    if (!smtp_cmd($sock, base64_encode($user), 334))                      { fclose($sock); return false; }
    if (!smtp_cmd($sock, base64_encode($pass), 235))                      { fclose($sock); return false; }
    if (!smtp_cmd($sock, "MAIL FROM:<$from>", 250))                       { fclose($sock); return false; }
    if (!smtp_cmd($sock, "RCPT TO:<$to>", 250))                           { fclose($sock); return false; }
    if (!smtp_cmd($sock, "DATA", 354))                                    { fclose($sock); return false; }

    $date    = date('r');
    $msgBody = "Date: $date\r\n"
             . "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$from>\r\n"
             . "To: =?UTF-8?B?" . base64_encode($toName) . "?= <$to>\r\n"
             . "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n"
             . "\r\n"
             . $body . "\r\n.\r\n";

    if (!smtp_cmd($sock, $msgBody, 250)) { fclose($sock); return false; }
    smtp_cmd($sock, "QUIT", 221);
    fclose($sock);
    return true;
}

// ── 1. Notify you ─────────────────────────────────────────────────────────────
$sub1  = "New Enquiry from NexInnovators ($page)";
$body1 = "New enquiry received from: $page\r\n\r\n"
       . "Name    : $name\r\n"
       . "Phone   : $phone\r\n"
       . "Email   : $email\r\n\r\n"
       . "Message :\r\n$message";

smtp_send($SMTP_HOST, $SMTP_PORT, $SMTP_USER, $SMTP_PASS,
    $SMTP_USER, 'NexInnovators Website',
    $OWNER, 'Nandha Kumar',
    $sub1, $body1);

// ── 2. Auto-reply to client ────────────────────────────────────────────────────
$sub2  = "Thank you for contacting NexInnovators!";
$body2 = "Hi $name,\r\n\r\n"
       . "Thank you for reaching out to us!\r\n\r\n"
       . "We have received your enquiry and our team will contact you soon.\r\n\r\n"
       . "Best regards,\r\n"
       . "NexInnovators Team\r\n"
       . "nandhudev804@gmail.com | +91 95858 33569";

smtp_send($SMTP_HOST, $SMTP_PORT, $SMTP_USER, $SMTP_PASS,
    $SMTP_USER, 'NexInnovators',
    $email, $name,
    $sub2, $body2);

ob_end_clean();
echo json_encode(['success' => true, 'message' => 'Successfully submitted! We will contact you soon.']);
