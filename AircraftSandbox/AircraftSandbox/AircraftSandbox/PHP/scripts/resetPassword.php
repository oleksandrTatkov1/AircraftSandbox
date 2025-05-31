<?php
use PHP\Utils\FirebasePublisher;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../utils/firebasePublisher.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // якщо PHPMailer встановлено через Composer

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit("Неправильна електронна пошта.");
    }

    // 1. Генеруємо код
    $code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);

    // 2. Надсилаємо листа
    $mail = new PHPMailer(true);
    try {
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // або інший SMTP-сервер
        $mail->SMTPAuth = true;
        $mail->Username = 'anton.palamarchuk@nure.ua';      // ⚠️ заміни
        $mail->Password = 'hmmx ahln ouow oktp';       // ⚠️ заміни
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('anton.palamarchuk@nure.ua', 'Відновлення пароля');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Код для відновлення пароля';
        $mail->Body    = "Ваш код для відновлення пароля: <b>$code</b>";

        $mail->send();
    } catch (Exception $e) {
        exit("Не вдалося надіслати листа. Помилка: {$mail->ErrorInfo}");
    }

    // 3. Зберігаємо код у Firebase
    $firebase = new FirebasePublisher();
    $safeKey = $firebase->sanitizeKey($email);
    $firebase->publish("PasswordResets/$safeKey", [
        'code' => $code,
        'timestamp' => time()
    ]);

    // 4. Зберігаємо email в сесії і перекидаємо
    $_SESSION['reset_email'] = $email;
    header("Location: /AircraftSandbox/AircraftSandbox/AircraftSandbox/AircraftSandbox/passwordReset.html");
    exit;
}
?>
