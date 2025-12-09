<?php
// processar_pagamento.php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$input = file_get_contents("php://input");
$dados = json_decode($input, true);

if (!$dados) {
    echo json_encode(["sucesso" => false, "mensagem" => "Dados inválidos."]);
    exit;
}

// --- DADOS DA YAMPI (CONFIGURADOS) ---
$alias = "store39";
$token = "YuxLCumxFftaA1LlGzdInxFPqpLYnrODvL0BMHnG";
$secret_key = "sk_jGBmqP1AWTKsxHlNtrVOmjVCGrkske08M5X6F";

// Endpoint de Criação de Pedido da Yampi
$api_url = "https://api.yampi.com.br/v1/orders";

// Formatação de valores
$cpf = preg_replace('/\D/', '', $dados['cpf']);
$telefone = preg_replace('/\D/', '', $dados['telefone']);
$valor_total = 19.99; // Valor do produto

// 1. Monta o Payload (Corpo do Pedido)
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
        // Dummy Shipping para produto digital (Yampi exige endereço)
        "price" => 0,
        "delivery_days" => 1,
        "address" => [
            "zipcode" => "01001000", // CEP Genérico SP
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
            "price" => $valor_total
        ]
    ],
    "payments" => []
];

// 2. Adiciona o Pagamento (Pix ou Cartão)
if ($dados['metodo'] === 'pix') {
    $payload['payments'][] = [
        "payment_method" => "pix"
    ];
} else {
    // CARTÃO: A Yampi idealmente exige tokenização no front via JS.
    // Tentando envio direto (pode ser bloqueado dependendo da config da loja)
    $payload['payments'][] = [
        "payment_method" => "credit_card",
        "credit_card" => [
            "number" => str_replace(' ', '', $dados['cartao']['numero']),
            "holder_name" => $dados['cartao']['nome'],
            "expiration_month" => explode('/', $dados['cartao']['validade'])[0],
            "expiration_year" => "20" . explode('/', $dados['cartao']['validade'])[1], // assume 20xx
            "cvv" => $dados['cartao']['cvv'],
            "installments" => (int)$dados['cartao']['parcelas']
        ]
    ];
}

// 3. Dispara para Yampi
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
curl_close($ch);

$res = json_decode($response, true);

// 4. Analisa a Resposta
if ($http_code == 201 || $http_code == 200) {
    // Sucesso
    $retorno = ["sucesso" => true, "metodo" => $dados['metodo']];

    if ($dados['metodo'] === 'pix') {
        // Tenta pegar o QR Code na estrutura da Yampi
        // A estrutura pode variar, geralmente vem dentro de 'transactions' ou 'pix_qrcode'
        $transaction = $res['data']['resource']['transactions'][0] ?? null;
        
        $retorno['qrcode_imagem'] = $transaction['pix_qrcode_url'] ?? 'https://placehold.co/200?text=Erro+QR';
        $retorno['qrcode_copicola'] = $transaction['pix_qrcode_text'] ?? 'Erro ao gerar copia e cola';
    } else {
        // Cartão
        $retorno['redirect_url'] = "obrigado.html"; 
    }
    
    echo json_encode($retorno);

} else {
    // Erro
    $msg = $res['errors'][0]['message'] ?? ($res['message'] ?? 'Erro desconhecido na Yampi');
    echo json_encode(["sucesso" => false, "mensagem" => $msg, "debug" => $res]);
}
?>