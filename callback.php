<?php

$input = file_get_contents("php://input");
$json = json_decode($input);
$EventName = $json->EventName;
$GameID = $json->GameID;


include("config.php");
$connection = new PDO(
    "mysql:dbname=$mydatabase;host=$myhost;port=$myport",
    $myuser, $mypass
);


$sql1 = "UPDATE gameserver SET GameState = 2, EventName = :EventName WHERE GameID = :GameID ";
$statement1 = $connection->prepare($sql1);
$statement1->bindParam(":EventName", $EventName);
$statement1->bindParam(":GameID", $GameID);
$statement1->execute();

echo "OK";
