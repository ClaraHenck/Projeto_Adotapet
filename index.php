<?php
session_start();

$logado = isset($_SESSION["usuario_id"]);
$usuario_nome = $_SESSION["usuario_nome"] ?? "";
$pode_cadastrar = $_SESSION["pode_cadastrar"] ?? 0;

$tipoUsuario = "";

if ($logado) {
$tipoUsuario = ($pode_cadastrar == 1) ? "ong" : "adotante";
}

?>

<!DOCTYPE html> <html lang="pt-BR"> <head>
<meta charset="UTF-8" />

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
/>

<title>
    AdotaPet - Plataforma de Adoção
</title>

<link
    rel="stylesheet"
    href="navbar.css"
/>

<link
    rel="stylesheet"
    href="index.css"
/>

<style>

    /* =========================
       CARROSSEL
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

        box-shadow:
            0 15px 40px
            rgba(0, 0, 0, 0.15);

        background-color: #f5f5f5;
    }

    .carousel-track {

        display: flex;

        width: 100%;

        height: 100%;

        transition:
            transform 0.8s ease-in-out;
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
       LINK PÓS-ADOÇÃO
    ========================= */

    .link-pos-adocao {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        color: #e94d61 !important;

        font-weight: 600 !important;

        text-decoration: none;

        transition: all 0.2s ease;
    }

    .link-pos-adocao:hover {

        color: #c9364b !important;

        transform: translateY(-1px);
    }

    /* =========================
       VISUAL DA ONG
    ========================= */

    .ong-page .badge {

        background-color: #e3f2fd;

        color: #00acc1
    }

    .ong-page .highlight {

        color:#00acc1 ;
    }

    .ong-page .carousel {

        box-shadow:
            0 15px 40px
            rgba(33, 150, 243, 0.18);
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

</head> <body>
<!-- =========================
     HEADER
========================= -->

<header class="navbar">

    <div class="left-brand-box">

        <a
            href="index.php"
            class="logo"
            style="text-decoration: none;"
        >
            🐾 AdotaPet
        </a>

    </div>

    <nav
        class="menu"
        id="menu-navegacao"
    >

        <!-- INÍCIO -->

        <a
            href="index.php"
            class="active"
        >
            Início
        </a>

        <!-- MEUS ANIMAIS - SOMENTE ONG -->

        <?php if ($logado && $tipoUsuario === "ong"): ?>

            <a
                href="meus_animais.php"
                id="link-meus-animais"
            >
                Meus Animais
            </a>

        <?php endif; ?>

        <!-- ADOTAR -->

        <?php if (!$logado || $tipoUsuario === "adotante"): ?>

            <a
                href="adotar.php"
                id="link-adotar"
            >
                Adotar
            </a>

        <?php endif; ?>

        <!-- PÓS-ADOÇÃO - SOMENTE ADOTANTE -->

        <?php if ($logado && $tipoUsuario === "adotante"): ?>

            <a
                href="pos_adocao.php"
                id="link-pos-adocao"
                class="link-pos-adocao"
                title="Guia de Pós-Adoção"
            >
                🐾 Pós-Adoção
            </a>

        <?php endif; ?>

        <!-- MAPA -->

        <?php if ($logado): ?>

            <?php if ($tipoUsuario !== 'ong'): ?>

                <a
                    href="mapa.php"
                    id="link-mapa"
                >
                    Mapa
                </a>

            <?php endif; ?>

            <!-- CANDIDATURAS -->

            <a
                href="<?= ($tipoUsuario === 'ong')
                    ? 'candidaturas_recebidas.php'
                    : 'candidaturas.php'; ?>"
                id="link-candidaturas"
            >

                <?= ($tipoUsuario === 'ong')
                    ? 'Candidaturas Recebidas'
                    : 'Candidaturas'; ?>

            </a>

        <?php endif; ?>

        <!-- =========================
             ÁREA DO USUÁRIO
        ========================= -->

        <div
            id="area-usuario-nav"
            style="
                display: flex;
                align-items: center;
                gap: 24px;
            "
        >

            <?php if (!$logado): ?>

                <!-- USUÁRIO NÃO LOGADO -->

                <a
                    href="../projeto_adotapet/login/login.php"
                    class="btn-nav-login"
                >
                    Entrar
                </a>

                <a
                    href="../projeto_adotapet/login/cadastrar.php"
                    class="btn-nav-cadastro"
                >
                    Cadastrar-se
                </a>

            <?php else: ?>

                <!-- USUÁRIO LOGADO -->

                <?php

                $linkHref =
                    ($tipoUsuario === "ong")
                        ? "minha_ong.php"
                        : "meu_perfil.php";

                $textoPerfil = "Meu Perfil";

                ?>

                <a
                    href="<?= $linkHref ?>"
                    class="perfil-link-container"
                    style="text-decoration: none;"
                >

                    <span
                        style="
                            font-weight: 500;
                            color: #718096;
                        "
                    >
                        <?= $textoPerfil ?>
                    </span>

                </a>

                <a
                    href="logout.php"
                    style="
                        color: #ff4d4d;
                        font-weight: 500;
                        text-decoration: none;
                    "
                >
                    Sair
                </a>

            <?php endif; ?>

        </div>

    </nav>

</header>

<!-- =========================
     CONTEÚDO PRINCIPAL
========================= -->

<main
    class="hero-container <?= ($tipoUsuario === 'ong') ? 'ong-page' : ''; ?>"
>

    <!-- =========================
         TEXTO PRINCIPAL
    ========================= -->

    <section class="hero-text">

        <?php if ($tipoUsuario === "ong"): ?>

            <!-- =================================
                 PÁGINA DA ONG
            ================================== -->

            <span class="badge">

                🏠 Área da ONG

            </span>

            <h1>

                Cadastre um

                <br />

                <span class="highlight">

                    novo animal

                </span>

                <br />

                para adoção

            </h1>

            <p id="hero-descricao">

                Cadastre os animais da sua ONG,
                acompanhe seus perfis e encontre
                tutores responsáveis para cada um deles.

            </p>

            <!-- =========================
                 BOTÕES DA ONG
            ========================= -->

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

                <div
                    style="
                        display: flex;
                        gap: 10px;
                        flex-wrap: wrap;
                    "
                >

                    <a
                        href="cadastrar_animal.php"
                        class="btn-primary"
                        style="
                            text-decoration: none;
                            display: inline-block;
                            text-align: center;
                            background-color: #00acc1;
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

                </div>

            </div>

        <?php else: ?>

            <!-- =================================
                 PÁGINA DO ADOTANTE / VISITANTE
            ================================== -->

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

                A AdotaPet conecta você ao animal perfeito
                com um algoritmo. Um lar amoroso está a
                poucos clicks de distância.

            </p>

            <!-- =========================
                 BOTÕES DO ADOTANTE
            ========================= -->

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

                <div
                    style="
                        display: flex;
                        gap: 10px;
                        flex-wrap: wrap;
                    "
                >

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

                </div>

                <!-- QUESTIONÁRIO -->

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

        <?php endif; ?>

    </section>

    <!-- =========================
         CARROSSEL
    ========================= -->

    <section class="hero-image-box">

        <div class="carousel">

            <div
                class="carousel-track"
                id="carouselTrack"
            >

                <!-- CACHORRO 1 -->

                <div class="carousel-slide">

                    <img
                        src="1.png"
                        alt="Cachorro para adoção"
                    />

                </div>

                <!-- CACHORRO 2 -->

                <div class="carousel-slide">

                    <img
                        src="2.png"
                        alt="Cachorro para adoção"
                    />

                </div>

                <!-- CACHORRO 3 -->

                <div class="carousel-slide">

                    <img
                        src="3.png"
                        alt="Cachorro para adoção"
                    />

                </div>

                <!-- CACHORRO 4 -->

                <div class="carousel-slide">

                    <img
                        src="4.png"
                        alt="Cachorro para adoção"
                    />

                </div>

                <!-- CACHORRO 5 -->

                <div class="carousel-slide">

                    <img
                        src="5.png"
                        alt="Cachorro para adoção"
                    />

                </div>

            </div>

        </div>

    </section>

</main>

<!-- =========================
     JAVASCRIPT
========================= -->

<script>

    /* =================================
       SINCRONIZAÇÃO COM LOCALSTORAGE
    ================================= */

    const tipoUsuarioSessao =
        <?php echo json_encode($tipoUsuario); ?>;

    const usuarioNomeSessao =
        <?php echo json_encode($usuario_nome); ?>;

    if (tipoUsuarioSessao) {

        localStorage.setItem(
            "tipoUsuario",
            tipoUsuarioSessao
        );

        localStorage.setItem(
            "usuarioLogadoNome",
            usuarioNomeSessao
        );

    } else {

        localStorage.removeItem(
            "tipoUsuario"
        );

        localStorage.removeItem(
            "usuarioLogadoEmail"
        );

        localStorage.removeItem(
            "usuarioLogadoNome"
        );

    }

    /* =================================
       QUESTIONÁRIO
    ================================= */

    const btnQuest =
        document.getElementById(
            "btn-questionario"
        );

    if (btnQuest) {

        const jaRespondeu =
            localStorage.getItem(
                "questionario_respondido_sinc"
            );

        if (jaRespondeu === "sim") {

            btnQuest.style.display =
                "none";

        }

    }

    /* =================================
       CARROSSEL AUTOMÁTICO
    ================================= */

    let slideAtual = 0;

    const track =
        document.getElementById(
            "carouselTrack"
        );

    const slides =
        document.querySelectorAll(
            ".carousel-slide"
        );

    const totalSlides =
        slides.length;

    function atualizarCarousel() {

        if (
            !track ||
            totalSlides === 0
        ) {

            return;

        }

        track.style.transform =
            `translateX(-${slideAtual * 100}%)`;

    }

    function proximoCachorro() {

        slideAtual++;

        if (
            slideAtual >= totalSlides
        ) {

            slideAtual = 0;

        }

        atualizarCarousel();

    }

    if (totalSlides > 1) {

        setInterval(
            proximoCachorro,
            3000
        );

    }

</script>
</body> </html>