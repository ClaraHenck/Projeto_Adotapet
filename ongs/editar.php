<?php
require_once "../conexao.php";

$id = $_GET["id"];

$sql = "SELECT * FROM ongs WHERE id_ong = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$o = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $razao_social      = $_POST["razao_social"];
    $nome_fantasia     = $_POST["nome_fantasia"];
    $cnpj              = $_POST["cnpj"];
    $numero_registro   = $_POST["numero_registro"];
    $nome_responsavel  = $_POST["nome_responsavel"];
    $email_responsavel = $_POST["email_responsavel"];
    $telefone          = $_POST["telefone_responsavel"];
    $termo             = isset($_POST["termo_veracidade"]) ? 1 : 0;

    if (!empty($_POST["senha_responsavel"])) {
        $senha = password_hash($_POST["senha_responsavel"], PASSWORD_DEFAULT);
        $sql = "UPDATE ongs SET razao_social=?, nome_fantasia=?, cnpj=?, numero_registro=?,
                nome_responsavel=?, email_responsavel=?, senha_responsavel=?, telefone_responsavel=?, termo_veracidade=?
                WHERE id_ong=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$razao_social, $nome_fantasia, $cnpj, $numero_registro,
                        $nome_responsavel, $email_responsavel, $senha, $telefone, $termo, $id]);
    } else {
        $sql = "UPDATE ongs SET razao_social=?, nome_fantasia=?, cnpj=?, numero_registro=?,
                nome_responsavel=?, email_responsavel=?, telefone_responsavel=?, termo_veracidade=?
                WHERE id_ong=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$razao_social, $nome_fantasia, $cnpj, $numero_registro,
                        $nome_responsavel, $email_responsavel, $telefone, $termo, $id]);
    }

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar ONG</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f4f8; margin: 0; padding: 0; }
        header { background: #2c7a4b; color: white; padding: 16px 30px; display: flex; align-items: center; gap: 12px; }
        header a { color: white; text-decoration: none; font-size: 14px; }
        header h1 { margin: 0; font-size: 20px; }
        .container { padding: 30px; max-width: 650px; }
        .form-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { font-size: 15px; color: #2c7a4b; border-bottom: 2px solid #d1fae5; padding-bottom: 6px; margin: 20px 0 14px; }
        label { display: block; font-weight: bold; margin-bottom: 4px; font-size: 14px; color: #374151; }
        input, select { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db;
                        border-radius: 6px; font-size: 14px; box-sizing: border-box; margin-bottom: 16px; }
        input:focus { outline: none; border-color: #2c7a4b; }
        .row { display: flex; gap: 16px; }
        .row > div { flex: 1; }
        .checkbox-row { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        .checkbox-row input { width: auto; margin: 0; }
        .btn { display: inline-block; padding: 10px 24px; border-radius: 6px; font-size: 14px;
               font-weight: bold; cursor: pointer; border: none; text-decoration: none; }
        .btn-green { background: #2c7a4b; color: white; }
        .btn-green:hover { background: #235f3b; }
        .btn-gray { background: #6b7280; color: white; margin-left: 10px; }
        .btn-gray:hover { background: #4b5563; }
        small { display: block; margin-top: -12px; margin-bottom: 16px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>

<header>
    <a href="index.php">&#8592; Voltar</a>
    <h1>✏️ Editar ONG #<?php echo $id; ?></h1>
</header>

<div class="container">
    <div class="form-box">
        <form method="post">

            <h2>Dados da ONG</h2>

            <label>Razão Social:</label>
            <input type="text" name="razao_social" value="<?php echo htmlspecialchars($o["razao_social"]); ?>" required>

            <label>Nome Fantasia:</label>
            <input type="text" name="nome_fantasia" value="<?php echo htmlspecialchars($o["nome_fantasia"]); ?>" required>

            <div class="row">
                <div>
                    <label>CNPJ:</label>
                    <input type="text" name="cnpj" value="<?php echo htmlspecialchars($o["cnpj"]); ?>" required>
                </div>
                <div>
                    <label>Número de Registro:</label>
                    <input type="text" name="numero_registro" value="<?php echo htmlspecialchars($o["numero_registro"]); ?>" required>
                </div>
            </div>

            <h2>Dados do Responsável</h2>

            <label>Nome do Responsável:</label>
            <input type="text" name="nome_responsavel" value="<?php echo htmlspecialchars($o["nome_responsavel"]); ?>" required>

            <label>E-mail do Responsável:</label>
            <input type="email" name="email_responsavel" value="<?php echo htmlspecialchars($o["email_responsavel"]); ?>" required>

            <label>Nova Senha do Responsável:</label>
            <input type="password" name="senha_responsavel" placeholder="Deixe em branco para manter a atual">
            <small>Deixe em branco se não quiser alterar a senha.</small>

            <label>Telefone do Responsável:</label>
            <input type="text" name="telefone_responsavel" value="<?php echo htmlspecialchars($o["telefone_responsavel"]); ?>" required>

            <div class="checkbox-row">
                <input type="checkbox" name="termo_veracidade" id="termo" value="1"
                    <?php echo $o["termo_veracidade"] ? "checked" : ""; ?>>
                <label for="termo" style="margin:0; font-weight: normal;">
                    Declaro que as informações fornecidas são verdadeiras.
                </label>
            </div>

            <button type="submit" class="btn btn-green">Atualizar</button>
            <a href="index.php" class="btn btn-gray">Cancelar</a>
        </form>
    </div>
</div>

</body>
</html>
