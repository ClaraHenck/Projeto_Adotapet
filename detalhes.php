<?php
// Inclui a verificação de autenticação e a conexão com o banco
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';

// Pega o ID do usuário logado diretamente da Sessão do seu sistema
$adotante_id = $_SESSION['usuario_id'];

// 1. CAPTURA E VALIDAÇÃO DO ID DO ANIMAL DA URL
$pet_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$pet_id) {
    die("ID do animal é inválido ou não foi fornecido.");
}

// 2. BUSCA OS DADOS DO ANIMAL E DA ONG RESPONSÁVEL
$stmt = $pdo->prepare("
    SELECT a.*, o.nome_instituicao, o.telefone as ong_telefone
    FROM animais a
    JOIN ongs o ON a.ong_id = o.id
    WHERE a.id = :id
");
$stmt->execute(['id' => $pet_id]);
$pet = $stmt->fetch();

if (!$pet) {
    die("Animal não encontrado no banco de dados.");
}

// 3. BUSCA O QUESTIONÁRIO DO ADOTANTE LOGADO
$stmtQ = $pdo->prepare("SELECT * FROM questionarios WHERE adotante_id = :adotante_id ORDER BY id DESC LIMIT 1");
$stmtQ->execute(['adotante_id' => $adotante_id]);
$questionario = $stmtQ->fetch();

// 4. LÓGICA DE COMPATIBILIDADE (MATCH)
$tem_questionario = (bool)$questionario;
$porcentagem_match = 70; // Pontuação base
$mensagem_match = "";

if ($tem_questionario) {
    if (strtolower($pet['porte']) === 'grande' && strtolower($questionario['onde_mora']) === 'apartamento') {
        $porcentagem_match -= 15;
    } elseif (in_array(strtolower($questionario['onde_mora']), ['casa_com_quintal', 'sitio', 'casa'])) {
        $porcentagem_match += 20;
    }

    if ($questionario['horas_fora_casa'] > 6) {
        $porcentagem_match -= 10;
    } else {
        $porcentagem_match += 10;
    }

    // Trava os limites entre 0% e 100%
    $porcentagem_match = min(100, max(0, $porcentagem_match));

    if ($porcentagem_match >= 85) {
        $mensagem_match = "Excelente combinação! O estilo de vida de vocês combina perfeitamente.";
    } elseif ($porcentagem_match >= 70) {
        $mensagem_match = "Boa combinação. Vocês têm pequenos pontos a ajustar, mas se darão super bem!";
    } else {
        $mensagem_match = "Atenção. As necessidades deste pet exigem cuidados extras para a sua rotina.";
    }
}

// 5. PROCESSAMENTO DO ENVIO DE CANDIDATURA (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_adotar'])) {
    // Se o adotante ainda não fez o questionário, manda preencher primeiro
    if (!$tem_questionario) {
        header("Location: questionario.php?returnTo={$pet_id}");
        exit;
    }

    try {
        // Verifica se o usuário já enviou candidatura para este pet para não duplicar
        $stmtCheck = $pdo->prepare("SELECT id FROM candidaturas WHERE adotante_id = :adotante AND animal_id = :animal");
        $stmtCheck->execute(['adotante' => $adotante_id, 'animal' => $pet_id]);

        if (!$stmtCheck->fetch()) {
            $stmtInsert = $pdo->prepare("
                INSERT INTO candidaturas (adotante_id, animal_id, questionario_id, status_candidatura)
                VALUES (:adotante_id, :animal_id, :questionario_id, 'Pendente')
            ");
            $stmtInsert->execute([
                'adotante_id' => $adotante_id,
                'animal_id' => $pet_id,
                'questionario_id' => $questionario['id']
            ]);
        }
        
        header("Location: candidaturas.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao registrar candidatura: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdotaPet - Detalhes do Animal</title>
    <link rel="stylesheet" href="detalhes.css">
</head>
<body>

    <header class="navbar">
        <div class="logo" onclick="window.location.href='adotar.php'">🐾 Adota<span>Pet</span></div>
        <nav class="menu">
            <a href="index.php">🏠 Início</a>
            <a href="adotar.php">🔍 Adotar</a>
            <a href="mapa.php">📍 Mapa</a>
            <a href="candidaturas.php">❤️ Candidaturas</a>
        </nav>
    </header>

    <main class="main-container">
        <!-- FOTO DO PET -->
        <div class="imagem-container">
            <img src="<?= htmlspecialchars($pet['foto_url']) ?>" alt="Foto do <?= htmlspecialchars($pet['nome']) ?>">
        </div>

        <div class="info-container">
            <div class="cabecalho-pet">
                <div class="titulo-pet">
                    <h1><?= htmlspecialchars($pet['nome']) ?></h1>
                    <p><?= htmlspecialchars($pet['especie_raca']) ?></p>
                </div>
                <div class="badge-status">Disponível</div>
            </div>

            <!-- BLOCO DE COMPATIBILIDADE DINÂMICO -->
            <div class="alinhamento-compatibilidade">
                <?php if (!$tem_questionario): ?>
                    <div class="container-sem-questionario">
                        <div class="texto-sem-questionario">
                            <h3>Descubra seu Match com o <?= htmlspecialchars($pet['nome']) ?></h3>
                            <p>Responda nosso teste de rotina para calcular a compatibilidade exata.</p>
                        </div>
                        <a href="questionario.php?returnTo=<?= $pet['id'] ?>" class="btn-fazer-questionario">Fazer Teste</a>
                    </div>
                <?php else: ?>
                    <div class="barra-compatibilidade-container">
                        <div class="progresso-circular" style="--porcentagem: <?= $porcentagem_match ?>;">
                            <span><?= $porcentagem_match ?>%</span>
                        </div>
                        <div class="texto-compatibilidade">
                            <h3>Compatibilidade com o seu perfil</h3>
                            <p><?= htmlspecialchars($mensagem_match) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- CARACTERÍSTICAS -->
            <div class="grid-caracteristicas">
                <div class="card-caracteristica">
                    <div class="icone-caract">📅</div>
                    <div class="textos-caract">
                        <span>Idade</span>
                        <strong><?= htmlspecialchars($pet['idade_estimada']) ?></strong>
                    </div>
                </div>
                <div class="card-caracteristica">
                    <div class="icone-caract">🛡️</div>
                    <div class="textos-caract">
                        <span>Porte</span>
                        <strong><?= htmlspecialchars($pet['porte']) ?></strong>
                    </div>
                </div>
                <div class="card-caracteristica">
                    <div class="icone-caract">🏢</div>
                    <div class="textos-caract">
                        <span>ONG</span>
                        <strong><?= htmlspecialchars($pet['nome_instituicao']) ?></strong>
                    </div>
                </div>
                <div class="card-caracteristica">
                    <div class="icone-caract">💉</div>
                    <div class="textos-caract">
                        <span>Vacinação</span>
                        <strong><?= htmlspecialchars($pet['carteira_vacinacao']) ?></strong>
                    </div>
                </div>
            </div>

            <!-- TAGS E DESCRIÇÃO -->
            <div class="tags-container">
                <span class="tag vacinado">💉 <?= htmlspecialchars($pet['carteira_vacinacao']) ?></span>
            </div>

            <p class="descricao-pet"><?= nl2br(htmlspecialchars($pet['descricao'])) ?></p>
            <div class="localizacao">📍 ONG Responsável: <?= htmlspecialchars($pet['nome_instituicao']) ?> (Contato: <?= htmlspecialchars($pet['ong_telefone']) ?>)</div>

            <!-- BOTÃO DE AÇÃO DA CANDIDATURA -->
            <form method="POST">
                <input type="hidden" name="acao_adotar" value="1">
                <button type="submit" class="btn-adotar">
                    ♡ Quero Adotar o <?= htmlspecialchars($pet['nome']) ?>
                </button>
            </form>
        </div>
    </main>

    <section class="tabs-container">
        <div class="tabs-header">
            <button class="tab-btn active">Prontuário de Saúde</button>
            <button class="tab-btn">Carteirinha de Vacinação</button>
            <button class="tab-btn">Sobre a ONG</button>
        </div>
        <div class="tab-content">🩺</div>
    </section>

</body>
</html>