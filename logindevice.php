<?php

$json = json_decode($input);
$PlayFabId = $json->PlayFabId;
$DisplayName = isset($json->DisplayName) ? $json->DisplayName : null;
$DeviceId = $json->DeviceId;
$DeviceIdExpired = $json->DeviceIdExpired;

include("config.php");
$connection = new PDO(
    "mysql:dbname=$mydatabase;host=$myhost;port=$myport",
    $myuser, $mypass
);

$sql3 = "INSERT INTO logindevice (PlayFabId, DisplayName, DeviceId, DeviceIdExpired) 
    VALUES (:PlayFabId, :DisplayName, :DeviceId, :DeviceIdExpired)
    ON DUPLICATE KEY UPDATE 
    DisplayName = :DisplayName2, DeviceId = :DeviceId2, DeviceIdExpired = :DeviceIdExpired2
    ";
$statement3 = $connection->prepare($sql3);
$statement3->bindParam(":PlayFabId", $PlayFabId);
$statement3->bindParam(":DisplayName", $DisplayName);
$statement3->bindParam(":DeviceId", $DeviceId);
$statement3->bindParam(":DeviceIdExpired", $DeviceIdExpired);
$statement3->bindParam(":DisplayName2", $DisplayName);
$statement3->bindParam(":DeviceId2", $DeviceId);
$statement3->bindParam(":DeviceIdExpired2", $DeviceIdExpired);
$statement3->execute();
    
$result = array(
    'error' => 0,
    'message' => 'Success'
);
return $result;        
