<?php
require 'includes/db.php';

if (isset($_POST['nome'])) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:n, :e, :s)");
        $stmt->execute(['n' => $nome, 'e' => $email, 's' => $senha]);
        header("Location: index.php?criado=ok");
    } catch (Exception $e) {
        $erro = "Email já utilizado.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cadastro</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="box">
            <h1>📝 Cadastro</h1>
            <?php if(isset($erro)) echo "<p style='color:#ff6b6b'>$erro</p>"; ?>
            <form method="POST">
                <input type="text" name="nome" placeholder="Nome do Jogador" required>
                <input type="email" name="email" placeholder="E-mail" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit" style="background:#007bff">CADASTRAR</button>
            </form>
            <br>
            <a href="index.php" style="color: #ccc;">Voltar para Login</a>
        </div>
    </div>
</body>
</html>