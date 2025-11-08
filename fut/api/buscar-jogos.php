<?php
// 1. CONFIGURAÇÃO
// Coloque sua NOVA chave da API aqui
$apiKey = "SUA_NOVA_CHAVE_API_VAI_AQUI"; 
$apiHost = "soccer-data6.p.rapidapi.com";

// URL para buscar jogos AO VIVO. 
// Verifique na documentação do RapidAPI se esta URL está correta.
$apiUrl = "https://soccer-data6.p.rapidapi.com/soccerdata/matches?status=live";

// 2. PREPARA A CHAMADA PARA A API
$headers = [
    "X-Rapidapi-Key: " . $apiKey,
    "X-Rapidapi-Host: " . $apiHost
];

// 3. FAZ A CHAMADA (USANDO cURL)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Desativa a verificação de SSL (útil em localhost, mas não ideal em produção)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$resposta_string = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 4. VERIFICA A RESPOSTA DA API
if ($http_code != 200) {
    // Se a API deu erro (ex: 403 Forbidden, 401 Unauthorized), envia o erro
    header('Content-Type: application/json');
    http_response_code($http_code); // Envia o mesmo código de erro (ex: 403)
    echo json_encode(['erro' => 'API do RapidAPI falhou.', 'codigo' => $http_code, 'resposta' => $resposta_string]);
    exit; // Para o script
}

// Decodifica o JSON da API
$dados_api = json_decode($resposta_string, true);

// IMPORTANTE: Veja como a API retorna os dados. 
// Às vezes é {'data': [...]}, às vezes é só [...]. Ajuste aqui.
$jogos_ao_vivo = $dados_api['data'] ?? $dados_api ?? [];

// 5. FAZ O FILTRO
$jogos_filtrados = [];
foreach ($jogos_ao_vivo as $jogo) {
    // Use '?? null' para evitar erros se a chave não existir
    $minuto = $jogo['status']['minute'] ?? null;
    $placar_casa = $jogo['homeScore']['current'] ?? null;
    $placar_fora = $jogo['awayScore']['current'] ?? null;
    
    // Só processa se tiver todos os dados
    if ($minuto !== null && $placar_casa !== null && $placar_fora !== null) {
        $diferenca_gols = abs($placar_casa - $placar_fora);

        // --- SEU FILTRO ---
        if (($minuto >= 70 && $minuto <= 80) && ($diferenca_gols >= 2)) {
            
            // Adiciona ao array de resultados
            $jogos_filtrados[] = [
                'casa' => $jogo['homeTeam']['name'] ?? 'Time Casa',
                'fora' => $jogo['awayTeam']['name'] ?? 'Time Fora',
                'placar' => $placar_casa . ' - ' . $placar_fora,
                'minuto' => $minuto
            ];
        }
    }
}

// 6. DEVOLVE OS DADOS FILTRADOS PARA O JAVASCRIPT
// Isso define que a resposta é JSON (obrigatório)
header('Content-Type: application/json');
echo json_encode($jogos_filtrados);

?>