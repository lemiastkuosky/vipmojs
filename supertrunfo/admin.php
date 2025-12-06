<?php
require 'includes/auth.php';
require 'includes/db.php';

// Segurança: Verifica se é o admin
$stmt = $pdo->prepare("SELECT email FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$user = $stmt->fetch();

if ($user['email'] !== 'le.miastkuosky@live.com') {
    header("Location: dashboard.php");
    exit;
}

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = "INSERT INTO cartas (nome, imagem, velocidade, potencia, aceleracao, peso, raridade, preco_loja) 
                VALUES (:nome, :img, :vel, :pot, :acc, :peso, :rar, :preco)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome' => $_POST['nome'], 'img' => $_POST['imagem'], 'vel' => $_POST['velocidade'], 
            'pot' => $_POST['potencia'], 'acc' => $_POST['aceleracao'], 'peso' => $_POST['peso'], 
            'rar' => $_POST['raridade'], 'preco' => $_POST['preco']
        ]);
        $msg = "<p style='color:#5cb85c'>Carta adicionada com sucesso!</p>";
    } catch (Exception $e) {
        $msg = "<p style='color:#d9534f'>Erro: " . $e->getMessage() . "</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="header">
        <div>Painel Admin</div>
        <a href="dashboard.php">Voltar</a>
    </div>

    <div class="admin-container">
        <h2>Adicionar Nova Carta</h2>
        <?= $msg ?>
        <form method="POST">
            <label>Nome:</label>
            <input type="text" name="nome" required>

            <label>Imagem URL:</label>
            <input type="text" name="imagem" required placeholder="https://...">

            <div style="display:flex; gap:10px;">
                <input type="number" name="velocidade" placeholder="Velocidade (km/h)" required>
                <input type="number" name="potencia" placeholder="Potência (cv)" required>
            </div>
            <div style="display:flex; gap:10px;">
                <input type="number" step="0.1" name="aceleracao" placeholder="0-100 (s)" required>
                <input type="number" name="peso" placeholder="Peso (kg)" required>
            </div>

            <label>Raridade:</label>
            <select name="raridade">
                <option value="comum">Comum</option>
                <option value="rara">Rara</option>
                <option value="lendaria">Lendária</option>
            </select>

            <label>Preço Loja:</label>
            <input type="number" step="0.01" name="preco" value="100.00">

            <button type="submit">SALVAR CARTA</button>
        </form>
    </div>
</body>
</html>