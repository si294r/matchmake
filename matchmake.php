<?php

$json = json_decode($input);
$PlayFabId = $json->PlayFabId;
$DeviceId = $json->DeviceId;
$SessionTicket = $json->SessionTicket;
$Build = $json->Build;
$Region = $json->Region;
$GameMode = $json->GameMode;
$GameID = isset($json->GameID) ? $json->GameID : "";


include("config.php");
$connection = new PDO(
        "mysql:dbname=$mydatabase;host=$myhost;port=$myport", $myuser, $mypass
);

include("matchmake_simulate.php");

$count_winning = 0;
$count_losing = 0;

$is_matchmake_easy_bot = simulate_easy_bot();
//$is_matchmake_easy_bot = false;

if (isset($json->TestSimulate)) {
    echo ($is_matchmake_easy_bot ? "true" : "false");
    die;
}

if (is_string($GameID) && strlen($GameID) > 0) {
    return include 'matchmake_by_ID.php';
} else if ($is_matchmake_easy_bot) {
    return include 'matchmake_by_easy_bot.php';
} else {
    return include 'matchmake_by_BRM.php';
}

