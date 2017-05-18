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
$sql2 = "
UPDATE gameserver 
SET PlayFabId = :PlayFabId, DeviceId = :DeviceId, SessionTicket = :SessionTicket, TotalWin = :TotalWin 
WHERE Build = :Build 
AND Region = :Region AND GameMode = :GameMode AND PlayFabId IS NULL 
AND ForceBot = 0 AND GameState IN (0,1) 
AND GameID NOT IN 
(
    SELECT GameID FROM 
    (
        SELECT GameID FROM gameserver 
    	WHERE PlayFabId = :PlayFabId2 AND Build = :Build2
        AND Region = :Region2 AND GameMode = :GameMode2
    	AND GameState IN (0,1)
    ) T1
)
ORDER BY TotalWin DESC, ID ASC LIMIT 1 
";
$statement2 = $connection->prepare($sql2);
$statement2->bindParam(":PlayFabId", $PlayFabId);
$statement2->bindParam(":DeviceId", $DeviceId);
$statement2->bindParam(":SessionTicket", $SessionTicket);
$statement2->bindParam(":TotalWin", $count_winning);
$statement2->bindParam(":Build", $Build);
$statement2->bindParam(":Region", $Region);
$statement2->bindParam(":GameMode", $GameMode);
// sub query param
$statement2->bindParam(":PlayFabId2", $PlayFabId);
$statement2->bindParam(":Build2", $Build);
$statement2->bindParam(":Region2", $Region);
$statement2->bindParam(":GameMode2", $GameMode);
// end sub query param
$statement2->execute();
$affected_row = $statement2->rowCount();

if ($affected_row == 0) {
    // create game
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

    if ($result_json->status == "OK") {
        $sql3 = "INSERT INTO gameserver (GameID, Build, Region, GameMode, ServerHostname, ServerPort, ForceBot) 
            VALUES (:GameID, :Build, :Region, :GameMode, :ServerHostname, :ServerPort, 0)
            ";
        $statement3 = $connection->prepare($sql3);
        for ($i = 1; $i <= 2; $i++) {
            $statement3->bindParam(":GameID", $result_json->data->GameID);
            $statement3->bindParam(":Build", $Build);
            $statement3->bindParam(":Region", $Region);
            $statement3->bindParam(":GameMode", $GameMode);
            $statement3->bindParam(":ServerHostname", $result_json->data->ServerHostname);
            $statement3->bindParam(":ServerPort", $result_json->data->ServerPort);
            $statement3->execute();
        }
    }
    
    goto lblupdate;
    
} else {

    goto lblselect;
}

lblsuccess:
    
if ($row['SessionTicket'] != $SessionTicket) {
    $sql4 = "UPDATE gameserver "
            . "SET SessionTicket = :SessionTicket "
            . "WHERE ID = :ID "
            . "LIMIT 1 "
            . " ";
    $statement4 = $connection->prepare($sql4);
    $statement4->bindParam(":SessionTicket", $SessionTicket);
    $statement4->bindParam(":ID", $row['ID']);
    $statement4->execute();
    //$affected_row = $statement4->rowCount();
}

$matchmaking_result = array(
    'GameID' => $row['GameID'],
    'DeviceId' => $row['DeviceId'],
    'SessionTicket' => $SessionTicket,
    'ServerHostname' => $row['ServerHostname'],
    'ServerPort' => intval($row['ServerPort']),
    'error' => 0,
    'message' => 'Success'
);
return $matchmaking_result;