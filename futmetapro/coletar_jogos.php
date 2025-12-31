<?php
date_default_timezone_set('America/Sao_Paulo');

// --- CONFIGURAÇÕES ---
$apiKey = 'd5a4e67034fdd6786c26e57acb38ee05';
$apiHost = 'v3.football.api-sports.io';
$firebaseUrl = 'https://futanalise-121f9-default-rtdb.firebaseio.com'; 
$brainFile = 'ia_brain.json'; // Arquivo onde a IA guarda o aprendizado

// --- 1. FUNÇÕES DE INTELIGÊNCIA (O CÉREBRO) ---

function carregarCerebro() {
    global $brainFile;
    if (file_exists($brainFile)) {
        return json_decode(file_get_contents($brainFile), true);
    }
    return []; // Cérebro vazio
}

function salvarCerebro($dados) {
    global $brainFile;
    file_put_contents($brainFile, json_encode($dados, JSON_PRETTY_PRINT));
}

// Função que aprende com os erros e acertos passados
function treinarIA($jogosDoDia, &$cerebro) {
    $aprendeuAlgo = false;
    
    foreach ($jogosDoDia as $jogo) {
        // Pula jogos que não acabaram ou que a IA não analisou
        if (!in_array($jogo['info']['status_short'], ['FT', 'AET', 'PEN'])) continue;
        if (!isset($jogo['analise']['prob_over05_ht'])) continue;

        $leagueId = $jogo['liga']['id'];
        
        // Inicializa a liga no cérebro se não existir (Começa com nota 0 - Neutro)
        if (!isset($cerebro[$leagueId])) {
            $cerebro[$leagueId] = ['score_ht' => 0, 'score_2t' => 0, 'score_ft' => 0];
        }

        // --- RECUPERA O QUE A IA DISSE ---
        $pHT = $jogo['analise']['prob_over05_ht'];
        $p2T = $jogo['analise']['prob_over05_2t'];
        $pFT = $jogo['analise']['prob_over05_ft'];

        // --- RECUPERA O QUE ACONTECEU ---
        $gHT = ($jogo['placar']['ht_casa'] ?? 0) + ($jogo['placar']['ht_fora'] ?? 0);
        $gFT = ($jogo['placar']['casa'] ?? 0) + ($jogo['placar']['fora'] ?? 0);
        $g2T = $gFT - $gHT; if ($g2T < 0) $g2T = 0;

        // --- AJUSTE DE PESOS (PUNIÇÃO OU RECOMPENSA) ---
        
        // Se a IA confiou no HT (>55%)
        if ($pHT >= 55) {
            if ($gHT > 0) {
                $cerebro[$leagueId]['score_ht'] += 0.5; // Recompensa pequena por acerto
            } else {
                $cerebro[$leagueId]['score_ht'] -= 2.0; // Punição GRANDE por erro (aprende a ter medo)
                $aprendeuAlgo = true;
            }
        }

        // Se a IA confiou no 2T (>65%)
        if ($p2T >= 65) {
            if ($g2T > 0) {
                $cerebro[$leagueId]['score_2t'] += 0.5;
            } else {
                $cerebro[$leagueId]['score_2t'] -= 2.0;
                $aprendeuAlgo = true;
            }
        }
    }
    return $aprendeuAlgo;
}

// --- FIM DAS FUNÇÕES IA ---

// Controle de dias (-3 a +3)
$diasAtras = 3; 
$diasFrente = 3;

echo "<h2>🧠 IA Trabalhando...</h2>";

// 1. Carrega a memória
$cerebroIA = carregarCerebro();
echo "Memória carregada. A IA conhece " . count($cerebroIA) . " ligas.<br>";

// 2. Loop de Atualização
for ($i = -$diasAtras; $i <= $diasFrente; $i++) {
    
    $date = date('Y-m-d', strtotime("$i days"));
    echo "<hr>📅 <strong>$date</strong> ";

    // BUSCA NO FIREBASE PRIMEIRO (Para treinar com dados existentes)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $firebaseUrl . "/jogos/" . $date . ".json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $existingData = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // --- FASE DE TREINAMENTO (SÓ NOS DIAS PASSADOS) ---
    if ($i < 0 && $existingData) {
        $treinou = treinarIA($existingData, $cerebroIA);
        if ($treinou) echo " <span style='color:blue'>[Aprendendo com erros...]</span>";
        else echo " [Auditado]";
        // Não precisa baixar da API de novo se for passado antigo, mas vamos baixar para atualizar placares recentes
    }

    // --- FASE DE COLETA E PREVISÃO ---
    // Baixa da API
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://" . $apiHost . "/fixtures?date=" . $date,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => ["x-apisports-key: " . $apiKey]
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    $data = json_decode($response, true);

    if (isset($data['errors']) && !empty($data['errors'])) { 
        echo "❌ Erro API.<br>"; continue; 
    }

    $jogosTratados = [];

    if(($data['results'] ?? 0) > 0) {
        foreach ($data['response'] as $jogo) {
            $idJogo = $jogo['fixture']['id'];
            $idLiga = $jogo['league']['id'];
            
            // PRESERVAR ANALISE SE JÁ EXISTIR (Para não mudar o palpite no meio do dia)
            // Se já temos esse jogo salvo e analisado, mantemos a probabilidade original
            // para sermos honestos na auditoria.
            $probHT = 0; $prob2T = 0; $probFT = 0;
            $jaAnalisado = false;

            if (isset($existingData[$idJogo]['analise']['processada']) && $existingData[$idJogo]['analise']['processada'] == true) {
                $probHT = $existingData[$idJogo]['analise']['prob_over05_ht'];
                $prob2T = $existingData[$idJogo]['analise']['prob_over05_2t'];
                $probFT = $existingData[$idJogo]['analise']['prob_over05_ft'];
                $jaAnalisado = true;
            } else {
                // --- NOVA ANÁLISE USANDO O CÉREBRO ---
                srand($idJogo);
                
                // Base aleatória (40 a 80)
                $baseHT = rand(40, 80);
                $base2T = rand(40, 80);
                $baseFT = rand(50, 90);

                // Aplica a Inteligência (Peso da Liga)
                // Se o score for negativo (muitos erros), diminui a chance.
                $pesoHT = $cerebroIA[$idLiga]['score_ht'] ?? 0;
                $peso2T = $cerebroIA[$idLiga]['score_2t'] ?? 0;

                // O ajuste soma ou subtrai da probabilidade
                $probHT = $baseHT + ($pesoHT * 2); // Multiplica o peso para ter impacto
                $prob2T = $base2T + ($peso2T * 2);
                $probFT = $baseFT; // FT mantemos mais estável por enquanto

                // Limites (0 a 99)
                if($probHT > 99) $probHT = 99; if($probHT < 1) $probHT = 1;
                if($prob2T > 99) $prob2T = 99; if($prob2T < 1) $prob2T = 1;
                
                srand(); 
            }

            // Monta o objeto
            $jogosTratados[$idJogo] = [
                'info' => [
                    'id' => $idJogo,
                    'timestamp' => $jogo['fixture']['timestamp'],
                    'status_short' => $jogo['fixture']['status']['short'], 
                    'tempo_decorrido' => $jogo['fixture']['status']['elapsed'],
                ],
                'liga' => [
                    'id' => $idLiga,
                    'nome' => $jogo['league']['name'],
                    'pais' => $jogo['league']['country'],
                    'season' => $jogo['league']['season']
                ],
                'times' => [
                    'casa' => $jogo['teams']['home']['name'],
                    'casa_id' => $jogo['teams']['home']['id'],
                    'fora' => $jogo['teams']['away']['name'],
                    'fora_id' => $jogo['teams']['away']['id']
                ],
                'placar' => [
                    'casa' => $jogo['goals']['home'] ?? 0,
                    'fora' => $jogo['goals']['away'] ?? 0,
                    'ht_casa' => $jogo['score']['halftime']['home'] ?? 0,
                    'ht_fora' => $jogo['score']['halftime']['away'] ?? 0
                ],
                'analise' => [
                    'processada' => true,
                    'prob_over05_ht' => $probHT,
                    'prob_over05_2t' => $prob2T,
                    'prob_over05_ft' => $probFT
                ]
            ];
        }
        
        // Salva no Firebase
        if (!empty($jogosTratados)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $firebaseUrl . "/jogos/" . $date . ".json");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH'); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jogosTratados));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            curl_close($ch);
            echo " ✅";
        }
    } else {
        echo " ⚠️ Sem jogos.";
    }
}

// 3. Salva o cérebro atualizado
salvarCerebro($cerebroIA);
echo "<hr><h3>🧠 Cérebro da IA atualizado com sucesso!</h3>";
?>