<?php
include '../../affiliations/php/db_connection.php';
require_once '../../affiliations/php/vendor/autoload.php'; // Autoloader einbinden

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$iban = 'DE61 1605 0000 1102 4637 24';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    if (isset($data['method']) && isset($data['mail'])) {
        $method = $data['method'];
        $email_empfänger = $data['mail'];

        #echo json_encode(["message" => "Trying to send", "mail_type" => $method, "mail" => $email_empfänger]);

        $mail = new PHPMailer(true);

        switch ($method) {
            case 'registration':
                $nachricht = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <title>Ticketreservierung Frühlingsball 2025 MCG</title>
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
                    <p>Hey " . htmlspecialchars(getName($conn, $email_empfänger), ENT_QUOTES, 'UTF-8') . ",</p>
                    <p>
                        Du hast es geschafft und dir deine grandiosen Tickets für den Frühlingsball 2025 gesichert – vielen Dank dafür!<br><br>

                        Hier sind alle wichtigen Infos:<br><br>

                        Datum: 11.04.2025<br>
                        Uhrzeit: Einlass ab 18:45 Uhr, Beginn um 20:00 Uhr, Ende: 01:00 Uhr<br>
                        Adresse: Friedrich-Wolf-Straße 31, Oranienburg<br><br>

                        Die Tickets könnt ihr wie gewohnt phänomenal vor der Bibliothek oder per Überweisung bezahlen.
                        Überweisungen sind ab sofort möglich (Bankverbindung unten), und ab wann ihr die Tickets bar bezahlen könnt, geben wir euch noch rechtzeitig bekannt.<br><br>
                        
                        <strong>Wichtig:</strong> Eure Reservierungen sind nicht unbegrenzt gültig! Unbezahlte Tickets werden am 30.03.2025 um 23:29 automatisch storniert, damit andere eine fancytastische Chance auf Resttickets haben.<br>
                    </p>
                    <p>Hier nochmal eine kleine Übersicht deiner Reservierung:</p>
                    <table>
                        <thead style='border-left:2px solid black;'>
                            <tr>
                                <th>Deine, noch zu begleichende, Summe:</th>
                                <th>" . number_format(getOpen($conn,$email_empfänger), 2, ',', '.') . "€</th>
                            </tr>
                        </thead>
                    </table>
                    <p>Bezüglich der Tickets:</p>
                    <table>
                        <thead>
                            <tr>
                                <th>Vorname</th>
                                <th>Nachname</th>
                                <th>Summe</th>
                            </tr>
                        </thead>
                        <tbody>
                    ";
                                //Tickets for this Käufer
                                $id = getID($conn, $email_empfänger);
                                $KäuferAllTickets = "SELECT email,vorname,nachname,sum FROM tickets WHERE käufer_ID = $id";
                                $stmt = $conn->prepare($KäuferAllTickets);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                // Füge Zeilen für jedes Ticket hinzu
                                while ($row = $result->fetch_assoc()) {
                                    $vorname = htmlspecialchars($row['vorname'], ENT_QUOTES, 'UTF-8');
                                    $nachname = htmlspecialchars($row['nachname'], ENT_QUOTES, 'UTF-8');
                                    $sum = number_format((float)$row['sum'], 2, ',', '.');
                        
                                    $nachricht .= "
                                    <tr>
                                        <td>$vorname</td>
                                        <td>$nachname</td>
                                        <td>" . $sum . "€</td>
                                    </tr>";
                                }
                        
                                $nachricht .= "
                        </tbody>
                    </table>
                    <p>
                        <strong>Wenn du überweisen möchtest:</strong> Überweise dazu die oben genannte Summe an dieses Konto:
                    </p>
                    <p>
                        <strong>IBAN:</strong> ".$iban."<br>
                        <strong>Name:</strong> Raphael Stark<br>
                        <strong>Verwendungszweck:</strong> \"". str_replace("@", "at", $email_empfänger)." Frühlingsball\"
                    </p>
                    <p>Wir freuen uns riesig auf einen crazytastischen Abend mit euch! 💕</p>
                    <p>Beste Grüße,<br>Gordon</p>
                </body>
                </html>
                ";
                break;

            case 'submitation':
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
                    <p>Hey " . htmlspecialchars(getName($conn, $email_empfänger), ENT_QUOTES, 'UTF-8') . ",</p>
                    <p>Deine Kosten in Höhe von<br><br>
                    ". htmlspecialchars(getSum($conn, $email_empfänger)) . "€<br><br>
                    wurden voll und ganz beglichen. Wie episch!<br>
                    Wir werden dir zu einem späteren Zeitpunkt nochmal eine Mail mit deinem finalen Ticket und wichtigen Informationen schicken.<br>
                    Wir haben Bock und freuen uns zusammen mit dir auf den 11.04.2025<br><br>
                    Mit freundlichen Grüßen,<br>Gordon!</p>
                </body>
                </html>
                ";
                break;

            case 'ticket':
                $nachricht = "404";
                break;
            
            default:
                # code...
                break;
        }

        try {

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
            $mail->addAddress($email_empfänger, $email_empfänger);

            // Nachricht
            $mail->isHTML(true);
            $mail->Subject = 'Fancytastische Buchungsbestätigung: Frühlingsball 2025';
            $mail->Body    = $nachricht;

            $mail->send();
            #sendJsonResponse(['message' => 'E-Mail erfolgreich gesendet', 'sum' => number_format($sum, 2)]);
        } catch (Exception $e) {
            #logError("PHPMailer Fehler: " . $mail->ErrorInfo);
            #sendJsonResponse(['error' => 'E-Mail konnte nicht gesendet werden']);
        }
    }
}

function getName($conn, $email_empfänger){
    $sql = "SELECT vorname FROM käufer WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email_empfänger);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['vorname'];
}

function getOpen($conn,$email){
    $sumQuery = "SELECT open AS SUM FROM käufer WHERE email = ?";
    $stmt = $conn->prepare($sumQuery);
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['SUM'];
}

function getSum($conn,$email){
    $sumQuery = "SELECT sum AS SUM FROM käufer WHERE email = ?";
    $stmt = $conn->prepare($sumQuery);
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['SUM'];
}

function getID($conn, $email){
    $idQuery = "SELECT ID AS ID FROM käufer WHERE email = ?";
    $stmt = $conn->prepare($idQuery);
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $id = $row['ID'];
    return $id;
}