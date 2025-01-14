<?php
require 'db_connection.php';

//CHECK IF TICKET SHOP IS OPEN
function checks($service, $conn){
    $isOpenStatement = "SELECT status FROM controlls WHERE service = '$service'";
    $result = $conn->query($isOpenStatement);
    $row = $result->fetch_assoc();
    if ($row['status'] == 1) {
        #echo "Offen";
        return true;
    }else{
        #echo "Geschlossen";
        return false;
    }
}
?>