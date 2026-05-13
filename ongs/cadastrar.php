<?php
require_once "../conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $razao_social      = $_POST["razao_social"];
    $nome_fantasia     = $_POST["nome_fantasia"];
    $cnpj              = $_POST["cnpj"];
    $numero_registro   = $_POST["numero_registro"];
    $nome_responsavel  = $_POST["nome_responsavel"];
    $email_responsavel = $_POST["email_responsavel"];
    $senha_responsavel = password_hash($_POST["senha_responsavel"], PASSWORD_DEFAULT);
    $telefone          = $_POST["telefone_responsavel"];
    $termo             = isset($_POST["termo_veracidade"]) ? 1 : 0;

    $sql = "INSERT INTO ongs (razao_social, nome_fantasia, cnpj, numero_registro, nome_responsavel,
            email_responsavel, senha_responsavel, telefone_responsavel, termo_veracidade)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$razao_social, $nome_fantasia, $cnpj, $numero_registro, $nome_responsavel,
                    $email_responsavel, $senha_responsavel, $telefone, $termo]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar ONG</title>
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
    </style>
</head>
<body>

<header>
    <a href="index.php">&#8592; Voltar</a>
    <h1>🏢 Cadastrar ONG</h1>
</header>

<div class="container">
    <div class="form-box">
        <form method="post">

            <h2>Dados da ONG</h2>

            <label>Razão Social:</label>
            <input type="text" name="razao_social" required>

            <label>Nome Fantasia:</label>
            <input type="text" name="nome_fantasia" required>

            <div class="row">
                <div>
                    <label>CNPJ:</label>
                    <input type="text" name="cnpj" placeholder="00.000.000/0000-00" required>
                </div>
                <div>
                    <label>Número de Registro:</label>
                    <input type="text" name="numero_registro" required>
                </div>
            </div>

            <h2>Dados do Responsável</h2>

            <label>Nome do Responsável:</label>
            <input type="text" name="nome_responsavel" required>

            <label>E-mail do Responsável:</label>
            <input type="email" name="email_responsavel" required>

            <label>Senha do Responsável:</label>
            <input type="password" name="senha_responsavel" required>

            <label>Telefone do Responsável:</label>
            <input type="text" name="telefone_responsavel" placeholder="(00) 00000-0000" required>

            <div class="checkbox-row">
                <input type="checkbox" name="termo_veracidade" id="termo" value="1">
                <label for="termo" style="margin:0; font-weight: normal;">
                    Declaro que as informações fornecidas são verdadeiras.
                </label>
            </div>

            <button type="submit" class="btn btn-green">Salvar</button>
            <a href="index.php" class="btn btn-gray">Cancelar</a>
        </form>
    </div>
</div>

</body>
</html>
