<?php
require __DIR__ . '/../affiliations/php/vendor/autoload.php';

include '../affiliations/php/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    $query = trim($_POST['query']);
    
    // Eingabe schützen (SQL-Injection verhindern)
    $safeQuery = $conn->real_escape_string($query);

    // Emails suchen
    $sqlSearchMail = "SELECT email FROM käufer WHERE email LIKE '$safeQuery%' LIMIT 10";
    $stmt = $conn->prepare($sqlSearchMail);
    $stmt->execute();
    $result = $stmt->get_result();

    // Ergebnisse ausgeben
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<p onclick='selectMail(\"{$row['email']}\")'>{$row['email']}</p>";
        }
    } else {
        echo "<p>Keine Ergebnisse gefunden</p>";
    }

    $conn->close();
}
?>