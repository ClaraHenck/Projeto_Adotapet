<?php
require_once "../conexao.php";

$id = $_GET["id"];

$sql = "SELECT * FROM adotantes WHERE id_adotante = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$a = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $_POST["email"];
    $telefone = $_POST["telefone"];
    $cidade   = $_POST["cidade"];
    $estado   = $_POST["estado"];
    $rua      = $_POST["rua"];
    $numero   = $_POST["numero_residencia"];

    // Só atualiza senha se o campo foi preenchido
    if (!empty($_POST["senha"])) {
        $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
        $sql = "UPDATE adotantes SET email=?, senha=?, telefone=?, cidade=?, estado=?, rua=?, numero_residencia=? WHERE id_adotante=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $senha, $telefone, $cidade, $estado, $rua, $numero, $id]);
    } else {
        $sql = "UPDATE adotantes SET email=?, telefone=?, cidade=?, estado=?, rua=?, numero_residencia=? WHERE id_adotante=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $telefone, $cidade, $estado, $rua, $numero, $id]);
    }

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Adotante</title>
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
        input:focus { outline: none; border-color: #2c7a4b; }
        .row { display: flex; gap: 16px; }
        .row > div { flex: 1; }
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
    <h1>✏️ Editar Adotante #<?php echo $id; ?></h1>
</header>

<div class="container">
    <div class="form-box">
        <form method="post">

            <label>E-mail:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($a["email"]); ?>" required>

            <label>Nova Senha:</label>
            <input type="password" name="senha" placeholder="Deixe em branco para manter a atual">
            <small>Deixe em branco se não quiser alterar a senha.</small>

            <label>Telefone:</label>
            <input type="text" name="telefone" value="<?php echo htmlspecialchars($a["telefone"]); ?>" required>

            <div class="row">
                <div>
                    <label>Cidade:</label>
                    <input type="text" name="cidade" value="<?php echo htmlspecialchars($a["cidade"]); ?>" required>
                </div>
                <div>
                    <label>Estado:</label>
                    <input type="text" name="estado" value="<?php echo htmlspecialchars($a["estado"]); ?>" maxlength="2" required>
                </div>
            </div>

            <div class="row">
                <div>
                    <label>Rua:</label>
                    <input type="text" name="rua" value="<?php echo htmlspecialchars($a["rua"]); ?>" required>
                </div>
                <div>
                    <label>Número:</label>
                    <input type="text" name="numero_residencia" value="<?php echo htmlspecialchars($a["numero_residencia"]); ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-green">Atualizar</button>
            <a href="index.php" class="btn btn-gray">Cancelar</a>
        </form>
    </div>
</div>

</body>
</html>
