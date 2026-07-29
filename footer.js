// Função que cria e adiciona o rodapé dinamicamente na página
function carregarRodape() {
    // 1. Cria o elemento HTML de rodapé
    const footer = document.createElement('footer');
    footer.className = 'main-footer';

    // 2. Define o conteúdo HTML do rodapé (mude os textos se quiser)
    footer.innerHTML = `
        <div class="footer-container">
            <div class="footer-logo">
                <span>🐾</span> AdotaPet
            </div>
            <p class="footer-text">&copy; 2026 AdotaPet - Plataforma de Adoção Responsável. Todos os direitos reservados.</p>
        </div>
    `;

    // 3. Coloca o rodapé automaticamente no final do arquivo HTML (dentro da tag body)
    document.body.appendChild(footer);
}

// Executa a função assim que a página carregar
window.addEventListener('DOMContentLoaded', carregarRodape);
