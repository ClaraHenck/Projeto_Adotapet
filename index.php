<?php

session_start();

$logado = isset($_SESSION["usuario_id"]);
$usuario_nome =$_SESSION["usuario_nome"] ?? "";
$pode_cadastrar =$_SESSION["pode_cadastrar"] ?? 0;

$tipoUsuario = "";

if ($logado) {
    $tipoUsuario = ($pode_cadastrar == 1) ? "ong" : "adotante";
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>AdotaPet - Plataforma de Adoção</title>

    <link rel="stylesheet" href="navbar.css" />
    <link rel="stylesheet" href="index.css" />

    <style>

        /* =========================
           CARROSSEL DE CACHORROS
        ========================= */

        .hero-image-box {
            position: relative;
            width: 50%;
            max-width: 600px;
        }

        .carousel {
            position: relative;
            width: 100%;
            height: 500px;
            overflow: hidden;
            border-radius: 25px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            background-color: #f5f5f5;
        }

        .carousel-track {
            display: flex;
            width: 100%;
            height: 100%;
            transition: transform 0.8s ease-in-out;
        }

        .carousel-slide {
            min-width: 100%;
            width: 100%;
            height: 100%;
            flex-shrink: 0;
        }

        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }


        /* =========================
           RESPONSIVIDADE
        ========================= */

        @media (max-width: 900px) {

            .hero-container {
                flex-direction: column;
            }

            .hero-image-box {
                width: 100%;
                max-width: 600px;
            }

            .carousel {
                height: 400px;
            }
        }


        @media (max-width: 600px) {

            .carousel {
                height: 320px;
                border-radius: 18px;
            }
        }

    </style>

</head>


<body>


    <header class="navbar">

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

        <!-- ====================================
             PÓS-ADOÇÃO
             SOMENTE PARA ADOTANTES
        ==================================== -->
        <?php if ($logado &&$tipoUsuario === "adotante"): ?>
            <a
                href="pos_adocao.php"
                id="link-pos-adocao"
                class="link-pos-adocao"
                title="Guia de Pós-Adoção"
            >
                🐾 Pós-Adoção
            </a>
        <?php endif; ?>

        <div id="area-usuario-nav" style="display: flex; align-items: center; gap: 24px;">
            <?php if (!$logado): ?>
                <a href="../projeto_adotapet/login/login.php" class="btn-nav-login">Entrar</a>
                <a href="../projeto_adotapet/login/cadastrar.php" class="btn-nav-cadastro">Cadastrar-se</a>
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



    <main class="hero-container">


        <!-- =========================
             TEXTO PRINCIPAL
        ========================= -->

        <section class="hero-text">

            <span class="badge">
                ✨ Plataforma de Adoção Responsável
            </span>

            <h1>
                Encontre seu
                <br />
                <span class="highlight">
                    melhor amigo
                </span>
                <br />
                para a vida toda
            </h1>

            <p id="hero-descricao">
                <?php if ($tipoUsuario === "ong"): ?>
                    Gerencie seus animais cadastrados, avalie candidaturas de adoção e encontre tutores responsáveis.
                <?php else: ?>
                    A AdotaPet conecta você ao animal perfeito com um algoritmo. Um lar amoroso está a poucos clicks de distância.
                <?php endif; ?>
            </p>

            <div
                class="buttons"
                id="botoes-container"
                style="
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                    align-items: flex-start;
                "
            >

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">

                    <?php if ($tipoUsuario !== "ong"): ?>

                        <a
                            href="adotar.php"
                            id="btn-encontrar"
                            class="btn-primary"
                            style="
                                text-decoration: none;
                                display: inline-block;
                                text-align: center;
                                border-radius: 10px;
                            "
                        >
                            🔍 Encontrar um Animal
                        </a>

                    <?php else: ?>

                        <a
                            href="cadastrar_animal.php"
                            class="btn-primary"
                            style="
                                text-decoration: none;
                                display: inline-block;
                                text-align: center;
                                background-color: #2196F3;
                                border-radius: 10px;
                            "
                        >
                            ➕ Cadastrar Novo Animal
                        </a>

                        <a
                            href="meus_animais.php"
                            class="btn-secondary"
                            style="
                                text-decoration: none;
                                display: inline-block;
                                text-align: center;
                                border-radius: 10px;
                            "
                        >
                            Gerenciar Animais →
                        </a>

                    <?php endif; ?>

                </div>

                <?php if ($tipoUsuario === "adotante"): ?>

                    <a
                        href="questionario.php"
                        id="btn-questionario"
                        class="btn-secondary"
                        style="
                            text-decoration: none;
                            text-align: center;
                            background-color: #ff6070ce;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 10px;
                            width: fit-content;
                            font-weight: bold;
                        "
                    >
                        📝 Responder Questionário de Adoção
                    </a>

                <?php endif; ?>

            </div>

        </section>


        <!-- =========================
             CARROSSEL AUTOMÁTICO
        ========================= -->

        <section class="hero-image-box">

            <div class="carousel">
                <div class="carousel-track" id="carouselTrack">
                    <div class="carousel-slide"><img src="1.png" alt="Cachorro para adoção" /></div>
                    <div class="carousel-slide"><img src="2.png" alt="Cachorro para adoção" /></div>
                    <div class="carousel-slide"><img src="3.png" alt="Cachorro para adoção" /></div>
                    <div class="carousel-slide"><img src="4.png" alt="Cachorro para adoção" /></div>
                    <div class="carousel-slide"><img src="5.png" alt="Cachorro para adoção" /></div>
                </div>
            </div>

        </section>

    </main>


    <!-- =========================================================
         INFORMAÇÕES ADICIONAIS (EXCLUSIVAS PARA ADOTANTES LOGADOS)
    ========================================================= -->
    <?php if ($tipoUsuario === "adotante"): ?>

        <!-- SEÇÃO: DICAS RÁPIDAS PARA ADOTANTES -->
        <section class="adotante-info-section">
            <h2 class="section-title">Dicas Rápidas para Adotantes</h2>
            
            <div class="cards-grid grid-3">
                <div class="info-card">
                    <div class="card-icon">🏠</div>
                    <h3>Preparando sua Casa</h3>
                    <p>Preparando sua casa e arrumá-la para o novo pet. Crie um ambiente seguro.</p>
                </div>

                <div class="info-card">
                    <div class="card-icon">📦</div>
                    <h3>Os Primeiros Dias</h3>
                    <p>Organize seus favoritos, os primeiros dias do pet e inicie o processo de adaptação.</p>
                </div>

                <div class="info-card">
                    <div class="card-icon">🩺</div>
                    <h3>Check-up Veterinário</h3>
                    <p>Check-up Veterinário para garantir a saúde e bem-estar. Faça as primeiras vacinas.</p>
                </div>
            </div>
        </section>

        <!-- SEÇÃO: O CAMINHO PARA SUA NOVA FAMÍLIA -->
        <section class="adotante-info-section">
            <h2 class="section-title">O Caminho para sua Nova Família</h2>
            
            <div class="cards-grid grid-5">
                <div class="info-card">
                    <div class="card-icon">🔍</div>
                    <h3>1. Pesquise e Filtre</h3>
                    <p>Use nossos filtros detalhados para encontrar o animal ideal.</p>
                </div>

                <div class="info-card">
                    <div class="card-icon">💖</div>
                    <h3>2. Favorite e Candidate-se</h3>
                    <p>Salve seus favoritos e inicie o processo de interesse.</p>
                </div>

                <div class="info-card">
                    <div class="card-icon">📅</div>
                    <h3>3. Agende uma Visita</h3>
                    <p>Marque um horário para conhecer o animal pessoalmente no abrigo.</p>
                </div>

                <div class="info-card">
                    <div class="card-icon">📝</div>
                    <h3>4. Entrevista e Termos</h3>
                    <p>Converse com a equipe e assine o contrato de adoção.</p>
                </div>

                <div class="info-card">
                    <div class="card-icon">🏡</div>
                    <h3>5. Leve para Casa</h3>
                    <p>Bem-vindo à sua nova família!</p>
                </div>
            </div>
        </section>

    <?php endif; ?>


    <script>
        /* =================================
           SINCRONIZAÇÃO COM LOCALSTORAGE
        ================================= */
        const tipoUsuarioSessao = <?php echo json_encode($tipoUsuario); ?>;
        const usuarioNomeSessao = <?php echo json_encode($usuario_nome); ?>;

        if (tipoUsuarioSessao) {
            localStorage.setItem("tipoUsuario", tipoUsuarioSessao);
            localStorage.setItem("usuarioLogadoNome", usuarioNomeSessao);
        } else {
            localStorage.removeItem("tipoUsuario");
            localStorage.removeItem("usuarioLogadoEmail");
            localStorage.removeItem("usuarioLogadoNome");
        }

        /* =================================
           QUESTIONÁRIO
        ================================= */
        const btnQuest = document.getElementById("btn-questionario");

        if (btnQuest) {
            const jaRespondeu = localStorage.getItem("questionario_respondido_sinc");
            if (jaRespondeu === "sim") {
                btnQuest.style.display = "none";
            }
        }

        /* =================================
           CARROSSEL AUTOMÁTICO
        ================================= */
        let slideAtual = 0;
        const track = document.getElementById("carouselTrack");
        const slides = document.querySelectorAll(".carousel-slide");
        const totalSlides = slides.length;

        function atualizarCarousel() {
            if (!track || totalSlides === 0) return;
            track.style.transform = `translateX(-${slideAtual * 100}%)`;
        }

        function proximoCachorro() {
            slideAtual++;
            if (slideAtual >= totalSlides) {
                slideAtual = 0;
            }
            atualizarCarousel();
        }

        if (totalSlides > 1) {
            setInterval(proximoCachorro, 3000);
        }
    </script>

</body>

</html>