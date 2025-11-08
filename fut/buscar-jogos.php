<?php
// Exemplo de Back-End (buscar-jogos.php)

// 2. A SUA CHAVE SECRETA FICA AQUI, SEGURA NO SERVIDOR
$apiKey = "7a84961df5mshfb0f31d390ef9edp154980jsn707aec9bcb35"; 
$apiHost = "soccer-data6.p.rapidapi.com";
$apiUrl = "https://soccer-data6.p.rapidapi.com/soccerdata/matches?status=live";

$headers = [
    "X-Rapidapi-Key: " . $apiKey,
    "X-Rapidapi-Host: " . $apiHost
];

// 3. O SEU servidor chama o RapidAPI
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$resposta = curl_exec($ch);
curl_close($ch);

$dados = json_decode($resposta, true);
$jogos_ao_vivo = $dados['data'] ?? []; // Ajuste conforme a resposta da API

// 4. O SEU servidor faz o filtro
$jogos_filtrados = [];
foreach ($jogos_ao_vivo as $jogo) {
    $minuto = $jogo['status']['minute'] ?? null;
    $placar_casa = $jogo['homeScore']['current'] ?? null;
    $placar_fora = $jogo['awayScore']['current'] ?? null;

    if ($minuto !== null && $placar_casa !== null && $placar_fora !== null) {
        $diferenca_gols = abs($placar_casa - $placar_fora);

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

// 5. O SEU servidor devolve SÓ OS DADOS FILTRADOS para o JavaScript
header('Content-Type: application/json');
echo json_encode($jogos_filtrados);

?>