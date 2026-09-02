<?php
// Inclui autenticação e conexão com o banco de dados
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mensagem_sucesso = null;
$mensagem_erro = null;

// Captura o ID do usuário logado na sessão
$usuario_id = $_SESSION['adotante_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['ong_id'] ?? $_SESSION['id'] ?? null;
$tipo_usuario = $_SESSION['tipo_usuario'] ?? ($_SESSION['ong_id'] ?? null ? 'ong' : 'adotante');

if (!$usuario_id) {
    header("Location: login.php");
    exit;
}

// 1. BUSCA OS DADOS ATUAIS DO USUÁRIO NO BANCO DE DADOS
$usuario = null;
$e_ong = ($tipo_usuario === 'ong');

if ($e_ong) {
    $stmt = $pdo->prepare("SELECT * FROM ongs WHERE id = :id");
    $stmt->execute(['id' => $usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT * FROM adotantes WHERE id = :id");
    $stmt->execute(['id' => $usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fallback: se não encontrou em adotantes, tenta buscar em ongs
if (!$usuario && !$e_ong) {
    $stmt = $pdo->prepare("SELECT * FROM ongs WHERE id = :id");
    $stmt->execute(['id' => $usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario) {
        $e_ong = true;
    }
}

if (!$usuario) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// 2. PROCESSA A EXCLUSÃO DO PERFIL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deletar_perfil') {
    try {
        if ($e_ong) {
            $stmtDelete = $pdo->prepare("DELETE FROM ongs WHERE id = :id");
        } else {
            $stmtDelete = $pdo->prepare("DELETE FROM adotantes WHERE id = :id");
        }
        $stmtDelete->execute(['id' => $usuario['id']]);

        // Destrói a sessão e redireciona
        session_unset();
        session_destroy();
        header("Location: login.php?msg=conta_excluida");
        exit;
    } catch (PDOException $e) {
        $mensagem_erro = "Erro ao excluir perfil: " . $e->getMessage();
    }
}

// 3. PROCESSA A ATUALIZAÇÃO DO PERFIL (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $nome = trim($_POST['nome'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $telefone = trim($_POST['telefone'] ?? '');
    $biografia = trim($_POST['biografia'] ?? '');
    $nova_senha = $_POST['nova_senha'] ?? '';

    if (empty($nome) || empty($email) || empty($telefone)) {
        $mensagem_erro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        try {
            if ($e_ong) {
                // Atualização para ONG
                if (!empty($nova_senha)) {
                    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $stmtUpdate = $pdo->prepare("
                        UPDATE ongs 
                        SET nome_instituicao = :nome, email = :email, telefone = :telefone, senha = :senha 
                        WHERE id = :id
                    ");
                    $stmtUpdate->execute([
                        'nome'     => $nome,
                        'email'    => $email,
                        'telefone' => $telefone,
                        'senha'    => $senha_hash,
                        'id'       => $usuario['id']
                    ]);
                } else {
                    $stmtUpdate = $pdo->prepare("
                        UPDATE ongs 
                        SET nome_instituicao = :nome, email = :email, telefone = :telefone 
                        WHERE id = :id
                    ");
                    $stmtUpdate->execute([
                        'nome'     => $nome,
                        'email'    => $email,
                        'telefone' => $telefone,
                        'id'       => $usuario['id']
                    ]);
                }
            } else {
                // Atualização para ADOTANTE (inclui Biografia)
                if (!empty($nova_senha)) {
                    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $stmtUpdate = $pdo->prepare("
                        UPDATE adotantes 
                        SET nome_completo = :nome, email = :email, telefone = :telefone, biografia = :biografia, senha = :senha 
                        WHERE id = :id
                    ");
                    $stmtUpdate->execute([
                        'nome'      => $nome,
                        'email'     => $email,
                        'telefone'  => $telefone,
                        'biografia' => $biografia,
                        'senha'     => $senha_hash,
                        'id'        => $usuario['id']
                    ]);
                } else {
                    $stmtUpdate = $pdo->prepare("
                        UPDATE adotantes 
                        SET nome_completo = :nome, email = :email, telefone = :telefone, biografia = :biografia 
                        WHERE id = :id
                    ");
                    $stmtUpdate->execute([
                        'nome'      => $nome,
                        'email'     => $email,
                        'telefone'  => $telefone,
                        'biografia' => $biografia,
                        'id'        => $usuario['id']
                    ]);
                }
            }

            $mensagem_sucesso = "Perfil atualizado com sucesso!";

            // Recarrega os dados atualizados
            if ($e_ong) {
                $stmt = $pdo->prepare("SELECT * FROM ongs WHERE id = :id");
            } else {
                $stmt = $pdo->prepare("SELECT * FROM adotantes WHERE id = :id");
            }
            $stmt->execute(['id' => $usuario['id']]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensagem_erro = "Este e-mail já está em uso por outro usuário.";
            } else {
                $mensagem_erro = "Erro ao atualizar perfil: " . $e->getMessage();
            }
        }
    }
}

// Define o nome a ser exibido dependendo da tabela
$nome_exibicao = $e_ong ? $usuario['nome_instituicao'] : $usuario['nome_completo'];
$biografia_valor = $usuario['biografia'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdotaPet - Meu Perfil</title>
    <!-- Versão com parâmetro dinâmico para evitar cache no navegador -->
    <link rel="stylesheet" href="meu_perfil.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- HEADER DA PÁGINA -->
    <header class="main-header">
        <div class="logo">
            <span class="paw-icon">🐾</span>
            <h1>AdotaPet</h1>
        </div>
        <nav class="nav-links">
            <a href="index.php">Início</a>
            <a href="adotar.php">Adotar</a>
            <a href="mapa.php">Mapa</a>
            <a href="candidaturas.php">Candidaturas</a>
            <a href="meu_perfil.php" class="active">MEU PERFIL</a>
            <a href="logout.php" class="btn-sair">Sair</a>
        </nav>
    </header>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="content-wrapper">
        <div class="profile-container">
            <div class="avatar-upload-wrapper">
                <div class="avatar-preview-container">
                    <div id="avatar-preview" class="avatar-preview">👤</div>
                </div>
                <span class="user-type-badge"><?= $e_ong ? 'Perfil ONG' : 'Perfil Adotante' ?></span>
            </div>

            <h2 class="profile-title">Meu Perfil</h2>
            <p class="profile-subtitle">Mantenha seus dados e informações sempre atualizados.</p>

            <!-- MENSAGENS DE FEEDBACK -->
            <?php if ($mensagem_sucesso): ?>
                <div class="alert-message alert-success">
                    ✓ <?= htmlspecialchars($mensagem_sucesso) ?>
                </div>
            <?php endif; ?>

            <?php if ($mensagem_erro): ?>
                <div class="alert-message alert-error">
                    ⚠️ <?= htmlspecialchars($mensagem_erro) ?>
                </div>
            <?php endif; ?>

            <!-- FORMULÁRIO DE EDIÇÃO DE DADOS -->
            <form method="POST" action="meu_perfil.php">
                <div class="form-group">
                    <label for="user-name"><?= $e_ong ? 'Nome da Instituição (ONG)' : 'Nome Completo' ?></label>
                    <input type="text" id="user-name" name="nome" value="<?= htmlspecialchars($nome_exibicao) ?>" required>
                </div>

                <div class="form-group">
                    <label for="user-email">E-mail de Contato</label>
                    <input type="email" id="user-email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="user-phone">Telefone / WhatsApp</label>
                    <input type="tel" id="user-phone" name="telefone" value="<?= htmlspecialchars($usuario['telefone']) ?>" required>
                </div>

                <!-- CAMPO DE BIOGRAFIA DO ADOTANTE -->
                <?php if (!$e_ong): ?>
                    <div class="form-group">
                        <label for="user-bio">Sobre Mim (Biografia)</label>
                        <textarea id="user-bio" name="biografia" placeholder="Conte um pouco sobre sua rotina, sua casa e sua experiência com animais..."><?= htmlspecialchars($biografia_valor) ?></textarea>
                    </div>
                <?php endif; ?>

                <?php if ($e_ong && !empty($usuario['cnpj'])): ?>
                    <div class="form-group">
                        <label>CNPJ (Institucional)</label>
                        <input type="text" value="<?= htmlspecialchars($usuario['cnpj']) ?>" disabled>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="nova_senha">Alterar Senha (Opcional)</label>
                    <input type="password" id="nova_senha" name="nova_senha" placeholder="Deixe em branco para manter a atual">
                </div>

                <button type="submit" class="btn-save-profile">Salvar Alterações</button>
            </form>

            <!-- FORMULÁRIO SEPARADO PARA EXCLUSÃO DE PERFIL -->
            <form method="POST" action="meu_perfil.php" onsubmit="return confirmarExclusao();" style="width: 100%;">
                <input type="hidden" name="action" value="deletar_perfil">
                <button type="submit" class="btn-delete-profile">Excluir Perfil</button>
            </form>
        </div>
    </main>

    <script>
        function confirmarExclusao() {
            return confirm("⚠️ ATENÇÃO: Tem certeza de que deseja excluir seu perfil definitivamente? Esta ação é irreversível e removerá todos os seus dados.");
        }
    </script>
</body>
</html>