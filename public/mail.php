<?php
// Contact form handler — VALT Academy

session_start();

function clean($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

function validEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// 1. Honeypot — bots fill hidden fields, humans don't
if (!empty($_POST['website'])) {
    exit('Message sent successfully! Check your email for a confirmation.');
}

// 2. Rate limiting — max 3 submissions per hour per session
if (!isset($_SESSION['contact_count']) || time() - $_SESSION['contact_time'] > 3600) {
    $_SESSION['contact_count'] = 0;
    $_SESSION['contact_time']  = time();
}
if ($_SESSION['contact_count'] >= 3) {
    http_response_code(429);
    exit('Too many submissions. Please try again in an hour.');
}

// 3. Validate required fields
$name    = clean($_POST['name']    ?? '');
$email   = clean($_POST['email']   ?? '');
$subject = clean($_POST['subject'] ?? '');
$message = clean($_POST['message'] ?? '');

if (!$name || !$email || !$message) {
    http_response_code(400);
    exit('Please fill in all required fields.');
}
if (!validEmail($email)) {
    http_response_code(400);
    exit('Please enter a valid email address.');
}

// 4. Spam keyword filter
$spamWords = ['casino', 'viagra', 'crypto', 'bitcoin', 'forex', 'loan', 'click here',
              'free money', 'winner', 'prize', 'seo', 'backlink', 'buy now', 'urgent'];
$content = strtolower($name . ' ' . $subject . ' ' . $message);
foreach ($spamWords as $word) {
    if (strpos($content, $word) !== false) {
        exit('Message sent successfully! Check your email for a confirmation.');
    }
}

// 5. Send emails
$to        = 'info@valt.co.za';
$fromEmail = 'info@valt.co.za';
$fromName  = 'VALT Academy Website';

// Notification to VALT
$notifySubject = "New Contact: " . ($subject ?: 'No Subject');
$notifyBody    = "
<h3>New Contact Form Submission</h3>
<p><strong>Name:</strong> {$name}</p>
<p><strong>Email:</strong> {$email}</p>
<p><strong>Subject:</strong>" . ($subject ?: 'N/A') . "</p>
<p><strong>Message:</strong></p>
<p>{$message}</p>
";
$notifyHeaders  = "From: {$fromName} <{$fromEmail}>\r\n";
$notifyHeaders .= "Reply-To: {$name} <{$email}>\r\n";
$notifyHeaders .= "MIME-Version: 1.0\r\n";
$notifyHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";

mail($to, $notifySubject, $notifyBody, $notifyHeaders);

// Auto-reply to sender
$replySubject = 'Thank you for contacting VALT Academy';
$replyBody    = "
<div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
  <div style='background:#0a2342;padding:30px;text-align:center;'>
    <h1 style='color:#fff;margin:0;'>VALT Academy</h1>
  </div>
  <div style='padding:30px;background:#f8f9fa;'>
    <h2 style='color:#0a2342;'>Thank you, {$name}!</h2>
    <p style='color:#555;line-height:1.6;'>We have received your message and will get back to you as soon as possible.</p>
    <div style='background:#fff;padding:20px;border-radius:8px;border-left:4px solid #2a9d8f;'>
      <p><strong>Subject:</strong> " . ($subject ?: 'N/A') . "</p>
      <p><strong>Message:</strong> {$message}</p>
    </div>
    <p style='color:#555;line-height:1.6;margin-top:20px;'>Kind regards,<br><strong>The VALT Team</strong></p>
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

// Increment rate limit counter
$_SESSION['contact_count']++;

exit('Message sent successfully! Check your email for a confirmation.');
