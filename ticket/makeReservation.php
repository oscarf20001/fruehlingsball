<?php

// Setzt den Content-Type-Header auf JSON
header('Content-Type: application/json; charset=utf-8');
// Optionale Header für CORS (bei Bedarf, z. B. wenn die Anfrage von einer anderen Domain kommt)
header('Access-Control-Allow-Origin: *'); // Erlaubt alle Ursprünge
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Zulässige HTTP-Methoden

// REQUIREMENTS AND INCLUDES FOR DATABASE CONNECTION
require '../affiliations/php/mail.php';
require '../affiliations/php/check.php';
require '../affiliations/php/db_connection.php';

// CHECKS - IS OPEN?

# Funktion check kommt aus check.php
if(!checks('Einlass', $conn)){
    die("Ticketshop geschlossen. Kein Ticketkauf möglich!");
}

#print_r($_POST);

$kaeufer_nachname = null;
$kaeufer_vorname = null;
$kaeufer_email = null;
$kaeufer_telNumber = null;
$kaeufer_age = null;
$kaeufer_klasse = null;
$kaeufer_cntTickets = null;
$kaeufer_sum = null;

$doNamesDiffer = false;

// Zentrale Sammlung für alle Antworten
$responseCollection = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // GET DATA IF 'Einlass' == open
    $ids = ['nachname','vorname','email','telNumber','age','klasse','cntTickets'];

    // Zugriff auf Formulardaten
    $kaeufer_nachname = $_POST['nachname'] ?? '';
    $kaeufer_vorname = $_POST['vorname'] ?? '';
    $kaeufer_email = $_POST['email'] ?? '';
    $kaeufer_telNumber = $_POST['telNumber'] ?? '';
    $kaeufer_age = $_POST['age'] ?? '';
    $kaeufer_klasse = $_POST['klasse'] ?? '13/0';
    $kaeufer_cntTickets = $_POST['cntTickets'] ?? 0;

    // Tickets verarbeiten
    for ($i = 1; $i <= $kaeufer_cntTickets; $i++) {
        $ticketName = $_POST["ticketName$i"] ?? '';
        $ticketVorName = $_POST["ticketVorName$i"] ?? '';
        $ticketEmail = $_POST["ticketEmail$i"] ?? '';
        $ticketAge = $_POST["ticketAge$i"] ?? '';

        // Hier kannst du die Ticketdaten speichern oder weiterverarbeiten
    }

    $vars = [$kaeufer_nachname,$kaeufer_vorname,$kaeufer_email,$kaeufer_telNumber,$kaeufer_age,$kaeufer_klasse,$kaeufer_cntTickets, $kaeufer_sum];

    // GET TICKET DATA
    $tickets = getTicketData($kaeufer_cntTickets, $conn);

    // CHECK DATA
        // CHECK, IF NAMES DIFFER
    if(!checkNamesDiffer($tickets) == 1){
        exportMessage("differ");
        return;
    }

    $doNamesDiffer = true;
    if(checkDataDatabase($conn, $tickets)){
        exportMessage("write");
        if(!insertTicket_db($conn, $vars, $tickets)){
            exportMessage("fail");
            return false;
        }
        exportMessage("success");
        sendResponse();
        sendSuccessMail($conn, $vars, $tickets);
    }else{
        exportMessage("fail");
        sendResponse();
    }
}

// Daten für Tickets aus dem Front-End holen
function getTicketData($kaeufer_cntTickets, $conn){
    // Initialisiere ein Array, um die Ticketdaten zu speichern
    $ticketsData = [];
    
    // Iteriere durch alle Tickets (01, 02, 03, ...)
    for ($i = 1; $i <= $kaeufer_cntTickets; $i++) {

        $ticketSuffix = $i;
        
        // Sicheres Abrufen der Daten aus $_POST mit real_escape_string
        $ticketData = [
            'name' => isset($_POST["ticketName{$ticketSuffix}"]) ? $conn->real_escape_string($_POST["ticketName{$ticketSuffix}"]) : null,
            'prename' => isset($_POST["ticketVorName{$ticketSuffix}"]) ? $conn->real_escape_string($_POST["ticketVorName{$ticketSuffix}"]) : null,
            'mail' => isset($_POST["ticketEmail{$ticketSuffix}"]) ? $conn->real_escape_string($_POST["ticketEmail{$ticketSuffix}"]) : null,
            'age' => isset($_POST["ticketAge{$ticketSuffix}"]) ? $conn->real_escape_string($_POST["ticketAge{$ticketSuffix}"]) : null,
        ];
        
        // Füge die Daten in die Hauptsammlung ein
        $ticketsData[] = $ticketData;
    }
    
    return $ticketsData;
}

// Prüfen, ob Namen unterschiedlich sind
function checkNamesDiffer($tickets){
    $nameTickets = [];
    
    foreach ($tickets as $ticket) {
        //GEHE DURCH DAS ARRAY UND SCHREIBE ALLE VOR UND NACHNAMEN ALS KOMBINATION IN EINE NEUE VARIABLE
        $fullName = strtolower($ticket['prename']) . strtolower($ticket['name']);
        
        // Prüfen, ob der Name schon im Array enthalten ist
        //WENN VORHANDEN, ABBRUCH = DOPPEL-EINTRAG
        if (in_array($fullName, $nameTickets)) {
            return false;
        }
        //WENN NICHT VORHANDEN => ALLES LEGIT
        //SCHREIBE DIESE VARIABLEN IN EIN ARRAY UND PRÜFE, OB DIESER STRING SCHON IM ARRAY VORHANDEN IST
        $nameTickets[] = $fullName; // Wenn nicht vorhanden, hinzufügen
    }
    return true;
}

// Prüfen, ob das Ticket, dass jetzt geschrieben werden soll, schon in der Datenbank enthalten ist
function checkDataDatabase($conn, $tickets){

    // Überprüfen der eingetragenen Daten für ein Ticket. 
    // Kein Ticket darf doppelt geschrieben werden bzw. doppelt ausgestellt werden.
    // Sobald ein doppelter Datensatz in den Tickets gefunden wurde => abbrechen und Fehler werfen.

    for ($i = 0; $i < count($tickets); $i++) {
        // Sicherstellen, dass der Vorname und Nachname aus den Ticket-Daten korrekt extrahiert werden
        $vorname = $tickets[$i]['prename'];
        $nachname = $tickets[$i]['name'];
    

        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM tickets WHERE nachname = ? AND vorname = ?");
        $stmt->bind_param("ss", $nachname, $vorname);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if(!$row['count'] == 0) {
            exportMessage('double');
            return false;
        }
    
        $stmt->close();
        return true;
    }
}

// Funktion zum vorbereiteten Schreiben des Tickets in die Datenbank
function insertTicket_db($conn, $vars, $tickets){
    for ($i=0; $i < count($tickets); $i++) { 
        //Name des Tickets in Format schreiben
        $name = $tickets[$i]['prename'] . ' ' . $tickets[$i]['name'];
        //Preis für Ticket suchen
        $price = checkNameKombinationOfMCG($name);
        //Preis für Ticket zum Array $tickets hinzufügen
        $tickets[$i]['price'] = $price['money'];
        $vars[7] = $vars[7] + $tickets[$i]['price'];
    }

    //Käufer suchen
    $k_id = kaeuferid($conn, $vars);
    log_data($conn, $tickets, $vars, 'kaeufer');

    //Ticket(s) auf erstellten oder gefundenen Käufer schreiben
    if(!writeTicket($conn,$tickets,$k_id)){
        exportMessage('db_error');
        return false;
    }

    log_data($conn, $tickets, $vars, 'ticket1');
    if(count($tickets) > 1){
        log_data($conn, $tickets, $vars, 'ticket2');
    }
    log_data($conn, $tickets, $vars, 'write_database');
    log_data($conn, $tickets, $vars, 'update_database');
    return $tickets;
}

// Funktion, die Überprüft, ob der Name des eingegebenn Tickets zugehörig zum MCG ist
function checkNameKombinationOfMCG($name) {
    $csvFile = '../affiliations/data/Namen+16.csv';
    
    // Datei öffnen und Zeile für Zeile lesen
    if (($handle = fopen($csvFile, "r")) !== false) {
        // Erste Zeile (Header) überspringen
        fgetcsv($handle); // Header-Zeile überspringen

        // Den gesuchten Namen aufteilen in Vorname und Nachname
        $nameParts = explode(" ", trim($name));
        $suchNachname = array_pop($nameParts); // Der letzte Teil ist der Nachname
        $suchVorname = implode(" ", $nameParts); // Der Rest ist der Vorname
    
        // Durch alle Zeilen der CSV-Datei iterieren
        while (($data = fgetcsv($handle, 1000, ",")) !== false) { // Komma als Trennzeichen
            // Sicherstellen, dass beide Spalten vorhanden sind
            $nachname = isset($data[0]) ? trim(str_replace(',', '', $data[0])) : '';
            $vorname = isset($data[1]) ? trim(str_replace(',', '', $data[1])) : '';

            // Format für den gesamten Namen "Vorname Nachname"
            $fullName = $vorname . " " . $nachname;

            // Prüfen, ob der gesamte Name übereinstimmt
            if (strcasecmp($fullName, $name) === 0) {
                fclose($handle); // Datei schließen
                return ['found' => true, 'money' => 12];
            }

            // Die Vornamen aufteilen und prüfen, ob einer übereinstimmt
            $vornamenArray = explode(" ", $vorname);

            // Überprüfen, ob der gesuchte Vorname mit einem der Vornamen übereinstimmt
            foreach ($vornamenArray as $vname) {
                if (strcasecmp($vname, $suchVorname) === 0 && strcasecmp($nachname, $suchNachname) === 0) {
                    fclose($handle); // Datei schließen
                    return ['found' => true, 'money' => 12];
                }
            }
        }
        fclose($handle); // Datei schließen
    }
    return ['found' => false, 'money' => 15];
}

// Sucht nach der KäuferID eines Datensatzes
function kaeuferid($conn, $käufer){
    $check = "SELECT COUNT(*) AS 'count' FROM käufer WHERE email LIKE ?";
    $stmt = $conn->prepare($check);
    $stmt->bind_param("s", $käufer[2]);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if(!$row['count'] == 0) {
        // Käufer ist schon vorhanden bzw. email wurde gefunden 
        $check = "SELECT ID FROM käufer WHERE email LIKE ?";
        $stmt = $conn->prepare($check);
        $stmt->bind_param("s", $käufer[2]);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // Id des Käufers holen
        $id = $row['ID'];
        $stmt->close();

        return $id;
    }

    // Email wurde noch nicht gefunden => wir schreiben den Käufer, mit den angegbeben Daten
    $insert = "INSERT INTO `käufer` (`vorname`, `nachname`, `email`, `age`, `telNr`, `klasse`) VALUES (?,?,?,?,?,?);";
    $stmt = $conn->prepare($insert);

    if($käufer[5] == ''){
        $käufer[5] = '13/0';
    }

    $stmt->bind_param("sssiss", $käufer[1],$käufer[0],$käufer[2],$käufer[4],$käufer[3],$käufer[5]);
    $stmt->execute();
    $stmt->close();

    // Käufer-ID: 
    $check = "SELECT ID FROM käufer WHERE email LIKE ?";
    $stmt = $conn->prepare($check);
    $stmt->bind_param("s", $käufer[2]);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Id des Käufers holen
    $id = $row['ID'];
    $stmt->close();

    // open resetten
    $open = "UPDATE `käufer` SET open = sum - paid WHERE ID = ?";
    $stmt = $conn->prepare($open);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    return $id;
}

// Schreibt das Ticket
function writeTicket($conn, $tickets, $k_id){
    for ($i=0; $i < count($tickets); $i++) { 
        $ticket = "INSERT INTO `tickets` (`vorname`,`nachname`,`email`,`age`,`sum`, `käufer_ID`) VALUES (?,?,?,?,?,?);";
        $stmt = $conn->prepare($ticket);
        $stmt->bind_param("sssiii", $tickets[$i]['prename'],$tickets[$i]['name'],$tickets[$i]['mail'],$tickets[$i]['age'],$tickets[$i]['price'],$k_id);
        $stmt->execute();
        $stmt->close();
    }
    //cntTickets und sum für Käufer updaten
    if(!updateTables($conn, $k_id)){
        return false;
    }

    return $tickets;
}

// Spalten wie: open, sum werden geupdatet
function updateTables($conn, $k_id){
    //cntTickets holen
    // Zähle die Tickets für den Käufer
    $cntQuery = "SELECT COUNT(*) AS 'Count' FROM tickets WHERE `käufer_ID` = ?";
    $stmt = $conn->prepare($cntQuery);
    if (!$stmt) {
        return false;  // Fehler beim Vorbereiten der Abfrage
    }
    $stmt->bind_param("i", $k_id);
    if (!$stmt->execute()) {
        return false;  // Fehler beim Ausführen der Abfrage
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $cnt = $row['Count'];
    $stmt->close();

    // Aktualisiere die Anzahl der Tickets für den Käufer
    $cntUpdateQuery = "UPDATE `käufer` SET `cntTickets` = ? WHERE ID = ?";
    $stmt = $conn->prepare($cntUpdateQuery);
    if (!$stmt) {
        return false;  // Fehler beim Vorbereiten der Abfrage
    }
    $stmt->bind_param("ii", $cnt, $k_id);
    if (!$stmt->execute()) {
        return false;  // Fehler beim Ausführen der Abfrage
    }
    $stmt->close();

    // Hole die Summe der Tickets für den Käufer
    $sumQuery = "SELECT SUM(sum) AS 'sum' FROM tickets WHERE käufer_ID = ?";
    $stmt = $conn->prepare($sumQuery);
    if (!$stmt) {
        return false;  // Fehler beim Vorbereiten der Abfrage
    }
    $stmt->bind_param("i", $k_id);
    if (!$stmt->execute()) {
        return false;  // Fehler beim Ausführen der Abfrage
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $sum = $row['sum'];
    $stmt->close();

    // Aktualisiere die Summe für den Käufer
    $sumUpdateQuery = "UPDATE `käufer` SET `sum` = ? WHERE ID = ?;";
    $stmt = $conn->prepare($sumUpdateQuery);
    if (!$stmt) {
        return false;  // Fehler beim Vorbereiten der Abfrage
    }
    $stmt->bind_param("ii", $sum, $k_id);
    if (!$stmt->execute()) {
        return false;  // Fehler beim Ausführen der Abfrage
    }
    $stmt->close();

    // Aktualisiere den offenen Betrag
    $openQuery = "UPDATE `käufer` SET open = sum - paid WHERE ID = ?;";
    $stmt = $conn->prepare($openQuery);
    $stmt->bind_param("i",$k_id);
    if (!$stmt) {
        return false;  // Fehler beim Vorbereiten der Abfrage
    }
    if (!$stmt->execute()) {
        return false;  // Fehler beim Ausführen der Abfrage
    }
    $stmt->close();
    return true;  // Alle Abfragen erfolgreich ausgeführt
}

// Sammeln der Return-Werte
function exportMessage($msg, $ticketNr = null, $tickets = null) {
    global $responseCollection; // Zugriff auf die zentrale Sammlung

    // Nachricht basierend auf dem Typ erstellen
    switch ($msg) {
        case 'write':
            $responseCollection[] = [
                'status' => 'valid',
                'message' => 'Vorgang wird verarbeitet!',
            ];
            break;

        case 'differ':
            $responseCollection[] = [
                'status' => 'error',
                'message' => 'Names do not differ',
            ];
            break;

        case 'double':
            $responseCollection[] = [
                'status' => 'error',
                'message' => 'Name schon in der Datenbank gefunden. Doppelter Eintrag für Ticket ' . (($ticketNr !== null) ? ($ticketNr + 1) : 'unbekannt'),
            ];
            break;

        case 'db_error':
            $responseCollection[] = [
                'status' => 'error',
                'message' => 'Fehler beim Schreiben der Tickets in die Datenbank!',
            ];
            break;

        case 'check':
            if (is_array($tickets)) {
                foreach ($tickets as $ticket) {
                    $responseCollection[] = [
                        'status' => 'info',
                        'message' => 'Ticket geprüft: ' . $ticket,
                    ];
                }
            } else {
                $responseCollection[] = [
                    'status' => 'info',
                    'message' => 'Keine Tickets übergeben zur Prüfung.',
                ];
            }
            break;

        case 'success':
            $responseCollection[] = [
                'status' => 'success',
                'message' => 'Tickets wurden erfolgreich geschrieben',
            ];
            break;

        case 'fail':
            $responseCollection[] = [
                'status' => 'error',
                'message' => 'Tickets wurden nicht geschrieben',
            ];
            break;

        default:
            $responseCollection[] = [
                'status' => 'error',
                'message' => 'Ungültige Anfrage bzw. unerwarteter Fehler',
            ];
            break;
    }
}

// Senden der Nachrichten ans Front-End
function sendResponse() {
    global $responseCollection; // Zugriff auf die zentrale Sammlung

    // Setze den Content-Type-Header auf JSON
    header('Content-Type: application/json; charset=utf-8');

    // JSON-Antwort ausgeben
    echo json_encode($responseCollection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

function sendSuccessMail($conn, $käufer, $tickets){
    # Log-Funktion einführen
    # Funktion aus mail.php aufrufen: 
    log_data($conn, $tickets, $käufer, 'mail');
    mailing($conn, $käufer,$tickets);

    //TODO:
    # Mail-Text anpassen
}

function log_data($conn, $tickets, $käufer, $content){
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
        switch ($content) {
            case 'ticket1':
                fwrite($file, '✎' . $time . ': Write Ticket 01 for ' . $tickets[0]['name'] . '_' . $tickets[0]['prename'] . PHP_EOL); // Mail schreiben
                break;
        
            case 'ticket2':
                fwrite($file, '✎' . $time . ': Write Ticket 02 for ' . $tickets[1]['name'] . '_' . $tickets[1]['prename'] . PHP_EOL); // Mail schreiben
                break;
        
            case 'kaeufer':
                fwrite($file, '✎' . $time . ': Write Kaeufer for ' . $käufer[2] . PHP_EOL); // Mail schreiben
                break;
        
            case 'write_database':
                fwrite($file, '⟳' . $time . ': Insert Database for ' . $käufer[2] . PHP_EOL); // Mail schreiben
                break;
        
            case 'update_database':
                fwrite($file, '⟳' . $time . ': Update Database for ' . $käufer[2] . PHP_EOL); // Mail schreiben
                break;
        
            case 'mail':
                fwrite($file, '✉' . $time . ': Attempting Mail for ' . $käufer[2] . PHP_EOL); // Mail schreiben
                break;
        
            default:
                fwrite($file, '❌' . $time . ': Error occured ' . $käufer[2] . PHP_EOL); // Mail schreiben
                break;
        }        
        #echo "Inhalt wurde erfolgreich in die Datei '$filename' geschrieben.";
        fclose($file); // Datei schließen
    } else {
        #echo "Fehler beim Öffnen der Datei '$filename'.";
    }
}