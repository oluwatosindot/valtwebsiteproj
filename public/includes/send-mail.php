<?php
require_once __DIR__ . '/config.php';

function valtMail($to, $subject, $body) {
    $headers  = 'From: ' . FROM_NAME . ' <' . FROM_EMAIL . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $body, $headers);
}

function emailTemplate($title, $content) {
    return "
    <div style='font-family:Arial,sans-serif;max-width:620px;margin:0 auto;background:#f4f6f8;'>
      <div style='background:#0a2342;padding:32px;text-align:center;'>
        <h1 style='color:#fff;margin:0;font-size:26px;letter-spacing:1px;'>VALT Academy</h1>
        <p style='color:#2a9d8f;margin:6px 0 0;font-size:13px;letter-spacing:2px;text-transform:uppercase;'>Changing The Future Now</p>
      </div>
      <div style='padding:40px 36px;background:#fff;'>
        {$content}
      </div>
      <div style='background:#0a2342;padding:18px;text-align:center;'>
        <p style='color:rgba(255,255,255,0.5);font-size:12px;margin:0;'>&copy; " . date('Y') . " VALT Academy &mdash; <a href=\"https://valt.co.za\" style=\"color:#2a9d8f;text-decoration:none;\">valt.co.za</a></p>
      </div>
    </div>";
}

function sendRegistrationConfirmation($email, $firstName, $studentId) {
    $content = "
      <h2 style='color:#0a2342;margin-top:0;'>Welcome to VALT Academy, {$firstName}! &#127881;</h2>
      <p style='color:#555;line-height:1.8;font-size:15px;'>Your registration has been received successfully. We are thrilled to have you as part of the VALT family!</p>
      <div style='background:#f0faf9;border-left:4px solid #2a9d8f;border-radius:6px;padding:20px 24px;margin:28px 0;'>
        <p style='margin:0 0 6px;color:#888;font-size:13px;text-transform:uppercase;letter-spacing:1px;'>Your Student ID</p>
        <p style='margin:0;color:#0a2342;font-size:28px;font-weight:700;letter-spacing:2px;'>{$studentId}</p>
        <p style='margin:8px 0 0;color:#888;font-size:12px;'>Keep this safe — you will need it when we launch the full learning platform.</p>
      </div>
      <p style='color:#555;line-height:1.8;font-size:15px;'>While you wait, explore our programmes and find out what excites you most:</p>
      <div style='text-align:center;margin:30px 0;'>
        <a href='https://valt.co.za/courses.html' style='background:#2a9d8f;color:#fff;padding:14px 36px;border-radius:6px;text-decoration:none;font-size:15px;font-weight:600;display:inline-block;'>Explore Programmes</a>
      </div>
      <p style='color:#555;line-height:1.8;font-size:15px;'>We look forward to being part of your journey.<br><br>Warm regards,<br><strong style='color:#0a2342;'>The VALT Team</strong></p>
    ";
    return valtMail($email, 'Welcome to VALT Academy — Registration Confirmed!', emailTemplate('Welcome', $content));
}

function sendAdminNotification($student) {
    $content = "
      <h2 style='color:#0a2342;margin-top:0;'>New Student Registration</h2>
      <table style='width:100%;border-collapse:collapse;font-size:14px;'>
        <tr><td style='padding:8px;color:#888;width:40%;'>Student ID</td><td style='padding:8px;color:#0a2342;font-weight:600;'>{$student['student_id']}</td></tr>
        <tr style='background:#f9f9f9;'><td style='padding:8px;color:#888;'>Name</td><td style='padding:8px;'>{$student['first_name']} {$student['last_name']}</td></tr>
        <tr><td style='padding:8px;color:#888;'>Email</td><td style='padding:8px;'>{$student['email']}</td></tr>
        <tr style='background:#f9f9f9;'><td style='padding:8px;color:#888;'>Grade</td><td style='padding:8px;'>Grade {$student['grade']}</td></tr>
        <tr><td style='padding:8px;color:#888;'>WhatsApp</td><td style='padding:8px;'>{$student['whatsapp_number']}</td></tr>
        <tr style='background:#f9f9f9;'><td style='padding:8px;color:#888;'>School</td><td style='padding:8px;'>{$student['school_name']}</td></tr>
        <tr><td style='padding:8px;color:#888;'>Province</td><td style='padding:8px;'>{$student['province']}</td></tr>
        <tr style='background:#f9f9f9;'><td style='padding:8px;color:#888;'>Programme Interest</td><td style='padding:8px;'>{$student['programme_interest']}</td></tr>
      </table>
      <div style='text-align:center;margin:28px 0;'>
        <a href='https://valt.co.za/admin/' style='background:#0a2342;color:#fff;padding:12px 30px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;display:inline-block;'>View Admin Panel</a>
      </div>
    ";
    return valtMail(FROM_EMAIL, 'New Student Registration — ' . $student['first_name'] . ' ' . $student['last_name'], emailTemplate('New Registration', $content));
}
