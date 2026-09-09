<?php
require_once 'config/auth.php'; // Proteção de sessão
require_once 'config/db.php';   // Conexão PDO

$ong_id = $_SESSION['usuario_id'] ?? $_SESSION['ong_id'] ?? null;

// Busca os animais filtrados pela ong_id
$stmt = $pdo->prepare("SELECT * FROM animais WHERE ong_id = ? ORDER BY id DESC");
$stmt->execute([$ong_id]);
$animais = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Animais - AdotaPet</title>
    <!-- Importação do CSS da Navbar e da Tela de Animais -->
    <link rel="stylesheet" href="navbar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="meus_animais.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- NAVBAR PADRONIZADA -->
    <header class="navbar">
        <a href="index.php" class="logo">🐾 AdotaPet</a>
        <nav class="menu">
            <a href="index.php">Início</a>
            <a href="meus_animais.php" class="active">Meus Animais</a>
            <a href="candidaturas_recebidas.php">Candidaturas Recebidas</a>
            <a href="minha_ong.php">Meu Perfil</a>
            <a href="logout.php" class="btn-logout">Sair</a>
        </nav>
    </header>

    <main>
        <div class="container">
            <h2>Animais Cadastrados</h2>
            
            <?php if (empty($animais)): ?>
                <div class="empty-msg">
                    Nenhum animal cadastrado ainda.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nome</th>
                            <th>Espécie / Raça</th>
                            <th>Idade</th>
                            <th>Porte</th>
                            <th>Vacinação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($animais as $animal): ?>
                            <tr>
                                <td><img src="<?= htmlspecialchars($animal['foto_url']) ?>" alt="Foto de <?= htmlspecialchars($animal['nome']) ?>" class="img-thumb"></td>
                                <td><strong><?= htmlspecialchars($animal['nome']) ?></strong></td>
                                <td><?= htmlspecialchars($animal['especie_raca']) ?></td>
                                <td><?= htmlspecialchars($animal['idade_estimada']) ?></td>
                                <td><?= htmlspecialchars($animal['porte']) ?></td>
                                <td><?= htmlspecialchars($animal['carteira_vacinacao']) ?></td>
                                <td>
                                    <form action="deletar_animal.php" method="POST" onsubmit="return confirm('Deseja realmente apagar este animal?');" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $animal['id'] ?>">
                                        <button type="submit" class="btn-deletar" title="Excluir">❌</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="actions">
                <?php if (!empty($animais)): ?>
                    <form action="limpar_animais.php" method="POST" onsubmit="return confirm('Deseja realmente apagar todos os animais cadastrados?');">
                        <button type="submit" class="btn btn-limpar">🗑️ Apagar Tudo</button>
                    </form>
                <?php endif; ?>
                <a href="cadastrar_animal.php" class="btn btn-adicionar">＋ Cadastrar Novo</a>
            </div>
        </div>
    </main>

</body>
</html>