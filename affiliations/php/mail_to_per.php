<?php
#require '../../affiliations/php/db_connection.php';
require '../../affiliations/php/db_connection_winterball.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
# Select every Email, that does not contain numbers in their class fields. Specified by the field containing an dash (-).
# Write those mails into an array
# Send a mail to every mail in this array
# Log every sending process into a log file

$mails = [];
$i = 0;

$selectMail = "SELECT * FROM käufer WHERE (klasse LIKE '%-%' OR klasse LIKE '%13/0%') AND id != 246 AND id != 247;";
$stmt = $conn->prepare($selectMail);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $currentMail = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
    $currentPreName = htmlspecialchars($row['vorname'], ENT_QUOTES, 'UTF-8');
    $currentName = htmlspecialchars($row['nachname'], ENT_QUOTES, 'UTF-8');
    $mails[] = [
        'id' => $i,
        'mail' => $currentMail,
        'vorname' => $currentPreName,
        'nachname' => $currentName
    ];
    #$mails = [[1,'streiosc@curiegym.de', 'Oscar', 'Streich'],[2,'starkrap@curiegym.de','Raphael','Stark']];
    #mailing($conn, $mails, $i);
    $i++;
}

print_r($mails);


function mailing($conn, $k_data, $i){
    // REQUIREMENTS AND INCLUDES FOR DATABASE CONNECTION
    require 'db_connection.php';
    require_once __DIR__ . '/vendor/autoload.php'; // Autoloader einbinden

    // Daten für Mail: 
    #$sum = getSum($conn, $k_data[2]);
    $iban = 'DE61 1605 0000 1102 4637 24';

    #print_r($k_data);
    #print_r($t_data);

    $mail = new PHPMailer(true);
    try {
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
                        <h3>Hey " . htmlspecialchars($k_data[$i]['vorname'], ENT_QUOTES, 'UTF-8') . ",</h3>
                        <p>Am 11.04.2025 findet im Kuluthaus Lehnitz der Frühlingsball organisiert vom 12er Jahrgang des Marie Curie Gymnasiums statt. Erleben sie einen crazytastischen Abend und unterstützen Sie gleicheztiig die Abikasse!!!<br></p>

                        <p>Wir laden Sie hiermit herzlich ein sich unter folgenden Link ein Ticket zu reservieren:
                        <a href='https://www.curiegymnasium.de'>www.curiegymnasium.de</a><br></p>

                        <p><strong>Wichtige Daten:</strong><br>
                        Datum: 11.04.2024<br>
                        Uhrzeit: Einlass ab 18:45, Beginn: 20:00<br>
                        Preis: 12€ (Schüler, ehemalig. Schüler, Lehrer), 15€ (Externe)</p>

                        <p>Die Reservierungen sind bis zum 30.03.2025, 23:59 Uhr gültig und werden danach ungültig.</p>

                        <p>Wir freuen uns riesig auf einen crazytastischen Abend mit euch! 💕</p>
                        <p>Beste Grüße,<br>Gordon</p>
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
        $mail->addAddress($k_data[$i]['mail'], $k_data[$i]['vorname']);

        // Nachricht
        $mail->isHTML(true);
        $mail->Subject = '!ACHTUNG! tolle Nachricht: DER FRÜHLINGSBALL 2025';
        $mail->Body    = $nachricht;

        $mail->send();
        #log_data_mail($conn, $k_data);
        #sendJsonResponse(['message' => 'E-Mail erfolgreich gesendet', 'sum' => number_format($sum, 2)]);
    } catch (Exception $e) {
        #logError("PHPMailer Fehler: " . $mail->ErrorInfo);
        #sendJsonResponse(['error' => 'E-Mail konnte nicht gesendet werden']);
    }
}

function getSum($conn,$email){
    $sumQuery = "SELECT open AS SUM FROM käufer WHERE email = ?";
    $stmt = $conn->prepare($sumQuery);
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $sum = $row['SUM'];
    return $sum;
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

function log_data_mail($conn, $k_data){
    #echo getcwd();
    $filename = 'fruelingsball.log';
    // Überprüfen, ob die Datei existiert
    if (!file_exists($filename)) {
        // Datei erstellen
        $file = fopen($filename, 'w'); // 'w' erstellt die Datei, falls sie nicht existiert
        if ($file) {
            fclose($file); // Schließt die neu erstellte Datei
            #echo "Datei '$filename' wurde erstellt.\n";
        } else {
            die("Fehler beim Erstellen der Datei '$filename'.");
        }
    }

    // Datei öffnen (zum Schreiben oder Anhängen)
    $file = fopen($filename, 'a'); // 'a' hängt den Inhalt an

    #time
    $microtime = microtime(true);
    $milliseconds = sprintf('%03d', ($microtime - floor($microtime)) * 1000);
    $time = date('Y:m:d / H:i:s') . ':' . $milliseconds;

    if ($file) {
        fwrite($file, $time . ': ✅ Mail should be sent to: ' . $k_data[1] . PHP_EOL); // Mail schreiben     
        fwrite($file, 'But check the internal webmail for possible errors!' . PHP_EOL); // Mail schreiben     
        fwrite($file, '---------------------------------------------------' . PHP_EOL); // Mail schreiben     
        #echo "Inhalt wurde erfolgreich in die Datei '$filename' geschrieben.";
        fclose($file); // Datei schließen
    } else {
        #echo "Fehler beim Öffnen der Datei '$filename'.";
    }
}

?>