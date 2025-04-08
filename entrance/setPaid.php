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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? null;

    if($id === null){
        echo json_encode(["error" => "Keine ID übergeben"]);
        exit();
    }

    $stmt = $conn->prepare("UPDATE tickets SET zuschlag_paid = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["message"=>"Paid wurde für Ticket $id auf 1 gesetzt!"]);
}