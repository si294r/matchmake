<?php

$input = file_get_contents("php://input");

include("config.php");
$connection = new PDO(
    "mysql:dbname=$mydatabase;host=$myhost;port=$myport",
    $myuser, $mypass, array(PDO::ATTR_PERSISTENT => false)
);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql1 = "SELECT count(*) as count FROM logindevice "
        . "WHERE FROM_UNIXTIME(ROUND(DeviceIdExpired/1000)) > NOW() + interval -5 minute ";
$statement1 = $connection->prepare($sql1);
$statement1->execute();
$row = $statement1->fetch(PDO::FETCH_ASSOC);
    
$logindevice_result = array(
    'logindevicecount' => (int)$row['count'],
    'error' => 0,
    'message' => 'Success'
);
        
header('Content-Type: application/json');
echo json_encode($logindevice_result);    
