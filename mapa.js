let map;
let marcadores = [];
let todasOngs = [];

document.addEventListener('DOMContentLoaded', initMap);

function initMap() {
    // Inicializa o mapa centralizado no Brasil
    map = L.map('map').setView([-23.55052, -46.633308], 11);

    // Servidor de mapas estável (CartoDB Voyager)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
        subdomains: 'abcd',
        attribution: '&copy; OpenStreetMap &copy; CARTO'
    }).addTo(map);

    // Garante que o Leaflet calcule o tamanho exato da div #map
    setTimeout(() => { map.invalidateSize(); }, 300);

    const inputBusca = document.getElementById('input-busca');
    if (inputBusca) {
        inputBusca.addEventListener('input', aplicarFiltrosCombinados);
    }

    carregarOngsDoBanco();
}

async function carregarOngsDoBanco() {
    try {
        const resposta = await fetch('api_ongs.php');
        const resultado = await resposta.json();

        if (resultado.sucesso) {
            todasOngs = resultado.dados;
            renderizarInterface(todasOngs);
        } else {
            document.getElementById('lista-ongs').innerHTML = 
                '<p style="padding:20px; text-align:center; color:#64748b;">Erro ao carregar dados.</p>';
        }
    } catch (erro) {
        console.error("Erro de conexão:", erro);
        document.getElementById('lista-ongs').innerHTML = 
            '<p style="padding:20px; text-align:center; color:#64748b;">Erro de conexão com o servidor.</p>';
    }
}

function renderizarInterface(ongs) {
    const containerSidebar = document.getElementById('lista-ongs');
    containerSidebar.innerHTML = '';

    marcadores.forEach(m => map.removeLayer(m));
    marcadores = [];

    if (ongs.length === 0) {
        containerSidebar.innerHTML = 
            '<p style="text-align:center; color:#64748b; padding: 20px;">Nenhuma ONG encontrada.</p>';
        return;
    }

    ongs.forEach(ong => {
        const lat = parseFloat(ong.latitude);
        const lng = parseFloat(ong.longitude);

        if (isNaN(lat) || isNaN(lng)) return;

        const marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup(`
            <div style="font-family: sans-serif;">
                <h3 style="color: #00C1DE; font-size: 15px; margin-bottom: 4px;">${ong.nome}</h3>
                <p style="margin: 0; font-size: 13px; color: #475569;">📍 ${ong.logradouro || ''}, ${ong.numero || ''} - ${ong.cidade}/${ong.estado}</p>
                <p style="margin-top: 4px; font-size: 13px;">📞 ${ong.telefone}</p>
            </div>
        `);
        marcadores.push(marker);

        const card = document.createElement('div');
        card.className = 'ong-card';
        card.innerHTML = `
            <h3>${ong.nome}</h3>
            <p>📍 ${ong.cidade}, ${ong.estado}</p>
            <p>📞 ${ong.telefone}</p>
        `;

        card.addEventListener('click', () => {
            map.flyTo([lat, lng], 15, { duration: 1.2 });
            marker.openPopup();
        });

        containerSidebar.appendChild(card);
    });
}

function filtrarPorSelect() {
    aplicarFiltrosCombinados();
}

function aplicarFiltrosCombinados() {
    const estadoSelecionado = document.getElementById('select-estado').value.toUpperCase();
    const textoBusca = document.getElementById('input-busca').value.toLowerCase().trim();

    const filtradas = todasOngs.filter(ong => {
        const bateEstado = !estadoSelecionado || (ong.estado && ong.estado.toUpperCase() === estadoSelecionado);
        const bateTexto = !textoBusca ||
            (ong.nome && ong.nome.toLowerCase().includes(textoBusca)) ||
            (ong.cidade && ong.cidade.toLowerCase().includes(textoBusca)) ||
            (ong.bairro && ong.bairro.toLowerCase().includes(textoBusca));

        return bateEstado && bateTexto;
    });

    renderizarInterface(filtradas);
}