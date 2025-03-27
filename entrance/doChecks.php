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
    echo json_encode(["error" => "Ticketshop geschlossen. Kein Ticketkauf möglich!"]);
    exit();
}

$checks = [
    "paid"=>false,
    "extra"=>true
];

$xtraPayCheck = 1744394400;
#$xtraPayCheck = time();
$currentTS = time();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? null;

    if($id === null){
        echo json_encode(["error" => "Keine ID übergeben"]);
        exit();
    }

    // Überprüfen, ob Käufer alles gezahlt hat
    // 1. Käufer ID holen

    $stmt = $conn->prepare("SELECT käufer_ID FROM tickets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Käufer ID für genau das Ticket
    $k_id = $row['käufer_ID'];
    $stmt->close();

    // Überprüfe den Wert von open auf dieser Käufer ID
    $stmt = $conn->prepare("SELECT status FROM käufer WHERE ID = ?");
    $stmt->bind_param("i",$k_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $status = $row['status'];
    $stmt->close();

    if($status === 1){
        $checks['paid'] = true;
    }else if($status === NULL || $status === 0){
        $checks['paid'] = false;
        $checks['extra'] = false;
        echo json_encode($checks);
        return;
    }

    $stmt = $conn->prepare("SELECT zuschlag FROM tickets WHERE id = ?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $zuschlag = $row['zuschlag'];
    $stmt->close();

    if($zuschlag == 0){
        $checks['extra'] = false;
    }

    echo json_encode($checks);
}