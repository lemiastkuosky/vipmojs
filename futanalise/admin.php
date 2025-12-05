<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(300); 
date_default_timezone_set('America/Sao_Paulo');

// ARQUIVOS
$configFile = 'config.json';
$futureFile = 'upcoming_odds.json';
$historyFile = 'database.json';
$logoDir = 'logos/';

// CARREGAR CONFIG (COM VALORES PADRÃO)
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
} else {
    $config = [];
}

// GARANTIR QUE TODAS AS CHAVES EXISTAM
$defaults = [
    "admin_pass" => "admin",
    "api_key_odds" => "",
    "api_key_football" => "",
    "api_key_3" => "",
    "api_key_4" => "",
    "api_key_5" => "",
    "show_logos" => true
];
$config = array_merge($defaults, $config);

// ==========================================================================
// 🔌 CONEXÃO COM O BANCO MYSQL (IGUAL AO INDEX.PHP)
// ==========================================================================
$dbHost = 'localhost';
$dbName = 'fut_analises';
$dbUser = 'root'; 
$dbPass = ''; 
$pdo = null;

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Falha silenciosa no admin para não travar login, mas avisa na exportação
}

// LOGIN CHECK
if (isset($_GET['logout'])) { session_destroy(); header("Location: admin.php"); exit; }
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if (isset($_POST['login_pass']) && $_POST['login_pass'] === $config['admin_pass']) {
        $_SESSION['logged_in'] = true; header("Location: admin.php"); exit;
    }
    echo '<form method="POST" style="text-align:center;margin-top:100px;font-family:sans-serif;color:#ddd;background:#111;padding:50px;max-width:300px;margin-left:auto;margin-right:auto;border-radius:10px;border:1px solid #333;">
    <h2 style="color:#3b82f6;margin-top:0;">PAINEL ADMIN</h2>
    <input type="password" name="login_pass" placeholder="Senha" style="padding:10px;width:90%;margin-bottom:10px;border-radius:5px;border:1px solid #444;background:#222;color:#fff;">
    <button style="padding:10px 20px;background:#3b82f6;color:white;border:none;border-radius:5px;cursor:pointer;font-weight:bold;width:100%;">ENTRAR</button>
    </form>';
    exit;
}

$msg = "";

// ==========================================================================
// AÇÕES DO SISTEMA
// ==========================================================================

// 0. EXPORTAR BACKUP CSV (NOVA FUNÇÃO)
if (isset($_POST['acao']) && $_POST['acao'] == 'exportar_backup') {
    if (!$pdo) {
        $msg = "<div class='alert error'>Erro: Não foi possível conectar ao Banco MySQL.</div>";
    } else {
        $filename = "Backup_FutAnalises_" . date('Y-m-d_H-i') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF"); // BOM para Excel
        
        fputcsv($output, ['ID', 'Liga', 'Data', 'Hora', 'Time Casa', 'Time Fora', 'Gols H', 'Gols A', 'HT H', 'HT A', 'Odd H', 'Odd D', 'Odd A']);
        
        $stmt = $pdo->query("SELECT id, liga, data_jogo, hora_jogo, time_casa, time_fora, gols_casa, gols_fora, ht_casa, ht_fora, odd_casa, odd_empate, odd_fora FROM historico_jogos");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit(); 
    }
}

// 1. SALVAR CONFIGURAÇÕES
if (isset($_POST['acao']) && $_POST['acao'] == 'save_config') {
    $newConfig = array_merge($config, [
        "admin_pass" => $_POST['admin_pass'],
        "api_key_odds" => $_POST['api_key_odds'],
        "api_key_football" => $_POST['api_key_football'],
        "api_key_3" => $_POST['api_key_3'],
        "api_key_4" => $_POST['api_key_4'],
        "api_key_5" => $_POST['api_key_5'],
        "show_logos" => isset($_POST['show_logos']) ? true : false
    ]);
    
    file_put_contents($configFile, json_encode($newConfig, JSON_PRETTY_PRINT));
    $config = $newConfig;
    $msg = "<div class='alert success'>Configurações Salvas com Sucesso!</div>";
}

// 2. ADICIONAR JOGO MANUAL
if (isset($_POST['acao']) && $_POST['acao'] == 'add_game') {
    $games = file_exists($futureFile) ? json_decode(file_get_contents($futureFile), true) : [];
    $games[] = [
        'sport_key' => $_POST['league_manual'], 'commence_time' => $_POST['date_manual'],
        'home_team' => $_POST['home_manual'], 'away_team' => $_POST['away_manual'],
        'odds_h' => (float)$_POST['odd_h_manual'], 'odds_d' => (float)$_POST['odd_d_manual'], 'odds_a' => (float)$_POST['odd_a_manual']
    ];
    usort($games, function($a, $b) { return strcmp($a['commence_time'], $b['commence_time']); });
    file_put_contents($futureFile, json_encode($games, JSON_PRETTY_PRINT));
    $msg = "<div class='alert success'>Jogo adicionado!</div>";
}

// 3. EXCLUIR JOGO
if (isset($_GET['delete_idx'])) {
    $games = file_exists($futureFile) ? json_decode(file_get_contents($futureFile), true) : [];
    if(isset($games[(int)$_GET['delete_idx']])) {
        array_splice($games, (int)$_GET['delete_idx'], 1);
        file_put_contents($futureFile, json_encode($games, JSON_PRETTY_PRINT));
    }
    header("Location: admin.php"); exit;
}

// 4. BAIXAR LOGOS DA API
if (isset($_POST['acao']) && $_POST['acao'] == 'fetch_logos') {
    if (empty($config['api_key_football'])) {
        $msg = "<div class='alert error'>Erro: Configure a API Key 2 (API-Football) primeiro!</div>";
    } else {
        $allTeams = [];
        
        // Tenta pegar times do SQL se possível, senão do JSON
        if($pdo) {
            $stmt = $pdo->query("SELECT DISTINCT time_casa FROM historico_jogos UNION SELECT DISTINCT time_fora FROM historico_jogos");
            while($row = $stmt->fetch(PDO::FETCH_NUM)) { $allTeams[$row[0]] = true; }
        }
        
        $fut = file_exists($futureFile) ? json_decode(file_get_contents($futureFile), true) : [];
        foreach($fut as $g) { $allTeams[$g['home_team']] = true; $allTeams[$g['away_team']] = true; }
        
        $downloaded = 0; $failed = 0; $limit = 20; $count = 0;
        if (!is_dir($logoDir)) mkdir($logoDir, 0777, true);

        foreach(array_keys($allTeams) as $teamName) {
            $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $teamName);
            $filename = $logoDir . $cleanName . ".png";

            if (!file_exists($filename)) {
                if ($count >= $limit) break;
                $url = "https://v3.football.api-sports.io/teams?name=" . urlencode($teamName);
                $opts = ["http" => ["method" => "GET", "header" => "x-rapidapi-key: " . $config['api_key_football'] . "\r\n" . "x-rapidapi-host: v3.football.api-sports.io\r\n"]];
                $context = stream_context_create($opts);
                $resp = @file_get_contents($url, false, $context);
                if ($resp) {
                    $json = json_decode($resp, true);
                    if (isset($json['response'][0]['team']['logo'])) {
                        $logoUrl = $json['response'][0]['team']['logo'];
                        $imgContent = @file_get_contents($logoUrl);
                        if ($imgContent) { file_put_contents($filename, $imgContent); $downloaded++; } else { $failed++; }
                    } else { $failed++; }
                }
                $count++; sleep(1);
            }
        }
        $msg = "<div class='alert success'>Processo finalizado! Baixados: $downloaded. (Limite de $limit por clique).</div>";
    }
}

// DADOS EXIBIÇÃO
$currentGames = file_exists($futureFile) ? json_decode(file_get_contents($futureFile), true) : [];
$logosCount = count(glob($logoDir . "*.png"));

// Contagem do SQL (Prioridade) ou JSON (Fallback)
$totalHistory = 0;
if($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM historico_jogos");
    $totalHistory = $stmt->fetchColumn();
} else {
    $totalHistory = file_exists($historyFile) ? count(json_decode(file_get_contents($historyFile), true)) : 0;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Fut Analises</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root { --bg: #0a0a0a; --card: #151515; --border: #333; --blue: #3b82f6; --green: #10b981; --red: #ef4444; }
        body { background: var(--bg); color: #ddd; font-family: 'Montserrat', sans-serif; margin: 0; padding: 20px; font-size: 13px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: 900; color: #fff; } .logo span { color: var(--blue); }
        .grid { display: grid; grid-template-columns: 350px 1fr; gap: 20px; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .card h3 { margin-top: 0; color: var(--blue); font-size: 14px; text-transform: uppercase; border-bottom: 1px solid #222; padding-bottom: 10px; }
        input[type="text"], input[type="password"], input[type="number"], input[type="datetime-local"], select { width: 100%; padding: 8px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; margin-top: 5px; }
        .btn { width: 100%; padding: 10px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 15px; text-transform: uppercase; font-size: 11px; }
        .btn-blue { background: var(--blue); color: white; } .btn-green { background: var(--green); color: white; } .btn-red { background: var(--red); color: white; } .btn-purple { background: #8b5cf6; color: white; }
        
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; text-align: center; }
        .success { background: rgba(16, 185, 129, 0.1); color: var(--green); border: 1px solid var(--green); }
        .error { background: rgba(239, 68, 68, 0.1); color: var(--red); border: 1px solid var(--red); }
        
        table { width: 100%; border-collapse: collapse; font-size: 12px; } th { text-align: left; color: #666; padding: 8px; border-bottom: 1px solid #333; } td { padding: 8px; border-bottom: 1px solid #222; }
        .team-row { display: flex; align-items: center; gap: 10px; } 
        .mini-logo { width: 20px; height: 20px; object-fit: contain; background: #fff; border-radius: 50%; }
        
        .switch-container { display: flex; align-items: center; justify-content: space-between; background: #222; padding: 10px; border-radius: 6px; border: 1px solid #444; margin-top: 10px; margin-bottom: 15px; }
        .switch { position: relative; display: inline-block; width: 50px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #444; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--green); }
        input:focus + .slider { box-shadow: 0 0 1px var(--green); }
        input:checked + .slider:before { transform: translateX(26px); }

        .divider { border-top: 1px dashed #333; margin: 15px 0; }
        .lbl-small { font-size: 10px; color: #666; margin-top: 2px; }
        
        .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; display: none; flex-direction: column; justify-content: center; align-items: center; color: white; }
        .spinner { border: 4px solid #222; border-top: 4px solid var(--blue); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin-bottom: 15px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        @media(max-width: 800px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div id="overlay-loading" class="loading-overlay">
    <div class="spinner"></div>
    <h3>PROCESSANDO... POR FAVOR AGUARDE</h3>
    <p style="color:#888; font-size:12px;">Isso pode levar alguns segundos.</p>
</div>

<div class="container">
    <div class="header">
        <div class="logo">FUT <span>ADMIN</span></div>
        <div><a href="index.php" target="_blank" style="color:#aaa; text-decoration:none;">IR PARA O SITE ↗</a></div>
    </div>
    <?php echo $msg; ?>
    <div class="grid">
        <div class="sidebar">
            <div class="card">
                <h3>⚙️ Configurações</h3>
                <form method="POST" onsubmit="document.getElementById('overlay-loading').style.display='flex'">
                    <input type="hidden" name="acao" value="save_config">
                    <label>Senha Admin</label><input type="text" name="admin_pass" value="<?php echo $config['admin_pass']; ?>">
                    
                    <label>Exibição de Logos</label>
                    <div class="switch-container">
                        <span style="font-weight:bold; font-size:12px;">Mostrar Logos no Site?</span>
                        <label class="switch" style="margin:0;">
                            <input type="checkbox" name="show_logos" <?php echo $config['show_logos'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="divider"></div>
                    <label>API 1 (Odds - The Odds API)</label>
                    <input type="text" name="api_key_odds" value="<?php echo $config['api_key_odds']; ?>">
                    
                    <label>API 2 (Logos/Dados - API Football)</label>
                    <input type="text" name="api_key_football" value="<?php echo $config['api_key_football']; ?>">
                    
                    <button type="submit" class="btn btn-blue">Salvar Tudo</button>
                </form>
            </div>
            
            <div class="card">
                <h3>📊 Dados</h3>
                <p>Histórico (SQL): <strong><?php echo number_format($totalHistory, 0, ',', '.'); ?></strong> jogos</p>
                <p>Scanner (Futuro): <strong><?php echo count($currentGames); ?></strong> jogos</p>
                
                <div class="divider"></div>
                
                <form method="POST" target="_blank">
                    <input type="hidden" name="acao" value="exportar_backup">
                    <button type="submit" class="btn" style="background:#222; color:#fff; border:1px solid #444;">💾 EXPORTAR CSV (BACKUP)</button>
                </form>
                <div class="lbl-small" style="text-align:center;">Baixa toda a base SQL (>60k jogos)</div>
            </div>

            <div class="card" style="border-color: #8b5cf6;">
                <h3 style="color: #8b5cf6;">🛡️ Cache de Logos</h3>
                <p>Logos salvos: <strong><?php echo $logosCount; ?></strong></p>
                <form method="POST" onsubmit="document.getElementById('overlay-loading').style.display='flex'">
                    <input type="hidden" name="acao" value="fetch_logos">
                    <button type="submit" class="btn btn-purple">🔄 Baixar Logos Faltantes</button>
                </form>
            </div>
        </div>

        <div class="content">
            <div class="card">
                <h3>📝 Gerenciar Jogos Futuros</h3>
                <table>
                    <thead><tr><th>Data</th><th>Jogo</th><th>Ação</th></tr></thead>
                    <tbody>
                        <?php foreach($currentGames as $idx => $g): 
                            $dt = date("d/m H:i", strtotime($g['commence_time']));
                            $cleanH = preg_replace('/[^A-Za-z0-9]/', '', $g['home_team']);
                            $cleanA = preg_replace('/[^A-Za-z0-9]/', '', $g['away_team']);
                            
                            $logoHtmlH = ""; $logoHtmlA = "";
                            if ($config['show_logos']) {
                                $srcH = file_exists($logoDir . $cleanH . ".png") ? $logoDir . $cleanH . ".png" : 'https://cdn-icons-png.flaticon.com/512/16/16480.png';
                                $srcA = file_exists($logoDir . $cleanA . ".png") ? $logoDir . $cleanA . ".png" : 'https://cdn-icons-png.flaticon.com/512/16/16480.png';
                                $logoHtmlH = "<img src='$srcH' class='mini-logo'>";
                                $logoHtmlA = "<img src='$srcA' class='mini-logo'>";
                            }
                        ?>
                        <tr>
                            <td><?php echo $dt; ?></td>
                            <td>
                                <div class="team-row"><?php echo $logoHtmlH; ?> <?php echo $g['home_team']; ?></div>
                                <div class="team-row"><?php echo $logoHtmlA; ?> <?php echo $g['away_team']; ?></div>
                            </td>
                            <td><a href="?delete_idx=<?php echo $idx; ?>" class="btn btn-red" style="padding:5px; margin:0;" onclick="return confirm('Apagar?')">X</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>