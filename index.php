<?php
$token = '6086783074:AAEI2YUXW1VS4OVLyrewDGnFI7GlPQt6ki0';
$apiUrl = "https://api.telegram.org/bot$token/";

$update = file_get_contents("php://input");
$update = json_decode($update, true);

if (isset($update['message'])) {
    $chatId = $update['message']['chat']['id'];
    $text = $update['message']['text'];
    $firstName = $update['message']['from']['first_name'];

    $rates = [
        'usd2uzs' => 12500,
        'eur2uzs' => 13500,
        'rub2uzs' => 130
    ];

    if ($text === '/start') {
        $response = "Salom, *{$firstName}*!\nMenga valyuta konvertatsiyasi uchun qiymat yuboring:\n" .
                    "/usd2uzs - AQSh dollaridan so'mga\n" .
                    "/eur2uzs - Evrodan so'mga\n" .
                    "/rub2uzs - Rubldan so'mga";
        $keyboard = [
            'keyboard' => [
                [['text' => '🇺🇸 USD > 🇺🇿 UZS']],
                [['text' => '🇪🇺 EUR > 🇺🇿 UZS']],
                [['text' => '🇷🇺 RUB > 🇺🇿 UZS']]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ];
        sendMessage($chatId, $response, $keyboard, null, 'Markdown');
    } elseif (in_array($text, ['/usd2uzs', '/eur2uzs', '/rub2uzs', '🇺🇸 USD > 🇺🇿 UZS', '🇪🇺 EUR > 🇺🇿 UZS', '🇷🇺 RUB > 🇺🇿 UZS'])) {
        $type = str_replace(['/', ' > 🇺🇿 UZS', '🇺🇸 USD', '🇪🇺 EUR', '🇷🇺 RUB'], '', strtolower($text));
        $response = "Qiymatni kiriting (masalan, 100):";
        $keyboard = [
            'keyboard' => [[['text' => '⬅️ Ortga']]],
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ];
        sendMessage($chatId, $response, $keyboard, $type);
    } elseif ($text === '⬅️ Ortga') {
        $response = "Bosh menyuga qaytdingiz.";
        $keyboard = [
            'keyboard' => [
                [['text' => '🇺🇸 USD > 🇺🇿 UZS']],
                [['text' => '🇪🇺 EUR > 🇺🇿 UZS']],
                [['text' => '🇷🇺 RUB > 🇺🇿 UZS']]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ];
        sendMessage($chatId, $response, $keyboard);
        if (file_exists("state_$chatId.txt")) {
            unlink("state_$chatId.txt");
        }
    } elseif (is_numeric($text)) {
        $stateFile = "state_$chatId.txt";
        if (file_exists($stateFile)) {
            $type = file_get_contents($stateFile);
            if (isset($rates[$type])) {
                $result = $text * $rates[$type];
                $currency = strtoupper(substr($type, 0, 3));
                $response = "*$text $currency* = *$result UZS*";
            } else {
                $response = "Xatolik yuz berdi. Qaytadan boshlang: /start";
            }
        } else {
            $response = "Iltimos, avval valyuta turini tanlang!";
        }
        $keyboard = [
            'keyboard' => [[['text' => '⬅️ Ortga']]],
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ];
        sendMessage($chatId, $response, $keyboard, null, 'Markdown');
    } else {
        $response = "Noma'lum buyruq. /start ni bosing.";
        sendMessage($chatId, $response);
    }
}

function sendMessage($chatId, $text, $keyboard = null, $type = null, $parseMode = null) {
    global $apiUrl;
    $params = [
        'chat_id' => $chatId,
        'text' => $text // Убрали urlencode, так как Telegram сам обрабатывает текст
    ];
    if ($keyboard) {
        $params['reply_markup'] = json_encode($keyboard);
    }
    if ($parseMode) {
        $params['parse_mode'] = $parseMode;
    }
    $url = $apiUrl . "sendMessage?" . http_build_query($params);
    file_get_contents($url);

    if ($type) {
        file_put_contents("state_$chatId.txt", $type);
    }
}