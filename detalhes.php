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
    SELECT a.*, a.ong_id, o.nome_instituicao, o.telefone as ong_telefone
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

// 4. ALGORITMO DINÂMICO E RIGOROSO DE COMPATIBILIDADE
$tem_questionario = (bool)$questionario;
$porcentagem_match = 0;
$mensagem_match = "";
$relatorio_match = [];

if ($tem_questionario) {
    // === EXTRAÇÃO E NORMALIZAÇÃO DOS DADOS DO PET ===
    $portePet         = strtolower(trim($pet['porte'] ?? 'médio'));
    $especieRaca      = strtolower(trim($pet['especie_raca'] ?? ''));
    $descricaoPet     = strtolower(trim($pet['descricao'] ?? ''));
    $idadeTexto       = strtolower(trim($pet['idade_estimada'] ?? ''));
    $temperamentoPet  = strtolower(trim($pet['temperamento'] ?? ''));
    $sociabilidadePet = strtolower(trim($pet['sociabilidade'] ?? ''));
    
    $isGato           = (str_contains($especieRaca, 'gato') || str_contains($especieRaca, 'felina'));
    preg_match('/\d+/', $idadeTexto, $matches);
    $idadeAnos        = isset($matches[0]) ? intval($matches[0]) : 3;
    $isFilhote        = ($idadeAnos <= 1);

    // === EXTRAÇÃO DAS RESPOSTAS DO ADOTANTE ===
    $ondeMora     = strtolower($questionario['onde_mora'] ?? 'apartamento');
    $areaExterna  = (int)($questionario['tem_area_externa'] ?? 0);
    $horasFora    = (int)($questionario['horas_fora_casa'] ?? 8);
    $experiencia  = strtolower($questionario['experiencia'] ?? 'primeira_vez');
    $temCriancas  = (int)($questionario['tem_criancas'] ?? 0);
    $temAnimais   = (int)($questionario['tem_outros_animais'] ?? 0);
    $atividade    = strtolower($questionario['nivel_atividade_fisica'] ?? 'moderado');

    // Mapeamento de incompatibilidade crítica de segurança
    $incompatibilidadeCritica = false;

    // 1º: HORAS FORA DE CASA (20%)
    if ($isFilhote) {
        if ($horasFora <= 4) {
            $porcentagem_match += 20;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Jornada de {$horasFora}h fora - Presença ideal para a criação de um filhote."];
        } elseif ($horasFora <= 6) {
            $porcentagem_match += 10;
            $relatorio_match[] = ['tipo' => 'alerta', 'texto' => "Jornada de {$horasFora}h fora - Razoável, mas exigirá dedicação extra no tempo livre."];
        } else {
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Jornada de {$horasFora}h fora - Período muito longo para a atenção que um filhote exige."];
        }
    } else {
        if ($horasFora <= 6) {
            $porcentagem_match += 20;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Jornada de {$horasFora}h fora - Excelente tempo de presença diária."];
        } elseif ($horasFora <= 10) {
            $porcentagem_match += 14;
            $relatorio_match[] = ['tipo' => 'alerta', 'texto' => "Jornada de {$horasFora}h fora - Aceitável para cão adulto, mas limita o tempo juntos."];
        } else {
            $porcentagem_match += 6;
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Jornada de {$horasFora}h fora - O pet passará a maior parte do dia sozinho."];
        }
    }

    // 2º: TIPO DE MORADIA (18%)
    if ($isGato || $portePet === 'pequeno') {
        $porcentagem_match += 18;
        $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Moradia (" . ucfirst($ondeMora) . ") - Excelente espaço para gatos ou porte pequeno."];
    } elseif (in_array($portePet, ['médio', 'medio'])) {
        if (in_array($ondeMora, ['casa_com_quintal', 'sitio'])) {
            $porcentagem_match += 18;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Moradia ampla - Ideal para o porte médio."];
        } elseif ($ondeMora === 'casa_sem_quintal') {
            $porcentagem_match += 14;
            $relatorio_match[] = ['tipo' => 'alerta', 'texto' => "Casa sem quintal - Adequada, mas exigirá passeios diários para compensar."];
        } else {
            $porcentagem_match += 10;
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Apartamento - Pode restringir o espaço de movimentação de um porte médio."];
        }
    } else {
        if (in_array($ondeMora, ['casa_com_quintal', 'sitio'])) {
            $porcentagem_match += 18;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Espaço amplo - Excelente para o porte grande."];
        } elseif ($ondeMora === 'casa_sem_quintal') {
            $porcentagem_match += 8;
            $relatorio_match[] = ['tipo' => 'alerta', 'texto' => "Casa sem quintal - Espaço bastante justo para o tamanho do pet."];
        } else {
            $porcentagem_match += 2;
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Apartamento - Não é um ambiente adequado para pets de porte grande."];
        }
    }

    // 3º: ÁREA EXTERNA (15%)
    if ($isGato) {
        $porcentagem_match += 15;
        $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Ambiente sem acesso livre à rua - Segurança ideal para felinos."];
    } elseif ($portePet === 'grande' || $portePet === 'médio' || $portePet === 'medio') {
        if ($areaExterna === 1) {
            $porcentagem_match += 15;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Possui área externa - Ótimo espaço para gasto de energia."];
        } else {
            $porcentagem_match += 4;
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Sem área externa - Demandará passeios e estímulo constante."];
        }
    } else {
        if ($areaExterna === 1) {
            $porcentagem_match += 15;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Possui área externa - Excelente para atividades ao ar livre."];
        } else {
            $porcentagem_match += 10;
            $relatorio_match[] = ['tipo' => 'alerta', 'texto' => "Sem área externa - Funciona para porte pequeno, mas restringe atividades."];
        }
    }

    // 4º: VALIDAÇÃO CRÍTICA DE OUTROS ANIMAIS EM CASA (12%)
    $petIncompativelAnimais = (
        str_contains($sociabilidadePet, 'humanos') || 
        str_contains($sociabilidadePet, 'não') || 
        str_contains($sociabilidadePet, 'nao') || 
        str_contains($sociabilidadePet, 'único') || 
        str_contains($sociabilidadePet, 'unico') || 
        str_contains($temperamentoPet, 'dominante') || 
        str_contains($temperamentoPet, 'territorial') ||
        str_contains($descricaoPet, 'animal único') ||
        str_contains($descricaoPet, 'não aceita outros')
    );

    if ($temAnimais === 1) {
        if ($petIncompativelAnimais) {
            $incompatibilidadeCritica = true;
            $relatorio_match[] = [
                'tipo' => 'negativo', 
                'texto' => "⚠️ RISCO DE SEGURANÇA: Você possui outros animais, mas este pet é cadastrado com perfil dominante, territorial ou não sociável com outros pets."
            ];
        } else {
            $porcentagem_match += 12;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Possui outros animais - Pet sociável para convivência compartilhada."];
        }
    } else {
        $porcentagem_match += 12;
        $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Sem outros animais - Sem disputa territorial."];
    }

    // 5º: VALIDAÇÃO CRÍTICA DE CRIANÇAS EM CASA (12%)
    $petIncompativelCriancas = (
        str_contains($temperamentoPet, 'dominante') || 
        str_contains($temperamentoPet, 'antissocial') || 
        str_contains($temperamentoPet, 'bravo') || 
        str_contains($temperamentoPet, 'arisco') ||
        str_contains($sociabilidadePet, 'adultos') || 
        str_contains($sociabilidadePet, 'não') || 
        str_contains($sociabilidadePet, 'nao') || 
        str_contains($descricaoPet, 'não recomendado para crianças') ||
        str_contains($descricaoPet, 'sem crianças')
    );

    if ($temCriancas === 1) {
        if ($petIncompativelCriancas) {
            $incompatibilidadeCritica = true;
            $relatorio_match[] = [
                'tipo' => 'negativo', 
                'texto' => "⚠️ RISCO DE SEGURANÇA: Você possui crianças em casa, porém o perfil dominante ou sociabilidade deste pet exige um ambiente apenas com adultos."
            ];
        } else {
            $porcentagem_match += 12;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Possui crianças - Temperamento e perfil amigáveis para ambiente familiar."];
        }
    } else {
        $porcentagem_match += 12;
        $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Sem crianças em casa - Ambiente mais calmo e previsível."];
    }

    // 6º: NÍVEL DE ATIVIDADE FÍSICA (12%)
    $petAltaEnergia = ($isFilhote || $portePet === 'grande' || in_array($temperamentoPet, ['brincalhão', 'agitado']));
    if ($petAltaEnergia) {
        if ($atividade === 'ativo') {
            $porcentagem_match += 12;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Tutor ativo - Sintonia perfeita com o ritmo de energia do pet."];
        } elseif ($atividade === 'moderado') {
            $porcentagem_match += 7;
            $relatorio_match[] = ['tipo' => 'alerta', 'texto' => "Tutor moderado - Pet ativo pode exigir um ritmo de exercícios maior."];
        } else {
            $porcentagem_match += 2;
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Tutor sedentário - Desalinhamento: o pet demanda bastante exercício físico."];
        }
    } else {
        if ($atividade === 'sedentario' || $atividade === 'moderado') {
            $porcentagem_match += 12;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Perfil tranquilo - Alinhado ao temperamento calmo do pet."];
        } else {
            $porcentagem_match += 9;
            $relatorio_match[] = ['tipo' => 'alerta', 'texto' => "Tutor ativo e pet calmo - Convivência tranquila, sem necessidade de alta intensidade."];
        }
    }

    // 7º: EXPERIÊNCIA PRÉVIA (11%)
    $exigeExperiencia = ($portePet === 'grande' || $isFilhote || in_array($temperamentoPet, ['dominante', 'antissocial', 'tímido']));
    if ($exigeExperiencia) {
        if ($experiencia === 'experiente' || $experiencia === 'ja_teve') {
            $porcentagem_match += 11;
            $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Experiência prévia - Conhecimento adequado para o manejo de pets dominantes ou de grande porte."];
        } else {
            $porcentagem_match += 2;
            $relatorio_match[] = ['tipo' => 'negativo', 'texto' => "Primeira viagem - Este pet possui perfil dominante ou exigências que requerem experiência prévia de manejo."];
        }
    } else {
        $porcentagem_match += 11;
        $relatorio_match[] = ['tipo' => 'positivo', 'texto' => "Manejo simples - Adaptável para qualquer perfil de tutor."];
    }

    // === REGRA DE SEGURANÇA MÁXIMA (TETO RIGOROSO) ===
    if ($incompatibilidadeCritica) {
        // Bloqueia a nota para no máximo 30%, independentemente de outros pontos positivos
        $porcentagem_match = min($porcentagem_match, 30);
    } else {
        $porcentagem_match = min(100, max(0, $porcentagem_match));
    }

    // MENSAGEM FINAL DE ACORDO COM A NOTA E RESTRIÇÕES
    if ($incompatibilidadeCritica) {
        $mensagem_match = "Incompatibilidade de Segurança: Este pet possui temperamento dominante ou restrições de sociabilidade incompatíveis com a presença de crianças ou outros animais na sua casa.";
    } elseif ($porcentagem_match >= 85) {
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

            <!-- GRID DE CARACTERÍSTICAS -->
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
                    <div class="icone-caract">🎭</div>
                    <div class="textos-caract">
                        <span>Temperamento</span>
                        <strong><?= htmlspecialchars($pet['temperamento'] ?? 'Não informado') ?></strong>
                    </div>
                </div>
                <div class="card-caracteristica">
                    <div class="icone-caract">👥</div>
                    <div class="textos-caract">
                        <span>Sociabilidade</span>
                        <strong><?= htmlspecialchars($pet['sociabilidade'] ?? 'Não informada') ?></strong>
                    </div>
                </div>
                <div class="card-caracteristica">
                    <div class="icone-caract">💉</div>
                    <div class="textos-caract">
                        <span>Vacinação</span>
                        <strong><?= htmlspecialchars($pet['carteira_vacinacao'] ?? 'Em dia') ?></strong>
                    </div>
                </div>
                <div class="card-caracteristica">
                    <div class="icone-caract">🏢</div>
                    <div class="textos-caract">
                        <span>ONG</span>
                        <strong><?= htmlspecialchars($pet['nome_instituicao'] ?? 'ONG Parceira') ?></strong>
                    </div>
                </div>
            </div>

            <!-- TAGS E DESCRIÇÃO -->
            <div class="tags-container">
                <span class="tag vacinado">💉 <?= htmlspecialchars($pet['carteira_vacinacao'] ?? 'Vacinado') ?></span>
                <?php if (!empty($pet['temperamento'])): ?>
                    <span class="tag temperamento">🎭 <?= htmlspecialchars($pet['temperamento']) ?></span>
                <?php endif; ?>
                <?php if (!empty($pet['sociabilidade'])): ?>
                    <span class="tag sociabilidade">👥 <?= htmlspecialchars($pet['sociabilidade']) ?></span>
                <?php endif; ?>
            </div>

            <p class="descricao-pet"><?= nl2br(htmlspecialchars($pet['descricao'] ?? '')) ?></p>
            <div class="localizacao">📍 ONG Responsável: <?= htmlspecialchars($pet['nome_instituicao'] ?? 'ONG Parceira') ?> (Contato: <?= htmlspecialchars($pet['ong_telefone'] ?? 'N/A') ?>)</div>

            <!-- BLOCO DE AÇÕES -->
            <div class="botoes-acao-container">
                <form method="POST">
                    <input type="hidden" name="acao_adotar" value="1">
                    <button type="submit" class="btn-adotar">
                        ♡ Quero Adotar o <?= htmlspecialchars($pet['nome']) ?>
                    </button>
                </form>

                <a href="perfil_ong.php?id=<?= $pet['ong_id'] ?>" class="btn-sobre-ong">
                    <span class="btn-icon">🏛️</span>
                    <span>Conhecer a ONG responsável</span>
                    <span class="btn-arrow">➔</span>
                </a>
            </div>
        </div>
    </main>

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
                        <span class="icone-fator">
                            <?php 
                                if ($item['tipo'] === 'positivo') echo '✔';
                                elseif ($item['tipo'] === 'alerta') echo '⚠️';
                                else echo '✖';
                            ?>
                        </span>
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