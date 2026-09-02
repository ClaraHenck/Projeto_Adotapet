<?php
// 1. Carrega a verificação de sessão e a conexão com o banco
require_once 'config/auth.php';
require_once 'config/db.php';

// Identifica a ONG logada através da sessão
$ong_id = $_SESSION['ong_id'] ?? $_SESSION['usuario_id'] ?? null;

if (!$ong_id) {
    header("Location: ../login/login.php");
    exit;
}

// 2. PROCESSAMENTO: Alterar Status da Candidatura (Aprovar / Recusar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'alterar_status') {
    $candidatura_id = intval($_POST['candidatura_id']);
    $novo_status = $_POST['novo_status'];

    $status_validos = ['Aprovado', 'Recusado', 'Pendente'];
    
    if (in_array($novo_status, $status_validos)) {
        // Atualiza garantindo por segurança que o animal pertence à ONG logada
        $stmtUpdate = $pdo->prepare("
            UPDATE candidaturas c
            INNER JOIN animais a ON c.animal_id = a.id
            SET c.status_candidatura = :status
            WHERE c.id = :candidatura_id AND a.ong_id = :ong_id
        ");
        $stmtUpdate->execute([
            ':status' => $novo_status,
            ':candidatura_id' => $candidatura_id,
            ':ong_id' => $ong_id
        ]);
    }

    // Redireciona para evitar reenvio do formulário ao atualizar a página
    header("Location: candidaturas_recebidas.php");
    exit;
}

// 3. CONSULTA: Buscar todas as candidaturas vinculadas aos animais desta ONG
$sql = "
    SELECT 
        c.id AS candidatura_id,
        c.status_candidatura,
        c.compatibilidade,
        c.data_envio,
        
        a.nome_completo AS adotante_nome,
        a.email AS adotante_email,
        a.telefone AS adotante_telefone,
        
        p.nome AS pet_nome,
        p.especie_raca AS pet_raca,
        
        q.onde_mora,
        q.tem_area_externa,
        q.horas_fora_casa,
        q.experiencia,
        q.tem_criancas,
        q.tem_outros_animais,
        q.nivel_atividade_fisica
    FROM candidaturas c
    INNER JOIN adotantes a ON c.adotante_id = a.id
    INNER JOIN animais p ON c.animal_id = p.id
    INNER JOIN questionarios q ON c.questionario_id = q.id
    WHERE p.ong_id = :ong_id
    ORDER BY c.data_envio DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':ong_id' => $ong_id]);
$candidaturas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AdotaPet - Candidaturas Recebidas</title>
  <link rel="stylesheet" href="index.css" />
  <link rel="stylesheet" href="candidaturas_recebidas.css" />
  
  <style>
    html, body {
      overflow-y: auto !important;
      height: auto !important;
      background-color: #FFF9F6;
      margin: 0;
      padding: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    }

    .container-painel-ong {
      max-width: 1000px !important;
      margin: 40px auto !important;
      padding: 0 20px !important;
      display: block !important;
    }

    .cabecalho-secao {
      margin-bottom: 30px;
    }

    .cabecalho-secao h1 {
      font-size: 28px;
      color: #22252A;
      margin-bottom: 5px;
    }

    .cabecalho-secao p {
      color: #7A7E85;
      font-size: 15px;
    }

    .lista-cards-ong {
      display: flex !important;
      flex-direction: column !important;
      gap: 20px !important;
    }

    .card-candidato-novo {
      background: #ffffff !important;
      border-radius: 16px !important;
      padding: 24px !important;
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02) !important;
      border: 1px solid #F1F5F9 !important;
      flex-wrap: wrap !important;
      gap: 20px;
    }

    .bloco-infos-esquerda {
      display: flex !important;
      align-items: center !important;
      gap: 20px !important;
      flex: 1;
      min-width: 280px;
    }

    .avatar-circulo-ong {
      width: 60px !important;
      height: 60px !important;
      border-radius: 50% !important;
      background-color: #00C1DE !important;
      color: white !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      font-weight: 700 !important;
      font-size: 18px !important;
      flex-shrink: 0 !important;
    }

    .textos-candidato {
      display: flex !important;
      flex-direction: column !important;
      gap: 4px !important;
    }

    .textos-candidato h3 {
      margin: 0 !important;
      font-size: 18px !important;
      color: #22252A !important;
    }

    .textos-candidato p {
      margin: 0 !important;
      font-size: 14px !important;
      color: #7A7E85 !important;
    }

    .tag-pet-escolhido {
      display: inline-block !important;
      background-color: #F8FAFC !important;
      color: #475569 !important;
      padding: 4px 10px !important;
      border-radius: 6px !important;
      font-size: 12px !important;
      font-weight: 600 !important;
      border: 1px solid #E2E8F0 !important;
      margin-top: 4px;
      width: fit-content;
    }

    .bloco-acoes-direita {
      display: flex !important;
      flex-direction: column !important;
      align-items: flex-end !important;
      gap: 12px !important;
      min-width: 220px;
    }

    .grupo-botoes-fluxo {
      display: flex !important;
      gap: 10px !important;
    }

    .btn-fluxo {
      padding: 10px 18px !important;
      border: none !important;
      border-radius: 20px !important;
      font-weight: 600 !important;
      font-size: 13px !important;
      cursor: pointer !important;
      transition: transform 0.1s, opacity 0.2s;
    }

    .btn-fluxo:hover { opacity: 0.9; }
    .btn-fluxo:active { transform: scale(0.96); }
    .btn-fluxo-aprovar { background-color: #00B67A !important; color: white !important; }
    .btn-fluxo-rejeitar { background-color: #FF4D4D !important; color: white !important; }

    .status-tag-nova {
      padding: 6px 14px !important;
      border-radius: 20px !important;
      font-size: 12px !important;
      font-weight: 700 !important;
      text-transform: uppercase !important;
      letter-spacing: 0.5px !important;
    }
    .status-tag-nova.pendente { background-color: #FFF4E5 !important; color: #FF9800 !important; }
    .status-tag-nova.aprovado { background-color: #E6F9F3 !important; color: #00B67A !important; }
    .status-tag-nova.recusado { background-color: #FFEAEA !important; color: #FF4D4D !important; }

    .nome-clicavel {
      cursor: pointer !important;
      color: #22252A !important;
      display: inline-block !important;
      transition: color 0.2s ease, text-decoration 0.2s ease !important;
    }
    .nome-clicavel:hover {
      color: #00C1DE !important;
      text-decoration: underline !important;
    }

    /* Modal Styling */
    .modal-fundo {
      display: none; 
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      width: 100% !important;
      height: 100% !important;
      background-color: rgba(34, 37, 42, 0.6) !important; 
      backdrop-filter: blur(4px) !important; 
      z-index: 9999 !important;
      align-items: center !important;
      justify-content: center !important;
    }

    .modal-conteudo {
      background-color: #ffffff !important;
      padding: 32px !important;
      border-radius: 20px !important;
      width: 90% !important;
      max-width: 580px !important;
      max-height: 85vh !important; 
      overflow-y: auto !important; 
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
      position: relative !important;
      box-sizing: border-box !important;
      animation: surgimentoModal 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    @keyframes surgimentoModal {
      from { transform: translateY(20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    .modal-fechar {
      position: absolute !important;
      top: 20px !important;
      right: 24px !important;
      font-size: 32px !important;
      line-height: 1 !important;
      cursor: pointer !important;
      color: #A0A5B0 !important;
      border: none !important;
      background: none !important;
      padding: 0 !important;
      transition: color 0.2s !important;
    }
    .modal-fechar:hover { color: #FF4D4D !important; }

    .modal-conteudo h2 {
      margin: 0 0 24px 0 !important;
      color: #22252A !important;
      font-size: 26px !important;
      font-weight: 700 !important;
    }

    .modal-secao-titulo {
      font-size: 13px !important;
      color: #00C1DE !important;
      margin: 24px 0 12px 0 !important;
      font-weight: 700 !important;
      text-transform: uppercase !important;
      letter-spacing: 0.8px !important;
    }

    .info-grid {
      display: grid !important;
      grid-template-columns: repeat(2, 1fr) !important; 
      gap: 16px !important;
      background: #F8FAFC !important;
      padding: 16px !important;
      border-radius: 12px !important;
      border: 1px solid #E2E8F0 !important;
    }

    .info-linha {
      font-size: 14px !important;
      color: #475569 !important;
      line-height: 1.5 !important;
    }

    .info-linha strong {
      color: #22252A !important;
      display: block !important;
      font-size: 12px !important;
      text-transform: uppercase !important;
      letter-spacing: 0.3px !important;
      margin-bottom: 4px !important;
    }

    .questionario-box {
      background: #FFF9F6 !important; 
      padding: 20px !important;
      border-radius: 12px !important;
      border: 1px solid #FFE4D6 !important;
      display: flex !important;
      flex-direction: column !important;
      gap: 16px !important;
    }

    .pergunta-item {
      font-size: 14px !important;
      border-bottom: 1px dashed #E2E8F0 !important;
      padding-bottom: 12px !important;
    }
    .pergunta-item:last-child {
      border-bottom: none !important;
      padding-bottom: 0 !important;
    }

    .pergunta-texto {
      font-weight: 600 !important;
      color: #22252A !important;
      margin-bottom: 6px !important;
      line-height: 1.4 !important;
    }

    .resposta-texto {
      color: #64748B !important;
      line-height: 1.5 !important;
      background-color: #ffffff !important;
      padding: 8px 12px !important;
      border-radius: 6px !important;
      border: 1px solid #F1F5F9 !important;
    }

    @media (max-width: 480px) {
      .info-grid { grid-template-columns: 1fr !important; }
      .modal-conteudo { padding: 20px !important; width: 95% !important; }
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <header class="navbar">
    <div class="left-brand-box">
      <div class="logo">🐾 AdotaPet</div>
    </div>
    <nav class="menu" id="menu-navegacao">
      <a href="index.php">Início</a>
      <a href="candidaturas_recebidas.php" class="active">Candidaturas recebidas</a>
      <a href="minha_ong.php" class="perfil-link-container">
        <span>MINHA ONG</span>
      </a>
    </nav>
  </header>

  <!-- CONTEÚDO PRINCIPAL -->
  <main class="container-painel-ong">
    <div class="cabecalho-secao">
      <h1>Candidaturas Recebidas</h1>
      <p>Clique no <strong>nome do adotante</strong> para visualizar a ficha do perfil e respostas do questionário.</p>
    </div>

    <div class="lista-cards-ong">
      <?php if (empty($candidaturas)): ?>
        <p style="text-align: center; color: #7A7E85; margin-top: 40px; font-size: 16px;">
          Nenhuma candidatura recebida no momento.
        </p>
      <?php else: ?>
        <?php foreach ($candidaturas as $cand): 
          // Iniciais do nome para o avatar
          $nomes = explode(' ', trim($cand['adotante_nome']));
          $iniciais = strtoupper(substr($nomes[0], 0, 1) . (isset($nomes[1]) ? substr($nomes[1], 0, 1) : ''));
          
          // Estilo dinâmico da tag de status
          $status_class = strtolower($cand['status_candidatura']);
          
          // Dados JSON para alimentar o modal no clique do nome
          $jsonData = htmlspecialchars(json_encode($cand), ENT_QUOTES, 'UTF-8');
        ?>
          <div class="card-candidato-novo">
            <div class="bloco-infos-esquerda">
              <div class="avatar-circulo-ong"><?= $iniciais; ?></div>
              <div class="textos-candidato">
                <h3 class="nome-clicavel" onclick="mostrarPerfilAdotante(<?= $jsonData; ?>)">
                  <?= htmlspecialchars($cand['adotante_nome']); ?>
                </h3>
                <p>Contato: <?= htmlspecialchars($cand['adotante_email']); ?> | <?= htmlspecialchars($cand['adotante_telefone']); ?></p>
                <span class="tag-pet-escolhido">
                  🐾 Quer adotar: <?= htmlspecialchars($cand['pet_nome']); ?> (<?= htmlspecialchars($cand['pet_raca']); ?>)
                </span>
              </div>
            </div>

            <div class="bloco-acoes-direita">
              <span class="status-tag-nova <?= $status_class; ?>">
                <?= htmlspecialchars($cand['status_candidatura']); ?>
              </span>

              <div class="grupo-botoes-fluxo">
                <!-- Botão Aprovar -->
                <form method="POST" style="margin: 0;">
                  <input type="hidden" name="action" value="alterar_status">
                  <input type="hidden" name="candidatura_id" value="<?= $cand['candidatura_id']; ?>">
                  <input type="hidden" name="novo_status" value="Aprovado">
                  <button type="submit" class="btn-fluxo btn-fluxo-aprovar">Aprovar</button>
                </form>

                <!-- Botão Rejeitar -->
                <form method="POST" style="margin: 0;">
                  <input type="hidden" name="action" value="alterar_status">
                  <input type="hidden" name="candidatura_id" value="<?= $cand['candidatura_id']; ?>">
                  <input type="hidden" name="novo_status" value="Recusado">
                  <button type="submit" class="btn-fluxo btn-fluxo-rejeitar">Rejeitar</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>

  <!-- MODAL DE PERFIL E QUESTIONÁRIO -->
  <div id="modalPerfil" class="modal-fundo" onclick="fecharModalExterno(event)">
    <div class="modal-conteudo">
      <button class="modal-fechar" onclick="fecharModal()">&times;</button>
      <h2>Ficha do Adotante</h2>
      
      <!-- Dados do Cadastro -->
      <div class="modal-secao-titulo">👤 Informações de Cadastro</div>
      <div class="info-grid">
        <div class="info-linha"><strong>Nome Completo:</strong> <span id="perfNome"></span></div>
        <div class="info-linha"><strong>E-mail:</strong> <span id="perfEmail"></span></div>
        <div class="info-linha"><strong>Telefone:</strong> <span id="perfTelefone"></span></div>
        <div class="info-linha"><strong>Pet Interessado:</strong> <span id="perfPet"></span></div>
      </div>

      <!-- Respostas do Questionário -->
      <div class="modal-secao-titulo">📋 Respostas do Questionário</div>
      <div class="questionario-box">
        <div class="pergunta-item">
          <div class="pergunta-texto">1. Possui outros animais / Nível de Atividade Física?</div>
          <div class="resposta-texto" id="perfQ1"></div>
        </div>
        <div class="pergunta-item">
          <div class="pergunta-texto">2. Tipo de residência / Área Externa?</div>
          <div class="resposta-texto" id="perfQ2"></div>
        </div>
        <div class="pergunta-item">
          <div class="pergunta-texto">3. Crianças em casa / Experiência anterior?</div>
          <div class="resposta-texto" id="perfQ3"></div>
        </div>
        <div class="pergunta-item">
          <div class="pergunta-texto">4. Tempo médio que o pet ficará sozinho por dia?</div>
          <div class="resposta-texto" id="perfQ4"></div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function mostrarPerfilAdotante(data) {
      document.getElementById("perfNome").innerText = data.adotante_nome || "-";
      document.getElementById("perfEmail").innerText = data.adotante_email || "-";
      document.getElementById("perfTelefone").innerText = data.adotante_telefone || "-";
      document.getElementById("perfPet").innerText = (data.pet_nome + " (" + data.pet_raca + ")") || "-";

      // Mapeamento dos campos do banco no questionário
      var possuiAnimais = data.tem_outros_animais == 1 ? "Sim, possui outros animais." : "Não possui outros animais.";
      document.getElementById("perfQ1").innerText = possuiAnimais + " | Nível de atividade: " + data.nivel_atividade_fisica;

      var areaExterna = data.tem_area_externa == 1 ? "Com área externa." : "Sem área externa.";
      document.getElementById("perfQ2").innerText = "Residência: " + data.onde_mora + " (" + areaExterna + ")";

      var temCriancas = data.tem_criancas == 1 ? "Possui crianças em casa." : "Não possui crianças.";
      document.getElementById("perfQ3").innerText = temCriancas + " | Experiência prévia: " + data.experiencia;

      document.getElementById("perfQ4").innerText = "Cerca de " + data.horas_fora_casa + " hora(s) sozinho(a) por dia.";

      document.getElementById("modalPerfil").style.display = "flex";
    }

    function fecharModal() {
      document.getElementById("modalPerfil").style.display = "none";
    }

    function fecharModalExterno(event) {
      if (event.target.id === "modalPerfil") {
        fecharModal();
      }
    }
  </script>
</body>
</html>