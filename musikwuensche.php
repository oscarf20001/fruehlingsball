<?php
require __DIR__ . '/affiliations/php/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/affiliations/php');
$dotenv->load();

$dbHost = $_ENV['DB_HOST'];
$dbDatabase = $_ENV['DB_NAME'];
$dbUsername = $_ENV['DB_USERNAME'];
$dbPassword = $_ENV['DB_PASSWORD'];

$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbDatabase);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Musikwünsche</title>
</head>
<body>
    <form action="#" method="post">
        <input type="text" name="musicwish" id="musikwunsch">
        <input type="submit" value="Daten absenden">
    </form>
</body>
</html>

<?php
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $musicwish = trim($_POST['musicwish']);
    $sqlInsertWishIntoDb = "INSERT INTO music (wish) VALUES (?)";
    $stmt = $conn->prepare($sqlInsertWishIntoDb);
    $stmt->bind_param('s',$musicwish);
    $stmt->execute();
}

function getWishes($conn){
    $sqlGetWishes = "SELECT wish FROM music";
    $result = $conn->query($sqlGetWishes);
    $wishes = array();

    while($row = $result->fetch_assoc()){
        $wishes[] = $row['wish'];
    }

    for ($i = count($wishes) - 1; $i > 0; $i--) {
        echo $wishes[$i]."<br>";
    }
}

getWishes($conn);