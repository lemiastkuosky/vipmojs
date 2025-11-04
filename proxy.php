<?php
// --- PROXY FINAL - VERSÃO REVISADA PARA GARANTIR O CABEÇALHO CORS ---
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/html; charset=utf-8");

set_time_limit(60);

// --- PARÂMETROS DE BUSCA ---
$tipo_busca = isset($_GET['tipo']) ? $_GET['tipo'] : 'atrasados';
$codigo_loteria = isset($_GET['loteria']) ? trim(strip_tags($_GET['loteria'])) : '';

// --- PARÂMETRO DE DATA ADICIONADO ---
$data_busca = isset($_GET['data']) ? trim(strip_tags($_GET['data'])) : null;


if (empty($codigo_loteria)) {
    http_response_code(400);
    die('Erro: Loteria não especificada.');
}

$url_alvo = '';
$post_data = [];
$is_post = false; // Flag para saber se é POST ou GET

if ($tipo_busca == 'atrasados') {
    // --- LÓGICA DE ATRASADOS (NÃO MUDA) ---
    // Adicionado 'ba' que vi nos seus logs
    $loterias_permitidas = ['fd', 'rj', 'lk', 'ln', 'ba']; 
    if (!in_array($codigo_loteria, $loterias_permitidas)) {
        http_response_code(400);
        die('Erro: Loteria inválida para atrasados.');
    }
    
    $url_alvo = 'https://bichocerto.com/estatisticas/atrasados/grupo/load/';
    $post_data = [
        'l'  => $codigo_loteria,
        'p'  => '1',
        'e'  => 'all',
        'et' => 'Geral'
    ];
    $is_post = true; // Atrasados usa POST

} elseif ($tipo_busca == 'resultados') {
    // --- LÓGICA DE RESULTADOS (MODIFICADA) ---
    $mapa_urls = [
        'rj' => 'https://bichocerto.com/resultados/rj/para-todos/',
        'lk' => 'https://bichocerto.com/resultados/look/para-todos/',
        'fd' => 'https://bichocerto.com/resultados/federal/para-todos/',
        'ln' => 'https://bichocerto.com/resultados/nacional/para-todos/', // ADICIONADO
        'ba' => 'https://bichocerto.com/resultados/bahia/para-todos/',   // ADICIONADO
    ];
    
    if (!isset($mapa_urls[$codigo_loteria])) {
        http_response_code(400);
        die('Erro: URL de resultados não definida para esta loteria: ' . $codigo_loteria);
    }
    
    $url_alvo = $mapa_urls[$codigo_loteria];
    
    // --- INÍCIO DA MODIFICAÇÃO PARA DATA ---
    if ($data_busca) {
        // Valida o formato da data (YYYY-MM-DD)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_busca)) {
            // Anexa a data e a barra final à URL
            $url_alvo = $url_alvo . $data_busca . '/';
        } else {
            http_response_code(400);
            die('Erro: Formato de data inválido. Use AAAA-MM-DD.');
        }
    }
    // Se $data_busca for nulo, a $url_alvo original é usada (que o site entende como "hoje")
    // --- FIM DA MODIFICAÇÃO PARA DATA ---
    
    $is_post = false; // Resultados usa GET

} else {
    http_response_code(400);
    die('Erro: Tipo de busca inválido.');
}

// --- LÓGICA cURL (SEM MUDANÇAS) ---
$ch = curl_init($url_alvo);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Mantido como false

if ($is_post) {
    // Se for POST (atrasados), envia os dados POST
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
} else {
    // Se for GET (resultados), não envia POST_POSTFIELDS
    curl_setopt($ch, CURLOPT_HTTPGET, true);
}

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($response === false || $http_code != 200) {
    // Adiciona a URL ao erro para facilitar a depuração
    http_response_code(502); // Bad Gateway
    die('Erro ao buscar conteudo da URL: ' . $url_alvo . ' | Código: ' . $http_code . ' | Erro cURL: ' . $curl_error);
}

echo $response;
?>