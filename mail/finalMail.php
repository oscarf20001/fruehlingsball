<?php
include '../affiliations/php/db_connection.php';
require __DIR__ . '/../affiliations/php/vendor/autoload.php';
// Pfad zum Autoloader von Composer einbinden
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// .env-Datei laden
$dotenv = Dotenv::createImmutable(__DIR__ . '/../affiliations/php');
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rohdaten aus dem Body der Anfrage lesen
    $input = file_get_contents('php://input');
    
    // JSON-Daten dekodieren
    $data = json_decode($input, true);
    
    // Überprüfen, ob die E-Mail vorhanden ist
    if (isset($data['email'])) {
        $email = $data['email'];
        email($conn, $email, $mailHost, $mailPassword, $mailPort, $mailUsername);
    } else {
        // Wenn die E-Mail nicht gesetzt ist
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Email fehlt']);
    }
}

function email($conn, $email_receipent, $mailHost, $mailPassword, $mailPort, $mailUsername){
    // E-Mail erstellen und senden
    $mail = new PHPMailer(true);
    $addedValue = 2.50;

    try {
        $nachricht = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Frühlingsball</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        padding: 8px;
                        text-align: left;
                        border: 1px solid #ddd;
                    }
                    th {
                        background-color: #f2f2f2;
                    }
                    p {
                        margin: 16px 0;
                    }
                </style>
            </head>
            <body>
                <p>Hey " . htmlspecialchars(vorname($conn, $email_receipent), ENT_QUOTES, 'UTF-8') . ",</p>
                <p>Deine Kosten in Höhe von<br><br>
                ". htmlspecialchars(sum($conn, $email_receipent)) . "€<br><br>
                wurden voll und ganz beglichen. Wie episch!<br>
                Wir werden dir zu einem späteren Zeitpunkt nochmal eine Mail mit deinem finalen Ticket und wichtigen Informationen schicken.<br>
                Wir haben Bock ud freuen uns zusammen mit dir auf den [Datum]<br><br>
                Mit freundlichen Grüßen,<br>Gordon!</p>
            </body>
            </html>
        ";

        // SMTP-Konfiguration
        $mail->isSMTP();
        $mail->Host       = $mailHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailUsername;
        $mail->Password   = $mailPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $mailPort;
        $mail->CharSet    = 'UTF-8';

        // Empfänger
        $mail->setFrom($mailUsername, 'Marie-Curie Gymnasium');
        $mail->addReplyTo('streiosc@curiegym.de', 'Oscar');
        $mail->addAddress($email_receipent, vorname($conn, $email_receipent));

        // Nachricht
        $mail->isHTML(true);
        $mail->Subject = 'Ticketbestätigung Frühlingsball';
        $mail->Body    = $nachricht;

        $mail->send();
        sendJsonResponse(['message' => 'E-Mail erfolgreich gesendet']);

    } catch (Exception $e) {
        #logError("PHPMailer Fehler: " . $mail->ErrorInfo);
        sendJsonResponse(['error' => 'E-Mail konnte nicht gesendet werden']);
    }
}

function vorname($conn, $email){
    $sqlGetVorname = "SELECT vorname FROM käufer WHERE email = ?";
    $stmt = $conn->prepare($sqlGetVorname);
    $stmt->bind_param('s', $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $vorname = $result->fetch_assoc();

    $stmt->close();
    
    return $vorname['vorname'];
}

function sum($conn, $email){
    $sqlGetSum = "SELECT sum FROM käufer WHERE email = ?";
    $stmt = $conn->prepare($sqlGetSum);
    $stmt->bind_param('s', $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $sum = $result->fetch_assoc();

    $stmt->close();

    return $sum['sum'];
}

function sendJsonResponse(array $response){
    header('Content-Type: application/json');
    echo json_encode($response);
}