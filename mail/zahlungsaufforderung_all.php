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

//WRITE ALL MAILS INTO AN ARRAY AND SAFE THIS ARRAY IN AN EXTERNAL FILE

//ALLE KÄUFER MAILS GÖNNEEEENN !!!!!!!!!!!! LIMIT STATEMENT ENTFERNEN, WENN IN PRODUCTION !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
#$sqlGetAllMails = "SELECT email, vorname, nachname, open FROM käufer WHERE email LIKE '%@gmail.com' AND ID >= 86 LIMIT 0";
$sqlGetAllMails = "SELECT email, vorname, nachname, open FROM käufer WHERE open > 0";
$stmt = $conn->prepare($sqlGetAllMails);
$stmt->execute();
$result = $stmt->get_result();
$allMails = array();
$iban = "DE 1605 0000 1102 4637 24";

function writeToLog($logHandle, $message) {
    fwrite($logHandle, "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL);
}

// Öffne oder erstelle die Logdatei
$logFile = __DIR__ . '/email_log_käufer.txt';
$logHandle = fopen($logFile, 'a');

if (!$logHandle) {
    die("Fehler beim Öffnen der Logdatei.");
}

while ($row = $result->fetch_assoc()) {
    $allMails[] = [
        'email' => $row['email'],
        'vorname' => $row['vorname'],
        'nachname' => $row['nachname'],
        'sum' => $row['open']
    ];
}

for ($i=0; $i < count($allMails); $i++) {
    //ID DIESER EINEN EMAIL AUFRUFEN, MIT DER DANN ALLE TICKETS GEFUNDEN WERDEN KÖNNEN, DIE AUF DIESE MAIL GEBUCHT WURDEN
    $sqlGetId = "SELECT ID FROM käufer WHERE email = '{$allMails[$i]['email']}'";
    $stmt = $conn->prepare($sqlGetId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $id = $row['ID'];

    //DATEN KÄUFER SPEICHERN = VORNAME, EMAIL (ZUM SENDEN), KOSTEN
    $nameKäufer = $allMails[$i]['vorname'];
    $emailKäufer = $allMails[$i]['email'];
    $sum = $allMails[$i]['sum'];

    //EMAIL VERSAND VORBEREITEN
    if ($emailKäufer && $sum && $nameKäufer) {
        // Nachricht und E-Mail-Inhalt erstellen
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
                        <p>Hey " . htmlspecialchars(getName($conn, $emailKäufer), ENT_QUOTES, 'UTF-8') . ",</p>
                        <p>in der vorherigen Email ist eine falsche IBAN angegeben. Bitte folge nun den Anweisungen in dieser Email. Falls du schon überwiesen hast, antworte bitte sofort auf diese Email und/oder melde dich bei 0175 4191084 (Oscar) oder 0176 60310274 (Raphael). Falls du jetzt eine Überweisung tätigen möchtest: Überweise bitte an die unten korrigierte IBAN (Endziffer: 24)</p>
                        <p>Wir bitten dich, deine Kosten für den Frühlingsball des MCG 2025 in Höhe von:<br><br>
                        <strong>". htmlspecialchars(getSum($conn, $emailKäufer)) . "€</strong><br><br>

                        zu begleichen. Da wir das Geld brauchen, sind eure Tickets ab sofort nicht mehr vollständig reserviert und es geht darum wer zuerst bezahlt. <br>Jeder kann nun wieder ein Ticket kaufen und wenn genug Tickets bezahlt wurden ist der Verkauf geschlossen und die restlichen Tickets verfallen - ob rerserviert oder nicht… <br><br>Wir würden es dir sehr hoch anrechnen, wenn du genannten Betrag möglichst zeitnahe, allerdings jedoch bis 30.03 an uns zahlst. Dafür hast du zwei Möglichkeiten:<br><br>

                        1. Du kannst uns das Geld jeweils Dienstag und Donnerstag in der zweiten Pause (ausgenommen 11.03: da bitte in der dritten Pause aufgrund einer Klausur der K12) vor der Bibliothek in Bar geben (eignet sich nur für Schüler und Lehrer des MCGs)<br>
                        2. Außerdem kannst du das Geld auch auf folgendes Konto überweisen. Wenn du dich für diese Methode entscheidest, achte bitte darauf, dass das Geld bis zum 30.03 eingegangen sein muss und du eventuell mit einer Zustellungsdauer von bis zu drei Tagen rechnen solltest:<br><br>
                        
                        <strong>IBAN:</strong> ".$iban." (korrigiert am 10.03 um 23:18)<br>
                        <strong>Name:</strong> Raphael Stark<br>
                        <strong>Verwendungszweck:</strong> \"". str_replace("@", "at", $emailKäufer)." Frühlingsball\"<br><br>

                        Wir freuen uns über deine Anmeldung und würden dir daher sehr verbunden sein, wenn du deine offene Summe möglichst fancy & schnell begleichst.<br><br>

                        Mit allerliebsten Grüßen,<br>
                        Gordon!</p>
                    </body>
                </html>
                ";

        try {
            $mail = new PHPMailer(true);
            
            // SMTP-Konfiguration
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['MAIL_PORT'];
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            // Absender und Empfänger
            $mail->setFrom($_ENV['MAIL_USERNAME'], 'Marie-Curie Gymnasium');
            $mail->addReplyTo('streiosc@curiegym.de', 'Oscar');
            $mail->addAddress($emailKäufer, $nameKäufer);

            // E-Mail-Inhalt
            $mail->isHTML(true);
            $mail->Body = $nachricht;
            $mail->Subject = 'ACHTUNG: falsche IBAN in den Mails vom 10.03 zwischen 21:44 - 21:56 Uhr';
            $mail->AltBody = 'Dies ist der Klartext-Inhalt der E-Mail.';

            // E-Mail senden
            // E-Mail senden und loggen
            if ($mail->send()) {
                writeToLog($logHandle, "ERFOLG: E-Mail an {$emailKäufer} über {$sum}€ gesendet.");
            } else {
                writeToLog($logHandle, "FEHLER: E-Mail an {$emailKäufer} über {$sum}€ nicht gesendet. Fehler: " . $mail->ErrorInfo);
            }

            // Empfänger und Anhänge leeren
            $mail->clearAddresses();
            $mail->clearAttachments();
            sleep(1);
        } catch (Exception $e) {
            writeToLog($logHandle, "FEHLER: E-Mail an {$emailKäufer} über {$sum}€ nicht gesendet. Fehler: {$mail->ErrorInfo}");
        }
    }
}

fclose($logHandle);

// Verbindung schließen
$conn->close();

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