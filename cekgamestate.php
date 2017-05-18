<?php

$json = json_decode($input);
$GameID = $json->GameID;
$PlayFabId = $json->PlayFabId;

include("config.php");
$connection = new PDO(
    "mysql:dbname=$mydatabase;host=$myhost;port=$myport",
    $myuser, $mypass
);


$sql1 = "SELECT * FROM gameserver WHERE PlayFabId = :PlayFabId "
        . "AND GameID = :GameID "
        . "LIMIT 1";
$statement1 = $connection->prepare($sql1);
$statement1->execute(
        array(':PlayFabId' => $PlayFabId, ':GameID' => $GameID)
        );
$row = $statement1->fetch(PDO::FETCH_ASSOC);

if ($row === FALSE) {
    $result = array(
        'GameState' => 2,
        'error' => 1,
        'message' => 'Error: Session Expired'
    );
} else {
    $result = array(
        'GameState' => intval($row['GameState']),
        'error' => 0,
        'message' => 'Success'
    );
}

return $result;
