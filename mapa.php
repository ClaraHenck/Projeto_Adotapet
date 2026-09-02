<?php
require_once __DIR__ . '/config/auth.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdotaPet - Mapa de ONGs</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="mapa.css">
</head>
<body>

    <header class="navbar">
        <div class="left-brand-box">
            <div class="logo">🐾 AdotaPet</div>
        </div>
        <nav class="menu" id="menu-navegacao">
            <a href="index.php">Início</a>
            <a href="adotar.php">Adotar</a>
            <a href="mapa.php" class="active">Mapa</a>
            <a href="candidaturas.php">Candidaturas</a>
            <a href="meu_perfil.php">MEU PERFIL</a>
            <a href="logout.php" style="color: #ff4d4d">Sair</a>
        </nav>
    </header>

    <div class="container">
        <div class="header">
            <h1>Mapa de ONGs & Abrigos</h1>
            <p>Encontre ONGs e abrigos próximos de você</p>
        </div>

        <!-- FILTRO RÁPIDO POR ESTADOS (UF) -->
        <div class="filter-select-container">
    <select id="select-estado" class="select-estado" onchange="filtrarPorSelect(this.value)">
        <option value="">Selecione um estado</option>
        <option value="AC">Acre</option>
        <option value="AL">Alagoas</option>
        <option value="AP">Amapá</option>
        <option value="AM">Amazonas</option>
        <option value="BA">Bahia</option>
        <option value="CE">Ceará</option>
        <option value="DF">Distrito Federal</option>
        <option value="ES">Espírito Santo</option>
        <option value="GO">Goiás</option>
        <option value="MA">Maranhão</option>
        <option value="MT">Mato Grosso</option>
        <option value="MS">Mato Grosso do Sul</option>
        <option value="MG">Minas Gerais</option>
        <option value="PA">Pará</option>
        <option value="PB">Paraíba</option>
        <option value="PR">Paraná</option>
        <option value="PE">Pernambuco</option>
        <option value="PI">Piauí</option>
        <option value="RJ">Rio de Janeiro</option>
        <option value="RN">Rio Grande do Norte</option>
        <option value="RS">Rio Grande do Sul</option>
        <option value="RO">Rondônia</option>
        <option value="RR">Roraima</option>
        <option value="SC">Santa Catarina</option>
        <option value="SP">São Paulo</option>
        <option value="SE">Sergipe</option>
        <option value="TO">Tocantins</option>
        </select>

        <!-- BUSCA GERAL -->
        <div class="search-container" style="margin-bottom: 20px;">
            <input
                type="text"
                id="input-busca"
                class="search-input"
                placeholder="Buscar por nome, bairro, cidade..."
            >
        </div>

        <div class="content">
            <div class="map-wrapper">
                <div id="map"></div>
            </div>
            <div class="sidebar" id="lista-ongs">
                <p style="text-align: center; color: #718096; padding: 20px;">Carregando ONGs...</p>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="mapa.js"></script>
</body>
</html>