<?php
// processar_pagamento.php

// 1. Configurações de CORS e Headers (ESSENCIAL PARA EVITAR ERRO 405 e CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// 2. Trata a requisição "OPTIONS" (Preflight) que os navegadores fazem antes do POST
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 3. Verifica se é um POST real
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["sucesso" => false, "mensagem" => "Método não permitido. Use POST."]);
    exit;
}

// 4. Recebe os dados
$input = file_get_contents("php://input");
$dados = json_decode($input, true);

if (!$dados) {
    echo json_encode(["sucesso" => false, "mensagem" => "Nenhum dado recebido ou JSON inválido."]);
    exit;
}

// --- DADOS DA YAMPI ---
$alias = "store39";
$token = "YuxLCumxFftaA1LlGzdInxFPqpLYnrODvL0BMHnG";
$secret_key = "sk_jGBmqP1AWTKsxHlNtrVOmjVCGrkske08M5X6F";
$api_url = "https://api.yampi.com.br/v1/orders";

// Formatação
$cpf = preg_replace('/\D/', '', $dados['cpf'] ?? '');
$telefone = preg_replace('/\D/', '', $dados['telefone'] ?? '');

// 5. Monta o Payload
$payload = [
    "customer" => [
        "name" => $dados['nome'],
        "email" => $dados['email'],
        "cpf" => $cpf,
        "phone" => [
            "number" => substr($telefone, 2),
            "area_code" => substr($telefone, 0, 2)
        ]
    ],
    "shipping" => [
        "price" => 0,
        "delivery_days" => 1,
        "address" => [
            "zipcode" => "01001000",
            "street" => "Entrega Digital",
            "number" => "1",
            "neighborhood" => "Digital",
            "city" => "São Paulo",
            "state" => "SP",
            "country" => "BR"
        ]
    ],
    "items" => [
        [
            "sku" => "PROD-FORENSE-001",
            "title" => "Pacote Mente Forense Premium",
            "quantity" => 1,
            "price" => 19.99
        ]
    ],
    "payments" => []
];

// Adiciona pagamento
if ($dados['metodo'] === 'pix') {
    $payload['payments'][] = ["payment_method" => "pix"];
} else {
    $payload['payments'][] = [
        "payment_method" => "credit_card",
        "credit_card" => [
            "number" => str_replace(' ', '', $dados['cartao']['numero']),
            "holder_name" => $dados['cartao']['nome'],
            "expiration_month" => explode('/', $dados['cartao']['validade'])[0],
            "expiration_year" => "20" . explode('/', $dados['cartao']['validade'])[1],
            "cvv" => $dados['cartao']['cvv'],
            "installments" => (int)$dados['cartao']['parcelas']
        ]
    ];
}

// 6. Envia para Yampi
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Alias: " . $alias,
    "Token: " . $token,
    "Key: " . $secret_key
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    echo json_encode(["sucesso" => false, "mensagem" => "Erro CURL: $curl_error"]);
    exit;
}

$res = json_decode($response, true);

if ($http_code == 201 || $http_code == 200) {
    $retorno = ["sucesso" => true, "metodo" => $dados['metodo']];
    if ($dados['metodo'] === 'pix') {
        // Ajuste conforme o retorno real da Yampi
        $transaction = $res['data']['resource']['transactions'][0] ?? null;
        $retorno['qrcode_imagem'] = $transaction['pix_qrcode_url'] ?? 'https://placehold.co/200?text=Aguardando+Yampi';
        $retorno['qrcode_copicola'] = $transaction['pix_qrcode_text'] ?? 'Erro no QrCode';
    }
    echo json_encode($retorno);
} else {
    $msg = $res['errors'][0]['message'] ?? ($res['message'] ?? 'Erro desconhecido');
    echo json_encode(["sucesso" => false, "mensagem" => $msg, "debug" => $res]);
}
?>