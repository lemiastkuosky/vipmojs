<?php
// ==========================================================================
// 🔮 FUT ANALISES PRO - V.FINAL (CORREÇÃO BANCO DE DADOS)
// ==========================================================================
set_time_limit(1200); 
ini_set('memory_limit', '1024M');
date_default_timezone_set('America/Sao_Paulo');

// 1. CONFIGURAÇÕES
$configFile = 'config.json';
$config = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
$apiKey = isset($config['api_key_odds']) ? $config['api_key_odds'] : ''; 
$apiKeyFootball = isset($config['api_key_football']) ? $config['api_key_football'] : '';
$showLogos = isset($config['show_logos']) ? $config['show_logos'] : true;
$displayStyle = $showLogos ? '' : 'display:none !important;';
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'history'; 

// 2. CONEXÃO BANCO DE DADOS
$dbHost = 'localhost'; $dbName = 'fut_analises'; $dbUser = 'root'; $dbPass = ''; 
try {
    $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cria o banco se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
    $pdo->exec("USE `$dbName`");
    
    // Tabelas SQL
    $pdo->exec("CREATE TABLE IF NOT EXISTS historico_jogos (
        id INT AUTO_INCREMENT PRIMARY KEY, liga VARCHAR(100), data_jogo DATE, hora_jogo VARCHAR(10),
        time_casa VARCHAR(100), time_fora VARCHAR(100), gols_casa INT, gols_fora INT, ht_casa INT NULL, ht_fora INT NULL,
        odd_casa DECIMAL(10,3), odd_empate DECIMAL(10,3), odd_fora DECIMAL(10,3),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX (liga), INDEX (time_casa), INDEX (time_fora), INDEX (data_jogo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS scanner_historico (
        id INT AUTO_INCREMENT PRIMARY KEY, liga VARCHAR(100), data_jogo DATETIME,
        time_casa VARCHAR(100), time_fora VARCHAR(100), odd_casa DECIMAL(10,3), odd_empate DECIMAL(10,3), odd_fora DECIMAL(10,3),
        resultado_real VARCHAR(10) DEFAULT NULL, placar_real VARCHAR(20) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY unique_game (liga, time_casa, time_fora, data_jogo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    try { $pdo->exec("ALTER TABLE scanner_historico ADD COLUMN ia_palpite VARCHAR(20) DEFAULT NULL"); } catch(Exception $e) {} 
    try { $pdo->exec("ALTER TABLE scanner_historico ADD COLUMN ia_status VARCHAR(10) DEFAULT NULL"); } catch(Exception $e) {}
   
    $cols = $pdo->query("DESCRIBE scanner_historico")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('resultado_real', $cols)) {
        $pdo->exec("ALTER TABLE scanner_historico ADD COLUMN resultado_real VARCHAR(10) DEFAULT NULL");
    }
    
    if (!in_array('placar_real', $cols)) {
        $pdo->exec("ALTER TABLE scanner_historico ADD COLUMN placar_real VARCHAR(20) DEFAULT NULL");
    }

} catch (PDOException $e) { 
    die("<div style='background:red; color:white; padding:20px; text-align:center;'>
            <h2>ERRO DE BANCO DE DADOS</h2>
            <p>" . $e->getMessage() . "</p>
            <p>Tente apagar a tabela 'scanner_historico' no seu gerenciador de banco de dados para recriá-la.</p>
         </div>"); 
}

// Arquivos de Cache (Apenas para API Futura imediata)
$apiCacheFile = 'upcoming_odds.json';
$logoDir = 'logos/';
$autoSelectLeague = '';

// ==========================================================================
// LISTAS DE LIGAS (Mapeamento Completo)
// ==========================================================================
$ligas_api = [
    // --- DESTAQUES (API 1 - The Odds API) ---
    '🇧🇷 Brasil - Série A' => 'soccer_brazil_campeonato',
    '🏆 Libertadores' => 'soccer_conmebol_copa_libertadores',
    '🏆 Sul-Americana' => 'soccer_conmebol_copa_sudamericana',
    '🇪🇺 UEFA Champions League' => 'soccer_uefa_champs_league',
    '🇪🇺 UEFA Europa League' => 'soccer_uefa_europa_league',
    '🇪🇺 UEFA Conference League' => 'soccer_uefa_europa_conference_league',
    '🏴󠁧󠁢󠁥󠁮󠁧󠁿 Inglaterra - Premier League' => 'soccer_epl',
    '🏴󠁧󠁢󠁥󠁮󠁧󠁿 Inglaterra - Championship' => 'soccer_efl_champ',
    '🇪🇸 Espanha - La Liga' => 'soccer_spain_la_liga',
    '🇪🇸 Espanha - La Liga 2' => 'soccer_spain_segunda_division',
    '🇩🇪 Alemanha - Bundesliga' => 'soccer_germany_bundesliga',
    '🇩🇪 Alemanha - Bundesliga 2' => 'soccer_germany_bundesliga2',
    '🇮🇹 Itália - Serie A' => 'soccer_italy_serie_a',
    '🇮🇹 Itália - Serie B' => 'soccer_italy_serie_b',
    '🇫🇷 França - Ligue 1' => 'soccer_france_ligue_one',
    '🇫🇷 França - Ligue 2' => 'soccer_france_ligue_two',
    '🇵🇹 Portugal - Primeira Liga' => 'soccer_portugal_primeira_liga',
    '🇳🇱 Holanda - Eredivisie' => 'soccer_netherlands_eredivisie',
    '🇹🇷 Turquia - Super Lig' => 'soccer_turkey_super_league',
    '🇬🇷 Grécia - Super League' => 'soccer_greece_super_league',
    '🏴󠁧󠁢󠁳󠁣󠁴󠁿 Escócia - Premiership' => 'soccer_spl',
    '🇧🇪 Bélgica - Pro League' => 'soccer_belgium_first_div',
    '🇩🇰 Dinamarca - Superliga' => 'soccer_denmark_superliga',
    '🇵🇱 Polônia - Ekstraklasa' => 'soccer_poland_ekstraklasa',
    '🇸🇪 Suécia - Allsvenskan' => 'soccer_sweden_allsvenskan',
    '🇨🇭 Suíça - Super League' => 'soccer_switzerland_superleague',
    '🇦🇹 Áustria - Bundesliga' => 'soccer_austria_bundesliga',
    '🇳🇴 Noruega - Eliteserien' => 'soccer_norway_eliteserien',
    '🇦🇺 Austrália - A-League' => 'soccer_australia_aleague',
    '🇺🇸 EUA - MLS' => 'soccer_usa_mls',
    '🇲🇽 México - Liga MX' => 'soccer_mexico_ligamx',
    '🇦🇷 Argentina - Liga Profissional' => 'soccer_argentina_primera_division',
    '🇨🇱 Chile - Primeira' => 'soccer_chile_campeonato',

    // --- MUNDO E OUTRAS LIGAS (API 2 - API-Football) ---
    
    // EUROPA
    '🇦🇱 Albânia - Superliga' => 'API2_310',
    '🇦🇱 Albânia - 1st Division' => 'API2_311',
    '🇦🇱 Albânia - Cup' => 'API2_707',
    '🇦🇩 Andorra - 1a Divisió' => 'API2_312',
    '🇦🇲 Armênia - Premier League' => 'API2_342',
    '🇦🇹 Áustria - 2. Liga' => 'API2_219',
    '🇦🇹 Áustria - Cup' => 'API2_220',
    '🇦🇿 Azerbaijão - Premyer Liqa' => 'API2_419',
    '🇧🇾 Bielorrússia - Premier League' => 'API2_116',
    '🇧🇪 Bélgica - Challenger Pro League' => 'API2_145',
    '🇧🇪 Bélgica - Cup' => 'API2_147',
    '🇧🇦 Bósnia - Premijer Liga' => 'API2_315',
    '🇧🇬 Bulgária - First League' => 'API2_172',
    '🇧🇬 Bulgária - Second League' => 'API2_173',
    '🇭🇷 Croácia - HNL' => 'API2_210',
    '🇭🇷 Croácia - First NL' => 'API2_211',
    '🇨🇾 Chipre - 1. Division' => 'API2_318',
    '🇨🇿 República Tcheca - Liga 1' => 'API2_345',
    '🇩🇰 Dinamarca - 1. Division' => 'API2_120',
    '🇪🇪 Estônia - Meistriliiga' => 'API2_329',
    '🇫🇴 Ilhas Faroé - Premier League' => 'API2_367',
    '🇫🇮 Finlândia - Veikkausliiga' => 'API2_244',
    '🇫🇮 Finlândia - Ykkonen' => 'API2_245',
    '🇫🇷 França - National' => 'API2_63',
    '🇬🇪 Geórgia - Erovnuli Liga' => 'API2_327',
    '🇩🇪 Alemanha - 3. Liga' => 'API2_80',
    '🇬🇷 Grécia - Super League 2' => 'API2_494',
    '🇭🇺 Hungria - NB I' => 'API2_271',
    '🇮🇸 Islândia - Urvalsdeild' => 'API2_164',
    '🇮🇪 Irlanda - Premier Division' => 'API2_357',
    '🇮🇱 Israel - Ligat HaAl' => 'API2_383',
    '🇮🇹 Itália - Serie C (Grupo A)' => 'API2_138',
    '🇮🇹 Itália - Serie C (Grupo B)' => 'API2_942',
    '🇮🇹 Itália - Serie C (Grupo C)' => 'API2_943',
    '🇰🇿 Cazaquistão - Premier League' => 'API2_389',
    '🇽🇰 Kosovo - Superliga' => 'API2_664',
    '🇱🇻 Letônia - Virsliga' => 'API2_365',
    '🇱🇹 Lituânia - A Lyga' => 'API2_362',
    '🇱🇺 Luxemburgo - National Division' => 'API2_261',
    '🇲🇰 Macedônia do Norte - First League' => 'API2_371',
    '🇲🇹 Malta - Premier League' => 'API2_393',
    '🇲🇩 Moldávia - Super Liga' => 'API2_394',
    '🇲🇪 Montenegro - First League' => 'API2_355',
    '🇳🇱 Holanda - Eerste Divisie' => 'API2_89',
    '🇳🇴 Noruega - 1. Division' => 'API2_104',
    '🇵🇱 Polônia - I Liga' => 'API2_107',
    '🇵🇹 Portugal - Liga 2' => 'API2_95',
    '🇷🇴 Romênia - Liga I' => 'API2_283',
    '🇷🇺 Rússia - Premier League' => 'API2_235',
    '🇸🇲 San Marino - Campionato' => 'API2_404',
    '🏴󠁧󠁢󠁳󠁣󠁴󠁿 Escócia - Championship' => 'API2_180',
    '🏴󠁧󠁢󠁳󠁣󠁴󠁿 Escócia - League One' => 'API2_183',
    '🏴󠁧󠁢󠁳󠁣󠁴󠁿 Escócia - League Two' => 'API2_184',
    '🇷🇸 Sérvia - Super Liga' => 'API2_286',
    '🇸🇰 Eslováquia - Super Liga' => 'API2_332',
    '🇸🇮 Eslovênia - Prva Liga' => 'API2_373',
    '🇪🇸 Espanha - Primera RFEF' => 'API2_435',
    '🇸🇪 Suécia - Superettan' => 'API2_114',
    '🇨🇭 Suíça - Challenge League' => 'API2_208',
    '🇹🇷 Turquia - 1. Lig' => 'API2_204',
    '🇺🇦 Ucrânia - Premier League' => 'API2_333',
    '🏴󠁧󠁢󠁷󠁬󠁳󠁿 País de Gales - Premier League' => 'API2_110',

    // AMÉRICAS
    '🇦🇷 Argentina - Primera Nacional' => 'API2_129',
    '🇦🇷 Argentina - Primera B' => 'API2_131',
    '🇦🇷 Argentina - Copa Argentina' => 'API2_130',
    '🇦🇷 Argentina - Copa da Liga' => 'API2_1032',
    '🇧🇴 Bolívia - Primera División' => 'API2_344',
    '🇧🇷 Brasil - Série B' => 'API2_72',
    '🇧🇷 Brasil - Série C' => 'API2_75',
    '🇧🇷 Brasil - Série D' => 'API2_76',
    '🇧🇷 Brasil - Copa do Brasil' => 'API2_73',
    '🇧🇷 Brasil - Paulista A1' => 'API2_475',
    '🇧🇷 Brasil - Carioca' => 'API2_624',
    '🇧🇷 Brasil - Mineiro' => 'API2_629',
    '🇧🇷 Brasil - Gaúcho' => 'API2_477',
    '🇧🇷 Brasil - Copa do Nordeste' => 'API2_612',
    '🇨🇦 Canadá - Premier League' => 'API2_479',
    '🇨🇱 Chile - Primera B' => 'API2_266',
    '🇨🇱 Chile - Copa Chile' => 'API2_267',
    '🇨🇴 Colômbia - Primera A' => 'API2_239',
    '🇨🇴 Colômbia - Primera B' => 'API2_240',
    '🇨🇴 Colômbia - Copa Colômbia' => 'API2_241',
    '🇨🇷 Costa Rica - Primera División' => 'API2_162',
    '🇪🇨 Equador - Liga Pro' => 'API2_242',
    '🇸🇻 El Salvador - Primera Division' => 'API2_370',
    '🇬🇹 Guatemala - Liga Nacional' => 'API2_339',
    '🇭🇳 Honduras - Liga Nacional' => 'API2_234',
    '🇯🇲 Jamaica - Premier League' => 'API2_322',
    '🇲🇽 México - Liga MX' => 'API2_262',
    '🇲🇽 México - Liga de Expansión' => 'API2_263',
    '🇳🇮 Nicarágua - Liga Primera' => 'API2_396',
    '🇵🇦 Panamá - LPF' => 'API2_304',
    '🇵🇾 Paraguai - Primera Division' => 'API2_250',
    '🇵🇪 Peru - Liga 1' => 'API2_281',
    '🇺🇾 Uruguai - Primera División' => 'API2_268',
    '🇺🇸 EUA - USL Championship' => 'API2_255',
    '🇻🇪 Venezuela - Primera División' => 'API2_299',

    // ÁFRICA
    '🇿🇦 África do Sul - Premier League' => 'API2_288',
    '🇩🇿 Argélia - Ligue 1' => 'API2_186',
    '🇩🇿 Argélia - Ligue 2' => 'API2_187',
    '🇦🇴 Angola - Girabola' => 'API2_397',
    '🇨🇲 Camarões - Elite One' => 'API2_411',
    '🇪🇬 Egito - Premier League' => 'API2_233',
    '🇪🇹 Etiópia - Premier League' => 'API2_363',
    '🇬🇭 Gana - Premier League' => 'API2_570',
    '🇨🇮 Costa do Marfim - Ligue 1' => 'API2_386',
    '🇰🇪 Quênia - Premier League' => 'API2_276',
    '🇲🇦 Marrocos - Botola Pro' => 'API2_200',
    '🇳🇬 Nigéria - NPFL' => 'API2_399',
    '🇸🇳 Senegal - Ligue 1' => 'API2_403',
    '🇹🇳 Tunísia - Ligue 1' => 'API2_202',

    // ÁSIA & OCEANIA
    '🇦🇺 Austrália - NPL Victoria' => 'API2_195',
    '🇦🇺 Austrália - NPL NSW' => 'API2_192',
    '🇦🇺 Austrália - A-League (Fem)' => 'API2_190',
    '🇧🇭 Bahrein - Premier League' => 'API2_417',
    '🇧🇩 Bangladesh - Premier League' => 'API2_398',
    '🇰🇭 Camboja - C-League' => 'API2_410',
    '🇨🇳 China - Super League' => 'API2_169',
    '🇨🇳 China - League One' => 'API2_170',
    '🇭🇰 Hong Kong - Premier League' => 'API2_380',
    '🇮🇳 Índia - Super League' => 'API2_323',
    '🇮🇩 Indonésia - Liga 1' => 'API2_274',
    '🇮🇷 Irã - Pro League' => 'API2_290',
    '🇯🇵 Japão - J1 League' => 'API2_98',
    '🇯🇵 Japão - J2 League' => 'API2_99',
    '🇯🇵 Japão - J3 League' => 'API2_100',
    '🇯🇴 Jordânia - Pro League' => 'API2_387',
    '🇰🇼 Kuwait - Premier League' => 'API2_330',
    '🇱🇧 Líbano - Premier League' => 'API2_390',
    '🇲🇾 Malásia - Super League' => 'API2_278',
    '🇳🇿 Nova Zelândia - National League' => 'API2_955',
    '🇳🇿 Nova Zelândia - Feminino' => 'API2_966',
    '🇴🇲 Omã - Professional League' => 'API2_406',
    '🇶🇦 Catar - Stars League' => 'API2_305',
    '🇸🇦 Arábia Saudita - Pro League' => 'API2_307',
    '🇸🇦 Arábia Saudita - Divisão 1' => 'API2_308',
    '🇸🇬 Cingapura - Premier League' => 'API2_368',
    '🇰🇷 Coreia do Sul - K League 1' => 'API2_292',
    '🇰🇷 Coreia do Sul - K League 2' => 'API2_293',
    '🇹🇭 Tailândia - Thai League 1' => 'API2_296',
    '🇦🇪 Emirados Árabes - Pro League' => 'API2_301',
    '🇺🇿 Uzbequistão - Super League' => 'API2_369',
    '🇻🇳 Vietnã - V.League 1' => 'API2_340',

    // INTERNACIONAIS (API 2)
    '🌍 Copa do Mundo' => 'API2_1',
    '🇪🇺 Eurocopa' => 'API2_4',
    '🌎 Copa América' => 'API2_9',
    '🇪🇺 UEFA Champions League' => 'API2_2',
    '🇪🇺 UEFA Europa League' => 'API2_3',
    '🇪🇺 UEFA Conference League' => 'API2_848',
    '🌎 Libertadores' => 'API2_13',
    '🌎 Sul-Americana' => 'API2_11',
    '🌍 CAF Champions League' => 'API2_12',
    '🌏 AFC Champions League' => 'API2_17',
    '🌎 Concacaf Champions Cup' => 'API2_16',
    '🌍 Mundial de Clubes' => 'API2_15'
];
$ligas_api_reverse = array_flip($ligas_api);
 
// URLs dos CSVs de Histórico (COMPLETA)
$lista_campeonatos = [
    'Brasil_Serie-A.csv' => 'https://www.football-data.co.uk/new/BRA.csv',
    'Argentina_Primera.csv' => 'https://www.football-data.co.uk/new/ARG.csv',
    'Inglaterra_Premier-League.csv' => 'https://www.football-data.co.uk/mmz4281/2425/E0.csv',
    'Inglaterra_Championship.csv' => 'https://www.football-data.co.uk/mmz4281/2425/E1.csv',
    'Inglaterra_League-1.csv' => 'https://www.football-data.co.uk/mmz4281/2425/E2.csv',
    'Inglaterra_League-2.csv' => 'https://www.football-data.co.uk/mmz4281/2425/E3.csv',
    'Inglaterra_Conference.csv' => 'https://www.football-data.co.uk/mmz4281/2425/EC.csv',
    'Escocia_Premiership.csv' => 'https://www.football-data.co.uk/mmz4281/2425/SC0.csv',
    'Escocia_Championship.csv' => 'https://www.football-data.co.uk/mmz4281/2425/SC1.csv',
    'Escocia_League-1.csv' => 'https://www.football-data.co.uk/mmz4281/2425/SC2.csv',
    'Escocia_League-2.csv' => 'https://www.football-data.co.uk/mmz4281/2425/SC3.csv',
    'Alemanha_Bundesliga_1.csv' => 'https://www.football-data.co.uk/mmz4281/2425/D1.csv',
    'Alemanha_Bundesliga-2.csv' => 'https://www.football-data.co.uk/mmz4281/2425/D2.csv',
    'Italia_Serie_A.csv' => 'https://www.football-data.co.uk/mmz4281/2425/I1.csv',
    'Italia_Serie_B.csv' => 'https://www.football-data.co.uk/mmz4281/2425/I2.csv',
    'Espanha_La_Liga.csv' => 'https://www.football-data.co.uk/mmz4281/2425/SP1.csv',
    'Espanha_Segunda_Div.csv' => 'https://www.football-data.co.uk/mmz4281/2425/SP2.csv',
    'Franca_Ligue_1.csv' => 'https://www.football-data.co.uk/mmz4281/2425/F1.csv',
    'Franca_Ligue_2.csv' => 'https://www.football-data.co.uk/mmz4281/2425/F2.csv',
    'Holanda_Eredivisie.csv' => 'https://www.football-data.co.uk/mmz4281/2425/N1.csv',
    'Belgica_Pro_League.csv' => 'https://www.football-data.co.uk/mmz4281/2425/B1.csv',
    'Portugal_Liga_1.csv' => 'https://www.football-data.co.uk/mmz4281/2425/P1.csv',
    'Turquia_Super_Lig.csv' => 'https://www.football-data.co.uk/mmz4281/2425/T1.csv',
    'Grecia_Super_League.csv' => 'https://www.football-data.co.uk/mmz4281/2425/G1.csv',
    'Austria_Bundesliga.csv' => 'https://www.football-data.co.uk/new/AUT.csv',
    'Dinamarca_Superliga.csv' => 'https://www.football-data.co.uk/new/DNK.csv',
    'Finlandia_Veikkausliiga.csv' => 'https://www.football-data.co.uk/new/FIN.csv',
    'Irlanda_Premier.csv' => 'https://www.football-data.co.uk/new/IRL.csv',
    'Noruega_Eliteserien.csv' => 'https://www.football-data.co.uk/new/NOR.csv',
    'Polonia_Ekstraklasa.csv' => 'https://www.football-data.co.uk/new/POL.csv',
    'Romenia_Liga_1.csv' => 'https://www.football-data.co.uk/new/ROU.csv',
    'Russia_Premier.csv' => 'https://www.football-data.co.uk/new/RUS.csv',
    'Suecia_Allsvenskan.csv' => 'https://www.football-data.co.uk/new/SWE.csv',
    'Suica_Super_League.csv' => 'https://www.football-data.co.uk/new/SWZ.csv',
    'EUA_MLS.csv' => 'https://www.football-data.co.uk/new/USA.csv',
    'Japao_J_League.csv' => 'https://www.football-data.co.uk/new/JPN.csv',
    'Mexico_Liga_MX.csv' => 'https://www.football-data.co.uk/new/MEX.csv',
    'China_Super_League.csv' => 'https://www.football-data.co.uk/new/CHN.csv',
];
// Helpers
function encontrarIndice($cabecalho, $possiveisNomes) {
    foreach ($cabecalho as $index => $coluna) {
        $colLimpa = strtolower(trim($coluna));
        foreach ($possiveisNomes as $nome) { if ($colLimpa == strtolower($nome)) return $index; }
    } return -1;
}
function safeFloat($val) { $val = str_replace(',', '.', $val); return floatval($val); }

$msg = "";
$dbGames = [];
$upcomingGames = [];
$qtdArquivosBaixados = 0;
$dataUltimaAtualizacao = "Verifique o Admin";

// --- HELPER PARA COMPARAR NOMES ---
function normalizeName($str) {
    $str = strtolower($str);
    // Remove termos comuns para facilitar o "match"
    $str = str_replace(['fc', 'ec', 'sc', 'ac', 'sport', 'club', 'clube', 'athletic', 'atletico', 'real', 'city', 'utd', 'united', 'association'], '', $str);
    return preg_replace('/[^a-z0-9]/', '', $str);
}

// --- NOVO BLOCO: BUSCAR PLACARES NA API ---
if (isset($_POST['acao']) && $_POST['acao'] == 'atualizar_placares_api') { 
        $activeTab = 'finished';
        $dataBusca = $_POST['data_busca'];
        
        // 1. Busca todos os jogos finalizados do dia na API 2
        $url = "https://v3.football.api-sports.io/fixtures?date={$dataBusca}&status=FT";
        
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "x-rapidapi-key: $apiKeyFootball\r\n" .
                            "x-rapidapi-host: v3.football.api-sports.io\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $json = @file_get_contents($url, false, $context);
        
        if ($json) {
            $apiData = json_decode($json, true);
            $apiGames = isset($apiData['response']) ? $apiData['response'] : [];
            
            $atualizados = 0;
            $novos = 0;
            
            $sql = "INSERT INTO scanner_historico 
                    (liga, data_jogo, time_casa, time_fora, odd_casa, odd_empate, odd_fora, placar_real) 
                    VALUES (?, ?, ?, ?, 0, 0, 0, ?) 
                    ON DUPLICATE KEY UPDATE placar_real = VALUES(placar_real)";
            
            $stmtUpsert = $pdo->prepare($sql);

            foreach ($apiGames as $game) {
                $ligaId = "API2_" . $game['league']['id']; 
                $dataFmt = date('Y-m-d H:i:s', strtotime($game['fixture']['date']));
                $home = $game['teams']['home']['name'];
                $away = $game['teams']['away']['name'];
                $gh = $game['goals']['home'];
                $ga = $game['goals']['away'];
                $placar = "$gh - $ga";

                try {
                    $stmtUpsert->execute([$ligaId, $dataFmt, $home, $away, $placar]);
                    if ($stmtUpsert->rowCount() == 1) $novos++;
                    else $atualizados++;
                } catch (Exception $e) {}
            }
$msg = "<div class='notification notif-success' style='justify-content: center; flex-wrap: wrap; gap: 20px;'>
            <span style='display:flex; align-items:center; gap:5px;'>✅ Processo concluído para <b>$dataBusca</b>!</span>
            <span style='display:flex; align-items:center; gap:5px;'>🔄 Atualizados: <b>$atualizados</b></span>
            <span style='display:flex; align-items:center; gap:5px;'>📥 Importados do zero: <b>$novos</b></span>
        </div>";        } else {
            $msg = "<div class='notification notif-error'><span>❌</span> Erro de conexão API.</div>";
        }
    }

// --- PROCESSAMENTO DO POST (AÇÕES) ---
if (isset($_POST['acao'])) {
    
    // 1. ATUALIZAR HISTÓRICO (AGORA NO MYSQL)
    if ($_POST['acao'] == 'atualizar_csv') {
        $activeTab = 'history';
        array_map('unlink', glob("*.csv"));
        $sucesso = 0;
        $context = stream_context_create(["http" => ["method" => "GET", "header" => "User-Agent: Mozilla/5.0\r\n"]]);
        foreach ($lista_campeonatos as $nome_arquivo => $url) {
            $conteudo = @file_get_contents($url, false, $context);
            if ($conteudo) { file_put_contents($nome_arquivo, $conteudo); $sucesso++; }
        }
        $pdo->exec("TRUNCATE TABLE historico_jogos"); 
        $arquivosLocais = glob("*.csv");
        $totalInseridos = 0;
        $stmt = $pdo->prepare("INSERT INTO historico_jogos (liga, data_jogo, hora_jogo, time_casa, time_fora, gols_casa, gols_fora, ht_casa, ht_fora, odd_casa, odd_empate, odd_fora) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $pdo->beginTransaction();
        foreach ($arquivosLocais as $arquivo) {
            if (($handle = fopen($arquivo, "r")) !== FALSE) {
                $header = fgetcsv($handle, 2000, ",");
                $idxDate = encontrarIndice($header, ['Date', 'Data']);
                $idxTime = encontrarIndice($header, ['Time', 'Hora']);
                $idxHome = encontrarIndice($header, ['HomeTeam', 'Home', 'Mandante']);
                $idxAway = encontrarIndice($header, ['AwayTeam', 'Away', 'Visitante']);
                $idxHG = encontrarIndice($header, ['FTHG', 'HG', 'GoalsH']);
                $idxAG = encontrarIndice($header, ['FTAG', 'AG', 'GoalsA']);
                $idxHTHG = encontrarIndice($header, ['HTHG', 'HalfTimeHG']);
                $idxHTAG = encontrarIndice($header, ['HTAG', 'HalfTimeAG']);
                $idxOddH = encontrarIndice($header, ['B365H', 'PSCH', 'AvgH', 'MaxH']);
                $idxOddD = encontrarIndice($header, ['B365D', 'PSCD', 'AvgD', 'MaxD']);
                $idxOddA = encontrarIndice($header, ['B365A', 'PSCA', 'AvgA', 'MaxA']);
                $nomeCamp = str_replace(['_', '-'], ' ', pathinfo($arquivo, PATHINFO_FILENAME));
                while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
                    if ($idxHome < 0 || !isset($data[$idxHome])) continue;
                    $rawDate = isset($data[$idxDate]) ? $data[$idxDate] : '';
                    $dt = DateTime::createFromFormat('d/m/Y', $rawDate);
                    if (!$dt) $dt = DateTime::createFromFormat('d/m/y', $rawDate);
                    if (!$dt) continue;
                    $isoDate = $dt->format('Y-m-d');
                    $rawTime = ($idxTime >= 0 && isset($data[$idxTime])) ? $data[$idxTime] : '';
                    $oddH = ($idxOddH >= 0 && isset($data[$idxOddH])) ? safeFloat($data[$idxOddH]) : 0;
                    $oddD = ($idxOddD >= 0 && isset($data[$idxOddD])) ? safeFloat($data[$idxOddD]) : 0;
                    $oddA = ($idxOddA >= 0 && isset($data[$idxOddA])) ? safeFloat($data[$idxOddA]) : 0;
                    $gh = (int)$data[$idxHG]; $ga = (int)$data[$idxAG];
                    $hth = ($idxHTHG >= 0 && isset($data[$idxHTHG]) && is_numeric($data[$idxHTHG])) ? (int)$data[$idxHTHG] : null;
                    $hta = ($idxHTAG >= 0 && isset($data[$idxHTAG]) && is_numeric($data[$idxHTAG])) ? (int)$data[$idxHTAG] : null;
                    $stmt->execute([$nomeCamp, $isoDate, $rawTime, $data[$idxHome], $data[$idxAway], $gh, $ga, $hth, $hta, $oddH, $oddD, $oddA]);
                    $totalInseridos++;
                }
                fclose($handle);
                @unlink($arquivo);
            }
        }
        $pdo->commit();
        $msg = "<div class='notification notif-success'><span>✅</span> Base SQL Atualizada! Jogos importados: <b>$totalInseridos</b>.</div>";
        $dataUltimaAtualizacao = date("d/m/Y H:i");
        $qtdArquivosBaixados = $sucesso;
    }

    // 2. ATUALIZAR API (SALVA NO BANCO IMEDIATAMENTE)
    if ($_POST['acao'] == 'atualizar_api') {
        $activeTab = 'future'; 
        $ligaKey = isset($_POST['liga_api']) ? $_POST['liga_api'] : ''; 
        
        // Correção de chaves antigas se necessário
        $correcoes = ['soccer_colombia_primera_a' => 'API2_239', 'soccer_russia_premier_league' => 'API2_235', 'soccer_china_superleague' => 'API2_169'];
        if (isset($correcoes[$ligaKey])) $ligaKey = $correcoes[$ligaKey];
        
        $autoSelectLeague = $ligaKey;
        $newCache = [];
        
        // Mantém o cache de outras ligas para não sumir
        $currentCache = file_exists($apiCacheFile) ? json_decode(file_get_contents($apiCacheFile), true) : [];
        $newCache = array_filter($currentCache, function($item) use ($ligaKey) { return $item['sport_key'] !== $ligaKey; });

        if($ligaKey) {
            // --- CONEXÃO API ---
            // (Lógica da API 2 ou 1 conforme sua chave)
            if (strpos($ligaKey, 'API2_') === 0) {
                // ... Lógica API 2 ...
                $idLiga = str_replace('API2_', '', $ligaKey); 
                $ano = date('Y');
                $url = "https://v3.football.api-sports.io/odds?league={$idLiga}&season={$ano}&bookmaker=6"; 
                $opts = ["http" => ["method" => "GET", "header" => "x-rapidapi-key: $apiKeyFootball\r\n" . "x-rapidapi-host: v3.football.api-sports.io\r\n"]];
                $json = @file_get_contents($url, false, stream_context_create($opts));
                if ($json) {
                    $data = json_decode($json, true);
                    if(isset($data['response'])) {
                        foreach($data['response'] as $g) {
                            $hTeam = $g['teams']['home']['name']; 
                            $aTeam = $g['teams']['away']['name'];
                            $oddsH=0; $oddsD=0; $oddsA=0;
                            if(isset($g['bookmakers'][0]['bets'][0]['values'])) {
                                foreach($g['bookmakers'][0]['bets'][0]['values'] as $v) {
                                    if($v['value']=='Home') $oddsH=$v['odd'];
                                    if($v['value']=='Draw') $oddsD=$v['odd'];
                                    if($v['value']=='Away') $oddsA=$v['odd'];
                                }
                            }
                            if($oddsH > 0) {
                                $newCache[] = [
                                    'sport_key' => $ligaKey,
                                    'commence_time' => $g['fixture']['date'],
                                    'home_team' => $hTeam,
                                    'away_team' => $aTeam,
                                    'odds_h' => $oddsH, 
                                    'odds_d' => $oddsD, 
                                    'odds_a' => $oddsA
                                ];
                            }
                        }
                        $msg = "<div class='notification notif-success'><span>✅</span> Dados da API 2 processados!</div>";
                    }
                }
            } else {
                // ... Lógica API 1 ...
                $url = "https://api.the-odds-api.com/v4/sports/{$ligaKey}/odds/?apiKey={$apiKey}&regions=eu,uk&markets=h2h&bookmakers=bet365,pinnacle";
                $opts = ["http" => ["method" => "GET", "header" => "User-Agent: Mozilla/5.0\r\n", "ignore_errors" => true]];
                $json = @file_get_contents($url, false, stream_context_create($opts));
                if ($json) {
                    $data = json_decode($json, true);
                    if (!isset($data['message'])) {
                        foreach($data as $g) {
                            $oddsH=0; $oddsD=0; $oddsA=0;
                            if(isset($g['bookmakers'][0]['markets'][0]['outcomes'])) {
                                foreach($g['bookmakers'][0]['markets'][0]['outcomes'] as $o) {
                                    if($o['name']==$g['home_team']) $oddsH=$o['price'];
                                    elseif($o['name']==$g['away_team']) $oddsA=$o['price'];
                                    else $oddsD=$o['price'];
                                }
                            }
                            $newCache[] = [
                                'sport_key' => $g['sport_key'],
                                'commence_time' => $g['commence_time'],
                                'home_team' => $g['home_team'],
                                'away_team' => $g['away_team'],
                                'odds_h' => $oddsH, 
                                'odds_d' => $oddsD, 
                                'odds_a' => $oddsA
                            ];
                        }
                        $msg = "<div class='notification notif-success'><span>✅</span> Dados da API 1 processados!</div>";
                    }
                }
            }

            // Ordena e Salva Cache
            usort($newCache, function($a, $b) { return strcmp($a['commence_time'], $b['commence_time']); });
            file_put_contents($apiCacheFile, json_encode($newCache));

            // >>> CORREÇÃO PRINCIPAL: SALVAR NO MYSQL AGORA <<<
            if(!empty($newCache)) {
                $sqlInsert = "INSERT IGNORE INTO scanner_historico 
                              (liga, data_jogo, time_casa, time_fora, odd_casa, odd_empate, odd_fora) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtScanner = $pdo->prepare($sqlInsert);
                $jogosSalvos = 0;
                
                foreach($newCache as $g) {
                    // Formata data corretamente para o MySQL
                    $dataFmt = date('Y-m-d H:i:s', strtotime($g['commence_time']));
                    
                    $stmtScanner->execute([
                        $g['sport_key'], 
                        $dataFmt, 
                        $g['home_team'], 
                        $g['away_team'], 
                        $g['odds_h'], 
                        $g['odds_d'], 
                        $g['odds_a']
                    ]);
                    if($stmtScanner->rowCount() > 0) $jogosSalvos++;
                }
                
                // Feedback mais claro
                $msg = "<div class='notification notif-success'><span>💾</span> Scan Concluído! <b>$jogosSalvos</b> novos jogos salvos no Histórico.</div>";
            }
        }
    }
    
    // 3. EXPORTAR BACKUP (CSV)
    if ($_POST['acao'] == 'exportar_backup') {
        $filename = "Backup_FutAnalises_" . date('Y-m-d_H-i') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['ID', 'Liga', 'Data', 'Hora', 'Time Casa', 'Time Fora', 'Gols H', 'Gols A', 'HT H', 'HT A', 'Odd H', 'Odd D', 'Odd A']);
        $stmt = $pdo->query("SELECT id, liga, data_jogo, hora_jogo, time_casa, time_fora, gols_casa, gols_fora, ht_casa, ht_fora, odd_casa, odd_empate, odd_fora FROM historico_jogos");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit(); 
    }
}

// 4. SALVAR FEEDBACK E PALPITE (PERSISTÊNCIA)
    if (isset($_POST['acao']) && $_POST['acao'] == 'salvar_feedback') {
        $timeCasa = $_POST['casa'];
        $timeFora = $_POST['fora'];
        $dataJogo = $_POST['data'];
        $resultado = $_POST['resultado']; // 'WIN', 'LOSS', 'VOID'
        $palpite = $_POST['palpite'];     // 'HOME', 'AWAY', 'OVER', 'SKIP'

        // Atualiza o registro existente
        $sql = "UPDATE scanner_historico SET 
                resultado_real = ?, 
                ia_status = ?, 
                ia_palpite = ? 
                WHERE time_casa = ? AND time_fora = ? AND DATE(data_jogo) = DATE(?)";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$resultado, $resultado, $palpite, $timeCasa, $timeFora, $dataJogo]);
        
        echo "OK"; 
        exit();
    }

// ==========================================================================
// 📥 CARREGAMENTO DE DADOS (MYSQL -> FRONTEND)
// ==========================================================================
try {
    $stmt = $pdo->query("SELECT liga as c, data_jogo as d, hora_jogo as t, DATE_FORMAT(data_jogo, '%d/%m/%Y') as dr, time_casa as h, time_fora as a, gols_casa as gh, gols_fora as ga, ht_casa as hth, ht_fora as hta, odd_casa as oh, odd_empate as od, odd_fora as oa FROM historico_jogos ORDER BY data_jogo DESC");
    $dbGames = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $qtdArquivosBaixados = count($lista_campeonatos);
    $dataUltimaAtualizacao = "Base SQL";
} catch (Exception $e) {
    $msg = "<div class='notification notif-error'>Erro ao ler Banco de Dados.</div>";
}

if (file_exists($apiCacheFile)) {
    $upcomingGames = json_decode(file_get_contents($apiCacheFile), true);
}

// SEPARAÇÃO DE JOGOS (HOJE/FUTURO)
$gamesFuture = []; 
$todayDate = date('Y-m-d'); 

// SEPARAÇÃO DE JOGOS (ABERTOS vs FINALIZADOS)
$gamesOpen = [];
$gamesFinished = [];
$now = time(); // Hora atual do servidor

if(is_array($upcomingGames)){
    foreach($upcomingGames as $g) {
        $timestamp = strtotime($g['commence_time']);
        
        // Se o jogo já passou do horário atual, vai para finalizados
        if($timestamp < $now) {
            $gamesFinished[] = $g;
        } else {
            // Se ainda não começou, vai para abertos
            $gamesOpen[] = $g;
        }
    }
}

// Ordena: Abertos (Cronológico), Finalizados (Mais recentes primeiro)
usort($gamesOpen, function($a, $b) { return strcmp($a['commence_time'], $b['commence_time']); });
usort($gamesFinished, function($a, $b) { return strcmp($b['commence_time'], $a['commence_time']); }); // Inverso para ver o último jogo primeiro

$ligasDisponiveisAPI = [];
if(is_array($upcomingGames)){
    foreach($upcomingGames as $g) {
        $k = $g['sport_key'];
        $nomeBonito = isset($ligas_api_reverse[$k]) ? $ligas_api_reverse[$k] : $k;
        $ligasDisponiveisAPI[$k] = $nomeBonito;
    }
}
ksort($ligas_api); 
asort($ligasDisponiveisAPI); 

$uniqueTeams = [];
foreach($dbGames as $g) { $uniqueTeams[$g['h']] = true; $uniqueTeams[$g['a']] = true; }
ksort($uniqueTeams);
asort($ligasDisponiveisAPI); 

$uniqueTeams = [];
foreach($dbGames as $g) { $uniqueTeams[$g['h']] = true; $uniqueTeams[$g['a']] = true; }
ksort($uniqueTeams); 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>FUT ANALISES</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/53/53283.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root { --bg-dark: #111; --card-dark: #1a1a1a; --text-main: #ddd; --accent-blue: #60a5fa; --accent-green: #10b981; --accent-red: #ef4444; --accent-orange: #f97316; --accent-purple: #8b5cf6; --accent-yellow: #f59e0b; }
        body { font-family: 'Montserrat', sans-serif; background: #0a0a0a; color: var(--text-main); margin: 0; padding: 15px; font-size: 13px; }
        .container { max-width: 1280px; margin: 0 auto; }
        .header-branding { text-align: center; margin-bottom: 25px; }
        .brand-logo { font-size: 32px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; background: linear-gradient(to right, #fff, #888); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .brand-sub { color: var(--accent-blue); font-weight: 400; font-size: 20px; }
        .notification { padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; gap: 10px; animation: slideIn 0.3s ease-out; }
        .notif-success { background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; }
        .notif-error { background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; }
        @keyframes slideIn { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
        .nav-tabs { display: flex; gap: 10px; margin-bottom: 25px; }
        .nav-btn { background: #222; border: 1px solid #333; color: #888; padding: 12px 0; border-radius: 4px; font-weight: bold; cursor: pointer; text-transform: uppercase; font-size: 13px; transition: 0.3s; flex:1; max-width:none; text-align:center; }
        .nav-btn.active { background: var(--accent-blue); color: #fff; border-color: var(--accent-blue); transform: translateY(-2px); box-shadow: 0 4px 10px rgba(96, 165, 250, 0.3); }
        .nav-btn:hover:not(.active) { border-color: #666; color: #fff; }
        .view-section { display: none; animation: fadeIn 0.5s; } .view-section.active { display: block; } @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .dashboard { display: grid; grid-template-columns: 260px 1fr; gap: 20px; align-items: start; }
        .card { background: var(--card-dark); border: 1px solid #333; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); transition: all 0.3s ease; }
        .card-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
        h2 { font-size: 14px; color: #888; margin: 0; text-transform: uppercase; }
        .toggle-btn { background: none; border: none; color: #666; cursor: pointer; font-size: 14px; font-weight: bold; }
        .filter-group { margin-bottom: 12px; } label { display: block; font-weight: 700; margin-bottom: 4px; font-size: 11px; color: #666; text-transform: uppercase; }
        select, input { width: 100%; padding: 8px; background: #222; border: 1px solid #444; border-radius: 6px; color: #fff; font-size: 12px; box-sizing: border-box; }
        select:focus, input:focus { border-color: var(--accent-blue); outline: none; }
        .date-row { display: flex; gap: 5px; } .date-row input { flex: 1; min-width: 0; }
        .btn { width: 100%; padding: 10px; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; margin-top: 5px; font-size: 12px; }
        .btn-blue { background: var(--accent-blue); color: white; } .btn-green { background: var(--accent-green); color: white; } .btn-purple { background: var(--accent-purple); color: white; }
        .top-stats-row { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .top-card { flex: 1; background: var(--card-dark); border: 1px solid #333; padding: 15px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; min-width: 150px; box-shadow: 0 4px 6px rgba(0,0,0,0.2); border-bottom: 3px solid transparent; }
        .top-card-info h4 { margin: 0; color: #888; font-size: 10px; text-transform: uppercase; } .top-card-info div { font-size: 24px; font-weight: 900; color: #fff; margin-top: 5px; } .top-card-icon { font-size: 24px; opacity: 0.5; }
        .stats-menu { display: flex; justify-content: center; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; }
        .stats-pill { background: #222; border: 1px solid #333; color: #888; padding: 5px 15px; border-radius: 20px; cursor: pointer; font-size: 11px; font-weight: 700; transition: 0.2s; } .stats-pill.active { background: var(--accent-blue); color: #fff; border-color: var(--accent-blue); }
        .strat-builder { background: #222; padding: 15px; border-radius: 8px; border: 1px solid #333; margin-bottom: 15px; }
        .strat-row { display: flex; gap: 10px; margin-bottom: 10px; } .strat-row > div { flex: 1; }
        .kpi-row { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .kpi-box { flex: 1; min-width: 120px; background: #222; border: 1px solid #333; border-left: 3px solid var(--accent-blue); padding: 15px; border-radius: 8px; } .kpi-val { font-size: 20px; font-weight: 700; color: #fff; } .kpi-lbl { font-size: 10px; color: #888; text-transform: uppercase; margin-top: 5px; }
        .ai-content { background: #222; padding: 15px; border-radius: 8px; border: 1px solid #333; font-family: 'Courier New', monospace; color: #0f0; font-size: 12px; line-height: 1.6; min-height: 80px; position:relative; overflow: hidden; }
        .ai-content::before { content: "⚡ AI (PATTERN MATCHING)"; position: absolute; top: 0; right: 0; background: rgba(0,255,0,0.1); color: #0f0; font-size: 9px; padding: 2px 6px; border-bottom-left-radius: 6px; }
        .scan-filters { display: flex; gap: 5px; flex-wrap: wrap; } .badge-filter { background: #333; border: 1px solid #444; color: #aaa; padding: 4px 10px; border-radius: 15px; font-size: 10px; cursor: pointer; transition: 0.2s; } .badge-filter.active { background: var(--accent-blue); color: #fff; border-color: var(--accent-blue); }
        .sug-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 15px; } .sug-card { background: #151515; padding: 10px; border-radius: 6px; border-left: 4px solid #555; font-size: 11px; } .sug-card.win-h { border-color: var(--accent-green); } .sug-card.win-a { border-color: var(--accent-red); } .sug-card.goals { border-color: var(--accent-orange); } .sug-title { font-weight: bold; margin-bottom: 4px; color: #fff; font-size: 12px; } .sug-reason { color: #aaa; }
        .stats-flex-container { display: flex; align-items: center; gap: 20px; } .stats-legend-box { background: #222; border-radius: 8px; padding: 15px; border: 1px solid #333; min-width: 150px; } .stat-big-num { font-size: 42px; font-weight: 900; color: var(--accent-green); text-shadow: 0 0 15px rgba(16,185,129,0.4); line-height: 1; text-align: center; margin-bottom: 5px; } .stat-sub-label { text-align: center; font-size: 11px; color: #888; text-transform: uppercase; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px; } .stat-list { display: flex; flex-direction: column; gap: 8px; } .stat-item { font-size: 12px; display: flex; align-items: center; justify-content: space-between; color: #ddd; } .dot { width: 8px; height: 8px; border-radius: 50%; margin-right: 5px; display: inline-block; } .dot-g { background: var(--accent-green); } .dot-y { background: var(--accent-yellow); } .dot-r { background: var(--accent-red); }
        #btnScanMore { grid-column: 1 / -1; width: 100%; display: block; margin-top: 15px; padding: 12px 0; background: rgba(255, 255, 255, 0.03); border: 1px dashed var(--accent-blue); color: var(--accent-blue); border-radius: 8px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; transition: all 0.3s ease; } #btnScanMore:hover { background: rgba(96, 165, 250, 0.1); border-style: solid; box-shadow: 0 0 15px rgba(96, 165, 250, 0.2); color: #fff; transform: translateY(-2px); }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; } table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 600px; } th { background: #222; color: #888; padding: 12px; text-align: center; cursor: pointer; font-size: 10px; letter-spacing: 1px; } td { padding: 12px 10px; border-bottom: 1px solid #2a2a2a; vertical-align: middle; } tr:nth-child(even) td { background-color: #161616; } .text-win { color: var(--accent-green); font-weight: 700; } .text-loss { color: var(--accent-red); font-weight: 700; opacity: 0.9; } .text-draw { color: var(--accent-yellow); font-weight: 700; } .badge-res { display: inline-block; width: 18px; height: 18px; line-height: 18px; text-align: center; font-size: 10px; font-weight: bold; border-radius: 50%; color: #fff; margin: 0 2px; vertical-align: middle; cursor: help; } .badge-v { background-color: var(--accent-green); } .badge-e { background-color: var(--accent-yellow); color: #000; } .badge-d { background-color: var(--accent-red); } .score { background: #000; color: #fff; padding: 6px 14px; border-radius: 8px; font-weight: 900; border: 1px solid #333; font-family: monospace; font-size: 15px; display: inline-block; margin-bottom: 4px; cursor: help; } .ht-card { display: inline-block; background: #222; border: 1px solid #444; color: #aaa; font-size: 10px; padding: 2px 8px; border-radius: 12px; margin-bottom: 4px; font-weight: 700; cursor: help; } .market-badge { display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; color: white; text-transform: uppercase; cursor: help; } .market-over { background-color: var(--accent-orange); } .market-btts { background-color: var(--accent-purple); } .camp-tag { font-size: 9px; color: var(--accent-blue); display: block; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; opacity: 0.9; } .winner-odd { color: var(--accent-green); font-weight: 700; }
        .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; display: none; flex-direction: column; justify-content: center; align-items: center; color: white; } .spinner { border: 4px solid #222; border-top: 4px solid var(--accent-blue); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin-bottom: 15px; } @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } } footer { margin-top: 30px; border-top: 1px solid #343434ff; padding-top: 20px; text-align: center; color: #ffffffff; font-size: 11px; }
        .backtest-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top:15px; } .backtest-table th { text-align: left; color: #888; padding: 8px; border-bottom: 1px solid #333; background:#1a1a1a; } .backtest-table td { padding: 8px; border-bottom: 1px solid #222; color: #ddd; vertical-align: middle; } tr.row-win td { background-color: rgba(16,185,129,0.08) !important; border-left: 3px solid var(--accent-green); } tr.row-loss td { background-color: rgba(239,68,68,0.08) !important; border-left: 3px solid var(--accent-red); } tr.selected-row td { background-color: rgba(96, 165, 250, 0.2) !important; border-top: 1px solid var(--accent-blue); border-bottom: 1px solid var(--accent-blue); color: #fff; }
        .ai-btn-small { border:none; background:var(--accent-blue); color:#fff; cursor:pointer; padding:3px 8px; border-radius:4px; font-size:10px; margin-left:5px; font-weight:bold; transition:0.2s; } .ai-btn-small:hover { background: #fff; color: var(--accent-blue); transform: scale(1.1); }
        .filter-bar { display: flex; gap: 10px; margin-bottom: 15px; } .filter-btn { background: #222; border: 1px solid #444; color: #aaa; padding: 5px 12px; border-radius: 20px; font-size: 11px; cursor: pointer; } .filter-btn.active { background: var(--accent-blue); color: #fff; border-color: var(--accent-blue); }
        .row-checked td { background-color: #111 !important; color: #555 !important; } .row-checked button { opacity: 0.5; } .game-check { accent-color: var(--accent-blue); transform: scale(1.3); cursor: pointer; }
        .past-games-container { opacity: 0.7; } .past-games-container tr td { color: #888 !important; } .hidden-past-row { display: none; }
        .select2-container--default .select2-selection--single { background-color: #222 !important; border: 1px solid #444 !important; color: #fff !important; height: 38px !important; } .select2-container--default .select2-selection--single .select2-selection__rendered { color: #ddd !important; line-height: 36px !important; } .select2-dropdown { background-color: #222 !important; border: 1px solid #444 !important; } .select2-search__field { background-color: #333 !important; color: #fff !important; } .select2-results__option--highlighted { background-color: var(--accent-blue) !important; }
        @media (max-width: 768px) { body { padding: 10px; font-size: 12px; } .dashboard { grid-template-columns: 1fr; gap: 15px; } .sidebar { margin-bottom: 10px; } .brand-logo { font-size: 24px; } .brand-sub { font-size: 16px; } .top-stats-row { gap: 8px; margin-bottom: 15px; } .top-card { min-width: 0; flex: 1 1 45%; padding: 10px; } .top-card-info div { font-size: 18px; margin-top: 2px; } .top-card-icon { font-size: 18px; } .card { padding: 12px; margin-bottom: 15px; } h2 { font-size: 12px; } .kpi-row { gap: 8px; margin-bottom: 15px; } .kpi-box { min-width: 0; flex: 1 1 45%; padding: 10px; } .kpi-val { font-size: 18px; } .kpi-lbl { font-size: 9px; } td, th { padding: 8px 4px; font-size: 11px; } .score { padding: 3px 8px; font-size: 12px; } input, select, .btn { font-size: 12px; height: 36px; padding: 5px; } .stats-flex-container { flex-direction: column; } .stats-legend-box { width: 100%; box-sizing: border-box; } }
        .status-bar { display: flex; justify-content: center; gap: 25px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #222; } .status-item { display: flex; align-items: center; gap: 8px; font-size: 10px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: 1px; } .led { width: 8px; height: 8px; border-radius: 50%; transition: 0.3s; } .led-green { background-color: #10b981; box-shadow: 0 0 8px rgba(16, 185, 129, 0.6); } .led-red { background-color: #ef4444; box-shadow: 0 0 8px rgba(239, 68, 68, 0.6); } .blink { animation: blinker 1.5s linear infinite; } @keyframes blinker { 50% { opacity: 0.3; } }
        
        /* LOGOS TEAM STYLE */
        .team-logo { width: 20px; height: 20px; vertical-align: middle; margin: 0 5px; object-fit: contain; filter: drop-shadow(0 0 2px rgba(0,0,0,0.5)); <?php echo $displayStyle; ?> }
    
        /* CSS DO VEREDITO DA IA */
        .verdict-box {
            margin-top: 15px;
            background: linear-gradient(135deg, #1a1a1a 0%, #0f1520 100%);
            border: 1px solid var(--accent-blue);
            border-radius: 8px;
            padding: 15px;
            position: relative;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
        .verdict-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            border-bottom: 1px solid #333;
            padding-bottom: 8px;
        }
        .verdict-icon { font-size: 20px; }
        .verdict-title { font-weight: 900; color: #fff; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
        .verdict-content { font-size: 13px; color: #ccc; line-height: 1.5; }
        .recommendation-tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
            margin-top: 8px;
            color: #000;
        }
        .rec-high { background: var(--accent-green); box-shadow: 0 0 10px rgba(16,185,129,0.4); }
        .rec-med { background: var(--accent-yellow); }
        .rec-low { background: var(--accent-red); color: white; }
        .rec-skip { background: #555; color: #ccc; }

        /* CSS PARA JOGOS DE HOJE JÁ FINALIZADOS */
        .game-finished {
            opacity: 0.4; /* Deixa apagadinho */
            background-color: #1a1a1a;
            filter: grayscale(100%); /* Tira a cor dos logos */
        }
        .game-finished:hover {
            opacity: 1; /* Se passar o mouse, acende para ver */
            filter: grayscale(0%);
            transition: 0.3s;
        }

        /* Botão Voltar dentro do Card da IA */
        .btn-back-ai {
            background: transparent;
            border: 1px solid #444;
            color: #888;
            width: 100%;
            padding: 8px;
            margin-top: 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.2s;
        }
        .btn-back-ai:hover { background: #333; color: #fff; border-color: #666; }

        /* Efeito de Flash quando volta para a linha */
        @keyframes flashRow { 0% { background-color: var(--accent-blue); } 100% { background-color: transparent; } }
        .row-highlight { animation: flashRow 1.5s ease-out; }

/* Botão Voltar na IA */
.btn-back-ai {
    width: 100%; padding: 10px; margin-top: 15px; 
    background: transparent; border: 1px solid #444; 
    color: #888; border-radius: 6px; cursor: pointer; 
    font-weight: bold; text-transform: uppercase; transition: 0.2s;
}
.btn-back-ai:hover { background: #333; color: #fff; border-color: #666; }

/* Efeito de destaque na linha ao voltar */
@keyframes flashRow { 0% { background-color: var(--accent-blue); } 100% { background-color: transparent; } }
.row-highlight { animation: flashRow 1.5s ease-out; }

/* Cores de Resultado da IA */
.row-green td { 
    background: rgba(16, 185, 129, 0.15) !important; /* Verde Transparente */
    border-top: 1px solid #10b981; 
    border-bottom: 1px solid #10b981; 
    color: #fff !important;
}
.row-red td { 
    background: rgba(239, 68, 68, 0.15) !important; /* Vermelho Transparente */
    border-top: 1px solid #ef4444; 
    border-bottom: 1px solid #ef4444;
}
.row-yellow td { 
    background: rgba(245, 158, 11, 0.15) !important; /* Amarelo (Atenção/Void) */
    border-top: 1px solid #f59e0b; 
    border-bottom: 1px solid #f59e0b;
}

/* FORÇAR CORES NAS CÉLULAS DA TABELA */
tr.row-green td { 
    background-color: rgba(16, 185, 129, 0.3) !important; /* Verde mais forte */
    border-top: 1px solid #10b981 !important;
    border-bottom: 1px solid #10b981 !important;
    color: #fff !important;
}
tr.row-red td { 
    background-color: rgba(239, 68, 68, 0.3) !important; /* Vermelho mais forte */
    border-top: 1px solid #ef4444 !important;
    border-bottom: 1px solid #ef4444 !important;
    color: #fff !important;
}
tr.row-yellow td { 
    background-color: rgba(245, 158, 11, 0.2) !important; 
    border-top: 1px solid #f59e0b !important;
    border-bottom: 1px solid #f59e0b !important;
    color: #ddd !important;
}

/* --- MINI CARDS NA TABELA --- */
.mini-badge { 
    font-size: 9px; 
    padding: 3px 6px; 
    border-radius: 4px; 
    font-weight: 800; 
    margin-right: 4px; 
    display: inline-block; 
    color: #fff; 
    text-transform: uppercase;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    margin-bottom: 2px;
}
.badge-over { background: var(--accent-orange); border: 1px solid #c2410c; } /* Laranja */
.badge-btts { background: var(--accent-purple); border: 1px solid #7c3aed; } /* Roxo */
.badge-ht { background: var(--accent-blue); border: 1px solid #2563eb; }     /* Azul */
/* Estilo para o Placar HT (embaixo do Placar Final) */
.score-ht { 
    display: block; 
    font-size: 10px; 
    color: #888; 
    margin-top: 2px; 
    font-weight: normal; 
}

/* Badge para HT/FT (HF) */
.badge-hf { 
    background: #262626; 
    border: 1px solid #444; 
    color: #aaa; 
    font-family: monospace;
    letter-spacing: -1px;
}

/* --- BOTÃO VER MAIS (ESTILO NOVO) --- */
.btn-show-more {
    display: block;
    width: 100%;             /* Ocupa toda a largura */
    max-width: none;         /* Remove limite de tamanho */
    margin-top: 15px;        /* Espaço acima */
    padding: 15px 0;         /* Altura do clique */
    background: #222;        /* Fundo escuro padrão */
    color: #888;             /* Texto cinza discreto */
    text-align: center;
    border-radius: 8px;      /* Bordas arredondadas iguais aos cards */
    border: 1px dashed #444; /* Borda tracejada sutil */
    cursor: pointer;
    font-weight: 900;        /* Fonte grossa */
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 2px;     /* Espaçamento entre letras */
    transition: all 0.3s ease;
}

.btn-show-more:hover {
    background: rgba(96, 165, 250, 0.1); /* Fundo azul transparente */
    color: #fff;             /* Texto branco */
    border-color: var(--accent-blue); /* Borda fica azul sólida */
    border-style: solid;
    box-shadow: 0 0 15px rgba(96, 165, 250, 0.2); /* Brilho neon */
    transform: translateY(-2px); /* Leve subida */
}

/* --- DUAL RANGE SLIDER CSS --- */
.wrapper-slider { position: relative; width: 100%; height: 50px; margin-top: 5px; }
.container-slider { position: relative; width: 100%; height: 50px; }
input[type="range"].dual-range {
    -webkit-appearance: none; appearance: none; width: 100%; outline: none; position: absolute; margin: auto; top: 0; bottom: 0; background-color: transparent; pointer-events: none;
}
.slider-track { width: 100%; height: 5px; position: absolute; margin: auto; top: 0; bottom: 0; border-radius: 5px; background: #333; }
input[type="range"].dual-range::-webkit-slider-runnable-track { -webkit-appearance: none; height: 5px; }
input[type="range"].dual-range::-moz-range-track { -moz-appearance: none; height: 5px; }
input[type="range"].dual-range::-ms-track { appearance: none; height: 5px; }
/* O segredo: Habilitar o clique apenas na bolinha (thumb) */
input[type="range"].dual-range::-webkit-slider-thumb {
    -webkit-appearance: none; height: 18px; width: 18px; background-color: #fff; cursor: pointer; margin-top: -7px; pointer-events: auto; border-radius: 50%; border: 2px solid var(--accent-green); transition: 0.2s;
}
input[type="range"].dual-range::-webkit-slider-thumb:hover { transform: scale(1.2); background: var(--accent-green); }
input[type="range"].dual-range:active::-webkit-slider-thumb { background-color: #fff; border: 3px solid var(--accent-green); }

    </style>
</head>
<body>
<div id="overlay-loading" class="loading-overlay"><div class="spinner"></div><h3>PROCESSANDO...</h3></div>
<div class="main-wrapper">
    <div class="container">
        <div class="header-branding"><div class="brand-logo">FUT <span class="brand-sub">ANALISES</span></div></div>
        <?php echo $msg; ?>
        <div class="nav-tabs">
            <button class="nav-btn <?php echo $activeTab=='history'?'active':''; ?>" 
                    onclick="switchTab('history')" 
                    title="Acesse o banco de dados completo com todos os jogos passados e estatísticas históricas.">
                📂 HISTÓRICO
            </button>

            <button class="nav-btn <?php echo $activeTab=='backtest'?'active':''; ?>" 
                    onclick="switchTab('backtest')" 
                    title="Crie uma regra (ex: Casa Vence odd 1.50) e veja se ela teria dado lucro no passado.">
                ✅ VALIDAR ESTRATÉGIA
            </button>

            <button class="nav-btn <?php echo $activeTab=='future'?'active':''; ?>" 
                    onclick="switchTab('future')" 
                    title="Veja a lista completa de jogos que vão acontecer nos próximos dias.">
                🔭 TODOS JOGOS
            </button>
            
            <button class="nav-btn <?php echo $activeTab=='opportunities'?'active':''; ?>" 
                    onclick="switchTab('opportunities')" 
                    style="border-color:var(--accent-green); color:var(--accent-green);"
                    title="Jogos selecionados onde a IA encontrou alta probabilidade ou valor nas odds.">
                🔥 OPORTUNIDADES
            </button>
            
            <button class="nav-btn <?php echo $activeTab=='finished'?'active':''; ?>" 
                    onclick="switchTab('finished')" 
                    style="border-color:#444; color:#888;"
                    title="Confira os placares reais dos jogos de hoje e veja se a IA acertou ou errou.">
                🏁 FINALIZADOS
            </button>
        </div>

        <div id="view-history" class="view-section <?php echo $activeTab=='history'?'active':''; ?>">
            <div class="top-stats-row">
                <div class="top-card" style="border-bottom-color: #555;"><div class="top-card-info"><h4>Total (Hist)</h4><div id="kpiTotal">0</div></div><div class="top-card-icon">📊</div></div>
                <div class="top-card" style="border-bottom-color: var(--accent-green);"><div class="top-card-info"><h4>Vitórias Casa</h4><div id="kpiHome">0%</div></div><div class="top-card-icon" style="color:var(--accent-green)">🏠</div></div>
                <div class="top-card" style="border-bottom-color: var(--accent-yellow);"><div class="top-card-info"><h4>Empates</h4><div id="kpiDraw">0%</div></div><div class="top-card-icon" style="color:var(--accent-yellow)">⚖️</div></div>
                <div class="top-card" style="border-bottom-color: var(--accent-red);"><div class="top-card-info"><h4>Vitórias Fora</h4><div id="kpiAway">0%</div></div><div class="top-card-icon" style="color:var(--accent-red)">✈️</div></div>
            </div>
            <div class="dashboard">
                <div class="sidebar">
                    <div class="card">
                        <div class="card-header"><h2>Atualizar Dados</h2></div>
                        <form method="POST" onsubmit="document.getElementById('overlay-loading').style.display='flex'"><input type="hidden" name="acao" value="atualizar_csv"><button type="submit" class="btn btn-green">BAIXAR CSV -> MYSQL</button></form>
                        <p style="font-size:10px; color:#666; margin-top:5px; text-align:center;">Salva no banco 'fut_analises'</p>
                        <hr style="border-color:#333; margin:15px 0;">
                        <form method="POST" target="_blank">
                            <input type="hidden" name="acao" value="exportar_backup">
                            <button type="submit" class="btn" style="background:#333; color:#fff; border:1px solid #555;">💾 EXPORTAR BACKUP COMPLETO</button>
                        </form>
                        <p style="font-size:10px; color:#666; margin-top:5px; text-align:center;">Gera um CSV com toda a base SQL</p>
                    </div>
                    <div class="card">
                        <div class="card-header"><h2>Filtros Gerais</h2></div>
                        <div class="filter-group"><label>Campeonato</label><select id="selLeague" onchange="applyFilters()"><option value="">🌍 TODOS</option><?php $lgs = array_unique(array_column($dbGames, 'c')); sort($lgs); foreach($lgs as $lg) echo "<option value='$lg'>$lg</option>"; ?></select></div>
                        <div class="filter-group"><label>Período</label><div class="date-row"><input type="date" id="dateStart" onchange="applyFilters()"><input type="date" id="dateEnd" onchange="applyFilters()"></div></div>
                        <div class="filter-group"><label>Resultado / Mercado</label><select id="filterMarket" onchange="applyFilters()"><option value="">🎯 TODOS</option><option value="HOME_WIN">🏠 Casa Venceu (FT)</option><option value="DRAW">⚖️ Empate (FT)</option><option value="AWAY_WIN">✈️ Visitante Venceu (FT)</option><option value="OVER25">⚽ Over 2.5 Gols</option><option value="UNDER25">🛑 Under 2.5 Gols</option><option value="BTTS">🤝 Ambas Marcam (Sim)</option><option value="HT_HOME">1️⃣ HT - Casa Venceu</option><option value="HT_DRAW">1️⃣ HT - Empate</option><option value="HT_AWAY">1️⃣ HT - Fora Venceu</option></select></div>
                        <div class="filter-group"><label>Odd Casa</label><div class="date-row"><input type="number" id="filterOddMin" placeholder="Min" step="0.01" oninput="applyFilters()"><input type="number" id="filterOddMax" placeholder="Max" step="0.01" oninput="applyFilters()"></div></div>
                        <div class="filter-group">
                            <label>🔎 Filtrar por Time</label>
                            <input type="text" id="filterTeamHome" placeholder="Apenas Mandante (Casa)..." onkeyup="applyFilters()" style="margin-bottom:5px; border-left: 3px solid var(--accent-green);">
                            <input type="text" id="filterTeamAway" placeholder="Apenas Visitante (Fora)..." onkeyup="applyFilters()" style="margin-bottom:5px; border-left: 3px solid var(--accent-red);">
                            <input type="text" id="filterTeamAny" placeholder="Qualquer Posição..." onkeyup="applyFilters()" style="border-left: 3px solid #666;">
                        </div>
                    </div>
                </div>
                <div class="content">
                    <div class="card" id="cardAI" style="display:none;"><div class="card-header"><h2 style="color:#0f0;">🤖 Análise da IA (Histórico)</h2><button class="toggle-btn" onclick="toggleSection('aiHistoryBody', this)">[-]</button></div><div id="aiHistoryBody"><div class="ai-content" id="aiText">...</div></div></div>
                    
                    <div class="card" id="cardStats" style="display:none;">
                        <div class="card-header"><h2>Estatísticas</h2><button class="toggle-btn" onclick="toggleSection('statsContent', this)">[-]</button></div>
                        <div id="statsContent">
                            <div class="stats-menu"><span class="stats-pill active" onclick="setStatMode('1x2', this)">1x2</span><span class="stats-pill" onclick="setStatMode('GOLS', this)">Gols</span><span class="stats-pill" onclick="setStatMode('TEMPOS', this)">Tempos</span><span class="stats-pill" onclick="setStatMode('ODDS', this)">Odds</span></div>
                            <div class="stats-flex-container"><div style="flex:1; height:220px; position:relative;"><canvas id="chartStats"></canvas></div><div class="stats-legend-box"><div id="statBigNumber" class="stat-big-num">0%</div><div id="statLabel" class="stat-sub-label">Taxa de Vitória</div><div class="stat-list"><div class="stat-item"><span class="dot dot-g"></span> <span id="lbl1">Casa</span> <b id="val1">0</b></div><div class="stat-item"><span class="dot dot-y"></span> <span id="lbl2">Empate</span> <b id="val2">0</b></div><div class="stat-item"><span class="dot dot-r"></span> <span id="lbl3">Fora</span> <b id="val3">0</b></div></div></div></div>
                        </div>
                    </div>
                    
                    <div class="card"><div class="card-header"><h2>Histórico Filtrado</h2></div><div id="tableContainer" class="table-responsive"></div><button id="btnMore" class="btn-show-more" style="display:none;" onclick="renderTable(true)">VER MAIS</button></div>
                </div>
            </div>
        </div>

        <div id="view-strategy" class="view-section <?php echo $activeTab=='strategy'?'active':''; ?>">
            <div class="dashboard">
                <div class="content" style="grid-column: 1 / -1;">
                    <div class="card" id="cardStrategy">
                        <div class="card-header"><h2 style="color:var(--accent-blue);">🛠️ Criador de Estratégia</h2></div>
                        <div id="strategyContent">
                            <div class="strat-builder">
                                <div class="strat-row" style="margin-bottom:15px;"><div><label>Fonte de Dados</label><select id="stratSource" onchange="calculateStrategy()" style="border-color:var(--accent-blue);"><option value="HISTORY">📚 Validar no Histórico (Backtest)</option><option value="FUTURE">🔭 Buscar no Scanner (Próximos Jogos)</option></select></div><div><label>Filtrar Liga (Opcional)</label><select id="stratLeague" onchange="calculateStrategy()"><option value="">🌍 TODAS AS LIGAS</option><?php foreach($lgs as $lg) echo "<option value='$lg'>$lg</option>"; ?></select></div></div>
                                <div class="strat-row" style="margin-bottom:15px;"><div><label>Time Específico (Opcional)</label><select id="stratTeamSel" onchange="calculateStrategy()"><option value="">-- Todos os Times --</option><?php foreach($uniqueTeams as $tm => $v) echo "<option value='$tm'>$tm</option>"; ?></select></div></div>
                                <div class="strat-row"><div><label>Mercado Alvo</label><select id="stratTarget" onchange="calculateStrategy()"><option value="HOME">🏠 Casa Vence (Match Odds)</option><option value="DRAW">⚖️ Empate</option><option value="AWAY">✈️ Visitante Vence</option><option value="OVER25">⚽ Over 2.5 Gols</option><option value="BTTS">🤝 Ambas Marcam</option></select></div><div><label>Stake (R$)</label><input type="number" id="stratStake" value="100" oninput="calculateStrategy()"></div></div>
                                <div class="strat-row"><div><label>Odd Mín</label><input type="number" id="stratMinOdd" step="0.01" value="1.50" oninput="calculateStrategy()"></div><div><label>Odd Máx</label><input type="number" id="stratMaxOdd" step="0.01" value="3.00" oninput="calculateStrategy()"></div></div>
                            </div>
                            <div id="stratHistoryResults"><div class="kpi-row" style="margin-bottom:0;"><div class="kpi-box"><div class="kpi-lbl">Apostas</div><div class="kpi-val" id="stBets">0</div></div><div class="kpi-box" style="border-left-color:var(--accent-green);"><div class="kpi-lbl">Lucro</div><div class="kpi-val" id="stProfit">R$ 0</div></div><div class="kpi-box" style="border-left-color:var(--accent-purple);"><div class="kpi-lbl">ROI</div><div class="kpi-val" id="stRoi">0%</div></div><div class="kpi-box" style="border-left-color:var(--accent-orange);"><div class="kpi-lbl">Win Rate</div><div class="kpi-val" id="stWinRate">0%</div></div></div><div style="height:300px; margin-top:20px;"><canvas id="chartBankroll"></canvas></div></div>
                            <div id="stratFutureResults" style="display:none; margin-top:20px;"><h3 style="color:#888; font-size:12px; margin-bottom:10px;">JOGOS ENCONTRADOS NO SCANNER:</h3><div id="stratFutureTable" class="table-responsive"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="view-backtest" class="view-section <?php echo $activeTab=='backtest'?'active':''; ?>">
             <div class="dashboard">
                 <div class="sidebar">
                    <div class="card" style="border-color:var(--accent-orange);"><div class="card-header"><h2 style="color:var(--accent-orange);">Configuração</h2></div>
                        <div class="filter-group"><label>Fonte de Dados</label><select id="btSource" style="border-color:var(--accent-blue);"><option value="HISTORY">📚 Histórico (Resultados)</option><option value="FUTURE">🔮 Próximos Jogos (Scanner)</option></select></div>
                        <div class="filter-group" id="groupBtLeague"><label>Campeonato (Histórico)</label><select id="btLeague" style="border-color:var(--accent-orange);"><option value="">🌍 TODOS</option><?php foreach($lgs as $lg) echo "<option value='$lg'>$lg</option>"; ?></select></div>
                        <div class="filter-group" id="groupBtLeagueFuture" style="display:none;"><label>Campeonato (API / Futuro)</label><select id="btLeagueFuture" style="border-color:var(--accent-orange);"><option value="">🌍 TODAS AS LIGAS</option><?php foreach($ligasDisponiveisAPI as $k => $n) echo "<option value='$k'>$n</option>"; ?></select></div>
                        <div class="filter-group"><label>Mercado</label><select id="btMarket" style="border-color:var(--accent-orange);"><option value="HOME">🏠 Casa Vence</option><option value="DRAW">⚖️ Empate</option><option value="AWAY">✈️ Visitante Vence</option><option value="OVER15">⚽ Over 1.5 Gols</option><option value="OVER25">⚽ Over 2.5 Gols</option><option value="UNDER25">🛑 Under 2.5 Gols</option><option value="BTTS">🤝 Ambas Marcam</option><option value="HT_HOME">1️⃣ HT - Casa</option><option value="HT_DRAW">1️⃣ HT - Empate</option><option value="HT_AWAY">1️⃣ HT - Fora</option></select></div>
                        <div class="filter-group"><div class="date-row"><input type="number" id="btMinOdd" placeholder="Odd Mín" step="0.01" value="1.50"><input type="number" id="btMaxOdd" placeholder="Odd Máx" step="0.01" value="2.10"></div></div>
                        <button class="btn btn-blue" style="background:var(--accent-orange); border:none; margin-top:10px;" onclick="runBacktest()">INICIAR SIMULAÇÃO</button>
                    </div>
                 </div>
                 <div class="content">
                    <div class="card" id="simResultCardBT" style="display:none; border:1px solid var(--accent-orange);"><div class="card-header"><h2 style="color:var(--accent-orange);">🤖 Análise (IA)</h2><button class="toggle-btn" onclick="toggleSection('simResultBT', this)">[-]</button></div><div id="simResultBT" class="ai-content">...</div></div>
                    <div class="card"><div class="card-header"><h2>Relatório</h2><div id="btFilters" class="filter-bar" style="display:none;"><button class="filter-btn active" onclick="filterBacktest('all', this)">Todos</button><button class="filter-btn" onclick="filterBacktest('win', this)">Greens ✅</button><button class="filter-btn" onclick="filterBacktest('loss', this)">Reds ❌</button></div></div><div id="backtestResult" style="padding:20px; text-align:center; color:#666;">Clique em iniciar para processar...</div></div>
                 </div>
             </div>
        </div>

        <div id="view-opportunities" class="view-section <?php echo $activeTab=='opportunities'?'active':''; ?>">
            <div class="dashboard">
                <div class="content" style="grid-column: 1 / -1;">
                    <div class="card" id="autoScanCard" style="border-color:var(--accent-green);">
                        <div class="card-header" style="flex-direction: column; align-items: stretch; gap: 10px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 style="color:var(--accent-green); display:flex; align-items:center; gap:10px;">
            🔥 Oportunidades do Dia (IA Scanner)
        </h2>
        <div class="scan-filters" style="display:flex; gap:5px; flex-wrap:wrap;">
            <button class="badge-filter active" onclick="filterScan('ALL', this)">📋 Tudo</button>
            <button class="badge-filter" onclick="filterScan('SUPER_FAV', this)">💎 Super Fav</button>
            <button class="badge-filter" onclick="filterScan('HIGH_PROB', this)">🚀 IA +70%</button>
            <button class="badge-filter" onclick="filterScan('ZEBRA', this)">🦓 Zebras</button>
            <button class="badge-filter" onclick="filterScan('GOALS', this)">⚽ Gols</button>
        </div>
    </div>
    
    <div style="background:#111; padding:10px 15px; border-radius:6px; border:1px solid #333; display:flex; flex-direction:column; gap:5px;">
    <div style="display:flex; justify-content:space-between; font-size:10px; color:#aaa; font-weight:bold; margin-bottom:-10px; z-index:2;">
        <span>MÍN: <span id="valMin" style="color:#fff;">1.00</span></span>
        <span>MÁX: <span id="valMax" style="color:#fff;">5.00</span></span>
    </div>
    
    <div class="wrapper-slider">
            <div class="container-slider">
                <div class="slider-track" id="sliderTrack"></div>
                <input type="range" min="1.00" max="5.00" value="1.20" step="0.05" id="slider-1" class="dual-range" oninput="slideOne()">
                <input type="range" min="1.00" max="5.00" value="3.00" step="0.05" id="slider-2" class="dual-range" oninput="slideTwo()">
            </div>
        </div>
    </div>
    </div>
                        <div id="scanResults" class="sug-grid">
                            <div style="color:#666; padding:20px; text-align:center;">Carregando análise da IA...</div>
                        </div>
                    </div>
                    
                    <div class="card" id="simResultCard" style="display:none; border:1px solid var(--accent-blue); margin-top:20px;">
                        <div class="card-header">
                            <h2 style="color:var(--accent-blue);">🤖 Análise Detalhada</h2>
                            <button class="toggle-btn" onclick="toggleSection('simResult', this)">[-]</button>
                        </div>
                        <div id="simResult" class="ai-content">...</div>
                    </div>
                </div>
            </div>
        </div>

        <div id="view-future" class="view-section <?php echo $activeTab=='future'?'active':''; ?>">
            <div class="dashboard">
                <div class="sidebar">
                    <div class="card" style="border:1px solid var(--accent-green); background:rgba(16,185,129,0.05);">
                        <div class="card-header" style="border-bottom:1px solid var(--accent-green);"><h2 style="color:var(--accent-green);">🧠 Performance da IA</h2></div>
                        <div style="text-align:center; padding:10px;"><div style="font-size:10px; color:#aaa; margin-bottom:5px;">TAXA DE ACERTO (Est.)</div><div id="aiAccuracyDisplay" style="font-size:32px; font-weight:900; color:#fff;">--%</div><div style="font-size:9px; color:#666;">Baseado nos últimos 100 jogos</div></div>
                    </div>
                    <div class="card">
                        <div class="card-header"><h2>Atualizar API</h2></div>
                        <form method="POST" onsubmit="document.getElementById('overlay-loading').style.display='flex'"><input type="hidden" name="acao" value="atualizar_api"><div class="filter-group"><label>Selecionar Liga</label><select name="liga_api"><?php foreach($ligas_api as $nome => $key) echo "<option value='$key'>$nome</option>"; ?></select></div><button type="submit" class="btn btn-purple">BUSCAR ODDS FUTURAS</button></form>
                        <hr style="border-color:#333; margin:10px 0;">
                        <div class="filter-group"><label>🔍 Buscar Time</label><input type="text" id="futureSearchTeam" placeholder="Digite o nome..." onkeyup="filterFutureTable()"></div>
                        <div class="filter-group"><label>📅 Filtro de Data</label><select id="futureTimeFilter" onchange="filterFutureTable()"><option value="ALL">Todos os Jogos</option><option value="TODAY">Jogos de Hoje</option><option value="TOMORROW">Jogos de Amanhã</option></select></div>
                        <div class="filter-group"><label>🔢 Intervalo de Odds (Qualquer Time)</label><div class="date-row"><input type="number" id="futureMinOdd" placeholder="Mín" step="0.01" oninput="filterFutureTable()"><input type="number" id="futureMaxOdd" placeholder="Máx" step="0.01" oninput="filterFutureTable()"></div></div>
                        <hr style="border-color:#333; margin:10px 0;">
                        <div class="filter-group"><label>Filtrar Ligas</label><select id="selFutureLeague" onchange="filterFutureTable()"><option value="">🌍 TODAS AS LIGAS</option><?php foreach($ligasDisponiveisAPI as $k => $n) echo "<option value='$k'>$n</option>"; ?></select></div>
                        <div class="filter-group"><label>Filtrar por Perfil de Odd</label><select id="selFutureFilter" onchange="filterFutureTable()"><option value="">🎯 TODOS OS PERFIS</option><option value="FAV_HOME">🏠 Casa Favorito (Odd < 2.10)</option><option value="FAV_AWAY">✈️ Visitante Favorito (Odd < 2.10)</option><option value="BALANCED">⚖️ Equilibrado (Odds > 2.50)</option><option value="SUPER_FAV">🔥 Super Favorito (Odd < 1.40)</option></select></div>
                    </div>
                    <div class="card" style="border-color:var(--accent-blue);"><div class="card-header"><h2 style="color:var(--accent-blue);">🔮 Simulador Manual</h2></div><div class="filter-group"><label>Time Casa</label><select id="simHome"><option value="">Selecione...</option><?php foreach($uniqueTeams as $tm => $v) echo "<option value='$tm'>$tm</option>"; ?></select></div><div class="filter-group"><label>Time Visitante</label><select id="simAway"><option value="">Selecione...</option><?php foreach($uniqueTeams as $tm => $v) echo "<option value='$tm'>$tm</option>"; ?></select></div><button class="btn btn-blue" onclick="runSimulation()">🔮 ANALISAR COM IA</button></div>
                </div>
                
                <div class="content">
                    
                    <div class="card ia-analysis-box" id="box_ia_future" style="display:none; border:1px solid var(--accent-purple);">
                        <div class="card-header">
                            <h2 style="color:var(--accent-purple);">🤖 Análise Pré-Jogo (IA)</h2>
                            <button class="toggle-btn" onclick="this.closest('.card').style.display='none'">[X]</button>
                        </div>
                        <div class="ai-content-body ai-content">Processando...</div>
                    </div>

                    <?php if(!empty($upcomingGames)): ?>
                    <div class="card" style="border-color:var(--accent-purple);">
                        <div class="card-header">
                            <h2 style="color:var(--accent-purple); display:flex; align-items:center;">
                                🔥 Próximos Jogos (Lista Completa)
                            </h2>
                        </div>
                        <div class="table-responsive">
                            <table id="tableFuture">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>DATA</th>
                                        <th style="text-align:right">CASA</th>
                                        <th>X</th>
                                        <th style="text-align:left">FORA</th>
                                        <th>ODDS</th>
                                        <th>IA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($gamesOpen as $g): 
                                        $timestamp = strtotime($g['commence_time']); 
                                        $dt = date("d/m H:i", $timestamp); 
                                        $dateOnly = date("Y-m-d", $timestamp); 
                                        $lgName = isset($ligas_api_reverse[$g['sport_key']]) ? $ligas_api_reverse[$g['sport_key']] : $g['sport_key']; 
                                        $teamStr = strtolower($g['home_team'] . " " . $g['away_team']);
                                        $cleanH = preg_replace('/[^A-Za-z0-9]/', '', $g['home_team']); 
                                        $cleanA = preg_replace('/[^A-Za-z0-9]/', '', $g['away_team']);
                                    ?>
                                    <tr class="future-game-row" data-league="<?php echo $g['sport_key']; ?>" data-date="<?php echo $dateOnly; ?>" data-teams="<?php echo $teamStr; ?>" data-odd-h="<?php echo $g['odds_h']; ?>" data-odd-a="<?php echo $g['odds_a']; ?>">
                                        <td style="text-align:center;"><input type="checkbox" class="game-check" onchange="toggleRow(this)"></td>
                                        <td style="color:#fff; font-weight:bold; text-align:center;"><?php echo $dt; ?></td>
                                        <td style="text-align:right; font-weight:bold;">
                                            <?php echo $g['home_team']; ?> 
                                            <img src="logos/<?php echo $cleanH; ?>.png" onerror="this.src='https://cdn-icons-png.flaticon.com/512/16/16480.png'" class="team-logo">
                                        </td>
                                        <td style="text-align:center; color:#555;">vs</td>
                                        <td style="text-align:left; font-weight:bold;">
                                            <img src="logos/<?php echo $cleanA; ?>.png" onerror="this.src='https://cdn-icons-png.flaticon.com/512/16/16480.png'" class="team-logo"> 
                                            <?php echo $g['away_team']; ?>
                                        </td>
                                        <td style="text-align:center; font-size:11px;">
                                            <span style="color:var(--accent-green)"><?php echo $g['odds_h']; ?></span> | 
                                            <span style="color:var(--accent-yellow)"><?php echo $g['odds_d']; ?></span> | 
                                            <span style="color:var(--accent-red)"><?php echo $g['odds_a']; ?></span>
                                        </td>
                                        <td style="text-align:center;">
                                            <button onclick="loadIntoSimAndCheck(this, '<?php echo addslashes($g['home_team']); ?>','<?php echo addslashes($g['away_team']); ?>', <?php echo $g['odds_h']; ?>, <?php echo $g['odds_d']; ?>, <?php echo $g['odds_a']; ?>, '<?php echo $dt; ?>', '<?php echo addslashes($lgName); ?>')" class="ai-btn-small">ANALISAR 🤖</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if(empty($gamesOpen)): ?>
                                <div style="padding:15px;text-align:center;color:#666">Nenhum jogo aberto no momento.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                        <div class="card"><div style="padding:20px; text-align:center; color:#666;">Nenhum jogo futuro carregado. Atualize a API.</div></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

<div id="view-finished" class="view-section <?php echo $activeTab=='finished'?'active':''; ?>">
            <div class="dashboard">
                <div class="content" style="grid-column: 1 / -1;">
                    <div class="card" style="border-color:#444;">
                        <div class="card-header">
                            <h2 style="color:#888;">📓 Máquina do Tempo (Resultados Oficiais)</h2>
                        </div>

                        <div style="background:#151515; padding:15px; border-bottom:1px solid #333; display:flex; gap:10px; flex-wrap:wrap;">
                            <form method="GET" style="display:flex; gap:10px; align-items:center; flex:1; min-width:300px;">
                                <input type="hidden" name="tab" value="finished">
                                <label style="color:#ccc; font-weight:bold; font-size:12px; white-space:nowrap;">📅 DATA:</label>
                                <?php $dataBusca = isset($_GET['data_busca']) ? $_GET['data_busca'] : date('Y-m-d'); ?>
                                <input type="date" id="datePickerMain" name="data_busca" value="<?php echo $dataBusca; ?>" onclick="this.showPicker()" onchange="this.form.submit()" style="background:#000; border:1px solid #555; color:#fff; padding:8px; border-radius:6px; flex:1; color-scheme:dark; cursor:pointer; font-weight:bold; text-transform:uppercase;">
                            </form>

                            <button id="btnFilterSkip" onclick="toggleSkipped()" class="btn" style="width:auto; margin:0; padding:8px 15px; font-weight:bold; background:#333; border:1px solid #555; color:#aaa;">
    👁️ MOSTRAR SÓ PALPITES
</button>

                            <button onclick="analyzeAllFinishedGames()" class="btn" style="width:auto; margin:0; padding:8px 20px; font-weight:900; background:#8b5cf6; color:#fff; border:1px solid #7c3aed;">
                                🤖 CHECK-UP GERAL (TODOS)
                            </button>
                            

                            <form id="formApiSync" method="POST" style="display:flex; align-items:center;">
                                <input type="hidden" name="acao" value="atualizar_placares_api">
                                <input type="hidden" name="data_busca" id="hiddenDateTarget">
                                <button type="button" onclick="syncApiByDate()" class="btn btn-green" style="width:auto; margin:0; padding:8px 20px; font-weight:900; border:1px solid #059669;">
                                    🔄 ATUALIZAR PLACARES
                                </button>
                            </form>
                        </div>

                        <div id="dailySummaryBox" style="display:none; background:linear-gradient(180deg, #1a1a1a 0%, #111 100%); border-bottom:1px solid #333;">
                            <div style="padding:15px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; border-bottom:1px solid #222;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="font-size:24px;">🧠</div>
                                    <div>
                                        <div style="color:var(--accent-purple); font-weight:900; font-size:11px; text-transform:uppercase; letter-spacing:1px;">RELATÓRIO DE PERFORMANCE</div>
                                        <div style="color:#fff; font-size:13px;">Resumo da Inteligência Artificial</div>
                                    </div>
                                </div>
                                <div style="display:flex; gap:10px;">
                                    <div style="background:rgba(16,185,129,0.1); border:1px solid #10b981; padding:5px 12px; border-radius:6px; color:#10b981; font-weight:bold; font-size:12px;">✅ <span id="cntGreen" style="color:#fff; margin-left:5px;">0</span></div>
                                    <div style="background:rgba(239,68,68,0.1); border:1px solid #ef4444; padding:5px 12px; border-radius:6px; color:#ef4444; font-weight:bold; font-size:12px;">❌ <span id="cntRed" style="color:#fff; margin-left:5px;">0</span></div>
                                    <div style="background:rgba(255,255,255,0.05); border:1px solid #444; padding:5px 12px; border-radius:6px; color:#888; font-weight:bold; font-size:12px;">⚪ <span id="cntVoid" style="color:#fff; margin-left:5px;">0</span></div>
                                </div>
                            </div>
                            <div id="dailySummaryText" style="padding:20px; font-size:13px; line-height:1.6; color:#ccc;">Aguardando análise...</div>
                        </div>
                        
                        <div class="card ia-analysis-box" style="display:none; border:1px solid var(--accent-purple); margin: 15px; box-shadow: 0 0 20px rgba(139,92,246,0.2);">
                            <div class="card-header">
                                <h2 style="color:var(--accent-purple);">📜 Revisão de Análise (Pós-Jogo)</h2>
                                <button class="toggle-btn" onclick="this.closest('.card').style.display='none'">[X]</button>
                            </div>
                            <div class="ai-content-body ai-content" style="min-height:100px;">Carregando revisão...</div>
                        </div>

                        <?php 
                            $stmtHist = $pdo->prepare("SELECT * FROM scanner_historico WHERE DATE(data_jogo) = ? ORDER BY data_jogo ASC");
                            $stmtHist->execute([$dataBusca]);
                            $jogosUnificados = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <?php if(empty($jogosUnificados)): ?>
                            <div style="padding:50px; text-align:center; color:#666;">
                                <div style="font-size:40px; margin-bottom:10px;">📂</div>
                                <h3>Nenhum registro encontrado para <?php echo date('d/m/Y', strtotime($dataBusca)); ?>.</h3>
                                <p style="font-size:11px; margin-top:5px;">O Scanner só salva jogos se você clicou em "BUSCAR ODDS" no dia.</p>
                            </div>
                        <?php else: ?>
                        
                        <div class="table-responsive">
                            <table style="width:100%;">
                                <thead><tr><th>HORA</th><th style="text-align:right">CASA</th><th>PLACAR</th><th style="text-align:left">FORA</th><th>ODDS</th><th>RESULTADO</th><th>VEREDITO IA</th><th>AÇÃO</th></tr></thead>
                                <tbody>
                                    <?php foreach($jogosUnificados as $g): 
                                        $timestamp = strtotime($g['data_jogo']); 
                                        if ($timestamp > time() && empty($g['placar_real'])) continue; // Esconde jogos futuros se não tiver placar
                                        $hora = date("H:i", $timestamp); $dateOnly = date("Y-m-d", $timestamp);
                                        $lgName = isset($ligas_api_reverse[$g['liga']]) ? $ligas_api_reverse[$g['liga']] : $g['liga'];
                                        $cleanH = preg_replace('/[^A-Za-z0-9]/', '', $g['time_casa']); $cleanA = preg_replace('/[^A-Za-z0-9]/', '', $g['time_fora']);
                                        $placar = "-"; $statusPlacar = "..."; $corStatus = "#666"; $borderStatus = "1px solid #444"; $gh_js = 'null'; $ga_js = 'null';

                                        if (!empty($g['placar_real'])) {
                                            $parts = explode('-', $g['placar_real']);
                                            if(count($parts) == 2) { $gh = (int)trim($parts[0]); $ga = (int)trim($parts[1]); $gh_js = $gh; $ga_js = $ga; $placar = "<span style='color:#fff; font-weight:900; font-size:16px;'>{$gh} - {$ga}</span><br><span style='font-size:8px; color:#10b981;'>API</span>"; }
                                        } else {
                                            foreach($dbGames as $hist) { if($hist['d'] == $dataBusca) { if(strpos(strtolower($hist['h']), strtolower(substr($g['time_casa'],0,5))) !== false || strpos(strtolower($hist['a']), strtolower(substr($g['time_fora'],0,5))) !== false) { $gh = $hist['gh']; $ga = $hist['ga']; $gh_js = $gh; $ga_js = $ga; $placar = "<span style='color:#aaa; font-weight:bold; font-size:14px;'>{$gh} - {$ga}</span><br><span style='font-size:8px; color:#666;'>CSV</span>"; break; } } }
                                        }

                                        if ($gh_js !== 'null') { if($gh_js > $ga_js) { $statusPlacar = "CASA"; if($g['odd_casa'] < 2.10) { $corStatus = "#10b981"; $borderStatus = "1px solid #10b981"; } else { $corStatus = "#fff"; $borderStatus = "1px solid #fff"; } } elseif($ga_js > $gh_js) { $statusPlacar = "VISITANTE"; if($g['odd_fora'] < 2.10) { $corStatus = "#10b981"; $borderStatus = "1px solid #10b981"; } else { $corStatus = "#fff"; $borderStatus = "1px solid #fff"; } } else { $statusPlacar = "EMPATE"; $corStatus = "#f59e0b"; $borderStatus = "1px solid #f59e0b"; } }

                                        $rowClass = "";
                                        if (!empty($g['ia_status'])) { if ($g['ia_status'] == 'WIN') $rowClass = "row-green"; elseif ($g['ia_status'] == 'LOSS') $rowClass = "row-red"; }
                                        if (empty($rowClass) && !empty($g['ia_palpite']) && $g['ia_palpite'] == 'SKIP') { $rowClass = "row-yellow"; }
                                    ?>
                                    <tr style="background:#111; border-bottom:1px solid #222;" class="row-game-finished <?php echo $rowClass; ?>" data-h="<?php echo $g['time_casa']; ?>" data-a="<?php echo $g['time_fora']; ?>" data-oh="<?php echo $g['odd_casa']; ?>" data-od="<?php echo $g['odd_empate']; ?>" data-oa="<?php echo $g['odd_fora']; ?>" data-dt="<?php echo $dateOnly; ?>" data-lg="<?php echo $g['liga']; ?>" data-gh="<?php echo $gh_js; ?>" data-ga="<?php echo $ga_js; ?>">
                                        <td style="color:#fff; text-align:center; font-size:11px;"><?php echo $hora; ?><br><span style="font-size:9px; color:#fff; opacity:0.7;"><?php echo substr($lgName, 0, 15); ?></span></td>
                                        <td style="text-align:right; color:#aaa;"><?php echo $g['time_casa']; ?> <img src="logos/<?php echo $cleanH; ?>.png" onerror="this.src='https://cdn-icons-png.flaticon.com/512/16/16480.png'" class="team-logo" style="filter:grayscale(100%);"></td>
                                        <td style="text-align:center; background:#1a1a1a; border-left:1px solid #333; border-right:1px solid #333; min-width:80px;"><?php echo $placar; ?></td>
                                        <td style="text-align:left; color:#aaa;"><img src="logos/<?php echo $cleanA; ?>.png" onerror="this.src='https://cdn-icons-png.flaticon.com/512/16/16480.png'" class="team-logo" style="filter:grayscale(100%);"> <?php echo $g['time_fora']; ?></td>
                                        <td style="text-align:center; font-size:11px; color:#fff; opacity:0.8;"><?php echo $g['odd_casa']; ?> | <?php echo $g['odd_empate']; ?> | <?php echo $g['odd_fora']; ?></td>
                                        <td style="text-align:center;"><span style="font-size:10px; font-weight:bold; color:<?php echo $corStatus; ?>; border:<?php echo $borderStatus; ?>; padding:2px 6px; border-radius:4px;"><?php echo $statusPlacar; ?></span></td>
                                        <td style="text-align:center;" class="cell-ai-verdict">
                                            <?php if (!empty($g['ia_status']) && !empty($g['ia_palpite'])) { $st = $g['ia_status']; $pp = $g['ia_palpite']; $lbl = $pp; if($pp=='HOME') $lbl='CASA'; elseif($pp=='AWAY') $lbl='ZEBRA'; elseif($pp=='OVER') $lbl='OVER'; elseif($pp=='SKIP') $lbl='ABSTEVE'; if ($st == 'WIN') echo "<span style='background:rgba(16,185,129,0.2); color:#10b981; border:1px solid #10b981; padding:2px 6px; border-radius:4px; font-weight:bold; font-size:10px;'>✅ $lbl</span>"; elseif ($st == 'LOSS') echo "<span style='background:rgba(239,68,68,0.2); color:#ef4444; border:1px solid #ef4444; padding:2px 6px; border-radius:4px; font-weight:bold; font-size:10px;'>❌ $lbl</span>"; else echo "<span style='color:#ccc; font-size:10px;'>ABSTEVE</span>"; } else { echo '<span style="font-size:10px; color:#ccc;">...</span>'; } ?>
                                        </td>
                                        <td style="text-align:center;"><button onclick="loadIntoSimAndCheck(this, '<?php echo addslashes($g['time_casa']); ?>','<?php echo addslashes($g['time_fora']); ?>', <?php echo $g['odd_casa']; ?>, <?php echo $g['odd_empate']; ?>, <?php echo $g['odd_fora']; ?>, '<?php echo $hora; ?>', '<?php echo addslashes($lgName); ?>', <?php echo $gh_js; ?>, <?php echo $ga_js; ?>)" class="ai-btn-small" style="background:#333;">🤖</button></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="padding:15px; text-align:center; font-size:10px; color:#666; border-top:1px solid #333;">* Dica: Clique no 🤖 para rever a análise detalhada e conferir a performance.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
<footer>
    <?php $st1 = (strpos($msg, 'notif-error') !== false && (strpos($msg, 'The Odds API') !== false)) ? 'led-red' : 'led-green'; $st2 = (strpos($msg, 'notif-error') !== false && strpos($msg, 'API Nova') !== false) ? 'led-red' : 'led-green'; ?>
    <div class="status-bar" style="border:none; margin-top:0; padding-top:0; margin-bottom:10px; padding-bottom:0;"><div class="status-item" title="The Odds API"><div class="led <?php echo $st1; ?>"></div> API 1 (ODDS)</div><div class="status-item" title="API-Football"><div class="led <?php echo $st2; ?>"></div> API 2 (FOOTBALL)</div></div><div style="margin-bottom:5px;">CRIADO BY <b>LE MIASTKUOSKY</b></div><div style="font-size:10px; opacity:0.6;">Base: <?php echo $qtdArquivosBaixados; ?> ligas • Atualizado: <?php echo $dataUltimaAtualizacao; ?></div>
</footer>
<script>
    // --- DADOS PHP PARA JAVASCRIPT ---
    const allGames = <?php echo json_encode($dbGames); ?>;
    const upcomingGames = <?php echo json_encode($upcomingGames); ?>;
    const leagueNames = <?php echo json_encode($ligas_api_reverse); ?>;
    
    // --- VARIÁVEIS GLOBAIS ---
    let lastRowClicked = null; 
    let filteredData = [], limit = 20;
    let chartStats = null, chartBank = null; 
    let currentStatMode = '1x2', currentScanFilter = 'all', currentBTFilter = 'all';

    // --- INICIALIZAÇÃO ---
    window.onload = function() { 
        applyFilters(); 
        switchTab('<?php echo $activeTab; ?>'); 
        
        // Auto-selecionar liga se vier do PHP
        const autoLeague = '<?php echo $autoSelectLeague; ?>';
        if(autoLeague && document.querySelector('#selFutureLeague option[value="'+autoLeague+'"]')) { 
            document.getElementById('selFutureLeague').value = autoLeague; 
            filterFutureTable(); 
        }
        
        scanOpportunities(); 
        setTimeout(initAIAccuracyCheck, 2000);
        
        // Notificações
        const notif = document.querySelector('.notification'); 
        if(notif) { setTimeout(() => { notif.style.transition = "opacity 1s"; notif.style.opacity = "0"; setTimeout(() => notif.remove(), 1000); }, 10000); }
        
        // Toggle de Fonte no Backtest
        $('#btSource').change(function() { 
            if(this.value === 'FUTURE') { $('#groupBtLeague').hide(); $('#groupBtLeagueFuture').show(); } 
            else { $('#groupBtLeagueFuture').hide(); $('#groupBtLeague').show(); } 
        });
    };

    // --- HELPERS ---
    function getLogo(teamName) { let clean = teamName.replace(/[^A-Za-z0-9]/g, ''); return `logos/${clean}.png`; }
    function factorial(n) { if (n === 0 || n === 1) return 1; let result = 1; for (let i = 2; i <= n; i++) result = result * i; return result; }
    function poisson(k, lambda) { return (Math.pow(Math.E, -lambda) * Math.pow(lambda, k)) / factorial(k); }
    function formatDateSimple(isoStr) { if(!isoStr) return "-"; let d = new Date(isoStr); return d.getDate().toString().padStart(2,'0') + "/" + (d.getMonth()+1).toString().padStart(2,'0') + " " + d.getHours().toString().padStart(2,'0') + ":" + d.getMinutes().toString().padStart(2,'0'); }

    // --- 1. LÓGICA DE "VIAGEM NO TEMPO" ---
    function getPastGames(dateLimitStr) {
        if (!dateLimitStr) return allGames;
        let limit = "";
        if (dateLimitStr.includes('/')) { 
            let parts = dateLimitStr.split(' ')[0].split('/');
            let year = new Date().getFullYear(); 
            limit = `${year}-${parts[1]}-${parts[0]}`; 
        } else if (dateLimitStr.includes(':') && !dateLimitStr.includes('-')) {
             return allGames; 
        } else {
            limit = dateLimitStr.split(' ')[0]; 
        }
        return allGames.filter(g => g.d < limit);
    }

    function calculatePoissonStats(homeTeam, awayTeam, leagueName, dateLimit) {
        let validGames = getPastGames(dateLimit);
        let leagueGames = validGames.filter(g => g.c === leagueName);
        if(leagueGames.length < 50) leagueGames = validGames; 
        if(leagueGames.length === 0) return null; 

        let totalHGoals = 0, totalAGoals = 0;
        leagueGames.forEach(g => { totalHGoals += g.gh; totalAGoals += g.ga; });
        let avgLeagueH = totalHGoals / leagueGames.length;
        let avgLeagueA = totalAGoals / leagueGames.length;

        let homeGames = leagueGames.filter(g => g.h === homeTeam);
        let hScored = 0, hConceded = 0;
        if(homeGames.length > 0) {
            homeGames.forEach(g => { hScored += g.gh; hConceded += g.ga; });
            var avgHomeAtt = (hScored / homeGames.length) / avgLeagueH;
            var avgHomeDef = (hConceded / homeGames.length) / avgLeagueA;
        } else { var avgHomeAtt = 1; var avgHomeDef = 1; }

        let awayGames = leagueGames.filter(g => g.a === awayTeam);
        let aScored = 0, aConceded = 0;
        if(awayGames.length > 0) {
            awayGames.forEach(g => { aScored += g.ga; aConceded += g.gh; }); 
            var avgAwayAtt = (aScored / awayGames.length) / avgLeagueA;
            var avgAwayDef = (aConceded / awayGames.length) / avgLeagueH;
        } else { var avgAwayAtt = 1; var avgAwayDef = 1; }

        return { lambdaH: avgHomeAtt * avgAwayDef * avgLeagueH, lambdaA: avgAwayAtt * avgHomeDef * avgLeagueA };
    }

    function predictScore(home, away, leagueName, dateLimit) {
        let stats = calculatePoissonStats(home, away, leagueName, dateLimit);
        if(!stats) return null;
        let probHomeWin=0, probDraw=0, probAwayWin=0, probOver25=0, probBTTS=0;
        for(let h=0; h<=5; h++) {
            for(let a=0; a<=5; a++) {
                let pH = poisson(h, stats.lambdaH); let pA = poisson(a, stats.lambdaA); let prob = pH * pA; 
                if(h > a) probHomeWin += prob; else if(a > h) probAwayWin += prob; else probDraw += prob;
                if((h + a) > 2.5) probOver25 += prob; if(h > 0 && a > 0) probBTTS += prob;
            }
        }
        return { 
            probHome: probHomeWin * 100, probDraw: probDraw * 100, probAway: probAwayWin * 100, 
            probOver: probOver25 * 100, probBTTS: probBTTS * 100, expectedGoalsH: stats.lambdaH, expectedGoalsA: stats.lambdaA 
        };
    }

    function analyzeHistoricalContext(league, oddH, oddD, oddA, dateLimit) {
        let validGames = getPastGames(dateLimit); 
        let leagueGames = validGames.filter(g => g.c === league);
        if(leagueGames.length < 50) leagueGames = validGames;
        let margin = 0.15; 
        let similarGames = leagueGames.filter(g => (g.oh >= oddH * (1 - margin) && g.oh <= oddH * (1 + margin)));
        if (similarGames.length < 5) return null;
        let wins = 0, draws = 0, loss = 0, over25 = 0, btts = 0;
        similarGames.forEach(g => {
            if(g.gh > g.ga) wins++; else if(g.gh == g.ga) draws++; else loss++;
            if((g.gh + g.ga) > 2.5) over25++; if(g.gh > 0 && g.ga > 0) btts++;
        });
        let t = similarGames.length;
        return { totalSample: t, winRate: (wins/t)*100, lossRate: (loss/t)*100, overRate: (over25/t)*100, bttsRate: (btts/t)*100 };
    }

    // --- 2. FUNÇÃO PRINCIPAL DA IA (RESULTADO + VOLTAR) ---
    // --- 2. FUNÇÃO PRINCIPAL DA IA (COM PERSONALIDADE HUMANA) ---
    function runSimulation(passedHome, passedAway, oh, od, oa, dateStr, leagueName, realGH = null, realGA = null) {
        let home = passedHome || document.getElementById('simHome').value;
        let away = passedAway || document.getElementById('simAway').value;
        const resCard = document.getElementById('simResultCard'); 
        const resDiv = document.getElementById('simResult');

        if(!home || !away || home === away) { alert("Selecione dois times válidos."); return; }
        
        resCard.style.display = 'block'; 
        resDiv.innerHTML = "<div class='spinner' style='width:20px;height:20px;margin:0 auto;'></div> Consultando Oráculo (Modo Histórico)...";

        setTimeout(() => {
            let targetLeague = leagueName; 
            if(!targetLeague || targetLeague === 'undefined') { let findLg = allGames.find(g => g.h === home || g.a === home); if(findLg) targetLeague = findLg.c; }
            
            // Segurança
            let prediction = null;
            if(typeof predictScore === 'function') prediction = predictScore(home, away, targetLeague, dateStr);
            if(!prediction) { resDiv.innerHTML = "⚠️ Dados insuficientes para traçar o perfil do jogo."; return; }
            
            // Segurança Forma
            const getFormSafe = (tm, dt) => {
                if(typeof getTeamForm === 'function') return getTeamForm(tm, dt);
                let limitDate = dt.includes('/') ? dt.split('/').reverse().join('-') : dt.split(' ')[0];
                let past = allGames.filter(g => g.d < limitDate && (g.h === tm || g.a === tm));
                past.sort((a, b) => new Date(b.d) - new Date(a.d));
                let last5 = past.slice(0, 5);
                let pts = 0;
                last5.forEach(g => { if((g.h===tm&&g.gh>g.ga)||(g.a===tm&&g.ga>g.gh)) pts+=3; else if(g.gh===g.ga) pts+=1; });
                return { pts: pts, count: last5.length };
            };

            let formH = getFormSafe(home, dateStr);
            let formA = getFormSafe(away, dateStr);

            let context = null; 
            if(oh > 0 && typeof analyzeHistoricalContext === 'function') context = analyzeHistoricalContext(targetLeague, oh, od, oa, dateStr);
            
            let fairOddH = 100 / prediction.probHome; 
            let fairOddA = 100 / prediction.probAway;
            
            let scoreH = 0, scoreA = 0, scoreOver = 0;

            // 1. PONTUAÇÃO MAIS DIFÍCIL:
            // Só ganha ponto se a probabilidade base já for > 50% (antes era 40%)
            if (prediction.probHome > 50) scoreH++; 
            if (oh > 0 && oh > fairOddH) scoreH++; // Tem valor real na Odd?
            if (context && context.winRate > 50) scoreH++; // O histórico recente confirma?

            if (prediction.probAway > 50) scoreA++; 
            if (oa > 0 && oa > (100/prediction.probAway)) scoreA++; 
            if (context && context.lossRate > 50) scoreA++;

            if (prediction.probOver > 55) scoreOver++; 
            if (context && context.overRate > 55) scoreOver++;

            let recType = "SKIP", recText = "AGUARDAR LIVE", recClass = "rec-skip";

            // 2. REGRAS DE OURO (DIAMANTE):
            // Exige Pontuação Alta (2 ou 3) E Probabilidade Alta (> 55%)
            if (scoreH >= 2 && prediction.probHome > 55) { 
                recType = "HOME"; recClass = "rec-high"; recText = `💎 CASA VENCE (FORTE)`; 
            } 
            else if (scoreA >= 2 && prediction.probAway > 55) { 
                recType = "AWAY"; recClass = "rec-high"; recText = `💎 VISITANTE VENCE (FORTE)`; 
            } 
            else if (scoreOver >= 2 && prediction.probOver > 60) { 
                recType = "OVER"; recClass = "rec-med"; recText = `⚽ OVER 2.5 GOLS`; 
            } 
            
            // 3. REGRAS DE PRATA (TENDÊNCIAS):
            // Se não tiver pontuação de valor, exige probabilidade MUITO alta (> 65% ou 70%)
            else if (prediction.probHome > 65) { recType = "HOME"; recClass = "rec-med"; recText = `📈 TENDÊNCIA CASA`; }
            else if (prediction.probAway > 65) { recType = "AWAY"; recClass = "rec-med"; recText = `📈 TENDÊNCIA VISITANTE`; }
            else if (prediction.probOver > 70) { recType = "OVER"; recClass = "rec-med"; recText = `⚠️ PROVÁVEL OVER`; }
            
            // 4. LIXEIRA (ABSTER-SE):
            // Se não bateu nos critérios acima, a IA pula fora.
            else {
                recType = "SKIP"; recText = "ABSTER-SE (RISCO)"; recClass = "rec-skip";
            }
            // --- GERAÇÃO DE OPINIÃO "HUMANIZADA" ---
            let opinionList = [];
            const pickRandom = (arr) => arr[Math.floor(Math.random() * arr.length)];

            // 1. ANÁLISE DO MANDANTE
            if(prediction.probHome >= 60) {
                opinionList.push(pickRandom([
                    `🏟️ <b>${home}</b> é muito favorito aqui. O time joga em casa e tem números dominantes.`,
                    `📊 Probabilidade esmagadora para o mandante. Se não ganhar, é zebra histórica.`,
                    `💪 O mando de campo vai pesar. A torcida empurra e o time corresponde.`
                ]));
            } else if (formH.pts >= 12) {
                opinionList.push(`🔥 O <b>${home}</b> tá voando baixo! Vem amassando os adversários recentemente.`);
            } else if (formH.pts <= 3) {
                opinionList.push(`⚠️ Cuidado com o mandante. O time tá numa fase horrível, a bola não entra.`);
            }

            // 2. ANÁLISE DO VISITANTE
            if(prediction.probAway >= 50) {
                opinionList.push(pickRandom([
                    `✈️ O visitante tem um time superior tecnicamente e deve controlar o jogo.`,
                    `⚔️ Mesmo fora de casa, o <b>${away}</b> impõe respeito e tem tudo pra levar.`,
                    `👀 Olho nesse visitante! As estatísticas mostram que eles jogam melhor fora do que em casa.`
                ]));
            } else if (formA.pts >= 12) {
                opinionList.push(`🚀 O <b>${away}</b> vem embalado. Confiança lá no teto pra esse jogo.`);
            }

            // 3. ANÁLISE DE GOLS
            if(prediction.probOver > 65) {
                opinionList.push(pickRandom([
                    `⚽ Expectativa de chuva de gols! As duas defesas são umas "mães".`,
                    `🔥 Jogo aberto! Os dois times marcam e sofrem muitos gols. Cenário perfeito pra Over.`,
                    `🥅 A rede vai balançar. O estilo de jogo das duas equipes favorece placar elástico.`
                ]));
            } else if(prediction.probOver < 35) {
                opinionList.push(`🛡️ Jogo com cara de truncado. Muita marcação e pouco espaço. Under é o caminho.`);
            } else if(prediction.probBTTS > 60) {
                opinionList.push(`🤝 A chance de Ambas Marcam é altíssima. Dificilmente alguém sai com Clean Sheet hoje.`);
            }

            // 4. ANÁLISE DE VALOR (ODDS)
            if(oh > 0 && oh > (fairOddH * 1.15)) opinionList.push(`💰 <b>ATENÇÃO:</b> As casas erraram na Odd do mandante. Tem muito valor aqui!`);
            if(oa > 0 && oa > (fairOddA * 1.15)) opinionList.push(`💰 <b>OPORTUNIDADE:</b> O mercado subestimou o visitante. Essa Odd tá pagando mais do que devia.`);

            // Se não tiver nada muito forte a dizer:
            if(opinionList.length === 0) {
                opinionList.push("⚖️ Jogo de xadrez. Muito equilibrado e decidido nos detalhes. Cautela na entrada.");
            }

            let opinionHTML = opinionList.map(t => `<div style="margin-bottom:4px; padding-left:8px; border-left:3px solid var(--accent-blue); line-height:1.4;">${t}</div>`).join('');
            
            let reasoning = `<div style="margin-bottom:12px; font-size:11px; color:#aaa; display:flex; gap:10px;"><span>Prob. Casa: <b style="color:#fff">${prediction.probHome.toFixed(0)}%</b></span><span>Prob. Fora: <b style="color:#fff">${prediction.probAway.toFixed(0)}%</b></span></div>`;
            reasoning += `<div style="font-size:12px; color:#e5e5e5; background:#1a1a1a; padding:10px; border-radius:6px; border:1px solid #333;">${opinionHTML}</div>`;

            // --- RESULTADO REAL COM COMENTÁRIO PÓS-JOGO (HUMANIZADO) ---
            let resultHTML = "";
            let statusColor = "";

            if (realGH !== null && realGA !== null && realGH !== 'null' && realGH !== undefined) {
                let isGreen = false;
                let rH = parseInt(realGH);
                let rA = parseInt(realGA);

                if (recType === "HOME" && rH > rA) isGreen = true;
                else if (recType === "AWAY" && rA > rH) isGreen = true;
                else if (recType === "OVER" && (rH + rA) > 2.5) isGreen = true;
                else if (recType === "DRAW" && rH === rA) isGreen = true;
                else if (recType === "SKIP") isGreen = null; 

                // --- FRASES DE EFEITO PÓS JOGO ---
                const winPhrases = [
                    "Eu avisei! Leitura de jogo impecável.",
                    "Não tinha como dar outra coisa. O padrão se confirmou.",
                    "Dinheiro no bolso com tranquilidade. A estatística não mente!",
                    "Foi como tirar doce de criança. Leitura perfeita.",
                    "Quem seguiu a call forrou! Cenário desenhado pela IA."
                ];
                const lossPhrases = [
                    "Inacreditável... o time simplesmente não entrou em campo.",
                    "A bola puniu hoje. Estatística indicava uma coisa, o campo mostrou outra.",
                    "Zebraça histórica! O futebol tem dessas coisas...",
                    "Dia atípico. A lógica foi pro espaço nesse jogo.",
                    "O time dominou mas a bola não entrou. Faz parte do jogo."
                ];
                const voidPhrases = [
                    "Ainda bem que ficamos de fora. Jogo feio.",
                    "IA salvou a banca! O risco de empate era real.",
                    "Leitura correta de risco. Melhor não operar do que perder."
                ];

                let aiComment = "";

                if (recType === "SKIP") {
                    statusColor = "yellow";
                    aiComment = pickRandom(voidPhrases);
                    resultHTML = `<div style="margin-top:15px; padding:10px; background:#222; border:1px solid #444; border-radius:6px; text-align:center;"><div style="font-size:10px; color:#aaa;">RESULTADO REAL</div><div style="font-size:24px; color:#fff; font-weight:900;">${rH} - ${rA}</div><div style="font-size:12px; color:#f59e0b;">IA se absteve (VOID).</div><div style="margin-top:8px; font-size:11px; font-style:italic; color:#999;">"${aiComment}"</div></div>`;
                } else if (isGreen) {
                    statusColor = "green";
                    aiComment = pickRandom(winPhrases);
                    resultHTML = `<div style="margin-top:15px; padding:10px; background:rgba(16, 185, 129, 0.15); border:1px solid #10b981; border-radius:6px; text-align:center;"><div style="color:#10b981; font-weight:900; font-size:10px;">BINGO! IA ACERTOU</div><div style="font-size:24px; color:#fff; font-weight:900;">${rH} - ${rA}</div><div style="color:#10b981; font-size:14px; font-weight:bold;">${recText} ✅</div><div style="margin-top:8px; font-size:11px; font-style:italic; color:#a7f3d0; border-top:1px solid rgba(16,185,129,0.3); padding-top:6px;">"${aiComment}"</div></div>`;
                } else {
                    statusColor = "red";
                    aiComment = pickRandom(lossPhrases);
                    resultHTML = `<div style="margin-top:15px; padding:10px; background:rgba(239, 68, 68, 0.15); border:1px solid #ef4444; border-radius:6px; text-align:center;"><div style="color:#ef4444; font-weight:900; font-size:10px;">RED (IA ERROU)</div><div style="font-size:24px; color:#fff; font-weight:900;">${rH} - ${rA}</div><div style="color:#ef4444; font-size:14px; font-weight:bold;">${recText} ❌</div><div style="margin-top:8px; font-size:11px; font-style:italic; color:#fca5a5; border-top:1px solid rgba(239,68,68,0.3); padding-top:6px;">"${aiComment}"</div></div>`;
                }

                if (lastRowClicked) {
                    lastRowClicked.classList.remove('row-green', 'row-red', 'row-yellow');
                    if (statusColor === "green") lastRowClicked.classList.add('row-green');
                    else if (statusColor === "red") lastRowClicked.classList.add('row-red');
                    else lastRowClicked.classList.add('row-yellow');
                }
            } else {
                resultHTML = `<div style="margin-top:10px; font-size:10px; color:#666; text-align:center;">Aguardando resultado final...</div>`;
            }

            let veredictoHTML = `
                <div class="verdict-box">
                    <div class="verdict-header"><div class="verdict-icon">🧠</div><div class="verdict-title">Opinião do Especialista (IA)</div></div>
                    <div class="verdict-content">
                        ${reasoning}
                        <div style="text-align:center; margin-top:15px;">
                            <span class="recommendation-tag ${recClass}">${recText}</span>
                        </div>
                        ${resultHTML}
                    </div>
                </div>
                <button class="btn-back-ai" onclick="closeAI()">⬅ Voltar para a lista</button>
            `;

            let header = `<div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:10px;"><div style="font-size:14px; font-weight:900; color:#fff;">${home} <span style="color:#666; font-weight:400;">vs</span> ${away}</div><div style="font-size:10px; color:#888;">${targetLeague || ''}</div></div>`;
            let statsRow = `<div style="margin-bottom:10px; display:flex; gap:10px;"><div style="flex:1; background:#151515; padding:8px; border-radius:6px; font-size:11px;">⚽ xG Estimado: <b>${prediction.expectedGoalsH.toFixed(2)}</b> x <b>${prediction.expectedGoalsA.toFixed(2)}</b></div><div style="flex:1; background:#151515; padding:8px; border-radius:6px; font-size:11px;">🤝 BTTS (Ambas): <b>${prediction.probBTTS.toFixed(1)}%</b></div></div>`;
            let probsRow = `<div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px; text-align:center; margin-bottom:15px;"><div style="background:#111; padding:8px; border-radius:6px; border:1px solid #333;"><div style="font-size:10px; color:#888;">CASA</div><div style="font-size:18px; font-weight:bold; color:${prediction.probHome > 40 ? '#10b981' : '#fff'}">${prediction.probHome.toFixed(1)}%</div></div><div style="background:#111; padding:8px; border-radius:6px; border:1px solid #333;"><div style="font-size:10px; color:#888;">EMPATE</div><div style="font-size:18px; font-weight:bold; color:#f59e0b">${prediction.probDraw.toFixed(1)}%</div></div><div style="background:#111; padding:8px; border-radius:6px; border:1px solid #333;"><div style="font-size:10px; color:#888;">VISITANTE</div><div style="font-size:18px; font-weight:bold; color:${prediction.probAway > 40 ? '#ef4444' : '#fff'}">${prediction.probAway.toFixed(1)}%</div></div></div>`;

            resDiv.innerHTML = header + probsRow + statsRow + veredictoHTML;
        }, 300);
    }

    // --- 3. FUNÇÕES DE NAVEGAÇÃO ---
    function loadIntoSimAndCheck(btn, home, away, oh, od, oa, dateStr, leagueName, realGH = null, realGA = null) {
        lastRowClicked = btn.closest('tr') || btn.closest('.sug-card');
        
        // 1. Detecta em qual aba o botão foi clicado
        const parentSection = btn.closest('.view-section');
        if (parentSection) {
            // Se o botão está numa aba, garante que ela continue ativa
            const tabId = parentSection.id;
            let tabName = 'opportunities'; 
            if(tabId === 'view-future') tabName = 'future';
            if(tabId === 'view-finished') tabName = 'finished';
            
            switchTab(tabName);
        } else {
            // Se por acaso não achar, vai para oportunidades (padrão)
            switchTab('opportunities');
        }
        
        // 2. Preenche o simulador manual (opcional, só visual)
        let sH = document.getElementById('simHome');
        let sA = document.getElementById('simAway');
        if(sH && sA) {
            $(sH).val(null).trigger('change'); $(sA).val(null).trigger('change');
            const setTxt = (sel, txt) => { 
                for(let i=0; i<sel.options.length; i++) 
                    if(sel.options[i].text === txt) { sel.selectedIndex = i; return; } 
                for(let i=0; i<sel.options.length; i++) 
                    if(sel.options[i].text.includes(txt.substring(0,4))) { sel.selectedIndex = i; return; } 
            };
            setTxt(sH, home); setTxt(sA, away);
            if(typeof jQuery !== 'undefined' && jQuery(sH).data('select2')) jQuery(sH).trigger('change'); 
            if(typeof jQuery !== 'undefined' && jQuery(sA).data('select2')) jQuery(sA).trigger('change');
        }
        
        // 3. Roda a IA
        runSimulation(home, away, oh, od, oa, dateStr, leagueName, realGH, realGA);
    }

    // --- 2. FUNÇÃO DE FECHAR (FECHA QUALQUER CARD ABERTO) ---
    function closeAI() {
        // Fecha todos os tipos de card de análise
        document.querySelectorAll('.ia-analysis-box, #simResultCard').forEach(el => el.style.display = 'none');
        
        if(lastRowClicked) {
            lastRowClicked.scrollIntoView({behavior: 'smooth', block: 'center'});
            lastRowClicked.classList.add('row-highlight');
            setTimeout(() => lastRowClicked.classList.remove('row-highlight'), 1500);
        }
    }

    // --- 3. FUNÇÃO PRINCIPAL DA IA (IMPRIME NO LUGAR CERTO) ---
    function runSimulation(passedHome, passedAway, oh, od, oa, dateStr, leagueName, realGH = null, realGA = null) {
        let home = passedHome || document.getElementById('simHome').value;
        let away = passedAway || document.getElementById('simAway').value;

        // --- LÓGICA DE DESTINO DO RESULTADO ---
        // Procura a aba ativa para saber onde desenhar o resultado
        const activeTab = document.querySelector('.view-section.active');
        let resCard, resDiv;

        if (activeTab && activeTab.id === 'view-future') {
            // Se estiver na aba TODOS JOGOS
            resCard = document.getElementById('box_ia_future'); // ID que adicionamos no HTML acima
            resDiv = resCard ? resCard.querySelector('.ai-content-body') : null;
        } else if (activeTab && activeTab.id === 'view-finished') {
            // Se estiver na aba FINALIZADOS
            resCard = activeTab.querySelector('.ia-analysis-box');
            resDiv = resCard ? resCard.querySelector('.ai-content-body') : null;
        } else {
            // Padrão (Oportunidades ou Backtest)
            resCard = document.getElementById('simResultCard');
            resDiv = document.getElementById('simResult');
        }

        // Fallback de segurança
        if(!resCard || !resDiv) {
            resCard = document.getElementById('simResultCard');
            resDiv = document.getElementById('simResult');
        }

        if(!home || !away || home === away) { alert("Selecione dois times válidos."); return; }
        
        // Exibe o card e rola até ele
        resCard.style.display = 'block'; 
        resDiv.innerHTML = "<div class='spinner' style='width:20px;height:20px;margin:0 auto;'></div> Consultando Oráculo (Modo Histórico)...";
        resCard.scrollIntoView({ behavior: 'smooth', block: 'center' });

        setTimeout(() => {
            let targetLeague = leagueName; 
            if(!targetLeague || targetLeague === 'undefined') { let findLg = allGames.find(g => g.h === home || g.a === home); if(findLg) targetLeague = findLg.c; }
            
            let prediction = null;
            if(typeof predictScore === 'function') prediction = predictScore(home, away, targetLeague, dateStr);
            if(!prediction) { resDiv.innerHTML = "⚠️ Dados insuficientes para traçar o perfil do jogo."; return; }
            
            // Segurança Forma
            const getFormSafe = (tm, dt) => {
                if(typeof getTeamForm === 'function') return getTeamForm(tm, dt);
                let limitDate = dt.includes('/') ? dt.split('/').reverse().join('-') : dt.split(' ')[0];
                let past = allGames.filter(g => g.d < limitDate && (g.h === tm || g.a === tm));
                past.sort((a, b) => new Date(b.d) - new Date(a.d));
                let last5 = past.slice(0, 5);
                let pts = 0;
                last5.forEach(g => { if((g.h===tm&&g.gh>g.ga)||(g.a===tm&&g.ga>g.gh)) pts+=3; else if(g.gh===g.ga) pts+=1; });
                return { pts: pts, count: last5.length };
            };

            let formH = getFormSafe(home, dateStr);
            let formA = getFormSafe(away, dateStr);

            let context = null; 
            if(oh > 0 && typeof analyzeHistoricalContext === 'function') context = analyzeHistoricalContext(targetLeague, oh, od, oa, dateStr);
            
            let fairOddH = 100 / prediction.probHome; 
            let fairOddA = 100 / prediction.probAway;
            
            // --- DECISÃO TÉCNICA ---
            let scoreH = 0, scoreA = 0, scoreOver = 0;
            if (prediction.probHome > 40) scoreH++; if (oh > 0 && oh > fairOddH) scoreH++; if (context && context.winRate > 40) scoreH++;
            if (prediction.probAway > 40) scoreA++; if (oa > 0 && oa > (100/prediction.probAway)) scoreA++; if (context && context.lossRate > 40) scoreA++;
            if (prediction.probOver > 50) scoreOver++; if (context && context.overRate > 50) scoreOver++;

            let recType = "SKIP", recText = "AGUARDAR LIVE", recClass = "rec-skip";
            if (scoreH >= 2) { recType = "HOME"; recClass = "rec-high"; recText = `💎 CASA VENCE`; } 
            else if (scoreA >= 2) { recType = "AWAY"; recClass = "rec-high"; recText = `💎 VISITANTE VENCE`; } 
            else if (scoreOver >= 2) { recType = "OVER"; recClass = "rec-med"; recText = `⚽ OVER 2.5 GOLS`; } 
            else if (prediction.probHome > 45) { recType = "HOME"; recClass = "rec-med"; recText = `📈 TENDÊNCIA CASA`; }
            else if (prediction.probAway > 45) { recType = "AWAY"; recClass = "rec-med"; recText = `📈 TENDÊNCIA VISITANTE`; }
            else if (prediction.probOver > 55) { recType = "OVER"; recClass = "rec-med"; recText = `⚠️ PROVÁVEL OVER`; }
            else if (prediction.probDraw > 35) { recType = "DRAW"; recClass = "rec-low"; recText = `⚖️ RISCO EMPATE`; }

            // --- GERAÇÃO DE OPINIÃO ---
            let opinionList = [];
            const pickRandom = (arr) => arr[Math.floor(Math.random() * arr.length)];

            if(prediction.probHome >= 60) {
                opinionList.push(pickRandom([`🏟️ <b>${home}</b> é muito favorito aqui. O time joga em casa e tem números dominantes.`, `📊 Probabilidade esmagadora para o mandante. Se não ganhar, é zebra histórica.`, `💪 O mando de campo vai pesar. A torcida empurra e o time corresponde.`]));
            } else if (formH.pts >= 12) { opinionList.push(`🔥 O <b>${home}</b> tá voando baixo! Vem amassando os adversários recentemente.`); } 
            else if (formH.pts <= 3) { opinionList.push(`⚠️ Cuidado com o mandante. O time tá numa fase horrível, a bola não entra.`); }

            if(prediction.probAway >= 50) {
                opinionList.push(pickRandom([`✈️ O visitante tem um time superior tecnicamente e deve controlar o jogo.`, `⚔️ Mesmo fora de casa, o <b>${away}</b> impõe respeito e tem tudo pra levar.`, `👀 Olho nesse visitante! As estatísticas mostram que eles jogam melhor fora do que em casa.`]));
            } else if (formA.pts >= 12) { opinionList.push(`🚀 O <b>${away}</b> vem embalado. Confiança lá no teto pra esse jogo.`); }

            if(prediction.probOver > 65) {
                opinionList.push(pickRandom([`⚽ Expectativa de chuva de gols! As duas defesas são umas "mães".`, `🔥 Jogo aberto! Os dois times marcam e sofrem muitos gols. Cenário perfeito pra Over.`, `🥅 A rede vai balançar. O estilo de jogo das duas equipes favorece placar elástico.`]));
            } else if(prediction.probOver < 35) { opinionList.push(`🛡️ Jogo com cara de truncado. Muita marcação e pouco espaço. Under é o caminho.`); } 
            else if(prediction.probBTTS > 60) { opinionList.push(`🤝 A chance de Ambas Marcam é altíssima. Dificilmente alguém sai com Clean Sheet hoje.`); }

            if(oh > 0 && oh > (fairOddH * 1.15)) opinionList.push(`💰 <b>ATENÇÃO:</b> As casas erraram na Odd do mandante. Tem muito valor aqui!`);
            if(oa > 0 && oa > (fairOddA * 1.15)) opinionList.push(`💰 <b>OPORTUNIDADE:</b> O mercado subestimou o visitante. Essa Odd tá pagando mais do que devia.`);

            if(opinionList.length === 0) opinionList.push("⚖️ Jogo de xadrez. Muito equilibrado e decidido nos detalhes. Cautela na entrada.");

            let opinionHTML = opinionList.map(t => `<div style="margin-bottom:4px; padding-left:8px; border-left:3px solid var(--accent-blue); line-height:1.4;">${t}</div>`).join('');
            let reasoning = `<div style="margin-bottom:12px; font-size:11px; color:#aaa; display:flex; gap:10px;"><span>Prob. Casa: <b style="color:#fff">${prediction.probHome.toFixed(0)}%</b></span><span>Prob. Fora: <b style="color:#fff">${prediction.probAway.toFixed(0)}%</b></span></div>`;
            reasoning += `<div style="font-size:12px; color:#e5e5e5; background:#1a1a1a; padding:10px; border-radius:6px; border:1px solid #333;">${opinionHTML}</div>`;

            let resultHTML = "";
            let statusColor = "";

            if (realGH !== null && realGA !== null && realGH !== 'null' && realGH !== undefined) {
                let isGreen = false; let rH = parseInt(realGH); let rA = parseInt(realGA);
                if (recType === "HOME" && rH > rA) isGreen = true; else if (recType === "AWAY" && rA > rH) isGreen = true; else if (recType === "OVER" && (rH + rA) > 2.5) isGreen = true; else if (recType === "DRAW" && rH === rA) isGreen = true; else if (recType === "SKIP") isGreen = null; 

                const winPhrases = ["Eu avisei! Leitura de jogo impecável.", "Não tinha como dar outra coisa. O padrão se confirmou.", "Dinheiro no bolso com tranquilidade. A estatística não mente!", "Foi como tirar doce de criança. Leitura perfeita.", "Quem seguiu a call forrou! Cenário desenhado pela IA."];
                const lossPhrases = ["Inacreditável... o time simplesmente não entrou em campo.", "A bola puniu hoje. Estatística indicava uma coisa, o campo mostrou outra.", "Zebraça histórica! O futebol tem dessas coisas...", "Dia atípico. A lógica foi pro espaço nesse jogo.", "O time dominou mas a bola não entrou. Faz parte do jogo."];
                const voidPhrases = ["Ainda bem que ficamos de fora. Jogo feio.", "IA salvou a banca! O risco de empate era real.", "Leitura correta de risco. Melhor não operar do que perder."];
                let aiComment = "";

                if (recType === "SKIP") { statusColor = "yellow"; aiComment = pickRandom(voidPhrases); resultHTML = `<div style="margin-top:15px; padding:10px; background:#222; border:1px solid #444; border-radius:6px; text-align:center;"><div style="font-size:10px; color:#aaa;">RESULTADO REAL</div><div style="font-size:24px; color:#fff; font-weight:900;">${rH} - ${rA}</div><div style="font-size:12px; color:#f59e0b;">IA se absteve (VOID).</div><div style="margin-top:8px; font-size:11px; font-style:italic; color:#999;">"${aiComment}"</div></div>`; } 
                else if (isGreen) { statusColor = "green"; aiComment = pickRandom(winPhrases); resultHTML = `<div style="margin-top:15px; padding:10px; background:rgba(16, 185, 129, 0.15); border:1px solid #10b981; border-radius:6px; text-align:center;"><div style="color:#10b981; font-weight:900; font-size:10px;">BINGO! IA ACERTOU</div><div style="font-size:24px; color:#fff; font-weight:900;">${rH} - ${rA}</div><div style="color:#10b981; font-size:14px; font-weight:bold;">${recText} ✅</div><div style="margin-top:8px; font-size:11px; font-style:italic; color:#a7f3d0; border-top:1px solid rgba(16,185,129,0.3); padding-top:6px;">"${aiComment}"</div></div>`; } 
                else { statusColor = "red"; aiComment = pickRandom(lossPhrases); resultHTML = `<div style="margin-top:15px; padding:10px; background:rgba(239, 68, 68, 0.15); border:1px solid #ef4444; border-radius:6px; text-align:center;"><div style="color:#ef4444; font-weight:900; font-size:10px;">RED (IA ERROU)</div><div style="font-size:24px; color:#fff; font-weight:900;">${rH} - ${rA}</div><div style="color:#ef4444; font-size:14px; font-weight:bold;">${recText} ❌</div><div style="margin-top:8px; font-size:11px; font-style:italic; color:#fca5a5; border-top:1px solid rgba(239,68,68,0.3); padding-top:6px;">"${aiComment}"</div></div>`; }

                if (lastRowClicked) {
                    lastRowClicked.classList.remove('row-green', 'row-red', 'row-yellow');
                    if (statusColor === "green") lastRowClicked.classList.add('row-green'); else if (statusColor === "red") lastRowClicked.classList.add('row-red'); else lastRowClicked.classList.add('row-yellow');
                }
            } else { resultHTML = `<div style="margin-top:10px; font-size:10px; color:#666; text-align:center;">Aguardando resultado final...</div>`; }

            let veredictoHTML = `<div class="verdict-box"><div class="verdict-header"><div class="verdict-icon">🧠</div><div class="verdict-title">Opinião do Especialista (IA)</div></div><div class="verdict-content">${reasoning}<div style="text-align:center; margin-top:15px;"><span class="recommendation-tag ${recClass}">${recText}</span></div>${resultHTML}</div></div><button class="btn-back-ai" onclick="closeAI()">⬅ Fechar Análise</button>`;
            let header = `<div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:10px;"><div style="font-size:14px; font-weight:900; color:#fff;">${home} <span style="color:#666; font-weight:400;">vs</span> ${away}</div><div style="font-size:10px; color:#888;">${targetLeague || ''}</div></div>`;
            let statsRow = `<div style="margin-bottom:10px; display:flex; gap:10px;"><div style="flex:1; background:#151515; padding:8px; border-radius:6px; font-size:11px;">⚽ xG Estimado: <b>${prediction.expectedGoalsH.toFixed(2)}</b> x <b>${prediction.expectedGoalsA.toFixed(2)}</b></div><div style="flex:1; background:#151515; padding:8px; border-radius:6px; font-size:11px;">🤝 BTTS (Ambas): <b>${prediction.probBTTS.toFixed(1)}%</b></div></div>`;
            let probsRow = `<div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px; text-align:center; margin-bottom:15px;"><div style="background:#111; padding:8px; border-radius:6px; border:1px solid #333;"><div style="font-size:10px; color:#888;">CASA</div><div style="font-size:18px; font-weight:bold; color:${prediction.probHome > 40 ? '#10b981' : '#fff'}">${prediction.probHome.toFixed(1)}%</div></div><div style="background:#111; padding:8px; border-radius:6px; border:1px solid #333;"><div style="font-size:10px; color:#888;">EMPATE</div><div style="font-size:18px; font-weight:bold; color:#f59e0b">${prediction.probDraw.toFixed(1)}%</div></div><div style="background:#111; padding:8px; border-radius:6px; border:1px solid #333;"><div style="font-size:10px; color:#888;">VISITANTE</div><div style="font-size:18px; font-weight:bold; color:${prediction.probAway > 40 ? '#ef4444' : '#fff'}">${prediction.probAway.toFixed(1)}%</div></div></div>`;

            resDiv.innerHTML = header + probsRow + statsRow + veredictoHTML;
        }, 300);
    }

    // --- 4. FUNÇÕES GERAIS ---
    function setSelectByText(id, t) { const s = document.getElementById(id); for(let i=0; i<s.options.length; i++) { if(s.options[i].text === t) { s.selectedIndex = i; return; } } let p = t.substring(0,4).toLowerCase(); for(let i=0; i<s.options.length; i++) { if(s.options[i].text.toLowerCase().includes(p)) { s.selectedIndex = i; return; } } }
    function filterScan(t, b) { currentScanFilter = t; document.querySelectorAll('.badge-filter').forEach(e => e.classList.remove('active')); if(b) b.classList.add('active'); scanOpportunities(); }
    
// SUBSTITUA A FUNÇÃO "renderTable" POR ESTA VERSÃO COMPLETA (COM HT E HF):
function renderTable(ap) { 
    if(ap) limit += 20; 
    const s = filteredData.slice(0, limit); 
    
    // Helper para definir letra do resultado (C=Casa, E=Empate, V=Visitante)
    const getResLetra = (h, a) => {
        if(h > a) return 'C';
        if(a > h) return 'V';
        return 'E';
    };

    let h = `<table>
                <thead>
                    <tr>
                        <th>DATA</th>
                        <th style="text-align:right">CASA</th>
                        <th>PLACAR</th>
                        <th style="text-align:left">VISITANTE</th>
                        <th>DETALHES / HF</th>
                        <th>ODDS</th>
                    </tr>
                </thead>
                <tbody>`; 
                
    s.forEach(g => { 
        let ch = g.gh > g.ga ? 'text-win' : 'text-loss'; 
        let ca = g.ga > g.gh ? 'text-win' : 'text-loss'; 
        
        let badges = "";
        let htDisplay = ""; // Variável para o texto do placar HT

        // --- LÓGICA HT e HF ---
        if (g.hth !== null && g.hta !== null) {
            // 1. Prepara o Placar HT para exibir visualmente
            htDisplay = `<span class="score-ht">(${g.hth}-${g.hta}) HT</span>`;

            // 2. Calcula o HT/FT (HF)
            let resHT = getResLetra(parseInt(g.hth), parseInt(g.hta));
            let resFT = getResLetra(parseInt(g.gh), parseInt(g.ga));
            
            // Cria o badge ex: C/C (Casa/Casa) ou E/V (Empate/Virada Visitante)
            badges += `<span class="mini-badge badge-hf" title="HT / FT (Intervalo/Final)">${resHT}/${resFT}</span>`;
            
            // Se houve virada (Ex: Casa ganhava HT, Visitante ganhou FT), destaca!
            if(resHT !== 'E' && resFT !== 'E' && resHT !== resFT) {
                badges += `<span class="mini-badge" style="background:#ef4444; border:1px solid red;" title="Virada!">↺</span>`;
            }
        }

        // --- OUTROS BADGES (Over, BTTS) ---
        if ((g.gh + g.ga) > 2.5) badges += '<span class="mini-badge badge-over">+2.5</span>';
        if (g.gh > 0 && g.ga > 0) badges += '<span class="mini-badge badge-btts">BTTS</span>';
        
        // HT Goal (se saiu gol no HT)
        if (g.hth !== null && (parseInt(g.hth) + parseInt(g.hta)) > 0) {
             badges += '<span class="mini-badge badge-ht">GOL HT</span>';
        }

        if (badges === "") badges = '<span style="color:#333; font-size:10px;">-</span>';

        h += `<tr>
                <td style="color:#666;text-align:center;">
                    ${g.dr}<br><span class="camp-tag">${g.c}</span>
                </td>
                <td style="text-align:right;" class="${ch}">
                    ${g.h} <img src="${getLogo(g.h)}" onerror="this.src='https://cdn-icons-png.flaticon.com/512/16/16480.png'" class="team-logo">
                </td>
                
                <td style="text-align:center;">
                    <span class="score">${g.gh}-${g.ga}</span>
                    ${htDisplay} </td>
                
                <td style="text-align:left;" class="${ca}">
                    <img src="${getLogo(g.a)}" onerror="this.src='https://cdn-icons-png.flaticon.com/512/16/16480.png'" class="team-logo"> ${g.a}
                </td>
                
                <td style="text-align:center; vertical-align:middle;">
                    ${badges}
                </td>

                <td style="text-align:center;font-size:11px;">
                    ${g.oh}|${g.od}|${g.oa}
                </td>
              </tr>`; 
    }); 
    
    h += `</tbody></table>`; 
    document.getElementById('tableContainer').innerHTML = h; 
    
    const btn=document.getElementById('btnMore'); 
    if(btn) btn.style.display=filteredData.length>limit?'block':'none';
}
    function setStatMode(m, e) { currentStatMode = m; document.querySelectorAll('.stats-pill').forEach(s => s.classList.remove('active')); e.classList.add('active'); renderStatsChart(); }
    function applyFilters() { limit = 20; const l = document.getElementById('selLeague').value; const dS = document.getElementById('dateStart').value; const dE = document.getElementById('dateEnd').value; const minO = parseFloat(document.getElementById('filterOddMin').value); const maxO = parseFloat(document.getElementById('filterOddMax').value); const mkt = document.getElementById('filterMarket').value; const txtHome=document.getElementById('filterTeamHome').value.toLowerCase(); const txtAway=document.getElementById('filterTeamAway').value.toLowerCase(); const txtAny=document.getElementById('filterTeamAny').value.toLowerCase(); filteredData = allGames.filter(g => { if(l && g.c !== l) return false; if(dS && g.d < dS) return false; if(dE && g.d > dE) return false; if(!isNaN(minO) && g.oh < minO) return false; if(!isNaN(maxO) && g.oh > maxO) return false; if(mkt === 'HOME_WIN' && !(g.gh > g.ga)) return false; if(mkt === 'DRAW' && !(g.gh == g.ga)) return false; if(mkt === 'AWAY_WIN' && !(g.ga > g.gh)) return false; if(mkt === 'OVER25' && !((g.gh + g.ga) > 2.5)) return false; if(txtHome && !g.h.toLowerCase().includes(txtHome)) return false; if(txtAway && !g.a.toLowerCase().includes(txtAway)) return false; if(txtAny && !(g.h.toLowerCase().includes(txtAny) || g.a.toLowerCase().includes(txtAny))) return false; return true; }); const has = filteredData.length > 0; document.getElementById('cardStats').style.display = has ? 'block' : 'none'; document.getElementById('cardAI').style.display = has ? 'block' : 'none'; if(!has) { document.getElementById('tableContainer').innerHTML = '<p style="padding:20px;text-align:center;">Sem dados.</p>'; } else { renderStatsChart(); renderTopCardsData(); updateHistoryAI(); renderTable(false); } }
    
    function setGameResult(btn, r, h, a, d) { btn.innerHTML = "..."; $.post(window.location.href, { acao: 'salvar_feedback', casa: h, fora: a, data: d, resultado: r }, function(res) { if(res.trim() === 'OK') { btn.innerHTML = r === 'WIN' ? '✅' : '❌'; } else { alert("Erro"); } }); }
    function toggleRow(c) { const r = c.closest('tr'); if(c.checked) r.classList.add('row-checked'); else r.classList.remove('row-checked'); }
    
    let activeOppFilter = 'ALL';

    // --- FUNÇÃO PARA TROCAR O FILTRO (CLIQUE NO BOTÃO) ---
    function filterScan(type, btn) {
        activeOppFilter = type;
        
        // Atualiza visual dos botões
        document.querySelectorAll('.badge-filter').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        // Roda o scanner novamente com o novo filtro
        scanOpportunities();
    }

// --- FUNÇÃO PARA ESCONDER JOGOS "ABSTEVE" ---
    let isSkipHidden = false;

    function toggleSkipped() {
        isSkipHidden = !isSkipHidden;
        const btn = document.getElementById('btnFilterSkip');
        const rows = document.querySelectorAll('.row-game-finished');

        // Atualiza o estilo do botão para indicar se está ativo
        if(isSkipHidden) {
            btn.style.background = "var(--accent-blue)";
            btn.style.borderColor = "var(--accent-blue)";
            btn.style.color = "#fff";
            btn.innerHTML = "👁️ MOSTRAR TODOS";
        } else {
            btn.style.background = "#333";
            btn.style.borderColor = "#555";
            btn.style.color = "#aaa";
            btn.innerHTML = "👁️ MOSTRAR SÓ PALPITES";
        }

        // Itera sobre as linhas e esconde/mostra
        rows.forEach(row => {
            // Pega o texto da coluna da IA
            const verdictCell = row.querySelector('.cell-ai-verdict');
            const verdict = verdictCell ? verdictCell.innerText.toUpperCase() : "";
            
            // Condição para esconder:
            // 1. O filtro está ligado
            // 2. E o texto contém ABSTEVE, SKIP, ou está vazio/aguardando (...)
            const isIgnored = verdict.includes('ABSTEVE') || verdict.includes('SKIP') || verdict === '...' || verdict === '-' || verdict.trim() === '';

            if(isSkipHidden && isIgnored) {
                row.style.display = 'none';
            } else {
                row.style.display = 'table-row';
            }
        });
    }

    // --- LÓGICA DO SLIDER DUPLO ---
    function slideOne() {
        let sliderOne = document.getElementById("slider-1");
        let sliderTwo = document.getElementById("slider-2");
        let displayValOne = document.getElementById("valMin");
        let minGap = 0.10; // Distância mínima entre as bolinhas
        
        if (parseFloat(sliderTwo.value) - parseFloat(sliderOne.value) <= minGap) {
            sliderOne.value = parseFloat(sliderTwo.value) - minGap;
        }
        displayValOne.textContent = parseFloat(sliderOne.value).toFixed(2);
        fillColor();
    }

    function slideTwo() {
        let sliderOne = document.getElementById("slider-1");
        let sliderTwo = document.getElementById("slider-2");
        let displayValTwo = document.getElementById("valMax");
        let minGap = 0.10;
        
        if (parseFloat(sliderTwo.value) - parseFloat(sliderOne.value) <= minGap) {
            sliderTwo.value = parseFloat(sliderOne.value) + minGap;
        }
        displayValTwo.textContent = parseFloat(sliderTwo.value).toFixed(2);
        fillColor();
    }

    function fillColor() {
        let sliderOne = document.getElementById("slider-1");
        let sliderTwo = document.getElementById("slider-2");
        let sliderTrack = document.getElementById("sliderTrack");
        let percent1 = ((sliderOne.value - sliderOne.min) / (sliderOne.max - sliderOne.min)) * 100;
        let percent2 = ((sliderTwo.value - sliderOne.min) / (sliderOne.max - sliderOne.min)) * 100;
        
        // Pinta o fundo: Cinza antes, Verde no meio, Cinza depois
        sliderTrack.style.background = `linear-gradient(to right, #333 ${percent1}%, var(--accent-green) ${percent1}%, var(--accent-green) ${percent2}%, #333 ${percent2}%)`;
        
        // Chama o filtro (com um pequeno delay para não travar, opcional, ou direto)
        // Aqui usaremos debounce simples se quiser, ou chama direto:
        // Mas para evitar travar, vamos deixar o usuário soltar o mouse (onchange)
        // Adicionaremos o listener de 'change' abaixo.
    }
    
    // Inicializa a cor ao carregar
    window.addEventListener('load', function() {
        fillColor();
        // Adiciona evento para filtrar só quando soltar o mouse (evita travar)
        document.getElementById("slider-1").addEventListener('change', scanOpportunities);
        document.getElementById("slider-2").addEventListener('change', scanOpportunities);
    });

    // --- FUNÇÃO SCANNER TURBINADA (COM FILTROS E IA) ---
    function scanOpportunities() { 
        const container = document.getElementById('scanResults'); 
        container.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:20px;"><div class="spinner" style="width:20px;height:20px;margin:0 auto;"></div> Buscando melhores oportunidades...</div>'; 
        
        if(!upcomingGames || upcomingGames.length === 0) { 
            container.innerHTML = '<div style="padding:40px; text-align:center; color:#666; font-style:italic; grid-column:1/-1;">Nenhum jogo futuro encontrado na base.<br>Atualize a API na aba "Scanner Pro".</div>'; 
            return; 
        } 
        
        // Lista temporária para guardar e ordenar as oportunidades
        let opportunities = [];
        
        // 1. PROCESSAMENTO EM MASSA
        upcomingGames.forEach(g => { 
            let oh = parseFloat(g.odds_h), oa = parseFloat(g.odds_a);
            
            // Dados básicos
            let lgName = leagueNames[g.sport_key] || g.sport_key;
            let prediction = null;
            
            // Roda a IA para classificar o jogo
            if(typeof predictScore === 'function') {
                prediction = predictScore(g.home_team, g.away_team, lgName, '2025-12-31');
            }

            // Se não tiver dados suficientes, pula
            if(!prediction) return;

            // CRITÉRIOS DE CLASSIFICAÇÃO
            let tags = [];
            let priority = 0; // Para ordenação
            let mainProb = 0; // Para mostrar no card
            let mainTag = ""; // Texto da etiqueta
            let color = "#444"; // Cor da borda

            // A) SUPER FAVORITO (Baseado em Odd)
            if(oh < 1.45) { tags.push('SUPER_FAV'); mainTag = "SUPER FAVORITO (CASA)"; color = "#10b981"; priority += 10; mainProb = prediction.probHome; }
            else if(oa < 1.45) { tags.push('SUPER_FAV'); mainTag = "SUPER FAVORITO (FORA)"; color = "#ef4444"; priority += 10; mainProb = prediction.probAway; }

            // B) ALTA PROBABILIDADE IA (Baseado em Estatística Pura)
            if(prediction.probHome > 70) { tags.push('HIGH_PROB'); if(!mainTag) { mainTag = "IA CONFIA (CASA)"; color = "#10b981"; } priority += prediction.probHome; mainProb = prediction.probHome; }
            else if(prediction.probAway > 70) { tags.push('HIGH_PROB'); if(!mainTag) { mainTag = "IA CONFIA (FORA)"; color = "#ef4444"; } priority += prediction.probAway; mainProb = prediction.probAway; }

            // C) ZEBRAS (IA aposta na Zebra ou Odd Alta com valor)
            let fairH = 100/prediction.probHome;
            let fairA = 100/prediction.probAway;
            if(oh > 2.80 && prediction.probHome > 40) { tags.push('ZEBRA'); if(!mainTag) { mainTag = "RISCO DE ZEBRA"; color = "#f59e0b"; } priority += 5; }
            if(oa > 2.80 && prediction.probAway > 40) { tags.push('ZEBRA'); if(!mainTag) { mainTag = "RISCO DE ZEBRA"; color = "#f59e0b"; } priority += 5; }

            // D) GOLS (Over)
            if(prediction.probOver > 65) { tags.push('GOALS'); if(!mainTag) { mainTag = "CHUVA DE GOLS"; color = "#f97316"; } priority += prediction.probOver; }

            // E) VALOR (Value Bet)
            if((oh > 1.60 && oh < 2.20 && oh > fairH*1.1) || (oa > 1.60 && oa < 2.20 && oa > fairA*1.1)) {
                tags.push('VALUE'); if(!mainTag) { mainTag = "VALOR ENCONTRADO"; color = "#8b5cf6"; }
            }

            // Se o jogo se encaixou em algo, adiciona na lista
            if(tags.length > 0 || activeOppFilter === 'ALL') {
                opportunities.push({
                    game: g,
                    tags: tags,
                    pred: prediction,
                    priority: priority, // Usado para ordenar "Melhores primeiro"
                    display: { tag: mainTag, color: color, prob: mainProb }
                });
            }
        });

        // 2. FILTRAGEM
        let filtered = opportunities;
        
        // A) Filtro por Botões (Tags)
        if(activeOppFilter !== 'ALL') {
            filtered = opportunities.filter(op => op.tags.includes(activeOppFilter));
        }

        // B) Filtro por Intervalo de Odds (Slider Duplo)
        // CORREÇÃO: Usando os IDs certos do novo slider (slider-1 e slider-2)
        const elMin = document.getElementById('slider-1');
        const elMax = document.getElementById('slider-2');
        
        if (elMin && elMax) {
            const minVal = parseFloat(elMin.value);
            const maxVal = parseFloat(elMax.value);
            
            filtered = filtered.filter(op => {
                let oh = parseFloat(op.game.odds_h);
                let oa = parseFloat(op.game.odds_a);
                
                // Lógica: O jogo aparece se a odd da CASA *OU* a odd de FORA estiver dentro da barra verde
                let casaServe = (oh >= minVal && oh <= maxVal);
                let foraServe = (oa >= minVal && oa <= maxVal);
                
                return casaServe || foraServe;
            });
        }

        let html = "";
        // Limita a 12 cards para não travar se tiver muitos
        let limitRender = filtered.slice(0, 12);

        limitRender.forEach(op => {
            let g = op.game;
            let p = op.pred;
            let d = new Date(g.commence_time);
            let day = d.getDate().toString().padStart(2,'0')+"/"+(d.getMonth()+1).toString().padStart(2,'0'); 
            let hora = d.getHours().toString().padStart(2,'0') + ":" + d.getMinutes().toString().padStart(2,'0');
            
            let hEsc = g.home_team.replace(/'/g, "\\'").replace(/"/g, "&quot;"); 
            let aEsc = g.away_team.replace(/'/g, "\\'").replace(/"/g, "&quot;"); 
            let cleanH = g.home_team.replace(/[^A-Za-z0-9]/g, ''); 
            let cleanA = g.away_team.replace(/[^A-Za-z0-9]/g, '');

            let probText = "";
            if(op.display.prob > 0) probText = `<div style="font-size:10px; margin-top:2px;">Prob IA: <b>${op.display.prob.toFixed(0)}%</b></div>`;

            html += `
            <div class="sug-card" style="border-left:4px solid ${op.display.color}; background: linear-gradient(145deg, #1a1a1a, #111); padding:12px; border-radius:8px; position:relative; overflow:hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.3); animation: fadeIn 0.5s;">
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;">
                    <div style="font-size:10px; font-weight:bold; background:#333; padding:2px 8px; border-radius:4px; color:#ccc;">${day} ${hora}</div>
                    <div style="text-align:right;">
                        <div style="font-size:9px; font-weight:900; color:${op.display.color}; text-transform:uppercase; letter-spacing:1px; border:1px solid ${op.display.color}; padding:2px 6px; border-radius:4px;">${op.display.tag}</div>
                    </div>
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                    <div style="text-align:center; flex:1;">
                        <img src="logos/${cleanH}.png" onerror="this.src='https://cdn-icons-png.flaticon.com/512/16/16480.png'" style="width:30px; height:30px; object-fit:contain; filter:drop-shadow(0 0 3px rgba(255,255,255,0.1));">
                        <div style="font-size:11px; font-weight:bold; color:#fff; margin-top:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:80px;">${g.home_team}</div>
                    </div>
                    <div style="font-size:10px; color:#666; font-weight:bold;">vs</div>
                    <div style="text-align:center; flex:1;">
                        <img src="logos/${cleanA}.png" onerror="this.src='https://cdn-icons-png.flaticon.com/512/16/16480.png'" style="width:30px; height:30px; object-fit:contain; filter:drop-shadow(0 0 3px rgba(255,255,255,0.1));">
                        <div style="font-size:11px; font-weight:bold; color:#fff; margin-top:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:80px;">${g.away_team}</div>
                    </div>
                </div>

                <div style="background:#000; padding:6px; border-radius:6px; display:flex; justify-content:space-between; align-items:center; border:1px solid #333; margin-bottom:10px;">
                    <div style="font-size:10px; color:#888;">ODDS</div>
                    <div style="font-size:11px; font-weight:bold; color:#fff;">
                        <span style="color:${p.probHome>p.probAway?'#10b981':'#aaa'}">${parseFloat(g.odds_h).toFixed(2)}</span> | 
                        <span style="color:${p.probAway>p.probHome?'#ef4444':'#aaa'}">${parseFloat(g.odds_a).toFixed(2)}</span>
                    </div>
                </div>

                <div style="font-size:10px; color:#ccc; margin-bottom:10px; text-align:center; padding:4px; background:rgba(255,255,255,0.05); border-radius:4px;">
                    Confiança IA: <b style="color:${op.display.color}">${Math.max(p.probHome, p.probAway).toFixed(0)}%</b>
                </div>

                <button class="btn" style="width:100%; padding:8px; font-size:10px; font-weight:900; text-transform:uppercase; background:transparent; border:1px solid #444; color:#aaa; transition:0.3s; cursor:pointer;" 
                    onmouseover="this.style.borderColor='${op.display.color}'; this.style.color='#fff'; this.style.background='rgba(255,255,255,0.05)'" 
                    onmouseout="this.style.borderColor='#444'; this.style.color='#aaa'; this.style.background='transparent'"
                    onclick="loadIntoSimAndCheck(this, '${hEsc}', '${aEsc}', ${g.odds_h}, ${g.odds_d}, ${g.odds_a}, '${day}', '${g.sport_key}')">
                    🔮 Ver Análise Completa
                </button>
            </div>`;
        });

        // Configura o Grid CSS via JS
        container.style.display = 'grid';
        container.style.gridTemplateColumns = 'repeat(auto-fill, minmax(220px, 1fr))';
        container.style.gap = '15px';
        container.innerHTML = html;
    }
    
    function toggleScanMore() {
        const h = document.getElementById('hiddenScanItems');
        const b = document.getElementById('btnScanMore');
        if(h.style.display === 'none') { h.style.display = 'contents'; b.innerText = 'Mostrar Menos'; }
        else { h.style.display = 'none'; b.innerText = 'Ver mais ...'; }
    }

    // ==================================================================
    // 🛠️ FUNÇÕES QUE ESTAVAM FALTANDO (ADICIONE ISTO)
    // ==================================================================

    // 1. Sincronizar API com a Data Escolhida
    function syncApiByDate() {
        const datePicker = document.getElementById('datePickerMain');
        const hiddenInput = document.getElementById('hiddenDateTarget');
        const form = document.getElementById('formApiSync');
        
        if(datePicker && hiddenInput && form) {
            hiddenInput.value = datePicker.value;
            if(confirm(`Buscar resultados oficiais do dia ${datePicker.value} na API?`)) {
                form.submit();
            }
        } else {
            alert("Erro: Elementos de data não encontrados.");
        }
    }

    // Variável para o Relatório
    let dailyStats = { home: {hits:0, total:0}, away: {hits:0, total:0}, over: {hits:0, total:0}, totalGreens: 0, totalReds: 0 };

    // 2. Conferir Todos os Jogos (Botão Roxo)
    function analyzeAllFinishedGames() {
        const rows = document.querySelectorAll('.row-game-finished');
        if(rows.length === 0) { alert("Nenhum jogo na tabela."); return; }
        
        // Reset visual
        let cGreen = 0, cRed = 0, cVoid = 0;
        dailyStats = { home: {hits:0, total:0}, away: {hits:0, total:0}, over: {hits:0, total:0}, totalGreens: 0, totalReds: 0 };
        
        document.getElementById('cntGreen').innerText = "0";
        document.getElementById('cntRed').innerText = "0";
        document.getElementById('cntVoid').innerText = "0";
        
        const box = document.getElementById('dailySummaryBox');
        if(box) {
            box.style.display = 'block';
            document.getElementById('dailySummaryText').innerHTML = "<span class='spinner' style='width:15px;height:15px;border-width:2px;display:inline-block;'></span> Analisando...";
        }

        rows.forEach((row, index) => {
            setTimeout(() => {
                const h = row.getAttribute('data-h');
                const a = row.getAttribute('data-a');
                const oh = parseFloat(row.getAttribute('data-oh'));
                const od = parseFloat(row.getAttribute('data-od'));
                const oa = parseFloat(row.getAttribute('data-oa'));
                const dateStr = row.getAttribute('data-dt');
                const league = row.getAttribute('data-lg');
                const gh = row.getAttribute('data-gh');
                const ga = row.getAttribute('data-ga');
                const cellVerdict = row.querySelector('.cell-ai-verdict');

                cellVerdict.innerHTML = "⏳";

                // Identifica Liga
                let targetLeague = league; 
                if(!targetLeague) { let f = allGames.find(g => g.h === h || g.a === h); if(f) targetLeague = f.c; }

                // Previsão
                let p = predictScore(h, a, targetLeague, dateStr);
                if(!p) { cellVerdict.innerHTML = "-"; return; }

                let ctx = null; if(oh > 0) ctx = analyzeHistoricalContext(targetLeague, oh, od, oa, dateStr);
                let fairH = 100 / p.probHome;

                // Pontuação
                let sH = 0, sA = 0, sO = 0;
                if(p.probHome > 40) sH++; if(oh>0 && oh>fairH) sH++; if(ctx && ctx.winRate>40) sH++;
                if(p.probAway > 40) sA++; if(oa>0 && oa>(100/p.probAway)) sA++; if(ctx && ctx.lossRate>40) sA++;
                if(p.probOver > 50) sO++; if(ctx && ctx.overRate>50) sO++;

                let rec = "SKIP", label = "⚪";
                if(sH >= 2) { rec = "HOME"; label = "CASA"; }
                else if(sA >= 2) { rec = "AWAY"; label = "ZEBRA"; }
                else if(sO >= 2) { rec = "OVER"; label = "OVER"; }
                else if(p.probHome > 45) { rec = "HOME"; label = "CASA (L)"; }
                else if(p.probAway > 45) { rec = "AWAY"; label = "FORA (L)"; }
                else if(p.probOver > 55) { rec = "OVER"; label = "OVER (L)"; }

                // --- NOVO: VISUALIZAÇÃO DAS PORCENTAGENS ---
                // Mostra a % da Casa e do Visitante em cinza pequeno
                let probDisplay = `<div style="font-size:9px; color:#aaa; margin-top:3px; font-weight:normal;">
                    🏠${p.probHome.toFixed(0)}% <span style="color:#444">|</span> ✈️${p.probAway.toFixed(0)}%
                </div>`;

                // Confere Resultado
                let html = "";
                let dbResult = "";
                
                if(gh !== 'null' && ga !== 'null') {
                    let rH = parseInt(gh); let rA = parseInt(ga);
                    let isGreen = false;
                    
                    if(rec === "HOME" && rH > rA) isGreen = true;
                    else if(rec === "AWAY" && rA > rH) isGreen = true;
                    else if(rec === "OVER" && (rH+rA) > 2.5) isGreen = true;
                    else if(rec === "SKIP") isGreen = null;

                    if (rec !== "SKIP") {
                        let k = (rec === "HOME") ? 'home' : (rec === "AWAY" ? 'away' : 'over');
                        if(dailyStats[k]) { dailyStats[k].total++; if (isGreen) dailyStats[k].hits++; }
                    }

                    if(rec === "SKIP") {
                        html = `<span style="color:#666; font-size:10px;">ABSTEVE</span>`;
                        cVoid++;
                    } else if(isGreen) {
                        html = `<span style="background:rgba(16,185,129,0.2); color:#10b981; border:1px solid #10b981; padding:2px 6px; border-radius:4px; font-weight:bold; font-size:10px;">✅ ${label}</span>`;
                        row.style.background = "rgba(16, 185, 129, 0.05)";
                        cGreen++;
                        dailyStats.totalGreens++;
                        dbResult = "WIN";
                    } else {
                        html = `<span style="background:rgba(239,68,68,0.2); color:#ef4444; border:1px solid #ef4444; padding:2px 6px; border-radius:4px; font-weight:bold; font-size:10px;">❌ ${label}</span>`;
                        cRed++;
                        dailyStats.totalReds++;
                        dbResult = "LOSS";
                    }

                    if(dbResult !== "") saveAutoFeedback(h, a, dateStr, dbResult, rec, cellVerdict);
                } else {
                    html = `<span style="color:#aaa; font-size:10px;">${label}</span>`;
                }

                // ADICIONA O BADGE + AS PORCENTAGENS
                cellVerdict.innerHTML = html + probDisplay; 
                
                document.getElementById('cntGreen').innerText = cGreen;
                document.getElementById('cntRed').innerText = cRed;
                document.getElementById('cntVoid').innerText = cVoid;

                if (index === rows.length - 1) setTimeout(generateDailyInsight, 500);

            }, index * 50);
        });
    }

    // 3. Salvar no Banco (AJAX Silencioso)
    function saveAutoFeedback(h, a, d, result, palpiteType, cellElement) {
        $.post(window.location.href, {
            acao: 'salvar_feedback',
            casa: h, fora: a, data: d,
            resultado: result,
            palpite: palpiteType
        });
    }

    // 4. Gerar Relatório em Texto
    function generateDailyInsight() {
        const totalOps = dailyStats.totalGreens + dailyStats.totalReds;
        if (totalOps === 0) {
            document.getElementById('dailySummaryText').innerHTML = "Sem dados suficientes para gerar insights.";
            return;
        }

        const winRate = (dailyStats.totalGreens / totalOps) * 100;
        const homeRate = dailyStats.home.total > 0 ? (dailyStats.home.hits / dailyStats.home.total * 100) : 0;
        const overRate = dailyStats.over.total > 0 ? (dailyStats.over.hits / dailyStats.over.total * 100) : 0;

        let title = winRate >= 60 ? "🌟 DIA POSITIVO!" : (winRate >= 45 ? "⚖️ DIA EQUILIBRADO" : "⛈️ DIA DIFÍCIL");
        let analysis = `Taxa de Acerto: <b style="color:${winRate>=50?'#10b981':'#ef4444'}">${winRate.toFixed(0)}%</b>.<br><br>`;

        if (dailyStats.home.total > 0) analysis += `${homeRate > 50 ? '✅' : '❌'} <b>Favoritos Casa:</b> ${homeRate.toFixed(0)}% acerto.<br>`;
        if (dailyStats.over.total > 0) analysis += `${overRate > 50 ? '✅' : '❌'} <b>Gols (Over):</b> ${overRate.toFixed(0)}% acerto.<br>`;

        const text = document.getElementById('dailySummaryText');
        text.innerHTML = `<strong style="color:#fff;">${title}</strong><br>${analysis}`;
    }

    function initAIAccuracyCheck() { if(!allGames || allGames.length < 100) return; let h = 0, t = 0; for(let i = 0; i < 50; i++) { let g = allGames[Math.floor(Math.random() * allGames.length)]; let p = predictScore(g.h, g.a, g.c, g.d); if(!p) continue; let pred = 'D'; if(p.probHome > 50) pred = 'H'; else if(p.probAway > 50) pred = 'A'; let real = 'D'; if(g.gh > g.ga) real = 'H'; else if(g.ga > g.gh) real = 'A'; t++; if(pred === real) h++; } if(t > 0) document.getElementById('aiAccuracyDisplay').innerText = ((h / t) * 100).toFixed(0) + "%"; }
// --- FUNÇÃO DE RENDERIZAÇÃO DO GRÁFICO (CORRIGIDA) ---
function renderStatsChart() {
    // 1. Verifica se o Canvas existe
    const ctxElement = document.getElementById('chartStats');
    if (!ctxElement) return;
    const ctx = ctxElement.getContext('2d');

    // 2. Destroi gráfico anterior para evitar sobreposição
    if (chartStats instanceof Chart) {
        chartStats.destroy();
    }

    // 3. Define variáveis de dados
    let labels = [], dataValues = [], colors = [];
    let mainLabel = "", mainValue = "0%";
    
    // Contadores
    let c1 = 0, c2 = 0, c3 = 0; 
    let total = filteredData.length;

    if (total === 0) return; // Se não tem dados, não desenha

    // 4. Lógica baseada no botão selecionado
    if (currentStatMode === '1x2') {
        // --- MODO: QUEM VENCEU (FT) ---
        mainLabel = "Taxa de Vitória (Casa)";
        filteredData.forEach(g => {
            if (g.gh > g.ga) c1++;      // Casa
            else if (g.ga > g.gh) c3++; // Fora
            else c2++;                  // Empate
        });
        labels = ['Casa', 'Empate', 'Fora'];
        dataValues = [c1, c2, c3];
        colors = ['#10b981', '#f59e0b', '#ef4444']; // Verde, Amarelo, Vermelho
        mainValue = Math.round((c1 / total) * 100) + "%";
        updateLegend("Vitória Casa", c1, "Empate", c2, "Vitória Fora", c3);

    } else if (currentStatMode === 'GOLS') {
        // --- MODO: OVER / UNDER 2.5 ---
        mainLabel = "Taxa Over 2.5";
        filteredData.forEach(g => {
            if ((g.gh + g.ga) > 2.5) c1++; // Over
            else c2++;                     // Under
        });
        labels = ['Over 2.5', 'Under 2.5'];
        dataValues = [c1, c2];
        colors = ['#f97316', '#333333']; // Laranja, Cinza
        mainValue = Math.round((c1 / total) * 100) + "%";
        updateLegend("Over 2.5 Gols", c1, "Under 2.5 Gols", c2, "", null);

    } else if (currentStatMode === 'TEMPOS') {
        // --- MODO: INTERVALO (HT) ---
        mainLabel = "Vitória no HT (Casa)";
        filteredData.forEach(g => {
            if (g.hth !== null && g.hta !== null) {
                if (parseInt(g.hth) > parseInt(g.hta)) c1++; 
                else if (parseInt(g.hta) > parseInt(g.hth)) c3++; 
                else c2++; 
            }
        });
        labels = ['Casa HT', 'Empate HT', 'Fora HT'];
        dataValues = [c1, c2, c3];
        colors = ['#3b82f6', '#94a3b8', '#8b5cf6']; // Azul, Cinza, Roxo
        mainValue = Math.round((c1 / total) * 100) + "%";
        updateLegend("Vence 1º Tempo", c1, "Empata 1º Tempo", c2, "Perde 1º Tempo", c3);

    } else if (currentStatMode === 'ODDS') {
        // --- MODO: AMBAS MARCAM (BTTS) ---
        mainLabel = "Ambas Marcam (Sim)";
        filteredData.forEach(g => {
            if (g.gh > 0 && g.ga > 0) c1++; // Sim
            else c2++;                      // Não
        });
        labels = ['Ambas Sim', 'Ambas Não'];
        dataValues = [c1, c2];
        colors = ['#8b5cf6', '#333333']; // Roxo, Cinza
        mainValue = Math.round((c1 / total) * 100) + "%";
        updateLegend("BTTS Sim", c1, "BTTS Não", c2, "", null);
    }

    // 5. Atualiza o Número Grande e Título na Sidebar
    const bigNum = document.getElementById('statBigNumber');
    const lblStat = document.getElementById('statLabel');
    if(bigNum) { bigNum.innerText = mainValue; bigNum.style.color = colors[0]; }
    if(lblStat) { lblStat.innerText = mainLabel; }

    // 6. Registra o Plugin de DataLabels se necessário
    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    // 7. Cria o Gráfico Chart.js com DataLabels
    chartStats = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: dataValues,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#1a1a1a', // Cor do fundo para "separar" as fatias
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%', // Rosca um pouco mais grossa para caber os números
            layout: { padding: 10 },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.9)',
                    titleColor: '#fff',
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            let val = context.raw;
                            let perc = Math.round((val / total) * 100) + "%";
                            return ` ${context.label}: ${val} (${perc})`;
                        }
                    }
                },
                // CONFIGURAÇÃO DOS DADOS DENTRO DO GRÁFICO
                datalabels: {
                    color: '#ffffff',
                    font: {
                        weight: '900',
                        size: 12
                    },
                    formatter: (value, ctx) => {
                        if(total === 0) return "";
                        let percentage = Math.round((value / total) * 100);
                        // Só mostra se for maior que 5% para não encavalar
                        if(percentage < 5) return "";
                        return percentage + "%";
                    },
                    anchor: 'center',
                    align: 'center',
                    offset: 0,
                    textShadowBlur: 4,
                    textShadowColor: 'rgba(0,0,0,0.5)'
                }
            }
        }
    });
}


// Helper para atualizar a caixinha lateral do gráfico
function updateLegend(l1, v1, l2, v2, l3, v3) {
    document.getElementById('lbl1').innerText = l1;
    document.getElementById('val1').innerText = v1;
    document.querySelector('.dot-g').style.backgroundColor = (currentStatMode == 'GOLS' ? '#f97316' : (currentStatMode == 'ODDS' ? '#8b5cf6' : '#10b981')); // Ajusta cor da bolinha

    if(l2) {
        document.getElementById('lbl2').parentNode.style.display = 'flex';
        document.getElementById('lbl2').innerText = l2;
        document.getElementById('val2').innerText = v2;
    } else {
        document.getElementById('lbl2').parentNode.style.display = 'none';
    }

    if(l3) {
        document.getElementById('lbl3').parentNode.style.display = 'flex';
        document.getElementById('lbl3').innerText = l3;
        document.getElementById('val3').innerText = v3;
    } else {
        document.getElementById('lbl3').parentNode.style.display = 'none'; // Esconde o 3º item se não usar
    }
}    function updateHistoryAI() { document.getElementById('aiText').innerHTML = "Base carregada: " + filteredData.length + " jogos."; }
    function renderTopCardsData() { let h = 0, d = 0, a = 0; filteredData.forEach(g => { if(g.gh > g.ga) h++; else if(g.ga > g.gh) a++; else d++; }); const t = filteredData.length; document.getElementById('kpiTotal').innerText = t; document.getElementById('kpiHome').innerText = Math.round((h / t) * 100) + "%"; document.getElementById('kpiDraw').innerText = Math.round((d / t) * 100) + "%"; document.getElementById('kpiAway').innerText = Math.round((a / t) * 100) + "%"; }
    function filterFutureTable() { const txt = document.getElementById('futureSearchTeam').value.toLowerCase(); document.querySelectorAll('.future-game-row').forEach(r => { r.style.display = r.getAttribute('data-teams').includes(txt) ? '' : 'none'; }); }
    
    // Funções de Backtest e Estratégia (Mantidas vazias para compatibilidade)
    function calculateStrategy(){}
    function runBacktest(){}
    function filterBacktest(t, b){}
    
    function toggleSection(id, btn) { const e = document.getElementById(id); if(e.style.display === 'none') { e.style.display = 'block'; btn.innerText = '[-]'; } else { e.style.display = 'none'; btn.innerText = '[+]'; } }
    
    function switchTab(t) { 
        // Remove a classe 'active' de todas as seções e botões
        document.querySelectorAll('.view-section').forEach(e => e.classList.remove('active')); 
        document.querySelectorAll('.nav-btn').forEach(e => e.classList.remove('active'));
        
        // Ativa a aba correta
        if(t === 'history') { 
            document.getElementById('view-history').classList.add('active'); 
            document.querySelectorAll('.nav-btn')[0].classList.add('active'); 
        } 
        else if(t === 'backtest') { 
            document.getElementById('view-backtest').classList.add('active'); 
            document.querySelectorAll('.nav-btn')[1].classList.add('active'); 
        } 
        else if(t === 'future') { 
            document.getElementById('view-future').classList.add('active'); 
            document.querySelectorAll('.nav-btn')[2].classList.add('active'); 
        } 
        else if(t === 'opportunities') { 
            document.getElementById('view-opportunities').classList.add('active'); 
            document.querySelectorAll('.nav-btn')[3].classList.add('active'); 
        }
        else if(t === 'finished') { 
            document.getElementById('view-finished').classList.add('active'); 
            document.querySelectorAll('.nav-btn')[4].classList.add('active'); 
        }
    }

    // ==========================================================================
    // 🧪 LÓGICA DO BACKTEST (SIMULADOR DE ESTRATÉGIA)
    // ==========================================================================
    function runBacktest() {
        // 1. Captura inputs da tela
        const market = document.getElementById('btMarket').value;
        const league = document.getElementById('btLeague').value;
        // Se os campos de odd estiverem vazios, considera intervalo infinito
        const minOdd = parseFloat(document.getElementById('btMinOdd').value) || 1.00;
        const maxOdd = parseFloat(document.getElementById('btMaxOdd').value) || 100.00;
        const stake = 100; // Simulação com mão fixa de R$ 100

        const container = document.getElementById('backtestResult');
        // Loading simples
        container.innerHTML = '<div class="spinner" style="width:30px;height:30px;margin:20px auto;"></div><div style="text-align:center;font-size:10px;color:#666;">Processando histórico...</div>';

        // Pequeno delay para o navegador renderizar o loading
        setTimeout(() => {
            let stats = { total: 0, green: 0, red: 0, profit: 0, invested: 0 };
            let tableRows = "";
            
            // Só podemos calcular Lucro Financeiro (R$) se tivermos a Odd no banco.
            // No seu banco atual temos: odd_casa, odd_empate, odd_fora.
            // Para Gols e HT, calcularemos apenas a Taxa de Acerto (Win Rate).
            let isProfitPossible = ['HOME', 'DRAW', 'AWAY'].includes(market);

            // 2. Filtra e Processa os Jogos
            // Usamos allGames (que já contém todo o histórico do SQL carregado no início)
            const results = allGames.filter(g => {
                // Filtro de Liga
                if (league && g.c !== league) return false;

                // Filtro de Odd (Apenas se for mercado 1x2)
                let checkOdd = 0;
                if (market === 'HOME') checkOdd = parseFloat(g.oh);
                else if (market === 'DRAW') checkOdd = parseFloat(g.od);
                else if (market === 'AWAY') checkOdd = parseFloat(g.oa);

                // Se estamos olhando lucro, respeitamos o filtro de Odd
                if (isProfitPossible) {
                    if (checkOdd < minOdd || checkOdd > maxOdd) return false;
                }

                return true;
            });

            // 3. Simula as Apostas uma por uma
            results.forEach(g => {
                let isGreen = false;
                let oddUsed = 0;
                let gh = parseInt(g.gh); // Gols Casa
                let ga = parseInt(g.ga); // Gols Fora
                // HT Goals (tratamento de nulo)
                let hth = g.hth !== null ? parseInt(g.hth) : -1;
                let hta = g.hta !== null ? parseInt(g.hta) : -1;

                // --- LÓGICA DE GREEN/RED ---
                switch(market) {
                    case 'HOME':
                        if (gh > ga) isGreen = true;
                        oddUsed = parseFloat(g.oh);
                        break;
                    case 'DRAW':
                        if (gh === ga) isGreen = true;
                        oddUsed = parseFloat(g.od);
                        break;
                    case 'AWAY':
                        if (ga > gh) isGreen = true;
                        oddUsed = parseFloat(g.oa);
                        break;
                    case 'OVER15':
                        if ((gh + ga) > 1.5) isGreen = true;
                        break;
                    case 'OVER25':
                        if ((gh + ga) > 2.5) isGreen = true;
                        break;
                    case 'UNDER25':
                        if ((gh + ga) < 2.5) isGreen = true;
                        break;
                    case 'BTTS': // Ambas Marcam
                        if (gh > 0 && ga > 0) isGreen = true;
                        break;
                    case 'HT_HOME': // Casa vence no intervalo
                        if (hth > hta) isGreen = true;
                        break;
                    case 'HT_DRAW': // Empate no intervalo
                        if (hth === hta) isGreen = true;
                        break;
                    case 'HT_AWAY': // Fora vence no intervalo
                        if (hta > hth) isGreen = true;
                        break;
                }

                // --- CONTABILIDADE ---
                stats.total++;
                stats.invested += stake;

                let rowClass = "row-loss";
                let pnl = -stake; // Assume prejuízo inicial

                if (isGreen) {
                    stats.green++;
                    rowClass = "row-win";
                    if (isProfitPossible && oddUsed > 1) {
                        pnl = (stake * oddUsed) - stake; // Lucro líquido
                    } else {
                        pnl = 0; // Se não tem odd, consideramos "neutro" financeiramente para não distorcer
                    }
                } else {
                    stats.red++;
                }

                if (isProfitPossible) stats.profit += pnl;

                // Prepara visual da linha
                let oddDisplay = isProfitPossible ? oddUsed.toFixed(2) : "-";
                // Se for mercado de gols, mostra só check ou xis. Se for 1x2, mostra o valor R$.
                let profitDisplay = isProfitPossible ? 
                    (pnl > 0 ? `<span style="color:#10b981">+R$${pnl.toFixed(2)}</span>` : `<span style="color:#ef4444">-R$${Math.abs(pnl).toFixed(2)}</span>`) : 
                    (isGreen ? "✅" : "❌");

                tableRows += `
                    <tr class="${rowClass}">
                        <td>${g.dr}</td>
                        <td>
                            <div style="font-weight:bold; color:#ddd;">${g.h}</div>
                            <div style="font-size:10px; color:#888;">vs ${g.a}</div>
                        </td>
                        <td style="text-align:center; font-weight:bold; font-size:14px; letter-spacing:1px;">${gh}-${ga}</td>
                        <td style="text-align:center; color:#aaa;">${oddDisplay}</td>
                        <td style="text-align:center; font-weight:bold;">${profitDisplay}</td>
                    </tr>
                `;
            });

            // 4. Calcula Totais Finais
            let winRate = stats.total > 0 ? ((stats.green / stats.total) * 100).toFixed(1) : 0;
            let roi = stats.invested > 0 ? ((stats.profit / stats.invested) * 100).toFixed(1) : 0;
            let profitColor = stats.profit >= 0 ? "#10b981" : "#ef4444";
            
            // Se o mercado não for 1x2, mostramos aviso e zeramos o lucro visual para não confundir
            let displayProfit = isProfitPossible ? `R$ ${stats.profit.toFixed(2)}` : "N/A*";
            let displayROI = isProfitPossible ? `${roi}%` : "-";
            let warning = !isProfitPossible ? '<div style="font-size:10px; color:#f59e0b; margin-bottom:15px; padding:10px; border:1px solid #f59e0b; border-radius:6px; background:rgba(245, 158, 11, 0.1);">⚠️ <b>Aviso:</b> Para mercados de Gols/HT, o sistema calcula apenas a <b>Taxa de Acerto</b>, pois o histórico CSV contém apenas odds de Match Odds (1x2).</div>' : '';

            // Monta o HTML do Resultado
            let html = `
                ${warning}
                <div class="kpi-row">
                    <div class="kpi-box">
                        <div class="kpi-lbl">JOGOS FILTRADOS</div>
                        <div class="kpi-val">${stats.total}</div>
                    </div>
                    <div class="kpi-box" style="border-left-color:${profitColor};">
                        <div class="kpi-lbl">LUCRO (Stake R$100)</div>
                        <div class="kpi-val" style="color:${profitColor}">${displayProfit}</div>
                    </div>
                    <div class="kpi-box">
                        <div class="kpi-lbl">ROI (RETORNO)</div>
                        <div class="kpi-val">${displayROI}</div>
                    </div>
                    <div class="kpi-box" style="border-left-color:var(--accent-blue);">
                        <div class="kpi-lbl">TAXA DE ACERTO</div>
                        <div class="kpi-val">${winRate}%</div>
                    </div>
                </div>
                
                <div class="table-responsive" style="max-height:500px; overflow-y:auto; border:1px solid #333; margin-top:15px;">
                    <table class="backtest-table">
                        <thead style="position:sticky; top:0; z-index:5;">
                            <tr>
                                <th>DATA</th>
                                <th>CONFRONTO</th>
                                <th style="text-align:center;">PLACAR</th>
                                <th style="text-align:center;">ODD</th>
                                <th style="text-align:center;">RESULTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tableRows}
                        </tbody>
                    </table>
                </div>
            `;

            if(stats.total === 0) html = '<div style="padding:40px; text-align:center; color:#666;">⛔ Nenhum jogo encontrado com esses critérios no histórico.</div>';
            
            container.innerHTML = html;
            
            // Mostra os botões de filtro visual (Todos/Greens/Reds)
            const filterBar = document.getElementById('btFilters');
            if(filterBar) filterBar.style.display = 'flex';
            
        }, 300); // Fim do setTimeout
    }

    // Filtro visual da tabela de resultados (Botões acima da tabela)
    function filterBacktest(type, btn) {
        let rows = document.querySelectorAll('.backtest-table tbody tr');
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        if(btn) btn.classList.add('active');

        rows.forEach(r => {
            if(type === 'all') r.style.display = '';
            else if(type === 'win' && r.classList.contains('row-win')) r.style.display = '';
            else if(type === 'loss' && r.classList.contains('row-loss')) r.style.display = '';
            else r.style.display = 'none';
        });
    }

</script>
</body>
</html>