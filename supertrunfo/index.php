<?php
session_start();
require 'includes/db.php';

if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['usuario_id'] = $user['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Email ou senha incorretos!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - SuperTrunfo</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="box">
            <h1>🏎️ Login</h1>
            <?php if(isset($erro)) echo "<p style='color:#ff6b6b'>$erro</p>"; ?>
            <form method="POST">
                <input type="email" name="email" placeholder="E-mail" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit">ENTRAR</button>
            </form>
            <br>
            <a href="cadastro.php" style="color: #ccc;">Criar conta nova</a>
        </div>
    </div>
</body>
</html>