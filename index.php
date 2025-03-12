<?php
$token = '6086783074:AAEI2YUXW1VS4OVLyrewDGnFI7GlPQt6ki0';
$apiUrl = "https://api.telegram.org/bot$token/";

define('TODO_FILE', 'todos.json');

$update = json_decode(file_get_contents("php://input"), true);
if (!$update || !isset($update['message'])) exit;

$chatId = $update['message']['chat']['id'];
$text = trim($update['message']['text']);

$tasks = file_exists(TODO_FILE) ? json_decode(file_get_contents(TODO_FILE), true) : [];
if (!isset($tasks[$chatId])) $tasks[$chatId] = [];

if ($text === '/start') {
    sendMessage($chatId, "Welcome! Use these commands:\n/add <task> - Add task\n/list - Show tasks\n/done <task#> - Complete task\n/delete <task#> - Delete task");
} elseif (str_starts_with($text, '/add ')) {
    $task = substr($text, 5);
    $tasks[$chatId][] = ["task" => $task, "done" => false];
    saveTasks($tasks);
    sendMessage($chatId, "Task added: $task");
} elseif ($text === '/list') {
    $reply = "Your tasks:\n";
    foreach ($tasks[$chatId] as $index => $task) {
        $status = $task['done'] ? '✅' : '❌';
        $reply .= "$index. $status {$task['task']}\n";
    }
    sendMessage($chatId, $reply ?: "No tasks yet!");
} elseif (str_starts_with($text, '/done ')) {
    $index = (int)substr($text, 6);
    if (isset($tasks[$chatId][$index])) {
        $tasks[$chatId][$index]['done'] = true;
        saveTasks($tasks);
        sendMessage($chatId, "Task marked as done: {$tasks[$chatId][$index]['task']}");
    } else {
        sendMessage($chatId, "Invalid task number!");
    }
} elseif (str_starts_with($text, '/delete ')) {
    $index = (int)substr($text, 8);
    if (isset($tasks[$chatId][$index])) {
        $task = $tasks[$chatId][$index]['task'];
        array_splice($tasks[$chatId], $index, 1);
        saveTasks($tasks);
        sendMessage($chatId, "Deleted: $task");
    } else {
        sendMessage($chatId, "Invalid task number!");
    }
} else {
    sendMessage($chatId, "Unknown command. Use /list to see your tasks.");
}

function sendMessage($chatId, $text) {
    global $apiUrl;
    file_get_contents($apiUrl . "sendMessage?" . http_build_query(["chat_id" => $chatId, "text" => $text]));
}

function saveTasks($tasks) {
    file_put_contents(TODO_FILE, json_encode($tasks));
}