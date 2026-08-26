<?php
// 1. Carrega as configurações primeiro (o auth.php já inicia a sessão)
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';

// Garante que a sessão está ativa sem duplicar o session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Busca o ID com verificação segura contra 'Undefined array key'
$id = $_SESSION['id'] ?? $_SESSION['ong_id'] ?? $_SESSION['usuario_id'] ?? null;

// Redireciona para o login caso a sessão não exista ou não contenha um ID válido
if (!$id) {
    header("Location: login/login.php");
    exit;
}

$mensagem = '';
$erro = '';

// Buscar informações atuais da ONG
try {
    $stmt = $pdo->prepare("SELECT * FROM ongs WHERE id = ?");
    $stmt->execute([$id]);
    $ong = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erro = "Erro ao carregar dados: " . $e->getMessage();
}

// Processar atualização de perfil ao enviar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $ong = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $erro = "Erro ao salvar alterações: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdotaPet - Minha ONG</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fafbfc;
            color: #333333;
        }

        /* Barra de Navegação */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background-color: #ffffff;
            border-bottom: 1px solid #f0f0f0;
        }

        .logo {
            font-weight: bold;
            font-size: 18px;
        }

        .menu a {
            text-decoration: none;
            color: #666;
            margin-left: 20px;
            font-size: 14px;
        }

        .menu a.active {
            color: #00b4d8;
            font-weight: bold;
        }

        /* Container de Conteúdo */
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header-titulo h1 {
            font-size: 32px;
            margin: 0 0 5px 0;
        }

        .header-titulo p {
            color: #777;
            margin: 0 0 30px 0;
            font-size: 15px;
        }

        /* BLOCOS EM QUADROS BRANCOS */
        .secao-bloco {
            background-color: #ffffff;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            border: 1px solid #f0f0f0;
        }

        .secao-bloco h2 {
            font-size: 15px;
            color: #444;
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Grid do Formulário */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .full-width {
            grid-column: span 2;
        }

        .campo-grupo {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .campo-grupo label {
            font-size: 13px;
            color: #666;
            font-weight: 500;
        }

        /* Inputs e Textarea */
        .campo-grupo input[type="text"],
        .campo-grupo input[type="number"],
        .campo-grupo input[type="url"],
        .campo-grupo select,
        .campo-grupo textarea {
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            background-color: #ffffff;
            color: #333;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s;
            font-family: inherit;
        }

        .campo-grupo input:focus,
        .campo-grupo select:focus,
        .campo-grupo textarea:focus {
            border-color: #00b4d8;
        }

        /* Área de Preview e Foto */
        .foto-container {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .preview-foto {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            background-color: #f1f5f9;
        }

        .btn-file {
            display: inline-block;
            background-color: #f1f5f9;
            color: #475569;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            transition: background 0.2s;
        }

        .btn-file:hover {
            background-color: #e2e8f0;
        }

        /* Alertas de Retorno */
        .alerta-sucesso {
            background-color: #d1fae5;
            color: #065f46;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alerta-erro {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        /* Botão de Envio */
        .btn-salvar {
            background-color: #00b4d8;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s;
        }

        .btn-salvar:hover {
            background-color: #0096b4;
        }

        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .full-width {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>

    <!-- Barra de Navegação -->
    <header class="navbar">
        <div class="logo">🐾 AdotaPet</div>
        <nav class="menu">
            <a href="adotar.php">Início</a>
            <a href="candidaturas.php">Candidaturas Recebidas</a>
            <a href="minha_ong.php" class="active">MINHA ONG</a>
            <a href="login/login.php" style="color: #e63946;">Sair</a>
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
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($ong['nome'] ?? ''); ?>" required placeholder="Ex: Instituto Patinhas Felizes">
                    </div>

                    <div class="campo-grupo">
                        <label for="cnpj">CNPJ</label>
                        <input type="text" id="cnpj" name="cnpj" value="<?php echo htmlspecialchars($ong['cnpj'] ?? ''); ?>" placeholder="00.000.000/0001-00">
                    </div>

                    <div class="campo-grupo">
                        <label for="telefone">Telefone / WhatsApp Comercial</label>
                        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($ong['telefone'] ?? ''); ?>" required placeholder="(00) 00000-0000">
                    </div>

                    <div class="campo-grupo">
                        <label for="responsavel">Nome do Responsável</label>
                        <input type="text" id="responsavel" name="responsavel" value="<?php echo htmlspecialchars($ong['responsavel'] ?? ''); ?>" required placeholder="Quem gerencia a conta">
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
            <!-- Botão Salvar -->

        </form>

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
</html>