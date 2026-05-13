<?php
require_once "../conexao.php";

$sql = "SELECT g.*, o.nome_fantasia FROM gatos g
        LEFT JOIN ongs o ON g.id_ong = o.id_ong
        ORDER BY g.data_cadastro DESC";
$stmt = $pdo->query($sql);
$gatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gatos</title>
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
        .badge-macho { background: #dbeafe; color: #1e40af; }
        .badge-femea { background: #fce7f3; color: #9d174d; }
        .badge-pequeno { background: #fef9c3; color: #854d0e; }
        .badge-medio { background: #ffedd5; color: #9a3412; }
        .badge-grande { background: #fee2e2; color: #991b1b; }
        .vazio { text-align: center; padding: 40px; color: #888; }
        .desc { max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body>

<header>
    <a href="../index.php">&#8592; Voltar ao menu</a>
    <h1>🐱 Gatos</h1>
</header>

<div class="container">
    <a class="btn btn-green" href="cadastrar.php">+ Cadastrar Gato</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Raça</th>
            <th>Gênero</th>
            <th>Porte</th>
            <th>Temperamento</th>
            <th>Idade</th>
            <th>Cidade / Estado</th>
            <th>ONG</th>
            <th>Descrição</th>
            <th>Cadastro</th>
            <th>Ações</th>
        </tr>
        <?php if (count($gatos) === 0): ?>
        <tr><td colspan="12" class="vazio">Nenhum gato cadastrado.</td></tr>
        <?php else: ?>
        <?php foreach ($gatos as $g): ?>
        <tr>
            <td><?php echo $g["id_gato"]; ?></td>
            <td><?php echo htmlspecialchars($g["nome"]); ?></td>
            <td><?php echo htmlspecialchars($g["raca"]); ?></td>
            <td>
                <span class="badge <?php echo $g['genero'] === 'Macho' ? 'badge-macho' : 'badge-femea'; ?>">
                    <?php echo $g["genero"] === "Macho" ? "♂ Macho" : "♀ Fêmea"; ?>
                </span>
            </td>
            <td>
                <?php
                $classe = ['Pequeno' => 'badge-pequeno', 'Medio' => 'badge-medio', 'Grande' => 'badge-grande'];
                $label  = ['Pequeno' => 'Pequeno', 'Medio' => 'Médio', 'Grande' => 'Grande'];
                ?>
                <span class="badge <?php echo $classe[$g['porte']] ?? ''; ?>">
                    <?php echo $label[$g['porte']] ?? $g['porte']; ?>
                </span>
            </td>
            <td><?php echo htmlspecialchars($g["temperamento"]); ?></td>
            <td><?php echo $g["idade"]; ?> mes<?php echo $g["idade"] > 1 ? 'es' : ''; ?></td>
            <td><?php echo htmlspecialchars($g["cidade"]); ?> / <?php echo htmlspecialchars($g["estado"]); ?></td>
            <td><?php echo htmlspecialchars($g["nome_fantasia"] ?? "—"); ?></td>
            <td class="desc" title="<?php echo htmlspecialchars($g["descricao"]); ?>">
                <?php echo htmlspecialchars($g["descricao"] ?? "—"); ?>
            </td>
            <td><?php echo date("d/m/Y", strtotime($g["data_cadastro"])); ?></td>
            <td class="acoes">
                <a class="btn btn-blue" href="editar.php?id=<?php echo $g["id_gato"]; ?>">Editar</a>
                <a class="btn btn-red" href="excluir.php?id=<?php echo $g["id_gato"]; ?>"
                   onclick="return confirm('Tem certeza que deseja excluir este gato?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
