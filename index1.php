<?php
// === CONFIGURAÇÃO ===
$token = "7386955755:AAEhkG1hgwRG4t5Esl8n-Tawlo1rtoARKZY"; // Seu Token inserido aqui
$website = "https://api.telegram.org/bot" . $token;

// Recebe os dados do Telegram
$input = file_get_contents('php://input');
$update = json_decode($input, TRUE);

// Se acessar pelo navegador (tela branca), avisa que está OK e para
if (!$update) {
    echo "Bot configurado com sucesso! Agora configure o Webhook.";
    exit;
}

// === LÓGICA DO BOT ===

$chatId = null;
$message = null;

// Verifica se é uma mensagem de texto
if (isset($update['message'])) {
    $chatId = $update['message']['chat']['id'];
    $message = isset($update['message']['text']) ? $update['message']['text'] : '';
    
    // Cria botões para teste
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => 'Opção A', 'callback_data' => 'botao_a'],
                ['text' => 'Opção B', 'callback_data' => 'botao_b']
            ]
        ]
    ];
    $encodedKeyboard = json_encode($keyboard);

    // Responde ao usuário
    $text = "Olá! O bot está funcionando. Você disse: " . $message;
    sendMessage($chatId, $text, $encodedKeyboard);
}

// Verifica se é um clique no botão
if (isset($update['callback_query'])) {
    $callbackId = $update['callback_query']['id'];
    $chatId = $update['callback_query']['message']['chat']['id'];
    $data = $update['callback_query']['data'];

    // Avisa o Telegram que o clique foi recebido (para parar de carregar)
    file_get_contents($website . "/answerCallbackQuery?callback_query_id=" . $callbackId);

    // Manda a resposta do botão
    sendMessage($chatId, "Você clicou no botão: " . $data);
}

// Função simples para enviar mensagem
function sendMessage($chatId, $text, $keyboard = null) {
    global $website;
    $url = $website . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($text);
    if ($keyboard) {
        $url .= "&reply_markup=" . $keyboard;
    }
    file_get_contents($url);
}
?>