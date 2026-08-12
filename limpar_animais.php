<?php
require_once 'config/auth.php'; // Garante autenticação[cite: 4]
require_once 'config/db.php';   // Conecta ao banco 'adotapet'[cite: 5]

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ong_id = $_SESSION['usuario_id'];

    $stmt = $pdo->prepare("DELETE FROM animais WHERE ong_id = ?");
    $stmt->execute([$ong_id]);
}

header("Location: meus_animais.php");
exit;