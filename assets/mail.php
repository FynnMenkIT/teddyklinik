<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require '../vendor/autoload.php';

// Laden der Umgebungsvariablen
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Formularfelder abrufen und bereinigen
    $vorname = isset($_POST["vorname"]) ? strip_tags(trim($_POST["vorname"])) : '';
    $nachname = isset($_POST["nachname"]) ? strip_tags(trim($_POST["nachname"])) : '';
    $email = isset($_POST["email"]) ? filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST["phone"]) ? strip_tags(trim($_POST["phone"])) : '';
    $subject = isset($_POST["subject"]) ? strip_tags(trim($_POST["subject"])) : '';
    $message = isset($_POST["message"]) ? trim($_POST["message"]) : '';

    // Überprüfen, ob die Daten an den Mailer gesendet wurden
    if (empty($vorname) or empty($nachname) or empty($subject) or empty($message) or !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Setze eine 400 (schlechte Anfrage) Antwort und beende die Ausführung
        http_response_code(400);
        echo "Bitte füllen Sie das Formular vollständig aus und versuchen Sie es erneut.";
        exit;
    }

    // PHPMailer-Instanz erstellen
    $mail = new PHPMailer(true);

    try {
        // Server-Einstellungen
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];

        // Empfänger
        $mail->setFrom($_POST["email"], "$vorname $nachname");
        $mail->addAddress($_ENV['SMTP_USERNAME']); // Empfänger-E-Mail-Adresse

        // Inhalt
        $mail->isHTML(false);
        $mail->Subject = "Neue Kontaktanfrage: $subject";
        $mail->Body = "Vorname: $vorname\nNachname: $nachname\nE-Mail: $email\nTelefon: $phone\n\nBetreff: $subject\n\nNachricht:\n$message\n";

        $mail->send();
        http_response_code(200);
        echo "Danke! Ihre Nachricht wurde gesendet.";
    } catch (Exception $e) {
        http_response_code(500);
        echo "Oops! Etwas ist schief gelaufen und wir konnten Ihre Nachricht nicht senden. Mailer Error: {$mail->ErrorInfo}";
    }

} else {
    // Nicht eine POST-Anfrage, setze eine 403 (verboten) Antwort
    http_response_code(403);
    echo "Es gab ein Problem mit Ihrer Einsendung, bitte versuchen Sie es erneut.";
}