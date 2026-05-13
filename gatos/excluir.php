<?php
require_once "../conexao.php";

$id = $_GET["id"];

$sql = "DELETE FROM gatos WHERE id_gato = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

header("Location: index.php");
exit;
?>
