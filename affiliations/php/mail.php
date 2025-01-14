<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function mailing($conn, $k_data, $t_data){
    // REQUIREMENTS AND INCLUDES FOR DATABASE CONNECTION
    require 'db_connection.php';
    require_once __DIR__ . '/vendor/autoload.php'; // Autoloader einbinden

    // Daten für Mail: 
    $sum = getSum($conn, $k_data[2]);
    $iban = '1337';

    #print_r($k_data);
    #print_r($t_data);

    $mail = new PHPMailer(true);
    try {
        $nachricht = "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        <title>Weihnachtsball</title>
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
                        <p>Hey " . htmlspecialchars($k_data[1], ENT_QUOTES, 'UTF-8') . ",</p>
                        <p>
                            [Hier steht Text von Gordon];<br> 
                            Damit alles glatt geht, komm gern jeden Dienstag und Donnerstag in jeder zweiten Pause zu uns vor die Bibliothek und besorgt dir dein Ticket!<br>
                        </p>
                        <p>Hier nochmal eine kleine Übersicht deiner Reservierung:</p>
                        <table>
                            <thead style='border-left:2px solid black;'>
                                <tr>
                                    <th>Deine, noch zu begleichende, Summe:</th>
                                    <th>" . number_format($sum, 2, ',', '.') . "€</th>
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
                            <tbody>";

                            //Tickets for this Käufer
                            $id = getID($conn, $k_data[2]);
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
                            Wir bieten auch die Möglichkeit einer Überweisung an. Überweise dazu die oben genannte Summe an dieses Konto:
                        </p>
                        <p>
                            <strong>IBAN:</strong> ".$iban."<br>
                            <strong>Name:</strong> Raphael Stark<br>
                            <strong>Verwendungszweck:</strong> \"". str_replace("@", "at", $k_data[2])." Frühlingsball\"
                        </p>
                        <p>Wir wünschen ein geiles Leben und freuen uns auf dich!</p>
                        <p>Mit freundlichen Grüßen,<br>Gordon</p>
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
        $mail->addAddress($k_data[2], $k_data[1]);

        // Nachricht
        $mail->isHTML(true);
        $mail->Subject = 'Ticketbestätigung Winterball';
        $mail->Body    = $nachricht;

        $mail->send();
        log_data_mail($conn, $k_data);
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
        fwrite($file, $time . ': ✅ Mail should be sent to: ' . $k_data[2] . PHP_EOL); // Mail schreiben     
        fwrite($file, 'But check the internal webmail for possible errors!' . PHP_EOL); // Mail schreiben     
        fwrite($file, '---------------------------------------------------' . PHP_EOL); // Mail schreiben     
        #echo "Inhalt wurde erfolgreich in die Datei '$filename' geschrieben.";
        fclose($file); // Datei schließen
    } else {
        #echo "Fehler beim Öffnen der Datei '$filename'.";
    }
}