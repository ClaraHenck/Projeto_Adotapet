<?php
require_once 'config/auth.php'; // Proteção de sessão
require_once 'config/db.php';   // Conexão PDO com banco 'adotapet'[cite: 5]

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mapeamento dos campos com base no SQL fornecido[cite: 7]
    $nome               = trim($_POST['nome'] ?? '');
    $especie_raca       = trim($_POST['especie_raca'] ?? '');
    $idade_estimada     = trim($_POST['idade_estimada'] ?? '');
    $porte              = trim($_POST['porte'] ?? '');
    $carteira_vacinacao = trim($_POST['carteira_vacinacao'] ?? '');
    $foto_url           = trim($_POST['foto_url'] ?? '');
    $descricao          = trim($_POST['descricao'] ?? '');
    
    // O id da ONG logada vem da sessão de autenticação
    $ong_id = $_SESSION['usuario_id'];

    if (empty($nome) || empty($especie_raca) || empty($idade_estimada) || empty($porte) || empty($carteira_vacinacao) || empty($foto_url) || empty($descricao)) {
        $erro = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        try {
            // Inserção com base na tabela 'animais'[cite: 7, 8]
            $stmt = $pdo->prepare("
                INSERT INTO animais (ong_id, nome, especie_raca, idade_estimada, porte, carteira_vacinacao, foto_url, descricao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$ong_id, $nome, $especie_raca, $idade_estimada, $porte, $carteira_vacinacao, $foto_url, $descricao]);
            
            header("Location: meus_animais.php");
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar no banco de dados: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Animal - AdotaPet</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { min-height: 100vh; background-color: #ffffff; color: #2D3748; display: flex; flex-direction: column; align-items: center; }
        header { width: 100%; max-width: 1200px; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; background-color: #ffffff; border-bottom: 1px solid #EDF2F7; }
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
        main { width: 100%; max-width: 1200px; padding: 40px; display: flex; justify-content: center; align-items: center; }
        .container { background-color: #ffffff; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); width: 100%; max-width: 520px; border: 1px solid #EDF2F7; }
        h2 { text-align: center; color: #1A202C; margin-bottom: 8px; font-size: 24px; }
        p.subtitle { text-align: center; color: #718096; font-size: 14px; margin-bottom: 28px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 6px; color: #4A5568; font-weight: 600; font-size: 14px; }
        input[type="text"], input[type="url"], select, textarea { width: 100%; padding: 12px; border: 2px solid #E2E8F0; border-radius: 12px; font-size: 15px; color: #2D3748; transition: all 0.3s ease; outline: none; background-color: #F7FAFC; }
        input:focus, select:focus, textarea:focus { border-color: #00B0FF; background-color: #ffffff; box-shadow: 0 0 0 3px rgba(0, 176, 255, 0.1); }
        .row { display: flex; gap: 15px; }
        .row .form-group { flex: 1; }
        textarea { resize: vertical; min-height: 90px; }
        button { width: 100%; background-color: #00B0FF; color: white; border: none; padding: 14px; font-size: 16px; font-weight: bold; border-radius: 12px; cursor: pointer; transition: all 0.2s ease; margin-top: 10px; box-shadow: 0 4px 14px rgba(0, 176, 255, 0.3); }
        button:hover { background-color: #0091EA; transform: translateY(-1px); }
        .alert-error { background-color: #FFF5F5; color: #E53E3E; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center; }
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
            <h2>Cadastrar Animal</h2>
            <p class="subtitle">Insira as informações do pet abaixo</p>
            
            <?php if (!empty($erro)): ?>
                <div class="alert-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form action="cadastroAnimal.php" method="POST">
                
                <div class="form-group">
                    <label for="nome">Nome do Animal</label>
                    <input type="text" id="nome" name="nome" required placeholder="Ex: Belinha, Thor, Mel">
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="especie_raca">Espécie e Raça</label>
                        <input type="text" id="especie_raca" name="especie_raca" required placeholder="Ex: Poodle, Vira-lata">
                    </div>

                    <div class="form-group">
                        <label for="idade_estimada">Idade Estimada</label>
                        <input type="text" id="idade_estimada" name="idade_estimada" required placeholder="Ex: 2 anos, 15 meses">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="porte">Porte</label>
                        <select id="porte" name="porte" required>
                            <option value="" disabled selected>Selecione</option>
                            <option value="Pequeno">Pequeno</option>
                            <option value="Médio">Médio</option>
                            <option value="Grande">Grande</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="carteira_vacinacao">Vacinação</label>
                        <select id="carteira_vacinacao" name="carteira_vacinacao" required>
                            <option value="" disabled selected>Selecione</option>
                            <option value="Completa">Completa</option>
                            <option value="Parcial">Parcial</option>
                            <option value="Nenhuma">Nenhuma</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="foto_url">URL da Foto</label>
                    <input type="url" id="foto_url" name="foto_url" required placeholder="https://exemplo.com/foto.jpg">
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição / História</label>
                    <textarea id="descricao" name="descricao" required placeholder="Breve descrição do temperamento ou história do animal..."></textarea>
                </div>

                <button type="submit">Cadastrar Animal</button>
            </form>
        </div>
    </main>

</body>
</html>