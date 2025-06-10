// Espera todo o conteúdo do HTML ser carregado antes de executar o script.
document.addEventListener('DOMContentLoaded', () => {

    // --- VERIFICAÇÃO INICIAL ---
    // Procura todos os elementos necessários no HTML.
    const roleta = document.getElementById('roleta');
    const girarBtn = document.getElementById('girar-btn');
    const modoRadios = document.querySelectorAll('input[name="modo"]');
    const premioSelect = document.getElementById('premio-select');
    const probabilidadeContainer = document.getElementById('probabilidade-container');
    const porcentagemInput = document.getElementById('porcentagem-input');

    // Se algum elemento essencial não for encontrado, exibe um alerta e para a execução.
    if (!roleta || !girarBtn || !premioSelect || !probabilidadeContainer || !porcentagemInput) {
        alert("ERRO: Um ou mais elementos do HTML não foram encontrados. Verifique se os IDs e classes no arquivo 'index.html' estão corretos e se o arquivo 'script.js' está sendo chamado no final do <body>.");
        return; // Para o script aqui para evitar mais erros.
    }
    
    // --- CONFIGURAÇÃO DA ROLETA ---
    const premios = [
        'Prêmio 1', 'Prêmio 2', 'Prêmio 3', 'Prêmio 4', 'Prêmio 5',
        'Prêmio 6', 'Prêmio 7', 'Prêmio 8', 'Prêmio 9', 'Prêmio 10',
        'Prêmio 11', 'Prêmio 12', 'Prêmio 13', 'Prêmio 14', 'Prêmio 15',
        'Prêmio 16', 'Prêmio 17', 'Prêmio 18', 'Prêmio 19', 'Tente Novamente'
    ];

    const totalPremios = premios.length;
    const anguloPorFatia = 360 / totalPremios;
    let modoAtual = 'direto';
    let isGirando = false;

    // Popula a roleta e a lista de seleção de prêmios
    premios.forEach((premio, i) => {
        const fatia = document.createElement('div');
        const texto = document.createElement('span');

        fatia.style.position = 'absolute';
        fatia.style.width = '50%';
        fatia.style.height = '50%';
        fatia.style.backgroundColor = i % 2 === 0 ? '#3498db' : '#ecf0f1';
        fatia.style.transformOrigin = '100% 100%';
        fatia.style.transform = `rotate(${i * anguloPorFatia}deg) skewX(${90 - anguloPorFatia}deg)`;
        
        texto.textContent = premio;
        texto.style.position = 'absolute';
        texto.style.color = '#333';
        texto.style.left = '10%';
        texto.style.top = '20%';
        texto.style.transform = `skewX(${-(90 - anguloPorFatia)}deg) rotate(${anguloPorFatia / 2}deg)`;
        texto.style.fontSize = '12px';
        fatia.appendChild(texto);
        roleta.appendChild(fatia);

        const option = document.createElement('option');
        option.value = premio;
        option.textContent = premio;
        premioSelect.appendChild(option);
    });

    // --- LÓGICA DOS CONTROLES ---
    modoRadios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            modoAtual = e.target.value;
            probabilidadeContainer.classList.toggle('hidden', modoAtual !== 'probabilidade');
        });
    });

    const sortearPremioComProbabilidade = () => {
        const premioAlvo = premioSelect.value;
        const chanceAlvo = parseFloat(porcentagemInput.value) || 0;

        if (chanceAlvo < 0 || chanceAlvo > 100) {
            alert("A porcentagem deve ser entre 0 e 100.");
            return null;
        }

        if (Math.random() * 100 < chanceAlvo) {
            return premioAlvo;
        }

        const outrosPremios = premios.filter(p => p !== premioAlvo);
        return outrosPremios[Math.floor(Math.random() * outrosPremios.length)];
    };

    const girarRoleta = (premioVencedor) => {
        if (!premioVencedor) return;
        isGirando = true;
        girarBtn.disabled = true;

        const indicePremio = premios.indexOf(premioVencedor);
        const voltasExtras = 5;
        const variacao = (Math.random() - 0.5) * anguloPorFatia * 0.8;
        const anguloFinalBase = 360 - (indicePremio * anguloPorFatia);
        const anguloDeCentralizacao = anguloPorFatia / 2;
        
        const anguloFinal = (voltasExtras * 360) + anguloFinalBase - anguloDeCentralizacao + variacao;

        roleta.style.transform = `rotate(${anguloFinal}deg)`;

        setTimeout(() => {
            isGirando = false;
            girarBtn.disabled = false;
            alert(`Parabéns! Você ganhou: ${premioVencedor}`);
        }, 5500);
    };

    girarBtn.addEventListener('click', () => {
        if (isGirando) return;

        let premioFinal = (modoAtual === 'direto')
            ? premioSelect.value
            : sortearPremioComProbabilidade();

        girarRoleta(premioFinal);
    });
});