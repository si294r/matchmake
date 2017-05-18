<?php

$json = json_decode($input);
$PlayFabId = $json->PlayFabId;
$GameID = $json->GameID;

include("config.php");
$connection = new PDO(
        "mysql:dbname=$mydatabase;host=$myhost;port=$myport", $myuser, $mypass
);

$sql2 = "UPDATE gameserver "
        . "SET PlayFabId = NULL, SessionTicket = NULL "
        . "WHERE GameID = :GameID "
        . "AND PlayFabId = :PlayFabId "
        . "AND GameState IN (0) "
        . "LIMIT 1 "
        . " ";
$statement2 = $connection->prepare($sql2);
$statement2->bindParam(":GameID", $GameID);
$statement2->bindParam(":PlayFabId", $PlayFabId);
$statement2->execute();
$affected_row = $statement2->rowCount();

$result = array(
    'affected_row' => intval($affected_row),
    'error' => 0,
    'message' => 'Success'
);
return $result;
