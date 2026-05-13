<?php
require_once "../conexao.php";

$id = $_GET["id"];

$sql = "DELETE FROM cachorros WHERE id_cachorro = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

header("Location: index.php");
exit;
?>
