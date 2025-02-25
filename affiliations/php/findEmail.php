<?php
// Setzt den Content-Type-Header auf JSON
header('Content-Type: application/json; charset=utf-8');
// Optionale Header für CORS (bei Bedarf, z. B. wenn die Anfrage von einer anderen Domain kommt)
header('Access-Control-Allow-Origin: *'); // Erlaubt alle Ursprünge
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Zulässige HTTP-Methoden

// REQUIREMENTS AND INCLUDES FOR DATABASE CONNECTION
require '../../affiliations/php/mail.php';
require '../../affiliations/php/check.php';
require '../../affiliations/php/db_connection.php';

// CHECKS - IS OPEN?

# Funktion check kommt aus check.php
if(!checks('Einlass', $conn)){
    die("Ticketshop geschlossen. Kein Ticketkauf möglich!");
}

$email = null;
$response = null;

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Empfange die JSON-Daten
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    
    if (isset($data['email'])) {
        $email = $data['email'];  // Die Email aus dem JSON-Array extrahieren
        $sqlCheckForEmail = "SELECT * FROM käufer WHERE email = ?";
        $stmt = $conn->prepare($sqlCheckForEmail);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if($row){
            echo json_encode(["status" => "success", "message" => "Email received", "email" => $email]);
            return;
        }else{
            echo json_encode(["status" => "fail", "message" => "Email not found", "email" => $email]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Email not provided"]);
    }
}