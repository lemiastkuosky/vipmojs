<?php
require 'includes/auth.php';
require 'includes/db.php';

// Busca usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$user = $stmt->fetch();

if (!$user) { session_destroy(); header("Location: index.php"); exit; }

// Gera uma notícia aleatória para o rodapé
$noticias = [
    "Aperto policial: Blitz ocorrendo na Avenida Principal...",
    "Mercado em alta: Preço das Ferraris subiu 15% hoje.",
    "Novo recorde: Jogador " . $user['nome'] . " acabou de entrar na cidade!",
    "Clima tenso: Gangues disputam territórios na Zona Norte."
];
$noticia_atual = $noticias[array_rand($noticias)];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crime Life - Street Trunfo</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="layout-wrapper">
    
    <div class="map-overlay"></div>

    <header class="top-bar">
        
        <div class="logo-area">
            <div class="logo-img">CRIME<span>LIFE</span></div>
        </div>

        <div class="hud-stats">
            <div class="hud-item" title="Horário do Servidor">
                <i class="fa-regular fa-clock hud-icon"></i> 
                <span><?= date('H:i') ?></span>
            </div>
            <div class="hud-item" title="Jogadores Online">
                <i class="fa-solid fa-users hud-icon"></i> 
                <span>31 / 60</span>
            </div>
            <div class="hud-item" title="Clima">
                <i class="fa-solid fa-sun hud-icon" style="color:#ff9800"></i> 
                <span>28°C Limpo</span>
            </div>
        </div>

        <div class="user-controls">
            
            <div class="player-status">
                <div class="p-money">R$ <?= number_format($user['dinheiro'], 2, ',', '.') ?></div>
                <div class="p-level">Nível <?= $user['nivel'] ?> • XP <?= $user['xp'] ?></div>
            </div>

            <?php if ($user['is_admin']): ?>
                <a href="admin.php" class="circle-btn admin" title="Admin Panel"><i class="fa-solid fa-gear"></i></a>
            <?php endif; ?>
            
            <a href="perfil.php" class="circle-btn" title="Perfil"><i class="fa-regular fa-user"></i></a>
            <a href="includes/logout.php" class="circle-btn" title="Sair"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
        </div>
    </header>


    <div class="game-area">
        
        <a href="game.php" class="map-pin pin-race" style="top: 50%; left: 50%;" data-label="Rachas de Rua">
            <i class="fa-solid fa-flag-checkered"></i>
        </a>

        <a href="shop.php" class="map-pin pin-shop" style="top: 30%; left: 20%;" data-label="Concessionária">
            <i class="fa-solid fa-cart-shopping"></i>
        </a>

        <a href="inventario.php" class="map-pin pin-garage" style="top: 70%; left: 75%;" data-label="Minha Garagem">
            <i class="fa-solid fa-car-side"></i>
        </a>

        <a href="mercado.php" class="map-pin pin-market" style="top: 35%; left: 65%;" data-label="Mercado Negro">
            <i class="fa-solid fa-handshake"></i>
        </a>

        <div class="map-pin" style="top: 65%; left: 25%; color:#aaa; border-color:#555;" data-label="Oficina (Em Breve)">
            <i class="fa-solid fa-wrench"></i>
        </div>

    </div>


    <div class="breaking-news-bar">
        <div class="bn-label">BREAKING NEWS</div>
        <div class="bn-ticker">
            <span>
                <b>URGENTE:</b> <?= $noticia_atual ?> &nbsp;&nbsp;&nbsp; /// &nbsp;&nbsp;&nbsp; 
                <b>SISTEMA:</b> Servidor operando com estabilidade máxima. &nbsp;&nbsp;&nbsp; /// &nbsp;&nbsp;&nbsp;
                <b>DICA:</b> Compre carros na concessionária para subir de nível!
            </span>
        </div>
    </div>

</div>

</body>
</html>