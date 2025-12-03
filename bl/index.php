<?php
// ==========================================================================
// 1. CONFIGURAÇÃO DO FIREBASE
// ==========================================================================
// COLOQUE AQUI A URL DO SEU BANCO (Mantenha a barra / no final)
define('FIREBASE_DB_URL', 'https://meu-app-vip-default-rtdb.firebaseio.com/');

date_default_timezone_set('America/Sao_Paulo');

// --- FUNÇÕES DE BANCO DE DADOS (VIA CURL) ---
function salvarNoFirebase($id, $dados) {
    $url = FIREBASE_DB_URL . "recibos/$id.json";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function lerDoFirebase($id) {
    $url = FIREBASE_DB_URL . "recibos/$id.json";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// --- LÓGICA DE CONTROLE ---
$modo_visualizacao = false;
$dados_salvos = [];
$mensagem_erro = "";

if (isset($_GET['ver'])) {
    $id_recibo = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['ver']);
    $conteudo = lerDoFirebase($id_recibo);

    if ($conteudo && isset($conteudo['timestamp'])) {
        if ((time() - $conteudo['timestamp']) > 1200) { // 20 min
            $mensagem_erro = "🚫 Link Expirado.<br><small>Válido por 20 min.</small>";
        } else {
            $modo_visualizacao = true;
            $dados_salvos = $conteudo;
            $_POST['lista'] = $conteudo['lista'];
            $_POST['v_terno'] = $conteudo['premios']['terno'];
            $_POST['v_quadra'] = $conteudo['premios']['quadra'];
            $_POST['v_quina'] = $conteudo['premios']['quina'];
            foreach ($conteudo['resultados'] as $sigla => $nums) {
                $_POST[strtolower($sigla)] = $nums;
            }
            $_SERVER['REQUEST_METHOD'] = 'POST';
        }
    } else {
        $mensagem_erro = "❌ Recibo não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#18181b">
    <title><?php echo $modo_visualizacao ? 'Relatório VIP' : 'Painel Master'; ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            /* PALETA CINZA CHUMBO */
            --bg-body: #18181b;
            --bg-card: #27272a;
            --bg-input: #3f3f46;
            --border-color: #3f3f46;
            --text-primary: #f4f4f5;
            --text-secondary: #a1a1aa;
            --primary: #3b82f6;       
            --success: #10b981;       
            --danger: #ef4444;
            --radius-md: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            margin: 0;
            padding: 15px;
            padding-bottom: <?php echo $modo_visualizacao ? '30px' : '100px'; ?>;
        }

        .container { max-width: 1100px; margin: 0 auto; }

        /* HEADER & CARDS */
        .header-card {
            background: linear-gradient(145deg, #27272a 0%, #18181b 100%);
            padding: 20px; border-radius: var(--radius-md);
            box-shadow: var(--shadow); margin-bottom: 20px;
            text-align: center; border: 1px solid rgba(255,255,255,0.08);
        }
        .header-card h1 { margin: 0; font-weight: 800; font-size: 1.5rem; letter-spacing: -0.5px; }
        .header-card p { margin: 5px 0 0; color: var(--text-secondary); font-size: 0.85rem; }
        
        .card {
            background: var(--bg-card); border-radius: var(--radius-md);
            padding: 20px; box-shadow: var(--shadow); margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }
        .card-title {
            font-size: 1rem; font-weight: 700; margin-bottom: 15px;
            color: var(--text-primary); border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px; display: flex; align-items: center; gap: 10px;
        }
        .card-title i { color: var(--primary); }

        /* INPUTS */
        .grid-bancas {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px;
        }
        .banca-box {
            background: rgba(0,0,0,0.2); padding: 12px;
            border-radius: var(--radius-md); border: 1px solid rgba(255,255,255,0.05);
        }
        .banca-label {
            display: block; text-align: center; font-weight: 800;
            margin-bottom: 10px; font-size: 0.8rem; padding: 4px;
            border-radius: 6px; color: white; text-transform: uppercase;
        }
        .lbl-rj { background: #2563eb; } .lbl-nc { background: #ea580c; }
        .lbl-lk { background: #7c3aed; } .lbl-fd { background: #059669; }

        .inputs-row { display: flex; justify-content: space-between; gap: 5px; }
        .input-bola {
            width: 100%; height: 42px; text-align: center; font-size: 18px; font-weight: 700;
            border: 2px solid var(--border-color); border-radius: 10px;
            background: var(--bg-input); color: white; outline: none; -moz-appearance: textfield;
        }
        .input-bola:focus { border-color: var(--primary); background: #1e3a8a; }

        .form-control {
            width: 100%; padding: 12px; background: var(--bg-input);
            border: 1px solid var(--border-color); color: white;
            border-radius: 8px; font-size: 16px; outline: none; margin-bottom: 10px;
        }
        
        /* BOTÕES DE AÇÃO */
        .action-group { display: flex; gap: 10px; margin-top: 15px; }
        
        .btn-action {
            flex: 1; background: var(--primary); color: white; border: none;
            padding: 14px; font-size: 16px; font-weight: 700; border-radius: var(--radius-md);
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-action:hover { filter: brightness(1.1); }

        .btn-clear {
            width: 30%; background: transparent; color: var(--danger); 
            border: 1px solid var(--danger); padding: 14px; font-size: 16px; font-weight: 700; 
            border-radius: var(--radius-md); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-clear:hover { background: rgba(239, 68, 68, 0.1); }

        /* ==================================================================
           TABELA HÍBRIDA (O PULO DO GATO)
           ================================================================== */
        table { width: 100%; border-collapse: collapse; }
        
        /* DESKTOP (Tabela Clássica) */
        @media (min-width: 769px) {
            th { 
                background: #18181b; color: var(--text-secondary); 
                padding: 15px; font-size: 0.85rem; text-transform: uppercase; text-align: left; 
            }
            td { padding: 15px; border-bottom: 1px solid var(--border-color); vertical-align: top; }
            .tr-ganhador { background-color: rgba(16, 185, 129, 0.08); }
            .grade-dezenas { display: flex; flex-wrap: wrap; gap: 6px; max-width: 450px; }
            .lista-resultados { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        }

        /* MOBILE (Card View) */
        @media (max-width: 768px) {
            thead { display: none; }
            table, tbody, tr, td { display: block; width: 100%; }
            tr {
                background: rgba(255,255,255,0.03); border: 1px solid var(--border-color);
                border-radius: 12px; margin-bottom: 15px; padding: 15px; position: relative;
            }
            .tr-ganhador { 
                background: rgba(16, 185, 129, 0.08) !important;
                border: 1px solid rgba(16, 185, 129, 0.3) !important;
            }
            td { padding: 5px 0; border: none; text-align: left; }
            td:not(:last-child) { border-bottom: 1px solid rgba(255,255,255,0.05); margin-bottom: 8px; padding-bottom: 8px; }
            .grade-dezenas { display: flex; flex-wrap: wrap; gap: 5px; }
            .lista-resultados { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        }

        /* ELEMENTOS VISUAIS */
        .nome-player { font-weight: 800; color: #60a5fa; font-size: 1.05rem; display: block; margin-bottom: 4px; }
        .contador-jogos { color: #fff; font-size: 11px; font-weight: bold; margin-right: 5px; background: #475569; padding: 2px 6px; border-radius: 4px; }
        .tag-multi { background: #ca8a04; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 4px; font-weight: 700; }

        .bola-jogo { 
            width: 28px; height: 28px; line-height: 28px; text-align: center; 
            border-radius: 6px; font-size: 12px; background: #3f3f46; 
            color: var(--text-secondary); font-weight: 700;
        }
        .bola-jogo.hit { background: var(--success); color: #022c22; font-weight: 800; box-shadow: 0 0 10px rgba(16, 185, 129, 0.4); }

        .res-item { 
            display: flex; align-items: center; justify-content: space-between; 
            background: rgba(0,0,0,0.3); padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;
        }
        .res-banca { font-weight: 700; color: var(--text-secondary); margin-right: 5px; }
        .res-qtd { font-weight: 700; }
        .res-qtd.hit { color: var(--success); }
        .res-qtd.zero { color: #52525b; }
        .premio-tag { color: var(--success); font-weight: 800; font-size: 0.7rem; margin-left: auto; }

        .total-box {
            background: #09090b; border: 1px solid var(--border-color); padding: 20px;
            border-radius: var(--radius-md); margin-top: 20px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .total-valor { font-size: 1.6rem; color: var(--success); font-weight: 800; }

        .fab-container { position: fixed; bottom: 25px; right: 25px; display: flex; flex-direction: column; gap: 12px; z-index: 100; }
        .fab {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 14px 24px; border-radius: 50px; color: white; 
            font-weight: 700; font-size: 14px; border: none; cursor: pointer;
            box-shadow: 0 5px 20px rgba(0,0,0,0.5);
        }
        .fab-whatsapp { background: #25d366; }
        .fab-copy { background: var(--primary); }
    </style>
    
    <script>
        function gerenciarInput(field, nextID) {
            field.value = field.value.replace(/[^0-9]/g, '');
            if (field.value.length >= 2 && nextID) document.getElementById(nextID).focus();
        }
        function formatInput(field) { if (field.value.length === 1) field.value = "0" + field.value; }
        function limparAoFocar(field) { if(!field.readOnly) field.value = ''; }

        function pegarTexto() { return document.getElementById('texto_oculto').value; }
        function compartilharZap() {
            let texto = pegarTexto();
            let url = "https://api.whatsapp.com/send?text=" + encodeURIComponent(texto);
            window.open(url, '_blank');
        }
        function copiarTexto() {
             let texto = pegarTexto();
             navigator.clipboard.writeText(texto).then(function() { alert('✨ Relatório copiado!'); });
        }
        
        function limparTudo() {
            if(confirm("Tem certeza? Isso apagará os resultados e a lista.")) {
                document.querySelectorAll('.input-bola').forEach(i => i.value = '');
                document.querySelector('textarea[name="lista"]').value = '';
                window.location.href = window.location.pathname;
            }
        }
    </script>
</head>
<body>

<div class="container">
    
    <?php if ($mensagem_erro): ?>
        <div style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid #ef4444;">
            <h3>⚠️ Link Inválido</h3>
            <?php echo $mensagem_erro; ?>
            <br><br><a href="?" style="color: white; font-weight: bold;">Voltar</a>
        </div>
        <?php exit; ?>
    <?php endif; ?>

    <div class="header-card">
        <h1><?php echo $modo_visualizacao ? '📊 RELATÓRIO VIP BOLOES 📊' : 'Painel de Controle VIP'; ?></h1>
        <?php if ($modo_visualizacao): ?>
            <div style="margin-top:10px; font-size:0.8rem; opacity:0.7;">
                <i class="far fa-clock"></i> Expira às: <?php echo date('H:i', $conteudo['timestamp'] + 1200); ?>
            </div>
        <?php endif; ?>
    </div>

    <form method="POST">
        <div class="card">
            <div class="card-title"><i class="fas fa-dice"></i> Resultados</div>
            <div class="grid-bancas">
                <?php 
                $bancas_arr = ['RJ' => 'lbl-rj', 'NC' => 'lbl-nc', 'LK' => 'lbl-lk', 'FD' => 'lbl-fd'];
                foreach($bancas_arr as $b => $classe): 
                    $sigla = strtolower($b);
                ?>
                <div class="banca-box">
                    <span class="banca-label <?php echo $classe; ?>"><?php echo $b; ?></span>
                    <div class="inputs-row">
                        <?php for($i=1; $i<=5; $i++): 
                            $curr = "{$sigla}_{$i}";
                            $next = ($i < 5) ? "{$sigla}_".($i+1) : null;
                            $val = isset($_POST[$sigla][$i-1]) ? $_POST[$sigla][$i-1] : '';
                        ?>
                        <input type="tel" name="<?php echo $sigla; ?>[]" id="<?php echo $curr; ?>" 
                               class="input-bola" value="<?php echo $val; ?>" autocomplete="off"
                               <?php echo $modo_visualizacao ? 'readonly' : ''; ?>
                               onfocus="limparAoFocar(this)"
                               oninput="gerenciarInput(this, '<?php echo $next; ?>')" 
                               onblur="formatInput(this)">
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!$modo_visualizacao): ?>
        <div class="card">
            <div class="card-title"><i class="fas fa-sliders-h"></i> Configuração</div>
            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px; margin-bottom:15px;">
                <input type="number" step="0.01" class="form-control" name="v_terno" value="<?php echo $_POST['v_terno'] ?? '25.00'; ?>" placeholder="Terno">
                <input type="number" step="0.01" class="form-control" name="v_quadra" value="<?php echo $_POST['v_quadra'] ?? '250.00'; ?>" placeholder="Quadra">
                <input type="number" step="0.01" class="form-control" name="v_quina" value="<?php echo $_POST['v_quina'] ?? '2500.00'; ?>" placeholder="Quina">
            </div>
            <textarea name="lista" class="form-control" placeholder="Cole a lista aqui..."><?php echo isset($_POST['lista']) ? $_POST['lista'] : ''; ?></textarea>
            
            <div class="action-group">
                <button type="button" onclick="limparTudo()" class="btn-clear">
                    <i class="fas fa-trash-alt"></i> LIMPAR
                </button>
                <button type="submit" class="btn-action">
                    <i class="fas fa-check-double"></i> PROCESSAR
                </button>
            </div>
        </div>
        <?php endif; ?>
    </form>

    <?php
    $texto_final = "";
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['lista'])) {
        function cleanNum($n) { return str_pad(preg_replace('/\D/', '', $n), 2, '0', STR_PAD_LEFT); }

        $res_bancas = []; $res_bancas_save = []; $texto_resultados_dia = "";
        
        foreach(['rj','nc','lk','fd'] as $sigla) {
            if(isset($_POST[$sigla])) {
                $limpos = array_map('cleanNum', array_filter($_POST[$sigla]));
                if(count($limpos) == 5) {
                    $banca_nome = strtoupper($sigla);
                    $res_bancas[$banca_nome] = $limpos;
                    $res_bancas_save[$banca_nome] = $limpos;
                    $icone = ($banca_nome == 'RJ') ? "🔵" : (($banca_nome == 'NC') ? "🟠" : (($banca_nome == 'LK') ? "🟣" : "🟢"));
                    $texto_resultados_dia .= "{$icone} *{$banca_nome}:* " . implode(' ', $limpos) . "\n";
                }
            }
        }

        $link_gerado = "";
        if (!$modo_visualizacao) {
            $id_unico = bin2hex(random_bytes(4));
            $dados = [
                'timestamp' => time(), 'lista' => $_POST['lista'],
                'premios' => ['terno' => $_POST['v_terno'], 'quadra' => $_POST['v_quadra'], 'quina' => $_POST['v_quina']],
                'resultados' => $res_bancas_save
            ];
            salvarNoFirebase($id_unico, $dados);
            $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
            $link_gerado = "$protocolo://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]?ver=$id_unico";
        }

        echo "<div class='card' style='padding:0; border:none; background:transparent; box-shadow:none;'>";
        echo "<h3 style='margin:0 0 15px 0; font-size:1.1rem; color:var(--text-primary);'><i class='fas fa-list-check'></i> Conferência</h3>";
        
        echo "<table style='width:100%'>";
        echo "<thead><tr><th width='30%'>Participante</th><th width='40%'>Dezenas</th><th width='30%'>Conferência</th></tr></thead><tbody>";

        $linhas = explode("\n", $_POST['lista']);
        $total_do_dia = 0;
        $v_terno = floatval($_POST['v_terno']); $v_quadra = floatval($_POST['v_quadra']); $v_quina = floatval($_POST['v_quina']);
        $ultimo_nome = "Desconhecido"; $ultimo_multi = 1; $ultimas_bancas = []; $contador_geral = 0; $tem_ganhador = false;
        $texto_zap_ganhadores = ""; $texto_zap_geral = "";

        foreach ($linhas as $linha) {
            preg_match_all('/\b\d{1,2}\b/', $linha, $m_nums); $jogo_nums = array_map('cleanNum', $m_nums[0]);
            if (count($jogo_nums) > 20) $jogo_nums = array_slice($jogo_nums, -20);
            $multi_linha = 0; if (preg_match('/(\d+)[xX]/', $linha, $m_mult)) $multi_linha = intval($m_mult[1]);
            
            $bancas_nesta_linha = []; $str_up = strtoupper($linha);
            if (strpos($str_up, 'RJ') !== false) $bancas_nesta_linha[] = 'RJ';
            if (strpos($str_up, 'NC') !== false) $bancas_nesta_linha[] = 'NC';
            if (strpos($str_up, 'LK') !== false) $bancas_nesta_linha[] = 'LK';
            if (strpos($str_up, 'FD') !== false) $bancas_nesta_linha[] = 'FD';
            if (!empty($bancas_nesta_linha)) $ultimas_bancas = $bancas_nesta_linha;

            $nome_processado = preg_replace('/[\d\/\.\-\s]{15,}/', '', $linha); 
            $nome_processado = preg_replace('/\d+[xX]/', '', $nome_processado); 
            $nome_processado = str_ireplace(['RJ', 'NC', 'LK', 'FD', 'LOOK', 'FED'], '', $nome_processado); 
            $nome_processado = str_replace(['✅', '➡', '-', '/', '🔟'], ' ', $nome_processado); 
            $nome_processado = preg_replace('/[\p{No}]/u', '', $nome_processado); 
            $nome_processado = preg_replace('/\x{20E3}/u', '', $nome_processado); 
            $nome_processado = preg_replace('/^\s*\d+[\.\)]?\s*/', '', $nome_processado); 
            $nome_limpo = trim($nome_processado);
            if (mb_strlen($nome_limpo) > 0) { $ultimo_nome = $nome_limpo; if($multi_linha > 0) $ultimo_multi = $multi_linha; }
            if (count($jogo_nums) < 10) continue;

            $contador_geral++;
            $nome_final = $ultimo_nome;
            $multi_final = ($multi_linha > 0) ? $multi_linha : $ultimo_multi;
            $bancas_alvo = !empty($bancas_nesta_linha) ? $bancas_nesta_linha : $ultimas_bancas;
            if(empty($bancas_alvo)) $bancas_alvo = ['ND'];

            $html_detalhes = "<div class='lista-resultados'>";
            $total_linha = 0; $numeros_acertados_global = []; $resumos_linha_zap = []; $jogo_premiado = false;

            foreach($bancas_alvo as $banca) {
                if($banca == 'ND' || !isset($res_bancas[$banca])) {
                    $html_detalhes .= "<div class='res-item'><span class='res-banca'>{$banca}:</span> <span style='color:#666'>...</span></div>";
                    $resumos_linha_zap[] = "{$banca}: ..."; continue;
                }
                $intersecao = array_intersect($jogo_nums, $res_bancas[$banca]);
                $qtd = count($intersecao);
                $numeros_acertados_global = array_merge($numeros_acertados_global, $intersecao);

                $premio = 0; $txt_premio = "";
                if($qtd == 3) { $premio = $v_terno * $multi_final; $txt_premio = "TERNO 🥉"; }
                if($qtd == 4) { $premio = $v_quadra * $multi_final; $txt_premio = "QUADRA 🥈"; }
                if($qtd >= 5) { $premio = $v_quina * $multi_final; $txt_premio = "QUINA 🥇"; }
                $total_linha += $premio;
                
                $classe_qtd = ($qtd > 0) ? 'hit' : 'zero';
                $html_detalhes .= "<div class='res-item'>
                    <span><span class='res-banca'>{$banca}</span> <span class='res-qtd $classe_qtd'>{$qtd}x</span></span>
                    ".($premio > 0 ? "<span class='premio-tag'>$txt_premio</span>" : "")."
                </div>";
                
                $txt_resumo = "{$banca}: {$qtd}x";
                if($premio > 0) {
                    $tem_ganhador = true; $jogo_premiado = true;
                    $val_format = number_format($premio, 2, ',', '.');
                    $texto_zap_ganhadores .= "👤  *{$nome_final}*\n   ✅ {$qtd}x Acertos ({$banca}) ⏩ {$txt_premio} - R$ {$val_format}\n\n";
                    $txt_resumo .= " 🏆"; 
                }
                $resumos_linha_zap[] = $txt_resumo;
            }
            $html_detalhes .= "</div>";

            $count_fmt = str_pad($contador_geral, 2, '0', STR_PAD_LEFT);
            $str_acertos = implode(" | ", $resumos_linha_zap);
            $texto_zap_geral .= "▫ #{$count_fmt} {$nome_final} ({$multi_final}x) ➡ {$str_acertos}" . ($jogo_premiado ? " (Cota Ganhadora)" : "") . "\n";

            $bg_tr = $jogo_premiado ? 'class="tr-ganhador"' : '';

            echo "<tr $bg_tr>";
            echo "<td>
                    <span class='nome-player'>$nome_final</span>
                    <div style='margin-top:2px;'>
                        <span class='contador-jogos'>#$count_fmt</span>
                        <span class='tag-multi'>{$multi_final}x</span>
                    </div>
                  </td>";
            
            echo "<td><div class='grade-dezenas'>";
            foreach($jogo_nums as $n) {
                $classe = in_array($n, $numeros_acertados_global) ? 'hit' : '';
                echo "<div class='bola-jogo $classe'>$n</div>";
            }
            echo "</div></td>";
            echo "<td>$html_detalhes</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";

        // TOTAIS
        if($total_do_dia > 0) {
            echo "<div class='total-box'>
                    <div><span style='color:var(--text-secondary)'>Jogos</span><br><strong>$contador_geral</strong></div>
                    <div style='text-align:right'><span style='color:var(--text-secondary)'>Pagar</span><br><span class='total-valor'>R$ ".number_format($total_do_dia, 2, ',', '.')."</span></div>
                  </div>";
        }

        // TEXTO ZAP
        $data_hoje = date('d/m/Y'); $hora_agora = date('H:i');
        $texto_final = "📊 *RELATÓRIO VIP BOLOES* 📊\n🗓 {$data_hoje}   ⏰ {$hora_agora}\n\n";
        $texto_final .= "━━━━━━━━━━━━━━━━━━\n🏆 *LISTA DE GANHADORES* 🏆\n━━━━━━━━━━━━━━━━━━\n\n";
        $texto_final .= ($tem_ganhador ? $texto_zap_ganhadores : "🐢 *Sem ganhadores nesta rodada*\n      Acumulou para a próxima!\n\n");
        $texto_final .= "━━━━━━━━━━━━━━━━━━\n🔢 *RESULTADOS DO DIA*\n\n{$texto_resultados_dia}";
        $texto_final .= "\n━━━━━━━━━━━━━━━━━━\n📋 *CONFERÊNCIA GERAL*\n\nJogos:\n{$texto_zap_geral}";
        $texto_final .= "\n━━━━━━━━━━━━━━━━━━\n🍀 *Boa sorte na próxima!*\n_Gerado em: {$data_hoje} às {$hora_agora}_";
        if ($link_gerado) { $texto_final .= "\n\n🔗 *Link do Recibo (Válido 20min):*\n$link_gerado"; }
        
        echo "<textarea id='texto_oculto' style='display:none;'>" . htmlspecialchars($texto_final) . "</textarea>";

        if (!$modo_visualizacao) {
            echo "<div class='fab-container'>
                    <button onclick=\"copiarTexto()\" class='fab fab-copy'><i class='fas fa-copy'></i> Copiar</button>
                    <button onclick=\"compartilharZap()\" class='fab fab-whatsapp'><i class='fab fa-whatsapp'></i> Enviar</button>
                  </div>";
        }
    }
    ?>
</div>
</body>
</html>