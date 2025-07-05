<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../PHPmailer/src/PHPMailer.php';
require_once '../PHPmailer/src/SMTP.php';
require_once '../PHPmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendResetEmail($to, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'zainabmoqbel99893@gmail.com'; // ✅ استبدله ببريدك الحقيقي
        $mail->Password = 'anamoslma';    // ✅ استبدله بكلمة مرور التطبيق من Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your_email@gmail.com', 'AutoParts Support');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset';
        $mail->Body = "Click this link to reset your password: 
            <a href='http://localhost:3000/reset-password?token=$token'>Reset Password</a>";

        $mail->send();
        return ['status' => 'success', 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'Mailer Error: ' . $mail->ErrorInfo];
    }
}

// استلام الإيميل من الواجهة
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? null;

if (!$email) {
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit;
}

// البحث عن المستخدم في قاعدة البيانات
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No user found with this email']);
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['id'];
$token = bin2hex(random_bytes(32));

// تحديث reset_token في الجدول
$stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE id = ?");
$stmt->bind_param("si", $token, $userId);
$stmt->execute();

// إرسال الإيميل
$response = sendResetEmail($email, $token);

// إرسال الاستجابة النهائية
echo json_encode($response);
