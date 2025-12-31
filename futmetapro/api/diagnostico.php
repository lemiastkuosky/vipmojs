<?php
// --- DIAGNÓSTICO PARA API-SPORTS (DIRETA) ---

$apiKey = 'd5a4e67034fdd6786c26e57acb38ee05'; // Sua chave
$apiHost = 'v3.football.api-sports.io';        // Host direto

// Data de hoje
$date = date('Y-m-d'); 

echo "<h1>🕵️ Testando API Direta...</h1>";
echo "<strong>URL:</strong> https://{$apiHost}/fixtures?date={$date}<br>";
echo "<strong>Key:</strong> {$apiKey}<br><br>";

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://" . $apiHost . "/fixtures?date=" . $date,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_SSL_VERIFYPEER => false, // Ignora SSL local
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPHEADER => [
        "x-apisports-key: " . $apiKey, // Header específico da API Direta
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

// --- RESULTADO ---

if ($err) {
    echo "<h3>❌ Erro de Conexão (cURL):</h3>";
    echo $err;
} else {
    echo "<h3>✅ Resposta da API:</h3>";
    echo "<div style='background: #f4f4f4; padding: 15px; border: 1px solid #ddd; max-height: 400px; overflow: auto;'>";
    
    $json = json_decode($response, true);
    
    if ($json) {
        // Verifica erros comuns na resposta JSON
        if (isset($json['errors']) && !empty($json['errors'])) {
            echo "<strong style='color:red'>ERRO RETORNADO PELA API:</strong><br>";
            print_r($json['errors']);
        } elseif (isset($json['message'])) {
            echo "<strong style='color:orange'>AVISO DA API:</strong> " . $json['message'] . "<br>";
        } else {
            $qtd = $json['results'] ?? 0;
            echo "<strong>Status:</strong> Sucesso!<br>";
            echo "<strong>Jogos Encontrados:</strong> " . $qtd . "<br>";
        }
        echo "<hr><pre>" . print_r($json, true) . "</pre>";
    } else {
        echo "Resposta não é JSON válido (pode ser erro HTML):<br>";
        echo htmlspecialchars($response);
    }
    
    echo "</div>";
}
?>