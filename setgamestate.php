<?php

$json = json_decode($input);
$GameID = $json->GameID;
$PlayFabId = $json->PlayFabId;
$GameState = $json->GameState;


include("config.php");
$connection = new PDO(
    "mysql:dbname=$mydatabase;host=$myhost;port=$myport",
    $myuser, $mypass
);

$sql1 = "UPDATE gameserver SET GameState = :GameState WHERE GameID = :GameID ";

if (isset($PlayFabId) && $PlayFabId != "") {
    $sql1 = $sql1 . " AND PlayFabId = :PlayFabId ";
}

$statement1 = $connection->prepare($sql1);
$statement1->bindParam(":GameID", $GameID);
$statement1->bindParam(":GameState", $GameState);

if (isset($PlayFabId) && $PlayFabId != "") {
    $statement1->bindParam(":PlayFabId", $PlayFabId);
}

$statement1->execute();

$result = array(
    'error' => 0,
    'message' => 'Success'
);
return $result;
