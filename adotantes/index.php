<?php
require_once "../conexao.php";

$sql = "SELECT * FROM adotantes ORDER BY data_cadastro DESC";
$stmt = $pdo->query($sql);
$adotantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Adotantes</title>
</head>
<body>

<header>
    <a href="../index.php">&#8592; Voltar ao menu</a>
    <h1>👤 Adotantes</h1>
</header>

<div class="container">
    <a class="btn btn-green" href="cadastrar.php">+ Cadastrar Adotante</a>

    <table>
        <tr>
            <th>ID</th>
            <th>E-mail</th>
            <th>Telefone</th>
            <th>Cidade</th>
            <th>Estado</th>
            <th>Rua</th>
            <th>Número</th>
            <th>Data Cadastro</th>
            <th>Ações</th>
        </tr>
        <?php if (count($adotantes) === 0): ?>
        <tr><td colspan="9" class="vazio">Nenhum adotante cadastrado.</td></tr>
        <?php else: ?>
        <?php foreach ($adotantes as $a): ?>
        <tr>
            <td><?php echo $a["id_adotante"]; ?></td>
            <td><?php echo htmlspecialchars($a["email"]); ?></td>
            <td><?php echo htmlspecialchars($a["telefone"]); ?></td>
            <td><?php echo htmlspecialchars($a["cidade"]); ?></td>
            <td><?php echo htmlspecialchars($a["estado"]); ?></td>
            <td><?php echo htmlspecialchars($a["rua"]); ?></td>
            <td><?php echo htmlspecialchars($a["numero_residencia"]); ?></td>
            <td><?php echo date("d/m/Y H:i", strtotime($a["data_cadastro"])); ?></td>
            <td class="acoes">
                <a class="btn btn-blue" href="editar.php?id=<?php echo $a["id_adotante"]; ?>">Editar</a>
                <a class="btn btn-red" href="excluir.php?id=<?php echo $a["id_adotante"]; ?>"
                   onclick="return confirm('Tem certeza que deseja excluir este adotante?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
