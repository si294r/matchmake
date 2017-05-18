<?php

lblselect:
$sql1 = "SELECT * FROM gameserver WHERE PlayFabId = :PlayFabId "
        . "AND SessionTicket = :SessionTicket "
        . "AND GameID = :GameID "
        . "AND GameState IN (0,1) "
        . "LIMIT 1";
$statement1 = $connection->prepare($sql1);
$statement1->execute(array(':PlayFabId' => $PlayFabId, ':SessionTicket' => $SessionTicket, ':GameID' => $GameID));
$row = $statement1->fetch(PDO::FETCH_ASSOC);

if ($row === FALSE) {
    goto lblupdate;
} else {
    
    $data = array("LobbyId" => $row['GameID'], "PlayFabId" => $PlayFabId);
    $data_string = json_encode($data);

    $result = file_get_contents('https://'.$PlayFab_TitleID.'.playfabapi.com/Matchmaker/PlayerJoined', null, stream_context_create(
            array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json' . "\r\n"
                    . 'X-SecretKey: '. $PlayFab_Secret . "\r\n"
                    . 'Content-Length: ' . strlen($data_string) . "\r\n",
                    'content' => $data_string
                )
            )
        )
    );

    $result_json = json_decode($result);
    
    goto lblsuccess;
}

lblupdate:
$sql2 = "UPDATE gameserver "
        . "SET PlayFabId = :PlayFabId, SessionTicket = :SessionTicket "
        . "WHERE GameID = :GameID "
        . "AND PlayFabId IS NULL AND GameState IN (0,1) "
        . "LIMIT 1 "
        . " ";
$statement2 = $connection->prepare($sql2);
$statement2->bindParam(":PlayFabId", $PlayFabId);
$statement2->bindParam(":SessionTicket", $SessionTicket);
$statement2->bindParam(":GameID", $GameID);
$statement2->execute();
$affected_row = $statement2->rowCount();

if ($affected_row == 0) {
    
    header('Content-Type: application/json');

    $matchmaking_result = array(
        'GameID' => $row['GameID'],
        'ServerHostname' => "",
        'ServerPort' => intval(0),
        'error' => 1,
        'message' => 'Error: Cannot Matchmake to GameID '.$GameID
    );
    echo json_encode($matchmaking_result);    
    die;
    
} else {
    goto lblselect;
}

lblsuccess:

$matchmaking_result = array(
    'GameID' => $row['GameID'],
    'ServerHostname' => $row['ServerHostname'],
    'ServerPort' => intval($row['ServerPort']),
    'error' => 0,
    'message' => 'Success'
);
return $matchmaking_result;

