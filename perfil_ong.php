<?php
// 1. Carrega as configurações de banco de dados e sessão
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definição do rótulo do perfil para a Navbar
$labelPerfil = 'Meu Perfil';
if (isset($_SESSION['ong_id'])) {
    $labelPerfil = 'Painel ONG';
}

// 2. Recebe e valida o ID da ONG vindo da URL
$ong_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$ong_id) {
    header("Location: index.php");
    exit;
}

// 3. Busca os dados da ONG no banco
try {
    $stmtOng = $pdo->prepare("SELECT * FROM ongs WHERE id = ?");
    $stmtOng->execute([$ong_id]);
    $ong = $stmtOng->fetch(PDO::FETCH_ASSOC);

    if (!$ong) {
        echo "<script>alert('ONG não encontrada!'); window.location.href='index.php';</script>";
        exit;
    }
} catch (PDOException $e) {
    die("Erro ao carregar perfil da ONG: " . $e->getMessage());
}

// 4. BUSCA QUESTIONÁRIO DO ADOTANTE LOGADO PARA CÁLCULO REAL
$adotante_id = $_SESSION['adotante_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;
$questionario = null;

if ($adotante_id) {
    $stmtQ = $pdo->prepare("SELECT * FROM questionarios WHERE adotante_id = :adotante_id ORDER BY id DESC LIMIT 1");
    $stmtQ->execute(['adotante_id' => $adotante_id]);
    $questionario = $stmtQ->fetch(PDO::FETCH_ASSOC);
}

// ALGORITMO REAL DE COMPATIBILIDADE (SINCRONIZADO COM DETALHES.PHP)
function calcularCompatibilidadeReal($pet, $questionario) {
    if (!$questionario) return null;

    $porcentagem_match = 0;
    $incompatibilidadeCritica = false;

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
    $ondeMora    = strtolower($questionario['onde_mora'] ?? 'apartamento');
    $areaExterna = (int)($questionario['tem_area_externa'] ?? 0);
    $horasFora   = (int)($questionario['horas_fora_casa'] ?? 8);
    $experiencia = strtolower($questionario['experiencia'] ?? 'primeira_vez');
    $temCriancas = (int)($questionario['tem_criancas'] ?? 0);
    $temAnimais  = (int)($questionario['tem_outros_animais'] ?? 0);
    $atividade   = strtolower($questionario['nivel_atividade_fisica'] ?? 'moderado');

    // ==========================================
    // 1º: HORAS FORA DE CASA (20%)
    // ==========================================
    if ($isFilhote) {
        if ($horasFora <= 4) {
            $porcentagem_match += 20;
        } elseif ($horasFora <= 6) {
            $porcentagem_match += 10;
        }
    } else {
        if ($horasFora <= 6) {
            $porcentagem_match += 20;
        } elseif ($horasFora <= 10) {
            $porcentagem_match += 14;
        } else {
            $porcentagem_match += 6;
        }
    }

    // ==========================================
    // 2º: TIPO DE MORADIA (18%)
    // ==========================================
    if ($isGato || $portePet === 'pequeno') {
        $porcentagem_match += 18;
    } elseif (in_array($portePet, ['médio', 'medio'])) {
        if (in_array($ondeMora, ['casa_com_quintal', 'sitio'])) {
            $porcentagem_match += 18;
        } elseif ($ondeMora === 'casa_sem_quintal') {
            $porcentagem_match += 14;
        } else {
            $porcentagem_match += 10;
        }
    } else {
        if (in_array($ondeMora, ['casa_com_quintal', 'sitio'])) {
            $porcentagem_match += 18;
        } elseif ($ondeMora === 'casa_sem_quintal') {
            $porcentagem_match += 8;
        } else {
            $porcentagem_match += 2;
        }
    }

    // ==========================================
    // 3º: ÁREA EXTERNA (15%)
    // ==========================================
    if ($isGato) {
        $porcentagem_match += 15;
    } elseif (in_array($portePet, ['grande', 'médio', 'medio'])) {
        if ($areaExterna === 1) {
            $porcentagem_match += 15;
        } else {
            $porcentagem_match += 4;
        }
    } else {
        if ($areaExterna === 1) {
            $porcentagem_match += 15;
        } else {
            $porcentagem_match += 10;
        }
    }

    // ==========================================
    // 4º: OUTROS ANIMAIS EM CASA (12%)
    // ==========================================
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
        } else {
            $porcentagem_match += 12;
        }
    } else {
        $porcentagem_match += 12;
    }

    // ==========================================
    // 5º: CRIANÇAS EM CASA (12%)
    // ==========================================
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
        } else {
            $porcentagem_match += 12;
        }
    } else {
        $porcentagem_match += 12;
    }

    // ==========================================
    // 6º: NÍVEL DE ATIVIDADE FÍSICA (12%)
    // ==========================================
    $petAltaEnergia = ($isFilhote || $portePet === 'grande' || in_array($temperamentoPet, ['brincalhão', 'agitado']));
    if ($petAltaEnergia) {
        if ($atividade === 'ativo') {
            $porcentagem_match += 12;
        } elseif ($atividade === 'moderado') {
            $porcentagem_match += 7;
        } else {
            $porcentagem_match += 2;
        }
    } else {
        if (in_array($atividade, ['sedentario', 'moderado'])) {
            $porcentagem_match += 12;
        } else {
            $porcentagem_match += 9;
        }
    }

    // ==========================================
    // 7º: EXPERIÊNCIA PRÉVIA (11%)
    // ==========================================
    $exigeExperiencia = ($portePet === 'grande' || $isFilhote || in_array($temperamentoPet, ['dominante', 'antissocial', 'tímido']));
    if ($exigeExperiencia) {
        if (in_array($experiencia, ['experiente', 'ja_teve'])) {
            $porcentagem_match += 11;
        } else {
            $porcentagem_match += 2;
        }
    } else {
        $porcentagem_match += 11;
    }

    // === REGRA DE SEGURANÇA MÁXIMA (TETO RIGOROSO) ===
    if ($incompatibilidadeCritica) {
        $porcentagem_match = min($porcentagem_match, 30);
    } else {
        $porcentagem_match = min(100, max(0, $porcentagem_match));
    }

    return $porcentagem_match;
}

// 5. Busca os animais cadastrados por esta ONG
try {
    $stmtAnimais = $pdo->prepare("SELECT * FROM animais WHERE ong_id = ? ORDER BY id DESC");
    $stmtAnimais->execute([$ong_id]);
    $animais = $stmtAnimais->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $animais = [];
}

// Aplica a compatibilidade real em cada animal
foreach ($animais as &$pet) {
    $pet['compatibilidade'] = calcularCompatibilidadeReal($pet, $questionario);
}
unset($pet);

// Formatação do link do Instagram
$instagramUrl = '';
if (!empty($ong['instagram'])) {
    $insta = trim($ong['instagram']);
    if (strpos($insta, 'http') === 0) {
        $instagramUrl = $insta;
    } else {
        $instaHandle = ltrim($insta, '@');
        $instagramUrl = "https://instagram.com/" . $instaHandle;
    }
}

// Puxa a descrição gravada da ONG
$descricaoOng = $ong['descricao'] ?? $ong['sobre'] ?? $ong['biografia'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($ong['nome_instituicao']); ?> - AdotaPet</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="perfil_ong.css?v=<?= time(); ?>">
</head>
<body>

    <!-- NAVBAR PADRONIZADA -->
    <header class="navbar">
      <a href="index.php" class="logo">🐾 AdotaPet</a>

      <nav class="menu" id="menu-navegacao">
        <a href="index.php">Início</a>
        <a href="adotar.php">Adotar</a>
        <a href="mapa.php">Mapa</a> 
        <a href="candidaturas.php">Candidaturas</a>
        <a href="meu_perfil.php"><?= htmlspecialchars($labelPerfil) ?></a>
        <?php if (isset($_SESSION['id']) || isset($_SESSION['ong_id']) || isset($_SESSION['adotante_id'])): ?>
            <a href="logout.php" class="btn-logout" title="Sair">Sair</a>
        <?php else: ?>
            <a href="login/login.php" class="btn-login">Entrar</a>
        <?php endif; ?>
      </nav>
    </header>

    <main class="main-container">

        <!-- CABEÇALHO DO PERFIL -->
        <div class="ong-header-card">
            <?php if (!empty($ong['foto']) && file_exists(__DIR__ . '/uploads/' . $ong['foto'])): ?>
                <img src="uploads/<?= htmlspecialchars($ong['foto']); ?>" alt="<?= htmlspecialchars($ong['nome_instituicao']); ?>" class="ong-avatar">
            <?php else: ?>
                <div class="ong-avatar">🏛️</div>
            <?php endif; ?>

            <div class="ong-details">
                <h1 class="ong-title"><?= htmlspecialchars($ong['nome_instituicao']); ?></h1>

                <div class="ong-meta">
                    <?php if (!empty($ong['cidade']) && !empty($ong['estado'])): ?>
                        <span>📍 <?= htmlspecialchars($ong['cidade']) . ' - ' . htmlspecialchars($ong['estado']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($ong['cnpj'])): ?>
                        <span>📄 CNPJ: <?= htmlspecialchars($ong['cnpj']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="ong-actions">
                    <?php if ($instagramUrl): ?>
                        <a href="<?= $instagramUrl; ?>" target="_blank" class="btn-acao btn-insta">
                            📸 Instagram
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($ong['chave_pix'])): ?>
                        <button onclick="copiarPix('<?= htmlspecialchars($ong['chave_pix']); ?>')" class="btn-acao btn-pix">
                            💛 Copiar Chave PIX
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- CONTAINER EXCLUSIVO DE DESCRIÇÃO -->
        <?php if (!empty($descricaoOng)): ?>
            <div class="ong-description-card">
                <h3>📖 Sobre a Instituição</h3>
                <p><?= nl2br(htmlspecialchars($descricaoOng)); ?></p>
            </div>
        <?php endif; ?>

        <!-- BLOCOS DE INFORMAÇÕES DETALHADAS -->
        <div class="info-grid">
            
            <div class="info-card">
                <h3>📍 Endereço e Contato</h3>
                <p><strong>Telefone / WhatsApp:</strong> <?= !empty($ong['telefone']) ? htmlspecialchars($ong['telefone']) : 'Não informado'; ?></p>
                <?php if (!empty($ong['logradouro'])): ?>
                    <p><strong>Rua/Av:</strong> <?= htmlspecialchars($ong['logradouro']); ?>, <?= htmlspecialchars($ong['numero'] ?? 'S/N'); ?></p>
                    <p><strong>Bairro:</strong> <?= htmlspecialchars($ong['bairro'] ?? '-'); ?></p>
                    <p><strong>CEP:</strong> <?= htmlspecialchars($ong['cep'] ?? '-'); ?></p>
                <?php else: ?>
                    <p>Endereço completo não disponibilizado.</p>
                <?php endif; ?>
            </div>

            <div class="info-card">
                <h3>🕒 Atendimento e Acolhimento</h3>
                <p><strong>Horário:</strong> <?= htmlspecialchars($ong['horario_atendimento'] ?? 'Não informado'); ?></p>
                <p><strong>Raio de Atuação:</strong> <?= htmlspecialchars($ong['raio_atuacao'] ?? 'Não informado'); ?></p>
                <p><strong>Capacidade de Abrigados:</strong> <?= htmlspecialchars($ong['capacidade_abrigados'] ?? 'Não informada'); ?></p>
            </div>

            <?php if (!empty($ong['chave_pix'])): ?>
            <div class="info-card">
                <h3>🤝 Apoie esta ONG</h3>
                <p>Contribua diretamente com o tratamento e resgate dos animais através do PIX:</p>
                <strong class="pix-key"><?= htmlspecialchars($ong['chave_pix']); ?></strong>
            </div>
            <?php endif; ?>

        </div>

        <!-- ANIMAIS CADASTRADOS DA ONG -->
        <h2 class="section-title">🐾 Animais Disponíveis para Adoção (<?= count($animais); ?>)</h2>

        <div class="cards-grid">
            <?php if (!empty($animais)): ?>
                <?php foreach ($animais as $pet): ?>
                    <a href="detalhes.php?id=<?= $pet['id']; ?>" class="card" data-id="<?= $pet['id']; ?>" data-compatibilidade="<?= $pet['compatibilidade'] ?? 0; ?>">
                        <div class="card-img-box">
                            <span class="badge-disponivel"><?= htmlspecialchars($pet['status'] ?? 'Disponível'); ?></span>
                            
                            <?php if ($pet['compatibilidade'] !== null): ?>
                                <span class="badge-compatibilidade"><?= $pet['compatibilidade']; ?>%</span>
                            <?php endif; ?>

                            <?php if (!empty($pet['foto_url'])): ?>
                                <img src="<?= htmlspecialchars($pet['foto_url']); ?>" alt="<?= htmlspecialchars($pet['nome']); ?>">
                            <?php elseif (!empty($pet['foto']) && file_exists(__DIR__ . '/uploads/' . $pet['foto'])): ?>
                                <img src="uploads/<?= htmlspecialchars($pet['foto']); ?>" alt="<?= htmlspecialchars($pet['nome']); ?>">
                            <?php else: ?>
                                <div class="no-photo-placeholder">Sem Foto</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-info">
                            <div class="card-header-info">
                                <h2 class="pet-nome"><?= htmlspecialchars($pet['nome']); ?></h2>
                                <p class="pet-raca"><?= htmlspecialchars($pet['especie_raca'] ?? ''); ?></p>
                            </div>

                            <div class="pet-tags">
                                <span class="tag-item highlight">🐾 <?= htmlspecialchars($pet['porte'] ?? 'Porte N/I'); ?></span>
                                <span class="tag-item">🎂 <?= htmlspecialchars($pet['idade_estimada'] ?? 'Idade N/I'); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="sem-animais">
                    <p>Esta ONG ainda não possui animais cadastrados para adoção no momento.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <script>
        function copiarPix(chave) {
            navigator.clipboard.writeText(chave).then(function() {
                alert('Chave PIX copiada para a área de transferência!');
            }, function(err) {
                alert('Erro ao copiar chave PIX: ' + chave);
            });
        }
    </script>

</body>
</html>