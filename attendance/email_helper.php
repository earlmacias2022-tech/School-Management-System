<?php

/**
 * PHPMailer Gmail SMTP Email Helper for Attendance Notifications
 *
 * SETUP INSTRUCTIONS:
 * 1. Enable 2-Factor Authentication on your Gmail account
 * 2. Go to https://myaccount.google.com/apppasswords
 * 3. Create a new App Password (select "Mail" and "Other custom name" e.g. "AttendanceSystem")
 * 4. Copy the 16-character password and set it below
 * 5. Change $smtp_password to your actual Gmail app password
 * 6. Change $smtp_from to your sending Gmail address
 *
 * DO NOT use your regular Gmail password — use an App Password only.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

// === SMTP CONFIGURATION ===
// IMPORTANT: Replace these with your actual Gmail app credentials
$smtp_host = 'smtp.gmail.com';
$smtp_port = 465;                  // 465 for SSL, or 587 for TLS
$smtp_username = 'vnhs.attendance.system@gmail.com';      // <-- CHANGE THIS to your Gmail address
$smtp_password = 'mdgz znym bwxm iiyu';       // <-- CHANGE THIS to your 16-char App Password
$smtp_from_name = 'ClassOptima';          // School / system name

/**
 * Extract email addresses from a contact string.
 * Handles formats like:
 *   "John Doe - john@example.com - 09171234567"
 *   "john@example.com"
 *   "Jane: jane.doe@example.com"
 *
 * @param string $contact The raw contact field (e.g. parent_contacts)
 * @return array List of email addresses found
 */
function extract_emails($contact)
{
    if (empty($contact)) return [];

    $emails = [];
    // Match anything that looks like an email
    if (preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $contact, $matches)) {
        $emails = $matches[0];
    }
    return $emails;
}

/**
 * Send an attendance notification email.
 *
 * @param mysqli $conn Database connection
 * @param int $student_id Student record ID
 * @param string $status 'present' or 'absent'
 * @param string $date Date in Y-m-d format
 * @param int $marked_by User ID of the teacher
 * @return array ['student_sent' => bool, 'parent_sent' => bool, 'errors' => string]
 */
function send_attendance_email($conn, $student_id, $status, $date, $marked_by)
{
    global $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_from_name;

    // Fetch student details
    $result = mysqli_query($conn, "
        SELECT s.*, sec.section_name, u.fullname as teacher_name
        FROM students s
        LEFT JOIN sections sec ON s.section_id = sec.sectionID
        LEFT JOIN users u ON u.id = '$marked_by'
        WHERE s.id = $student_id
    ");

    if (!$result || mysqli_num_rows($result) === 0) {
        return ['student_sent' => false, 'parent_sent' => false, 'errors' => 'Student not found'];
    }

    $student = mysqli_fetch_assoc($result);

    $status_label = ($status === 'present') ? 'Present' : 'Absent';
    $status_color = ($status === 'present') ? '#10b981' : '#ef4444';
    $formatted_date = date('F j, Y', strtotime($date));
    $recipients = [];

    // Collect student email
    if (!empty($student['email']) && filter_var($student['email'], FILTER_VALIDATE_EMAIL)) {
        $recipients[$student['email']] = $student['legal_name'];
    }

    // Collect parent emails from parent_contacts
    $parent_emails = extract_emails($student['parent_contacts']);
    foreach ($parent_emails as $email) {
        $recipients[$email] = 'Parent/Guardian';
    }

    if (empty($recipients)) {
        return ['student_sent' => false, 'parent_sent' => false, 'errors' => 'No valid email addresses found'];
    }

    // Build HTML email
    $html_body = build_email_html($student, $status_label, $status_color, $formatted_date);

    // Build plain text fallback
    $text_body = "Attendance Notification - ClassOptima\n";
    $text_body .= "==========================================\n\n";
    $text_body .= "Dear {$student['legal_name']},\n\n";
    $text_body .= "This is to notify you that your attendance for $formatted_date has been marked as: $status_label\n\n";
    $text_body .= "Section: {$student['section_name']}\n";
    $text_body .= "Date: $formatted_date\n";
    $text_body .= "Marked by: {$student['teacher_name']}\n\n";
    $text_body .= "If you have any concerns, please contact the school.\n";
    $text_body .= "\nClassOptima Attendance System\n";

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        $mail->Port = $smtp_port;
        $mail->setFrom($smtp_username, $smtp_from_name);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Attendance Notification: {$student['legal_name']} - $status_label ($formatted_date)";

        $student_sent = false;
        $parent_sent = false;
        $errors = [];

        foreach ($recipients as $email => $name) {
            $mail->addAddress($email, $name);
            $mail->Body = $html_body;
            $mail->AltBody = $text_body;

            try {
                $mail->send();
                if ($name === $student['legal_name']) {
                    $student_sent = true;
                } else {
                    $parent_sent = true;
                }
            } catch (Exception $e) {
                $errors[] = "Failed to send to $email: " . $mail->ErrorInfo;
            }

            // Reset for next recipient
            $mail->clearAddresses();
        }

        return [
            'student_sent' => $student_sent,
            'parent_sent' => $parent_sent,
            'errors' => implode('; ', $errors)
        ];
    } catch (Exception $e) {
        return ['student_sent' => false, 'parent_sent' => false, 'errors' => $mail->ErrorInfo];
    }
}

/**
 * Build the HTML email body for attendance notification.
 */
function build_email_html($student, $status_label, $status_color, $formatted_date)
{
    $status_icon = ($status_label === 'Present')
        ? '<svg style="width:48px;height:48px;color:#10b981" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        : '<svg style="width:48px;height:48px;color:#ef4444" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Notification</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Inter,Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:30px 15px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#2563eb,#06b6d4);padding:30px 40px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;">ClassOptima</h1>
                            <p style="margin:6px 0 0;color:rgba(255,255,255,0.85);font-size:14px;">Attendance Notification</p>
                        </td>
                    </tr>

                    <!-- Status Icon & Label -->
                    <tr>
                        <td align="center" style="padding:30px 40px 10px;">
                            ' . $status_icon . '
                            <h2 style="margin:12px 0 0;font-size:28px;font-weight:700;color:' . $status_color . ';">' . $status_label . '</h2>
                        </td>
                    </tr>

                    <!-- Student Info Card -->
                    <tr>
                        <td style="padding:20px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <p style="margin:0 0 8px;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Student Name</p>
                                        <p style="margin:0;font-size:16px;font-weight:600;color:#1e293b;">' . htmlspecialchars($student['legal_name']) . '</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 20px 16px;">
                                        <p style="margin:0 0 8px;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Section</p>
                                        <p style="margin:0;font-size:16px;font-weight:600;color:#1e293b;">' . htmlspecialchars($student['section_name'] ?? 'N/A') . '</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 20px 16px;">
                                        <p style="margin:0 0 8px;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Date</p>
                                        <p style="margin:0;font-size:16px;font-weight:600;color:#1e293b;">' . $formatted_date . '</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 20px 16px;">
                                        <p style="margin:0 0 8px;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Marked by</p>
                                        <p style="margin:0;font-size:16px;font-weight:600;color:#1e293b;">' . htmlspecialchars($student['teacher_name'] ?? 'Teacher') . '</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 40px 30px;text-align:center;">
                            <p style="margin:0;font-size:13px;color:#94a3b8;">This is an automated notification from ClassOptima Attendance System.<br>If you have concerns, please contact the school directly.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
}
