<?php
// PHPMailer লাইব্রেরি ফাইলগুলো ইনক্লুড করা (যদি কম্পোজার ব্যবহার করেন)
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendNotificationEmail($to_email, $subject, $message_body) {
    $mail = new PHPMailer(true);

    try {
        // সার্ভার সেটিংস (এখানে আপনার জিমেইল বা অফিশিয়াল ইমেইল বসাবেন)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';                     // জিমেইল SMTP সার্ভার
        $mail->SMTPAuth   = true;                                   // SMTP অথেন্টিকেশন চালু
        $mail->Username   = 'your_email@gmail.com';                 // আপনার জিমেইল অ্যাড্রেস
        $mail->Password   = 'your_gmail_app_password';              // জিমেইলের App Password (নরমাল পাসওয়ার্ড নয়)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // সিকিউরিটি এনক্রিপশন
        $mail->Port       = 587;                                    // পোর্ট নম্বর

        // প্রেরক ও প্রাপক
        $mail->setFrom('your_email@gmail.com', 'SkPayZone Support');
        $mail->addAddress($to_email);                               // যার কাছে ইমেইল যাবে

        // ইমেইলের বিষয়বস্তু
        $mail->isHTML(true);                                        // HTML ফরম্যাট চালু
        $mail->Subject = $subject;
        $mail->Body    = $message_body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false; // ইমেইল পাঠানো না গেলে ফলস রিটার্ন করবে
    }
}
?>
