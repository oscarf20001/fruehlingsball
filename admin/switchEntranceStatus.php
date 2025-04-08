<?php
require __DIR__ . '/../affiliations/php/vendor/autoload.php';

include '../affiliations/php/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Welchen Service möchten wir aus der Datenbank abfragen, bzw. ändern
    $service = $_POST['service'] ?? 0;

    // Hier wird angegeben, was wir mit dem Service machen wollen; get = nur den Status holen; switch = status ändern
    $request = $_POST['request'] ?? 'get';

    if($service != 0 && $request == 'get'){
        $stmt = $conn->prepare("SELECT status FROM controlls WHERE service = ?");
        $stmt->bind_param("s", $service);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $status = $row['status'];
        $stmt->close();
        echo json_encode(["service" => $service, "status" => $status]);
    }else if($service != 0 && $request == 'switch'){
        // Get the current status
        $stmt = $conn->prepare("SELECT status FROM controlls WHERE service = ?");
        $stmt->bind_param("s", $service);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $current_status = $row['status'];
        $stmt->close();
        
        // Switch the status
        if($current_status == 0){
            $stmt = $conn->prepare("UPDATE controlls SET status = 1 WHERE service = ?");
            $stmt->bind_param("s",$service);
            $stmt->execute();
            $stmt->close();
            echo json_encode(["message"=>"switched ".$service." from ".$current_status ." to 1",
                                "old_stat"=>0,
                                "new_stat"=>1]);
        }else{
            $stmt = $conn->prepare("UPDATE controlls SET status = 0 WHERE service = ?");
            $stmt->bind_param("s",$service);
            $stmt->execute();
            $stmt->close();
            echo json_encode(["message"=>"switched ".$service." from ".$current_status ." to 0",
                                "old_stat"=>1,
                                "new_stat"=>0]);
        }
    }
}