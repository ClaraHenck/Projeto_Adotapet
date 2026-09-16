<?php
// Carrega a conexão com o banco de dados e autenticação
require_once __DIR__ . "../config/db.php";
require_once __DIR__ . "../config/auth.php";

$logado = isset($_SESSION["usuario_id"]);
$usuario_nome = $_SESSION["usuario_nome"] ?? "";
$tipoUsuario = "";

if ($logado) {
    $tipoUsuario = (isset($_SESSION["pode_cadastrar"]) && $_SESSION["pode_cadastrar"] == 1) ? "ong" : "adotante";
}

// Captura filtros enviados pelo formulário
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$porte  = isset($_GET['porte']) ? $_GET['porte'] : '';
$idade  = isset($_GET['idade']) ? $_GET['idade'] : '';
$ordem  = isset($_GET['ordem']) ? $_GET['ordem'] : '';

// Função para converter qualquer texto de idade em categoria
function categorizarIdade($idadeStr) {
    if (empty($idadeStr)) return 'Adulto';
    
    $str = mb_strtolower(trim($idadeStr));

    if (strpos($str, 'filhote') !== false) return 'Filhote';
    if (strpos($str, 'idoso') !== false) return 'Idoso';
    if (strpos($str, 'adulto') !== false) return 'Adulto';

    preg_match('/(\d+)/', $str, $matches);
    $num = isset($matches[1]) ? (int)$matches[1] : 0;

    if (strpos($str, 'mê') !== false || strpos($str, 'me') !== false) {
        return ($num <= 12) ? 'Filhote' : 'Adulto';
    }

    if ($num <= 1) return 'Filhote';
    if ($num >= 8) return 'Idoso';
    return 'Adulto';
}

// BUSCA QUESTIONÁRIO DO ADOTANTE LOGADO
$adotante_id = $_SESSION['adotante_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;
$questionario = null;

if ($adotante_id) {
    $stmtQ = $pdo->prepare("SELECT * FROM questionarios WHERE adotante_id = :adotante_id ORDER BY id DESC LIMIT 1");
    $stmtQ->execute(['adotante_id' => $adotante_id]);
    $questionario = $stmtQ->fetch(PDO::FETCH_ASSOC);
}

// ALGORITMO REAL DE COMPATIBILIDADE (Idêntico ao detalhes.php)
function calcularCompatibilidadeReal($pet, $questionario) {
    if (!$questionario) return null;

    $porcentagem_match = 0;

    $portePet      = strtolower(trim($pet['porte'] ?? 'médio'));
    $especieRaca   = strtolower(trim($pet['especie_raca'] ?? ''));
    $descricaoPet  = strtolower(trim($pet['descricao'] ?? ''));
    $idadeTexto    = strtolower(trim($pet['idade_estimada'] ?? ''));
    
    $isGato        = (str_contains($especieRaca, 'gato') || str_contains($especieRaca, 'felina'));
    preg_match('/\d+/', $idadeTexto, $matches);
    $idadeAnos     = isset($matches[0]) ? intval($matches[0]) : 3;
    $isFilhote     = ($idadeAnos <= 1);

    $ondeMora     = strtolower($questionario['onde_mora'] ?? 'apartamento');
    $areaExterna  = (int)($questionario['tem_area_externa'] ?? 0);
    $horasFora    = (int)($questionario['horas_fora_casa'] ?? 8);
    $experiencia  = strtolower($questionario['experiencia'] ?? 'primeira_vez');
    $temCriancas  = (int)($questionario['tem_criancas'] ?? 0);
    $temAnimais   = (int)($questionario['tem_outros_animais'] ?? 0);
    $atividade    = strtolower($questionario['nivel_atividade_fisica'] ?? 'moderado');

    // 1º: HORAS FORA DE CASA (20%)
    if ($isFilhote) {
        if ($horasFora <= 4) $porcentagem_match += 20;
        elseif ($horasFora <= 6) $porcentagem_match += 10;
    } else {
        if ($horasFora <= 6) $porcentagem_match += 20;
        elseif ($horasFora <= 10) $porcentagem_match += 14;
        else $porcentagem_match += 6;
    }

    // 2º: TIPO DE MORADIA (18%)
    if ($isGato || $portePet === 'pequeno') {
        $porcentagem_match += 18;
    } elseif (in_array($portePet, ['médio', 'medio'])) {
        if (in_array($ondeMora, ['casa_com_quintal', 'sitio'])) $porcentagem_match += 18;
        elseif ($ondeMora === 'casa_sem_quintal') $porcentagem_match += 14;
        else $porcentagem_match += 10;
    } else {
        if (in_array($ondeMora, ['casa_com_quintal', 'sitio'])) $porcentagem_match += 18;
        elseif ($ondeMora === 'casa_sem_quintal') $porcentagem_match += 8;
        else $porcentagem_match += 2;
    }

    // 3º: ÁREA EXTERNA (15%)
    if ($isGato) {
        $porcentagem_match += 15;
    } elseif ($portePet === 'grande' || $portePet === 'médio' || $portePet === 'medio') {
        if ($areaExterna === 1) $porcentagem_match += 15;
        else $porcentagem_match += 4;
    } else {
        if ($areaExterna === 1) $porcentagem_match += 15;
        else $porcentagem_match += 10;
    }

    // 4º: OUTROS ANIMAIS EM CASA (12%)
    $petRestritoAnimais = (str_contains($descricaoPet, 'único') || str_contains($descricaoPet, 'nao gosta de caes') || str_contains($descricaoPet, 'não gosta de gatos'));
    if ($temAnimais === 1) {
        if (!$petRestritoAnimais) $porcentagem_match += 12;
    } else {
        $porcentagem_match += 12;
    }

    // 5º: CRIANÇAS EM CASA (12%)
    $petAriscoOuBravo = (str_contains($descricaoPet, 'bravo') || str_contains($descricaoPet, 'arisco') || str_contains($descricaoPet, 'não recomendado para crianças'));
    if ($temCriancas === 1) {
        if (!$petAriscoOuBravo) $porcentagem_match += 12;
    } else {
        $porcentagem_match += 12;
    }

    // 6º: NÍVEL DE ATIVIDADE FÍSICA (12%)
    $petAltaEnergia = ($isFilhote || $portePet === 'grande' || str_contains($descricaoPet, 'ativo') || str_contains($descricaoPet, 'brincalhão'));
    if ($petAltaEnergia) {
        if ($atividade === 'ativo') $porcentagem_match += 12;
        elseif ($atividade === 'moderado') $porcentagem_match += 7;
        else $porcentagem_match += 2;
    } else {
        if ($atividade === 'sedentario' || $atividade === 'moderado') $porcentagem_match += 12;
        else $porcentagem_match += 9;
    }

    // 7º: EXPERIÊNCIA PRÉVIA (11%)
    $exigeExperiencia = ($portePet === 'grande' || $isFilhote || str_contains($descricaoPet, 'especial') || str_contains($descricaoPet, 'trauma'));
    if ($exigeExperiencia) {
        if ($experiencia === 'experiente' || $experiencia === 'ja_teve') $porcentagem_match += 11;
        else $porcentagem_match += 2;
    } else {
        $porcentagem_match += 11;
    }

    return min(100, max(0, $porcentagem_match));
}

// Query SQL para buscar animais
$sql = "SELECT * FROM animais WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (nome LIKE ? OR especie_raca LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($porte)) {
    $sql .= " AND porte = ?";
    $params[] = $porte;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$animais = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Atribui categoria de idade e compatibilidade calculada com o questionário real
foreach ($animais as &$pet) {
    $pet['categoria_idade'] = categorizarIdade($pet['idade_estimada'] ?? '');
    $pet['compatibilidade'] = calcularCompatibilidadeReal($pet, $questionario);
}
unset($pet);

// Aplica o filtro de idade caso selecionado no form
if (!empty($idade)) {
    $animais = array_values(array_filter($animais, function($pet) use ($idade) {
        return strcasecmp($pet['categoria_idade'], $idade) === 0;
    }));
}

// Ordenação Decrescente por Compatibilidade
usort($animais, function($a, $b) {
    return ($b['compatibilidade'] ?? 0) <=> ($a['compatibilidade'] ?? 0);
});

$total = count($animais);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdotaPet - Encontre seu Companheiro</title>
    <link rel="stylesheet" href="adotar1.css">
    <link rel="stylesheet" href="navbar.css">
</head>
<body>

    <header class="navbar">
      <div class="left-brand-box">
        <div class="logo">🐾 AdotaPet</div>
      </div>

      <nav class="menu" id="menu-navegacao">
        <a href="index.php">Início</a>
        <a href="adotar.php" class="active">Adotar</a>
        
        <?php if ($logado): ?>
          <a href="mapa.php" id="link-mapa">Mapa</a> 
          <a href="candidaturas.php" id="link-candidaturas">
            <?php echo ($tipoUsuario === "ong") ? "Candidaturas Recebidas" : "Candidaturas"; ?>
          </a>
        <?php endif; ?>

        <div id="area-usuario-nav">
            <?php if (!$logado): ?>
                <a href="../login/login.php" class="btn-nav-login">Entrar</a>
                <a href="../login/cadastrar.php" class="btn-nav-cadastro">Cadastrar-se</a>
            <?php else: ?>
                <?php 
                    $linkHref = ($tipoUsuario === "ong") ? "minha_ong.php" : "meu_perfil.php";
                    $textoPerfil = ($tipoUsuario === "ong") ? "MINHA ONG" : "MEU PERFIL";
                ?>
                <a href="<?php echo $linkHref; ?>" class="perfil-link-container">
                    <span><?php echo $textoPerfil; ?></span>
                </a>
                <a href="logout.php">Sair</a>
            <?php endif; ?>
        </div>
      </nav>
    </header>

    <main class="container">
        <div class="header-titulo">
            <h1>Encontre seu Companheiro</h1>
            <p><?= $total; ?> animais encontrados para adoção</p>
        </div>

        <form method="GET" action="adotar.php" class="search-container">
            <input type="text" name="search" class="search-input" placeholder="Buscar por nome ou raça..." value="<?= htmlspecialchars($search); ?>">
            
            <select name="porte" class="filter-select">
                <option value="">Porte (Todos)</option>
                <option value="Pequeno" <?php echo ($porte == 'Pequeno') ? 'selected' : ''; ?>>Pequeno</option>
                <option value="Médio" <?php echo ($porte == 'Médio') ? 'selected' : ''; ?>>Médio</option>
                <option value="Grande" <?php echo ($porte == 'Grande') ? 'selected' : ''; ?>>Grande</option>
            </select>

            <select name="idade" class="filter-select">
                <option value="">Idade (Todas)</option>
                <option value="Filhote" <?php echo ($idade == 'Filhote') ? 'selected' : ''; ?>>Filhote</option>
                <option value="Adulto" <?php echo ($idade == 'Adulto') ? 'selected' : ''; ?>>Adulto</option>
                <option value="Idoso" <?php echo ($idade == 'Idoso') ? 'selected' : ''; ?>>Idoso</option>
            </select>

            

            <button type="submit" class="btn-filtrar">Filtrar</button>
        </form>

        <div class="cards-grid">
    <?php if ($total > 0) { ?>
        <?php foreach($animais as $pet) { ?>
        <div class="card" data-id="<?= $pet['id']; ?>" data-compatibilidade="<?= $pet['compatibilidade'] ?? 0; ?>">
    <div class="card-img-box">
        <span class="badge-disponivel">Disponível</span>
        
        <?php if ($pet['compatibilidade'] !== null): ?>
            <span class="badge-compatibilidade"><?= $pet['compatibilidade']; ?>%</span>
        <?php endif; ?>

        <img src="<?= htmlspecialchars($pet['foto_url']); ?>" alt="<?= htmlspecialchars($pet['nome']); ?>">
    </div>
    
    <div class="card-info">
        <div class="card-header-info">
            <h2 class="pet-nome"><?= htmlspecialchars($pet['nome']); ?></h2>
            <p class="pet-raca"><?= htmlspecialchars($pet['especie_raca']); ?></p>
        </div>

        <div class="pet-tags">
            <span class="tag-item highlight">🐾 <?= htmlspecialchars($pet['porte'] ?? 'Porte N/I'); ?></span>
            <span class="tag-item">🎂 <?= htmlspecialchars($pet['idade_estimada'] ?? 'Idade N/I'); ?></span>
        </div>
    </div>
</div>
        <?php } ?>
    <?php } else { ?>
        <p class="no-data">Nenhum animal cadastrado com os filtros selecionados.</p>
    <?php } ?>
    </div>
    </main>

    <script src="filtro.js"></script>
</body>
</html>