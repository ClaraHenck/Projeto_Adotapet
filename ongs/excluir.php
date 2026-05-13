<?php
require_once "../conexao.php";

$id = $_GET["id"];

$sql = "DELETE FROM ongs WHERE id_ong = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

header("Location: index.php");
exit;
?>
