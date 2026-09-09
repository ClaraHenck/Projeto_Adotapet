document.addEventListener('DOMContentLoaded', () => {
    // 1. Redirecionamento ao clicar no Card do Pet
    const cards = document.querySelectorAll('.card');
    
    cards.forEach(card => {
        card.addEventListener('click', () => {
            const petId = card.getAttribute('data-id');
            const petNome = card.querySelector('.pet-nome').innerText;
            
            localStorage.setItem('petSelecionadoNome', petNome);
            window.location.href = `detalhes.php?id=${petId}`;
        });
    });

    // 2. Elementos da Busca e Filtros
    const inputSearch = document.getElementById('input-search');
    const selectPorte = document.getElementById('select-porte');
    const selectIdade = document.getElementById('select-idade');
    const contadorAnimais = document.getElementById('contador-animais');
    const mensagemSemResultados = document.getElementById('mensagem-sem-resultados');
    const btnFiltrar = document.getElementById('btn-filtrar');

    // 3. Função principal de Filtragem
    function aplicarFiltro() {
        const termoBusca = inputSearch.value.toLowerCase().trim();
        const porteSelecionado = selectPorte.value;
        const idadeSelecionada = selectIdade.value;

        let visiveis = 0;

        cards.forEach(card => {
            const nome = card.dataset.nome || '';
            const raca = card.dataset.raca || '';
            const porte = card.dataset.porte || '';
            const idade = card.dataset.idade || '';

            // Validação dos filtros
            const bateuBusca = !termoBusca || nome.includes(termoBusca) || raca.includes(termoBusca);
            const bateuPorte = !porteSelecionado || porte === porteSelecionado;
            const bateuIdade = !idadeSelecionada || idade.toLowerCase().includes(idadeSelecionada.toLowerCase());

            if (bateuBusca && bateuPorte && bateuIdade) {
                card.style.display = 'flex';
                visiveis++;
            } else {
                card.style.display = 'none';
            }
        });

        // Atualização do contador de resultados
        if (contadorAnimais) {
            contadorAnimais.innerText = `${visiveis} animais encontrados para adoção`;
        }

        // Exibe/Oculta a mensagem de "Nenhum resultado"
        if (mensagemSemResultados) {
            mensagemSemResultados.style.display = visiveis === 0 ? 'block' : 'none';
        }
    }

    // 4. Escutadores de Eventos (Event Listeners)
    if (inputSearch) inputSearch.addEventListener('input', aplicarFiltro);
    if (selectPorte) selectPorte.addEventListener('change', aplicarFiltro);
    if (selectIdade) selectIdade.addEventListener('change', aplicarFiltro);
    if (btnFiltrar) btnFiltrar.addEventListener('click', aplicarFiltro);
});