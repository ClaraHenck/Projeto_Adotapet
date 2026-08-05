<?php
require_once 'config/auth.php'; // Proteção de sessão[cite: 4]
require_once 'config/db.php';   // Conexão PDO[cite: 5]

$ong_id = $_SESSION['usuario_id'];

// Busca os animais filtrados pela ong_id[cite: 7]
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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { min-height: 100vh; background-color: #FFFDF9; color: #2D3748; display: flex; flex-direction: column; align-items: center; }
        header { width: 100%; max-width: 1200px; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; background-color: transparent; }
        .logo { font-size: 24px; font-weight: bold; color: #1A202C; display: flex; align-items: center; gap: 8px; text-decoration: none; }
        .logo span { color: #FF4081; }
        nav { display: flex; align-items: center; gap: 25px; }
        nav a { text-decoration: none; color: #718096; font-size: 15px; font-weight: 500; transition: color 0.2s ease; }
        nav a:hover { color: #1A202C; }
        nav a.active { background-color: #00B0FF; color: white; padding: 8px 18px; border-radius: 20px; }
        .perfil-container { display: flex; align-items: center; gap: 15px; }
        .perfil-link { display: flex; align-items: center; gap: 8px; color: #718096; text-decoration: none; font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .perfil-avatar { width: 32px; height: 32px; border-radius: 50%; background-color: #E2E8F0; display: inline-block; }
        .btn-sair { color: #FF5A5F; text-decoration: none; font-size: 15px; font-weight: 500; }
        main { width: 100%; max-width: 1000px; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .container { background-color: #ffffff; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04); width: 100%; }
        h2 { text-align: left; color: #1A202C; margin-bottom: 25px; font-size: 26px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid #EDF2F7; vertical-align: middle; }
        th { background-color: #F7FAFC; color: #4A5568; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { font-size: 15px; color: #4A5568; }
        tr:hover { background-color: #F7FAFC; }
        .img-thumb { width: 48px; height: 48px; border-radius: 8px; object-fit: cover; }
        .empty-msg { text-align: center; color: #718096; padding: 40px 20px; font-style: italic; font-size: 16px; }
        .actions { display: flex; justify-content: flex-end; gap: 15px; }
        .btn { padding: 12px 24px; border-radius: 12px; font-weight: bold; cursor: pointer; text-decoration: none; border: none; font-size: 15px; transition: all 0.2s ease; display: inline-block; }
        .btn-limpar { background-color: #FFF5F5; color: #E53E3E; }
        .btn-limpar:hover { background-color: #FED7D7; }
        .btn-adicionar { background-color: #00B0FF; color: white; box-shadow: 0 4px 14px rgba(0, 176, 255, 0.3); }
        .btn-adicionar:hover { background-color: #0091EA; transform: translateY(-1px); }
        .btn-deletar { background-color: transparent; color: #E53E3E; border: none; cursor: pointer; font-size: 16px; padding: 6px 10px; border-radius: 8px; transition: background 0.2s; }
        .btn-deletar:hover { background-color: #FFF5F5; }
    </style>
</head>
<body>

    <header>
        <a href="index.php" class="logo">
            🐾 <span>Adota</span>Pet
        </a>
        <nav>
            <a href="index.php" class="active">Início</a>
            <a href="#">Mapa</a>
            <a href="#">Candidaturas Recebidas</a>
            <div class="perfil-container">
                <a href="#" class="perfil-link">
                    <span class="perfil-avatar"></span>
                    Meu Perfil
                </a>
                <a href="logout.php" class="btn-sair">Sair</a>
            </div>
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
                                        <button type="submit" class="btn-deletar">❌</button>
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
                <a href="../Projeto_Adotapet/cadastrar_animal.php" class="btn btn-adicionar">＋ Cadastrar Novo</a>
            </div>
        </div>
    </main>

</body>
</html>