<?php
require __DIR__ . '/../affiliations/php/vendor/autoload.php';

use Dotenv\Dotenv;

// Erstelle ein Dotenv-Objekt und lade die .env-Datei
$dotenv = Dotenv::createImmutable(__DIR__ . '/../affiliations/php/');
$dotenv->load();

// Greife auf die Umgebungsvariablen zu
$dbHost = $_ENV['DB_HOST'];
$dbDatabase = $_ENV['DB_NAME'];
$dbUsername = $_ENV['DB_USERNAME'];
$dbPassword = $_ENV['DB_PASSWORD'];

// Erstellen einer MySQL-Verbindung mit den Umgebungsvariablen
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbDatabase);

// Verbindung auf UTF-8 setzen
$conn->set_charset("utf8");

// Überprüfen der Verbindung
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}else{
    //echo "<script>console.log('Verbindung zur Datenbank erfolgreich hergestellt!')</script>";
}

// POST-Daten abrufen
$postUsername = $_POST['username'] ?? '';
$postPassword = $_POST['password'] ?? '';

// Anmeldedaten in der Datenbank prüfen
$stmt = $conn->prepare("SELECT * FROM zdata WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $postUsername, $postPassword);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Erfolgreiche Anmeldung
    session_set_cookie_params(1800);  // 30 Minuten (1800 Sekunden)
    ini_set('session.gc_maxlifetime', 1800); // Setze die maximale Lebensdauer der Session auf 30 Minuten
    session_start();
    $_SESSION['loggedin'] = true;
    // Setze den Cookie "User" mit dem Benutzernamen und einer Laufzeit von 30 Minuten
    $cookieExpirationTime = time() + 1800; // 30 Minuten = 1800 Sekunden
    setcookie("User", $postUsername, $cookieExpirationTime, "/");  // "/" bedeutet, dass der Cookie für die gesamte Website gültig ist
    echo json_encode(["success" => true]);
} else {
    // Ungültige Anmeldedaten
    echo json_encode(["success" => false]);
}

$stmt->close();
$conn->close();
?>