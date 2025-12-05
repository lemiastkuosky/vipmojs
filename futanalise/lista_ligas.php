<?php
// lista_ligas.php
// Script para listar todas as ligas disponíveis nas suas APIs

// 1. Carrega as chaves do seu arquivo de config
$configFile = 'config.json';
$config = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];

$apiKeyOdds = isset($config['api_key_odds']) ? $config['api_key_odds'] : '';
$apiKeyFootball = isset($config['api_key_football']) ? $config['api_key_football'] : '';

echo "<h1>🔍 Consultor de Ligas</h1>";

// ==================================================================
// CONSULTA API 1: THE ODDS API
// ==================================================================
echo "<h2>1. Ligas da 'The Odds API' (API 1)</h2>";

if ($apiKeyOdds) {
    $url = "https://api.the-odds-api.com/v4/sports/?apiKey=$apiKeyOdds";
    $json = @file_get_contents($url);
    
    if ($json) {
        $data = json_decode($json, true);
        echo "<textarea style='width:100%; height:200px; font-family:monospace; background:#111; color:#0f0;'>";
        foreach ($data as $sport) {
            // Ignora esportes que não são futebol se quiser limpar a lista
            // if ($sport['group'] != 'Soccer') continue; 
            
            echo "'⚽ " . $sport['title'] . " (" . $sport['group'] . ")' => '" . $sport['key'] . "',\n";
        }
        echo "</textarea>";
        echo "<p><small>Copie as linhas acima e cole no seu array <b>\$ligas_api</b>.</small></p>";
    } else {
        echo "<p style='color:red'>Erro ao conectar na API 1. Verifique a chave.</p>";
    }
} else {
    echo "<p style='color:orange'>Chave da API 1 não encontrada no config.json</p>";
}

echo "<hr>";

// ==================================================================
// CONSULTA API 2: API-FOOTBALL
// ==================================================================
echo "<h2>2. Ligas da 'API-Football' (API 2)</h2>";
echo "<p>Mostrando apenas ligas ativas. Use Ctrl+F para buscar o país (ex: Japan, Australia).</p>";

if ($apiKeyFootball) {
    // Busca todas as ligas disponíveis
    $url = "https://v3.football.api-sports.io/leagues";
    
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "x-rapidapi-key: $apiKeyFootball\r\n" .
                        "x-rapidapi-host: v3.football.api-sports.io\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $json = @file_get_contents($url, false, $context);
    
    if ($json) {
        $data = json_decode($json, true);
        
        if (isset($data['response'])) {
            // Vamos ordenar por PAÍS para facilitar sua busca
            $ligas = $data['response'];
            usort($ligas, function($a, $b) {
                return strcmp($a['country']['name'], $b['country']['name']);
            });

            echo "<textarea style='width:100%; height:400px; font-family:monospace; background:#222; color:#fff;'>";
            foreach ($ligas as $item) {
                $pais = $item['country']['name'];
                $nome = $item['league']['name'];
                $id = $item['league']['id'];
                
                // Formatação pronta para seu código
                echo "'$pais - $nome' => 'API2_$id',\n";
            }
            echo "</textarea>";
             echo "<p><small>Copie as linhas acima e cole no seu array <b>\$ligas_api</b>.</small></p>";
        } else {
            echo "<p style='color:red'>API respondeu, mas sem dados. Verifique limites.</p>";
        }
    } else {
        echo "<p style='color:red'>Erro ao conectar na API 2. Verifique a chave.</p>";
    }
} else {
    echo "<p style='color:orange'>Chave da API 2 não encontrada no config.json</p>";
}
?>