<?php
require_once "../conexao.php";

$sql = "SELECT * FROM ongs ORDER BY data_cadastro DESC";
$stmt = $pdo->query($sql);
$ongs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>ONGs</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f4f8; margin: 0; padding: 0; }
        header { background: #2c7a4b; color: white; padding: 16px 30px; display: flex; align-items: center; gap: 12px; }
        header a { color: white; text-decoration: none; font-size: 14px; }
        header h1 { margin: 0; font-size: 20px; }
        .container { padding: 30px; }
        .btn { display: inline-block; padding: 9px 18px; border-radius: 6px; text-decoration: none;
               font-size: 14px; font-weight: bold; cursor: pointer; border: none; }
        .btn-green { background: #2c7a4b; color: white; }
        .btn-green:hover { background: #235f3b; }
        .btn-blue { background: #2563eb; color: white; }
        .btn-blue:hover { background: #1d4ed8; }
        .btn-red { background: #dc2626; color: white; }
        .btn-red:hover { background: #b91c1c; }
        table { width: 100%; border-collapse: collapse; background: white;
                border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-top: 20px; }
        th { background: #2c7a4b; color: white; padding: 12px 14px; text-align: left; font-size: 14px; }
        td { padding: 11px 14px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f9fafb; }
        .acoes { display: flex; gap: 8px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-sim { background: #d1fae5; color: #065f46; }
        .badge-nao { background: #fee2e2; color: #991b1b; }
        .vazio { text-align: center; padding: 40px; color: #888; }
    </style>
</head>
<body>

<header>
    <a href="../index.php">&#8592; Voltar ao menu</a>
    <h1>🏢 ONGs</h1>
</header>

<div class="container">
    <a class="btn btn-green" href="cadastrar.php">+ Cadastrar ONG</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Nome Fantasia</th>
            <th>Razão Social</th>
            <th>CNPJ</th>
            <th>Nº Registro</th>
            <th>Responsável</th>
            <th>E-mail Responsável</th>
            <th>Telefone</th>
            <th>Termo</th>
            <th>Data Cadastro</th>
            <th>Ações</th>
        </tr>
        <?php if (count($ongs) === 0): ?>
        <tr><td colspan="11" class="vazio">Nenhuma ONG cadastrada.</td></tr>
        <?php else: ?>
        <?php foreach ($ongs as $o): ?>
        <tr>
            <td><?php echo $o["id_ong"]; ?></td>
            <td><?php echo htmlspecialchars($o["nome_fantasia"]); ?></td>
            <td><?php echo htmlspecialchars($o["razao_social"]); ?></td>
            <td><?php echo htmlspecialchars($o["cnpj"]); ?></td>
            <td><?php echo htmlspecialchars($o["numero_registro"]); ?></td>
            <td><?php echo htmlspecialchars($o["nome_responsavel"]); ?></td>
            <td><?php echo htmlspecialchars($o["email_responsavel"]); ?></td>
            <td><?php echo htmlspecialchars($o["telefone_responsavel"]); ?></td>
            <td>
                <?php if ($o["termo_veracidade"]): ?>
                    <span class="badge badge-sim">Sim</span>
                <?php else: ?>
                    <span class="badge badge-nao">Não</span>
                <?php endif; ?>
            </td>
            <td><?php echo date("d/m/Y H:i", strtotime($o["data_cadastro"])); ?></td>
            <td class="acoes">
                <a class="btn btn-blue" href="editar.php?id=<?php echo $o["id_ong"]; ?>">Editar</a>
                <a class="btn btn-red" href="excluir.php?id=<?php echo $o["id_ong"]; ?>"
                   onclick="return confirm('Tem certeza que deseja excluir esta ONG? Todos os animais vinculados também serão excluídos!')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
