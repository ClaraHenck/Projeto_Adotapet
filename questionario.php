<?php
// 1. INICIALIZAÇÃO E AUTENTICAÇÃO
require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/db.php";

// Recupera o ID do adotante armazenado na sessão
$adotante_id = $_SESSION['adotante_id'] ?? $_SESSION['usuario_id'];

// ==========================================================================
// 2. BUSCA RESPOSTAS ANTERIORES (SE EXISTIREM) PARA PREENCHER A TELA
// ==========================================================================
$stmtFetch = $pdo->prepare("SELECT * FROM questionarios WHERE adotante_id = ?");
$stmtFetch->execute([$adotante_id]);
$respostasAtuais = $stmtFetch->fetch(PDO::FETCH_ASSOC) ?: [];

// ==========================================================================
// 3. PROCESSAMENTO DO FORMULÁRIO (POST)
// ==========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta e trata as respostas do formulário
    $ondeMora               = $_POST['ondeMora'] ?? 'apartamento';
    $temAreaExterna         = (isset($_POST['temAreaExterna']) && $_POST['temAreaExterna'] === '1') ? 1 : 0;
    $horasForaCasa          = intval($_POST['horasFora'] ?? 8);
    $experiencia            = $_POST['experiencia'] ?? 'primeira_vez';
    $temCriancas            = (isset($_POST['temCriancas']) && $_POST['temCriancas'] === '1') ? 1 : 0;
    $temOutrosAnimais       = (isset($_POST['temOutrosAnimais']) && $_POST['temOutrosAnimais'] === '1') ? 1 : 0;
    $nivelAtividadeFisica   = $_POST['atividade'] ?? 'moderado';

    // Verifica se o adotante já possui um questionário no banco
    $stmtCheck = $pdo->prepare("SELECT id FROM questionarios WHERE adotante_id = ?");
    $stmtCheck->execute([$adotante_id]);
    $questionarioExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($questionarioExistente) {
        // ATUALIZA (REESCREVE) OS DADOS EXISTENTES
        $sql = "UPDATE questionarios SET 
                    onde_mora = ?, 
                    tem_area_externa = ?, 
                    horas_fora_casa = ?, 
                    experiencia = ?, 
                    tem_criancas = ?, 
                    tem_outros_animais = ?, 
                    nivel_atividade_fisica = ?, 
                    data_resposta = NOW() 
                WHERE adotante_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $ondeMora,
            $temAreaExterna,
            $horasForaCasa,
            $experiencia,
            $temCriancas,
            $temOutrosAnimais,
            $nivelAtividadeFisica,
            $adotante_id
        ]);
    } else {
        // INSERE PELA PRIMEIRA VEZ
        $sql = "INSERT INTO questionarios 
                (adotante_id, onde_mora, tem_area_externa, horas_fora_casa, experiencia, tem_criancas, tem_outros_animais, nivel_atividade_fisica, data_resposta) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $adotante_id,
            $ondeMora,
            $temAreaExterna,
            $horasForaCasa,
            $experiencia,
            $temCriancas,
            $temOutrosAnimais,
            $nivelAtividadeFisica
        ]);
    }

    // Redireciona para o perfil após salvar
    header("Location: adotar.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionário de Adoção</title>
    <style>
        /* --- ESTILIZAÇÃO GERAL --- */
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        body { 
            background-color: #f9fafb; 
            margin: 0; 
            padding: 20px; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            position: relative;
        }

        .container { 
            background: white; 
            padding: 32px; 
            border-radius: 24px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); 
            border: 1px solid #f3f4f6;
            width: 100%; 
            max-width: 480px; 
            position: relative;
            z-index: 10;
        }

        /* --- PATINHAS FLUTUANTES --- */
        .fundo-patinhas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            z-index: 1;
            pointer-events: none;
        }

        .patinha {
            position: absolute;
            bottom: -50px;
            opacity: 0;
            animation: flutuar 15s linear infinite;
        }

        @keyframes flutuar {
            0% { transform: translateY(0) scale(0.8) rotate(0deg); opacity: 1; }
            10% { opacity: 0.12; }
            90% { opacity: 0.12; }
            100% { transform: translateY(-120vh) scale(1.2) rotate(45deg); opacity: 1; }
        }

        /* --- PROGRESSO --- */
        .topo-progresso {
            display: flex;
            justify-content: space-between;
            color: #9ca3af;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .barra-fundo {
            width: 100%;
            height: 8px;
            background-color: #ffe4e6;
            border-radius: 999px;
            overflow: hidden;
            margin-bottom: 32px;
        }

        .barra-preenchimento {
            height: 100%;
            background-color: #f87171;
            width: 14%;
            transition: width 0.3s ease;
        }

        /* --- PASSOS E PERGUNTAS --- */
        .passo { display: none; }
        .passo.ativo { display: block; }

        h2 {
            font-size: 22px;
            color: #1f2937;
            margin: 0 0 24px 0;
            font-weight: 700;
        }

        /* --- CARDS DE SELEÇÃO --- */
        .opcao-card {
            display: flex;
            align-items: center;
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .opcao-card:hover { background-color: #f9fafb; }

        .opcao-card.selecionado {
            border-color: #2bc4b6;
            background-color: #f0fdfa;
        }

        .bola-radio {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #d1d5db;
            margin-right: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .opcao-card.selecionado .bola-radio {
            border-color: #2bc4b6;
        }

        .opcao-card.selecionado .bola-radio::after {
            content: '';
            width: 10px;
            height: 10px;
            background-color: #2bc4b6;
            border-radius: 50%;
        }

        .emoji { font-size: 20px; margin-right: 12px; }
        .texto-opcao { font-weight: 600; color: #4b5563; font-size: 15px; }

        /* --- TOGGLE --- */
        .toggle-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f9fafb;
            padding: 16px;
            border-radius: 16px;
        }

        .toggle-switch {
            width: 56px;
            height: 32px;
            background-color: #d1d5db;
            border-radius: 999px;
            padding: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            position: relative;
        }

        .toggle-switch .pino {
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .toggle-container.ativo .toggle-switch { background-color: #2bc4b6; }
        .toggle-container.ativo .toggle-switch .pino { transform: translateX(24px); }

        /* --- SLIDER --- */
        .range-container { text-align: center; }
        .contador-horas { font-size: 48px; font-weight: 700; color: #2bc4b6; margin-bottom: 16px; }
        .contador-horas span { font-size: 18px; color: #9ca3af; font-weight: 400; }
        
        input[type="range"] {
            width: 100%;
            accent-color: #2bc4b6;
            margin-bottom: 8px;
            cursor: pointer;
        }
        .range-labels { display: flex; justify-content: space-between; color: #9ca3af; font-size: 12px; font-weight: 600; }

        /* --- BOTÕES --- */
        .botoes-navegacao {
            margin-top: 32px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        button {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-proximo {
            background-color: #2bc4b6;
            color: white;
            box-shadow: 0 4px 12px rgba(43, 196, 182, 0.15);
        }

        .btn-proximo:hover { background-color: #24a89c; }

        .btn-voltar {
            background: none;
            color: #9ca3af;
            font-size: 14px;
            padding: 4px;
            width: auto;
            align-self: flex-start;
        }
        .btn-voltar:hover { color: #4b5563; }
    </style>
</head>
<body>

    <div class="fundo-patinhas">
        <div class="patinha" style="left: 8%; animation-delay: 0s; animation-duration: 14s; font-size: 24px;">🐾</div>
        <div class="patinha" style="left: 22%; animation-delay: 4s; animation-duration: 18s; font-size: 36px;">🐾</div>
        <div class="patinha" style="left: 38%; animation-delay: 8s; animation-duration: 15s; font-size: 26px;">🐾</div>
        <div class="patinha" style="left: 55%; animation-delay: 2s; animation-duration: 16s; font-size: 32px;">🐾</div>
        <div class="patinha" style="left: 72%; animation-delay: 11s; animation-duration: 22s; font-size: 24px;">🐾</div>
        <div class="patinha" style="left: 88%; animation-delay: 6s; animation-duration: 13s; font-size: 40px;">🐾</div>
    </div>

    <form id="form-questionario" method="POST" action="questionario.php" class="container">
        <!-- Campos Ocultos com valores pré-carregados se existirem -->
        <input type="hidden" name="ondeMora" id="inp_ondeMora" value="<?= htmlspecialchars($respostasAtuais['onde_mora'] ?? '') ?>">
        <input type="hidden" name="temAreaExterna" id="inp_temAreaExterna" value="<?= !empty($respostasAtuais['tem_area_externa']) ? '1' : '0' ?>">
        <input type="hidden" name="horasFora" id="inp_horasFora" value="<?= intval($respostasAtuais['horas_fora_casa'] ?? 8) ?>">
        <input type="hidden" name="experiencia" id="inp_experiencia" value="<?= htmlspecialchars($respostasAtuais['experiencia'] ?? '') ?>">
        <input type="hidden" name="temCriancas" id="inp_temCriancas" value="<?= !empty($respostasAtuais['tem_criancas']) ? '1' : '0' ?>">
        <input type="hidden" name="temOutrosAnimais" id="inp_temOutrosAnimais" value="<?= !empty($respostasAtuais['tem_outros_animais']) ? '1' : '0' ?>">
        <input type="hidden" name="atividade" id="inp_atividade" value="<?= htmlspecialchars($respostasAtuais['nivel_atividade_fisica'] ?? '') ?>">

        <button type="button" class="btn-voltar" id="btn-voltar" onclick="mudarPasso(-1)" style="display: none;">← Voltar</button>

        <div class="topo-progresso">
            <span id="txt-passo">Passo 1 de 7</span>
            <span id="txt-porcentagem">14%</span>
        </div>
        <div class="barra-fundo">
            <div class="barra-preenchimento" id="barra"></div>
        </div>

        <!-- PASSO 1 -->
        <div class="passo ativo" id="p1">
            <h2>Onde você mora?</h2>
            <div class="opcao-card" data-val="apartamento" onclick="selecionarOpcao('ondeMora', 'apartamento', this)">
                <div class="bola-radio"></div>
                <span class="emoji">🏢</span>
                <span class="texto-opcao">Apartamento</span>
            </div>
            <div class="opcao-card" data-val="casa_sem_quintal" onclick="selecionarOpcao('ondeMora', 'casa_sem_quintal', this)">
                <div class="bola-radio"></div>
                <span class="emoji">🏠</span>
                <span class="texto-opcao">Casa sem quintal</span>
            </div>
            <div class="opcao-card" data-val="casa_com_quintal" onclick="selecionarOpcao('ondeMora', 'casa_com_quintal', this)">
                <div class="bola-radio"></div>
                <span class="emoji">🏠</span>
                <span class="texto-opcao">Casa com quintal</span>
            </div>
            <div class="opcao-card" data-val="sitio" onclick="selecionarOpcao('ondeMora', 'sitio', this)">
                <div class="bola-radio"></div>
                <span class="emoji">🌲</span>
                <span class="texto-opcao">Sítio/Chácara</span>
            </div>
        </div>

        <!-- PASSO 2 -->
        <div class="passo" id="p2">
            <h2>Tem área externa?</h2>
            <div class="toggle-container" id="container-externa" onclick="alternarToggle('temAreaExterna', 'container-externa', 'txt-externa')">
                <span class="texto-opcao" id="txt-externa">Não</span>
                <div class="toggle-switch"><div class="pino"></div></div>
            </div>
        </div>

        <!-- PASSO 3 -->
        <div class="passo" id="p3">
            <h2>Quantas horas por dia fica fora de casa?</h2>
            <div class="range-container">
                <div class="contador-horas"><span id="txt-horas"><?= intval($respostasAtuais['horas_fora_casa'] ?? 8) ?></span> <span>horas</span></div>
                <input type="range" id="input-range-horas" min="0" max="16" value="<?= intval($respostasAtuais['horas_fora_casa'] ?? 8) ?>" oninput="atualizarHoras(this.value)">
                <div class="range-labels"><span>0 h</span><span>16 h</span></div>
            </div>
        </div>

        <!-- PASSO 4 -->
        <div class="passo" id="p4">
            <h2>Experiência com animais?</h2>
            <div class="opcao-card" data-val="primeira_vez" onclick="selecionarOpcao('experiencia', 'primeira_vez', this)">
                <div class="bola-radio"></div>
                <span class="emoji">🐾</span>
                <span class="texto-opcao">Primeira vez</span>
            </div>
            <div class="opcao-card" data-val="ja_teve" onclick="selecionarOpcao('experiencia', 'ja_teve', this)">
                <div class="bola-radio"></div>
                <span class="emoji">🐾</span>
                <span class="texto-opcao">Já tive animais</span>
            </div>
            <div class="opcao-card" data-val="experiente" onclick="selecionarOpcao('experiencia', 'experiente', this)">
                <div class="bola-radio"></div>
                <span class="emoji">🏆</span>
                <span class="texto-opcao">Experiente</span>
            </div>
        </div>

        <!-- PASSO 5 -->
        <div class="passo" id="p5">
            <h2>Tem crianças em casa?</h2>
            <div class="toggle-container" id="container-criancas" onclick="alternarToggle('temCriancas', 'container-criancas', 'txt-criancas')">
                <span class="texto-opcao" id="txt-criancas">Não</span>
                <div class="toggle-switch"><div class="pino"></div></div>
            </div>
        </div>

        <!-- PASSO 6 -->
        <div class="passo" id="p6">
            <h2>Tem outros animais?</h2>
            <div class="toggle-container" id="container-outros" onclick="alternarToggle('temOutrosAnimais', 'container-outros', 'txt-outros')">
                <span class="texto-opcao" id="txt-outros">Não</span>
                <div class="toggle-switch"><div class="pino"></div></div>
            </div>
        </div>

        <!-- PASSO 7 -->
        <div class="passo" id="p7">
            <h2>Qual seu nível de atividade física?</h2>
            <div class="opcao-card" data-val="sedentario" onclick="selecionarOpcao('atividade', 'sedentario', this)">
                <div class="bola-radio"></div>
                <span class="emoji">🛋️</span>
                <span class="texto-opcao">Sedentário</span>
            </div>
            <div class="opcao-card" data-val="moderado" onclick="selecionarOpcao('atividade', 'moderado', this)">
                <div class="bola-radio"></div>
                <span class="emoji">🚶</span>
                <span class="texto-opcao">Moderado</span>
            </div>
            <div class="opcao-card" data-val="ativo" onclick="selecionarOpcao('atividade', 'ativo', this)">
                <div class="bola-radio"></div>
                <span class="emoji">🏃</span>
                <span class="texto-opcao">Ativo</span>
            </div>
        </div>

        <div class="botoes-navegacao">
            <button type="button" class="btn-proximo" id="btn-proximo" onclick="mudarPasso(1)">Próximo →</button>
        </div>
    </form>

    <script>
        let passoAtual = 1;
        const totalPassos = 7;

        // Recupera valores salvos previamente do PHP
        const dadosFormulario = {
            ondeMora: document.getElementById('inp_ondeMora').value,
            temAreaExterna: document.getElementById('inp_temAreaExterna').value === '1',
            horasFora: parseInt(document.getElementById('inp_horasFora').value),
            experiencia: document.getElementById('inp_experiencia').value,
            temCriancas: document.getElementById('inp_temCriancas').value === '1',
            temOutrosAnimais: document.getElementById('inp_temOutrosAnimais').value === '1',
            atividade: document.getElementById('inp_atividade').value
        };

        // Preenche visualmente a tela ao carregar caso o usuário já tenha respostas salvas
        window.addEventListener('DOMContentLoaded', () => {
            if (dadosFormulario.ondeMora) {
                const el = document.querySelector(`#p1 .opcao-card[data-val="${dadosFormulario.ondeMora}"]`);
                if (el) el.classList.add('selecionado');
            }
            if (dadosFormulario.experiencia) {
                const el = document.querySelector(`#p4 .opcao-card[data-val="${dadosFormulario.experiencia}"]`);
                if (el) el.classList.add('selecionado');
            }
            if (dadosFormulario.atividade) {
                const el = document.querySelector(`#p7 .opcao-card[data-val="${dadosFormulario.atividade}"]`);
                if (el) el.classList.add('selecionado');
            }
            if (dadosFormulario.temAreaExterna) {
                document.getElementById('container-externa').classList.add('ativo');
                document.getElementById('txt-externa').innerText = 'Sim';
            }
            if (dadosFormulario.temCriancas) {
                document.getElementById('container-criancas').classList.add('ativo');
                document.getElementById('txt-criancas').innerText = 'Sim';
            }
            if (dadosFormulario.temOutrosAnimais) {
                document.getElementById('container-outros').classList.add('ativo');
                document.getElementById('txt-outros').innerText = 'Sim';
            }
        });

        function mudarPasso(direcao) {
            if (direcao === 1) {
                if (passoAtual === 1 && dadosFormulario.ondeMora === '') {
                    alert("Por favor, selecione onde você mora antes de prosseguir!");
                    return;
                }
                if (passoAtual === 4 && dadosFormulario.experiencia === '') {
                    alert("Por favor, selecione sua experiência com animais antes de prosseguir!");
                    return;
                }
                if (passoAtual === 7 && dadosFormulario.atividade === '') {
                    alert("Por favor, selecione seu nível de atividade física antes de finalizar!");
                    return;
                }
            }

            document.getElementById('p' + passoAtual).classList.remove('ativo');
            passoAtual += direcao;

            if (passoAtual > totalPassos) {
                finalizarFormulario();
                return;
            }

            document.getElementById('p' + passoAtual).classList.add('ativo');

            let porcentagem = Math.round((passoAtual / totalPassos) * 100);
            document.getElementById('txt-passo').innerText = `Passo ${passoAtual} de ${totalPassos}`;
            document.getElementById('txt-porcentagem').innerText = `${porcentagem}%`;
            document.getElementById('barra').style.width = `${porcentagem}%`;

            document.getElementById('btn-voltar').style.display = (passoAtual > 1) ? 'block' : 'none';
            document.getElementById('btn-proximo').innerText = (passoAtual === totalPassos) ? 'Atualizar Perfil' : 'Próximo →';
        }

        function selecionarOpcao(campo, valor, elemento) {
            dadosFormulario[campo] = valor;
            document.getElementById('inp_' + campo).value = valor;

            const pai = elemento.parentElement;
            pai.querySelectorAll('.opcao-card').forEach(card => card.classList.remove('selecionado'));

            elemento.classList.add('selecionado');
        }

        function alternarToggle(campo, idContainer, idTexto) {
            dadosFormulario[campo] = !dadosFormulario[campo];
            document.getElementById('inp_' + campo).value = dadosFormulario[campo] ? "1" : "0";
            
            const container = document.getElementById(idContainer);
            const texto = document.getElementById(idTexto);

            if (dadosFormulario[campo]) {
                container.classList.add('ativo');
                texto.innerText = 'Sim';
            } else {
                container.classList.remove('ativo');
                texto.innerText = 'Não';
            }
        }

        function atualizarHoras(valor) {
            dadosFormulario.horasFora = parseInt(valor);
            document.getElementById('inp_horasFora').value = valor;
            document.getElementById('txt-horas').innerText = valor;
        }

        function finalizarFormulario() {
            document.getElementById('form-questionario').submit();
        }
    </script>
</body>
</html