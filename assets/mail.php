<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Formularfelder abrufen und bereinigen
    $vorname = strip_tags(trim($_POST["vorname"]));
    $nachname = strip_tags(trim($_POST["nachname"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $message = trim($_POST["message"]);

    // Überprüfen, ob die Daten an den Mailer gesendet wurden
    if (empty($vorname) or empty($nachname) or empty($subject) or empty($message) or !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Setze eine 400 (schlechte Anfrage) Antwort und beende die Ausführung
        http_response_code(400);
        echo "Bitte füllen Sie das Formular vollständig aus und versuchen Sie es erneut.";
        exit;
    }

    // Empfänger-E-Mail-Adresse
    $recipient = "fynn.menk@gmail.com";
    $subject = "Neue Kontaktanfrage: $subject";

    // E-Mail-Inhalt
    $email_content = "Vorname: $vorname\n";
    $email_content .= "Nachname: $nachname\n";
    $email_content .= "E-Mail: $email\n\n";
    $email_content .= "Betreff: $subject\n\n";
    $email_content .= "Nachricht:\n$message\n";

    // E-Mail-Header
    $email_headers = "From: $vorname $nachname <$email>";

    // Versuche die E-Mail zu senden
    if (mail($recipient, $subject, $email_content, $email_headers)) {
        // Setze eine 200 (Erfolg) Antwort
        http_response_code(200);
        echo "Danke! Ihre Nachricht wurde gesendet.";
    } else {
        // Setze eine 500 (interner Serverfehler) Antwort
        http_response_code(500);
        echo "Oops! Etwas ist schief gelaufen und wir konnten Ihre Nachricht nicht senden.";
    }

} else {
    // Nicht eine POST-Anfrage, setze eine 403 (verboten) Antwort
    http_response_code(403);
    echo "Es gab ein Problem mit Ihrer Einsendung, bitte versuchen Sie es erneut.";
}