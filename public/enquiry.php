<?php
// Programme enquiry form handler — VALT Academy

session_start();
header('Content-Type: application/json');

function clean($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

function validEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function jsonResponse($success, $message, $code = 200) {
    http_response_code($code);
    exit(json_encode(['success' => $success, 'message' => $message]));
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.', 405);
}

// Parse JSON body if sent as JSON
$body = json_decode(file_get_contents('php://input'), true);
if ($body) {
    $_POST = array_merge($_POST, $body);
}

// 1. Honeypot
if (!empty($_POST['website'])) {
    jsonResponse(true, 'Enquiry submitted! Check your email for a confirmation.');
}

// 2. Rate limiting — max 3 per hour per session
if (!isset($_SESSION['enquiry_count']) || time() - $_SESSION['enquiry_time'] > 3600) {
    $_SESSION['enquiry_count'] = 0;
    $_SESSION['enquiry_time']  = time();
}
if ($_SESSION['enquiry_count'] >= 3) {
    jsonResponse(false, 'Too many submissions. Please try again in an hour.', 429);
}

// 3. Validate
$name      = clean($_POST['name']      ?? '');
$phone     = clean($_POST['phone']     ?? '');
$email     = clean($_POST['email']     ?? '');
$programme = clean($_POST['programme'] ?? '');

if (!$name || !$email) {
    jsonResponse(false, 'Name and email are required.', 400);
}
if (!validEmail($email)) {
    jsonResponse(false, 'Please enter a valid email address.', 400);
}

// 4. Spam keyword filter
$spamWords = ['casino', 'viagra', 'crypto', 'bitcoin', 'forex', 'loan',
              'click here', 'free money', 'winner', 'prize', 'seo', 'backlink'];
$content = strtolower($name . ' ' . $programme);
foreach ($spamWords as $word) {
    if (strpos($content, $word) !== false) {
        jsonResponse(true, 'Enquiry submitted! Check your email for a confirmation.');
    }
}

// 5. Send emails
$to        = 'info@valt.co.za';
$fromEmail = 'info@valt.co.za';
$fromName  = 'VALT Academy Website';

// Notification to VALT
$notifySubject = "New Programme Enquiry: " . ($programme ?: 'General');
$notifyBody    = "
<h3>New Programme Enquiry</h3>
<p><strong>Name:</strong> {$name}</p>
<p><strong>Phone:</strong> " . ($phone ?: 'N/A') . "</p>
<p><strong>Email:</strong> {$email}</p>
<p><strong>Programme:</strong> " . ($programme ?: 'N/A') . "</p>
";
$notifyHeaders  = "From: {$fromName} <{$fromEmail}>\r\n";
$notifyHeaders .= "Reply-To: {$name} <{$email}>\r\n";
$notifyHeaders .= "MIME-Version: 1.0\r\n";
$notifyHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";

mail($to, $notifySubject, $notifyBody, $notifyHeaders);

// Auto-reply to enquirer
$replySubject = 'VALT Academy — Enquiry Received';
$replyBody    = "
<div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
  <div style='background:#0a2342;padding:30px;text-align:center;'>
    <h1 style='color:#fff;margin:0;'>VALT Academy</h1>
  </div>
  <div style='padding:30px;background:#f8f9fa;'>
    <h2 style='color:#0a2342;'>Hi {$name}!</h2>
    <p style='color:#555;line-height:1.6;'>Thank you for your interest in <strong>" . ($programme ?: 'our programmes') . "</strong>. We've received your enquiry and will be in touch shortly.</p>
    <p style='color:#555;line-height:1.6;'>Kind regards,<br><strong>The VALT Team</strong></p>
  </div>
  <div style='background:#0a2342;padding:15px;text-align:center;'>
    <p style='color:rgba(255,255,255,0.6);font-size:12px;margin:0;'>&copy; VALT Academy. All rights reserved.</p>
  </div>
</div>
";
$replyHeaders  = "From: VALT Academy <{$fromEmail}>\r\n";
$replyHeaders .= "MIME-Version: 1.0\r\n";
$replyHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";

mail($email, $replySubject, $replyBody, $replyHeaders);

$_SESSION['enquiry_count']++;

jsonResponse(true, 'Enquiry submitted! Check your email for a confirmation.');
