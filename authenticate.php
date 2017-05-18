<?php

$json = json_decode($input);
$GameID = $json->GameID;
$SessionTicket = $json->SessionTicket;
$PlayFabId = $json->PlayFabId;

include("config.php");
$connection = new PDO(
    "mysql:dbname=$mydatabase;host=$myhost;port=$myport",
    $myuser, $mypass
);

$sql1 = "SELECT * FROM gameserver WHERE PlayFabId = :PlayFabId "
        . "AND SessionTicket = :SessionTicket "
        . "AND GameID = :GameID "
        . "AND GameState IN (0,1) "
        . "LIMIT 1";
$statement1 = $connection->prepare($sql1);
$statement1->execute(
        array(':PlayFabId' => $PlayFabId, ':SessionTicket' => $SessionTicket, 
            ':GameID' => $GameID)
        );
$row = $statement1->fetch(PDO::FETCH_ASSOC);

if ($row === FALSE) {
    $result = array(
        'ForceBot' => 0,
        'error' => 1,
        'message' => 'Error: Session Expired'
    );
} else {
    $result = array(
        'ForceBot' => intval($row['ForceBot']),
        'error' => 0,
        'message' => 'Success'
    );
}

return $result;
