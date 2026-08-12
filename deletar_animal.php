<?php
require_once 'config/auth.php'; // Garante autenticação[cite: 4]
require_once 'config/db.php';   // Conecta ao banco 'adotapet'[cite: 5]

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $animal_id = (int)$_POST['id'];
    $ong_id = $_SESSION['usuario_id'];

    $stmt = $pdo->prepare("DELETE FROM animais WHERE id = ? AND ong_id = ?");
    $stmt->execute([$animal_id, $ong_id]);
}

header("Location: meus_animais.php");
exit;