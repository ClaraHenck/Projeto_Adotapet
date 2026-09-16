<?php
// 1. Carrega as configurações primeiro (o auth.php já inicia a sessão)
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';
$stmt = null;


// Garante que a sessão está ativa sem duplicar o session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Busca o ID com verificação segura
$id = $_SESSION['id'] ?? $_SESSION['ong_id'] ?? $_SESSION['usuario_id'] ?? null;

// Redireciona para o login caso a sessão não exista ou não contenha um ID válido
if (!$id) {
    header("Location: login/login.php");
    exit;
}

$mensagem = '';
$erro = '';

// 3. Processar exclusão de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_perfil'])) {
    try {
        $stmtDelete = $pdo->prepare("DELETE FROM ongs WHERE id = ?");
        $stmtDelete->execute([$id]);

        // Encerra e destrói completamente a sessão do usuário
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        header("Location: login/login.php?status=conta_excluida");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao excluir conta: " . $e->getMessage();
    }
}

// 4. Buscar informações atuais da ONG no banco de dados
try {
    $stmt = $pdo->prepare("SELECT * FROM ongs WHERE id = ?");
    $stmt->execute([$id]);
    $ong = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    $erro = "Erro ao carregar dados: " . $e->getMessage();
    $ong = [];
}

// 5. Processar atualização de perfil ao enviar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['excluir_perfil'])) {
    $nome = $_POST['nome'] ?? '';
    $cnpj = $_POST['cnpj'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $responsavel = $_POST['responsavel'] ?? '';
    $capacidade = !empty($_POST['capacidade']) ? (int)$_POST['capacidade'] : null;
    $instagram = $_POST['instagram'] ?? '';
    $descricao = $_POST['descricao'] ?? '';

    // Processamento da Foto / Logo
    $foto_path = $ong['foto'] ?? '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $novo_nome = 'ong_' . $id . '_' . time() . '.' . $extensao;
        $diretorio = 'uploads/';
        
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0777, true);
        }
        
        $destino = $diretorio . $novo_nome;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
            $foto_path = $destino;
        }
    }

    // Atualização dos dados no MySQL
    try {
        $sql = "UPDATE ongs SET 
                    nome = :nome, 
                    cnpj = :cnpj, 
                    telefone = :telefone, 
                    responsavel = :responsavel, 
                    capacidade = :capacidade, 
                    instagram = :instagram, 
                    descricao = :descricao, 
                    foto = :foto 
                WHERE id = :id";

        $stmtUpdate = $pdo->prepare($sql);
        $stmtUpdate->execute([
            ':nome' => $nome,
            ':cnpj' => $cnpj,
            ':telefone' => $telefone,
            ':responsavel' => $responsavel,
            ':capacidade' => $capacidade,
            ':instagram' => $instagram,
            ':descricao' => $descricao,
            ':foto' => $foto_path,
            ':id' => $id
        ]);

        $mensagem = "Alterações salvas com sucesso!";

        // Recarrega os dados atualizados
        $stmt->execute([$id]);
        $ong = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        $erro = "Erro ao salvar alterações: " . $e->getMessage();
    }
}

// 6. Fallbacks para garantir o resgate dos campos cadastrados no login/sessão
$valNome        = $ong['nome'] ?? $ong['nome_ong'] ?? $ong['razao_social'] ?? $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? '';
$valCnpj        = $ong['cnpj'] ?? $_SESSION['cnpj'] ?? '';
$valTelefone    = $ong['telefone'] ?? $ong['whatsapp'] ?? $_SESSION['telefone'] ?? '';
$valResponsavel = $ong['responsavel'] ?? $ong['nome_responsavel'] ?? $ong['contato'] ?? $_SESSION['responsavel'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdotaPet - Minha ONG</title>
    <!-- Importação do CSS da Navbar e da Tela Minha ONG -->
    <link rel="stylesheet" href="navbar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="minha_ong.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- NAVBAR PADRONIZADA -->
    <header class="navbar">
        <a href="index.php" class="logo">🐾 AdotaPet</a>
        <nav class="menu">
            <a href="index.php">Início</a>
            <a href="meus_animais.php">Meus Animais</a>
            <a href="candidaturas_recebidas.php">Candidaturas Recebidas</a>
            <a href="minha_ong.php" class="active">MINHA ONG</a>
            <a href="logout.php" class="btn-logout">Sair</a>
        </nav>
    </header>

    <!-- Container de Conteúdo -->
    <main class="container">
        
        <div class="header-titulo">
            <h1>Perfil da ONG</h1>
            <p>Gerencie as informações cadastrais e de exibição da sua instituição</p>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div class="alerta-sucesso"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div class="alerta-erro"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <form action="minha_ong.php" method="POST" enctype="multipart/form-data">
            
            <!-- Bloco 1: Foto / Logo -->
            <div class="secao-bloco">
                <h2>Logo / Foto do Abrigo</h2>
                <div class="foto-container">
                    <img id="preview-foto" src="<?php echo !empty($ong['foto']) ? htmlspecialchars($ong['foto']) : 'https://via.placeholder.com/90'; ?>" alt="Logo da ONG" class="preview-foto">
                    <div>
                        <label for="input-foto" class="btn-file">Escolher Imagem</label>
                        <input type="file" id="input-foto" name="foto" accept="image/*" style="display: none;" onchange="previewImagem(event)">
                        <p style="font-size: 12px; color: #888; margin-top: 6px; margin-bottom: 0;">Formatos: JPG, PNG ou WEBP.</p>
                    </div>
                </div>
            </div>

            <!-- Bloco 2: Dados do Abrigo -->
            <div class="secao-bloco">
                <h2>Informações da ONG</h2>
                <div class="form-grid">
                    <div class="campo-grupo">
                        <label for="nome">Nome da ONG / Abrigo</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($valNome); ?>" required placeholder="Ex: Instituto Patinhas Felizes">
                    </div>

                    <div class="campo-grupo">
                        <label for="cnpj">CNPJ</label>
                        <input type="text" id="cnpj" name="cnpj" value="<?php echo htmlspecialchars($valCnpj); ?>" placeholder="00.000.000/0001-00">
                    </div>

                    <div class="campo-grupo">
                        <label for="telefone">Telefone / WhatsApp Comercial</label>
                        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($valTelefone); ?>" required placeholder="(00) 00000-0000">
                    </div>

                    <div class="campo-grupo">
                        <label for="responsavel">Nome do Responsável</label>
                        <input type="text" id="responsavel" name="responsavel" value="<?php echo htmlspecialchars($valResponsavel); ?>" required placeholder="Quem gerencia a conta">
                    </div>
                </div>
            </div>

            <!-- Bloco 3: Informações Adicionais -->
            <div class="secao-bloco">
                <h2>Informações Adicionais</h2>
                <div class="form-grid">
                    <div class="campo-grupo">
                        <label for="capacidade">Capacidade Máxima de Abrigados</label>
                        <input type="number" id="capacidade" name="capacidade" value="<?php echo htmlspecialchars($ong['capacidade'] ?? ''); ?>" placeholder="Ex: 50" min="1">
                    </div>

                    <div class="campo-grupo">
                        <label for="instagram">Link do Instagram</label>
                        <input type="url" id="instagram" name="instagram" value="<?php echo htmlspecialchars($ong['instagram'] ?? ''); ?>" placeholder="https://instagram.com/suaong">
                    </div>

                    <div class="campo-grupo full-width">
                        <label for="descricao">História ou Descrição do Trabalho</label>
                        <textarea id="descricao" name="descricao" rows="4" placeholder="Conte resumidamente sobre o abrigo e sua missão..."><?php echo htmlspecialchars($ong['descricao'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Botão Salvar -->
            <button type="submit" class="btn-salvar">Salvar Alterações</button>

        </form>

        <!-- Bloco 4: Zona de Perigo / Exclusão de Perfil -->
        <div class="secao-perigo">
            <h2>Zona de Perigo</h2>
            <p>Ao excluir a conta, todas as informações cadastrais e dados vinculados a esta ONG serão permanentemente removidos. Esta ação não pode ser desfeita.</p>
            <form action="minha_ong.php" method="POST" onsubmit="return confirm('Tem certeza absoluta de que deseja excluir o perfil da ONG? Todos os dados serão perdidos definitivamente.');">
                <button type="submit" name="excluir_perfil" value="1" class="btn-excluir">Excluir Perfil da ONG</button>
            </form>
        </div>

    </main>

    <script>
        function previewImagem(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-foto').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
<<<<<<< HEAD
<<<<<<< HEAD
</html>
=======
</html>

<!-- 
update -->
>>>>>>> 0b442a682232944b80db869d3172fc1c9bc1391c
=======
</html>
>>>>>>> 7e9d4b5c8a493b810594995d5509ac45ef3da631
