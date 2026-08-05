<?php
session_start();
require_once "../config/db.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim(strtolower($_POST["email"]));
    $senha = $_POST["senha"];
    $telefone = trim($_POST["telefone"]);
    $tipo_conta = $_POST["tipo_conta"];
    
    // Verifica se o e-mail já existe na tabela de adotantes
    $sqlCheckAdotante = "SELECT id FROM adotantes WHERE email = ?";
    $stmtCheckAdotante = $pdo->prepare($sqlCheckAdotante);
    $stmtCheckAdotante->execute([$email]);
    
    // Verifica se o e-mail já existe na tabela de ongs
    $sqlCheckOng = "SELECT id FROM ongs WHERE email = ?";
    $stmtCheckOng = $pdo->prepare($sqlCheckOng);
    $stmtCheckOng->execute([$email]);
    
    if ($stmtCheckAdotante->fetch() || $stmtCheckOng->fetch()) {
        $erro = "Este e-mail já está registrado!";
    } else {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $sucesso = false;
        
        // Define permissão padrão com base na escolha
        $pode_cadastrar = ($tipo_conta === 'ong') ? 1 : 0;
        $pode_editar = 0;
        $pode_excluir = 0;
        
        // Insere os dados na tabela correspondente ao tipo selecionado no formulário
        if ($tipo_conta === 'ong') {
            $sqlInsert = "INSERT INTO ongs (nome_instituicao, email, senha, telefone) VALUES (?, ?, ?, ?)";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $sucesso = $stmtInsert->execute([$nome, $email, $senhaHash, $telefone]);
        } else {
            $sqlInsert = "INSERT INTO adotantes (nome_completo, email, senha, telefone) VALUES (?, ?, ?, ?)";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $sucesso = $stmtInsert->execute([$nome, $email, $senhaHash, $telefone]);
        }
        
        if ($sucesso) {
            $_SESSION["usuario_id"] = $pdo->lastInsertId();
            $_SESSION["usuario_nome"] = $nome;
            $_SESSION["tipo_usuario"] = $tipo_conta;
            $_SESSION["pode_cadastrar"] = $pode_cadastrar;
            $_SESSION["pode_editar"] = $pode_editar;
            $_SESSION["pode_excluir"] = $pode_excluir;
            
            // Redireciona corretamente para a index principal
            header("Location: ../index.php");
            exit;
        } else {
            $erro = "Ocorreu um erro ao criar a conta.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdotaPet - Criar Conta</title>
    
    <link rel="stylesheet" href="cadastrar.css">
</head>
<body>

    <div class="cadastro-box">
        <div class="avatar-placeholder"></div>

        <h2>Criar Nova Conta</h2>
        <p class="subtitulo">Preencha todas as suas informações abaixo.</p>

        <?php if (!empty($erro)): ?>
            <div class="erro-mensagem"><?php echo $erro; ?></div>
        <?php endif; ?>

        <form method="post">
            
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" placeholder="exemplo@email.com" required>
            </div>

            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="Digite sua senha" required>
            </div>

            <div class="seletor-container">
                <div id="btn-adotante" class="btn-opcao selecionado" onclick="mudarTipo('adotante')">ADOTANTE</div>
                <div id="btn-ong" class="btn-opcao" onclick="mudarTipo('ong')">ONG</div>
            </div>
            
            <input type="hidden" name="tipo_conta" id="tipo_conta" value="adotante">

            <div class="form-group">
                <label id="label-nome">Nome Completo</label>
                <input type="text" name="nome" id="input-nome" placeholder="Seu nome aqui" required>
            </div>

            <div class="form-group">
                <label>Telefone / WhatsApp</label>
                <input type="text" name="telefone" placeholder="(00) 00000-0000" required>
            </div>

            <button type="submit" class="btn-submit">Criar Minha Conta</button>
        </form>

        <div class="footer-text">
            Já possui uma conta? <a href="login.php">Fazer Login</a>
        </div>
    </div>

    <script>
        function mudarTipo(tipo) {
            document.getElementById('tipo_conta').value = tipo;
            
            const btnAdotante = document.getElementById('btn-adotante');
            const btnOng = document.getElementById('btn-ong');
            const labelNome = document.getElementById('label-nome');
            const inputNome = document.getElementById('input-nome');

            if (tipo === 'adotante') {
                btnAdotante.classList.add('selecionado');
                btnOng.classList.remove('selecionado');
                
                labelNome.innerText = 'Nome Completo';
                inputNome.placeholder = 'Seu nome aqui';
            } else {
                btnOng.classList.add('selecionado');
                btnAdotante.classList.remove('selecionado');
                
                labelNome.innerText = 'Nome da ONG';
                inputNome.placeholder = 'Nome do Abrigo';
            }
        }
    </script>
</body>
</html>