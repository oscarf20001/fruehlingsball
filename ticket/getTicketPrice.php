<?php
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Den vollständigen Namen aus dem POST-Request holen
    $name = $_POST['fullName'];

    // Überprüfen, ob der Name leer ist
    if (empty(trim($name))) {
        // Wenn der Name leer ist, den Preis auf 0 setzen
        echo ['money' => 0]; // oder echo 0;
        exit; // Stoppe die Ausführung des Scripts
    }

    // Die Funktion ausführen und das Ergebnis abrufen
    $result = checkNameKombinationOfMCG($name);

    // Rückgabe des Preises als Antwort
    echo $result['money'];
}
?>