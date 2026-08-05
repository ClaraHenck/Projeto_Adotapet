<?php
// Inclui a verificação de autenticação e a conexão com o banco
require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/db.php";

$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['adotante_id'] ?? $_SESSION['id'] ?? null;

// ==========================================================================
// 1. CANCELAR CANDIDATURA (POST)
// ==========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cancelar') {
    $candidatura_id = intval($_POST['candidatura_id']);

    $stmtDel = $pdo->prepare("
        DELETE FROM candidaturas 
        WHERE id = ? AND (usuario_id = ? OR adotante_id = ?)
    ");
    $stmtDel->execute([$candidatura_id, $usuario_id, $usuario_id]);

    header("Location: candidaturas.php");
    exit;
}

// ==========================================================================
// 2. CONSULTA DAS CANDIDATURAS (UNINDO COM A TABELA ANIMAIS)
// ==========================================================================
$sql = "
    SELECT 
        c.id,
        COALESCE(a.nome, c.pet_nome, 'Pet') AS pet_nome,
        COALESCE(c.status, c.status_candidatura, 'Pendente') AS status,
        COALESCE(c.compatibilidade, '90%') AS compatibilidade,
        COALESCE(c.data_envio, NOW()) AS data_envio
    FROM candidaturas c
    LEFT JOIN animais a ON (c.pet_id = a.id OR c.animal_id = a.id)
    WHERE (c.usuario_id = :uid OR c.adotante_id = :uid)
    ORDER BY c.id DESC
";

// INSERT
$sql = "INSERT INTO candidaturas (usuario_id, pet_id, status) VALUES (?, ?, 'Pendente')";

// SELECT
$sql = "SELECT * FROM candidaturas WHERE usuario_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute(['uid' => $usuario_id]);
$candidaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labelPerfil = (isset($_SESSION['pode_cadastrar']) && $_SESSION['pode_cadastrar'] == 1) ? "Minha ONG" : "Perfil";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdotaPet - Minhas Candidaturas</title>
    
    <link rel="stylesheet" href="candidaturas.css?v=<?= time(); ?>">
</head>
<body>

    <!-- CABEÇALHO PADRÃO -->
    <header class="navbar">
      <div class="logo">
        <svg class="logo-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
        </svg>
        <span>Adota<span class="logo-highlight">Pet</span></span>
      </div>

      <nav class="menu" id="menu-navegacao">
        <a href="index.php" class="nav-item">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
            Início
        </a>
        <a href="adotar.php" class="nav-item">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            Adotar
        </a>
        <a href="mapa.php" class="nav-item">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            Mapa
        </a> 
        <a href="candidaturas.php" class="nav-item active-pill">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            Candidaturas
        </a>
        <a href="mensagens.php" class="nav-item">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            Mensagens
        </a>
        <a href="meu_perfil.php" class="nav-item">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <?= htmlspecialchars($labelPerfil) ?>
        </a>
        <a href="logout.php" class="nav-item nav-logout" title="Sair">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        </a>
      </nav>
    </header>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="content-wrapper">
        <h1 class="page-title">Minhas Candidaturas</h1>
        <p class="subtitle">Acompanhe o status de suas adoções</p>

        <div id="lista-candidaturas" class="candidaturas-container">
            <?php if (empty($candidaturas)): ?>
                <p class="no-data">Nenhuma candidatura enviada até o momento.</p>
            <?php else: ?>
                <?php foreach ($candidaturas as $item): ?>
                    <div class="candidatura-card">
                        <div class="card-left">
                            <div class="match-badge">
                                <?= htmlspecialchars($item['compatibilidade']) ?>
                            </div>

                            <div class="pet-info">
                                <div class="pet-title-row">
                                    <h3 class="pet-name"><?= htmlspecialchars($item['pet_nome']) ?></h3>
                                    <span class="status-badge status-pendente">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <?= htmlspecialchars($item['status']) ?>
                                    </span>
                                </div>
                                <span class="sent-date">Enviada em <?= date('d/m/Y', strtotime($item['data_envio'])) ?></span>
                            </div>
                        </div>

                        <div class="card-actions">
                            <a href="mensagens.php?candidatura_id=<?= $item['id'] ?>" class="btn-circle" title="Mensagens">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            </a>

                            <form method="POST" action="candidaturas.php" style="display:inline;" onsubmit="return confirm('Deseja cancelar esta solicitação?');">
                                <input type="hidden" name="acao" value="cancelar">
                                <input type="hidden" name="candidatura_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn-circle" title="Cancelar Solicitação">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>