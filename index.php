<?php
$token = 'token';
$apiUrl = "https://api.telegram.org/bot$token/";

$update = file_get_contents("php://input");
$update = json_decode($update, true);

$chatId = $update['message']['chat']['id'];
$text = $update['message']['text'];

if ($text === '/start') {
    $response = "Matn kiriting";
} else {
    $response = "Siz '''$text''' ni kiritidingiz!";
}

sendMessage($chatId, $response);

function sendMessage($chatId, $text) {
    global $apiUrl;
    $url = $apiUrl . "sendMessage?chat_id=$chatId&text=" . urlencode($text);
    file_get_contents($url);
}