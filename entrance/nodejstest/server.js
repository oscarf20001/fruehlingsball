const WebSocket = require('ws');
const mysql = require('mysql2');

// WebSocket-Server starten (Port 8080)
const wss = new WebSocket.Server({ port: 8080 });

// MySQL-Verbindung aufbauen
let db = mysql.createConnection({
    host: '91.204.46.203',   // Externe MySQL-Server-Adresse
    user: 'k150883_oscar',   // Dein MySQL-Benutzer
    password: '4Qzoc4&01',   // Dein MySQL-Passwort
    database: 'k150883_fruehlingsball' // Name der Datenbank
});

// MySQL-Verbindung sicherstellen (automatische Wiederverbindung)
function handleDisconnect() {
    db.connect((err) => {
        if (err) {
            console.error('Fehler bei der MySQL-Verbindung:', err);
            setTimeout(handleDisconnect, 2000); // Nach 2 Sekunden erneut versuchen
        } else {
            console.log('Erfolgreich mit MySQL verbunden!');
        }
    });

    db.on('error', (err) => {
        console.error('MySQL-Fehler:', err);
        if (err.code === 'PROTOCOL_CONNECTION_LOST') {
            console.log('Verbindung verloren. Stelle erneut eine Verbindung her...');
            handleDisconnect();
        } else {
            throw err;
        }
    });
}
handleDisconnect(); // Verbindung herstellen

// Verbundene Clients speichern
let clients = new Set();

// WebSocket-Verbindung aufbauen
wss.on('connection', (ws) => {
    console.log('Ein Client hat sich verbunden.');
    clients.add(ws);

    ws.on('close', () => {
        console.log('Ein Client hat die Verbindung getrennt.');
        clients.delete(ws);
    });

    ws.on('error', (err) => {
        console.error('WebSocket-Fehler:', err);
    });

    // Direkt nach Verbindung die letzte Änderung senden
    sendLatestUpdate(ws);
});

// Funktion zum Abrufen & Senden der neuesten Änderung
function sendLatestUpdate(ws) {
    db.query('SELECT k_id FROM trigger_log ORDER BY created_at DESC LIMIT 1', (err, result) => {
        if (err) {
            console.error('Datenbankabfrage fehlgeschlagen:', err);
            return;
        }

        if (result.length && result[0].k_id) {
            const kId = result[0].k_id;

            db.query(`SELECT * FROM testdata WHERE id = ?`, [kId], (err, result) => {
                if (err) {
                    console.error('Zweite Abfrage fehlgeschlagen:', err);
                    return;
                }

                if (result.length) {
                    const message = JSON.stringify({ update: result[0] });

                    // Nachricht an alle verbundenen Clients senden
                    clients.forEach(client => {
                        if (client.readyState === WebSocket.OPEN) {
                            client.send(message);
                        }
                    });
                }
            });
        }
    });
}

// Polling: Alle 5 Sekunden nach neuen Daten fragen
setInterval(() => {
    sendLatestUpdate();
}, 1000);

console.log('WebSocket-Server läuft auf ws://localhost:8080');