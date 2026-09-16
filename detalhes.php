<?php
// Inclui a verificação de autenticação e a conexão com o banco
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$erro = null;

// Captura o ID do adotante logado na sessão
$adotante_id = $_SESSION['adotante_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;

// 1. CAPTURA E VALIDAÇÃO DO ID DO ANIMAL DA URL
$pet_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$pet_id) {
    header("Location: adotar.php");
    exit;
}

// 2. BUSCA OS DADOS DO ANIMAL E DA ONG RESPONSÁVEL
$stmt = $pdo->prepare("
    SELECT a.*, o.nome_instituicao, o.telefone as ong_telefone
    FROM animais a
    LEFT JOIN ongs o ON a.ong_id = o.id
    WHERE a.id = :id
");
$stmt->execute(['id' => $pet_id]);
$pet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pet) {
    header("Location: adotar.php");
    exit;
}

// 3. BUSCA O QUESTIONÁRIO DO ADOTANTE LOGADO
$questionario = null;
if ($adotante_id) {
    $stmtQ = $pdo->prepare("SELECT * FROM questionarios WHERE adotante_id = :adotante_id ORDER BY id DESC LIMIT 1");
    $stmtQ->execute(['adotante_id' => $adotante_id]);
    $questionario = $stmtQ->fetch(PDO::FETCH_ASSOC);
}

// 4. ALGORITMO DINÂMICO DE COMPATIBILIDADE
$tem_questionario = (bool)$questionario;
$porcentagem_match = 0;
$mensagem_match = "";
$relatorio_match = [];

if ($tem_questionario) {
    // === EXTRAÇÃO E NORMALIZAÇÃO DOS DADOS DO PET ===
    $portePet      = strtolower(trim($pet['porte'] ?? 'médio'));
    $especieRaca   = strtolower(trim($pet['especie_raca'] ?? ''));
    $descricaoPet  = strtolower(trim($pet['descricao'] ?? ''));
    $idadeTexto    = strtolower(trim($pet['idade_estimada'] ?? ''));
    
    $isGato        = (str_contains($especieRaca, 'gato') || str_contains($especieRaca, 'felina'));
    preg_match('/\d+/', $idadeTexto, $matches);
    $idadeAnos     = isset($matches[0]) ? intval($matches[0]) : 3;
    $isFilhote     = ($idadeAnos <= 1);

    // === EXTRAÇÃO DAS RESPOSTAS DO ADOTANTE ===
    $ondeMora     = strtolower($questionario['onde_mora'] ?? 'apartamento');
    $areaExterna  = (int)($questionario['tem_area_externa'] ?? 0);
    $horasFora    = (int)($questionario['horas_fora_casa'] ?? 8);
    $experiencia  = strtolower($questionario['experiencia'] ?? 'primeira_vez');
    $temCriancas  = (int)($questionario['tem_criancas'] ?? 0);
    $temAnimais   = (int)($questionario['tem_outros_animais'] ?? 0);
    $atividade    = strtolower($questionario['nivel_atividade_fisica'] ?? 'moderado');

    // 1º: HORAS FORA DE CASA (20%)
    if ($isFilhote) {
        if ($horasFora <= 4) {
            $porcentagem_match += 20;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Jornada de {$horasFora}h fora - Ideal para dar a atenção necessária a um filhote."];
        } elseif ($horasFora <= 6) {
            $porcentagem_match += 10;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Jornada de {$horasFora}h fora - Razoável, mas exige dedicação extra para o filhote."];
        } else {
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Jornada de {$horasFora}h fora - Alta para um filhote que requer presença constante."];
        }
    } else {
        if ($horasFora <= 6) {
            $porcentagem_match += 20;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Jornada de {$horasFora}h fora - Excelente presença diária para o animal."];
        } elseif ($horasFora <= 10) {
            $porcentagem_match += 14;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Jornada de {$horasFora}h fora - Compatível com a rotina de um pet adulto."];
        } else {
            $porcentagem_match += 6;
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Jornada de {$horasFora}h fora - O pet passará longos períodos sozinho."];
        }
    }

    // 2º: TIPO DE MORADIA (18%)
    if ($isGato || $portePet === 'pequeno') {
        $porcentagem_match += 18;
        $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Moradia (" . ucfirst($ondeMora) . ") - Excelente tamanho para gatos ou porte pequeno."];
    } elseif (in_array($portePet, ['médio', 'medio'])) {
        if (in_array($ondeMora, ['casa_com_quintal', 'sitio'])) {
            $porcentagem_match += 18;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Moradia ampla - Ótima para o porte médio do pet."];
        } elseif ($ondeMora === 'casa_sem_quintal') {
            $porcentagem_match += 14;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Casa sem quintal - Adequada, exigindo rotina de passeios."];
        } else {
            $porcentagem_match += 10;
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Apartamento - Pode limitar a movimentação para um pet de porte médio."];
        }
    } else {
        if (in_array($ondeMora, ['casa_com_quintal', 'sitio'])) {
            $porcentagem_match += 18;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Espaço amplo - Essencial para o bem-estar de um cão de porte grande."];
        } elseif ($ondeMora === 'casa_sem_quintal') {
            $porcentagem_match += 8;
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Casa sem quintal - Espaço restrito para um pet de porte grande."];
        } else {
            $porcentagem_match += 2;
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Apartamento - Não é o espaço mais indicado para um pet grande."];
        }
    }

    // 3º: ÁREA EXTERNA (15%)
    if ($isGato) {
        $porcentagem_match += 15;
        $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Ambiente sem acesso livre à rua - Segurança ideal para felinos."];
    } elseif ($portePet === 'grande' || $portePet === 'médio' || $portePet === 'medio') {
        if ($areaExterna === 1) {
            $porcentagem_match += 15;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Possui área externa - Ótimo para o animal gastar energia."];
        } else {
            $porcentagem_match += 4;
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Sem área externa - Demandará passeios diários frequentes."];
        }
    } else {
        if ($areaExterna === 1) {
            $porcentagem_match += 15;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Possui área externa - Ponto positivo para momentos ao ar livre."];
        } else {
            $porcentagem_match += 10;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Sem área externa - Adequado para o porte pequeno."];
        }
    }

    // 4º: OUTROS ANIMAIS EM CASA (12%)
    $petRestritoAnimais = (str_contains($descricaoPet, 'único') || str_contains($descricaoPet, 'nao gosta de caes') || str_contains($descricaoPet, 'não gosta de gatos'));
    if ($temAnimais === 1) {
        if ($petRestritoAnimais) {
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Possui outros animais - Este pet precisa ser animal único segundo o perfil."];
        } else {
            $porcentagem_match += 12;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Possui outros animais - Pet sociável para convivência."];
        }
    } else {
        $porcentagem_match += 12;
        $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Sem outros animais em casa - Sem concorrência por espaço ou atenção."];
    }

    // 5º: CRIANÇAS EM CASA (12%)
    $petAriscoOuBravo = (str_contains($descricaoPet, 'bravo') || str_contains($descricaoPet, 'arisco') || str_contains($descricaoPet, 'não recomendado para crianças'));
    if ($temCriancas === 1) {
        if ($petAriscoOuBravo) {
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Possui crianças - Pet reativo ou receoso, exige cuidados redobrados."];
        } else {
            $porcentagem_match += 12;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Possui crianças - Pet com perfil amigável para famílias."];
        }
    } else {
        $porcentagem_match += 12;
        $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Sem crianças pequenas - Ambiente calmo garantido para o pet."];
    }

    // 6º: NÍVEL DE ATIVIDADE FÍSICA (12%)
    $petAltaEnergia = ($isFilhote || $portePet === 'grande' || str_contains($descricaoPet, 'ativo') || str_contains($descricaoPet, 'brincalhão'));
    if ($petAltaEnergia) {
        if ($atividade === 'ativo') {
            $porcentagem_match += 12;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Tutor ativo - Perfeita sintonia com a alta energia do pet."];
        } elseif ($atividade === 'moderado') {
            $porcentagem_match += 7;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Tutor moderado - Boa compatibilidade com o nível de energia do pet."];
        } else {
            $porcentagem_match += 2;
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Tutor sedentário - O pet tem alta energia e demandará mais exercícios."];
        }
    } else {
        if ($atividade === 'sedentario' || $atividade === 'moderado') {
            $porcentagem_match += 12;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Perfil tranquilo/moderado - Alinhado ao temperamento calmo do pet."];
        } else {
            $porcentagem_match += 9;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Tutor ativo com pet calmo - Boa convivência garantida."];
        }
    }

    // 7º: EXPERIÊNCIA PRÉVIA (11%)
    $exigeExperiencia = ($portePet === 'grande' || $isFilhote || str_contains($descricaoPet, 'especial') || str_contains($descricaoPet, 'trauma'));
    if ($exigeExperiencia) {
        if ($experiencia === 'experiente' || $experiencia === 'ja_teve') {
            $porcentagem_match += 11;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Experiência prévia - Conhecimento ideal para o manejo do pet."];
        } else {
            $porcentagem_match += 2;
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Primeira viagem - Este pet exige um nível maior de experiência no manejo."];
        }
    } else {
        $porcentagem_match += 11;
        $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Manejo simples - Excelente opção para qualquer perfil de tutor."];
    }

    $porcentagem_match = min(100, max(0, $porcentagem_match));

    if ($porcentagem_match >= 85) {
        $mensagem_match = "Excelente combinação! O seu estilo de vida encaixa perfeitamente com este pet.";
    } elseif ($porcentagem_match >= 65) {
        $mensagem_match = "Boa combinação. A rotina de vocês é compatível e exige poucos ajustes.";
    } else {
        $mensagem_match = "Atenção. O perfil deste pet possui exigências específicas para o seu formato de rotina.";
    }
}

// 5. PROCESSAMENTO DO ENVIO DE CANDIDATURA (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_adotar'])) {
    
    if (!$adotante_id) {
        $erro = "Sua sessão expirou ou você não está logado como adotante. Faça login novamente.";
    } elseif (!$tem_questionario) {
        header("Location: questionario.php?returnTo={$pet_id}");
        exit;
    } else {
        try {
            $stmtCheck = $pdo->prepare("SELECT id FROM candidaturas WHERE adotante_id = :adotante_id AND animal_id = :animal_id");
            $stmtCheck->execute([
                'adotante_id' => $adotante_id,
                'animal_id'   => $pet_id
            ]);

            if (!$stmtCheck->fetch()) {
                $stmtInsert = $pdo->prepare("
                    INSERT INTO candidaturas (adotante_id, animal_id, questionario_id, status_candidatura, compatibilidade)
                    VALUES (:adotante_id, :animal_id, :questionario_id, 'Pendente', :compatibilidade)
                ");
                $stmtInsert->execute([
                    'adotante_id'     => $adotante_id,
                    'animal_id'       => $pet_id,
                    'questionario_id' => $questionario['id'],
                    'compatibilidade' => $porcentagem_match . '%'
                ]);
            }

            header("Location: candidaturas.php");
            exit;

        } catch (PDOException $e) {
            $erro = "Erro ao enviar candidatura: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdotaPet - Detalhes do Animal</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="detalhes.css">
</head>
<body>

    <header class="main-header">
        <a href="index.php" class="logo">
            <span class="paw-icon">🐾</span>
            <h1>Adota<span>Pet</span></h1>
        </a>
        <nav class="nav-links">
            <a href="index.php">Início</a>
            <a href="adotar.php" class="active">Adotar</a>
            <a href="mapa.php">Mapa</a>
            <a href="candidaturas.php">Candidaturas</a>
            <a href="perfil.php">Meu Perfil</a>
            <a href="logout.php" class="btn-logout" title="Sair">Sair</a>
        </nav>
    </header>

    <?php if ($erro): ?>
        <div class="alerta-erro">
            ⚠️ <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <main class="main-container">
        <!-- FOTO DO PET -->
        <div class="imagem-container">
            <img src="<?= htmlspecialchars($pet['foto_url'] ?? 'img/default-pet.png') ?>" alt="Foto do <?= htmlspecialchars($pet['nome']) ?>">
        </div>

        <div class="info-container">
            <div class="cabecalho-pet">
                <div class="titulo-pet">
                    <h1><?= htmlspecialchars($pet['nome']) ?></h1>
                    <p><?= htmlspecialchars($pet['especie_raca'] ?? '') ?></p>
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
                            <button type="button" class="btn-ver-detalhes-match" onclick="abrirModalMatch()">
                                🔍 Ver Fatores da Nota
                            </button>
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
                        <strong><?= htmlspecialchars($pet['idade_estimada'] ?? 'Não informada') ?></strong>
                    </div>
                </div>
                <div class="card-caracteristica">
                    <div class="icone-caract">🛡️</div>
                    <div class="textos-caract">
                        <span>Porte</span>
                        <strong><?= htmlspecialchars($pet['porte'] ?? 'Não informado') ?></strong>
                    </div>
                </div>
                <div class="card-caracteristica">
                    <div class="icone-caract">🏢</div>
                    <div class="textos-caract">
                        <span>ONG</span>
                        <strong><?= htmlspecialchars($pet['nome_instituicao'] ?? 'ONG Parceira') ?></strong>
                    </div>
                </div>
                <div class="card-caracteristica">
                    <div class="icone-caract">💉</div>
                    <div class="textos-caract">
                        <span>Vacinação</span>
                        <strong><?= htmlspecialchars($pet['carteira_vacinacao'] ?? 'Em dia') ?></strong>
                    </div>
                </div>
            </div>

            <!-- TAGS E DESCRIÇÃO -->
            <div class="tags-container">
                <span class="tag vacinado">💉 <?= htmlspecialchars($pet['carteira_vacinacao'] ?? 'Vacinado') ?></span>
            </div>

            <p class="descricao-pet"><?= nl2br(htmlspecialchars($pet['descricao'] ?? '')) ?></p>
            <div class="localizacao">📍 ONG Responsável: <?= htmlspecialchars($pet['nome_instituicao'] ?? 'ONG Parceira') ?> (Contato: <?= htmlspecialchars($pet['ong_telefone'] ?? 'N/A') ?>)</div>

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
        <div class="tab-content">🩺 Prontuário e histórico de saúde do pet...</div>
    </section>

    <!-- MODAL DE LISTAGEM DOS FATORES DE COMPATIBILIDADE -->
    <?php if ($tem_questionario): ?>
    <div id="modal-match" class="modal-match-overlay" style="display: none;">
        <div class="modal-match-content">
            <div class="modal-match-header">
                <h3>Detalhamento do Match (<?= $porcentagem_match ?>%)</h3>
                <button type="button" class="btn-fechar-modal" onclick="fecharModalMatch()">&times;</button>
            </div>
            <p style="margin-bottom: 15px; color: #555; font-size: 14px;">
                Veja como cada fator da sua rotina se alinhou com as necessidades do <strong><?= htmlspecialchars($pet['nome']) ?></strong>:
            </p>
            <ul class="lista-fatores-match">
                <?php foreach ($relatorio_match as $item): ?>
                    <li class="item-fator <?= $item['tipo'] ?>">
                        <span class="icone-fator"><?= $item['tipo'] === 'positivo' ? '✔' : '✖' ?></span>
                        <span class="texto-fator"><?= htmlspecialchars($item['texto']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function abrirModalMatch() {
            const modal = document.getElementById('modal-match');
            if (modal) modal.style.display = 'flex';
        }

        function fecharModalMatch() {
            const modal = document.getElementById('modal-match');
            if (modal) modal.style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('modal-match');
            if (event.target === modal) {
                fecharModalMatch();
            }
        }
    </script>
</body>
</html>