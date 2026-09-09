<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/db.php';

try {
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            nome_instituicao AS nome, 
            email, 
            telefone, 
            logradouro, 
            numero, 
            bairro, 
            cidade, 
            estado, 
            latitude, 
            longitude 
        FROM ongs 
        WHERE latitude IS NOT NULL AND longitude IS NOT NULL
    ");
    $stmt->execute();
    $ongs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'sucesso' => true,
        'dados' => $ongs
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao consultar ONGs: ' . $e->getMessage()
    ]);
}