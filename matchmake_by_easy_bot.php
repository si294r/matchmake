<?php

lblselect:
$sql1 = "SELECT * FROM gameserver WHERE PlayFabId = :PlayFabId "
        . "AND Build = :Build "
        . "AND Region = :Region "
        . "AND GameMode = :GameMode "
        . "AND GameState IN (0,1) "
        . "LIMIT 1";
$statement1 = $connection->prepare($sql1);
$statement1->execute(
        array(':PlayFabId' => $PlayFabId, 
            ':Build' => $Build, ':Region' => $Region, ':GameMode' => $GameMode)
        );
$row = $statement1->fetch(PDO::FETCH_ASSOC);

if ($row === FALSE) {
    // start game instance
    $data = array("Build" => $Build, "Region" => $Region, "GameMode" => $GameMode, "CustomCommandLineData" => "",
        "ExternalMatchmakerEventEndpoint" => $Matchmaking_Callback);
    $data_string = json_encode($data);

    $result = file_get_contents('https://'.$PlayFab_TitleID.'.playfabapi.com/Matchmaker/StartGame', null, stream_context_create(
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

    // insert game server data
    if ($result_json->status == "OK") {
        $GameID = $result_json->data->GameID;
        $ServerHostname = $result_json->data->ServerHostname;
        $ServerPort = $result_json->data->ServerPort;
        
        $sql3 = "INSERT INTO gameserver (GameID, Build, Region, GameMode, ServerHostname, ServerPort, ForceBot, 
            PlayFabId, DeviceId, SessionTicket, TotalWin) 
            VALUES (:GameID, :Build, :Region, :GameMode, :ServerHostname, :ServerPort, 1,
            :PlayFabId, :DeviceId, :SessionTicket, :TotalWin)
            ";
        $statement3 = $connection->prepare($sql3);
        $statement3->bindParam(":GameID", $GameID);
        $statement3->bindParam(":Build", $Build);
        $statement3->bindParam(":Region", $Region);
        $statement3->bindParam(":GameMode", $GameMode);
        $statement3->bindParam(":ServerHostname", $ServerHostname);
        $statement3->bindParam(":ServerPort", $ServerPort);
        $statement3->bindParam(":PlayFabId", $PlayFabId);
        $statement3->bindParam(":DeviceId", $DeviceId);
        $statement3->bindParam(":SessionTicket", $SessionTicket);
        $statement3->bindParam(":TotalWin", $count_winning);
        $statement3->execute();
        
        $sql3 = "INSERT INTO gameserver (GameID, Build, Region, GameMode, ServerHostname, ServerPort, ForceBot) 
            VALUES (:GameID, :Build, :Region, :GameMode, :ServerHostname, :ServerPort, 1)
            ";
        $statement3 = $connection->prepare($sql3);
        $statement3->bindParam(":GameID", $GameID);
        $statement3->bindParam(":Build", $Build);
        $statement3->bindParam(":Region", $Region);
        $statement3->bindParam(":GameMode", $GameMode);
        $statement3->bindParam(":ServerHostname", $ServerHostname);
        $statement3->bindParam(":ServerPort", $ServerPort);
        $statement3->execute();
        
    } else {
        header('Content-Type: application/json');

        $matchmaking_result = array(
            'GameID' => "",
            'ServerHostname' => "",
            'ServerPort' => intval(0),
            'error' => 1,
            'message' => 'Error: Matchmake failed to start game instance.'
        );
        echo json_encode($matchmaking_result);        
        die;
    }

} else {
    
    $GameID = $row['GameID'];
    $ServerHostname = $row['ServerHostname'];
    $ServerPort = $row['ServerPort'];
    
}

// player join
$data = array("LobbyId" => $GameID, "PlayFabId" => $PlayFabId);
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
$playerjoined_result_json = json_decode($result);

$matchmaking_result = array(
    'GameID' => $GameID,
    'DeviceId' => $DeviceId,
    'SessionTicket' => $SessionTicket,
    'ServerHostname' => $ServerHostname,
    'ServerPort' => intval($ServerPort),
    'error' => 0,
    'message' => 'Success'
);
return $matchmaking_result;