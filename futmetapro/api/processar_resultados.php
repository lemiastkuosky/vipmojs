<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Analyst Pro | Accordion</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0f172a;       
            --secondary: #334155;     
            --bg-body: #f8fafc;       
            --border: #e2e8f0;        
            
            /* Cores */
            --green-strong: #16a34a;  
            --red-strong: #dc2626;    
            --green-light: #f0fdf4;   
            --red-light: #fef2f2;     
            --blue: #2563eb;          
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-body); 
            color: var(--secondary); 
            margin: 0; padding: 15px; font-size: 13px;
            display: flex; flex-direction: column; min-height: 100vh;
        }

        /* HEADER */
        .header-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .app-logo { font-size: 1.3rem; font-weight: 800; color: var(--primary); letter-spacing: -0.5px; }
        .logo-highlight { color: var(--blue); }
        .btn-update { background: var(--primary); color: #fff; border: none; width: 38px; height: 38px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: transform 0.1s; }
        .btn-update:active { transform: scale(0.95); }
        .btn-update.spinning { animation: spin 1s linear infinite; opacity: 0.7; pointer-events: none; }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        /* ABAS */
        .tabs-wrapper { overflow-x: auto; margin: 0 -15px 20px -15px; padding: 0 15px; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
        .tabs-container { display: flex; gap: 8px; }
        .tab-btn { background: #fff; border: 1px solid var(--border); padding: 8px 14px; border-radius: 100px; font-size: 0.8rem; font-weight: 600; color: #64748b; white-space: nowrap; cursor: pointer; display: flex; align-items: center; gap: 5px; flex-shrink: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
        .tab-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); box-shadow: 0 4px 10px rgba(15, 23, 42, 0.2); }
        .tab-count { font-size: 0.75em; opacity: 0.8; font-weight: 400; background: rgba(255,255,255,0.2); padding: 1px 5px; border-radius: 4px; }
        .tab-content { display: none; animation: fadeIn 0.3s ease-out; flex-grow: 1; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        /* STATS */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 25px; }
        .stat-card { background: #fff; padding: 12px 5px; border-radius: 12px; text-align: center; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .stat-val { font-size: 1.25rem; font-weight: 800; display: block; line-height: 1.1; margin-bottom: 3px; }
        .stat-lbl { font-size: 0.6rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .c-blue { color: var(--blue); } .c-green { color: var(--green-strong); } .c-red { color: var(--red-strong); }

        /* TOP 5 */
        .section-header { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px; display: flex; align-items: center; gap: 5px; }
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(145px, 1fr)); gap: 10px; margin-bottom: 30px; }
        .top-card { background: #fff; border-radius: 14px; padding: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); border: 1px solid var(--border); display: flex; flex-direction: column; justify-content: space-between; min-height: 120px; position: relative; }
        .card-win { background: var(--green-strong) !important; color: #fff !important; border: none; }
        .card-loss { background: var(--red-strong) !important; color: #fff !important; border: none; }
        .card-win .tc-teams, .card-loss .tc-teams { color: #fff; }
        .card-win .tc-bet, .card-loss .tc-bet { background: rgba(255,255,255,0.2); color: #fff; border: none; }
        .card-win .tc-res, .card-loss .tc-res { color: #fff; font-weight: 800; background: rgba(0,0,0,0.15); padding: 4px 8px; border-radius: 6px; }
        .card-win span, .card-loss span { color: rgba(255,255,255,0.7) !important; }
        .card-win .tc-conf, .card-loss .tc-conf { color: rgba(255,255,255,0.9); }
        .card-win .tc-time, .card-loss .tc-time { color: rgba(255,255,255,0.8); }

        .tc-header { display: flex; justify-content: space-between; font-size: 0.7rem; color: #94a3b8; margin-bottom: 8px; }
        .tc-conf { font-weight: 800; color: var(--blue); }
        .tc-teams { font-weight: 700; font-size: 0.9rem; color: var(--primary); margin-bottom: 10px; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tc-teams span { color: #cbd5e1; font-weight: 400; font-size: 0.8em; margin: 0 2px; }
        .tc-bet { background: #f1f5f9; border: 1px solid var(--border); color: #475569; padding: 6px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; text-align: center; margin-bottom: 8px; }
        .tc-res { font-size: 0.8rem; font-weight: 700; text-align: center; margin-top: auto; color: var(--primary); }

        /* --- LISTA SANFONA (ACCORDION) --- */
        .list-card { background: #fff; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        
        /* Cabeçalho da Tabela (Fixo) */
        thead th { text-align: left; padding: 12px 10px; background: #f8fafc; color: #64748b; font-size: 0.65rem; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid var(--border); }
        
        /* HEADER DA LIGA (Clicável) */
        .league-row { cursor: pointer; background-color: #f1f5f9; transition: background 0.2s; -webkit-tap-highlight-color: transparent; }
        .league-row:hover { background-color: #e2e8f0; }
        .league-cell { 
            padding: 10px 12px; font-size: 0.75rem; font-weight: 800; color: var(--secondary); 
            text-transform: uppercase; letter-spacing: 0.5px; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); 
            display: flex; align-items: center; justify-content: space-between;
        }
        .league-icon { font-size: 0.8rem; transition: transform 0.3s ease; color: #94a3b8; }
        
        /* Estado Aberto */
        .league-row.active .league-icon { transform: rotate(180deg); color: var(--primary); }
        .league-row.active { background-color: #fff; border-bottom: 2px solid var(--primary); }

        /* Corpo dos Jogos (Escondido por padrão ou aberto) */
        .league-body { display: none; }
        .league-body.show { display: table-row-group; animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

        td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }

        /* Colunas */
        .col-time { width: 45px; color: #94a3b8; font-weight: 600; font-size: 0.7rem; vertical-align: top; padding-top: 15px; }
        .col-game { width: auto; } 
        .col-res { width: 85px; text-align: right; vertical-align: top; padding-top: 15px; }

        .row-win { background-color: var(--green-light); }
        .row-loss { background-color: var(--red-light); }
        .row-win td, .row-loss td { opacity: 0.95; }

        .team-row { font-weight: 700; color: var(--primary); font-size: 0.85rem; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .vs-row { font-weight: 400; color: #94a3b8; font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ia-pill { display: inline-block; margin-top: 6px; font-size: 0.65rem; font-weight: 700; color: var(--blue); background: #eff6ff; padding: 2px 6px; border-radius: 4px; }
        .score-val { font-family: 'Inter', monospace; font-size: 0.85rem; font-weight: 700; color: var(--primary); margin-bottom: 6px; display: block; }
        
        .badge { padding: 4px 8px; border-radius: 6px; font-weight: 800; font-size: 0.6rem; display: inline-block; color: #fff; text-transform: uppercase; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 100%; text-align: center; }
        .bg-green { background: var(--green-strong); }
        .bg-red { background: var(--red-strong); }
        .bg-wait { background: #e2e8f0; color: #64748b; box-shadow: none; }
        .bg-live { background: #f59e0b; color: #fff; animation: pulse 2s infinite; }
        
        /* Tooltip */
        .tt-wrap { position: relative; display: inline-block; cursor: help; width: 100%; }
        .tt-box { visibility: hidden; position: absolute; right: 0; bottom: 120%; background: #1e293b; color: #fff; padding: 0; border-radius: 8px; width: 180px; font-size: 0.7rem; z-index: 100; opacity: 0; transition: 0.2s; text-align: left; box-shadow: 0 10px 20px rgba(0,0,0,0.3); pointer-events: none; }
        .tt-wrap:hover .tt-box { visibility: visible; opacity: 1; transform: translateY(-5px); }
        .tt-header { background: #0f172a; padding: 8px 10px; border-radius: 8px 8px 0 0; font-weight: 600; color: #f59e0b; border-bottom: 1px solid #334155; font-size: 0.7rem; line-height: 1.3; }
        .tt-body { padding: 8px 10px; }
        .tt-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .tt-high { color: #4ade80; font-weight: bold; }
        .tt-box::after { content: ""; position: absolute; top: 100%; right: 20px; border-width: 6px; border-style: solid; border-color: #1e293b transparent transparent transparent; }

        .empty-state { text-align: center; padding: 50px 20px; color: #94a3b8; }
        .empty-icon { font-size: 2rem; margin-bottom: 15px; display: block; opacity: 0.4; }
        .app-footer { margin-top: auto; padding: 30px 0; text-align: center; border-top: 1px solid var(--border); color: #94a3b8; font-size: 0.75rem; }
        .footer-credits strong { color: var(--primary); font-weight: 700; }
        .footer-version { margin-top: 5px; opacity: 0.6; font-size: 0.7rem; font-weight: 500; }
    </style>
</head>
<body>

<?php
date_default_timezone_set('America/Sao_Paulo');
$firebaseUrl = 'https://futanalise-121f9-default-rtdb.firebaseio.com';
$semana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

$diasAtras = -7; $diasFrente = 3;
$datasParaExibir = [];

for ($i = $diasAtras; $i <= $diasFrente; $i++) {
    $ts = strtotime("$i days");
    $d = date('Y-m-d', $ts);
    $nomeDia = $semana[date('w', $ts)];
    $diaMes = date('d/m', $ts);
    if ($i == 0) $label = "Hoje";
    elseif ($i == -1) $label = "Ontem";
    elseif ($i == 1) $label = "Amanhã";
    else $label = "$nomeDia $diaMes";
    $datasParaExibir[] = ['offset' => $i, 'date' => $d, 'label' => $label];
}
?>

<div class="header-area">
    <div class="app-logo">Analyst<span class="logo-highlight">Pro</span></div>
    
    <div style="display: flex; gap: 8px;">
        <a href="calc.html" class="btn-update" style="text-decoration: none; background-color: #334155;" title="Voltar para o Caixa">
            💰
        </a>

        <button id="btnUpdate" class="btn-update" onclick="atualizarJogosManual()" title="Atualizar">🔄</button>
    </div>
</div>

<div class="tabs-wrapper">
    <div class="tabs-container">
        <?php foreach ($datasParaExibir as $d): ?>
            <button id="btn-<?php echo $d['date']; ?>" 
                    class="tab-btn <?php echo ($d['offset'] == 0) ? 'active' : ''; ?>" 
                    onclick="openTab('tab-<?php echo $d['date']; ?>', this)">
                <?php echo $d['label']; ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<?php foreach ($datasParaExibir as $d): 
    $isActive = ($d['offset'] == 0) ? 'active' : '';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $firebaseUrl . "/jogos/" . $d['date'] . ".json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    $jogos = json_decode($result, true);
    curl_close($ch);

    $listaJogos = [];
    $melhoresDoDia = []; 
    $statsDia = ['greens' => 0, 'reds' => 0, 'total_fin' => 0];
    $qtdJogosDia = 0;
    $jogosPorLiga = [];

    if ($jogos) {
        foreach ($jogos as $j) {
            $pHT = $j['analise']['prob_over05_ht'] ?? 0;
            $p2T = $j['analise']['prob_over05_2t'] ?? 0;
            $pFT = $j['analise']['prob_over05_ft'] ?? 0;

            if ($pFT <= 0 && $pHT <= 0 && $p2T <= 0) continue;
            
            $target = ""; $conf = 0; $txtP = ""; $resumoIA = "Neutro";

            if ($pHT >= 55) { 
                $target="HT"; $conf=$pHT; $txtP="Gol 1º T"; 
                if($pHT >= 75) $resumoIA = "IA: Forte pressão inicial esperada.";
                else $resumoIA = "IA: Probabilidade de gol cedo.";
            }
            elseif ($p2T >= 65) { 
                $target="2T"; $conf=$p2T; $txtP="Gol 2º T"; 
                if($p2T >= 80) $resumoIA = "IA: Jogo aberto no final.";
                else $resumoIA = "IA: Tendência de gol tardio.";
            }
            elseif ($pFT >= 70) { 
                $target="FT"; $conf=$pFT; $txtP="Gol Jogo"; 
                $resumoIA = "IA: Segurança no over gols.";
            }
            else { continue; }

            $qtdJogosDia++;
            $j['ia'] = ['target' => $target, 'conf' => $conf, 'txt' => $txtP, 'resumo' => $resumoIA];
            $listaJogos[] = $j;
            $melhoresDoDia[] = $j;

            // Agrupa por Liga
            $chaveLiga = ($j['liga']['pais'] ?? 'Mundo') . " - " . ($j['liga']['nome'] ?? 'League');
            $jogosPorLiga[$chaveLiga][] = $j;

            $st = $j['info']['status_short'];
            if (in_array($st, ['FT', 'AET', 'PEN'])) {
                $gHT = ($j['placar']['ht_casa']??0)+($j['placar']['ht_fora']??0);
                $gFT = ($j['placar']['casa']??0)+($j['placar']['fora']??0);
                $g2T = $gFT - $gHT; if($g2T<0)$g2T=0;
                $win = false;
                if ($target=="HT" && $gHT>0) $win=true;
                elseif ($target=="2T" && $g2T>0) $win=true;
                elseif ($target=="FT" && $gFT>0) $win=true;
                if ($win) $statsDia['greens']++; else $statsDia['reds']++;
                $statsDia['total_fin']++;
            }
        }
        
        usort($melhoresDoDia, function($a, $b) { return $b['ia']['conf'] <=> $a['ia']['conf']; });
        $top5 = array_slice($melhoresDoDia, 0, 5);
        ksort($jogosPorLiga);
        foreach($jogosPorLiga as $c => $jg) {
            usort($jogosPorLiga[$c], function($a, $b) { return $a['info']['timestamp'] - $b['info']['timestamp']; });
        }
    }
    $taxa = ($statsDia['total_fin'] > 0) ? ($statsDia['greens'] / $statsDia['total_fin']) * 100 : 0;
?>

<script>
    (function() {
        var btn = document.getElementById("btn-<?php echo $d['date']; ?>");
        if(btn && <?php echo $qtdJogosDia; ?> > 0) {
            btn.innerHTML += " <span class='tab-count'><?php echo $qtdJogosDia; ?></span>";
        }
    })();
</script>

<div id="tab-<?php echo $d['date']; ?>" class="tab-content <?php echo $isActive; ?>">
    
    <?php if (empty($listaJogos)): ?>
        <div class="empty-state"><span class="empty-icon">📂</span>Sem oportunidades hoje.</div>
    <?php else: ?>

        <div class="stats-grid">
            <div class="stat-card"><span class="stat-val c-blue"><?php echo number_format($taxa, 0); ?>%</span><span class="stat-lbl">Acerto</span></div>
            <div class="stat-card"><span class="stat-val c-green"><?php echo $statsDia['greens']; ?></span><span class="stat-lbl">Greens</span></div>
            <div class="stat-card"><span class="stat-val c-red"><?php echo $statsDia['reds']; ?></span><span class="stat-lbl">Reds</span></div>
        </div>

        <?php if (!empty($top5)): ?>
            <div class="section-header">🔥 Destaques</div>
            <div class="cards-grid">
                <?php foreach ($top5 as $top): 
                    $st = $top['info']['status_short'];
                    $tg = $top['ia']['target'];
                    $isFin = in_array($st, ['FT','AET','PEN']);
                    $isLive = in_array($st, ['1H','2H','HT','ET']);
                    $gHT = ($top['placar']['ht_casa']??0)+($top['placar']['ht_fora']??0);
                    $gFT = ($top['placar']['casa']??0)+($top['placar']['fora']??0);
                    $g2T = $gFT - $gHT; if($g2T<0)$g2T=0;
                    $win = false; $cardClass = ""; $txtRes = "AGUARDANDO";
                    if ($isFin || $isLive) {
                        if ($tg=="HT" && $gHT>0) $win=true;
                        elseif ($tg=="2T" && $g2T>0) $win=true;
                        elseif ($tg=="FT" && $gFT>0) $win=true;
                        if($win) { $cardClass = "card-win"; $txtRes = "GREEN"; }
                        else { if($isFin) { $cardClass = "card-loss"; $txtRes = "RED"; } else { $txtRes = "LIVE"; } }
                    }
                ?>
                <div class="top-card <?php echo $cardClass; ?>">
                    <div class="tc-header"><span class="tc-time"><?php echo date('H:i', $top['info']['timestamp']); ?></span><span class="tc-conf"><?php echo $top['ia']['conf']; ?>%</span></div>
                    <div class="tc-teams"><?php echo $top['times']['casa']; ?><br><span><?php echo $top['times']['fora']; ?></span></div>
                    <div class="tc-bet"><?php echo $top['ia']['txt']; ?></div>
                    <div class="tc-res"><?php echo $txtRes; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="section-header">📋 Auditoria (<?php echo $qtdJogosDia; ?>)</div>
        
        <div class="list-card">
            <table>
                <thead>
                    <tr>
                        <th class="col-time">Hora</th>
                        <th class="col-game">Jogo & Análise</th>
                        <th class="col-res">Resultado</th>
                    </tr>
                </thead>
                
                <?php 
                $i_liga = 0;
                foreach ($jogosPorLiga as $nomeLiga => $jogosDaLiga): 
                    $i_liga++;
                    $idLiga = "liga-" . $d['date'] . "-" . $i_liga;
                ?>
                    <tbody class="league-row" onclick="toggleLiga('<?php echo $idLiga; ?>', this)">
                        <tr>
                            <td colspan="3">
                                <div class="league-cell">
                                    <?php echo $nomeLiga; ?>
                                    <span class="league-icon">▼</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>

                    <tbody id="<?php echo $idLiga; ?>" class="league-body <?php echo ($d['offset']==0) ? 'show' : ''; ?>">
                    <?php foreach ($jogosDaLiga as $jogo): 
                        $tg = $jogo['ia']['target'];
                        $conf = $jogo['ia']['conf'];
                        $resumo = $jogo['ia']['resumo'];
                        $st = $jogo['info']['status_short'];
                        $isFin = in_array($st, ['FT','AET','PEN']);
                        $isLive = in_array($st, ['1H','2H','HT','ET']);
                        $scHT = ($jogo['placar']['ht_casa']??0)."-".($jogo['placar']['ht_fora']??0);
                        $scFT = ($jogo['placar']['casa']??0)."-".($jogo['placar']['fora']??0);
                        $gHT = ($jogo['placar']['ht_casa']??0)+($jogo['placar']['ht_fora']??0);
                        $gFT = ($jogo['placar']['casa']??0)+($jogo['placar']['fora']??0);
                        $g2T = $gFT - $gHT; if($g2T<0)$g2T=0;
                        $win = false;
                        if ($tg=="HT" && $gHT>0) $win=true;
                        if ($tg=="2T" && $g2T>0) $win=true;
                        if ($tg=="FT" && $gFT>0) $win=true;
                        
                        $rowCls = "";
                        $badgeCls = "bg-wait"; $badgeTxt = "---";
                        $scoreTxt = "-";
                        
                        if ($isFin) { 
                            $rowCls = $win ? "row-win" : "row-loss";
                            $badgeCls = $win ? "bg-green" : "bg-red"; 
                            $badgeTxt = $win ? "GREEN" : "RED"; 
                            $scoreTxt = ($tg=="HT") ? "HT: $scHT" : "FT: $scFT";
                        } elseif ($isLive) { 
                            $badgeCls = $win ? "bg-green" : "bg-live"; 
                            $badgeTxt = $win ? "BATEU" : "LIVE";
                            $scoreTxt = "$scFT";
                        }

                        $pHT = $jogo['analise']['prob_over05_ht'];
                        $p2T = $jogo['analise']['prob_over05_2t'];
                        $pFT = $jogo['analise']['prob_over05_ft'];
                    ?>
                    <tr class="<?php echo $rowCls; ?>">
                        <td class="col-time"><?php echo date('H:i', $jogo['info']['timestamp']); ?></td>
                        <td class="col-game">
                            <div class="team-row"><?php echo $jogo['times']['casa']; ?></div>
                            <div class="vs-row"><?php echo $jogo['times']['fora']; ?></div>
                            <span class="ia-pill">🎯 <?php echo $jogo['ia']['txt']; ?> (<?php echo $conf; ?>%)</span>
                        </td>
                        <td class="col-res">
                            <span class="score-val"><?php echo $scoreTxt; ?></span>
                            
                            <div class="tt-wrap">
                                <span class="badge <?php echo $badgeCls; ?>"><?php echo $badgeTxt; ?></span>
                                <div class="tt-box">
                                    <div class="tt-header"><?php echo $resumo; ?></div>
                                    <div class="tt-body">
                                        <div class="tt-row"><span>HT:</span> <span class="<?php echo ($pHT>=55?'tt-high':''); ?>"><?php echo $pHT; ?>%</span></div>
                                        <div class="tt-row"><span>2T:</span> <span class="<?php echo ($p2T>=65?'tt-high':''); ?>"><?php echo $p2T; ?>%</span></div>
                                        <div class="tt-row"><span>FT:</span> <span class="<?php echo ($pFT>=70?'tt-high':''); ?>"><?php echo $pFT; ?>%</span></div>
                                    </div>
                                </div>
                            </div>

                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                <?php endforeach; ?>
            </table>
        </div>

    <?php endif; ?>
</div>
<?php endforeach; ?>

<footer class="app-footer">
    <div class="footer-credits">Criado por <strong>Le Miastkuosky</strong></div>
    <div class="footer-version">Versão 1.0.5 Beta</div>
</footer>

<script>
    window.onload = function() {
        const activeBtn = document.querySelector('.tab-btn.active');
        if(activeBtn) { activeBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' }); }
        setInterval(function() { atualizarJogosAuto(); }, 600000);
    };

    function openTab(tabId, btnElement) {
        var contents = document.getElementsByClassName("tab-content");
        for (var i = 0; i < contents.length; i++) { contents[i].classList.remove("active"); }
        var btns = document.getElementsByClassName("tab-btn");
        for (var i = 0; i < btns.length; i++) { btns[i].classList.remove("active"); }
        document.getElementById(tabId).classList.add("active");
        btnElement.classList.add("active");
    }

    // Função de Accordion (Sanfona)
    function toggleLiga(id, headerElement) {
        var body = document.getElementById(id);
        if (body.classList.contains('show')) {
            body.classList.remove('show');
            headerElement.classList.remove('active');
        } else {
            body.classList.add('show');
            headerElement.classList.add('active');
        }
    }

    function atualizarJogosManual() {
        const btn = document.getElementById('btnUpdate');
        btn.classList.add('spinning'); btn.disabled = true;
        fetch('coletar_jogos.php')
            .then(r => r.ok ? r.text() : Promise.reject('Erro'))
            .then(() => setTimeout(() => location.reload(), 1000))
            .catch(e => { alert('Erro.'); btn.classList.remove('spinning'); btn.disabled = false; });
    }

    function atualizarJogosAuto() {
        const btn = document.getElementById('btnUpdate');
        if(btn) btn.classList.add('spinning');
        fetch('coletar_jogos.php').then(r => { if(r.ok) location.reload(); }).catch(e => console.log("Erro auto"));
    }
</script>
</body>
</html>