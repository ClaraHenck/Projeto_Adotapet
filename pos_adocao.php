<?php
session_start();

$logado = isset($_SESSION["usuario_id"]);
$usuario_nome = $_SESSION["usuario_nome"] ?? "";
$pode_cadastrar = $_SESSION["pode_cadastrar"] ?? 0;

$tipoUsuario = "";

if ($logado) {
    $tipoUsuario = ($pode_cadastrar == 1) ? "ong" : "adotante";
}

/*
Página exclusiva para adotantes.
*/
if (!$logado || $tipoUsuario !== "adotante") {
    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdotaPet - Pós-Adoção</title>

    <link rel="stylesheet" href="navbar.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f8fafc;
            color: #2d3748;
        }

        /* =========================
           CONTAINER
        ========================= */

        .pagina {
            max-width: 1100px;
            margin: 0 auto;
            padding: 45px 25px 70px;
        }

        /* =========================
           HERO
        ========================= */

        .hero {
            text-align: center;
            margin-bottom: 35px;
        }

        .hero-icon {
            width: 85px;
            height: 85px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: #fff1f3;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 43px;
            box-shadow: 0 8px 25px rgba(255,96,112,0.12);
        }

        .hero h1 {
            margin: 0 0 10px;
            font-size: 50px;
            color: #2d3748;
        }

        .hero p {
            max-width: 700px;
            margin: auto;
            color: #718096;
            line-height: 1.7;
            font-size: 16px;
        }

        /* =========================
           SELETOR CÃO / GATO
        ========================= */

        .seletor-animal {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin: 30px 0;
        }

        .animal-btn {
            border: none;
            padding: 13px 28px;
            border-radius: 30px;
            background: white;
            color: #4a5568;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            border: 2px solid #edf0f4;
            transition: 0.25s;
        }

        .animal-btn:hover {
            transform: translateY(-2px);
            border-color: #ffb4bd;
        }

        .animal-btn.active {
            background: #ff6070;
            color: white;
            border-color: #ff6070;
            box-shadow: 0 6px 18px rgba(255,96,112,0.25);
        }

        /* =========================
           PAINEL
        ========================= */

        .painel-animal {
            display: none;
        }

        .painel-animal.active {
            display: block;
            animation: aparecer 0.3s ease;
        }

        @keyframes aparecer {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .titulo-secao {
            text-align: center;
            margin: 35px 0 20px;
        }

        .titulo-secao h2 {
            margin: 0 0 7px;
            font-size: 27px;
        }

        .titulo-secao p {
            margin: 0;
            color: #718096;
        }

        /* =========================
           CARDS DE REAÇÃO
        ========================= */

        .reacoes-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 17px;
        }

        .reacao-card {
            background: white;
            border-radius: 17px;
            border: 1px solid #edf0f4;
            overflow: hidden;
            box-shadow: 0 5px 18px rgba(0,0,0,0.04);
        }

        .reacao-botao {
            width: 100%;
            border: none;
            background: white;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-align: left;
            cursor: pointer;
            color: #2d3748;
        }

        .reacao-botao:hover {
            background: #fff8f9;
        }

        .reacao-emoji {
            width: 48px;
            height: 48px;
            min-width: 48px;
            border-radius: 13px;
            background: #fff1f3;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
        }

        .reacao-titulo {
            flex: 1;
        }

        .reacao-titulo strong {
            display: block;
            font-size: 17px;
            margin-bottom: 4px;
        }

        .reacao-titulo span {
            color: #a0aec0;
            font-size: 13px;
        }

        .seta {
            font-size: 18px;
            color: #ff6070;
            transition: 0.25s;
        }

        .reacao-card.aberto .seta {
            transform: rotate(180deg);
        }

        .reacao-conteudo {
            display: none;
            padding: 0 20px 20px 83px;
            color: #718096;
            line-height: 1.65;
            font-size: 14px;
        }

        .reacao-card.aberto .reacao-conteudo {
            display: block;
        }

        /* =========================
           CHECKLIST
        ========================= */

        .checklist {
            margin-top: 38px;
            background: white;
            border-radius: 20px;
            padding: 28px;
            border: 1px solid #edf0f4;
            box-shadow: 0 5px 18px rgba(0,0,0,0.04);
        }

        .checklist h2 {
            margin: 0 0 6px;
            font-size: 25px;
        }

        .checklist-intro {
            color: #718096;
            margin-bottom: 18px;
        }

        .check-item {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            padding: 15px 0;
            border-bottom: 1px solid #edf0f4;
            cursor: pointer;
        }

        .check-item:last-child {
            border-bottom: none;
        }

        .check-box {
            width: 24px;
            height: 24px;
            min-width: 24px;
            border-radius: 7px;
            border: 2px solid #ff9ca8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: transparent;
            font-weight: bold;
            transition: 0.2s;
        }

        .check-item.feito .check-box {
            background: #ff6070;
            border-color: #ff6070;
            color: white;
        }

        .check-item.feito .check-text {
            text-decoration: line-through;
            color: #a0aec0;
        }

        .check-text {
            color: #4a5568;
            line-height: 1.5;
        }

        .progresso {
            margin-top: 20px;
            height: 9px;
            background: #edf0f4;
            border-radius: 20px;
            overflow: hidden;
        }

        .progresso-barra {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #ff6070, #ff8794);
            border-radius: 20px;
            transition: 0.3s;
        }

        .progresso-texto {
            text-align: right;
            margin-top: 8px;
            color: #718096;
            font-size: 13px;
        }

        /* =========================
           EMERGÊNCIA
        ========================= */

        .emergencia {
            margin-top: 35px;
            background: #fff4f4;
            border: 1px solid #ffcaca;
            border-radius: 20px;
            overflow: hidden;
        }

        .emergencia-header {
            padding: 22px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .emergencia-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #ffe0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 27px;
        }

        .emergencia-header h2 {
            margin: 0 0 4px;
            color: #a8323d;
        }

        .emergencia-header p {
            margin: 0;
            color: #80545a;
            font-size: 14px;
        }

        .emergencia-conteudo {
            padding: 0 25px 25px;
        }

        .emergencia-conteudo ul {
            margin: 0;
            padding-left: 22px;
            color: #6b4a4d;
            line-height: 1.8;
        }

        .emergencia-conteudo li {
            margin-bottom: 5px;
        }

        .botao-emergencia {
            margin-top: 17px;
            border: none;
            background: #a8323d;
            color: white;
            padding: 11px 18px;
            border-radius: 9px;
            cursor: pointer;
            font-weight: bold;
        }

        .aviso-emergencia {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: white;
            border-radius: 10px;
            color: #6b4a4d;
            line-height: 1.5;
        }

        .aviso-emergencia.visivel {
            display: block;
        }

        /* =========================
           RODAPÉ
        ========================= */

        .rodape {
            text-align: center;
            margin-top: 40px;
            color: #a0aec0;
            font-size: 14px;
        }

        /* =========================
           RESPONSIVO
        ========================= */

        @media (max-width: 800px) {
            .reacoes-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 550px) {
            .pagina {
                padding: 30px 15px 50px;
            }

            .hero h1 {
                font-size: 28px;
            }

            .seletor-animal {
                flex-direction: column;
            }

            .animal-btn {
                width: 100%;
            }

            .reacao-conteudo {
                padding-left: 20px;
            }

            .checklist {
                padding: 22px;
            }
        }

    </style>
</head>
<body>

<!-- =========================
     HEADER (PADRONIZADO)
========================= -->

<header class="navbar">

    <div class="left-brand-box">
        <a href="index.php" class="logo" style="text-decoration: none;">🐾 AdotaPet</a>
    </div>

    <nav class="menu" id="menu-navegacao">
        <a href="index.php">Início</a>

        <?php if ($logado && $tipoUsuario === "ong"): ?>
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

        <!-- PÓS-ADOÇÃO (SOMENTE ADOTANTES LOGADOS) -->
        <?php if ($logado && $tipoUsuario === "adotante"): ?>
            <a href="pos_adocao.php" id="link-pos-adocao" class="active" title="Guia de Pós-Adoção">
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

<!-- =========================
     CONTEÚDO
========================= -->

<main class="pagina">

    <section class="hero">

        <div class="hero-icon">
            🐾
        </div>

        <h1>
            Guia de Pós-Adoção
        </h1>

        <p>
            A chegada a um novo lar pode causar diferentes
            reações em cada animal. Veja o que pode acontecer
            e como ajudar seu novo companheiro nos primeiros dias.
        </p>

    </section>

    <!-- =========================
         SELETOR
    ========================= -->

    <div class="seletor-animal">

        <button
            type="button"
            class="animal-btn active"
            onclick="mostrarAnimal('cao', this)"
        >
            🐶 Cachorros
        </button>

        <button
            type="button"
            class="animal-btn"
            onclick="mostrarAnimal('gato', this)"
        >
            🐱 Gatos
        </button>

    </div>

    <!-- =====================================================
         CACHORROS
    ====================================================== -->

    <section id="painel-cao" class="painel-animal active">

        <div class="titulo-secao">

            <h2>
                🐶 Como o cachorro pode reagir
            </h2>

            <p>
                Algumas mudanças de comportamento podem acontecer
                durante a adaptação ao novo lar.
            </p>

        </div>

        <div class="reacoes-grid">

            <article class="reacao-card">

                <button class="reacao-botao" onclick="abrirReacao(this)">

                    <div class="reacao-emoji">
                        😨
                    </div>

                    <div class="reacao-titulo">
                        <strong>Medo ou insegurança</strong>
                        <span>Pode acontecer nos primeiros dias</span>
                    </div>

                    <span class="seta">▼</span>

                </button>

                <div class="reacao-conteudo">

                    O cachorro pode ficar assustado, se esconder,
                    evitar contato ou permanecer mais quieto.
                    Dê espaço e deixe que ele se aproxime
                    voluntariamente.

                </div>

            </article>

            <article class="reacao-card">

                <button class="reacao-botao" onclick="abrirReacao(this)">

                    <div class="reacao-emoji">
                        🥺
                    </div>

                    <div class="reacao-titulo">
                        <strong>Carência</strong>
                        <span>Busca constante por atenção</span>
                    </div>

                    <span class="seta">▼</span>

                </button>

                <div class="reacao-conteudo">

                    Alguns cães podem procurar o tutor o tempo
                    todo. Ofereça carinho, mas também ensine
                    gradualmente que ficar sozinho por pequenos
                    períodos é seguro.

                </div>

            </article>

            <article class="reacao-card">

                <button class="reacao-botao" onclick="abrirReacao(this)">

                    <div class="reacao-emoji">
                        🗣️
                    </div>

                    <div class="reacao-titulo">
                        <strong>Latidos ou choros</strong>
                        <span>Comunicação durante a adaptação</span>
                    </div>

                    <span class="seta">▼</span>

                </button>

                <div class="reacao-conteudo">

                    O cachorro pode latir, chorar ou vocalizar
                    mais que o habitual. Observe quando isso
                    acontece para tentar entender se existe medo,
                    solidão, ansiedade ou outro motivo.

                </div>

            </article>

            <article class="reacao-card">

                <button class="reacao-botao" onclick="abrirReacao(this)">

                    <div class="reacao-emoji">
                        ⚡
                    </div>

                    <div class="reacao-titulo">
                        <strong>Agitação</strong>
                        <span>Exploração e excesso de energia</span>
                    </div>

                    <span class="seta">▼</span>

                </button>

                <div class="reacao-conteudo">

                    Alguns cães podem ficar muito agitados ao
                    conhecer o novo ambiente. Uma rotina com
                    passeios, brincadeiras adequadas e períodos
                    de descanso pode ajudar.

                </div>

            </article>

            <article class="reacao-card">

                <button class="reacao-botao" onclick="abrirReacao(this)">

                    <div class="reacao-emoji">
                        🚽
                    </div>

                    <div class="reacao-titulo">
                        <strong>Acidentes</strong>
                        <span>Pode esquecer hábitos temporariamente</span>
                    </div>

                    <span class="seta">▼</span>

                </button>

                <div class="reacao-conteudo">

                    Mesmo um cão acostumado a fazer suas
                    necessidades no lugar correto pode ter
                    acidentes durante a adaptação. Evite
                    punições e reforce o comportamento correto.

                </div>

            </article>

            <article class="reacao-card">

                <button class="reacao-botao" onclick="abrirReacao(this)">

                    <div class="reacao-emoji">
                        😡
                    </div>

                    <div class="reacao-titulo">
                        <strong>Rosnado ou desconforto</strong>
                        <span>Um sinal de comunicação</span>
                    </div>

                    <span class="seta">▼</span>

                </button>

                <div class="reacao-conteudo">

                    Um cachorro pode rosnar quando está com medo,
                    desconfortável ou querendo distância. Não puna
                    o rosnado. Afaste-se e procure orientação
                    profissional se houver risco de mordida.

                </div>

            </article>

        </div>

        <div class="checklist">

            <h2>
                📋 Checklist dos primeiros dias
            </h2>

            <p class="checklist-intro">
                Marque o que você já preparou para receber seu cachorro.
            </p>

            <div class="lista-checks">

                <div class="check-item" onclick="marcarCheck(this)">

                    <div class="check-box">✓</div>

                    <div class="check-text">
                        Preparei um local tranquilo e seguro para ele.
                    </div>

                </div>

                <div class="check-item" onclick="marcarCheck(this)">

                    <div class="check-box">✓</div>

                    <div class="check-text">
                        Deixei água fresca disponível.
                    </div>

                </div>

                <div class="check-item" onclick="marcarCheck(this)">

                    <div class="check-box">✓</div>

                    <div class="check-text">
                        Separei alimentação adequada.
                    </div>

                </div>

                <div class="check-item" onclick="marcarCheck(this)">

                    <div class="check-box">✓</div>

                    <div class="check-text">
                        Preparei um espaço confortável para descanso.
                    </div>

                </div>

                <div class="check-item" onclick="marcarCheck(this)">

                    <div class="check-box">✓</div>

                    <div class="check-text">
                        Evitei apresentar muitas pessoas de uma vez.
                    </div>

                </div>

                <div class="check-item" onclick="marcarCheck(this)">

                    <div class="check-box">✓</div>

                    <div class="check-text">
                        Vou respeitar o tempo de adaptação dele.
                    </div>

                </div>

            </div>

            <div class="progresso">
                <div class="progresso-barra"></div>
            </div>

            <div class="progresso-texto">
                <span class="progresso-numero">0</span>/6 concluídos
            </div>

        </div>

    </section>

    <!-- =====================================================
         GATOS
    ====================================================== -->

    <section id="painel-gato" class="painel-animal">

        <div class="titulo-secao">

            <h2>
                🐱 Como o gato pode reagir
            </h2>

            <p>
                Gatos costumam precisar de tempo para reconhecer
                o novo ambiente como seguro.
            </p>

        </div>

        <div class="reacoes-grid">

            <article class="reacao-card">

                <button class="reacao-botao" onclick="abrirReacao(this)">

                    <div class="reacao-emoji">
                        🙈
                    </div>

                    <div class="reacao-titulo">
                        <strong>Se esconder</strong>
                        <span>Uma reação comum à mudança</span>
                    </div>

                    <span class="seta">▼</span>

                </button>

                <div class="reacao-conteudo">

                    O gato pode permanecer escondido durante
                    algum tempo. Não tente retirá-lo à força.
                    Deixe água, alimento e uma caixa de areia
                    acessíveis.

                </div>

            </article>

            <article class="reacao-card">

                <button class="reacao-botao" onclick="abrirReacao(this)">

                    <div class="reacao-emoji">
                        😾
                    </div>

                    <div class="reacao-titulo">
                        <strong>Medo ou irritação</strong>
                        <span>Pode evitar contato inicialmente</span>
                    </div>

                    <span class="seta">▼</span>

                </button>

                <div class="reacao-conteudo">

                    O gato pode ficar assustado, recuar ou demonstrar
                    desconforto ao ser tocado. Respeite os sinais
                    corporais e não force o contato.

                </div>

            </article>

            <article class="reacao-card">

                <button class="reacao-botao" onclick="abrirReacao(this)">

                    <div class="reacao-emoji">
                        🐾
                    </div>

                    <div class="reacao-titulo">
                        <strong>Exploração gradual</strong>
                        <span>Conhecendo cada espaço</span>
                    </div>

                    <span class="seta">▼</span>

                </button>

                <div class="reacao-conteudo">

                    Alguns gatos começam a explorar aos poucos.
                    Permita que ele conheça o ambiente no próprio
                    ritmo e mantenha portas e janelas seguras.

                </div>

            </article>

            <article class="reacao-card">

                <button class="reacao-botao" onclick="abrirReacao(this)">

                    <div class="reacao-emoji">
                        🍽️
                    </div>

                    <div class="reacao-titulo">
                        <strong>Alteração no apetite</strong>
                        <span>O estresse pode influenciar</span>
                    </div>

                    <span class="seta">▼</span>

                </button>

                <div class="reacao-conteudo">

                    Alguns gatos podem comer menos durante a adaptação.
                    Observe a alimentação e procure orientação
                    veterinária diante de uma recusa persistente.

                </div>

            </article>

            <article class="reacao-card">

                <button class="reacao-botao" onclick="abrirReacao(this)">

                    <div class="reacao-emoji">
                        🚽
                    </div>

                    <div class="reacao-titulo">
                        <strong>Mudanças na caixa de areia</strong>
                        <span>Observe os hábitos</span>
                    </div>

                    <span class="seta">▼</span>

                </button>

                <div class="reacao-conteudo">

                    O estresse pode alterar temporariamente os
                    hábitos do gato. Mantenha a caixa de areia
                    limpa, acessível e em um local tranquilo.

                </div>

            </article>

            <article class="reacao-card">

                <button class="reacao-botao" onclick="abrirReacao(this)">

                    <div class="reacao-emoji">
                        ❤️
                    </div>

                    <div class="reacao-titulo">
                        <strong>Busca por carinho</strong>
                        <span>Alguns gatos se aproximam rapidamente</span>
                    </div>

                    <span class="seta">▼</span>

                </button>

                <div class="reacao-conteudo">

                    Alguns gatos podem procurar carinho e atenção
                    desde o primeiro momento. Mesmo assim, respeite
                    quando ele demonstrar que quer ficar sozinho.

                </div>

            </article>

        </div>

        <div class="checklist">

            <h2>
                📋 Checklist dos primeiros dias
            </h2>

            <p class="checklist-intro">
                Marque o que você já preparou para receber seu gato.
            </p>

            <div class="lista-checks">

                <div class="check-item" onclick="marcarCheck(this)">

                    <div class="check-box">✓</div>

                    <div class="check-text">
                        Preparei um espaço tranquilo para adaptação.
                    </div>

                </div>

                <div class="check-item" onclick="marcarCheck(this)">

                    <div class="check-box">✓</div>

                    <div class="check-text">
                        Separei água e alimentação.
                    </div>

                </div>

                <div class="check-item" onclick="marcarCheck(this)">

                    <div class="check-box">✓</div>

                    <div class="check-text">
                        Preparei uma caixa de areia limpa e acessível.
                    </div>

                </div>

                <div class="check-item" onclick="marcarCheck(this)">

                    <div class="check-box">✓</div>

                    <div class="check-text">
                        Separei um local onde ele possa se esconder com segurança.
                    </div>

                </div>

                <div class="check-item" onclick="marcarCheck(this)">

                    <div class="check-box">✓</div>

                    <div class="check-text">
                        Mantive portas e janelas protegidas.
                    </div>

                </div>

                <div class="check-item" onclick="marcarCheck(this)">

                    <div class="check-box">✓</div>

                    <div class="check-text">
                        Vou deixar o gato se aproximar no próprio ritmo.
                    </div>

                </div>

            </div>

            <div class="progresso">
                <div class="progresso-barra"></div>
            </div>

            <div class="progresso-texto">
                <span class="progresso-numero">0</span>/6 concluídos
            </div>

        </div>

    </section>

    <!-- =========================
         EMERGÊNCIA
    ========================= -->

    <section class="emergencia">

        <div class="emergencia-header">

            <div class="emergencia-icon">
                🚨
            </div>

            <div>

                <h2>
                    Sinais de emergência
                </h2>

                <p>
                    Alguns sinais precisam de atendimento veterinário.
                </p>

            </div>

        </div>

        <div class="emergencia-conteudo">

            <ul>

                <li>
                    Dificuldade para respirar.
                </li>

                <li>
                    Sangramento intenso ou ferimento grave.
                </li>

                <li>
                    Desmaio, convulsão ou perda de consciência.
                </li>

                <li>
                    Dor intensa ou comportamento de sofrimento.
                </li>

                <li>
                    Vômitos ou diarreia intensos ou persistentes.
                </li>

                <li>
                    Recusa persistente de água ou alimento.
                </li>

                <li>
                    Fraqueza ou apatia intensa.
                </li>

                <li>
                    Qualquer situação em que o animal pareça estar em risco imediato.
                </li>

            </ul>

            <button
                type="button"
                class="botao-emergencia"
                onclick="mostrarAvisoEmergencia()"
            >
                ⚠️ O que fazer?
            </button>

            <div
                id="aviso-emergencia"
                class="aviso-emergencia"
            >

                <strong>
                    Procure atendimento veterinário.
                </strong>

                Em uma situação de emergência, mantenha o animal
                em segurança, evite administrar medicamentos por
                conta própria e procure um serviço veterinário.
                Se possível, entre em contato também com a ONG
                responsável pela adoção.

            </div>

        </div>

    </section>

    <div class="rodape">

        🐾 Cada animal possui seu próprio tempo de adaptação.
        <br>
        Paciência, segurança e respeito ajudam na construção
        de uma nova rotina.

    </div>

</main>

<script>

    /* =========================
       ALTERNAR CÃO / GATO
    ========================= */

    function mostrarAnimal(tipo, botao) {

        document
            .querySelectorAll(".painel-animal")
            .forEach(function(painel) {

                painel.classList.remove("active");

            });

        document
            .querySelectorAll(".animal-btn")
            .forEach(function(btn) {

                btn.classList.remove("active");

            });

        document
            .getElementById("painel-" + tipo)
            .classList.add("active");

        botao.classList.add("active");

    }

    /* =========================
       ABRIR REAÇÕES
    ========================= */

    function abrirReacao(botao) {

        const card = botao.closest(".reacao-card");

        card.classList.toggle("aberto");

    }

    /* =========================
       CHECKLIST
    ========================= */

    function marcarCheck(item) {

        item.classList.toggle("feito");

        atualizarProgresso();

    }

    function atualizarProgresso() {

        const painelAtivo =
            document.querySelector(".painel-animal.active");

        if (!painelAtivo) return;

        const checks =
            painelAtivo.querySelectorAll(".check-item");

        const feitos =
            painelAtivo.querySelectorAll(".check-item.feito");

        const quantidade =
            feitos.length;

        const total =
            checks.length;

        const porcentagem =
            total > 0
                ? (quantidade / total) * 100
                : 0;

        const barra =
            painelAtivo.querySelector(".progresso-barra");

        const numero =
            painelAtivo.querySelector(".progresso-numero");

        if (barra) {
            barra.style.width = porcentagem + "%";
        }

        if (numero) {
            numero.textContent = quantidade;
        }

    }

    /* =========================
       AVISO DE EMERGÊNCIA
    ========================= */

    function mostrarAvisoEmergencia() {

        const aviso =
            document.getElementById("aviso-emergencia");

        aviso.classList.toggle("visivel");

    }

</script>

</body>
</html>