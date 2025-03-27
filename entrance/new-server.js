const WebSocket = require('ws');
const mysql = require('mysql2');

//Umgebungsvariablen
require('dotenv').config({path:'../affiliations/php/.env'});

class Database {
    constructor(config) {
        this.config = config;
        this.connection = this.createConnection();
        this.handleDisconnect();
    }

    createConnection() {
        return mysql.createConnection(this.config);
    }

    handleDisconnect() {
        this.connection.connect((err) => {
            if (err) {
                console.error('Fehler bei der MySQL-Verbindung:', err);
                setTimeout(() => this.handleDisconnect(), 2000);
            } else {
                console.log('Erfolgreich mit MySQL verbunden!');
            }
        });

        this.connection.on('error', (err) => {
            console.error('MySQL-Fehler:', err);
            if (err.code === 'PROTOCOL_CONNECTION_LOST') {
                console.log('Verbindung verloren. Stelle erneut eine Verbindung her...');
                this.connection = this.createConnection();
                this.handleDisconnect();
            } else {
                throw err;
            }
        });
    }

    query(sql, params = []) {
        return new Promise((resolve, reject) => {
            this.connection.query(sql, params, (err, result) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(result);
                }
            });
        });
    }
}

class TicketManager {
    constructor(database, websocketServer) {
        this.database = database;
        this.websocketServer = websocketServer;
        this.startPolling();
    }

    async sendLatestUpdate() {
        try {
            const result = await this.database.query('SELECT k_id FROM trigger_log ORDER BY created_at DESC LIMIT 1');
            if (result.length && result[0].k_id) {
                const kId = result[0].k_id;
                const ticketData = await this.database.query('SELECT * FROM tickets WHERE id = ?', [kId]);

                if (ticketData.length) {
                    this.websocketServer.broadcast({ update: ticketData[0] });
                }
            }
        } catch (error) {
            console.error('Fehler beim Abrufen des neuesten Updates:', error);
        }
    }

    startPolling() {
        setInterval(() => this.sendLatestUpdate(), 1000);
    }
}

class WebSocketServer {
    constructor(port) {
        this.port = port;
        this.clients = new Set();
        this.wss = new WebSocket.Server({port: this.port });
        this.init();
    }

    init() {
        this.wss.on('connection', (ws) => {
            console.log('Ein Client hat sich verbunden.');
            this.clients.add(ws);

            ws.on('close', () => {
                console.log('Ein Client hat die Verbindung getrennt.');
                this.clients.delete(ws);
            });

            ws.on('error', (err) => {
                console.error('WebSocket-Fehler:', err);
            });
        });

        console.log(`WebSocket-Server läuft auf ws://localhost:${this.port}`);
    }

    broadcast(data) {
        const message = JSON.stringify(data);
        this.clients.forEach(client => {
            if (client.readyState === WebSocket.OPEN) {
                client.send(message);
            }
        });
    }
}

// Initialisierung
const dbConfig = {
    host: process.env.DB_HOST,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME
};

const database = new Database(dbConfig);
const websocketServer = new WebSocketServer(8080);
const ticketManager = new TicketManager(database, websocketServer);