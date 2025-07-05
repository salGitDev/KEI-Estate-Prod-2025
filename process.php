<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize mailer
$mail = new PHPMailer(true);

// Get form inputs
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$honeypot = trim($_POST['company'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'];

// 🛡️ Stop if honeypot field is filled (bot detected)
if (!empty($honeypot)) {
    file_put_contents('spam_honeypot.log', "Bot caught: $ip | $name <$email>\n", FILE_APPEND);
    exit;
}

// 🛡️ Spam filter function
function isSuspicious($name, $email, $message) {
    $msgLower = strtolower($message);

    $blacklist = [
        '/claim\s+your\s+\$\d{1,3}(,\d{3})*/i',
        '/prize/i',
        '/\bbitcoin\b/i',
        '/https?:\/\/[^\s]+/i',
        '/<script\b/i'
    ];

    $disposableDomains = ['mailinator.com', 'tempmail.com', '10minutemail.com', 'guerrillamail.com'];
    $emailDomain = strtolower(substr(strrchr($email, "@"), 1));

    foreach ($blacklist as $pattern) {
        if (preg_match($pattern, $message)) {
            return true;
        }
    }

    if (in_array($emailDomain, $disposableDomains)) {
        return true;
    }

    if (preg_match('/^[a-zA-Z]{10,}$/', $name)) {
        return true;
    }

    if (strlen($message) < 10) {
        return true;
    }

    // Allow normal "urgent" use but flag only if paired with spammy terms
    if (preg_match('/urgent|act fast/i', $message)) {
        if (
            preg_match('/(prize|claim|winner|offer)/i', $message) ||
            preg_match('/https?:\/\/[^\s]+/i', $message)
        ) {
            return true;
        }
    }

    return false;
}

// Log access
file_put_contents('form_access.log', "$ip - $name <$email>\n", FILE_APPEND);

$rateLimitFile = 'ratelimit.log';
$rateLimitDuration = 60; // 60 seconds per submission

$entries = file_exists($rateLimitFile) ? file($rateLimitFile, FILE_IGNORE_NEW_LINES) : [];
$recent = array_filter($entries, fn($line) => strpos($line, $ip) !== false && time() - (int)explode('|', $line)[1] < $rateLimitDuration);

if (count($recent) > 0) {
    echo "Too many submissions. Please wait a moment before trying again.";
    exit;
}

// Log the attempt
file_put_contents($rateLimitFile, "$ip|" . time() . "\n", FILE_APPEND);


// 🛑 Stop and log if spam
if (isSuspicious($name, $email, $message)) {
    echo "Suspicious content detected. Your message was not sent.";
    file_put_contents('suspicious.log', "IP: $ip | Name: $name | Email: $email | Message: $message\n", FILE_APPEND);
    exit;
}

// ✅ Send email
try {
    $mail->isSMTP();
    $mail->Host       = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAIL_USERNAME'];
    $mail->Password   = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
    $mail->Port       = $_ENV['MAIL_PORT'];

    $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
    $mail->addAddress($_ENV['MAIL_TO']);

    $mail->Subject = 'New Message from ' . $name;
    $mail->Body    = "Name: $name\nEmail: $email\nMessage:\n$message";

    $mail->send();
    echo "Message sent successfully!<br><br>Thank you $name.<br>KEI Estate Investment will get back to you shortly.";
} catch (Exception $e) {
    echo "Message could not be sent. Error: {$mail->ErrorInfo}";
}
