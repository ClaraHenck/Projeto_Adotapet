<?php
// 1. IMPORTAÇÕES DE AUTENTICAÇÃO E BANCO DE DADOS
require_once 'config/auth.php';
require_once 'config/db.php';

$ong_id = $_SESSION['usuario_id'] ?? $_SESSION['ong_id'] ?? null;
$mensagemErro = '';

// Variáveis padrão para o formulário
$id = $_GET['id'] ?? $_POST['id'] ?? null;
$nome = '';
$especie = '';
$raca = '';
$idade = '';
$porte = '';
$carteira_vacinacao = '';
$foto_url = '';
$descricao = '';
$modo_edicao = false;

// 2. SE VIER UM ID, BUSCA OS DADOS DO ANIMAL NO BANCO PARA PREENCHER O FORMULÁRIO
if ($id && $ong_id) {
    $stmt = $pdo->prepare("SELECT * FROM animais WHERE id = ? AND ong_id = ?");
    $stmt->execute([$id, $ong_id]);
    $animal = $stmt->fetch();

    if ($animal) {
        $modo_edicao = true;
        $nome = $animal['nome'];
        
        // Separa 'Espécie' e 'Raça' caso estejam salvas como "Cachorro - Labrador"
        $partes = explode(' - ', $animal['especie_raca'], 2);
        $especie = $partes[0] ?? '';
        $raca = $partes[1] ?? '';

        // Extrai apenas o número da idade (ex: "2 anos" vira "2")
        $idade = preg_replace('/[^0-9]/', '', $animal['idade_estimada']);

        $porte = $animal['porte'];
        $carteira_vacinacao = $animal['carteira_vacinacao'];
        $foto_url = $animal['foto_url'];
        $descricao = $animal['descricao'];
    }
}

// 3. PROCESSAMENTO DO FORMULÁRIO (SALVAR OU ATUALIZAR)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Resgate e sanitização dos campos enviados
    $nome = trim($_POST['nome'] ?? '');
    $especie = trim($_POST['especie'] ?? '');
    $raca = trim($_POST['raca'] ?? '');
    $idade = trim($_POST['idade'] ?? '');
    $porte = trim($_POST['porte'] ?? '');
    $carteira_vacinacao = trim($_POST['carteira_vacinacao'] ?? '');
    $foto_url = trim($_POST['foto_url'] ?? '');
    $descricao = trim($_POST['observacoes'] ?? '');

    // Formatação para combinar Espécie + Raça no campo 'especie_raca'
    $especie_raca = !empty($raca) ? "{$especie} - {$raca}" : $especie;
    $idade_estimada = !empty($idade) ? "{$idade} anos" : "Não informada";

    // Validação simples dos campos obrigatórios
    if (!$ong_id) {
        $mensagemErro = "Sessão expirada. Faça login novamente como ONG.";
    } elseif (empty($nome) || empty($especie) || empty($porte) || empty($carteira_vacinacao) || empty($foto_url) || empty($descricao)) {
        $mensagemErro = "Por favor, preencha todos os campos obrigatórios (*).";
    } else {
        try {
            if ($modo_edicao) {
                // ATUALIZAÇÃO (UPDATE) - Mantém o registro e altera os dados
                $sql = "UPDATE animais SET 
                            nome = :nome, 
                            especie_raca = :especie_raca, 
                            idade_estimada = :idade_estimada, 
                            porte = :porte, 
                            carteira_vacinacao = :carteira_vacinacao, 
                            foto_url = :foto_url, 
                            descricao = :descricao 
                        WHERE id = :id AND ong_id = :ong_id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nome'               => $nome,
                    ':especie_raca'       => $especie_raca,
                    ':idade_estimada'     => $idade_estimada,
                    ':porte'              => $porte,
                    ':carteira_vacinacao' => $carteira_vacinacao,
                    ':foto_url'           => $foto_url,
                    ':descricao'          => $descricao,
                    ':id'                 => $id,
                    ':ong_id'             => $ong_id
                ]);
            } else {
                // NOVO CADASTRO (INSERT)
                $sql = "INSERT INTO animais (ong_id, nome, especie_raca, idade_estimada, porte, carteira_vacinacao, foto_url, descricao) 
                        VALUES (:ong_id, :nome, :especie_raca, :idade_estimada, :porte, :carteira_vacinacao, :foto_url, :descricao)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':ong_id'             => $ong_id,
                    ':nome'               => $nome,
                    ':especie_raca'       => $especie_raca,
                    ':idade_estimada'     => $idade_estimada,
                    ':porte'              => $porte,
                    ':carteira_vacinacao' => $carteira_vacinacao,
                    ':foto_url'           => $foto_url,
                    ':descricao'          => $descricao
                ]);
            }

            // Redireciona após salvar com sucesso
            header("Location: meus_animais.php?sucesso=1");
            exit;

        } catch (PDOException $e) {
            $mensagemErro = "Erro ao salvar no banco de dados: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $modo_edicao ? 'Editar Animal' : 'Cadastro de Animal' ?> - AdotaPet</title>
    <!-- Importação do CSS da Navbar e de Cadastro -->
    <link rel="stylesheet" href="navbar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="cadastrar_animal.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- NAVBAR PADRONIZADA -->
    <header class="navbar">
        <a href="index.php" class="logo">🐾 AdotaPet</a>
        <nav class="menu">
            <a href="index.php">Início</a>
            <a href="meus_animais.php" class="active">Meus Animais</a>
            <a href="candidaturas_recebidas.php">Candidaturas</a>
            <a href="minha_ong.php">MINHA ONG</a>
            <a href="logout.php" class="btn-logout">Sair</a>
        </nav>
    </header>

    <main>
        <div class="container">
            <h2><?= $modo_edicao ? 'Editar Animal' : 'Cadastrar Animal' ?></h2>
            <p class="subtitle"><?= $modo_edicao ? 'Altere as informações do pet abaixo' : 'Insira as informações do pet abaixo' ?></p>
            
            <?php if (!empty($mensagemErro)): ?>
                <div class="alert-error"><?= htmlspecialchars($mensagemErro) ?></div>
            <?php endif; ?>

            <form action="cadastrar_animal.php<?= $modo_edicao ? '?id=' . $id : '' ?>" method="POST">
                
                <!-- Campo oculto com o ID para manter o modo de edição no submit -->
                <?php if ($modo_edicao): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nome">Nome do Animal *</label>
                    <input type="text" id="nome" name="nome" required placeholder="Ex: Thor, Mel, Luna" value="<?= htmlspecialchars($nome) ?>">
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="especie">Espécie *</label>
                        <select id="especie" name="especie" required>
                            <option value="" disabled <?= empty($especie) ? 'selected' : '' ?>>Selecione</option>
                            <option value="Cachorro" <?= $especie === 'Cachorro' ? 'selected' : '' ?>>Cachorro</option>
                            <option value="Gato" <?= $especie === 'Gato' ? 'selected' : '' ?>>Gato</option>
                            <option value="Pássaro" <?= $especie === 'Pássaro' ? 'selected' : '' ?>>Pássaro</option>
                            <option value="Outro" <?= ($especie && !in_array($especie, ['Cachorro','Gato','Pássaro'])) ? 'selected' : '' ?>>Outro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="idade">Idade (Anos)</label>
                        <input type="number" id="idade" name="idade" min="0" max="30" placeholder="Ex: 2" value="<?= htmlspecialchars($idade) ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="raca">Raça / Tipo</label>
                        <input type="text" id="raca" name="raca" placeholder="Ex: Vira-lata, Labrador" value="<?= htmlspecialchars($raca) ?>">
                    </div>

                    <div class="form-group">
                        <label for="porte">Porte *</label>
                        <select id="porte" name="porte" required>
                            <option value="" disabled <?= empty($porte) ? 'selected' : '' ?>>Selecione</option>
                            <option value="Pequeno" <?= $porte === 'Pequeno' ? 'selected' : '' ?>>Pequeno</option>
                            <option value="Médio" <?= $porte === 'Médio' ? 'selected' : '' ?>>Médio</option>
                            <option value="Grande" <?= $porte === 'Grande' ? 'selected' : '' ?>>Grande</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="carteira_vacinacao">Carteira de Vacinação *</label>
                    <select id="carteira_vacinacao" name="carteira_vacinacao" required>
                        <option value="" disabled <?= empty($carteira_vacinacao) ? 'selected' : '' ?>>Selecione a situação</option>
                        <option value="Vacinado e Vermifugado" <?= $carteira_vacinacao === 'Vacinado e Vermifugado' ? 'selected' : '' ?>>Vacinado e Vermifugado</option>
                        <option value="Apenas Vacinado" <?= $carteira_vacinacao === 'Apenas Vacinado' ? 'selected' : '' ?>>Apenas Vacinado</option>
                        <option value="Pendente de Vacinação" <?= $carteira_vacinacao === 'Pendente de Vacinação' ? 'selected' : '' ?>>Pendente de Vacinação</option>
                        <option value="Completa" <?= $carteira_vacinacao === 'Completa' ? 'selected' : '' ?>>Completa</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="foto_url">URL da Foto do Pet *</label>
                    <input type="url" id="foto_url" name="foto_url" required placeholder="https://exemplo.com/imagem.jpg" value="<?= htmlspecialchars($foto_url) ?>">
                </div>

                <div class="form-group">
                    <label for="observacoes">Observações Médicas ou Comportamento *</label>
                    <textarea id="observacoes" name="observacoes" required placeholder="Ex: Alérgico a medicamentos, dócil, sociável com gatos."><?= htmlspecialchars($descricao) ?></textarea>
                </div>

                <button type="submit"><?= $modo_edicao ? 'Salvar Alterações' : 'Cadastrar Animal' ?></button>
            </form>
        </div>
    </main>

</body>
</html>