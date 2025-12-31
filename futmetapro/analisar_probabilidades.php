<?php
// --- CONFIGURAÇÕES ---
$apiKey = 'd5a4e67034fdd6786c26e57acb38ee05';
$apiHost = 'v3.football.api-sports.io';
$firebaseUrl = 'https://futanalise-121f9-default-rtdb.firebaseio.com';
$date = date('Y-m-d'); 

// --- FUNÇÃO DE BUSCA ROBUSTA ---
function getTeamStats($teamId, $leagueId, $season) {
    global $apiKey, $apiHost;
    
    $url = "https://{$apiHost}/teams/statistics?season={$season}&team={$teamId}&league={$leagueId}";
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => ["x-apisports-key: {$apiKey}"]
    ]);
    
    $response = curl_exec($curl);
    curl_close($curl);
    
    return json_decode($response, true);
}

// 1. Ler jogos do Firebase
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $firebaseUrl . "/jogos/" . $date . ".json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$jogos = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!$jogos) die("Nenhum jogo no banco. Rode o coletar_jogos.php primeiro.");

echo "<h2>🧠 Análise Matemática Inteligente</h2>";

$updates = [];
$contador_requests = 0;
$limite_seguranca = 5; // Analisa 5 jogos por vez

foreach ($jogos as $id => $jogo) {
    
    // Filtros: Pula se já acabou ou já foi processado
    if ($jogo['info']['status_short'] == 'FT' || ($jogo['analise']['processada'] ?? false) == true) {
        continue;
    }

    if ($contador_requests >= $limite_seguranca) break;

    $seasonOriginal = $jogo['liga']['season'] ?? 2025;
    $ligaId = $jogo['liga']['id'];
    $casaId = $jogo['times']['casa_id'];
    $foraId = $jogo['times']['fora_id'];

    echo "<hr>Analisando: <strong>{$jogo['times']['casa']} (id: $casaId) vs {$jogo['times']['fora']} (id: $foraId)</strong><br>";

    // --- TENTATIVA 1: Season Original ---
    echo "Tentando Season $seasonOriginal... ";
    $statsCasa = getTeamStats($casaId, $ligaId, $seasonOriginal);
    $statsFora = getTeamStats($foraId, $ligaId, $seasonOriginal);
    $contador_requests += 2;

    // Se falhar, tenta Season Anterior (Correção automática)
    if (empty($statsCasa['response'])) {
        $seasonBackup = $seasonOriginal - 1;
        echo "❌ Falhou. Tentando Season $seasonBackup... ";
        
        $statsCasa = getTeamStats($casaId, $ligaId, $seasonBackup);
        $statsFora = getTeamStats($foraId, $ligaId, $seasonBackup);
        $contador_requests += 2;
    }

    // --- VERIFICAÇÃO FINAL ---
    if (isset($statsCasa['response']['goals']['for'])) {
        
        // SUCESSO! TEMOS DADOS
        $media_gols_casa = $statsCasa['response']['goals']['for']['average']['home'];
        $media_gols_fora = $statsFora['response']['goals']['for']['average']['away'];
        $media_sofridos_casa = $statsCasa['response']['goals']['against']['average']['home'];
        $media_sofridos_fora = $statsFora['response']['goals']['against']['average']['away'];

        // Tratamento para zeros (evitar divisão por zero ou dados vazios)
        $media_gols_casa = (float)$media_gols_casa;
        $media_gols_fora = (float)$media_gols_fora;
        
        // Cálculo do Índice
        $forca_ataque = ($media_gols_casa + $media_gols_fora);
        $fraqueza_defesa = ($media_sofridos_casa + $media_sofridos_fora);
        
        // Ajuste fino da fórmula
        $indice = ($forca_ataque * 0.6) + ($fraqueza_defesa * 0.4);
        
        // Probabilidades Estimadas
        $prob_ft = min(99, ($indice / 1.1) * 100); 
        $prob_ft = max(5, $prob_ft); // Mínimo 5%

        $prob_ht = $prob_ft * 0.55; // HT costuma ser mais difícil
        $prob_2t = $prob_ft * 0.85; // 2T costuma ter mais gols

        $updates[$id . '/analise'] = [
            'processada' => true,
            'prob_over05_ht' => round($prob_ht),
            'prob_over05_2t' => round($prob_2t),
            'prob_over05_ft' => round($prob_ft)
        ];

        echo "<strong style='color:green'>✅ SUCESSO!</strong><br>";
        echo "Médias: Casa ($media_gols_casa) + Fora ($media_gols_fora) = Prob FT: " . round($prob_ft) . "%<br>";

    } else {
        // MOSTRA O ERRO REAL (DEBUG)
        echo "<strong style='color:red'>❌ FALHA DEFINITIVA.</strong><br>";
        echo "Resposta da API (Casa): <pre>" . print_r($statsCasa, true) . "</pre><br>";
    }
    
    sleep(1); // Respira
}

// 4. Salvar
if (!empty($updates)) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $firebaseUrl . "/jogos/" . $date . ".json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updates));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
    echo "<br>💾 Firebase atualizado com sucesso!";
} else {
    echo "<br>Fim do ciclo (Nenhum dado salvo).";
}
?>