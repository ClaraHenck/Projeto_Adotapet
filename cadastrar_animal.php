<?php
// 1. IMPORTAÇÕES DE AUTENTICAÇÃO E BANCO DE DADOS
require_once 'config/auth.php';
require_once 'config/db.php';

// Mensagens de feedback para o usuário
$mensagemErro = '';

// 2. PROCESSAMENTO DO FORMULÁRIO QUANDO ENVIADO VIA POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Captura o ID da ONG autenticada na sessão
    $ong_id = $_SESSION['usuario_id'] ?? $_SESSION['ong_id'] ?? null;

    // Resgate e sanitização dos campos enviados
    $nome = trim($_POST['nome'] ?? '');
    $especie = trim($_POST['especie'] ?? '');
    $raca = trim($_POST['raca'] ?? '');
    $idade = trim($_POST['idade'] ?? '');
    $porte = trim($_POST['porte'] ?? '');
    $carteira_vacinacao = trim($_POST['carteira_vacinacao'] ?? '');
    $foto_url = trim($_POST['foto_url'] ?? '');
    $descricao = trim($_POST['observacoes'] ?? '');

    // Formatação para combinar Espécie + Raça no campo 'especie_raca' da tabela
    $especie_raca = !empty($raca) ? "{$especie} - {$raca}" : $especie;
    $idade_estimada = !empty($idade) ? "{$idade} anos" : "Não informada";

    // Validação simples dos campos obrigatórios
    if (!$ong_id) {
        $mensagemErro = "Sessão expirada. Faça login novamente como ONG para cadastrar.";
    } elseif (empty($nome) || empty($especie) || empty($porte) || empty($carteira_vacinacao) || empty($foto_url) || empty($descricao)) {
        $mensagemErro = "Por favor, preencha todos os campos obrigatórios (*).";
    } else {
        try {
            // Preparação da instrução SQL para evitar SQL Injection
            $sql = "INSERT INTO animais (ong_id, nome, especie_raca, idade_estimada, porte, carteira_vacinacao, foto_url, descricao) 
                    VALUES (:ong_id, :nome, :especie_raca, :idade_estimada, :porte, :carteira_vacinacao, :foto_url, :descricao)";
            
            $stmt = $pdo->prepare($sql);
            
            // Execução enviando os parâmetros sanitizados
            $stmt->execute([
                ':ong_id'            => $ong_id,
                ':nome'              => $nome,
                ':especie_raca'      => $especie_raca,
                ':idade_estimada'    => $idade_estimada,
                ':porte'             => $porte,
                ':carteira_vacinacao' => $carteira_vacinacao,
                ':foto_url'          => $foto_url,
                ':descricao'         => $descricao
            ]);

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
    <title>Cadastro de Animal - AdotaPet</title>
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
            <h2>Cadastrar Animal</h2>
            <p class="subtitle">Insira as informações do pet abaixo</p>
            
            <?php if (!empty($mensagemErro)): ?>
                <div class="alert-error"><?= htmlspecialchars($mensagemErro) ?></div>
            <?php endif; ?>

            <form action="cadastrar_animal.php" method="POST">
                
                <div class="form-group">
                    <label for="nome">Nome do Animal *</label>
                    <input type="text" id="nome" name="nome" required placeholder="Ex: Thor, Mel, Luna">
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="especie">Espécie *</label>
                        <select id="especie" name="especie" required>
                            <option value="" disabled selected>Selecione</option>
                            <option value="Cachorro">Cachorro</option>
                            <option value="Gato">Gato</option>
                            <option value="Pássaro">Pássaro</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="idade">Idade (Anos)</label>
                        <input type="number" id="idade" name="idade" min="0" max="30" placeholder="Ex: 2">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="raca">Raça / Tipo</label>
                        <input type="text" id="raca" name="raca" placeholder="Ex: Vira-lata, Labrador">
                    </div>

                    <div class="form-group">
                        <label for="porte">Porte *</label>
                        <select id="porte" name="porte" required>
                            <option value="" disabled selected>Selecione</option>
                            <option value="Pequeno">Pequeno</option>
                            <option value="Médio">Médio</option>
                            <option value="Grande">Grande</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="carteira_vacinacao">Carteira de Vacinação *</label>
                    <select id="carteira_vacinacao" name="carteira_vacinacao" required>
                        <option value="" disabled selected>Selecione a situação</option>
                        <option value="Vacinado e Vermifugado">Vacinado e Vermifugado</option>
                        <option value="Apenas Vacinado">Apenas Vacinado</option>
                        <option value="Pendente de Vacinação">Pendente de Vacinação</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="foto_url">URL da Foto do Pet *</label>
                    <input type="url" id="foto_url" name="foto_url" required placeholder="https://exemplo.com/imagem.jpg">
                </div>

                <div class="form-group">
                    <label for="observacoes">Observações Médicas ou Comportamento *</label>
                    <textarea id="observacoes" name="observacoes" required placeholder="Ex: Alérgico a medicamentos, dócil, sociável com gatos."></textarea>
                </div>

                <button type="submit">Cadastrar Animal</button>
            </form>
        </div>
    </main>

</body>
</html>