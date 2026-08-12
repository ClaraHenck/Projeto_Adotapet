<?php
// Carrega a conexão com o banco de dados de forma segura
require_once __DIR__ . "../config/db.php";
require_once __DIR__ . "../config/auth.php";

$logado = isset($_SESSION["usuario_id"]);
$usuario_nome = $_SESSION["usuario_nome"] ?? "";
$tipoUsuario = "";

// Define o tipo de usuário baseado nas permissões salvas no login
if ($logado) {
    $tipoUsuario = (isset($_SESSION["pode_cadastrar"]) && $_SESSION["pode_cadastrar"] == 1) ? "ong" : "adotante";
}

// Captura filtros enviados pelo formulário de busca
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$porte  = isset($_GET['porte']) ? $_GET['porte'] : '';
$idade  = isset($_GET['idade']) ? $_GET['idade'] : '';

// Monta a Query SQL baseada no modelo de tabela 'animais'
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

if (!empty($idade)) {
    $sql .= " AND idade_estimada LIKE ?";
    $params[] = "%$idade%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$animais = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = count($animais);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdotaPet - Encontre seu Companheiro</title>
    <link rel="stylesheet" href="adotar.css">
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
          <a href="mapa.html" id="link-mapa">Mapa</a> 
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
                    $linkHref = ($tipoUsuario === "ong") ? "minha_ong.php" : "meu_perfil.";
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

        <div class="cards">
            <?php if ($total > 0) { ?>
                <?php foreach($animais as $pet) { ?>
                    <div class="card" data-id="<?= $pet['id']; ?>">
                        <div class="area-imagem-card">
                            <img src="<?= htmlspecialchars($pet['foto_url']); ?>" alt="<?= $pet['nome']; ?>">
                        </div>
                        <h2><?= $pet['nome']; ?></h2>
                        <p><?= htmlspecialchars($pet['especie_raca']); ?></p>
                        <div class="pet-idade-badge"><?= htmlspecialchars($pet['idade_estimada']); ?></div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p class="empty-state">Nenhum animal cadastrado com os filtros selecionados.</p>
            <?php } ?>
        </div>
    </main>

    <script>
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', () => {
                const petId = card.getAttribute('data-id');
                const petNome = card.querySelector('h2').innerText;
                
                localStorage.setItem('petSelecionadoNome', petNome);
                window.location.href = `detalhes.php?id=${petId}`;
            });
        });
    </script>
</body>
</html>