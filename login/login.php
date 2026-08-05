<?php
// Inicia a sessão diretamente aqui para gerenciar o login
session_start();

// Se o usuário já estiver logado, joga ele direto para o index
if (isset($_SESSION["usuario_id"])) {
    header("Location: ../index.php"); 
    exit;
}

// Carrega apenas o banco de dados
require_once __DIR__ . "/../config/db.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim(strtolower($_POST["email"]));
    $senha = $_POST["senha"];
    
    // 1. Busca primeiro na tabela correta de adotantes
    $sql = "SELECT id, nome_completo AS nome, senha, 'adotante' AS tipo FROM adotantes WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 2. Se não encontrar nos adotantes, busca na tabela de ongs
    if (!$usuario) {
        $sql = "SELECT id, nome_instituicao AS nome, senha, 'ong' AS tipo FROM ongs WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Verifica se o usuário existe em alguma das tabelas e se a senha está correta
    if ($usuario && password_verify($senha, $usuario["senha"])) {
        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nome"] = $usuario["nome"];
        $_SESSION["tipo_usuario"] = $usuario["tipo"];
        
        // Mantém as variáveis de permissão que você usava, definidas dinamicamente
        $_SESSION["pode_cadastrar"] = ($usuario["tipo"] === 'ong') ? 1 : 0;
        $_SESSION["pode_editar"] = 0;
        $_SESSION["pode_excluir"] = 0;
        
        // Redireciona de volta para a index dentro de teste
        header("Location: ../index.php");
        exit;
    } else {
        $erro = "E-mail ou senha inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdotaPet - Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <img src="img/Captura de tela 2026-05-13 110314.png" alt="AdotaPet Logo" class="logo-destaque" />

        <h2 id="tela-titulo">Login</h2>
        <p id="tela-subtitulo">Insira suas credenciais para acessar o sistema.</p>

        <?php if (!empty($erro)): ?>
            <p class="erro-mensagem" style="color: #b91c1c; background-color: #fee2e2; padding: 10px; border-radius: 8px; font-weight: bold;">
                <?php echo $erro; ?>
            </p>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <input type="email" name="email" placeholder="E-mail" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="senha" placeholder="Senha" required>
            </div>
            
            <button type="submit" class="btn-login">Entrar</button>
        </form>

        <p style="margin-top: 15px">
            <span>Não tem uma conta?</span>
            <a href="cadastrar.php" class="alternar-link">Criar Conta</a>
        </p>
    </div>
</body>
</html>