function carregarCabecalho() {
    // 1. Cria o elemento HTML do cabeçalho
    const header = document.createElement('header');
    header.className = 'main-header';

    // 2. Define a estrutura exatamente igual à da sua imagem
    header.innerHTML = `
        <div class="logo">
            <span class="paw-icon">🐾</span>
            <h1>AdotaPet</h1>
        </div>
        <nav class="nav-links">
            <a href="index.html" id="nav-inicio">Início</a>
            <a href="adotar.html" id="nav-adotar">Adotar</a>
            <a href="#" id="nav-mapa">Mapa</a>
            <a href="candidaturas.html" id="nav-candidaturas">Candidaturas</a>
            <div class="profile-section">
                <span class="profile-pic">👤</span>
                <span class="profile-name">MEU PERFIL</span>
            </div>
            <a href="#" class="btn-logout">Sair</a>
        </nav>
    `;

    // 3. Injeta o cabeçalho no início do body (antes de qualquer outro elemento)
    document.body.insertBefore(header, document.body.firstChild);

    // 4. Lógica para destacar automaticamente o link da página atual
    const paginaAtual = window.location.pathname.split("/").pop();
    
    if (paginaAtual === "index.html" || paginaAtual === "") {
        document.getElementById("nav-inicio").classList.add("active");
    } else if (paginaAtual === "adotar.html") {
        document.getElementById("nav-adotar").classList.add("active");
    } else if (paginaAtual === "candidaturas.html") {
        document.getElementById("nav-candidaturas").classList.add("active");
    }
}

// Executa assim que o documento HTML estiver pronto
window.addEventListener('DOMContentLoaded', carregarCabecalho);
