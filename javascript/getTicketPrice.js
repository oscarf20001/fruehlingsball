function getTicketPrice(prename, lastname) {
    // Hole die Werte der Eingabefelder und kombiniere sie zum vollständigen Namen
    const fullName = prename + " " + lastname;

    // Sende eine POST-Anfrage an die PHP-Datei, um den Preis abzurufen
    return fetch("../ticket/getTicketPrice.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "fullName=" + encodeURIComponent(fullName)  // Den vollständigen Namen als Parameter senden
    })
    .then(response => response.text())  // Die Antwort als Text empfangen
    .then(data => {
        // Konvertiere die Antwort in einen Integer
        const price = parseInt(data, 10);
        
        // Überprüfe, ob die Konvertierung erfolgreich war, sonst gebe eine Fehlermeldung aus
        if (isNaN(price)) {
            //console.error('Fehler: Der Rückgabewert ist kein gültiger Integer.', data);
            return 0; // Wenn der Wert ungültig ist, null zurückgeben oder eine andere Aktion ausführen
        }
        
        // Gib den Preis als Integer zurück
        return price;
    })
    .catch(error => {
        console.error('Fehler:', error);
        return null; // Bei Fehlern null zurückgeben oder eine andere Aktion ausführen
    });
}