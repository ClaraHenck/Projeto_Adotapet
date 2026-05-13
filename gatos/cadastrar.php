<?php
require_once "../conexao.php";

$ongs = $pdo->query("SELECT id_ong, nome_fantasia FROM ongs ORDER BY nome_fantasia")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_ong       = $_POST["id_ong"];
    $nome         = $_POST["nome"];
    $raca         = $_POST["raca"];
    $genero       = $_POST["genero"];
    $porte        = $_POST["porte"];
    $temperamento = $_POST["temperamento"];
    $idade        = $_POST["idade"];
    $descricao    = $_POST["descricao"];
    $cidade       = $_POST["cidade"];
    $estado       = $_POST["estado"];
    $rua          = $_POST["rua"];
    $numero       = $_POST["numero"];

    $sql = "INSERT INTO gatos (id_ong, nome, raca, genero, porte, temperamento, idade, cidade, estado, rua, numero, descricao)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_ong, $nome, $raca, $genero, $porte, $temperamento, $idade, $cidade, $estado, $rua, $numero, $descricao]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Gato</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f4f8; margin: 0; padding: 0; }
        header { background: #2c7a4b; color: white; padding: 16px 30px; display: flex; align-items: center; gap: 12px; }
        header a { color: white; text-decoration: none; font-size: 14px; }
        header h1 { margin: 0; font-size: 20px; }
        .container { padding: 30px; max-width: 650px; }
        .form-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { font-size: 15px; color: #2c7a4b; border-bottom: 2px solid #d1fae5; padding-bottom: 6px; margin: 20px 0 14px; }
        label { display: block; font-weight: bold; margin-bottom: 4px; font-size: 14px; color: #374151; }
        input, select, textarea { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db;
                        border-radius: 6px; font-size: 14px; box-sizing: border-box; margin-bottom: 16px; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #2c7a4b; }
        textarea { resize: vertical; height: 90px; }
        .row { display: flex; gap: 16px; }
        .row > div { flex: 1; }
        .btn { display: inline-block; padding: 10px 24px; border-radius: 6px; font-size: 14px;
               font-weight: bold; cursor: pointer; border: none; text-decoration: none; }
        .btn-green { background: #2c7a4b; color: white; }
        .btn-green:hover { background: #235f3b; }
        .btn-gray { background: #6b7280; color: white; margin-left: 10px; }
        .btn-gray:hover { background: #4b5563; }
        .info-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;
                    padding: 12px 16px; margin-bottom: 16px; font-size: 13px; color: #166534; }
    </style>
</head>
<body>

<header>
    <a href="index.php">&#8592; Voltar</a>
    <h1>🐱 Cadastrar Gato</h1>
</header>

<div class="container">
    <div class="form-box">
        <form method="post">

            <h2>Vínculo com ONG</h2>

            <label>ONG Responsável:</label>
            <select name="id_ong" required>
                <option value="">-- Selecione a ONG --</option>
                <?php foreach ($ongs as $o): ?>
                <option value="<?php echo $o["id_ong"]; ?>">
                    <?php echo htmlspecialchars($o["nome_fantasia"]); ?>
                </option>
                <?php endforeach; ?>
            </select>

            <h2>Dados do Animal</h2>

            <div class="row">
                <div>
                    <label>Nome:</label>
                    <input type="text" name="nome" required>
                </div>
                <div>
                    <label>Raça:</label>
                    <input type="text" name="raca" placeholder="Ex: Persa, SRD" required>
                </div>
            </div>

            <div class="row">
                <div>
                    <label>Gênero:</label>
                    <select name="genero" required>
                        <option value="">-- Selecione --</option>
                        <option value="Macho">♂ Macho</option>
                        <option value="Femea">♀ Fêmea</option>
                    </select>
                </div>
                <div>
                    <label>Porte:</label>
                    <select name="porte" required>
                        <option value="">-- Selecione --</option>
                        <option value="Pequeno">Pequeno</option>
                        <option value="Medio">Médio</option>
                        <option value="Grande">Grande</option>
                    </select>
                </div>
                <div>
                    <label>Idade (meses):</label>
                    <input type="number" name="idade" min="0" placeholder="Ex: 4" required>
                </div>
            </div>

            <label>Temperamento:</label>
            <input type="text" name="temperamento" placeholder="Ex: Independente, carinhoso, tranquilo" required>

            <label>Descrição:</label>
            <textarea name="descricao" placeholder="Conte um pouco sobre o animal..."></textarea>

            <h2>Localidade do Animal</h2>
            <div class="info-box">📍 Preencha o endereço onde o animal se encontra (normalmente o da ONG).</div>

            <div class="row">
                <div>
                    <label>Cidade:</label>
                    <input type="text" name="cidade" required>
                </div>
                <div>
                    <label>Estado:</label>
                    <input type="text" name="estado" placeholder="Ex: SP" maxlength="2" required>
                </div>
            </div>

            <div class="row">
                <div>
                    <label>Rua:</label>
                    <input type="text" name="rua" required>
                </div>
                <div>
                    <label>Número:</label>
                    <input type="text" name="numero" required>
                </div>
            </div>

            <button type="submit" class="btn btn-green">Salvar</button>
            <a href="index.php" class="btn btn-gray">Cancelar</a>
        </form>
    </div>
</div>

</body>
</html>
