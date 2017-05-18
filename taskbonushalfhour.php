<?php

include("config.php");

$data = array(
    "PlayFabId" => "A6B1D3ABAA3D60B2",
    "FunctionName" => "TaskBonusHalfHour",
    "FunctionParameter" => array(
          "PlayFabId" => "A6B1D3ABAA3D60B2"
    ),
    "RevisionSelection" => "Latest",
    "GeneratePlayStreamEvent" => true    
);
$data_string = json_encode($data);

$result = file_get_contents('https://'.$PlayFab_TitleID.'.playfabapi.com/Server/ExecuteCloudScript', null, stream_context_create(
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

echo $result."\r\n";