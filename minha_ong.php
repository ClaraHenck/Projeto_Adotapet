<?php
// 1. Carrega as configurações (o auth.php inicia a sessão)
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';
$stmt = null;


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Busca o ID com verificação segura
$id = $_SESSION['id'] ?? $_SESSION['ong_id'] ?? $_SESSION['usuario_id'] ?? null;

if (!$id) {
    header("Location: login/login.php");
    exit;
}

$mensagem = '';
$erro = '';

// 3. Processar exclusão de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_perfil'])) {
    try {
        $stmtFoto = $pdo->prepare("SELECT foto FROM ongs WHERE id = ?");
        $stmtFoto->execute([$id]);
        $fotoAtual = $stmtFoto->fetchColumn();

        if (!empty($fotoAtual) && file_exists(__DIR__ . '/uploads/' . $fotoAtual)) {
            @unlink(__DIR__ . '/uploads/' . $fotoAtual);
        }

        $stmtDelete = $pdo->prepare("DELETE FROM ongs WHERE id = ?");
        $stmtDelete->execute([$id]);

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

// 4. Processar Upload e Salvamento Exclusivo da Foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_foto'])) {
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extensao, $extensoes_permitidas)) {
            $diretorio_uploads = __DIR__ . '/uploads/';

            if (!is_dir($diretorio_uploads)) {
                mkdir($diretorio_uploads, 0755, true);
            }

            // Busca foto antiga para deletar
            $stmtFoto = $pdo->prepare("SELECT foto FROM ongs WHERE id = ?");
            $stmtFoto->execute([$id]);
            $fotoAntiga = $stmtFoto->fetchColumn();

            $novo_nome_foto = 'ong_' . $id . '_' . time() . '.' . $extensao;
            $caminho_destino = $diretorio_uploads . $novo_nome_foto;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_destino)) {
                if (!empty($fotoAntiga) && file_exists($diretorio_uploads . $fotoAntiga)) {
                    @unlink($diretorio_uploads . $fotoAntiga);
                }

                $stmtUpdateFoto = $pdo->prepare("UPDATE ongs SET foto = ? WHERE id = ?");
                $stmtUpdateFoto->execute([$novo_nome_foto, $id]);

                $mensagem = "Foto de perfil atualizada com sucesso!";
            } else {
                $erro = "Falha ao salvar a imagem no servidor.";
            }
        } else {
            $erro = "Formato de arquivo inválido. Escolha uma imagem JPG, PNG ou WEBP.";
        }
    } else {
        $erro = "Selecione uma foto da sua galeria antes de clicar em salvar.";
    }
}

// 5. Processar atualização dos Dados Gerais
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_dados'])) {
    $nome_instituicao    = trim($_POST['nome_instituicao'] ?? '');
    $cnpj                = trim($_POST['cnpj'] ?? '') ?: null;
    $telefone            = trim($_POST['telefone'] ?? '');
    $instagram           = trim($_POST['instagram'] ?? '') ?: null;
    $logradouro          = trim($_POST['logradouro'] ?? '') ?: null;
    $numero              = trim($_POST['numero'] ?? '') ?: null;
    $bairro              = trim($_POST['bairro'] ?? '') ?: null;
    $cidade              = trim($_POST['cidade'] ?? '') ?: null;
    $estado              = trim($_POST['estado'] ?? '') ?: null;
    $cep                 = trim($_POST['cep'] ?? '') ?: null;
    $horario_atendimento = trim($_POST['horario_atendimento'] ?? '') ?: null;
    $raio_atuacao        = trim($_POST['raio_atuacao'] ?? '') ?: null;
    $chave_pix           = trim($_POST['chave_pix'] ?? '') ?: null;
    $capacidade_abrigados= $_POST['capacidade_abrigados'] !== '' ? (int)$_POST['capacidade_abrigados'] : null;

    try {
        $sql = "UPDATE ongs SET 
                    nome_instituicao = :nome_instituicao, 
                    cnpj = :cnpj, 
                    telefone = :telefone, 
                    instagram = :instagram,
                    logradouro = :logradouro, 
                    numero = :numero, 
                    bairro = :bairro, 
                    cidade = :cidade, 
                    estado = :estado, 
                    cep = :cep, 
                    horario_atendimento = :horario_atendimento, 
                    raio_atuacao = :raio_atuacao, 
                    chave_pix = :chave_pix, 
                    capacidade_abrigados = :capacidade_abrigados 
                WHERE id = :id";

        $stmtUpdate = $pdo->prepare($sql);
        $stmtUpdate->execute([
            ':nome_instituicao'    => $nome_instituicao,
            ':cnpj'                => $cnpj,
            ':telefone'            => $telefone,
            ':instagram'           => $instagram,
            ':logradouro'          => $logradouro,
            ':numero'              => $numero,
            ':bairro'              => $bairro,
            ':cidade'              => $cidade,
            ':estado'              => $estado,
            ':cep'                 => $cep,
            ':horario_atendimento' => $horario_atendimento,
            ':raio_atuacao'        => $raio_atuacao,
            ':chave_pix'           => $chave_pix,
            ':capacidade_abrigados'=> $capacidade_abrigados,
            ':id'                  => $id
        ]);

        $mensagem = "Informações atualizadas com sucesso!";
    } catch (PDOException $e) {
        $erro = "Erro ao salvar alterações: " . $e->getMessage();
    }
}

// 6. Buscar informações atuais da ONG no banco de dados
try {
    $stmt = $pdo->prepare("SELECT * FROM ongs WHERE id = ?");
    $stmt->execute([$id]);
    $ong = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    $erro = "Erro ao carregar dados: " . $e->getMessage();
    $ong = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdotaPet - Minha ONG</title>
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
            <a href="minha_ong.php" class="active">Meu Perfil</a>
            <a href="logout.php" class="btn-logout">Sair</a>
        </nav>
    </header>

    <!-- CONTAINER PRINCIPAL -->
    <main class="container">
        
        <div class="header-titulo">
            <h1>Perfil da ONG</h1>
            <p>Gerencie as informações cadastrais e a foto de perfil da sua instituição</p>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div class="alerta-sucesso"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div class="alerta-erro"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <!-- BLOCO 1: FOTO DE PERFIL (Formulário Próprio) -->
        <div class="secao-bloco">
            <h2>Foto de Perfil / Logo</h2>
            <form action="minha_ong.php" method="POST" enctype="multipart/form-data">
                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                    
                    <!-- Preview da Foto -->
                    <div>
                        <?php if (!empty($ong['foto']) && file_exists(__DIR__ . '/uploads/' . $ong['foto'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($ong['foto']); ?>" alt="Foto da ONG" style="width: 110px; height: 110px; object-fit: cover; border-radius: 50%; border: 3px solid #cbd5e1;">
                        <?php else: ?>
                            <div style="width: 110px; height: 110px; border-radius: 50%; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 13px; font-weight: bold;">Sem foto</div>
                        <?php endif; ?>
                    </div>

                    <!-- Controles para Seleção e Upload -->
                    <div style="flex: 1; min-width: 250px;">
                        <label for="foto" style="display: block; font-weight: 600; margin-bottom: 8px;">Escolher imagem da galeria</label>
                        <input type="file" id="foto" name="foto" accept="image/png, image/jpeg, image/jpg, image/webp" required style="margin-bottom: 12px;">
                        <br>
                        <button type="submit" name="salvar_foto" value="1" class="btn-salvar" style="padding: 10px 20px; width: auto;">Salvar Foto</button>
                    </div>

                </div>
            </form>
        </div>

        <!-- BLOCO 2: INFORMAÇÕES CADASTRAIS DA ONG -->
        <form action="minha_ong.php" method="POST">
            
            <div class="secao-bloco">
                <h2>Informações Institucionais</h2>
                <div class="form-grid">
                    <div class="campo-grupo full-width">
                        <label for="nome_instituicao">Nome da Instituição / ONG *</label>
                        <input type="text" id="nome_instituicao" name="nome_instituicao" value="<?php echo htmlspecialchars($ong['nome_instituicao'] ?? ''); ?>" required placeholder="Ex: ONG Proteção Animal SP">
                    </div>

                    <div class="campo-grupo">
                        <label for="email">E-mail de Login (Não alterável)</label>
                        <input type="text" id="email" value="<?php echo htmlspecialchars($ong['email'] ?? ''); ?>" disabled style="background-color: #f1f5f9; cursor: not-allowed;">
                    </div>

                    <div class="campo-grupo">
                        <label for="cnpj">CNPJ</label>
                        <input type="text" id="cnpj" name="cnpj" value="<?php echo htmlspecialchars($ong['cnpj'] ?? ''); ?>" placeholder="00.000.000/0001-00">
                    </div>

                    <div class="campo-grupo">
                        <label for="telefone">Telefone / WhatsApp *</label>
                        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($ong['telefone'] ?? ''); ?>" required placeholder="(11) 98888-1111">
                    </div>

                    <div class="campo-grupo">
                        <label for="instagram">Instagram</label>
                        <input type="text" id="instagram" name="instagram" value="<?php echo htmlspecialchars($ong['instagram'] ?? ''); ?>" placeholder="@minhaong ou link">
                    </div>

                    <div class="campo-grupo full-width">
                        <label for="chave_pix">Chave PIX para Doações</label>
                        <input type="text" id="chave_pix" name="chave_pix" value="<?php echo htmlspecialchars($ong['chave_pix'] ?? ''); ?>" placeholder="CNPJ, E-mail, Telefone ou Chave Aleatória">
                    </div>
                </div>
            </div>

            <div class="secao-bloco">
                <h2>Endereço e Sede</h2>
                <div class="form-grid">
                    <div class="campo-grupo">
                        <label for="cep">CEP</label>
                        <input type="text" id="cep" name="cep" value="<?php echo htmlspecialchars($ong['cep'] ?? ''); ?>" placeholder="00000-000">
                    </div>

                    <div class="campo-grupo">
                        <label for="logradouro">Logradouro (Rua / Av.)</label>
                        <input type="text" id="logradouro" name="logradouro" value="<?php echo htmlspecialchars($ong['logradouro'] ?? ''); ?>" placeholder="Av. Paulista">
                    </div>

                    <div class="campo-grupo">
                        <label for="numero">Número</label>
                        <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($ong['numero'] ?? ''); ?>" placeholder="1000">
                    </div>

                    <div class="campo-grupo">
                        <label for="bairro">Bairro</label>
                        <input type="text" id="bairro" name="bairro" value="<?php echo htmlspecialchars($ong['bairro'] ?? ''); ?>" placeholder="Bela Vista">
                    </div>

                    <div class="campo-grupo">
                        <label for="cidade">Cidade</label>
                        <input type="text" id="cidade" name="cidade" value="<?php echo htmlspecialchars($ong['cidade'] ?? ''); ?>" placeholder="São Paulo">
                    </div>

                    <div class="campo-grupo">
                        <label for="estado">Estado (UF)</label>
                        <input type="text" id="estado" name="estado" maxlength="2" value="<?php echo htmlspecialchars($ong['estado'] ?? ''); ?>" placeholder="SP" style="text-transform: uppercase;">
                    </div>
                </div>
            </div>

            <div class="secao-bloco">
                <h2>Funcionamento e Capacidade</h2>
                <div class="form-grid">
                    <div class="campo-grupo">
                        <label for="capacidade_abrigados">Capacidade de Abrigados</label>
                        <input type="number" id="capacidade_abrigados" name="capacidade_abrigados" value="<?php echo htmlspecialchars($ong['capacidade_abrigados'] ?? ''); ?>" placeholder="Ex: 50" min="0">
                    </div>

                    <div class="campo-grupo">
                        <label for="raio_atuacao">Raio de Atuação</label>
                        <input type="text" id="raio_atuacao" name="raio_atuacao" value="<?php echo htmlspecialchars($ong['raio_atuacao'] ?? ''); ?>" placeholder="Ex: Toda a região metropolitana">
                    </div>

                    <div class="campo-grupo full-width">
                        <label for="horario_atendimento">Horário de Atendimento</label>
                        <input type="text" id="horario_atendimento" name="horario_atendimento" value="<?php echo htmlspecialchars($ong['horario_atendimento'] ?? ''); ?>" placeholder="Ex: Seg a Sex das 08h às 17h, Sáb das 09h às 13h">
                    </div>
                </div>
            </div>

            <button type="submit" name="salvar_dados" value="1" class="btn-salvar">Salvar Alterações do Perfil</button>

        </form>

        <!-- BLOCO 3: ZONA DE PERIGO -->
        <div class="secao-perigo">
            <h2>Zona de Perigo</h2>
            <p>Ao excluir a conta, todas as informações cadastrais e dados vinculados a esta ONG serão permanentemente removidos. Esta ação não pode ser desfeita.</p>
            <form action="minha_ong.php" method="POST" onsubmit="return confirm('Tem certeza absoluta de que deseja excluir o perfil da ONG? Todos os dados serão perdidos definitivamente.');">
                <button type="submit" name="excluir_perfil" value="1" class="btn-excluir">Excluir Perfil da ONG</button>
            </form>
        </div>

    </main>

</body>

</html>
