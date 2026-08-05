function carregarCabecalho() {
    // 1. Cria o elemento HTML do cabeçalho
    const header = document.createElement('header');
    header.className = 'main-header';

    // 2. Define a estrutura do cabeçalho
    header.innerHTML = `
        <div class="logo">
            <span class="paw-icon">🐾</span>
            <h1>AdotaPet</h1>
        </div>
        <nav class="nav-links">
            <a href="index.html" id="nav-inicio">Início</a>
            <a href="adotar.html" id="nav-adotar">Adotar</a>
            <a href="mapa.html" id="nav-mapa">Mapa</a>
            <a href="candidaturas.html" id="nav-candidaturas">Candidaturas</a>

            <div class="profile-section">
                <span class="profile-pic">👤</span>
                <span class="profile-name" id="profile-name">MEU PERFIL</span>
            </div>

            <a href="#" class="btn-logout">Sair</a>
        </nav>
    `;

    // Injeta o cabeçalho no início do body
    document.body.insertBefore(header, document.body.firstChild);

    // ===== Validação do tipo de usuário =====
    const tipoUsuario = localStorage.getItem("tipoUsuario");

    const navMapa = document.getElementById("nav-mapa");
    const navAdotar = document.getElementById("nav-adotar");
    const navCandidaturas = document.getElementById("nav-candidaturas");
    const profileName = document.getElementById("profile-name");

    if (tipoUsuario === "ong") {

        // Oculta páginas exclusivas do adotante
        if (navMapa) navMapa.style.display = "none";
        if (navAdotar) navAdotar.style.display = "none";

        // Altera o menu de candidaturas
        if (navCandidaturas) {
            navCandidaturas.textContent = "Candidaturas Recebidas";
            navCandidaturas.href = "candidaturas_recebidas.html";
        }

        // Altera o texto do perfil
        if (profileName) {
            profileName.textContent = "MINHA ONG";
        }

    } else {

        // Mantém configuração do adotante
        if (navCandidaturas) {
            navCandidaturas.textContent = "Candidaturas";
            navCandidaturas.href = "candidaturas.html";
        }

        if (profileName) {
            profileName.textContent = "MEU PERFIL";
        }
    }

    // Destaca automaticamente a página atual
    const paginaAtual = window.location.pathname.split("/").pop();

    if (paginaAtual === "index.html" || paginaAtual === "") {
        document.getElementById("nav-inicio").classList.add("active");
    } else if (paginaAtual === "adotar.html") {
        document.getElementById("nav-adotar").classList.add("active");
    } else if (
        paginaAtual === "candidaturas.html" ||
        paginaAtual === "candidaturas_recebidas.html"
    ) {
        document.getElementById("nav-candidaturas").classList.add("active");
    }
}

// Executa assim que o documento HTML estiver pronto
window.addEventListener("DOMContentLoaded", carregarCabecalho);