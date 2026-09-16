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

            <div class="logo">
                🐾 AdotaPet
            </div>

        </div>


        <nav class="menu" id="menu-navegacao">


            <a href="index.php" class="active">
                Início
            </a>


            <?php if (!$logado || $tipoUsuario === "adotante"): ?>

                <a href="adotar.php" id="link-adotar">
                    Adotar
                </a>

            <?php endif; ?>


            <?php if ($logado): ?>


                <?php if ($tipoUsuario !== 'ong'): ?>

                    <a href="mapa.php" id="link-mapa">
                        Mapa
                    </a>

                <?php endif; ?>


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


            <div
                id="area-usuario-nav"
                style="
                    display: flex;
                    align-items: center;
                    gap: 24px;
                "
            >


                <?php if (!$logado): ?>


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


                    <?php

                    $linkHref = ($tipoUsuario === "ong")
                        ? "minha_ong.php"
                        : "meu_perfil.php";

                    $textoPerfil = ($tipoUsuario === "ong")
                        ? "MINHA ONG"
                        : "MEU PERFIL";

                    ?>


                    <a
                        href="<?php echo $linkHref; ?>"
                        class="perfil-link-container"
                    >

                        <span
                            style="
                                font-weight: bold;
                                color: #1e293b;
                            "
                        >

                            <?php echo $textoPerfil; ?>

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


                    A AdotaPet conecta você ao animal perfeito usando inteligência artificial. Um lar amoroso está a poucos clicks de distância.


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


                <div
                    style="
                        display: flex;
                        gap: 10px;
                        flex-wrap: wrap;
                    "
                >


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
             SOMENTE CACHORROS
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

                btnQuest.style.display = "none";

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



        /* ================================
           ATUALIZA A IMAGEM
        ================================= */

        function atualizarCarousel() {


            if (!track || totalSlides === 0) {
                return;
            }


            track.style.transform =
                `translateX(-${slideAtual * 100}%)`;


        }



        /* ================================
           PRÓXIMO CACHORRO
        ================================= */

        function proximoCachorro() {


            slideAtual++;


            if (slideAtual >= totalSlides) {

                slideAtual = 0;

            }


            atualizarCarousel();


        }



        /* ================================
           TROCA AUTOMÁTICA
           A CADA 3 SEGUNDOS
        ================================= */

        if (totalSlides > 1) {


            setInterval(
                proximoCachorro,
                3000
            );


        }


    </script>


</body>

</html>