<?php
session_start();

$logado = isset($_SESSION["usuario_id"]);
$usuario_nome =$_SESSION["usuario_nome"] ?? "";
$pode_cadastrar =$_SESSION["pode_cadastrar"] ?? 0;

$tipoUsuario = "";

if ($logado) {
    $tipoUsuario = ($pode_cadastrar == 1) ? "ong" : "adotante";
}

/*
Página exclusiva para ONGs (ou acessível conforme regra do sistema).
Caso queira restringir o acesso apenas a ONGs logadas:
*/
if (!$logado || $tipoUsuario !== "ong") {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AdotaPet - Guia do Papel da ONG no Site</title>
  
  <!-- CSS Global do Header -->
  <link rel="stylesheet" href="navbar.css">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- Google Fonts - Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            pastel: {
              bg: '#FAF8F5',
              card: '#FFFFFF',
              orangeBg: '#FFF3EB',
              orangeAccent: '#FF7A59',
              pinkBg: '#FFF0F5',
              pinkAccent: '#FF5C8A',
              blueBg: '#EEF6FF',
              blueAccent: '#3B82F6',
              purpleBg: '#F5F0FF',
              purpleAccent: '#8B5CF6',
              greenBg: '#ECFDF5',
              greenAccent: '#10B981',
              amberBg: '#FEF3C7',
              amberAccent: '#D97706'
            }
          },
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
          },
          boxShadow: {
            'soft': '0 10px 30px -5px rgba(0, 0, 0, 0.04), 0 4px 12px -2px rgba(0, 0, 0, 0.025)',
            'card-hover': '0 20px 35px -10px rgba(0, 0, 0, 0.08), 0 8px 16px -4px rgba(0, 0, 0, 0.04)',
          }
        }
      }
    }
  </script>

  <style>
    body {
      background-color: #FAF8F5;
      font-family: 'Plus Jakarta Sans', sans-serif;
      color: #2D3748;
    }
    
    /* Background subtle paw texture */
    .paw-pattern {
      background-image: radial-gradient(#FF7A59 0.75px, transparent 0.75px), radial-gradient(#3B82F6 0.75px, #FAF8F5 0.75px);
      background-size: 32px 32px;
      background-position: 0 0, 16px 16px;
      opacity: 0.12;
    }

    /* Custom smooth transitions for tab changes */
    .tab-active {
      border-color: #FF7A59 !important;
      background-color: #FFFFFF !important;
      box-shadow: 0 12px 28px -6px rgba(255, 122, 89, 0.22) !important;
      transform: translateY(-2px);
    }

    .custom-scrollbar::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
      background: #F1F1F1;
      border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #CBD5E1;
      border-radius: 10px;
    }
  </style>
</head>
<body class="min-h-screen pb-16 relative bg-[#FAF8F5]">

  <!-- Background Paw Pattern Overlay -->
  <div class="fixed inset-0 pointer-events-none z-0 paw-pattern"></div>

  <!-- =========================
       HEADER (PADRONIZADO)
  ========================= -->
  <header class="navbar relative z-50">
    <div class="left-brand-box">
        <a href="index.php" class="logo" style="text-decoration: none;">🐾 AdotaPet</a>
    </div>

    <nav class="menu" id="menu-navegacao">
        <a href="index.php" class="active">Início</a>

        <?php if ($logado &&$tipoUsuario === "ong"): ?>
            <a href="meus_animais.php" id="link-meus-animais">Meus Animais</a>
        <?php endif; ?>

        <?php if (!$logado || $tipoUsuario === "adotante"): ?>
            <a href="adotar.php" id="link-adotar">Adotar</a>
        <?php endif; ?>

        <?php if ($logado): ?>
            <?php if ($tipoUsuario !== 'ong'): ?>
                <a href="mapa.php" id="link-mapa">Mapa</a>
            <?php endif; ?>

            <a href="<?php echo ($tipoUsuario === 'ong') ? 'candidaturas_recebidas.php' : 'candidaturas.php'; ?>" id="link-candidaturas">
                <?php echo ($tipoUsuario === 'ong') ? 'Candidaturas Recebidas' : 'Candidaturas'; ?>
            </a>
        <?php endif; ?>

        <?php if ($logado &&$tipoUsuario === "adotante"): ?>
            <a href="pos_adocao.php" id="link-pos-adocao" title="Guia de Pós-Adoção">
                🐾 Pós-Adoção
            </a>
        <?php endif; ?>

        <div id="area-usuario-nav" style="display: flex; align-items: center; gap: 24px;">
            <?php if (!$logado): ?>
                <a href="../login/login.php" class="btn-nav-login">Entrar</a>
                <a href="../login/cadastrar.php" class="btn-nav-cadastro">Cadastrar-se</a>
            <?php else: ?>
                <?php 
                    $linkHref = ($tipoUsuario === "ong") ? "minha_ong.php" : "meu_perfil.php";
                    $textoPerfil = "Meu Perfil";
                ?>
                <a href="<?php echo $linkHref; ?>" class="perfil-link-container" style="text-decoration: none;">
                    <span style="font-weight: 500; color: #718096;"><?php echo $textoPerfil; ?></span>
                </a>
                <a href="logout.php" style="color: #ff4d4d; font-weight: 500; text-decoration: none;">Sair</a>
            <?php endif; ?>
        </div>
    </nav>
  </header>

  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-8 relative z-10">

    <!-- Hero / Intro Section: Os Dois Pilares Cruciais -->
    <section class="bg-white rounded-3xl p-6 sm:p-8 border border-orange-100/80 shadow-soft relative overflow-hidden">
      <div class="absolute -top-20 -right-20 w-72 h-72 bg-pastel-orangeBg rounded-full filter blur-3xl opacity-70 pointer-events-none"></div>
      <div class="absolute -bottom-20 -left-20 w-72 h-72 bg-pastel-blueBg rounded-full filter blur-3xl opacity-60 pointer-events-none"></div>

      <div class="relative z-10 space-y-5">
        <div class="inline-flex items-center gap-2 bg-pastel-orangeBg px-3.5 py-1.5 rounded-full border border-orange-200/80 text-orange-800 text-xs font-extrabold">
          <i class="fa-solid fa-heart text-orange-500"></i> Como a ONG transforma a adoção no AdotaPet
        </div>

        <div class="max-w-3xl space-y-2">
          <h1 class="text-2xl sm:text-3xl font-black text-gray-800 tracking-tight leading-tight">
            O Papel Estratégico da ONG: Conectar Vidas com Precisão
          </h1>
          <p class="text-xs sm:text-sm text-gray-600 leading-relaxed font-medium">
            No AdotaPet, sua ONG não apenas lista animais: você ativa um <strong class="text-gray-800 font-bold">mecanismo inteligente de compatibilidade</strong>. A precisão do nosso algoritmo depende diretamente da riqueza das informações cadastradas de forma minuciosa no perfil do pet.
          </p>
        </div>

        <!-- Highlights for the 2 Most Important Pillars -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
          <!-- Pillar 1: BIO da ONG -->
          <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-pink-50/70 to-orange-50/50 border border-rose-100 flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
              <i class="fa-solid fa-building-shield"></i>
            </div>
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <h3 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider">Pilar 1: BIO Institucional</h3>
                <span class="text-[10px] font-black bg-rose-100 text-rose-800 px-2 py-0.5 rounded-md">Credibilidade</span>
              </div>
              <p class="text-xs text-gray-600 leading-relaxed">
                Transmita confiança com história, regras de adoção, área de atuação e chave Pix oficial para captação de recursos.
              </p>
            </div>
          </div>

          <!-- Pillar 2: Cadastro Rico -->
          <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-orange-50/70 to-amber-50/50 border border-orange-200 flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
              <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <h3 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider">Pilar 2: Cadastro Rico do Pet</h3>
                <span class="text-[10px] font-black bg-orange-100 text-orange-800 px-2 py-0.5 rounded-md">Coração do Site</span>
              </div>
              <p class="text-xs text-gray-600 leading-relaxed">
                Preencha detalhes de saúde, temperamento, sociabilidade e rotina ideal. Isso garante matches perfeitos e muito mais rápidos.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: O Ciclo de Sucesso da ONG no AdotaPet -->
    <section class="space-y-6">
      <div class="text-center max-w-2xl mx-auto space-y-2">
        <span class="text-xs font-black text-orange-600 bg-pastel-orangeBg px-3.5 py-1 rounded-full uppercase tracking-wider border border-orange-200/60">
          Passo a Passo da Instituição
        </span>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-800">O Ciclo de Sucesso da ONG no AdotaPet</h2>
        <p class="text-xs text-gray-500 font-medium">Clique em cada módulo abaixo para explorar o papel prático e as ações recomendadas.</p>
      </div>

      <!-- Navigation Steps Bar / Selector Grid (4 PASSOS) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        
        <!-- Step 1 Tab -->
        <button onclick="selectStep(1)" id="stepBtn1" class="step-nav-btn tab-active bg-white p-4 rounded-3xl border-2 border-orange-200 text-left transition-all duration-300 hover:border-orange-400 group relative">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-black text-orange-600 bg-pastel-orangeBg px-2 py-0.5 rounded-md">PASSO 1</span>
            <div class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xs">
              <i class="fa-solid fa-id-card"></i>
            </div>
          </div>
          <h3 class="font-extrabold text-gray-800 text-xs group-hover:text-orange-600 transition">BIO & Credibilidade</h3>
          <p class="text-[11px] text-gray-400 line-clamp-1 mt-0.5">Perfil institucional completo</p>
        </button>

        <!-- Step 2 Tab -->
        <button onclick="selectStep(2)" id="stepBtn2" class="step-nav-btn bg-white p-4 rounded-3xl border-2 border-gray-100 text-left transition-all duration-300 hover:border-purple-300 group relative">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-black text-purple-600 bg-pastel-purpleBg px-2 py-0.5 rounded-md">PASSO 2</span>
            <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xs">
              <i class="fa-solid fa-file-pen"></i>
            </div>
          </div>
          <h3 class="font-extrabold text-gray-800 text-xs group-hover:text-purple-600 transition">Cadastro Rico do Pet</h3>
          <p class="text-[11px] text-gray-400 line-clamp-1 mt-0.5">Alimentando o Algoritmo</p>
        </button>

        <!-- Step 3 Tab -->
        <button onclick="selectStep(3)" id="stepBtn3" class="step-nav-btn bg-white p-4 rounded-3xl border-2 border-gray-100 text-left transition-all duration-300 hover:border-blue-300 group relative">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-black text-blue-600 bg-pastel-blueBg px-2 py-0.5 rounded-md">PASSO 3</span>
            <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">
              <i class="fa-solid fa-sliders"></i>
            </div>
          </div>
          <h3 class="font-extrabold text-gray-800 text-xs group-hover:text-blue-600 transition">Match em Ação</h3>
          <p class="text-[11px] text-gray-400 line-clamp-1 mt-0.5">Cruzamento inteligente</p>
        </button>

        <!-- Step 4 Tab -->
        <button onclick="selectStep(4)" id="stepBtn4" class="step-nav-btn bg-white p-4 rounded-3xl border-2 border-gray-100 text-left transition-all duration-300 hover:border-pink-300 group relative">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-black text-pink-600 bg-pastel-pinkBg px-2 py-0.5 rounded-md">PASSO 4</span>
            <div class="w-6 h-6 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center text-xs">
              <i class="fa-solid fa-comments text-xs"></i>
            </div>
          </div>
          <h3 class="font-extrabold text-gray-800 text-xs group-hover:text-pink-600 transition">Triagem & Conclusão</h3>
          <p class="text-[11px] text-gray-400 line-clamp-1 mt-0.5">Avaliação e decisão final</p>
        </button>

      </div>

      <!-- Expanded Dynamic Display Panel -->
      <div id="stepDisplayPanel" class="bg-white p-6 sm:p-8 rounded-3xl border border-orange-100 shadow-soft transition-all duration-300">
        <!-- Content dynamically injected by JavaScript -->
      </div>
    </section>

  </main>

  <script>
    // Data structures for 4 steps of the NGO Success Cycle
    const cycleData = {
      1: {
        stepNumber: 1,
        tag: "PASSO 1: CONFIAVEL & TRANSPARENTE",
        title: "BIO & Credibilidade da Instituição",
        badge: "Pilar Institucional",
        badgeColor: "bg-rose-100 text-rose-800",
        description: "A página institucional da ONG no AdotaPet funciona como o cartão de visitas seguro que acalma e encanta o futuro adotante.",
        actions: [
          { title: "Apresentação & Missão", desc: "Conte a história da fundação da ONG, total de resgatados e a paixão da equipe." },
          { title: "Área de Atuação & Vistorias", desc: "Especifique as cidades e bairros atendidos para entregas presenciais e visitas." },
          { title: "Regras do Processo Seletivo", desc: "Deixe claro se exige apartamento telado, casa com muros altos ou idade mínima do tutor." },
          { title: "Chave Pix para Apoio", desc: "Cadastre a chave Pix oficial da ONG para receber doações espontâneas de visitantes do perfil." }
        ],
        goldenTip: "ONGs que preenchem 100% da sua BIO transmitem 4x mais segurança e recebem candidaturas mais maduras."
      },
      2: {
        stepNumber: 2,
        tag: "PASSO 2: O CORAÇÃO DO SITE",
        title: "Cadastro Rico & Algoritmo de Match",
        badge: "Maior Impacto de Adoção",
        badgeColor: "bg-purple-100 text-purple-800",
        description: "Para que o algoritmo funcione, a ONG precisa preencher minuciosamente os dados do pet. Perfis rasos geram matches incorretos.",
        actions: [
          { title: "Histórico Completo de Saúde", desc: "Vacinas em dia, status de castração, vermifugação e necessidades médicas contínuas." },
          { title: "Mapeamento Temperamental", desc: "Indique se o animal é calmo, brincalhão, tímido, protetor ou muito carinhoso." },
          { title: "Sociabilidade Multiespécie", desc: "Marque expressamente a afinidade com crianças, gatos e outros cachorros." },
          { title: "Nível de Energia e Rotina Ideal", desc: "Detalhe a frequência de passeios necessária, tempo diário de atenção e comportamento a sós." }
        ],
        goldenTip: "Um perfil rico reduz em 85% as chances de devolução do pet por incompatibilidade na rotina!"
      },
      3: {
        stepNumber: 3,
        tag: "PASSO 3: TECNOLOGIA INTELIGENTE",
        title: "Algoritmo de Compatibilidade em Ação",
        badge: "Match Automatizado",
        badgeColor: "bg-blue-100 text-blue-800",
        description: "Com o perfil rico cadastrado pela ONG, o AdotaPet realiza o cruzamento de dados em tempo real assim que o adotante faz a pesquisa.",
        actions: [
          { title: "Cálculo de Afinidade (%)", desc: "O sistema compara a rotina da família com a ficha do pet para gerar uma nota percentual." },
          { title: "Filtro Antidevolução", desc: "Gatos hiperativos não são indicados para lares com rotina calma sem enriquecimento ambiental." },
          { title: "Priorização no Feed", desc: "Os animais mais bem cadastrados ganham destaque prioritário na busca dos adotantes no aplicativo." }
        ],
        goldenTip: "O algoritmo economiza horas de triagem manual da ONG, sugerindo apenas candidatos pré-compatíveis."
      },
      4: {
        stepNumber: 4,
        tag: "PASSO 4: AVALIAÇÃO & DECISÃO",
        title: "Triagem & Conclusão da Candidatura",
        badge: "Processo Humanizado",
        badgeColor: "bg-pink-100 text-pink-800",
        description: "No painel da ONG, as candidaturas chegam organizadas pelo grau de compatibilidade para facilitar a análise e finalização.",
        actions: [
          { title: "Análise do Perfil do Adotante", desc: "Verifique a rotina da família, horas que o pet ficará só e estrutura do imóvel." },
          { title: "Contato via WhatsApp / Entrevista", desc: "Converse diretamente com o candidato para tirar dúvidas ou agendar uma visita presencial." },
          { title: "Atualização de Status", desc: "Atualize o status da candidatura no painel para 'Aprovado' ou 'Concluído' para retirar o pet da busca ativa." }
        ],
        goldenTip: "Responder aos candidatos em menos de 24h mantém o entusiasmo da família no auge do processo."
      }
    };

    // Render step details inside the display panel
    function selectStep(stepNum) {
      // Update Tab Styles
      document.querySelectorAll('.step-nav-btn').forEach((btn, idx) => {
        if (idx + 1 === stepNum) {
          btn.classList.add('tab-active');
          btn.classList.remove('border-gray-100');
        } else {
          btn.classList.remove('tab-active');
          btn.classList.add('border-gray-100');
        }
      });

      const data = cycleData[stepNum];
      const panel = document.getElementById('stepDisplayPanel');

      panel.innerHTML = `
        <div class="space-y-5 animate-fade-in">
          <!-- Step Header -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-4">
            <div>
              <span class="text-[10px] font-black text-orange-600 tracking-wider uppercase">${data.tag}</span>
              <h3 class="text-xl sm:text-2xl font-black text-gray-800">${data.title}</h3>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full ${data.badgeColor} w-max">${data.badge}</span>
          </div>

          <p class="text-xs sm:text-sm text-gray-600 leading-relaxed font-medium">${data.description}</p>

          <!-- List of Actions for this Step -->
          <div class="space-y-3 pt-1">
            <h4 class="text-xs font-extrabold text-gray-700 uppercase tracking-wider">Ações Práticas Recomendadas para a ONG:</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              ${data.actions.map(act => `
                <div class="p-4 rounded-2xl bg-pastel-bg border border-gray-100 hover:border-orange-200 transition space-y-1">
                  <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm shrink-0"></i>
                    <h5 class="text-xs font-extrabold text-gray-800">${act.title}</h5>
                  </div>
                  <p class="text-[11px] text-gray-500 leading-normal pl-5">${act.desc}</p>
                </div>
              `).join('')}
            </div>
          </div>

          <!-- Golden Tip Box -->
          <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200/80 text-xs text-amber-900 font-medium flex items-center gap-3">
            <i class="fa-solid fa-lightbulb text-amber-500 text-lg shrink-0"></i>
            <span><strong>Dica da Plataforma:</strong> ${data.goldenTip}</span>
          </div>

          <!-- Bottom Nav buttons -->
          <div class="flex items-center justify-between pt-4 border-t border-gray-100 text-xs font-bold">
            <button onclick="selectStep(${stepNum > 1 ? stepNum - 1 : 4})" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 transition flex items-center gap-2">
              <i class="fa-solid fa-arrow-left"></i> Passo Anterior
            </button>
            <span class="text-gray-400 font-semibold">${stepNum} de 4</span>
            <button onclick="selectStep(${stepNum < 4 ? stepNum + 1 : 1})" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white transition flex items-center gap-2 shadow-sm">
              Próximo Passo <i class="fa-solid fa-arrow-right"></i>
            </button>
          </div>
        </div>
      `;
    }

    // Initialize default view on load
    window.onload = function() {
      selectStep(1);
    };
  </script>
</body>
</html>