<?php
require __DIR__ . '/../affiliations/php/vendor/autoload.php';

include '../affiliations/php/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paid = $_POST['paid'] ?? 0;
    $methodStr = $_POST['method'];
    $email = $_POST['email'];

    // Get buyer ID
    $sqlGetKaeuferId = "SELECT ID FROM käufer WHERE email = ?";
    $stmt = $conn->prepare($sqlGetKaeuferId);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $k_id = $result->fetch_assoc()['ID'] ?? null;
    $stmt->close();

    if (!$k_id) {
        echo json_encode(['error' => 'Buyer not found']);
        exit;
    }

    $timestamp = date("Y/m/d - H:i:s");
    $sqlBuchen = "UPDATE `käufer` SET `paid` = `paid` + ?, `method` = ?, `date_paid` = ? WHERE `ID` = ?";
    $stmt = $conn->prepare($sqlBuchen);
    $stmt->bind_param('dssi', $paid, $methodStr, $timestamp, $k_id);
    $stmt->execute();
    $stmt->close();

    $sqlDif = "SELECT `open` FROM `käufer` WHERE ID = ?";
    $stmt = $conn->prepare($sqlDif);
    $stmt->bind_param('i', $k_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $diff = $result->fetch_assoc()['open'];
    $stmt->close();

    $status = $diff <= 0 ? 1 : 0;
    $sqlStatus = "UPDATE `käufer` SET `status` = ? WHERE ID = ?";
    $stmt = $conn->prepare($sqlStatus);
    $stmt->bind_param('ii', $status, $k_id);
    $stmt->execute();
    $stmt->close();

    $sqlOpen = "UPDATE käufer SET `open` = `open` - ?";
    $stmt = $conn->prepare($sqlOpen);
    $stmt->bind_param('i',$paid);
    $stmt->execute();
    $stmt->close();

    $sqlFinal = "SELECT `paid`, `open`, `status`, `method` FROM `käufer` WHERE ID = ?";
    $stmt = $conn->prepare($sqlFinal);
    $stmt->bind_param('i', $k_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $finalData = $result->fetch_assoc();
    $stmt->close();

    header('Content-Type: application/json');
    echo json_encode([
        'paid' => number_format($finalData['paid'], 2),
        'open' => number_format($finalData['open'], 2),
        'status' => $finalData['status'],
        'method' => $finalData['method']
    ]);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}
exit;