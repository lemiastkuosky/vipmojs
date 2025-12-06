<?php
// api.php - Backend Inteligente (Busca Odds e Ligas)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$apiKey = '0b48ae993afe2d7029f915ddab0c6bdf'; // Sua chave

// Verifica a ação solicitada
$action = isset($_GET['action']) ? $_GET['action'] : 'odds';

if ($action === 'leagues') {
    // MODO 1: LISTAR TODAS AS LIGAS ATIVAS
    $url = "https://api.the-odds-api.com/v4/sports/?apiKey=$apiKey";
} else {
    // MODO 2: BUSCAR ODDS DE UMA LIGA ESPECÍFICA
    $sport = isset($_GET['sport']) ? $_GET['sport'] : 'soccer_brazil_campeonato';
    $regions = 'us,eu,uk';
    $markets = 'h2h';
    $url = "https://api.the-odds-api.com/v4/sports/$sport/odds/?apiKey=$apiKey&regions=$regions&markets=$markets&oddsFormat=decimal";
}

// Executa
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);

if ($response === false) {
    echo json_encode(["message" => "Erro cURL: " . curl_error($ch)]);
} else {
    echo $response;
}
curl_close($ch);
?>