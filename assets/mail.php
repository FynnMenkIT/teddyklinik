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

    // Spam-Erkennung: Honeypot-Feld, Ausfüllzeit, Link-Spam und Wegwerf-Mail-Domains.
    // Bots bekommen bewusst dieselbe Erfolgsmeldung wie echte Absender, damit sie nicht
    // merken, dass sie erkannt wurden und sich entsprechend anpassen.
    $honeypot = isset($_POST['website']) ? trim($_POST['website']) : '';
    $elapsedMs = isset($_POST['elapsed']) ? (int) $_POST['elapsed'] : 0;
    $linkCount = substr_count(strtolower($message), 'http://') + substr_count(strtolower($message), 'https://');
    $emailDomain = strtolower(substr(strrchr($email, '@'), 1));
    $disposableDomains = file(__DIR__ . '/disposable-email-domains.conf', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $isSpam = !empty($honeypot)
        || ($elapsedMs > 0 && $elapsedMs < 2000)
        || $linkCount >= 2
        || in_array($emailDomain, $disposableDomains, true);

    if ($isSpam) {
        http_response_code(200);
        echo "Danke! Ihre Nachricht wurde gesendet.";
        exit;
    }

    // PHPMailer-Instanz erstellen
    $mail = new PHPMailer(true);

    try {
        // Server-Einstellungen
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = filter_var($_ENV['SMTP_AUTH'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $mail->Username = $_ENV['SMTP_USERNAME'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];

        // Empfänger
        $mail->setFrom($_POST["email"], "$vorname $nachname");
        $mail->addAddress($_ENV['EMAIL']); // Empfänger-E-Mail-Adresse

        // Inhalt
        $mail->isHTML(false);
        $mail->Subject = "Neue Kontaktanfrage: $subject";
        $mail->Body = "Vorname: $vorname\nNachname: $nachname\nE-Mail: $email\nTelefon: $phone\n\nBetreff: $subject\n\nNachricht:\n$message\n";

        $mail->send();
        http_response_code(200);
        echo "Danke! Ihre Nachricht wurde gesendet.";
    } catch (Exception $e) {
        http_response_code(500);
        echo "Oops! Etwas ist schief gelaufen und wir konnten Ihre Nachricht nicht senden. Bitte versuchen Sie es später erneut.";
    }

} else {
    // Nicht eine POST-Anfrage, setze eine 403 (verboten) Antwort
    http_response_code(403);
    echo "Es gab ein Problem mit Ihrer Einsendung, bitte versuchen Sie es erneut.";
}