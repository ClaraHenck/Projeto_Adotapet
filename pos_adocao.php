<?php

session_start();

$logado = isset($_SESSION["usuario_id"]);
$usuario_nome = $_SESSION["usuario_nome"] ?? "";
$pode_cadastrar = $_SESSION["pode_cadastrar"] ?? 0;

$tipoUsuario = "";

if ($logado) {
    $tipoUsuario = ($pode_cadastrar == 1)
        ? "ong"
        : "adotante";
}

/*
 * Esta página é destinada aos adotantes.
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>AdotaPet - Pós-Adoção</title>

    <link
        rel="stylesheet"
        href="navbar.css"
    >

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

        /* =====================================
           HEADER
        ===================================== */

        .navbar {
            min-height: 75px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            background: white;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .left-brand-box {
            display: flex;
            align-items: center;
        }

        .logo {
            font-size: 25px;
            font-weight: bold;
            color: #ff6070;
            text-decoration: none;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
        }

        .menu a {
            text-decoration: none;
            color: #4a5568;
            font-weight: 500;
            transition: 0.2s;
        }

        .menu a:hover {
            color: #ff6070;
        }

        .menu a.active {
            color: #ff6070;
            font-weight: 700;
        }

        .pos-link {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 10px;
            background: #fff1f3;
            color: #e94d61 !important;
        }

        /* =====================================
           CONTAINER
        ===================================== */

        .pagina {
            max-width: 1150px;
            margin: 0 auto;
            padding: 50px 25px 70px;
        }

        /* =====================================
           HERO
        ===================================== */

        .hero-pos {
            background: linear-gradient(
                135deg,
                #fff1f3,
                #fff8f5
            );

            border-radius: 25px;
            padding: 45px;
            margin-bottom: 35px;

            display: flex;
            align-items: center;
            gap: 35px;

            border: 1px solid #ffe0e5;
        }

        .hero-icon {
            width: 100px;
            height: 100px;
            min-width: 100px;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            background: white;

            font-size: 50px;

            box-shadow:
                0 8px 25px
                rgba(255, 96, 112, 0.15);
        }

        .hero-pos h1 {
            margin: 0 0 12px;
            font-size: 36px;
            color: #2d3748;
        }

        .hero-pos p {
            margin: 0;
            color: #718096;
            font-size: 17px;
            line-height: 1.7;
        }

        /* =====================================
           AVISO
        ===================================== */

        .aviso {
            background: #fffaf0;
            border: 1px solid #f6d88b;
            color: #765b00;
            border-radius: 15px;
            padding: 20px 23px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .aviso strong {
            display: block;
            margin-bottom: 5px;
            font-size: 17px;
        }

        /* =====================================
           TITULOS DE SEÇÃO
        ===================================== */

        .titulo-secao {
            margin: 40px 0 20px;
        }

        .titulo-secao h2 {
            margin: 0 0 8px;
            font-size: 28px;
            color: #2d3748;
        }

        .titulo-secao p {
            margin: 0;
            color: #718096;
            line-height: 1.6;
        }

        /* =====================================
           GRID
        ===================================== */

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 22px;
        }

        .card {
            background: white;
            border-radius: 18px;
            padding: 27px;
            border: 1px solid #edf0f4;

            box-shadow:
                0 5px 18px
                rgba(0, 0, 0, 0.05);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 17px;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            min-width: 50px;

            border-radius: 13px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 25px;

            background: #fff1f3;
        }

        .card h2 {
            margin: 0;
            color: #2d3748;
            font-size: 21px;
        }

        .card p {
            color: #718096;
            line-height: 1.65;
            margin: 0 0 12px;
        }

        .card ul {
            margin: 10px 0 0;
            padding-left: 21px;
            color: #4a5568;
            line-height: 1.8;
        }

        .card li {
            margin-bottom: 5px;
        }

        /* =====================================
           CARDS DE ESPÉCIES
        ===================================== */

        .especie-card {
            border-top: 5px solid #ff6070;
        }

        .especie-card.gato {
            border-top-color: #805ad5;
        }

        .especie-card .icone-especie {
            font-size: 45px;
            margin-bottom: 10px;
        }

        .especie-card h2 {
            margin-bottom: 12px;
        }

        /* =====================================
           DESTAQUE
        ===================================== */

        .destaque {
            border-left: 5px solid #ff6070;
            background: #fff8f9;
        }

        .positivo {
            color: #237a45;
            font-weight: 600;
        }

        .alerta {
            color: #946c00;
            font-weight: 600;
        }

        .perigo {
            color: #a8323d;
            font-weight: 600;
        }

        /* =====================================
           REAÇÕES
        ===================================== */

        .reacoes {
            margin-top: 40px;
        }

        .reacoes h2 {
            font-size: 27px;
            margin-bottom: 20px;
            color: #2d3748;
        }

        .reacao-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .reacao {
            background: white;
            border-radius: 16px;
            padding: 23px;
            border: 1px solid #edf0f4;
        }

        .reacao .emoji {
            font-size: 34px;
            margin-bottom: 12px;
        }

        .reacao h3 {
            margin: 0 0 9px;
            font-size: 18px;
        }

        .reacao p {
            margin: 0;
            color: #718096;
            line-height: 1.6;
            font-size: 14px;
        }

        /* =====================================
           COMPARAÇÃO
        ===================================== */

        .comparacao {
            margin-top: 40px;
            background: white;
            border-radius: 20px;
            padding: 30px;
            border: 1px solid #edf0f4;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.04);
        }

        .comparacao h2 {
            margin-top: 0;
            font-size: 25px;
        }

        .tabela-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        th {
            background: #fff1f3;
            color: #4a5568;
            text-align: left;
            padding: 14px;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #edf0f4;
            color: #718096;
            line-height: 1.5;
            vertical-align: top;
        }

        /* =====================================
           CHECKLIST
        ===================================== */

        .checklist {
            margin-top: 40px;
            background: white;
            border-radius: 20px;
            padding: 30px;
            border: 1px solid #edf0f4;

            box-shadow:
                0 5px 18px
                rgba(0, 0, 0, 0.04);
        }

        .checklist h2 {
            margin-top: 0;
            font-size: 25px;
        }

        .check {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            padding: 14px 0;
            border-bottom: 1px solid #edf0f4;
            color: #4a5568;
            line-height: 1.5;
        }

        .check:last-child {
            border-bottom: none;
        }

        .check span {
            color: #38a169;
            font-size: 20px;
            font-weight: bold;
        }

        /* =====================================
           SINAIS DE ALERTA
        ===================================== */

        .ajuda {
            margin-top: 40px;
            padding: 30px;
            border-radius: 20px;
            background: #fff0f1;
            border: 1px solid #ffd4d9;
        }

        .ajuda h2 {
            margin-top: 0;
            color: #a8323d;
        }

        .ajuda h3 {
            color: #8f3039;
            margin-top: 25px;
        }

        .ajuda ul {
            line-height: 1.8;
            color: #5a4a4c;
        }

        /* =====================================
           RODAPÉ
        ===================================== */

        .rodape-info {
            text-align: center;
            margin-top: 45px;
            color: #a0aec0;
            font-size: 14px;
            line-height: 1.6;
        }

        /* =====================================
           RESPONSIVIDADE
        ===================================== */

        @media (max-width: 850px) {

            .navbar {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }

            .menu {
                justify-content: center;
                gap: 13px;
            }

            .hero-pos {
                flex-direction: column;
                text-align: center;
                padding: 32px 23px;
            }

            .hero-pos h1 {
                font-size: 29px;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .reacao-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 500px) {

            .pagina {
                padding: 30px 15px 50px;
            }

            .hero-icon {
                width: 80px;
                height: 80px;
                min-width: 80px;
                font-size: 40px;
            }

            .hero-pos h1 {
                font-size: 25px;
            }

            .card {
                padding: 21px;
            }
        }

    </style>

</head>


<body>


    <!-- =====================================
         HEADER
    ===================================== -->

    <header class="navbar">

        <div class="left-brand-box">

            <a
                href="index.php"
                class="logo"
            >
                🐾 AdotaPet
            </a>

        </div>


        <nav class="menu">

            <a href="index.php">
                Início
            </a>

            <a href="adotar.php">
                Adotar
            </a>

            <a
                href="pos_adocao.php"
                class="pos-link active"
            >
                🐾 Pós-Adoção
            </a>

            <a href="mapa.php">
                Mapa
            </a>

            <a href="candidaturas.php">
                Candidaturas
            </a>

            <a href="meu_perfil.php">
                Meu Perfil
            </a>

            <a
                href="logout.php"
                style="color: #ff4d4d;"
            >
                Sair
            </a>

        </nav>

    </header>


    <!-- =====================================
         CONTEÚDO
    ===================================== -->

    <main class="pagina">


        <!-- HERO -->

        <section class="hero-pos">

            <div class="hero-icon">
                🐶🐱
            </div>

            <div>

                <h1>
                    Guia de Pós-Adoção
                </h1>

                <p>

                    A chegada a um novo lar é uma grande mudança
                    para qualquer animal. Aqui você encontra
                    orientações sobre cães e gatos, seus possíveis
                    comportamentos durante a adaptação e cuidados
                    importantes depois da adoção.

                </p>

            </div>

        </section>


        <!-- AVISO -->

        <div class="aviso">

            <strong>
                💛 Cada animal possui seu próprio tempo de adaptação
            </strong>

            Cães e gatos podem reagir de maneiras diferentes
            quando chegam a um novo ambiente. Um animal que era
            muito carinhoso na ONG pode ficar mais reservado em
            casa, enquanto outro pode ficar agitado ou buscar
            atenção constantemente. Essas mudanças podem fazer
            parte do processo de adaptação.

        </div>


        <!-- =====================================
             CÃES E GATOS
        ===================================== -->

        <div class="titulo-secao">

            <h2>
                🐶🐱 O que esperar depois da adoção?
            </h2>

            <p>
                Embora cada animal seja único, existem alguns
                comportamentos que podem aparecer nos primeiros
                dias ou semanas.
            </p>

        </div>


        <section class="grid">


            <!-- CÃO -->

            <article class="card especie-card">

                <div class="icone-especie">
                    🐶
                </div>

                <h2>
                    Cães
                </h2>

                <p>

                    Um cachorro recém-adotado pode precisar de
                    tempo para entender a nova rotina e criar
                    confiança com seus novos tutores.

                </p>

                <ul>

                    <li>
                        Pode ficar assustado ou se esconder.
                    </li>

                    <li>
                        Pode apresentar ansiedade quando fica sozinho.
                    </li>

                    <li>
                        Pode latir ou vocalizar mais que o habitual.
                    </li>

                    <li>
                        Pode apresentar acidentes com as necessidades.
                    </li>

                    <li>
                        Pode ficar muito agitado ou procurar atenção.
                    </li>

                    <li>
                        Pode dormir mais nos primeiros dias.
                    </li>

                </ul>

            </article>


            <!-- GATO -->

            <article class="card especie-card gato">

                <div class="icone-especie">
                    🐱
                </div>

                <h2>
                    Gatos
                </h2>

                <p>

                    Gatos costumam valorizar muito o controle
                    sobre o próprio espaço. Um novo ambiente pode
                    fazer com que eles procurem esconderijos e
                    observem a casa antes de interagir.

                </p>

                <ul>

                    <li>
                        Pode se esconder por algumas horas ou dias.
                    </li>

                    <li>
                        Pode evitar contato inicialmente.
                    </li>

                    <li>
                        Pode miar mais ou menos que o habitual.
                    </li>

                    <li>
                        Pode demonstrar medo diante de pessoas novas.
                    </li>

                    <li>
                        Pode apresentar alterações temporárias no apetite.
                    </li>

                    <li>
                        Pode explorar a casa principalmente quando estiver tranquila.
                    </li>

                </ul>

            </article>


            <!-- PRIMEIROS DIAS -->

            <article class="card destaque">

                <div class="card-header">

                    <div class="card-icon">
                        🏠
                    </div>

                    <h2>
                        Primeiros dias em casa
                    </h2>

                </div>

                <p>

                    Evite apresentar toda a casa, muitas pessoas
                    e muitos estímulos de uma só vez. Um ambiente
                    tranquilo facilita a adaptação.

                </p>

                <ul>

                    <li>
                        Prepare um espaço seguro e confortável.
                    </li>

                    <li>
                        Mantenha água disponível.
                    </li>

                    <li>
                        Respeite o tempo de adaptação.
                    </li>

                    <li>
                        Evite forçar contato físico.
                    </li>

                    <li>
                        Observe como o animal reage ao novo ambiente.
                    </li>

                </ul>

            </article>


            <!-- CONFIANÇA -->

            <article class="card">

                <div class="card-header">

                    <div class="card-icon">
                        ❤️
                    </div>

                    <h2>
                        Construindo confiança
                    </h2>

                </div>

                <p>

                    A confiança é construída gradualmente.
                    Não existe um prazo igual para todos os animais.

                </p>

                <ul>

                    <li>
                        Fale com voz tranquila.
                    </li>

                    <li>
                        Não force carinho.
                    </li>

                    <li>
                        Respeite quando o animal quiser ficar sozinho.
                    </li>

                    <li>
                        Use recompensas e associações positivas.
                    </li>

                    <li>
                        Mantenha uma rotina previsível.
                    </li>

                </ul>

            </article>


            <!-- ALIMENTAÇÃO -->

            <article class="card">

                <div class="card-header">

                    <div class="card-icon">
                        🍖
                    </div>

                    <h2>
                        Alimentação
                    </h2>

                </div>

                <p>

                    A mudança de ambiente pode alterar temporariamente
                    o comportamento alimentar.

                </p>

                <ul>

                    <li>
                        Siga inicialmente a alimentação indicada pela ONG.
                    </li>

                    <li>
                        Disponibilize água limpa e fresca.
                    </li>

                    <li>
                        Evite mudanças bruscas na alimentação.
                    </li>

                    <li>
                        Observe alterações persistentes no apetite.
                    </li>

                    <li>
                        Para gatos, mantenha o alimento em um local tranquilo.
                    </li>

                </ul>

            </article>


            <!-- SONO -->

            <article class="card">

                <div class="card-header">

                    <div class="card-icon">
                        😴
                    </div>

                    <h2>
                        Sono e descanso
                    </h2>

                </div>

                <p>

                    O descanso é importante durante a adaptação.

                    Cães podem dormir bastante depois de um período
                    de estresse, enquanto gatos podem alternar períodos
                    de descanso e exploração.

                </p>

                <ul>

                    <li>
                        Prepare um local confortável.
                    </li>

                    <li>
                        Evite acordar o animal constantemente.
                    </li>

                    <li>
                        Para gatos, ofereça esconderijos seguros.
                    </li>

                    <li>
                        Para cães, mantenha um espaço próprio para descanso.
                    </li>

                </ul>

            </article>


            <!-- HIGIENE -->

            <article class="card">

                <div class="card-header">

                    <div class="card-icon">
                        🧼
                    </div>

                    <h2>
                        Higiene e necessidades
                    </h2>

                </div>

                <p>

                    Mudanças de ambiente podem provocar acidentes
                    ou alterações temporárias nos hábitos de higiene.

                </p>

                <ul>

                    <li>
                        Nunca utilize punição física.
                    </li>

                    <li>
                        Cães podem precisar reaprender o local das necessidades.
                    </li>

                    <li>
                        Gatos precisam de caixa de areia limpa e acessível.
                    </li>

                    <li>
                        Evite colocar a caixa de areia perto da comida.
                    </li>

                    <li>
                        Reforce comportamentos adequados de maneira positiva.
                    </li>

                </ul>

            </article>


            <!-- FICAR SOZINHO -->

            <article class="card">

                <div class="card-header">

                    <div class="card-icon">
                        🏡
                    </div>

                    <h2>
                        Quando ficar sozinho
                    </h2>

                </div>

                <p>

                    Alguns animais podem apresentar sinais de
                    estresse quando ficam sozinhos.

                </p>

                <ul>

                    <li>
                        Faça a adaptação gradualmente.
                    </li>

                    <li>
                        Evite ausências muito longas inicialmente.
                    </li>

                    <li>
                        Para cães, observe latidos, destruição ou agitação.
                    </li>

                    <li>
                        Para gatos, ofereça locais seguros e enriquecimento ambiental.
                    </li>

                    <li>
                        Mantenha horários previsíveis.
                    </li>

                </ul>

            </article>

        </section>


        <!-- =====================================
             COMPORTAMENTOS
        ===================================== -->

        <section class="reacoes">

            <h2>
                🐾 Como cães e gatos podem reagir
            </h2>


            <div class="reacao-grid">


                <article class="reacao">

                    <div class="emoji">
                        😨
                    </div>

                    <h3>
                        Medo ou insegurança
                    </h3>

                    <p>

                        Cães podem evitar pessoas, ficar imóveis
                        ou procurar esconderijos. Gatos frequentemente
                        procuram locais mais reservados e podem evitar
                        contato no começo.

                    </p>

                </article>


                <article class="reacao">

                    <div class="emoji">
                        🐕
                    </div>

                    <h3>
                        Agitação
                    </h3>

                    <p>

                        Cães podem correr, pular ou explorar
                        excessivamente. Gatos podem explorar a casa
                        principalmente durante períodos mais tranquilos.

                    </p>

                </article>


                <article class="reacao">

                    <div class="emoji">
                        🗣️
                    </div>

                    <h3>
                        Vocalização
                    </h3>

                    <p>

                        Cães podem latir ou choramingar.
                        Gatos podem miar mais ou modificar seus
                        padrões habituais de vocalização.

                    </p>

                </article>


                <article class="reacao">

                    <div class="emoji">
                        🛋️
                    </div>

                    <h3>
                        Se esconder
                    </h3>

                    <p>

                        É especialmente comum em gatos recém-adotados,
                        mas cães também podem procurar locais onde
                        se sintam protegidos.

                    </p>

                </article>


                <article class="reacao">

                    <div class="emoji">
                        🥺
                    </div>

                    <h3>
                        Busca por atenção
                    </h3>

                    <p>

                        Alguns cães podem procurar contato constantemente.
                        Alguns gatos também podem ficar mais carinhosos,
                        enquanto outros preferem manter distância.

                    </p>

                </article>


                <article class="reacao">

                    <div class="emoji">
                        😡
                    </div>

                    <h3>
                        Rosnados, arranhões ou desconforto
                    </h3>

                    <p>

                        Rosnados, tentativas de fuga, arranhões ou
                        outros sinais de desconforto são formas de
                        comunicação. Evite punições e dê espaço ao animal.

                    </p>

                </article>

            </div>

        </section>


        <!-- =====================================
             DIFERENÇAS ENTRE CÃES E GATOS
        ===================================== -->

        <section class="comparacao">

            <h2>
                🐶🐱 Diferenças importantes entre cães e gatos
            </h2>

            <div class="tabela-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Situação
                            </th>

                            <th>
                                🐶 Cães
                            </th>

                            <th>
                                🐱 Gatos
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr>

                            <td>
                                Adaptação
                            </td>

                            <td>
                                Podem buscar bastante interação
                                ou apresentar ansiedade.
                            </td>

                            <td>
                                Podem precisar de um espaço reservado
                                para se sentir seguros.
                            </td>

                        </tr>

                        <tr>

                            <td>
                                Necessidades
                            </td>

                            <td>
                                Precisam de rotina para passeios
                                e necessidades.
                            </td>

                            <td>
                                Precisam de caixas de areia limpas
                                e facilmente acessíveis.
                            </td>

                        </tr>

                        <tr>

                            <td>
                                Exercício
                            </td>

                            <td>
                                Passeios e atividades adequadas
                                fazem parte da rotina de muitos cães.
                            </td>

                            <td>
                                Brincadeiras e enriquecimento ambiental
                                ajudam a estimular o comportamento natural.
                            </td>

                        </tr>

                        <tr>

                            <td>
                                Espaço seguro
                            </td>

                            <td>
                                Uma área tranquila onde possa descansar.
                            </td>

                            <td>
                                Esconderijos e locais onde possa observar
                                o ambiente sem ser incomodado.
                            </td>

                        </tr>

                        <tr>

                            <td>
                                Interação
                            </td>

                            <td>
                                Muitos cães gostam de interação frequente,
                                mas cada animal possui seu próprio perfil.
                            </td>

                            <td>
                                Muitos gatos preferem controlar quando
                                e como acontece o contato.
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </section>


        <!-- =====================================
             CHECKLIST
        ===================================== -->

        <section class="checklist">

            <h2>
                📋 Checklist dos primeiros dias
            </h2>


            <div class="check">

                <span>✓</span>

                <div>
                    Prepare um local seguro e confortável para o animal.
                </div>

            </div>


            <div class="check">

                <span>✓</span>

                <div>
                    Disponibilize água limpa e fresca.
                </div>

            </div>


            <div class="check">

                <span>✓</span>

                <div>
                    Mantenha a alimentação indicada pela ONG inicialmente.
                </div>

            </div>


            <div class="check">

                <span>✓</span>

                <div>
                    Estabeleça uma rotina previsível.
                </div>

            </div>


            <div class="check">

                <span>✓</span>

                <div>
                    Evite apresentar muitas pessoas ao mesmo tempo.
                </div>

            </div>


            <div class="check">

                <span>✓</span>

                <div>
                    Supervisione a interação com crianças e outros animais.
                </div>

            </div>


            <div class="check">

                <span>✓</span>

                <div>
                    Para cães, organize uma rotina adequada de passeios
                    e necessidades.
                </div>

            </div>


            <div class="check">

                <span>✓</span>

                <div>
                    Para gatos, disponibilize caixa de areia limpa,
                    esconderijos e locais seguros.
                </div>

            </div>


            <div class="check">

                <span>✓</span>

                <div>
                    Observe mudanças de comportamento, alimentação
                    e hábitos de higiene.
                </div>

            </div>

        </section>


        <!-- =====================================
             SINAIS DE ALERTA
        ===================================== -->

        <section class="ajuda">

            <h2>
                🚨 Quando procurar ajuda
            </h2>

            <p>

                Alguns comportamentos podem fazer parte da adaptação,
                mas sinais persistentes ou intensos devem ser avaliados
                por um profissional.

            </p>


            <h3>
                🩺 Sinais físicos
            </h3>

            <ul>

                <li>
                    Recusa persistente de água ou alimento.
                </li>

                <li>
                    Vômitos ou diarreia persistentes.
                </li>

                <li>
                    Dificuldade para respirar.
                </li>

                <li>
                    Sangramentos ou ferimentos.
                </li>

                <li>
                    Dor aparente.
                </li>

                <li>
                    Apatia intensa ou perda de consciência.
                </li>

            </ul>


            <h3>
                🧠 Sinais comportamentais
            </h3>

            <ul>

                <li>
                    Agressividade intensa ou tentativa de mordida.
                </li>

                <li>
                    Medo extremo que não apresenta melhora.
                </li>

                <li>
                    Comportamento muito diferente do habitual.
                </li>

                <li>
                    Tentativas frequentes de fuga.
                </li>

                <li>
                    Alterações persistentes nos hábitos de alimentação
                    ou higiene.
                </li>

            </ul>


            <p>

                <strong>
                    Em situações de emergência ou quando houver
                    risco à saúde do animal ou das pessoas, procure
                    atendimento veterinário imediatamente.
                </strong>

            </p>

        </section>


        <!-- =====================================
             MENSAGEM FINAL
        ===================================== -->

        <div class="rodape-info">

            🐾 Cada cão e cada gato possui sua própria personalidade
            e seu próprio tempo de adaptação.

            <br><br>

            Com segurança, paciência, rotina e cuidado,
            você pode ajudar seu novo companheiro a construir
            confiança e se adaptar ao novo lar.

        </div>


    </main>

</body>

</html>
