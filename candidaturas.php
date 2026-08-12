<?php
// Inclui a verificação de autenticação e a conexão com o banco
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Captura o ID do adotante logado
$adotante_id = $_SESSION['adotante_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;
//$adotante_id = $_SESSION['adotante_id'] ?? $_SESSION['id'] ?? null;

if (!$adotante_id) {
    header("Location: login.php");
    exit;
}

// ==========================================================================
// 1. CANCELAR CANDIDATURA (POST)
// ==========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cancelar') {
    $candidatura_id = filter_input(INPUT_POST, 'candidatura_id', FILTER_VALIDATE_INT);

    if ($candidatura_id) {
        $stmtDel = $pdo->prepare("DELETE FROM candidaturas WHERE id = :id AND adotante_id = :adotante_id");
        $stmtDel->execute([
            'id'          => $candidatura_id,
            'adotante_id' => $adotante_id
        ]);
    }

    header("Location: candidaturas.php");
    exit;
}

// ==========================================================================
// 2. CONSULTA DAS CANDIDATURAS COM DADOS DOS ANIMAIS
// ==========================================================================
$sql = "
    SELECT 
        c.id,
        c.status_candidatura,
        c.compatibilidade,
        c.data_envio,
        a.id AS animal_id,
        a.nome AS pet_nome,
        a.foto_url AS pet_foto,
        a.especie_raca
    FROM candidaturas c
    INNER JOIN animais a ON c.animal_id = a.id
    WHERE c.adotante_id = :adotante_id
    ORDER BY c.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['adotante_id' => $adotante_id]);
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

    <!-- NAVBAR -->
    <header class="navbar">
      <div class="logo" onclick="window.location.href='index.php'" style="cursor: pointer;">
        🐾 <span>Adota<span class="logo-highlight">Pet</span></span>
      </div>

      <nav class="menu" id="menu-navegacao">
        <a href="index.php" class="nav-item">Início</a>
        <a href="adotar.php" class="nav-item">Adotar</a>
        <a href="mapa.php" class="nav-item">Mapa</a> 
        <a href="candidaturas.php" class="nav-item active-pill">Candidaturas</a>
        <a href="meu_perfil.php" class="nav-item"><?= htmlspecialchars($labelPerfil) ?></a>
        <a href="logout.php" class="nav-item nav-logout" title="Sair">Sair</a>
      </nav>
    </header>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="content-wrapper">
        <h1 class="page-title">Minhas Candidaturas</h1>
        <p class="subtitle">Acompanhe o status de suas solicitações de adoção</p>

        <div id="lista-candidaturas" class="candidaturas-container">
            <?php if (empty($candidaturas)): ?>
                <p class="no-data">Nenhuma candidatura enviada até o momento.</p>
            <?php else: ?>
                <?php foreach ($candidaturas as $item): ?>
                    <?php 
                        $statusClass = 'status-pendente';
                        if (strtolower($item['status_candidatura']) === 'aprovado') {
                            $statusClass = 'status-aprovado';
                        } elseif (strtolower($item['status_candidatura']) === 'recusado') {
                            $statusClass = 'status-recusado';
                        }

                        $dataEnvio = date('d/m/Y \à\s H:i', strtotime($item['data_envio']));
                    ?>
                    <div class="candidatura-card">
                        <div class="card-left">
                            <div class="match-badge"><?= htmlspecialchars($item['compatibilidade'] ?? '70%') ?></div>

                            <img src="<?= htmlspecialchars($item['pet_foto'] ?? 'img/default-pet.png') ?>" alt="Foto de <?= htmlspecialchars($item['pet_nome']) ?>" class="pet-thumb" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-right: 15px;">

                            <div class="pet-info">
                                <div class="pet-title-row">
                                    <h3 class="pet-name"><?= htmlspecialchars($item['pet_nome']) ?></h3>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= htmlspecialchars($item['status_candidatura']) ?>
                                    </span>
                                </div>
                                <span class="sent-date">Enviada em <?= $dataEnvio ?></span>
                            </div>
                        </div>

                        <div class="card-actions">
                            <a href="mensagens.php?candidatura_id=<?= $item['id'] ?>" class="btn-circle" title="Mensagens">💬</a>

                            <form method="POST" action="candidaturas.php" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja cancelar esta solicitação?');">
                                <input type="hidden" name="acao" value="cancelar">
                                <input type="hidden" name="candidatura_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn-circle btn-cancelar" title="Cancelar Solicitação">❌</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>