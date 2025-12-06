<?php
// setup.php - Cria o banco de dados SQLite
try {
    $db = new PDO('sqlite:fut_pro.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Tabela de Histórico (Jogos Passados)
    $db->exec("CREATE TABLE IF NOT EXISTS history (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        league TEXT,
        date TEXT,
        time TEXT,
        home_team TEXT,
        away_team TEXT,
        fthg INTEGER,
        ftag INTEGER,
        hthg INTEGER,
        htag INTEGER,
        odd_h REAL,
        odd_d REAL,
        odd_a REAL
    )");

    // 2. Tabela de Scanner (Jogos Futuros)
    $db->exec("CREATE TABLE IF NOT EXISTS future (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        league_key TEXT,
        commence_time TEXT,
        home_team TEXT,
        away_team TEXT,
        odd_h REAL,
        odd_d REAL,
        odd_a REAL
    )");

    echo "<h1 style='color:green'>Sucesso! Banco de dados 'fut_pro.db' criado.</h1>";
    echo "<p>Agora você pode deletar este arquivo setup.php e atualizar o index e admin.</p>";

} catch (PDOException $e) {
    echo "<h1 style='color:red'>Erro ao criar banco: " . $e->getMessage() . "</h1>";
}
?>