<?php
// Permite que o arquivo HTML acesse esta API mesmo estando em portas diferentes
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST");
header("Content-Type: application/json; charset=UTF-8");

// CONFIGURAÇÃO DA CONEXÃO COM O MYSQL WORKBENCH
$host = "localhost";
$dbname = "adotapet";
$username = "root"; // Usuário padrão do XAMPP (mude se configurou senha no Workbench)
$password = "";     // Senha padrão do XAMPP fica vazia

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Falha na conexão com o banco: " . $e->getMessage()]);
    exit;
}

// DETECTA O TIPO DE REQUISIÇÃO (MÉTODO HTTP)
$metodo = $_SERVER['REQUEST_METHOD'];

// ──> SE FOR "GET": Busca as ONGs salvas e envia para a tela
if ($metodo === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM ongs");
        $stmt->execute();
        $ongs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Envia de volta para o HTML em formato JSON
        echo json_encode($ongs);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}

// ──> SE FOR "POST": Recebe o cadastro do formulário e salva no Workbench
if ($metodo === 'POST') {
    // Lê o pacote JSON enviado pelo formulário HTML
    $dadosRecebidos = json_decode(file_get_contents("php://input"), true);

    if (!empty($dadosRecebidos['nome']) && !empty($dadosRecebidos['latitude'])) {
        try {
            $sql = "INSERT INTO ongs (nome, cidade, descricao, latitude, longitude) 
                    VALUES (:nome, :cidade, :descricao, :latitude, :longitude)";
                    
            $stmt = $pdo->prepare($sql);
            
            // Vincula os valores vindos do JSON de forma segura contra invasões (SQL Injection)
            $stmt->bindParam(':nome', $dadosRecebidos['nome']);
            $stmt->bindParam(':cidade', $dadosRecebidos['cidade']);
            $stmt->bindParam(':descricao', $dadosRecebidos['descricao']);
            $stmt->bindParam(':latitude', $dadosRecebidos['latitude']);
            $stmt->bindParam(':longitude', $dadosRecebidos['longitude']);
            
            $stmt->execute();
            echo json_encode(["success" => true, "message" => "ONG salva no MySQL Workbench!"]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Dados incompletos."]);
    }
}
?>
