<?php
require_once "../conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $telefone = $_POST["telefone"];
    $cidade = $_POST["cidade"];
    $estado = $_POST["estado"];
    $rua = $_POST["rua"];
    $numero_residencia = $_POST["numero_residencia"];

    $sql = "INSERT INTO adotantes (email, senha, telefone, cidade, estado, rua, numero_residencia)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $senha, $telefone, $cidade, $estado, $rua, $numero_residencia]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Adotante</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f4f8; margin: 0; padding: 0; }
        header { background: #2c7a4b; color: white; padding: 16px 30px; display: flex; align-items: center; gap: 12px; }
        header a { color: white; text-decoration: none; font-size: 14px; }
        header h1 { margin: 0; font-size: 20px; }
        .container { padding: 30px; max-width: 600px; }
        .form-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        label { display: block; font-weight: bold; margin-bottom: 4px; font-size: 14px; color: #374151; }
        input, select { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db;
                        border-radius: 6px; font-size: 14px; box-sizing: border-box; margin-bottom: 16px; }
        input:focus, select:focus { outline: none; border-color: #2c7a4b; }
        .row { display: flex; gap: 16px; }
        .row > div { flex: 1; }
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
    <h1>👤 Cadastrar Adotante</h1>
</header>

<div class="container">
    <div class="form-box">
        <form method="post">

            <label>E-mail:</label>
            <input type="email" name="email" required>

            <label>Senha:</label>
            <input type="password" name="senha" required>

            <label>Telefone:</label>
            <input type="text" name="telefone" placeholder="(00) 00000-0000" required>

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
                    <input type="text" name="numero_residencia" required>
                </div>
            </div>

            <button type="submit" class="btn btn-green">Salvar</button>
            <a href="index.php" class="btn btn-gray">Cancelar</a>
        </form>
    </div>
</div>

</body>
</html>
