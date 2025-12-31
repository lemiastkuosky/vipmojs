<?php
// --- CONFIGURAÇÕES ---
$apiKey = 'd5a4e67034fdd6786c26e57acb38ee05';
$apiHost = 'v3.football.api-sports.io';
$firebaseUrl = 'https://futanalise-121f9-default-rtdb.firebaseio.com';
$date = date('Y-m-d'); 

// --- FUNÇÕES ---
function getJogosDisponiveis($teamId, $seasonInicial) {
    global $apiKey, $apiHost;
    $anosParaTentar = [$seasonInicial, 2025, 2024, 2023];
    $anosParaTentar = array_unique($anosParaTentar);

    foreach ($anosParaTentar as $ano) {
        $url = "https://{$apiHost}/fixtures?team={$teamId}&season={$ano}&status=FT";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => ["x-apisports-key: {$apiKey}"]
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $json = json_decode($response, true);

        if (isset($json['response']) && count($json['response']) > 0) {
            return ['dados' => $json['response'], 'ano_usado' => $ano];
        }
    }
    return false;
}

function calcularProbabilidadePoisson($listaJogos, $teamId) {
    $ultimosJogos = array_slice($listaJogos, -10); 
    $gols_feitos = 0;
    $gols_sofridos = 0;
    $qtd = 0;

    foreach ($ultimosJogos as $match) {
        $qtd++;
        if ($match['teams']['home']['id'] == $teamId) {
            $gols_feitos += $match['goals']['home'];
            $gols_sofridos += $match['goals']['away'];
        } else {
            $gols_feitos += $match['goals']['away'];
            $gols_sofridos += $match['goals']['home'];
        }
    }
    if ($qtd == 0) return false;
    return ['media_ataque' => $gols_feitos / $qtd, 'media_defesa' => $gols_sofridos / $qtd];
}

// --- LOOP PRINCIPAL ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $firebaseUrl . "/jogos/" . $date . ".json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$jogos = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!$jogos) die("Nenhum jogo no banco.");

echo "<h2>🧠 Análise Multi-Linhas (0.5 a 3.5)</h2>";

$updates = [];
$contador = 0;
$limite_execucao = 40; 

foreach ($jogos as $id => $jogo) {
    
    // IMPORTANTE: Removemos a trava de 'FT' para recalcular mesmo jogos que já acabaram
    // Pula apenas se JÁ tem as probabilidades NOVAS (campo prob_over35)
    if (isset($jogo['analise']['prob_over35'])) {
        continue;
    }

    if ($contador >= $limite_execucao) {
        echo "<br>⏸️ Limite de $limite_execucao atingido. Recarregue a página.";
        break;
    }

    $season = $jogo['liga']['season'] ?? 2025;
    $casaId = $jogo['times']['casa_id'];
    $foraId = $jogo['times']['fora_id'];

    echo "<hr>Analisando: {$jogo['times']['casa']} x {$jogo['times']['fora']}... ";

    $resCasa = getJogosDisponiveis($casaId, $season);
    $resFora = getJogosDisponiveis($foraId, $season);
    
    usleep(300000); // Delay menor

    if ($resCasa && $resFora) {
        $statsCasa = calcularProbabilidadePoisson($resCasa['dados'], $casaId);
        $statsFora = calcularProbabilidadePoisson($resFora['dados'], $foraId);

        // Expectativa de Gols (Lambda)
        $lambda_casa = ($statsCasa['media_ataque'] + $statsFora['media_defesa']) / 2;
        $lambda_fora = ($statsFora['media_ataque'] + $statsCasa['media_defesa']) / 2;
        $lambda_total = $lambda_casa + $lambda_fora;

        // --- CÁLCULO DE POISSON ACUMULADO ---
        // P(k) = (e^-λ * λ^k) / k!
        
        $p0 = exp(-$lambda_total); // Chance de 0 gols
        $p1 = ($lambda_total * $p0) / 1; // Chance de exatos 1 gol
        $p2 = ($lambda_total * $p1) / 2; // Chance de exatos 2 gols
        $p3 = ($lambda_total * $p2) / 3; // Chance de exatos 3 gols

        // Probabilidades OVER (Mais de...)
        $prob_over05 = (1 - $p0) * 100;                 // 100% - Chance de 0
        $prob_over15 = (1 - ($p0 + $p1)) * 100;         // 100% - Chance de 0 ou 1
        $prob_over25 = (1 - ($p0 + $p1 + $p2)) * 100;   // 100% - Chance de 0, 1 ou 2
        $prob_over35 = (1 - ($p0 + $p1 + $p2 + $p3)) * 100;

        // Probabilidades HT e 2T (Estimativas baseadas na liquidez)
        $prob_ht = $prob_over05 * 0.65;
        $prob_2t = $prob_over05 * 0.88;

        // Limites (Max 99, Min 1)
        $prob_over05 = min(99, max(1, $prob_over05));
        $prob_over15 = min(99, max(1, $prob_over15));
        $prob_over25 = min(99, max(1, $prob_over25));
        $prob_over35 = min(99, max(1, $prob_over35));

        $updates[$id . '/analise'] = [
            'processada' => true,
            'prob_over05_ht' => round($prob_ht, 1),
            'prob_over05_2t' => round($prob_2t, 1),
            // NOVOS DADOS
            'prob_over05_ft' => round($prob_over05, 1),
            'prob_over15_ft' => round($prob_over15, 1),
            'prob_over25_ft' => round($prob_over25, 1),
            'prob_over35_ft' => round($prob_over35, 1),
            'gols_esperados' => round($lambda_total, 2)
        ];

        echo "<span style='color:blue'>Calculado!</span> Gols Esp: ".round($lambda_total,2)." | Ov1.5: <strong>".round($prob_over15)."%</strong><br>";
        $contador++;

    } else {
        echo "<span style='color:red'>Sem dados.</span><br>";
    }
}

if (!empty($updates)) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $firebaseUrl . "/jogos/" . $date . ".json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updates));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
    echo "<br>💾 <strong>Novas Probabilidades Salvas!</strong>";
}
?>