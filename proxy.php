<?php
// --- PROXY FINAL - VERSÃO REVISADA PARA GARANTIR O CABEÇALHO CORS ---
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/html; charset=utf-8");

set_time_limit(60);

// --- NOVOS PARÂMETROS ---
$tipo_busca = isset($_GET['tipo']) ? $_GET['tipo'] : 'atrasados';
$codigo_loteria = isset($_GET['loteria']) ? trim(strip_tags($_GET['loteria'])) : '';

if (empty($codigo_loteria)) {
    http_response_code(400);
    die('Erro: Loteria não especificada.');
}

$url_alvo = '';
$post_data = [];
$is_post = false; // Flag para saber se é POST ou GET

if ($tipo_busca == 'atrasados') {
    // --- LÓGICA ANTIGA (ATRASADOS) ---
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
    // --- LÓGICA NOVA (RESULTADOS) ---
    // Mapeia o código da loteria para a URL da página de resultados
    $mapa_urls = [
        'rj' => 'https://bichocerto.com/resultados/rj/para-todos/',
        'lk' => 'https://bichocerto.com/resultados/look/para-todos/',
        'fd' => 'https://bichocerto.com/resultados/federal/para-todos/',
        // Adicione outras URLs aqui conforme necessário
        // 'sp' => 'https://bichocerto.com/resultados/sp/para-todos/',
    ];
    
    if (!isset($mapa_urls[$codigo_loteria])) {
        http_response_code(400);
        die('Erro: URL de resultados não definida para esta loteria.');
    }
    
    $url_alvo = $mapa_urls[$codigo_loteria];
    $is_post = false; // Resultados usa GET
} else {
    http_response_code(400);
    die('Erro: Tipo de busca inválido.');
}

// --- LÓGICA cURL ATUALIZADA ---
$ch = curl_init($url_alvo);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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
    http_response_code(502);
    die('Erro ao buscar conteúdo. Código: ' . $http_code . ' Erro cURL: ' . $curl_error);
}

echo $response;
?>