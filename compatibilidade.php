<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Conexão com o banco (ajuste o caminho se necessário)
require_once "config/db.php";

// 1. Verifica se o usuário está logado
$usuario_id = $_SESSION["usuario_id"] ?? null;

if (!$usuario_id) {
    echo "Você precisa estar logado para ver a compatibilidade. <a href='login.php'>Fazer Login</a>";
    exit;
}

// 2. Recupera o ID do animal passado via GET (?id=X)
$animal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($animal_id <= 0) {
    echo "Animal não especificado ou inválido.";
    exit;
}

try {
    // 3. Busca os dados do Adotante (Questionário)
    // OBS: Ajuste 'usuario_id' se na sua tabela a coluna tiver outro nome (ex: id_adotante)
    $sqlAdotante = "SELECT * FROM questionario_adotante WHERE usuario_id = ?";
    $stmtAdotante = $pdo->prepare($sqlAdotante);
    $stmtAdotante->execute([$usuario_id]);
    $adotante = $stmtAdotante->fetch(PDO::FETCH_ASSOC);

    // 4. Busca os dados do Animal
    $sqlAnimal = "SELECT * FROM animais WHERE id = ?";
    $stmtAnimal = $pdo->prepare($sqlAnimal);
    $stmtAnimal->execute([$animal_id]);
    $animal = $stmtAnimal->fetch(PDO::FETCH_ASSOC);

    if (!$animal) {
        echo "Animal não encontrado no sistema.";
        exit;
    }

} catch (PDOException $e) {
    echo "Erro de conexão/consulta no banco: " . $e->getMessage();
    exit;
}

// Verifica se o usuário preencheu o questionário prévio
$tem_questionario = !empty($adotante);

$analise = [];
$pontos_positivos = 0;
$total_criterios = 5;

if ($tem_questionario) {

    // --- CRITÉRIO 1: Tipo de Moradia ---
    $moradia_user = $adotante['tipo_moradia'] ?? 'Não informado';
    $moradia_pet = $animal['moradia_ideal'] ?? 'Apartamento';

    if ($moradia_user === $moradia_pet || $moradia_user === 'Casa') {
        $pontos_positivos++;
        $analise[] = [
            'status' => 'sucesso',
            'titulo' => 'Tipo de Moradia',
            'user' => $moradia_user,
            'pet' => $moradia_pet,
            'motivo' => "Sua residência ({$moradia_user}) atende perfeitamente ao espaço de convivência que o pet precisa."
        ];
    } else {
        $analise[] = [
            'status' => 'erro',
            'titulo' => 'Tipo de Moradia',
            'user' => $moradia_user,
            'pet' => $moradia_pet,
            'motivo' => "O pet precisa de {$moradia_pet}, porém você informou que mora em {$moradia_user}."
        ];
    }

    // --- CRITÉRIO 2: Espaço de Quintal ---
    $pesos = ['Grande' => 3, 'Médio' => 2, 'Pequeno' => 1, 'Nenhum' => 0];
    $espaco_user = $adotante['espaco_quintal'] ?? 'Nenhum';
    $espaco_pet = $animal['espaco_necessario'] ?? 'Pequeno';

    $val_user = $pesos[$espaco_user] ?? 0;
    $val_pet = $pesos[$espaco_pet] ?? 0;

    if ($val_user >= $val_pet) {
        $pontos_positivos++;
        $analise[] = [
            'status' => 'sucesso',
            'titulo' => 'Espaço de Quintal / Área Externa',
            'user' => $espaco_user,
            'pet' => $espaco_pet,
            'motivo' => "Seu espaço físico disponível é amplo o suficiente para a rotina do animal."
        ];
    } else {
        $analise[] = [
            'status' => 'erro',
            'titulo' => 'Espaço de Quintal / Área Externa',
            'user' => $espaco_user,
            'pet' => $espaco_pet,
            'motivo' => "O pet necessita de um espaço {$espaco_pet}, mas seu quintal/espaço livre é {$espaco_user}."
        ];
    }

    // --- CRITÉRIO 3: Tempo Disponível vs Nível de Energia ---
    $tempo_user = $adotante['tempo_disponivel'] ?? 'Baixo';
    $energia_pet = $animal['nivel_energia'] ?? 'Médio';

    if ($tempo_user === $energia_pet) {
        $pontos_positivos++;
        $analise[] = [
            'status' => 'sucesso',
            'titulo' => 'Tempo Disponível x Nível de Energia',
            'user' => "Tempo " . $tempo_user,
            'pet' => "Energia " . $energia_pet,
            'motivo' => "Sua disponibilidade de tempo diária combina idealmente com a necessidade de exercícios e passeios do pet."
        ];
    } else {
        $analise[] = [
            'status' => 'erro',
            'titulo' => 'Tempo Disponível x Nível de Energia',
            'user' => "Tempo " . $tempo_user,
            'pet' => "Energia " . $energia_pet,
            'motivo' => "O nível de atenção e exercícios cobrados pela energia do pet destoa do tempo livre informado."
        ];
    }

    // --- CRITÉRIO 4: Convivência com Outros Pets ---
    $tem_outros = $adotante['tem_outros_pets'] ?? 'Não';
    $social_pet = $animal['social_outros_pets'] ?? 'Sim';

    if ($tem_outros === 'Não' || ($tem_outros === 'Sim' && $social_pet === 'Sim')) {
        $pontos_positivos++;
        $analise[] = [
            'status' => 'sucesso',
            'titulo' => 'Sociabilidade com Outros Animais',
            'user' => "Possui pets: " . $tem_outros,
            'pet' => "Sociável: " . $social_pet,
            'motivo' => "Não há conflitos esperados de convivência com outros animais em casa."
        ];
    } else {
        $analise[] = [
            'status' => 'erro',
            'titulo' => 'Sociabilidade com Outros Animais',
            'user' => "Possui pets: " . $tem_outros,
            'pet' => "Sociável: " . $social_pet,
            'motivo' => "Você já possui animais, porém este pet tem restrições e prefere ser o único animal no lar."
        ];
    }

    // --- CRITÉRIO 5: Experiência Anterior ---
    $exp_pesos = ['Iniciante' => 1, 'Intermediário' => 2, 'Experiente' => 3];
    $exp_user = $adotante['experiencia'] ?? 'Iniciante';
    $exp_pet = $animal['experiencia_exigida'] ?? 'Iniciante';

    $v_exp_user = $exp_pesos[$exp_user] ?? 1;
    $v_exp_pet = $exp_pesos[$exp_pet] ?? 1;

    if ($v_exp_user >= $v_exp_pet) {
        $pontos_positivos++;
        $analise[] = [
            'status' => 'sucesso',
            'titulo' => 'Experiência do Tutor',
            'user' => $exp_user,
            'pet' => "Nível " . $exp_pet,
            'motivo' => "Seu histórico de convivência com animais dá total suporte para cuidar desse perfil de pet."
        ];
    } else {
        $analise[] = [
            'status' => 'erro',
            'titulo' => 'Experiência do Tutor',
            'user' => $exp_user,
            'pet' => "Nível " . $exp_pet,
            'motivo' => "Este animal requer cuidados ou manejo específico de um tutor com experiência '{$exp_pet}'."
        ];
    }

    // Cálculo da Porcentagem Final
    $porcentagem = round(($pontos_positivos / $total_criterios) * 100);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compatibilidade com <?php echo htmlspecialchars($animal['nome'] ?? 'o Pet'); ?></title>
    
    <style>
        :root {
            --primary: #ff7a00;
            --bg: #f4f6f9;
            --card-bg: #ffffff;
            --success: #2ecc71;
            --success-bg: #e8f8f0;
            --danger: #e74c3c;
            --danger-bg: #fdeaea;
            --text: #333333;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .container {
            background-color: var(--card-bg);
            max-width: 650px;
            width: 100%;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        .pet-header {
            text-align: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .pet-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary);
            margin-bottom: 10px;
        }

        .score-card {
            background: #f8f9fa;
            border-radius: 10px;
            text-align: center;
            padding: 20px;
            margin-bottom: 25px;
        }

        .score-number {
            font-size: 50px;
            font-weight: bold;
        }

        .score-number.alto { color: var(--success); }
        .score-number.medio { color: #f39c12; }
        .score-number.baixo { color: var(--danger); }

        .score-detalhe {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .criterio-item {
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid;
            margin-bottom: 12px;
        }

        .criterio-item.sucesso {
            background-color: var(--success-bg);
            border-color: var(--success);
        }

        .criterio-item.erro {
            background-color: var(--danger-bg);
            border-color: var(--danger);
        }

        .criterio-topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .badge {
            font-size: 11px;
            padding: 3px 7px;
            border-radius: 4px;
            color: #fff;
            text-transform: uppercase;
        }

        .sucesso .badge { background-color: var(--success); }
        .erro .badge { background-color: var(--danger); }

        .respostas-box {
            font-size: 12px;
            background: rgba(255, 255, 255, 0.7);
            padding: 6px 10px;
            border-radius: 4px;
            margin-bottom: 6px;
            color: #555;
        }

        .motivo-txt {
            font-size: 13.5px;
            margin: 0;
            line-height: 1.4;
        }

        .btn-voltar {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 15px;
        }

        .btn-voltar:hover {
            opacity: 0.9;
        }

        .alerta-aviso {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="pet-header">
        <img src="<?php echo htmlspecialchars($animal['foto_url'] ?? 'LOGO.png'); ?>" alt="Foto do Pet" class="pet-avatar">
        <h2>Compatibilidade com <?php echo htmlspecialchars($animal['nome']); ?></h2>
    </div>

    <?php if (!$tem_questionario): ?>
        <div class="alerta-aviso">
            <h3>Questionário Não Encontrado</h3>
            <p>Você ainda não respondeu ao questionário de adoção. Responda para calcularmos sua compatibilidade com este pet!</p>
            <a href="questionario.php" class="btn-voltar">Preencher Questionário</a>
        </div>
    <?php else: ?>

        <div class="score-card">
            <div>Sua Pontuação de Compatibilidade</div>
            <div class="score-number <?php echo ($porcentagem >= 70) ? 'alto' : (($porcentagem >= 50) ? 'medio' : 'baixo'); ?>">
                <?php echo $porcentagem; ?>%
            </div>
            <div class="score-detalhe">
                Você cumpre <strong><?php echo $pontos_positivos; ?> de <?php echo $total_criterios; ?></strong> requisitos de convivência.
            </div>
        </div>

        <h3>Entenda o resultado:</h3>

        <div class="lista-criterios">
            <?php foreach ($analise as $item): ?>
                <div class="criterio-item <?php echo $item['status']; ?>">
                    
                    <div class="criterio-topo">
                        <span><?php echo $item['titulo']; ?></span>
                        <span class="badge">
                            <?php echo ($item['status'] === 'sucesso') ? '✓ Deu Certo' : '✕ Incompatível'; ?>
                        </span>
                    </div>

                    <div class="respostas-box">
                        <strong>Sua resposta:</strong> <?php echo htmlspecialchars($item['user']); ?> | 
                        <strong>Pet precisa:</strong> <?php echo htmlspecialchars($item['pet']); ?>
                    </div>

                    <p class="motivo-txt">
                        <?php echo $item['motivo']; ?>
                    </p>

                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

    <div style="text-align: center;">
        <a href="javascript:history.back()" class="btn-voltar">← Voltar para os Detalhes</a>
    </div>

</div>

</body>
</html>