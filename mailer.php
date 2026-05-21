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
$SMTP_USER = 'nandhudev804@gmail.com';
$SMTP_PASS = 'mpbxzlqahzjhzxfu';
$OWNER     = 'nandhudev804@gmail.com';

// ── Input ─────────────────────────────────────────────────────────────────────
$name    = htmlspecialchars(strip_tags(trim(isset($_POST['name'])    ? $_POST['name']    : '')));
$phone   = htmlspecialchars(strip_tags(trim(isset($_POST['phone'])   ? $_POST['phone']   : '')));
$email   = filter_var(trim(isset($_POST['email']) ? $_POST['email'] : ''), FILTER_SANITIZE_EMAIL);
$message = htmlspecialchars(strip_tags(trim(isset($_POST['message']) ? $_POST['message'] : '')));
$page    = htmlspecialchars(strip_tags(trim(isset($_POST['page'])    ? $_POST['page']    : 'Website')));

if (empty($name) || empty($phone) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Name, phone and a valid email are required.']);
    exit;
}

// ── SMTP via SSL port 465 (more compatible than STARTTLS 587) ─────────────────
function smtp_send($user, $pass, $to, $toName, $subject, $body) {
    $ctx = stream_context_create([
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ]
    ]);

    $sock = stream_socket_client('ssl://smtp.gmail.com:465', $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
    if (!$sock) return "Connection failed: $errstr ($errno)";

    function smtp_get($sock) {
        $r = '';
        while ($line = fgets($sock, 512)) {
            $r .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $r;
    }
    function smtp_put($sock, $cmd) {
        fwrite($sock, $cmd . "\r\n");
        return smtp_get($sock);
    }

    $r = smtp_get($sock);
    if ((int)$r !== 220 && strpos($r, '220') === false) { fclose($sock); return "No greeting: $r"; }

    $r = smtp_put($sock, "EHLO localhost");
    if (strpos($r, '250') === false) { fclose($sock); return "EHLO failed: $r"; }

    $r = smtp_put($sock, "AUTH LOGIN");
    if (strpos($r, '334') === false) { fclose($sock); return "AUTH failed: $r"; }

    $r = smtp_put($sock, base64_encode($user));
    if (strpos($r, '334') === false) { fclose($sock); return "User failed: $r"; }

    $r = smtp_put($sock, base64_encode($pass));
    if (strpos($r, '235') === false) { fclose($sock); return "Pass failed: $r"; }

    $r = smtp_put($sock, "MAIL FROM:<$user>");
    if (strpos($r, '250') === false) { fclose($sock); return "MAIL FROM failed: $r"; }

    $r = smtp_put($sock, "RCPT TO:<$to>");
    if (strpos($r, '250') === false) { fclose($sock); return "RCPT TO failed: $r"; }

    $r = smtp_put($sock, "DATA");
    if (strpos($r, '354') === false) { fclose($sock); return "DATA failed: $r"; }

    $msg = "From: NexInnovators <$user>\r\n"
         . "To: $toName <$to>\r\n"
         . "Subject: $subject\r\n"
         . "MIME-Version: 1.0\r\n"
         . "Content-Type: text/plain; charset=UTF-8\r\n"
         . "\r\n"
         . $body . "\r\n.";

    $r = smtp_put($sock, $msg);
    if (strpos($r, '250') === false) { fclose($sock); return "MSG failed: $r"; }

    smtp_put($sock, "QUIT");
    fclose($sock);
    return true;
}

// ── Send notification to you ───────────────────────────────────────────────────
$r1 = smtp_send($SMTP_USER, $SMTP_PASS,
    $OWNER, 'Nandha Kumar',
    "New Enquiry from NexInnovators ($page)",
    "Name: $name\r\nPhone: $phone\r\nEmail: $email\r\n\r\nMessage:\r\n$message"
);

// ── Send auto-reply to client ─────────────────────────────────────────────────
$r2 = smtp_send($SMTP_USER, $SMTP_PASS,
    $email, $name,
    "Thank you for contacting NexInnovators!",
    "Hi $name,\r\n\r\nThank you for reaching out!\r\n\r\nOur team will contact you soon.\r\n\r\nBest regards,\r\nNexInnovators Team\r\nnandhudev804@gmail.com | +91 95858 33569"
);

ob_end_clean();

if ($r1 === true) {
    echo json_encode(['success' => true, 'message' => 'Successfully submitted! We will contact you soon.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $r1]);
}
