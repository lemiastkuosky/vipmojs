// SCRIPT COMPLETO - INÍCIO
document.addEventListener('DOMContentLoaded', async () => {
    // --- 1. REFERÊNCIAS GLOBAIS ---
    const relatorioTotalContainer = document.getElementById('relatorio-total-container');
    const relatorioSessaoContainer = document.getElementById('relatorio-sessao-container');
    const logJogadasContainer = document.getElementById('log-jogadas-container');
    const iniciarSessaoBtn = document.getElementById('iniciar-sessao-btn');
    const invictorDisplay = document.getElementById('invictor-display');
    const arrojadaDisplay = document.getElementById('arrojada-display');
    const limparBtn = document.getElementById('limparBtn');
    const undoBtn = document.getElementById('undoBtn');
    const tabelaRoletaContainer = document.getElementById('tabela-roleta-container');
    const historicoDiv = document.getElementById('historicoNumeros');
    const estrategiasContainer = document.getElementById('estrategias-container');
    const alertasContainer = document.getElementById('alertas-container');
    const atrasosDisplay = document.getElementById('atrasos-display');
    const setoresDisplay = document.getElementById('setores-display');
    const modalOverlay = document.getElementById('modal-numero-overlay');
    const modalBody = document.getElementById('modal-body');
    const modalCloseBtn = document.getElementById('modal-close-btn');
    const hotNumbersDisplay = document.getElementById('hot-numbers-display');
    const coldNumbersDisplay = document.getElementById('cold-numbers-display');
    const statisticalDelayDisplay = document.getElementById('statistical-delay-display');
    const avisosBtn = document.getElementById('avisosBtn');
    const modalAvisosOverlay = document.getElementById('modal-avisos-overlay');
    const modalAvisosCloseBtn = document.getElementById('modal-avisos-close-btn');
    const avisosEstrategiasContainer = document.getElementById('avisos-estrategias-container');
    const numeroInput = document.getElementById('numero-input');
    const adicionarNumeroBtn = document.getElementById('adicionar-numero-btn');
    const selecionarTodosAvisosBtn = document.getElementById('selecionar-todos-avisos-btn');
    const desmarcarTodosAvisosBtn = document.getElementById('desmarcar-todos-avisos-btn');
    const modalEstrategiaOverlay = document.getElementById('modal-estrategia-overlay');
    const modalEstrategiaCloseBtn = document.getElementById('modal-estrategia-close-btn');
    const salvarEstrategiaBtn = document.getElementById('salvar-estrategia-btn');
    const modalEstrategiaTitulo = document.getElementById('modal-estrategia-titulo');
    const estrategiaNomeInput = document.getElementById('estrategia-nome-input');
    const estrategiaGatilhosInput = document.getElementById('estrategia-gatilhos-input');
    const estrategiaAlvosInput = document.getElementById('estrategia-alvos-input');
    const estrategiaVizinhosInput = document.getElementById('estrategia-vizinhos-input');
    const estrategiaIdInput = document.getElementById('estrategia-id-input');
    const bancaInicialInput = document.getElementById('banca-inicial-input');
    const iniciarBancaBtn = document.getElementById('iniciar-banca-btn');
    const saldoAtualDisplay = document.getElementById('saldo-atual-display');
    const lucroPrejuizoDisplay = document.getElementById('lucro-prejuizo-display');
    const unidadesEmJogoDisplay = document.getElementById('unidades-em-jogo-display');
    const bancaBtn = document.getElementById('bancaBtn');
    const modalBancaOverlay = document.getElementById('modal-banca-overlay');
    const modalBancaCloseBtn = document.getElementById('modal-banca-close-btn');
    const saldoAtualResumo = document.getElementById('saldo-atual-resumo');
    const lucroPrejuizoResumo = document.getElementById('lucro-prejuizo-resumo');
    const chipSelectorContainer = document.getElementById('chip-selector-container');
    const habilitarEstrategiasContainer = document.getElementById('habilitar-estrategias-container');
    const analisarTimingBtn = document.getElementById('analisar-timing-btn');
    const prejuizoManualInput = document.getElementById('prejuizo-manual-input');
    const aplicarPrejuizoBtn = document.getElementById('aplicar-prejuizo-btn');
    const bancaHistoricoContainer = document.getElementById('banca-historico-container');
    const abrirGerenciadorBtn = document.getElementById('abrir-gerenciador-estrategias-btn');
    const modalGerenciadorOverlay = document.getElementById('modal-gerenciador-overlay');
    const modalGerenciadorCloseBtn = document.getElementById('modal-gerenciador-close-btn');
    const listaTodasEstrategiasContainer = document.getElementById('lista-todas-estrategias-container');
    const abrirModalEstrategiaBtn = document.getElementById('abrir-modal-estrategia-btn');
    const abrirConfigPadraoBtn = document.getElementById('abrir-config-padrao-btn');
    const modalConfigPadraoOverlay = document.getElementById('modal-config-padrao-overlay');
    const modalConfigPadraoCloseBtn = document.getElementById('modal-config-padrao-close-btn');
    const avisoCentralAlertasBar = document.getElementById('aviso-central-alertas-bar');
    const avisoCentralAlertasTexto = document.getElementById('aviso-central-alertas-texto');
    const avisoCentralAlertasBtn = document.getElementById('aviso-central-alertas-btn');
    const modalCentralAlertasOverlay = document.getElementById('modal-central-alertas-overlay');
    const modalCentralAlertasCloseBtn = document.getElementById('modal-central-alertas-close-btn');
    const centralAlertasContainer = document.getElementById('central-alertas-container');
    const apostasAtivasContainer = document.getElementById('apostas-ativas-container');
    const bancaResumoBar = document.querySelector('.banca-resumo-bar');
    const habilitarBancaCheck = document.getElementById('habilitar-banca-check');
    const logo = document.getElementById('logo');
    const customAlertOverlay = document.getElementById('custom-alert-overlay');
    const customAlertMessage = document.getElementById('custom-alert-message');
    const customAlertCloseBtn = document.getElementById('custom-alert-close-btn');
    const customAlertOkBtn = document.getElementById('custom-alert-ok-btn');
    const customConfirmOverlay = document.getElementById('custom-confirm-overlay');
    const customConfirmMessage = document.getElementById('custom-confirm-message');
    const customConfirmOkBtn = document.getElementById('custom-confirm-ok-btn');
    const customConfirmCancelBtn = document.getElementById('custom-confirm-cancel-btn');
    const importarHistoricoBtn = document.getElementById('importar-historico-btn');
    const modalImportarOverlay = document.getElementById('modal-importar-overlay');
    const modalImportarCloseBtn = document.getElementById('modal-importar-close-btn');
    const readFromSelectionBtn = document.getElementById('read-from-selection-btn');
    const importFromTextBtn = document.getElementById('import-from-text-btn');
    const importarTextoArea = document.getElementById('importar-texto-area');
    const pasteArea = document.getElementById('paste-area');
    const ocrProgress = document.getElementById('ocr-progress');
    const importarInstrucao = document.getElementById('importar-instrucao');
    const cardHistorico = document.getElementById('card-historico');
    const fixarHistoricoCheck = document.getElementById('fixar-historico-check');
    const medidorMesaContainer = document.getElementById('medidor-mesa-container');
    const medidorMesaBarra = document.getElementById('medidor-mesa-barra');
    const medidorMesaTexto = document.getElementById('medidor-mesa-texto');
    const stopGainInput = document.getElementById('stop-gain-input');
    const stopLossInput = document.getElementById('stop-loss-input');
    const habilitarHeatmapCheck = document.getElementById('habilitar-heatmap-check');
    const heatmapLegenda = document.getElementById('heatmap-legenda'); 
    const modalConfigGeraisOverlay = document.getElementById('modal-config-gerais-overlay');
    const abrirConfigGeraisBtn = document.getElementById('abrir-config-gerais-btn');
    const modalConfigGeraisCloseBtn = document.getElementById('modal-config-gerais-close-btn');
    const modalHabilitarEstrategiasOverlay = document.getElementById('modal-habilitar-estrategias-overlay');
    const abrirHabilitarEstrategiasBtn = document.getElementById('abrir-habilitar-estrategias-btn');
    const modalHabilitarEstrategiasCloseBtn = document.getElementById('modal-habilitar-estrategias-close-btn');
    const cardQuenteFrio = document.getElementById('card-quente-frio');



    // --- 2. VARIÁVEIS DE ESTADO ---
    let historicoNumeros = [];
    let logJogadas = [];
    let estadoEstrategias = {};
    let apostasAtivas = [];
    let apostasAutomaticas = [];
    let estadoInvictorArrojada = {};
    let estadoSequenciaCor = {};
    let avisoConfig = {};
    let strategyTimingConfig = {};
    let strategyCustomNames = {};
    let customStrategies = [];
    let bancaInicial = 1000;
    let valorUnidade = 1;
    let saldoAtual = 1000;
    let unidadesEmJogo = 0;
    let estrategiasHabilitadas = {};
    let strategyOrder = [];
    let draggedItem = null;
    let editingTxnId = null;
    let historicoBanca = [];
    let bancaHabilitada = true;
    let timingPerformanceData = {};
    let filtroPrioridadeAtual = 'todos';
    let alertasSugeridosGlobal = [];
    const customNamesDocRef = db.collection("configs").doc("strategyNames");
    let metaStopGain = 0;
    let metaStopLoss = 0;
    let stopGainAlertado = false;
    let stopLossAlertado = false;
    let heatmapHabilitado = true;

    // --- 3. DADOS E CONFIGURAÇÕES ---
    const DELETION_PASSWORD = '121212';
    const ROULETTE_WHEEL = [0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5, 24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26];
    const INVICTOR_MULTIPLOS_GRUPO_A = [2, 4, 8];
    const INVICTOR_MULTIPLOS_GRUPO_B = [3, 6, 9];
    const INVICTOR_MULTIPLOS = [...INVICTOR_MULTIPLOS_GRUPO_A, ...INVICTOR_MULTIPLOS_GRUPO_B];
    const INVICTOR_QUEBRANTES = [5, 7, 1];
    const INVICTOR_PROTETIVO = 0;
    const numerosVermelhos = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];
    const numerosPretos = [2, 4, 6, 8, 10, 11, 13, 15, 17, 20, 22, 24, 26, 28, 29, 31, 33, 35];
    const duzia1 = Array.from({ length: 12 }, (_, i) => i + 1);
    const duzia2 = Array.from({ length: 12 }, (_, i) => i + 13);
    const duzia3 = Array.from({ length: 12 }, (_, i) => i + 25);
    const coluna1 = [1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31, 34];
    const coluna2 = [2, 5, 8, 11, 14, 17, 20, 23, 26, 29, 32, 35];
    const coluna3 = [3, 6, 9, 12, 15, 18, 21, 24, 27, 30, 33, 36];
    const QUADRANTES = { Q1: [32, 15, 19, 4, 21, 2, 25, 17, 34], Q2: [6, 27, 13, 36, 11, 30, 8, 23, 10], Q3: [5, 24, 16, 33, 1, 20, 14, 31, 9], Q4: [22, 18, 29, 7, 28, 12, 35, 3, 26] };
    const SETOR_VOISINS = [22, 18, 29, 7, 28, 12, 35, 3, 26, 0, 32, 15, 19, 4, 21, 2, 25];
    const SETOR_TIERS = [27, 13, 36, 11, 30, 8, 23, 10, 5, 24, 16, 33];
    const SETOR_ORPHELINS = [1, 6, 9, 14, 17, 20, 31, 34];
    const ESTRATEGIAS_PADRAO = [
        { id: 'estrat-6', titulo: 'Alvo 5 e 22 + Zero', gatilhos: [2, 5, 25], alvos: [5, 22], vizinhos: 3, cobertura: [0], prioridade: 2 },
        { id: 'estrat-7', titulo: 'Alvo 14 e 34 + Zero', gatilhos: [14, 34], alvos: [14, 34], vizinhos: 3, cobertura: [0], prioridade: 2 },
        { id: 'estrat-8', titulo: 'Alvo 17 e 20 + Zero', gatilhos: [17, 20], alvos: [17, 20], vizinhos: 3, cobertura: [0], prioridade: 2 },
        { id: 'estrat-9', titulo: 'Alvo 25 e 0 + Zero', gatilhos: [23, 33], alvos: [25, 0], vizinhos: 3, cobertura: [0], prioridade: 2 },
        { id: 'estrat-10', titulo: 'Alvo 10 e 27 + Zero', gatilhos: [28], alvos: [10, 27], vizinhos: 3, cobertura: [0], prioridade: 2 },
        { id: 'invictor-conservadora', titulo: 'Invictor (Conservadora)', type: 'automatica', prioridade: 2 },
        { id: 'estrat-1', titulo: 'Alvo: 9 e 17', gatilhos: [2, 4, 7, 9, 17, 19, 20, 22, 25, 34], alvos: [9, 17], vizinhos: 5, prioridade: 3 },
        { id: 'estrat-2', titulo: 'Alvo: 32 e 23', gatilhos: [5, 10, 16, 23, 26, 32], alvos: [32, 23], vizinhos: 5, prioridade: 3 },
        { id: 'estrat-3', titulo: 'Alvo: 30 e 35', gatilhos: [0, 3, 10, 26, 32, 35, 36], alvos: [30, 35], vizinhos: 5, prioridade: 3 },
        { id: 'estrat-4', titulo: 'Alvo: 10 e 26', gatilhos: [5, 35], alvos: [10, 26], vizinhos: 5, prioridade: 3 },
        { id: 'estrat-5-alvo-8-7', titulo: 'Alvo 8 e 7 (Vizinhos)', gatilhos: [8, 18, 28], alvos: [8, 7], vizinhos: 5, prioridade: 3 },
        { id: 'estrat-quad-1', titulo: 'Quadrante (Q1/Q4): 5 viz. de 16 e 19', gatilhos: [...QUADRANTES.Q1, ...QUADRANTES.Q4], alvos: [16, 19], vizinhos: 5, prioridade: 3 },
        { id: 'estrat-quad-2', titulo: 'Quadrante (Q1/Q4): 5 viz. de 1 e 21', gatilhos: [...QUADRANTES.Q1, ...QUADRANTES.Q4], alvos: [1, 21], vizinhos: 5, prioridade: 3 },
        { id: 'estrat-quad-3', titulo: 'Quadrante (Q1/Q4): 5 viz. de 4 e 14', gatilhos: [...QUADRANTES.Q1, ...QUADRANTES.Q4], alvos: [4, 14], vizinhos: 5, prioridade: 3 },
        { id: 'estrat-quad-4', titulo: 'Quadrante (Q2/Q3): 5 viz. de 7 e 13', gatilhos: [...QUADRANTES.Q2, ...QUADRANTES.Q3], alvos: [7, 13], vizinhos: 5, prioridade: 4 },
        { id: 'estrat-quad-5', titulo: 'Quadrante (Q2/Q3): 5 viz. de 27 e 28', gatilhos: [...QUADRANTES.Q2, ...QUADRANTES.Q3], alvos: [27, 28], vizinhos: 5, prioridade: 4 },
        { id: 'iron-man', titulo: 'Iron Man', gatilhos: [1, 5, 13, 18, 28], alvos: [4, 1], vizinhos: 3, prioridade: 2 },
        { id: 'invictor-arrojada', titulo: 'Invictor (Arrojada)', type: 'automatica', prioridade: 1 },
        { id: 'sequencia-cor', titulo: 'Sequência de Cor', type: 'automatica', prioridade: 2 },
        { id: 'estrela-davi', titulo: 'Estrela de Davi', type: 'alternancia', grupos: { g1: [30, 31, 32], g2: [33, 34, 35] }, alvosBase: [30, 31, 32, 33, 34, 35], vizinhos: 1, prioridade: 2 },
        { id: 'duzia1-desvio', titulo: '1ª Dúzia (Atraso)', type: 'atraso-fixo', alvos: duzia1, gatilhoFixo: 6, prioridade: 2 },
        { id: 'duzia2-desvio', titulo: '2ª Dúzia (Atraso)', type: 'atraso-fixo', alvos: duzia2, gatilhoFixo: 6, prioridade: 2 },
        { id: 'duzia3-desvio', titulo: '3ª Dúzia (Atraso)', type: 'atraso-fixo', alvos: duzia3, gatilhoFixo: 6, prioridade: 2 },
        { id: 'coluna1-desvio', titulo: 'Coluna 1 (Atraso)', type: 'atraso-fixo', alvos: coluna1, gatilhoFixo: 6, prioridade: 2 },
        { id: 'coluna2-desvio', titulo: 'Coluna 2 (Atraso)', type: 'atraso-fixo', alvos: coluna2, gatilhoFixo: 6, prioridade: 2 },
        { id: 'coluna3-desvio', titulo: 'Coluna 3 (Atraso)', type: 'atraso-fixo', alvos: coluna3, gatilhoFixo: 6, prioridade: 2 },
        { id: 'jeu-zero-desvio', titulo: 'Jogo do Zero (Atraso)', type: 'desvio-estatistico', alvos: [0, 3, 12, 15, 26, 32, 35], gatilhoPadrao: 2.0, prioridade: 1 },
        { id: 'finais-3-4', titulo: 'Finais 3 e 4', gatilhos: [2, 5, 12, 15, 22, 25, 32, 35], alvos: [3, 4, 13, 14, 23, 24, 33, 34], vizinhos: 0, prioridade: 2 },
        { id: 'james-bond', titulo: 'Estratégia James Bond', gatilhos: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], alvos: [0, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36], vizinhos: 0, prioridade: 2 },
        { id: 'james-inverso', titulo: 'James Inverso', gatilhos: [25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36], alvos: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24], vizinhos: 0, prioridade: 2 },
        { id: 'eco-vizinhos', titulo: 'Eco do Último Número', type: 'dinamica', vizinhos: 2, prioridade: 3 },
        { id: 'eco-simples', titulo: 'Eco Simples (1 Vizinho)', type: 'dinamica', vizinhos: 1, prioridade: 3 },
        { id: 'romana', titulo: 'Estratégia Romana', gatilhos: [4, 6, 26, 27], alvos: [10, 0], vizinhos: 3, prioridade: 2 },
        { id: 'alternancia-duzias', titulo: 'Alternância de Dúzias', type: 'automatica', prioridade: 3 },
        { id: 'repeticao-setor', titulo: 'Repetição de Setor', type: 'automatica', prioridade: 3 },
    ];
    const getAllStrategies = () => {
        const all = [...ESTRATEGIAS_PADRAO, ...customStrategies];
        const strategyMap = new Map(all.map(s => [s.id, s]));
        if (strategyOrder.length === 0) return all;
        const orderedStrategies = strategyOrder
            .map(id => strategyMap.get(id))
            .filter(Boolean);
        return orderedStrategies;
    };

    // --- 4. LÓGICA PRINCIPAL ---
    const showCustomAlert = (message) => {
        customAlertMessage.textContent = message;
        customAlertOverlay.classList.remove('hidden');
    };
    const showCustomConfirm = (message) => {
        return new Promise(resolve => {
            customConfirmMessage.textContent = message;
            customConfirmOverlay.classList.remove('hidden');

            const cleanupAndResolve = (result) => {
                customConfirmOverlay.classList.add('hidden');
                customConfirmOkBtn.replaceWith(customConfirmOkBtn.cloneNode(true));
                customConfirmCancelBtn.replaceWith(customConfirmCancelBtn.cloneNode(true));
                resolve(result);
            };
            
            document.getElementById('custom-confirm-ok-btn').addEventListener('click', () => cleanupAndResolve(true));
            document.getElementById('custom-confirm-cancel-btn').addEventListener('click', () => cleanupAndResolve(false));
        });
    };
    const performUndo = () => {
        if (historicoNumeros.length === 0) return;

        const historicoAntigo = [...historicoNumeros];
        historicoAntigo.pop();

        historicoNumeros = [];
        apostasAtivas = [];
        apostasAutomaticas = [];
        logJogadas = [];
        resetarTodosOsEstados();
        inicializarTimingPerformance();

        historicoAntigo.forEach(num => processarNumero(num));
        renderizarTudo();
    };
    const voltarParaInicio = () => {
        const abaInicial = document.querySelector('.tab-nav li[data-tab="tab-geral"]');
        if (abaInicial) {
            abaInicial.click();
        }
    };
    const adicionarNumero = (numero) => {
        const botao = tabelaRoletaContainer.querySelector(`.botao-numero[data-numero="${numero}"]`);
        if (botao) {
            botao.classList.add('flash');
            setTimeout(() => { botao.classList.remove('flash'); }, 800);
        }
        processarNumero(numero);
        renderizarTudo();
    };
    const processarNumero = (numero) => {
        historicoNumeros.push(numero);
        atualizarPerformanceTiming(numero);
        processarResultadosDeApostasManuais();
        processarResultadosDeApostasAutomaticas();
    };
   const renderizarTudo = () => {
        gerenciarAlertas();
        renderizarApostasAtivas();
        analisarInvictor();
        analisarArrojada();
        analisarAtrasos();
        analisarAtrasosDeSetores();
        renderizarHistorico();
        renderizarRelatorios();
        renderizarLogJogadas();
        calcularEstatisticasPorNumero();
        analisarNumerosQuentes();
        analisarNumerosFrios();
        analisarAtrasoEstatistico();
        renderizarControlesHabilitarEstrategias();
        renderizarControlesEstrategias();
        renderBankroll();
        renderizarMedidorDeMesa();
        renderizarHeatmap(); // <-- ADICIONE ESTA LINHA
    };
    const processarInputPrincipal = () => {
        const texto = numeroInput.value.trim();
        if (!texto) return;
        const numerosParaProcessar = texto.split(/[\s,;\n]+/).map(n => parseInt(n, 10)).filter(n => !isNaN(n) && n >= 0 && n <= 36);
        if (numerosParaProcessar.length === 0) {
            showCustomAlert("Nenhum número válido encontrado.");
            numeroInput.value = "";
            return;
        }
        if (numerosParaProcessar.length === 1) {
            adicionarNumero(numerosParaProcessar[0]);
        } else {
            numerosParaProcessar.forEach((num, index) => {
                setTimeout(() => {
                    const botao = tabelaRoletaContainer.querySelector(`.botao-numero[data-numero="${num}"]`);
                    if (botao) botao.classList.add('flash');
                    setTimeout(() => { if (botao) botao.classList.remove('flash'); }, 800);
                }, index * 100);
                processarNumero(num);
            });
            renderizarTudo();
        }
        numeroInput.value = "";
        numeroInput.focus();
    };
    const renderizarApostasAtivas = () => {
        if (!apostasAtivasContainer) return;
        apostasAtivasContainer.innerHTML = '';

        if (apostasAtivas.length > 0) {
            const titulo = document.createElement('h2');
            titulo.style.color = 'var(--accent-blue)';
            titulo.textContent = 'Apostas em Andamento';
            apostasAtivasContainer.appendChild(titulo);
        }

        apostasAtivas.forEach(aposta => {
            const giroAtual = historicoNumeros.length;
            let progressoTexto = '';

            if (aposta.tentativa === 1) {
                progressoTexto = 'Aguardando 1º giro...';
            } else if (aposta.tentativa === 2) {
                progressoTexto = 'Aposta dobrada! Aguardando 2º giro...';
            }

            const card = document.createElement('div');
            card.className = 'aposta-ativa-card';
            card.innerHTML = `
                <p><strong>${getStrategyName(aposta.id)}</strong></p>
                <p>Aposta em: ${aposta.alvosTexto}</p>
                <p class="progresso-giros">${progressoTexto}</p>
            `;
            apostasAtivasContainer.appendChild(card);
        });
    };
    const renderizarMedidorDeMesa = () => {
        if (!medidorMesaContainer) return;

        let totalVitorias = 0;
        let totalDerrotas = 0;
        
        getAllStrategies().forEach(estrat => {
            if (isEstrategiaHabilitada(estrat.id)) {
                let estado;
                switch (estrat.id) {
                    case 'invictor-arrojada':
                        estado = estadoInvictorArrojada;
                        break;
                    case 'sequencia-cor':
                        estado = estadoSequenciaCor;
                        break;
                    default:
                        estado = estadoEstrategias[estrat.id];
                        break;
                }
                if (estado) {
                    totalVitorias += estado.vitorias || 0;
                    totalDerrotas += estado.derrotas || 0;
                }
            }
        });

        const totalJogadas = totalVitorias + totalDerrotas;

        if (totalJogadas < 10) {
            medidorMesaContainer.classList.add('hidden');
            return;
        }
        medidorMesaContainer.classList.remove('hidden');

        const winRate = (totalVitorias / totalJogadas) * 100;
        const lucro = saldoAtual - bancaInicial;

        let cor = 'amarela';
        let texto = 'Mesa com Atenção';

        if (winRate > 58 && lucro > 0) {
            cor = 'verde';
            texto = 'Análise: Mesa Boa';
        } else if (winRate < 48 || (bancaHabilitada && lucro < -(bancaInicial * 0.15))) {
            cor = 'vermelha';
            texto = 'Análise: Mesa Ruim';
        }

        medidorMesaBarra.style.width = winRate.toFixed(0) + '%';
        medidorMesaBarra.className = `${cor}`;
        medidorMesaTexto.textContent = `${texto} | Assertividade Geral: ${winRate.toFixed(0)}%`;
    };

    // --- 5. FUNÇÕES DE SETUP E INICIALIZAÇÃO ---
    const verificarMetasDeBanca = () => {
        const lucro = saldoAtual - bancaInicial;

        if (metaStopGain > 0 && !stopGainAlertado && lucro >= metaStopGain) {
            showCustomAlert(`🎯 META ATINGIDA! Você alcançou sua meta de lucro de R$ ${metaStopGain.toFixed(2)}.`);
            stopGainAlertado = true;
        }

        if (metaStopLoss > 0 && !stopLossAlertado && lucro <= -metaStopLoss) {
            showCustomAlert(`🛑 LIMITE ATINGIDO! Você alcançou seu limite de perda de R$ ${metaStopLoss.toFixed(2)}.`);
            stopLossAlertado = true;
        }
    };
    const atualizarEstadoFixar = () => {
        if (fixarHistoricoCheck.checked) {
            cardHistorico.classList.add('is-sticky');
        } else {
            cardHistorico.classList.remove('is-sticky');
        }
    };
    const salvarPreferenciaFixar = () => {
        localStorage.setItem('historicoFixo', fixarHistoricoCheck.checked);
    };
    const carregarPreferenciaFixar = () => {
        const preferenciaSalva = localStorage.getItem('historicoFixo') !== 'false';
        fixarHistoricoCheck.checked = preferenciaSalva;
        atualizarEstadoFixar();
    };
    const salvarEstadoBancaHabilitada = () => {
        localStorage.setItem('rouletteBancaHabilitada', JSON.stringify(bancaHabilitada));
    };
    const carregarEstadoBancaHabilitada = () => {
        const estadoSalvo = localStorage.getItem('rouletteBancaHabilitada');
        bancaHabilitada = estadoSalvo !== null ? JSON.parse(estadoSalvo) : true;
    };
    const atualizarVisibilidadeBanca = () => {
        if (bancaHabilitada) {
            bancaBtn.classList.remove('hidden');
            bancaResumoBar.classList.remove('hidden');
        } else {
            bancaBtn.classList.add('hidden');
            bancaResumoBar.classList.add('hidden');
        }
        habilitarBancaCheck.checked = bancaHabilitada;
    };
    const saveStrategyOrder = () => localStorage.setItem('rouletteStrategyOrder', JSON.stringify(strategyOrder));
    const loadStrategyOrder = () => {
        const savedOrder = localStorage.getItem('rouletteStrategyOrder');
        const allStrategyIds = new Set([...ESTRATEGIAS_PADRAO, ...customStrategies].map(s => s.id));
        if (savedOrder) {
            let loadedOrder = JSON.parse(savedOrder);
            loadedOrder = loadedOrder.filter(id => allStrategyIds.has(id));
            const loadedOrderSet = new Set(loadedOrder);
            for (const id of allStrategyIds) {
                if (!loadedOrderSet.has(id)) loadedOrder.push(id);
            }
            strategyOrder = loadedOrder;
        } else {
            strategyOrder = Array.from(allStrategyIds);
        }
        saveStrategyOrder();
    };
    const saveBankrollHistory = () => localStorage.setItem('rouletteBankrollHistory', JSON.stringify(historicoBanca));
    const loadBankrollHistory = () => {
        const saved = localStorage.getItem('rouletteBankrollHistory');
        if (saved) historicoBanca = JSON.parse(saved);
    };
    const logTransaction = (type, amount, description) => {
        if (isNaN(amount) || amount === 0) return;
        historicoBanca.push({ id: Date.now() + Math.random(), timestamp: Date.now(), type, amount, description });
        saveBankrollHistory();
        recalculateBalanceAndRender();
    };
    const recalculateBalanceAndRender = () => {
        let totalEntradas = 0,
            totalSaidas = 0;
        historicoBanca.forEach(txn => {
            if (txn.type === 'entrada') totalEntradas += txn.amount;
            else if (txn.type === 'saida') totalSaidas += txn.amount;
        });
        saldoAtual = bancaInicial + totalEntradas - totalSaidas;
        updateBalanceSummary();
        renderBankrollHistory();
        verificarMetasDeBanca(); // Verifica as metas sempre que o saldo é recalculado
    };
    const salvarEstrategiasHabilitadas = () => localStorage.setItem('rouletteEstrategiasHabilitadas', JSON.stringify(estrategiasHabilitadas));
    const carregarEstrategiasHabilitadas = () => {
        const configSalva = localStorage.getItem('rouletteEstrategiasHabilitadas');
        const tempConfig = configSalva ? JSON.parse(configSalva) : {};
        getAllStrategies().forEach(estrat => {
            estrategiasHabilitadas[estrat.id] = tempConfig[estrat.id] !== false;
        });
    };
    const isEstrategiaHabilitada = (id) => estrategiasHabilitadas[id] !== false;
    const saveBankrollState = () => localStorage.setItem('rouletteBankrollState', JSON.stringify({ bancaInicial, valorUnidade }));
    const loadBankrollState = () => {
        const state = localStorage.getItem('rouletteBankrollState');
        if (state) {
            const savedState = JSON.parse(state);
            bancaInicial = savedState.bancaInicial || 1000;
            valorUnidade = savedState.valorUnidade || 1;
        }
    };
    const saveCustomStrategies = async () => {
        try {
            await db.collection("configs").doc("customStrategies").set({ list: customStrategies });
        } catch (error) {
            console.error("Erro ao salvar estratégias na nuvem: ", error);
        }
    };
    const loadCustomStrategies = async () => {
        try {
            const docRef = db.collection("configs").doc("customStrategies");
            const docSnap = await docRef.get();
            if (docSnap.exists && docSnap.data().list) {
                customStrategies = docSnap.data().list;
            } else {
                customStrategies = [];
            }
        } catch (error) {
            console.error("Erro ao carregar estratégias da nuvem: ", error);
            customStrategies = [];
        }
    };
    const salvarConfigAvisos = () => localStorage.setItem('rouletteAvisoConfig', JSON.stringify(avisoConfig));
    const carregarConfigAvisos = () => {
        const configSalva = localStorage.getItem('rouletteAvisoConfig');
        const tempConfig = configSalva ? JSON.parse(configSalva) : {};
        getAllStrategies().forEach(estrat => {
            avisoConfig[estrat.id] = tempConfig[estrat.id] !== false;
        });
    };
    const salvarTimingConfig = () => localStorage.setItem('rouletteTimingConfig', JSON.stringify(strategyTimingConfig));
    const carregarTimingConfig = () => {
        const configSalva = localStorage.getItem('rouletteTimingConfig');
        const tempConfig = configSalva ? JSON.parse(configSalva) : {};
        getAllStrategies().forEach(estrat => {
            if (!tempConfig[estrat.id]) strategyTimingConfig[estrat.id] = 1;
            else strategyTimingConfig[estrat.id] = tempConfig[estrat.id];
        });
    };
    const salvarNomesCustomizados = async () => {
        try {
            await customNamesDocRef.set(strategyCustomNames);
        } catch (error) {
            console.error("Erro ao salvar nomes no Firestore: ", error);
        }
    };
    const carregarNomesCustomizados = async () => {
        try {
            const docSnap = await customNamesDocRef.get();
            if (docSnap.exists) strategyCustomNames = docSnap.data();
            else strategyCustomNames = {};
        } catch (error) {
            console.error("Erro ao carregar nomes do Firestore: ", error);
            strategyCustomNames = {};
        }
    };
    const resetarTodosOsEstados = () => {
        const estadoInicial = { vitorias: 0, derrotas: 0, vitoriasSessao: 0, derrotasSessao: 0, ultimoGatilhoNoGiro: -100 };
        getAllStrategies().forEach(estrat => {
            if (estrat.type !== 'automatica') estadoEstrategias[estrat.id] = { ...estadoInicial };
        });
        estadoInvictorArrojada = { ...estadoInicial };
        estadoSequenciaCor = { ...estadoInicial };
    };
    const iniciarNovaSessao = async () => {
        const userConfirmed = await showCustomConfirm("Tem certeza que deseja zerar a contagem da sessão atual?");
        if (!userConfirmed) return;

        Object.values(estadoEstrategias).forEach(estado => { estado.vitoriasSessao = 0; estado.derrotasSessao = 0; });
        estadoInvictorArrojada.vitoriasSessao = 0;
        estadoInvictorArrojada.derrotasSessao = 0;
        estadoSequenciaCor.vitoriasSessao = 0;
        estadoSequenciaCor.derrotasSessao = 0;
        logJogadas = [];
        apostasAtivas = [];
        renderizarTudo();
        showCustomAlert("Sessão manual zerada.");
    };
    const limparTudo = async () => {
        const userConfirmed = await showCustomConfirm("Isso limpará todo o histórico e estatísticas. Deseja continuar?");
        if (!userConfirmed) return;

        historicoNumeros = [];
        apostasAtivas = [];
        apostasAutomaticas = [];
        logJogadas = [];
        resetarTodosOsEstados();
        inicializarTimingPerformance();
        renderizarTudo();
    };
    const getStrategyName = (id) => strategyCustomNames[id] || getAllStrategies().find(e => e.id === id)?.titulo || id;
    const aplicarPrejuizoManual = () => {
        const valorPrejuizo = parseFloat(prejuizoManualInput.value);
        if (isNaN(valorPrejuizo) || valorPrejuizo <= 0) {
            showCustomAlert("Por favor, insira um valor de prejuízo válido e positivo.");
            return;
        }
        logTransaction('saida', valorPrejuizo, 'Prejuízo Manual');
        showCustomAlert(`Prejuízo de R$ ${valorPrejuizo.toFixed(2)} foi aplicado ao saldo.`);
        prejuizoManualInput.value = '';
    };
    const updateBalanceSummary = () => {
        const lucro = saldoAtual - bancaInicial;
        const lucroFormatado = `R$ ${lucro.toFixed(2)}`;
        saldoAtualDisplay.textContent = `R$ ${saldoAtual.toFixed(2)}`;
        lucroPrejuizoDisplay.textContent = lucroFormatado;
        saldoAtualResumo.textContent = `R$ ${saldoAtual.toFixed(2)}`;
        lucroPrejuizoResumo.textContent = lucroFormatado;
        [lucroPrejuizoDisplay, lucroPrejuizoResumo].forEach(el => {
            el.classList.remove('positivo', 'negativo');
            if (lucro > 0) el.classList.add('positivo');
            else if (lucro < 0) el.classList.add('negativo');
        });
    };

    const salvarPreferenciaHeatmap = () => {
        localStorage.setItem('rouletteHeatmapHabilitado', JSON.stringify(heatmapHabilitado));
    };
  const carregarPreferenciaHeatmap = () => {
        const estadoSalvo = localStorage.getItem('rouletteHeatmapHabilitado');
        heatmapHabilitado = estadoSalvo !== null ? JSON.parse(estadoSalvo) : true;
        if (habilitarHeatmapCheck) {
            habilitarHeatmapCheck.checked = heatmapHabilitado;
        }
        
        // A lógica de visibilidade agora controla os dois elementos
        if (heatmapLegenda) {
            heatmapLegenda.classList.toggle('hidden', !heatmapHabilitado);
        }
        if (cardQuenteFrio) {
            cardQuenteFrio.classList.toggle('hidden', !heatmapHabilitado);
        }
    };

    // --- 6. FUNÇÕES DE RENDERIZAÇÃO ---
    const renderizarControlesHabilitarEstrategias = () => {
        if (!habilitarEstrategiasContainer) return;
        habilitarEstrategiasContainer.innerHTML = '';
        getAllStrategies().forEach(estrat => {
            const item = document.createElement('div');
            item.className = 'aviso-config-item sortable-item';
            item.setAttribute('draggable', true);
            item.dataset.strategyId = estrat.id;
            const handle = document.createElement('span');
            handle.className = 'drag-handle';
            handle.innerHTML = '&#x2630;';
            handle.title = 'Arraste para reordenar';
            const contentWrapper = document.createElement('div');
            contentWrapper.className = 'strategy-toggle-content';
            const label = document.createElement('label');
            label.setAttribute('for', `habilitar-check-${estrat.id}`);
            label.textContent = getStrategyName(estrat.id);
            const check = document.createElement('input');
            check.type = 'checkbox';
            check.id = `habilitar-check-${estrat.id}`;
            check.checked = isEstrategiaHabilitada(estrat.id);
            check.addEventListener('change', () => {
                estrategiasHabilitadas[estrat.id] = check.checked;
                salvarEstrategiasHabilitadas();
                renderizarTudo();
            });
            contentWrapper.appendChild(label);
            contentWrapper.appendChild(check);
            item.appendChild(handle);
            item.appendChild(contentWrapper);
            habilitarEstrategiasContainer.appendChild(item);
        });
        addDragAndDropListeners();
    };
    const renderBankroll = () => {
        bancaInicialInput.value = bancaInicial;
        unidadesEmJogoDisplay.textContent = unidadesEmJogo;
        updateBalanceSummary();
        chipSelectorContainer.querySelectorAll('.chip-btn').forEach(btn => {
            btn.classList.remove('active');
            if (parseFloat(btn.dataset.value) === valorUnidade) btn.classList.add('active');
        });
    };
    const renderBankrollHistory = () => {
        if (!bancaHistoricoContainer) return;
        bancaHistoricoContainer.innerHTML = (historicoBanca.length === 0) ? "<p>Nenhuma transação registrada ainda.</p>" : "";
        if (historicoBanca.length === 0) return;

        historicoBanca.slice().reverse().forEach(txn => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'banca-historico-item';
            itemDiv.dataset.id = txn.id;
            if (txn.id === editingTxnId) {
                itemDiv.innerHTML = `<div class="item-info edit-form"><input type="text" class="edit-description-input" value="${txn.description}"></div><div class="item-amount edit-form"><input type="number" class="edit-amount-input" value="${txn.amount.toFixed(2)}" step="0.01"></div><div class="item-actions"><button class="save-txn-btn" title="Salvar">✔️</button><button class="cancel-txn-btn" title="Cancelar">❌</button></div>`;
            } else {
                const formattedDate = new Date(txn.timestamp).toLocaleString('pt-BR');
                const amountPrefix = txn.type === 'entrada' ? '+' : '-';
                itemDiv.innerHTML = `<div class="item-info"><span class="item-description">${txn.description}</span><span class="item-timestamp">${formattedDate}</span></div><div class="item-amount ${txn.type}">${amountPrefix} R$ ${txn.amount.toFixed(2)}</div><div class="item-actions"><button class="edit-txn-btn" title="Editar">✏️</button><button class="delete-txn-btn" title="Excluir">🗑️</button></div>`;
            }
            bancaHistoricoContainer.appendChild(itemDiv);
        });
    };
    const renderizarControlesEstrategias = () => {
        if (!estrategiasContainer) return;
        estrategiasContainer.innerHTML = '';
        getAllStrategies().filter(e => (!e.type || e.type === 'custom') && e.gatilhos).forEach(estrat => {
            const currentTiming = strategyTimingConfig[estrat.id] || 1;
            const currentName = getStrategyName(estrat.id);
            const card = document.createElement('div');
            card.className = 'estrategia-card';
            let cardHTML = `<div class="strategy-title-header"><h4 data-strategy-id-title="${estrat.id}">${currentName}</h4><button class="edit-name-btn" data-strategy-id="${estrat.id}" title="Editar nome">✏️</button></div><div class="strategy-options-group">`;
            for (let i = 1; i <= 4; i++) {
                const delay = i - 1;
                cardHTML += `<div><input type="radio" id="timing-${estrat.id}-${i}" name="timing-${estrat.id}" value="${i}" ${currentTiming === i ? 'checked' : ''}><label for="timing-${estrat.id}-${i}">${delay === 0 ? 'Avisar na mesma rodada (padrão)' : `Avisar com ${delay} rodada(s) de atraso`}</label></div>`;
            }
            card.innerHTML = cardHTML + `</div>`;
            estrategiasContainer.appendChild(card);
        });
        estrategiasContainer.querySelectorAll('.strategy-options-group input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                strategyTimingConfig[e.target.name.replace('timing-', '')] = parseInt(e.target.value, 10);
                salvarTimingConfig();
            });
        });
    };
    const criarBotoes = () => {
        if (!tabelaRoletaContainer) return;
        const criarBotaoHTML = num => `<button class="botao-numero ${numerosVermelhos.includes(num) ? 'red' : numerosPretos.includes(num) ? 'black' : 'green'}" data-numero="${num}">${num}</button>`;
        tabelaRoletaContainer.innerHTML = `<div class="roleta-layout"><div class="zero-container">${criarBotaoHTML(0)}</div><div class="numeros-grid">${[...coluna3, ...coluna2, ...coluna1].map(criarBotaoHTML).join('')}</div></div>`;
        tabelaRoletaContainer.querySelectorAll('.botao-numero').forEach(botao => {
            botao.addEventListener('click', e => adicionarNumero(parseInt(e.currentTarget.dataset.numero, 10)));
        });
    };
    const setupTabs = () => {
        const tabNav = document.querySelector('.tab-nav');
        if (!tabNav) return;
        tabNav.addEventListener('click', (e) => {
            const targetButton = e.target.closest('li[data-tab]');
            if (!targetButton) return;
            tabNav.querySelectorAll('li').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
            targetButton.classList.add('active');
            const activePane = document.getElementById(targetButton.dataset.tab);
            if (activePane) activePane.classList.add('active');
        });
    };
    const renderizarHistorico = () => {
        if (!historicoDiv) return;
        historicoDiv.innerHTML = `<div class="historico-contador"><span>Total</span>${historicoNumeros.length}</div>`;
        historicoNumeros.slice().reverse().forEach(num => {
            const bola = document.createElement("div");
            bola.className = `numero-bola ${numerosVermelhos.includes(num) ? 'red' : numerosPretos.includes(num) ? 'black' : 'green'}`;
            bola.textContent = num;
            historicoDiv.appendChild(bola);
        });
    };
    const renderizarRelatorio = (container, tipo) => {
        if (!container) return;
        const performanceData = getAllStrategies().filter(e => isEstrategiaHabilitada(e.id)).map(estrat => {
            let estado;
            switch (estrat.id) {
                case 'invictor-arrojada':
                    estado = estadoInvictorArrojada;
                    break;
                case 'sequencia-cor':
                    estado = estadoSequenciaCor;
                    break;
                default:
                    estado = estadoEstrategias[estrat.id];
                    break;
            }
            if (!estado) return null;
            const vitorias = tipo === 'sessao' ? estado.vitoriasSessao : estado.vitorias;
            const derrotas = tipo === 'sessao' ? estado.derrotasSessao : estado.derrotas;
            const total = vitorias + derrotas;
            return { nome: getStrategyName(estrat.id), vitorias, derrotas, aproveitamento: total > 0 ? (vitorias / total) * 100 : 0 };
        }).filter(Boolean).sort((a, b) => b.aproveitamento - a.aproveitamento);
        if (performanceData.length === 0) {
            container.innerHTML = '<p>Nenhuma estratégia habilitada para exibir o relatório.</p>';
            return;
        }
        let tableHTML = `<table class="relatorio-tabela"><thead><tr><th>Estratégia</th><th>V</th><th>D</th><th>%</th></tr></thead><tbody>`;
        performanceData.forEach(data => {
            tableHTML += `<tr><td>${data.nome}</td><td class="vitorias">${data.vitorias}</td><td class="derrotas">${data.derrotas}</td><td>${data.aproveitamento.toFixed(0)}%</td></tr>`;
        });
        container.innerHTML = tableHTML + '</tbody></table>';
    };
    const renderizarRelatorios = () => {
        renderizarRelatorio(relatorioTotalContainer, 'total');
        renderizarRelatorio(relatorioSessaoContainer, 'sessao');
    };
    const renderizarLogJogadas = (container = logJogadasContainer) => {
        if (!container) return;
        if (logJogadas.length === 0) {
            container.innerHTML = '<p>Nenhuma aposta foi realizada ainda.</p>';
            return;
        }
        let html = '<table class="log-tabela"><thead><tr><th class="giro-numero">Giro</th><th>Estratégia</th><th>Alvos da Aposta</th><th class="giro-numero">Resultado no Giro</th><th>Status</th></tr></thead><tbody>';
        logJogadas.slice().reverse().forEach(log => {
            const statusClass = log.status.startsWith('Vitória') ? 'status-vitoria' : (log.status.startsWith('Derrota') ? 'status-derrota' : 'status-ativa');
            const numeroSorteado = log.numeroSorteado !== '-' ? ` (Nº ${log.numeroSorteado})` : '';
            html += `<tr><td class="giro-numero">${log.giroAposta}</td><td>${log.estrategiaTitulo}</td><td>${log.alvosTexto}</td><td class="giro-numero">${log.giroResultado}</td><td class="${statusClass}">${log.status}${numeroSorteado}</td></tr>`;
        });
        container.innerHTML = html + '</tbody></table>';
    };
    const excluirEstrategia = async (id, isCustom) => {
        const enteredPassword = prompt(`Digite a senha para excluir a estratégia "${getStrategyName(id)}":`);
        if (enteredPassword === null) return;

        if (enteredPassword === DELETION_PASSWORD) {
            if (isCustom) {
                customStrategies = customStrategies.filter(s => s.id !== id);
                delete estrategiasHabilitadas[id];
                strategyOrder = strategyOrder.filter(strategyId => strategyId !== id);
                
                await saveCustomStrategies();
                salvarEstrategiasHabilitadas();
                saveStrategyOrder();
            } else {
                estrategiasHabilitadas[id] = false;
                salvarEstrategiasHabilitadas();
            }
            
            renderizarTudo();
            renderizarGerenciadorEstrategias();
            
            showCustomAlert("Estratégia removida/desabilitada com sucesso!");
        } else {
            showCustomAlert("Senha incorreta. A exclusão foi cancelada.");
        }
    };
    const renderizarGerenciadorEstrategias = () => {
        if (!listaTodasEstrategiasContainer) return;
        listaTodasEstrategiasContainer.innerHTML = '';
        
        getAllStrategies().forEach(estrat => {
            const isCustom = customStrategies.some(c => c.id === estrat.id);

            if (!isCustom && !isEstrategiaHabilitada(estrat.id)) {
                return; 
            }

            const tipo = isCustom ? 'Personalizada' : 'Padrão';
            const itemDiv = document.createElement('div');
            itemDiv.className = 'item-gerenciador';
            let buttonsHTML = '';
            if (isCustom) {
                buttonsHTML += `<button class="editar-estrategia-btn" data-id="${estrat.id}">Editar</button>`;
            }
            buttonsHTML += `<button class="excluir-estrategia-btn" data-id="${estrat.id}" data-custom="${isCustom}">Excluir</button>`;
            itemDiv.innerHTML = `<div class="info-principal"><span class="info-nome">${getStrategyName(estrat.id)}</span><span class="info-tipo">${tipo}</span></div><div class="acoes-item">${buttonsHTML}</div>`;
            listaTodasEstrategiasContainer.appendChild(itemDiv);
        });
    };

 const renderizarHeatmap = () => {
        const botoes = tabelaRoletaContainer.querySelectorAll('.botao-numero');
        
        // Limpa classes antigas de todos os botões
        botoes.forEach(btn => {
            btn.classList.remove('hot-1', 'hot-2', 'hot-3', 'cold-1', 'cold-2', 'cold-3');
        });

        if (!heatmapHabilitado || historicoNumeros.length < 20) {
            return;
        }

        const LIMITE_HISTORICO_QUENTE = 100;
        const historicoRecente = historicoNumeros.slice(-LIMITE_HISTORICO_QUENTE);
        const contagem = {};
        
        historicoRecente.forEach(num => {
            contagem[num] = (contagem[num] || 0) + 1;
        });

        const maxFrequencia = Math.max(...Object.values(contagem), 0);

        for (let i = 0; i <= 36; i++) {
            const botao = tabelaRoletaContainer.querySelector(`.botao-numero[data-numero="${i}"]`);
            if (!botao) continue;

            const atraso = historicoNumeros.length - (historicoNumeros.lastIndexOf(i) + 1);
            const freq = contagem[i] || 0;

            if (atraso >= 111) {
                botao.classList.add('cold-3');
            } else if (atraso >= 74) {
                botao.classList.add('cold-2');
            } else if (atraso >= 45) {
                botao.classList.add('cold-1');
            }
            else if (maxFrequencia > 2 && freq > 1) {
                if (freq >= maxFrequencia * 0.8) {
                    botao.classList.add('hot-3');
                } else if (freq >= maxFrequencia * 0.5) {
                    botao.classList.add('hot-2');
                } else {
                    botao.classList.add('hot-1');
                }
            }
        }
    };

    const renderizarControlesAvisos = () => {
        avisosEstrategiasContainer.innerHTML = '';
        getAllStrategies().forEach(estrat => {
            const item = document.createElement('div');
            item.className = 'aviso-config-item';
            const contentWrapper = document.createElement('div');
            contentWrapper.className = 'strategy-toggle-content';
            const label = document.createElement('label');
            label.setAttribute('for', `aviso-check-${estrat.id}`);
            label.textContent = getStrategyName(estrat.id);
            label.style.cursor = 'pointer';
            const check = document.createElement('input');
            check.type = 'checkbox';
            check.id = `aviso-check-${estrat.id}`;
            check.checked = avisoConfig[estrat.id] !== false;
            check.addEventListener('change', (e) => {
                avisoConfig[estrat.id] = e.target.checked;
                salvarConfigAvisos();
            });
            contentWrapper.appendChild(label);
            contentWrapper.appendChild(check);
            item.appendChild(contentWrapper);
            avisosEstrategiasContainer.appendChild(item);
        });
    };

    // --- 7. FUNÇÕES DE ANÁLISE E ESTRATÉGIAS ---
    const getDuzia = (numero) => {
        if (numero === 0) return 0;
        if (numero <= 12) return 1;
        if (numero <= 24) return 2;
        return 3;
    };
    const getSetor = (numero) => {
        if (SETOR_VOISINS.includes(numero)) return 'Vizinhos do Zero';
        if (SETOR_TIERS.includes(numero)) return 'Terço do Cilindro';
        if (SETOR_ORPHELINS.includes(numero)) return 'Órfãos';
        return null;
    };
    const inicializarTimingPerformance = () => {
        timingPerformanceData = {};
        getAllStrategies().forEach(estrat => {
            if (isEstrategiaHabilitada(estrat.id) && (!estrat.type || estrat.type === 'custom') && estrat.gatilhos) {
                timingPerformanceData[estrat.id] = { 1: { v: 0, d: 0 }, 2: { v: 0, d: 0 }, 3: { v: 0, d: 0 }, 4: { v: 0, d: 0 } };
            }
        });
    };
    const atualizarPerformanceTiming = (numeroSorteado) => {
        const giroAtual = historicoNumeros.length;
        for (const id in timingPerformanceData) {
            const estrat = getAllStrategies().find(e => e.id === id);
            if (!estrat) continue;
            const alvosFinais = new Set(getAlvosComVizinhos(estrat.alvos, estrat.vizinhos));
            for (let timing = 1; timing <= 4; timing++) {
                const delay = timing - 1;
                const indiceGatilho = giroAtual - 1 - delay;
                if (indiceGatilho > 0 && estrat.gatilhos.includes(historicoNumeros[indiceGatilho])) {
                    const numeroResultado1 = numeroSorteado;
                    const numeroResultado2 = historicoNumeros[giroAtual];
                    if (alvosFinais.has(numeroResultado1) || (numeroResultado2 !== undefined && alvosFinais.has(numeroResultado2))) {
                        timingPerformanceData[id][timing].v++;
                    } else {
                        timingPerformanceData[id][timing].d++;
                    }
                }
            }
        }
    };
    const sugerirMelhorTimingComIA = async () => {
        if (Object.keys(timingPerformanceData).length === 0 || historicoNumeros.length < 50) {
            showCustomAlert("É necessário um histórico de pelo menos 50 números para uma análise confiável.");
            return;
        }
        const userConfirmed = await showCustomConfirm("Deseja usar a análise contínua para sugerir os melhores timings agora? As sugestões atuais serão substituídas.");
        if (!userConfirmed) return;
        let sugestoesAplicadas = 0;
        for (const id in timingPerformanceData) {
            let melhorTiming = 1, melhorTaxa = -1, melhoresVitorias = -1, totalJogadas = 0;
            for (let timing = 1; timing <= 4; timing++) {
                const { v, d } = timingPerformanceData[id][timing];
                totalJogadas += (v + d);
                if (v + d > 5) {
                    const taxa = v / (v + d);
                    if (taxa > melhorTaxa || (taxa === melhorTaxa && v > melhoresVitorias)) {
                        melhorTaxa = taxa;
                        melhoresVitorias = v;
                        melhorTiming = timing;
                    }
                }
            }
            if (totalJogadas > 5) {
                strategyTimingConfig[id] = melhorTiming;
                sugestoesAplicadas++;
            }
        }
        salvarTimingConfig();
        renderizarControlesEstrategias();
        Object.keys(strategyTimingConfig).forEach(id => {
            const label = document.querySelector(`label[for="timing-${id}-${strategyTimingConfig[id]}"]`);
            if (label && !label.textContent.includes('⭐')) {
                label.innerHTML += ' <span style="color:var(--accent-yellow)" title="Sugestão baseada na análise contínua">⭐</span>';
            }
        });
        showCustomAlert(`${sugestoesAplicadas} sugestões de timing foram atualizadas com base na análise contínua!`);
    };
    const getAlvosComVizinhos = (numerosBase, distancia) => {
        if (distancia === 0) return numerosBase;
        const alvosFinais = new Set(numerosBase);
        numerosBase.forEach(num => {
            const wheelIndex = ROULETTE_WHEEL.indexOf(num);
            if (wheelIndex !== -1) {
                for (let i = 1; i <= distancia; i++) {
                    alvosFinais.add(ROULETTE_WHEEL[(wheelIndex + i + 37) % 37]);
                    alvosFinais.add(ROULETTE_WHEEL[(wheelIndex - i + 37) % 37]);
                }
            }
        });
        return Array.from(alvosFinais);
    };
    const getProtectionText = (mainTargets) => {
        if (historicoNumeros.length === 0) return '';
        
        const ultimoNumero = historicoNumeros.at(-1);
        const wheelIndex = ROULETTE_WHEEL.indexOf(ultimoNumero);
        
        if (wheelIndex !== -1) {
            const vizinhoAnterior = ROULETTE_WHEEL[(wheelIndex - 1 + 37) % 37];
            const vizinhoPosterior = ROULETTE_WHEEL[(wheelIndex + 1) % 37];
            
            const mainTargetsSet = new Set(mainTargets);
            let protecaoFinal = [];

            if (!mainTargetsSet.has(vizinhoAnterior)) {
                protecaoFinal.push(vizinhoAnterior);
            }
            if (!mainTargetsSet.has(vizinhoPosterior)) {
                protecaoFinal.push(vizinhoPosterior);
            }

            if (protecaoFinal.length > 0) {
               return `<br>🛡️ Proteção (vizinhos de ${ultimoNumero}): <strong>${protecaoFinal.join(', ')}</strong>`;
            }
        }
        return '';
    };
    const calcularAtraso = (arr) => {
        let atraso = 0;
        for (let i = historicoNumeros.length - 1; i >= 0; i--) {
            if (arr.includes(historicoNumeros[i])) return atraso;
            atraso++;
        }
        return historicoNumeros.length;
    };
    const analisarInvictor = () => {
        if (!invictorDisplay) return;
        if (!isEstrategiaHabilitada('invictor-conservadora')) return invictorDisplay.innerHTML = "<p>Estratégia desabilitada.</p>";
        invictorDisplay.innerHTML = "<p>Aguardando gatilho...</p>";
        if (historicoNumeros.length < 2) return;
        const ultimoNumero = historicoNumeros.at(-1);
        const penultimoNumero = historicoNumeros.at(-2);
        if (!INVICTOR_QUEBRANTES.includes(penultimoNumero) || !INVICTOR_MULTIPLOS.includes(ultimoNumero)) return;
        const idadesDosQuebrantes = INVICTOR_QUEBRANTES.map(q => ({ quebrante: q, idade: historicoNumeros.length - 1 - historicoNumeros.lastIndexOf(q) }));
        const quebranteMaisAntigo = idadesDosQuebrantes.sort((a, b) => b.idade - a.idade)[0]?.quebrante;
        const grupoDeMultiplos = INVICTOR_MULTIPLOS_GRUPO_A.includes(ultimoNumero) ? INVICTOR_MULTIPLOS_GRUPO_A : INVICTOR_MULTIPLOS_GRUPO_B;
        const sugestao = new Set([INVICTOR_PROTETIVO, penultimoNumero, quebranteMaisAntigo, ...grupoDeMultiplos]);
        const renderizarLinha = (titulo, numero) => {
            if (numero === null || numero === undefined) return "";
            const cor = numerosVermelhos.includes(numero) ? 'red' : (numerosPretos.includes(numero) ? 'black' : 'green');
            return `<div class="invictor-sugestao"><span class="invictor-sugestao-titulo">${titulo}:</span><div class="numero-bola ${cor}">${numero}</div></div>`;
        };
        invictorDisplay.innerHTML = `<p style="text-align:center; color: var(--accent-green);"><strong>Gatilho Ativado!</strong></p>` +
            renderizarLinha("Último Quebrante", penultimoNumero) + renderizarLinha("Quebrante Antigo", quebranteMaisAntigo) +
            `<div class="invictor-sugestao"><span class="invictor-sugestao-titulo">Aposta:</span><span><strong>${Array.from(sugestao).filter(n => n !== null).sort((a, b) => a - b).join(", ")}</strong></span></div>`;
    };
    const analisarArrojada = () => {
        if (!arrojadaDisplay) return;
        if (!isEstrategiaHabilitada('invictor-arrojada')) return arrojadaDisplay.innerHTML = "<p>Estratégia desabilitada.</p>";
        let contadorMultiplos = 0;
        for (let i = historicoNumeros.length - 1; i >= 0; i--) {
            if (!INVICTOR_MULTIPLOS.includes(historicoNumeros[i])) break;
            contadorMultiplos++;
        }
        if ((contadorMultiplos >= 2 && contadorMultiplos <= 3) || contadorMultiplos >= 5) {
            const tipo = contadorMultiplos >= 5 ? 'Alongado' : 'Curto';
            arrojadaDisplay.innerHTML = `<div class="arrojada-alerta ${tipo.toLowerCase()} gatilho-ativo">Padrão ${tipo} Detectado (${contadorMultiplos}x)<span>Apostar nos Quebrantes: <strong>1, 5, 7</strong></span></div>`;
        } else {
            arrojadaDisplay.innerHTML = contadorMultiplos === 0 ? "<p>Analisando padrão...</p>" : `<p>Sequência de ${contadorMultiplos} múltiplos. Aguardando gatilho...</p>`;
        }
    };
    const analisarAtrasos = () => {
        if (!atrasosDisplay || historicoNumeros.length === 0) return atrasosDisplay.innerHTML = '<p>Aguardando números...</p>';
        const atrasos = { '1ª Dúzia': duzia1, '2ª Dúzia': duzia2, '3ª Dúzia': duzia3, 'Coluna 1': coluna1, 'Coluna 2': coluna2, 'Coluna 3': coluna3 };
        atrasosDisplay.innerHTML = Object.entries(atrasos).map(([nome, valor]) => `<div class="atraso-item"><strong>${nome}</strong> <span>${calcularAtraso(valor)}</span></div>`).join('');
    };
    const analisarAtrasosDeSetores = () => {
        if (!setoresDisplay || historicoNumeros.length === 0) return setoresDisplay.innerHTML = '<p>Aguardando números...</p>';
        const atrasos = { 'Vizinhos do Zero': SETOR_VOISINS, 'Terço do Cilindro': SETOR_TIERS, 'Órfãos': SETOR_ORPHELINS };
        setoresDisplay.innerHTML = Object.entries(atrasos).map(([nome, valor]) => `<div class="setor-item"><strong>${nome}</strong> <span>${calcularAtraso(valor)}</span></div>`).join('');
    };
    const analisarNumerosQuentes = () => {
        if (!hotNumbersDisplay) return; // Se o elemento não existe, para a execução.

        const historicoRecente = historicoNumeros.slice(-25);
        if (historicoRecente.length < 10) {
            hotNumbersDisplay.innerHTML = '<p>Aguardando...</p>';
            return;
        }
        const contagem = {};
        historicoRecente.forEach(num => contagem[num] = (contagem[num] || 0) + 1);
        const top3 = Object.entries(contagem).sort((a, b) => b[1] - a[1]).slice(0, 3);
        hotNumbersDisplay.innerHTML = (top3.length === 0) ? '<p>Sem tendência.</p>' : `<div class="sugestao-numeros">${top3.map(([numero, vezes]) => {
            const num = parseInt(numero), cor = numerosVermelhos.includes(num) ? 'red' : (numerosPretos.includes(num) ? 'black' : 'green');
            return `<div class="numero-bola ${cor}" title="${vezes} vezes">${num}</div>`;
        }).join('')}</div>`;
    };
    const analisarNumerosFrios = () => {
        if (!coldNumbersDisplay) return; // Se o elemento não existe, para a execução.

        if (historicoNumeros.length < 20) {
            coldNumbersDisplay.innerHTML = '<p>Aguardando...</p>';
            return;
        }
        const atrasos = Array.from({ length: 37 }, (_, i) => ({ numero: i, atraso: historicoNumeros.length - 1 - historicoNumeros.lastIndexOf(i) }));
        const top3 = atrasos.sort((a, b) => b.atraso - a.atraso).slice(0, 3);
        coldNumbersDisplay.innerHTML = `<div class="sugestao-numeros">${top3.map(({ numero, atraso }) => {
            const cor = numerosVermelhos.includes(numero) ? 'red' : (numerosPretos.includes(numero) ? 'black' : 'green');
            return `<div class="numero-bola ${cor}" title="Atrasado há ${atraso} giros">${numero}</div>`;
        }).join('')}</div>`;
    };
    const analisarAtrasoEstatistico = () => {
        if (!statisticalDelayDisplay) return;
        const htmlItens = getAllStrategies().filter(e => e.type === 'desvio-estatistico' && isEstrategiaHabilitada(e.id)).map(estrat => {
            if (historicoNumeros.length < 30) return `<div class="desvio-status-item"><h4>${getStrategyName(estrat.id)}</h4><div class="status-analisando">Analisando...</div><p>Aguardando dados</p></div>`;
            const aparicoes = historicoNumeros.map((num, i) => estrat.alvos.includes(num) ? i : -1).filter(i => i !== -1);
            if (aparicoes.length < 5) return `<div class="desvio-status-item"><h4>${getStrategyName(estrat.id)}</h4><div class="status-analisando">Analisando...</div><p>Aguardando dados</p></div>`;
            const delays = aparicoes.slice(1).map((pos, i) => pos - aparicoes[i] - 1);
            const media = delays.reduce((a, b) => a + b, 0) / delays.length;
            const desvioPadrao = Math.sqrt(delays.map(x => (x - media) ** 2).reduce((a, b) => a + b, 0) / delays.length);
            const atrasoAtual = historicoNumeros.length - 1 - aparicoes.at(-1);
            const gatilho = media + (estrat.gatilhoPadrao * desvioPadrao);
            const statusClass = atrasoAtual > gatilho ? 'status-entrar' : 'status-aguardar';
            const statusText = atrasoAtual > gatilho ? 'PODE ENTRAR' : 'MELHOR AGUARDAR';
            return `<div class="desvio-status-item"><h4>${getStrategyName(estrat.id)}</h4><div class="${statusClass}">${statusText}</div><p>Atraso: ${atrasoAtual} / Gatilho: ${gatilho.toFixed(1)}</p></div>`;
        });
        statisticalDelayDisplay.innerHTML = htmlItens.length > 0 ? htmlItens.join('') : "<p>Nenhuma estratégia de desvio estatístico habilitada.</p>";
    };
    const calcularEstatisticasPorNumero = () => {
        const freqContainer = document.getElementById('frequencia-container');
        const faltContainer = document.getElementById('faltantes-container');
        if (!freqContainer || !faltContainer) return;
        if (historicoNumeros.length === 0) {
            freqContainer.innerHTML = "<h3>Frequência</h3><p>Aguardando...</p>";
            faltContainer.innerHTML = "<h3>Faltantes</h3><p>Aguardando...</p>";
            return;
        }
        const contagem = Array(37).fill(0);
        historicoNumeros.forEach(num => contagem[num]++);
        const frequenciaArray = contagem.map((vezes, numero) => ({ numero, vezes })).sort((a, b) => b.vezes - a.vezes);
        const faltantes = contagem.map((vezes, numero) => (vezes === 0 ? numero : -1)).filter(n => n !== -1);
        freqContainer.innerHTML = `<h3>Frequência</h3><ul class="frequencia-lista">${frequenciaArray.map(item => `<li class="frequencia-item">Nº ${item.numero}: <span>${item.vezes}x</span></li>`).join('')}</ul>`;
        faltContainer.innerHTML = `<h3>Faltantes</h3><div class="faltantes-lista">${faltantes.length > 0 ? faltantes.map(num => `<span class="faltante-numero">${num}</span>`).join('') : '<p>Nenhum!</p>'}</div>`;
    };
    const abrirPainelDoNumero = numero => {
        const num = parseInt(numero, 10);
        if (isNaN(num)) return;
        const cor = numerosVermelhos.includes(num) ? 'red' : (numerosPretos.includes(num) ? 'black' : 'green');
        const wheelIndex = ROULETTE_WHEEL.indexOf(num);
        let statusInvictor = 'Nenhum';
        if (INVICTOR_MULTIPLOS.includes(num)) statusInvictor = 'Múltiplo';
        if (INVICTOR_QUEBRANTES.includes(num)) statusInvictor = 'Quebrante';
        if (num === 0) statusInvictor = 'Protetivo';
        modalBody.innerHTML = `
            <div class="modal-header"><div class="numero-bola ${cor}">${num}</div><h3>Análise do Nº ${num}</h3></div>
            <div class="modal-info-grid">
                <div class="modal-info-item"><span class="label">Frequência</span><span class="value">${historicoNumeros.filter(n => n === num).length}x</span></div>
                <div class="modal-info-item"><span class="label">Última Aparição</span><span class="value">${historicoNumeros.lastIndexOf(num) === -1 ? 'Nunca saiu' : `${historicoNumeros.length - 1 - historicoNumeros.lastIndexOf(num)} giros`}</span></div>
                <div class="modal-info-item"><span class="label">Paridade</span><span class="value">${num === 0 ? '-' : (num % 2 === 0 ? 'Par' : 'Ímpar')}</span></div>
                <div class="modal-info-item"><span class="label">Dúzia</span><span class="value">${num === 0 ? '-' : (num <= 12 ? '1ª' : (num <= 24 ? '2ª' : '3ª'))}</span></div>
                <div class="modal-info-item"><span class="label">Status Invictor</span><span class="value">${statusInvictor}</span></div>
                <div class="modal-info-item"><span class="label">Vizinhos na Roda</span><span class="value">${wheelIndex !== -1 ? `${ROULETTE_WHEEL.at(wheelIndex - 1)} ‹ ${num} › ${ROULETTE_WHEEL[(wheelIndex + 1) % 37]}` : 'N/A'}</span></div>
            </div>`;
        modalOverlay.classList.remove('hidden');
    };
    const processarResultadosDeApostasManuais = () => {
        const giroAtual = historicoNumeros.length;
        if (giroAtual === 0) return;

        let balanceChanged = false;
        const ultimoNumero = historicoNumeros.at(-1);

        apostasAtivas.forEach(aposta => {
            if (aposta.resolvida) return;

            const alvosVencedores = new Set(getAlvosComVizinhos(aposta.alvos, aposta.vizinhos || 0));
            const logEntry = logJogadas.find(log => log.apostaUnicaId === aposta.apostaUnicaId);
            const estado = estadoEstrategias[aposta.id];

            const resolverAposta = (resultado, statusLog, numeroSorteado) => {
                aposta.resolvida = true;
                if (logEntry) {
                    logEntry.status = statusLog;
                    logEntry.giroResultado = giroAtual;
                    logEntry.numeroSorteado = numeroSorteado;
                }
                if (estado) {
                    if (resultado === 'vitoria') {
                        estado.vitoriasSessao++;
                    } else {
                        estado.derrotasSessao++;
                    }
                }
            };

            if (aposta.tentativa === 1 && giroAtual === aposta.giroAposta + 1) {
                if (alvosVencedores.has(ultimoNumero)) {
                    if (bancaHabilitada) {
                        const payout = 36 * valorUnidade;
                        logTransaction('entrada', payout, `Vitória (1/2) - ${getStrategyName(aposta.id)}`);
                        balanceChanged = true;
                    }
                    resolverAposta('vitoria', 'Vitória (1º Giro)', ultimoNumero);
                } else {
                    aposta.tentativa = 2;
                    if (logEntry) logEntry.status = 'Ativa (2/2)';
                    
                    if (bancaHabilitada) {
                        const custoSegundaTentativa = aposta.custoInicial * 2;
                        if (saldoAtual < custoSegundaTentativa) {
                            showCustomAlert(`Saldo insuficiente para dobrar a aposta em "${getStrategyName(aposta.id)}". Aposta encerrada como derrota.`);
                            resolverAposta('derrota', 'Derrota (Saldo Insuf.)', '-');
                        } else {
                            logTransaction('saida', custoSegundaTentativa, `Aposta (2/2) - ${getStrategyName(aposta.id)}`);
                            balanceChanged = true;
                        }
                    }
                }
            }
            else if (aposta.tentativa === 2 && giroAtual === aposta.giroAposta + 2) {
                if (alvosVencedores.has(ultimoNumero)) {
                    if (bancaHabilitada) {
                        const payout = (36 * valorUnidade) * 2;
                        logTransaction('entrada', payout, `Vitória (2/2) - ${getStrategyName(aposta.id)}`);
                        balanceChanged = true;
                    }
                    resolverAposta('vitoria', 'Vitória (2º Giro)', ultimoNumero);
                } else {
                    resolverAposta('derrota', 'Derrota (2 Giros)', ultimoNumero);
                }
            }
        });

        apostasAtivas = apostasAtivas.filter(aposta => !aposta.resolvida);

        if (bancaHabilitada && balanceChanged) {
            recalculateBalanceAndRender();
        } else {
            renderBankroll();
        }
    };
    const processarResultadosDeApostasAutomaticas = () => {
        const giroAtual = historicoNumeros.length;
        apostasAutomaticas.forEach(aposta => {
            if (aposta.resolvida) return;
            const resolverAposta = (resultado) => {
                let estado = estadoEstrategias[aposta.id] || (aposta.id === 'invictor-arrojada' ? estadoInvictorArrojada : estadoSequenciaCor);
                if (!estado) return;
                if (resultado === 'vitoria') estado.vitorias++;
                else estado.derrotas++;
                aposta.resolvida = true;
            };
            if (giroAtual >= aposta.giroAposta + 1 && aposta.alvos.has(historicoNumeros.at(-1))) resolverAposta('vitoria');
            else if (giroAtual >= aposta.giroAposta + 2 && aposta.alvos.has(historicoNumeros.at(-2))) resolverAposta('vitoria');
            else if (giroAtual >= aposta.giroAposta + 2) resolverAposta('derrota');
        });
        apostasAutomaticas = apostasAutomaticas.filter(aposta => !aposta.resolvida);
    };
    const registrarApostaAutomatica = (id, alvosSet) => {
        if (!isEstrategiaHabilitada(id)) return;
        const giroAtual = historicoNumeros.length;
        if (!apostasAutomaticas.some(a => a.id === id && a.giroAposta === giroAtual)) {
            apostasAutomaticas.push({ id, giroAposta: giroAtual, alvos: alvosSet, resolvida: false });
        }
    };
    const renderizarAlertasNoModal = () => {
        if (!centralAlertasContainer) return;
        centralAlertasContainer.innerHTML = '';
    
        const alertasFiltrados = alertasSugeridosGlobal.filter(alerta => {
            if (filtroPrioridadeAtual === 'todos') return true;
            return alerta.prioridade == filtroPrioridadeAtual;
        });
    
        if (alertasFiltrados.length === 0) {
            centralAlertasContainer.innerHTML = '<p>Nenhum alerta para exibir com este filtro.</p>';
            return;
        }
    
        const alertasAgrupados = alertasFiltrados.reduce((acc, alerta) => {
            let categoria = 'Outras Sugestões';
            if (alerta.titulo.includes('Padrão') || alerta.titulo.includes('Tendência')) {
                categoria = 'Padrões e Tendências';
            } else if (alerta.titulo.includes('Gatilho')) {
                categoria = 'Gatilhos de Números';
            } else if (alerta.titulo.includes('Eco')) {
                categoria = 'Estratégias Dinâmicas';
            }
            
            if (!acc[categoria]) {
                acc[categoria] = [];
            }
            acc[categoria].push(alerta);
            return acc;
        }, {});
    
        for (const categoria in alertasAgrupados) {
            const tituloGrupo = document.createElement('h3');
            tituloGrupo.className = 'alerta-grupo-titulo';
            tituloGrupo.textContent = categoria;
            centralAlertasContainer.appendChild(tituloGrupo);
    
            alertasAgrupados[categoria].forEach(alerta => {
                const alertaDiv = document.createElement("div");
                alertaDiv.className = `alerta-item prioridade-${alerta.prioridade}`;
                let html = `<p><strong>${alerta.titulo} (${alerta.aproveitamento >= 0 ? alerta.aproveitamento.toFixed(0) + '%' : 'N/A'})</strong></p><p>${alerta.texto}</p>`;
                if (alerta.dadosAposta) {
                    html += `<div class="alerta-acoes"><button class="alerta-acao-btn apostar" data-aposta-id="${alerta.id}" data-aposta-info='${JSON.stringify(alerta.dadosAposta)}'>✔ Apostar</button><button class="alerta-acao-btn excluir">✖ Ignorar</button></div>`;
                }
                alertaDiv.innerHTML = html;
                centralAlertasContainer.appendChild(alertaDiv);
            });
        }
    };
    const gerenciarAlertas = () => {
        if (!alertasContainer) return;
        
        alertasSugeridosGlobal = [];
        const giroAtual = historicoNumeros.length;
    
        getAllStrategies().forEach(estrat => {
            if (!isEstrategiaHabilitada(estrat.id) || !avisoConfig[estrat.id]) return;
            const estado = estadoEstrategias[estrat.id] || {};
            if (apostasAtivas.some(a => a.id === estrat.id) || giroAtual - (estado.ultimoGatilhoNoGiro || -100) <= 3) return;
    
            let dadosAposta = {};
            const prioridade = estrat.prioridade || 3;
    
            if (estrat.id === 'alternancia-duzias' && giroAtual >= 4) {
                const ultimas4Duzias = historicoNumeros.slice(-4).map(n => getDuzia(n));
                const duziasUnicas = [...new Set(ultimas4Duzias)];
                if (duziasUnicas.length === 2 && ultimas4Duzias[0] === ultimas4Duzias[2] && ultimas4Duzias[1] === ultimas4Duzias[3]) {
                    const duziaFaltante = [1, 2, 3].find(d => !duziasUnicas.includes(d));
                    if (duziaFaltante) {
                        const alvos = [duzia1, duzia2, duzia3][duziaFaltante - 1];
                        const numerosDeProtecaoTexto = getProtectionText(alvos);
                        dadosAposta = { alvos: alvos, vizinhos: 0, alvosTexto: `${duziaFaltante}ª Dúzia`, texto: `Padrão de alternância detectado entre a <strong>${duziasUnicas[0]}ª e a ${duziasUnicas[1]}ª Dúzia</strong>.<br>Sugestão: Quebra do padrão na <strong>${duziaFaltante}ª Dúzia</strong>.${numerosDeProtecaoTexto}` };
                        alertasSugeridosGlobal.push({ id: estrat.id, tipo: 'sugerida', titulo: `♟️ Padrão: ${getStrategyName(estrat.id)}`, texto: dadosAposta.texto, dadosAposta, prioridade });
                    }
                }
            } else if (estrat.id === 'repeticao-setor' && giroAtual >= 3) {
                const ultimos3Setores = historicoNumeros.slice(-3).map(n => getSetor(n));
                if (ultimos3Setores.every(s => s && s === ultimos3Setores[0])) {
                    const setorRepetido = ultimos3Setores[0];
                    const alvos = { 'Vizinhos do Zero': SETOR_VOISINS, 'Terço do Cilindro': SETOR_TIERS, 'Órfãos': SETOR_ORPHELINS }[setorRepetido];
                    const numerosDeProtecaoTexto = getProtectionText(alvos);
                    dadosAposta = { alvos: alvos, vizinhos: 0, alvosTexto: setorRepetido, texto: `O setor <strong>${setorRepetido}</strong> saiu 3 vezes seguidas.<br>Sugestão: Apostar na continuação da tendência.${numerosDeProtecaoTexto}` };
                    alertasSugeridosGlobal.push({ id: estrat.id, tipo: 'sugerida', titulo: `🔥 Tendência: ${getStrategyName(estrat.id)}`, texto: dadosAposta.texto, dadosAposta, prioridade });
                }
            } else if (estrat.type === 'dinamica' && giroAtual > 0) {
                const ultimoNumero = historicoNumeros.at(-1);
                const alvosFinais = getAlvosComVizinhos([ultimoNumero], estrat.vizinhos);
                const numerosDeProtecaoTexto = getProtectionText(alvosFinais);
                dadosAposta = { alvos: alvosFinais, vizinhos: undefined, alvosTexto: `${estrat.vizinhos} viz. de ${ultimoNumero}`, texto: `O último número foi <strong>${ultimoNumero}</strong>.<br>Sugestão: <strong>${estrat.vizinhos} vizinhos de ${ultimoNumero}</strong>.<br><br>Jogar em: ${alvosFinais.sort((a,b)=>a-b).join(', ')}${numerosDeProtecaoTexto}`};
                alertasSugeridosGlobal.push({ id: estrat.id, tipo: 'sugerida', titulo: `⭐ Sugestão: ${getStrategyName(estrat.id)}`, texto: dadosAposta.texto, dadosAposta, prioridade });
                registrarApostaAutomatica(estrat.id, new Set(alvosFinais));
            } else if (estrat.gatilhos) {
                const timingOffset = strategyTimingConfig[estrat.id] || 1;
                if (giroAtual < timingOffset) return;
                const numeroVerificar = historicoNumeros[giroAtual - timingOffset];
                if (numeroVerificar !== undefined && estrat.gatilhos.includes(numeroVerificar)) {
                    const alvosFinais = new Set(getAlvosComVizinhos(estrat.alvos, estrat.vizinhos));
                    if (estrat.cobertura) estrat.cobertura.forEach(n => alvosFinais.add(n));
                    const alvosFinaisArr = Array.from(alvosFinais).sort((a, b) => a - b);
                    const numerosDeProtecaoTexto = getProtectionText(alvosFinaisArr);
                    const numeroDeUnidades = alvosFinaisArr.length;
                    const custoAposta = numeroDeUnidades * valorUnidade;
                    let infoRiscoHTML = `<span class="info-custo">Custo: <strong>${numeroDeUnidades} fichas (R$ ${custoAposta.toFixed(2)})</strong></span>`;
                    if (bancaHabilitada && saldoAtual > 0) {
                        const riscoPercentual = (custoAposta / saldoAtual) * 100;
                        let nivelRisco = 'Baixo', corRisco = 'var(--accent-green)';
                        if (riscoPercentual > 15) { nivelRisco = 'Alto'; corRisco = 'var(--accent-red)'; }
                        else if (riscoPercentual > 5) { nivelRisco = 'Médio'; corRisco = 'var(--accent-yellow)'; }
                        infoRiscoHTML += `<span class="info-risco" style="color: ${corRisco};">Risco: <strong>${riscoPercentual.toFixed(1)}% (${nivelRisco})</strong></span>`;
                    }
                    const descVizinhos = estrat.vizinhos > 0 ? `${estrat.vizinhos} vizinhos de ${estrat.alvos.join(' e ')}` : getStrategyName(estrat.id);
                    const descCobertura = estrat.cobertura ? ` + cobertura de ${estrat.cobertura.join(', ')}` : '';
                    dadosAposta = { alvos: alvosFinaisArr, vizinhos: undefined, alvosTexto: descVizinhos + descCobertura, texto: `Gatilho: Nº <strong>${numeroVerificar}</strong> (${timingOffset - 1}r atrás).<br>Sugestão: <strong>${descVizinhos + descCobertura}</strong>.<br>Jogar em: <strong>${alvosFinaisArr.join(', ')}</strong>${numerosDeProtecaoTexto}<br><br><div class="aposta-info-extra">${infoRiscoHTML}</div>` };
                    alertasSugeridosGlobal.push({ id: estrat.id, tipo: 'sugerida', titulo: `Gatilho: ${getStrategyName(estrat.id)}`, texto: dadosAposta.texto, dadosAposta, prioridade });
                    registrarApostaAutomatica(estrat.id, new Set(dadosAposta.alvos));
                }
            }
        });
    
        alertasSugeridosGlobal.forEach(alerta => {
            const estado = estadoEstrategias[alerta.id] || {};
            const vitorias = estado.vitorias || 0;
            const derrotas = estado.derrotas || 0;
            const total = vitorias + derrotas;
            alerta.aproveitamento = total > 0 ? (vitorias / total) * 100 : -1;
        });
    
        alertasSugeridosGlobal.sort((a, b) => {
            if (a.prioridade !== b.prioridade) return a.prioridade - b.prioridade;
            return b.aproveitamento - a.aproveitamento;
        });
    
        alertasContainer.innerHTML = '';
        const top3Alertas = alertasSugeridosGlobal.slice(0, 3);
        if (top3Alertas.length > 0) {
            const titulo = document.createElement('h2');
            titulo.style.color = 'var(--accent-yellow)';
            titulo.textContent = 'Top 3 Sugestões';
            alertasContainer.appendChild(titulo);
        }
        top3Alertas.forEach(alerta => {
            const alertaDiv = document.createElement("div");
            alertaDiv.className = `alerta-estrategia sugerida`;
            alertaDiv.innerHTML = `<p><strong>${alerta.titulo}</strong></p><p>${alerta.texto}</p><div class="alerta-acoes"><button class="alerta-acao-btn apostar" data-aposta-id="${alerta.id}" data-aposta-info='${JSON.stringify(alerta.dadosAposta)}'>✔ Apostar</button><button class="alerta-acao-btn excluir">✖ Ignorar</button></div>`;
            alertasContainer.appendChild(alertaDiv);
        });
    
        const outrosAlertasCount = alertasSugeridosGlobal.length - top3Alertas.length;
        if (outrosAlertasCount > 0) {
            avisoCentralAlertasBar.classList.remove('hidden');
            avisoCentralAlertasTexto.innerHTML = `🔔 <strong>+${outrosAlertasCount}</strong> outra(s) sugestão(ões) disponível(is)`;
        } else {
            avisoCentralAlertasBar.classList.add('hidden');
        }
    
        renderizarAlertasNoModal();
    };
    const openStrategyModal = (strategy = null) => {
        if (strategy) {
            modalEstrategiaTitulo.textContent = "Editar Estratégia";
            estrategiaIdInput.value = strategy.id;
            estrategiaNomeInput.value = strategy.titulo;
            estrategiaGatilhosInput.value = strategy.gatilhos.join(', ');
            estrategiaAlvosInput.value = strategy.alvos.join(', ');
            estrategiaVizinhosInput.value = strategy.vizinhos || 0;
        } else {
            modalEstrategiaTitulo.textContent = "Criar Nova Estratégia";
            estrategiaIdInput.value = '';
            estrategiaNomeInput.value = '';
            estrategiaGatilhosInput.value = '';
            estrategiaAlvosInput.value = '';
            estrategiaVizinhosInput.value = 0;
        }
        modalEstrategiaOverlay.classList.remove('hidden');
    };
    const saveCustomStrategy = async () => {
        const id = estrategiaIdInput.value;
        const nome = estrategiaNomeInput.value.trim();
        const gatilhos = estrategiaGatilhosInput.value.split(',').map(n => parseInt(n.trim())).filter(n => !isNaN(n) && n >= 0 && n <= 36);
        const alvos = estrategiaAlvosInput.value.split(',').map(n => parseInt(n.trim())).filter(n => !isNaN(n) && n >= 0 && n <= 36);
        const vizinhos = parseInt(estrategiaVizinhosInput.value, 10);
        if (!nome || gatilhos.length === 0 || alvos.length === 0) {
            showCustomAlert("Por favor, preencha o nome, gatilhos e alvos da estratégia.");
            return;
        }

        if (id) {
            const index = customStrategies.findIndex(s => s.id === id);
            if (index > -1) customStrategies[index] = { ...customStrategies[index], titulo: nome, gatilhos, alvos, vizinhos };
        } else {
            const newStrategy = { id: `custom-${Date.now()}`, titulo: nome, gatilhos, alvos, vizinhos, type: 'custom' };
            customStrategies.push(newStrategy);
            estrategiasHabilitadas[newStrategy.id] = true;
        }
        await saveCustomStrategies();
        salvarEstrategiasHabilitadas();
        loadStrategyOrder();
        resetarTodosOsEstados();
        renderizarTudo();
        renderizarGerenciadorEstrategias();
        modalEstrategiaOverlay.classList.add('hidden');
    };
    
    // --- 8. EVENT LISTENERS ---
    if (limparBtn) limparBtn.addEventListener('click', limparTudo);
    if (undoBtn) {
        undoBtn.addEventListener('click', async () => {
            if (historicoNumeros.length === 0) {
                showCustomAlert("Não há jogadas para desfazer.");
                return;
            }
            const userConfirmed = await showCustomConfirm("Tem certeza que deseja desfazer a última jogada? Esta ação reiniciará as estatísticas da sessão.");
            if (userConfirmed) {
                performUndo();
                showCustomAlert("Jogada anterior removida com sucesso!");
            }
        });
    }
    if (adicionarNumeroBtn) adicionarNumeroBtn.addEventListener('click', processarInputPrincipal);
    if (numeroInput) numeroInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') processarInputPrincipal(); });
    if (iniciarSessaoBtn) iniciarSessaoBtn.addEventListener('click', iniciarNovaSessao);
    if (analisarTimingBtn) analisarTimingBtn.addEventListener('click', sugerirMelhorTimingComIA);
    if (logo) logo.addEventListener('click', voltarParaInicio);
    if (habilitarBancaCheck) {
        habilitarBancaCheck.addEventListener('change', () => {
            bancaHabilitada = habilitarBancaCheck.checked;
            salvarEstadoBancaHabilitada();
            atualizarVisibilidadeBanca();
        });
    }
    if (stopGainInput) {
        stopGainInput.addEventListener('change', () => {
            metaStopGain = parseFloat(stopGainInput.value) || 0;
            stopGainAlertado = false; // Reseta o alerta se a meta for alterada
        });
    }
    if (stopLossInput) {
        stopLossInput.addEventListener('change', () => {
            metaStopLoss = parseFloat(stopLossInput.value) || 0;
            stopLossAlertado = false; // Reseta o alerta se a meta for alterada
        });
    }
    const filtrosContainer = document.getElementById('central-alertas-filtros');
    if (filtrosContainer) {
        filtrosContainer.addEventListener('click', (e) => {
            if (e.target.matches('.filtro-btn')) {
                filtrosContainer.querySelectorAll('.filtro-btn').forEach(btn => btn.classList.remove('active'));
                e.target.classList.add('active');
                filtroPrioridadeAtual = e.target.dataset.prioridade;
                renderizarAlertasNoModal();
            }
        });
    }
    if (customAlertCloseBtn) customAlertCloseBtn.addEventListener('click', () => customAlertOverlay.classList.add('hidden'));
    if (customAlertOkBtn) customAlertOkBtn.addEventListener('click', () => customAlertOverlay.classList.add('hidden'));
    if (customAlertOverlay) customAlertOverlay.addEventListener('click', (e) => { if (e.target === customAlertOverlay) customAlertOverlay.classList.add('hidden'); });
    if (alertasContainer) {
        alertasContainer.addEventListener('click', (e) => {
            const target = e.target;
            const alertaDiv = target.closest('.alerta-estrategia');
            if (target.matches('.alerta-acao-btn.excluir')) {
                if (alertaDiv) alertaDiv.remove();
                return;
            }
            if (target.matches('.alerta-acao-btn.apostar')) {
                const id = target.dataset.apostaId;
                const info = JSON.parse(target.dataset.apostaInfo);
                const giroAtual = historicoNumeros.length;

                if (!id || !info || apostasAtivas.some(a => a.id === id && !a.resolvida)) {
                    showCustomAlert("Já existe uma aposta ativa para esta estratégia. Aguarde a resolução.");
                    return;
                }

                const numerosVencedores = new Set(info.alvos);
                const numeroDeUnidades = numerosVencedores.size;
                const custoPrimeiraTentativa = numeroDeUnidades * valorUnidade;

                if (bancaHabilitada) {
                    if (saldoAtual < custoPrimeiraTentativa) {
                        showCustomAlert(`Saldo insuficiente para a 1ª tentativa! Custo: R$ ${custoPrimeiraTentativa.toFixed(2)}.`);
                        return;
                    }
                    logTransaction('saida', custoPrimeiraTentativa, `Aposta (1/2) - ${getStrategyName(id)}`);
                }

                const apostaUnicaId = `${id}-${giroAtual}-${Math.random()}`;
                apostasAtivas.push({
                    id, ...info,
                    giroAposta: giroAtual,
                    resolvida: false,
                    apostaUnicaId,
                    unidades: numeroDeUnidades,
                    custoInicial: custoPrimeiraTentativa,
                    tentativa: 1
                });

                logJogadas.push({ apostaUnicaId, giroAposta: giroAtual, estrategiaTitulo: getStrategyName(id), alvosTexto: info.alvosTexto, status: 'Ativa (1/2)', numeroSorteado: '-', giroResultado: '-' });
                
                const estado = estadoEstrategias[id];
                if (estado) estado.ultimoGatilhoNoGiro = giroAtual;
                
                if(alertaDiv) alertaDiv.remove();
                renderizarTudo();
            }
        });
    }

     if (habilitarHeatmapCheck) {
        habilitarHeatmapCheck.addEventListener('change', () => {
            heatmapHabilitado = habilitarHeatmapCheck.checked;
            salvarPreferenciaHeatmap();
            renderizarHeatmap();
            
            // A lógica de visibilidade agora controla os dois elementos
            if (heatmapLegenda) {
                heatmapLegenda.classList.toggle('hidden', !heatmapHabilitado);
            }
            if (cardQuenteFrio) {
                cardQuenteFrio.classList.toggle('hidden', !heatmapHabilitado);
            }
        });
    }

    if (centralAlertasContainer) {
        centralAlertasContainer.addEventListener('click', (e) => {
            const target = e.target;
            const alertaItem = target.closest('.alerta-item');
            if (target.matches('.alerta-acao-btn.excluir')) {
                if(alertaItem) alertaItem.remove();
                return;
            }
            if (target.matches('.alerta-acao-btn.apostar')) {
                const id = target.dataset.apostaId;
                const info = JSON.parse(target.dataset.apostaInfo);
                const giroAtual = historicoNumeros.length;

                if (!id || !info || apostasAtivas.some(a => a.id === id && !a.resolvida)) {
                    showCustomAlert("Já existe uma aposta ativa para esta estratégia. Aguarde a resolução.");
                    return;
                }

                const numerosVencedores = new Set(info.alvos);
                const numeroDeUnidades = numerosVencedores.size;
                const custoPrimeiraTentativa = numeroDeUnidades * valorUnidade;

                if (bancaHabilitada) {
                    if (saldoAtual < custoPrimeiraTentativa) {
                        showCustomAlert(`Saldo insuficiente para a 1ª tentativa! Custo: R$ ${custoPrimeiraTentativa.toFixed(2)}.`);
                        return;
                    }
                    logTransaction('saida', custoPrimeiraTentativa, `Aposta (1/2) - ${getStrategyName(id)}`);
                }

                const apostaUnicaId = `${id}-${giroAtual}-${Math.random()}`;
                apostasAtivas.push({
                    id, ...info,
                    giroAposta: giroAtual,
                    resolvida: false,
                    apostaUnicaId,
                    unidades: numeroDeUnidades,
                    custoInicial: custoPrimeiraTentativa,
                    tentativa: 1
                });

                logJogadas.push({ apostaUnicaId, giroAposta: giroAtual, estrategiaTitulo: getStrategyName(id), alvosTexto: info.alvosTexto, status: 'Ativa (1/2)', numeroSorteado: '-', giroResultado: '-' });

                const estado = estadoEstrategias[id];
                if (estado) estado.ultimoGatilhoNoGiro = giroAtual;
                
                if(alertaItem) alertaItem.remove();
                modalCentralAlertasOverlay.classList.add('hidden');
                renderizarTudo();
            }
        });
    }
    if (bancaHistoricoContainer) {
        bancaHistoricoContainer.addEventListener('click', (e) => {
            const target = e.target;
            const itemDiv = target.closest('.banca-historico-item');
            if (!itemDiv) return;
            const txnId = parseFloat(itemDiv.dataset.id);
            if (!txnId) return;
            if (target.closest('.delete-txn-btn')) {
                if (confirm('Tem certeza que deseja excluir esta transação?')) {
                    historicoBanca = historicoBanca.filter(txn => txn.id !== txnId);
                    saveBankrollHistory();
                    recalculateBalanceAndRender();
                }
            } else if (target.closest('.edit-txn-btn')) {
                editingTxnId = txnId;
                renderBankrollHistory();
            } else if (target.closest('.cancel-txn-btn')) {
                editingTxnId = null;
                renderBankrollHistory();
            } else if (target.closest('.save-txn-btn')) {
                const descInput = itemDiv.querySelector('.edit-description-input');
                const amountInput = itemDiv.querySelector('.edit-amount-input');
                const newAmount = parseFloat(amountInput.value);
                const newDescription = descInput.value.trim();
                if (!newDescription || isNaN(newAmount) || newAmount <= 0) {
                    showCustomAlert('Insira uma descrição e um valor válido.');
                    return;
                }
                const transactionIndex = historicoBanca.findIndex(txn => txn.id === txnId);
                if (transactionIndex > -1) {
                    historicoBanca[transactionIndex].description = newDescription;
                    historicoBanca[transactionIndex].amount = newAmount;
                }
                editingTxnId = null;
                saveBankrollHistory();
                recalculateBalanceAndRender();
            }
        });
    }
    if (avisosBtn) avisosBtn.addEventListener('click', () => { renderizarControlesAvisos(); modalAvisosOverlay.classList.remove('hidden'); });
    if (modalAvisosCloseBtn) modalAvisosCloseBtn.addEventListener('click', () => modalAvisosOverlay.classList.add('hidden'));
    if (modalAvisosOverlay) modalAvisosOverlay.addEventListener('click', (e) => { if (e.target === modalAvisosOverlay) modalAvisosOverlay.classList.add('hidden'); });
    if (selecionarTodosAvisosBtn) selecionarTodosAvisosBtn.addEventListener('click', () => { Object.keys(avisoConfig).forEach(k => avisoConfig[k] = true); salvarConfigAvisos(); renderizarControlesAvisos(); });
    if (desmarcarTodosAvisosBtn) desmarcarTodosAvisosBtn.addEventListener('click', () => { Object.keys(avisoConfig).forEach(k => avisoConfig[k] = false); salvarConfigAvisos(); renderizarControlesAvisos(); });
    if (modalCloseBtn) modalCloseBtn.addEventListener('click', () => modalOverlay.classList.add('hidden'));
    if (modalOverlay) modalOverlay.addEventListener('click', (e) => { if (e.target === modalOverlay) modalOverlay.classList.add('hidden'); });
    if (historicoDiv) historicoDiv.addEventListener('click', (e) => { if (e.target.matches('.numero-bola')) abrirPainelDoNumero(e.target.textContent); });
    if (estrategiasContainer) {
        estrategiasContainer.addEventListener('click', async (e) => {
            const target = e.target;
            const headerDiv = target.closest('.strategy-title-header');
            if (target.matches('.edit-name-btn')) {
                const strategyId = target.dataset.strategyId;
                const currentName = getStrategyName(strategyId);
                headerDiv.innerHTML = `<input type="text" class="edit-name-input" value="${currentName}" data-strategy-id="${strategyId}"><div class="edit-actions"><button class="save-name-btn" title="Salvar">✔️</button><button class="cancel-name-btn" title="Cancelar">❌</button></div>`;
                headerDiv.querySelector('.edit-name-input').focus();
            } else if (target.matches('.save-name-btn')) {
                const input = headerDiv.querySelector('.edit-name-input');
                const strategyId = input.dataset.strategyId;
                const newName = input.value.trim();
                if (newName) strategyCustomNames[strategyId] = newName;
                else delete strategyCustomNames[strategyId];
                await salvarNomesCustomizados();
                renderizarTudo();
            } else if (target.matches('.cancel-name-btn')) {
                renderizarControlesEstrategias();
            }
        });
    }
    if (iniciarBancaBtn) {
        iniciarBancaBtn.addEventListener('click', async () => {
            const inicial = parseFloat(bancaInicialInput.value);
            if (isNaN(inicial) || inicial < 0) {
                showCustomAlert("Insira um valor válido para a banca inicial.");
                return;
            }
            if (unidadesEmJogo > 0) {
                 const userConfirmed = await showCustomConfirm("Apostas em jogo. Resetar a banca agora pode causar inconsistências. Continuar?");
                 if (!userConfirmed) return;
            }
            bancaInicial = inicial;
            saldoAtual = inicial;
            historicoBanca = [];
            stopGainAlertado = false;
            stopLossAlertado = false;
            saveBankrollState();
            saveBankrollHistory();
            recalculateBalanceAndRender();
            showCustomAlert(`Banca iniciada com R$ ${bancaInicial.toFixed(2)}.`);
        });
    }
    if (abrirModalEstrategiaBtn) abrirModalEstrategiaBtn.addEventListener('click', () => openStrategyModal());
    if (modalEstrategiaCloseBtn) modalEstrategiaCloseBtn.addEventListener('click', () => modalEstrategiaOverlay.classList.add('hidden'));
    if (modalEstrategiaOverlay) modalEstrategiaOverlay.addEventListener('click', (e) => { if (e.target === modalEstrategiaOverlay) modalEstrategiaOverlay.classList.add('hidden'); });
    if (salvarEstrategiaBtn) salvarEstrategiaBtn.addEventListener('click', saveCustomStrategy);
    if (bancaBtn) bancaBtn.addEventListener('click', () => {
        if (!bancaHabilitada) return;
        modalBancaOverlay.classList.remove('hidden');
    });
    if (modalBancaCloseBtn) modalBancaCloseBtn.addEventListener('click', () => modalBancaOverlay.classList.add('hidden'));
    if (modalBancaOverlay) modalBancaOverlay.addEventListener('click', (e) => { if (e.target === modalBancaOverlay) modalBancaOverlay.classList.add('hidden'); });
    if (chipSelectorContainer) chipSelectorContainer.addEventListener('click', (e) => { if (e.target.matches('.chip-btn')) { valorUnidade = parseFloat(e.target.dataset.value); renderBankroll(); saveBankrollState(); } });
    if (aplicarPrejuizoBtn) aplicarPrejuizoBtn.addEventListener('click', aplicarPrejuizoManual);
    if (abrirGerenciadorBtn) {
        abrirGerenciadorBtn.addEventListener('click', () => {
            renderizarGerenciadorEstrategias();
            modalGerenciadorOverlay.classList.remove('hidden');
        });
    }
    if (modalGerenciadorCloseBtn) modalGerenciadorCloseBtn.addEventListener('click', () => modalGerenciadorOverlay.classList.add('hidden'));
    if (modalGerenciadorOverlay) modalGerenciadorOverlay.addEventListener('click', (e) => { if (e.target === modalGerenciadorOverlay) modalGerenciadorOverlay.classList.add('hidden'); });
    if (listaTodasEstrategiasContainer) {
        listaTodasEstrategiasContainer.addEventListener('click', (e) => {
            const target = e.target;
            if (target.matches('.excluir-estrategia-btn')) {
                const id = target.dataset.id;
                const isCustom = target.dataset.custom === 'true';
                excluirEstrategia(id, isCustom);
            }
            if (target.matches('.editar-estrategia-btn')) {
                const id = target.dataset.id;
                const strategyToEdit = customStrategies.find(s => s.id === id);
                if (strategyToEdit) openStrategyModal(strategyToEdit);
            }
        });
    }
    if (abrirConfigPadraoBtn) {
        abrirConfigPadraoBtn.addEventListener('click', () => {
            modalConfigPadraoOverlay.classList.remove('hidden');
        });
    }
    if (modalConfigPadraoCloseBtn) modalConfigPadraoCloseBtn.addEventListener('click', () => modalConfigPadraoOverlay.classList.add('hidden'));
    if (modalConfigPadraoOverlay) modalConfigPadraoOverlay.addEventListener('click', (e) => { if (e.target === modalConfigPadraoOverlay) modalConfigPadraoOverlay.classList.add('hidden'); });
    if (avisoCentralAlertasBar) avisoCentralAlertasBar.addEventListener('click', () => modalCentralAlertasOverlay.classList.remove('hidden'));
    if (modalCentralAlertasCloseBtn) modalCentralAlertasCloseBtn.addEventListener('click', () => modalCentralAlertasOverlay.classList.add('hidden'));
    if (modalCentralAlertasOverlay) modalCentralAlertasOverlay.addEventListener('click', (e) => { if (e.target === modalCentralAlertasOverlay) modalCentralAlertasOverlay.classList.add('hidden'); });
    if (fixarHistoricoCheck) {
        fixarHistoricoCheck.addEventListener('change', () => {
            atualizarEstadoFixar();
            salvarPreferenciaFixar();
        });
    }
    
    // --- LÓGICA ATUALIZADA PARA OCR COM RECORTE ---
    let cropRect = null;
    let originalImage = null;
    
    if (importarHistoricoBtn) {
        importarHistoricoBtn.addEventListener('click', () => {
            pasteArea.innerHTML = 'Cole a imagem aqui';
            pasteArea.classList.remove('cropping');
            importarInstrucao.textContent = 'Cole a imagem (Ctrl+V) na área pontilhada abaixo.';
            importarTextoArea.value = '';
            ocrProgress.classList.add('hidden');
            readFromSelectionBtn.disabled = true;
            cropRect = null;
            originalImage = null;
            modalImportarOverlay.classList.remove('hidden');
        });
    }

    if (modalImportarCloseBtn) modalImportarCloseBtn.addEventListener('click', () => modalImportarOverlay.classList.add('hidden'));
    if (modalImportarOverlay) modalImportarOverlay.addEventListener('click', (e) => { if (e.target === modalImportarOverlay) modalImportarOverlay.classList.add('hidden'); });
    
    if (pasteArea) {
        pasteArea.addEventListener('paste', async (e) => {
            e.preventDefault();
            const items = e.clipboardData.items;
            let imageFile = null;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    imageFile = items[i].getAsFile();
                    break;
                }
            }
            if (!imageFile) {
                showCustomAlert("Nenhuma imagem encontrada na área de transferência.");
                return;
            }

            importarInstrucao.textContent = 'Clique e arraste na imagem para selecionar a área dos números.';
            pasteArea.innerHTML = '';
            pasteArea.classList.add('cropping');

            const canvas = document.createElement('canvas');
            canvas.id = 'image-canvas';
            const ctx = canvas.getContext('2d');
            pasteArea.appendChild(canvas);

            originalImage = new Image();
            originalImage.onload = () => {
                canvas.width = originalImage.width;
                canvas.height = originalImage.height;
                ctx.drawImage(originalImage, 0, 0);

                let isCropping = false;
                let startX, startY;

                const getCanvasCoords = (e) => {
                    const rect = canvas.getBoundingClientRect();
                    const scaleX = canvas.width / rect.width;
                    const scaleY = canvas.height / rect.height;
                    return {
                        x: (e.clientX - rect.left) * scaleX,
                        y: (e.clientY - rect.top) * scaleY
                    };
                };

                canvas.onmousedown = (e) => {
                    isCropping = true;
                    const coords = getCanvasCoords(e);
                    startX = coords.x;
                    startY = coords.y;
                };

                canvas.onmousemove = (e) => {
                    if (!isCropping) return;
                    const coords = getCanvasCoords(e);
                    const width = coords.x - startX;
                    const height = coords.y - startY;

                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(originalImage, 0, 0);
                    ctx.fillStyle = 'rgba(0, 150, 255, 0.3)';
                    ctx.fillRect(startX, startY, width, height);
                    ctx.strokeStyle = 'rgba(0, 150, 255, 0.8)';
                    ctx.strokeRect(startX, startY, width, height);
                };

                canvas.onmouseup = (e) => {
                    if (!isCropping) return;
                    isCropping = false;
                    const coords = getCanvasCoords(e);
                    const width = coords.x - startX;
                    const height = coords.y - startY;

                    if (Math.abs(width) > 10 && Math.abs(height) > 10) {
                        cropRect = {
                            x: Math.min(startX, coords.x),
                            y: Math.min(startY, coords.y),
                            width: Math.abs(width),
                            height: Math.abs(height)
                        };
                        readFromSelectionBtn.disabled = false;
                    } else {
                        cropRect = null;
                        readFromSelectionBtn.disabled = true;
                    }
                };
            };
            originalImage.src = URL.createObjectURL(imageFile);
        });
    }

    if (readFromSelectionBtn) {
    readFromSelectionBtn.addEventListener('click', async () => {
        console.log("Botão 'Ler da Seleção' foi clicado.");
        ocrProgress.classList.remove('hidden');
        ocrProgress.textContent = 'Botão clicado! Iniciando processo...';

        if (!cropRect || !originalImage) {
            console.error("Erro de pré-condição: Área de recorte (cropRect) ou imagem original não encontrada.");
            showCustomAlert("Por favor, selecione uma área na imagem primeiro.");
            ocrProgress.classList.add('hidden');
            return;
        }

        console.log("Pré-condições verificadas. Desabilitando botão.");
        readFromSelectionBtn.disabled = true;

        try {
            console.log("Entrando no bloco de processamento OCR.");
            const cropCanvas = document.createElement('canvas');
            cropCanvas.width = cropRect.width;
            cropCanvas.height = cropRect.height;
            const cropCtx = cropCanvas.getContext('2d');
            cropCtx.drawImage(originalImage, cropRect.x, cropRect.y, cropRect.width, cropRect.height, 0, 0, cropRect.width, cropRect.height);
            
            console.log("Canvas de recorte criado. Pré-processando imagem...");
            const imageData = cropCtx.getImageData(0, 0, cropCanvas.width, cropCanvas.height);
            const data = imageData.data;
            for (let i = 0; i < data.length; i += 4) {
                const gray = data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114;
                const threshold = 128;
                const color = gray > threshold ? 255 : 0;
                data[i] = data[i + 1] = data[i + 2] = color;
            }
            cropCtx.putImageData(imageData, 0, 0);
            console.log("Imagem pré-processada.");

            const logger = (m) => {
                console.log('Tesseract Log:', m);
                let statusText = '';
                switch(m.status) {
                    case 'initializing':
                        statusText = 'Inicializando motor...';
                        break;
                    case 'loading language model':
                        statusText = 'Carregando modelo de linguagem...';
                        break;
                    case 'recognizing text':
                        const progress = (m.progress * 100).toFixed(0);
                        statusText = `Lendo os números... ${progress}%`;
                        break;
                    default:
                        statusText = m.status.charAt(0).toUpperCase() + m.status.slice(1);
                }
                ocrProgress.textContent = statusText;
            }
            
            console.log("Iniciando Tesseract.createWorker...");
            const worker = await Tesseract.createWorker('eng', 1, { logger });
            console.log("Worker do Tesseract criado.");

            await worker.setParameters({
                tessedit_char_whitelist: '0123456789',
            });
            console.log("Parâmetros do Tesseract definidos.");

            console.log("Iniciando reconhecimento (recognize)...");
            const { data: { text } } = await worker.recognize(cropCanvas);
            console.log("Reconhecimento concluído.");
            
            await worker.terminate();
            console.log("Worker finalizado.");

            const numerosFormatados = text.replace(/\n/g, ' ').split(' ').filter(n => n.trim() !== '' && !isNaN(n)).join(', ');
            importarTextoArea.value = numerosFormatados;
            ocrProgress.textContent = 'Análise concluída! Verifique o texto e clique em "Importar Números".';

        } catch (error) {
            console.error("ERRO CRÍTICO no processo de OCR:", error);
            showCustomAlert("Ocorreu um erro inesperado durante a leitura da imagem. Verifique o console para mais detalhes.");
            ocrProgress.textContent = 'Erro ao ler a imagem.';
        } finally {
            console.log("Bloco finally executado. Re-habilitando botão.");
            readFromSelectionBtn.disabled = false;
        }
    });
}

    if (importFromTextBtn) {
        importFromTextBtn.addEventListener('click', async () => {
            const textoFinal = importarTextoArea.value.trim();
            if (!textoFinal) {
                showCustomAlert("O campo de texto está vazio. Cole ou leia os números de uma imagem primeiro.");
                return;
            }

            const userConfirmed = await showCustomConfirm("Isso substituirá o histórico atual pelos números do campo de texto. Deseja continuar?");
            if (!userConfirmed) return;

            historicoNumeros = [];
            apostasAtivas = [];
            apostasAutomaticas = [];
            logJogadas = [];
            resetarTodosOsEstados();
            inicializarTimingPerformance();
            
            const numerosParaProcessar = textoFinal.split(/[\s,;]+/).map(n => parseInt(n, 10)).filter(n => !isNaN(n) && n >= 0 && n <= 36);
        
            if (numerosParaProcessar.length === 0) {
                showCustomAlert("Nenhum número válido no campo de texto para importar.");
                return;
            }

            numerosParaProcessar.forEach(num => processarNumero(num));
        
            renderizarTudo();
            modalImportarOverlay.classList.add('hidden');
            showCustomAlert(`${numerosParaProcessar.length} números foram importados com sucesso!`);
        });
    }

    // Eventos para o popup de Configurações Gerais
    if (abrirConfigGeraisBtn) abrirConfigGeraisBtn.addEventListener('click', () => modalConfigGeraisOverlay.classList.remove('hidden'));
    if (modalConfigGeraisCloseBtn) modalConfigGeraisCloseBtn.addEventListener('click', () => modalConfigGeraisOverlay.classList.add('hidden'));
    if (modalConfigGeraisOverlay) modalConfigGeraisOverlay.addEventListener('click', (e) => { if (e.target === modalConfigGeraisOverlay) modalConfigGeraisOverlay.classList.add('hidden'); });

    // Eventos para o popup de Habilitar/Desabilitar Estratégias
    if (abrirHabilitarEstrategiasBtn) abrirHabilitarEstrategiasBtn.addEventListener('click', () => modalHabilitarEstrategiasOverlay.classList.remove('hidden'));
    if (modalHabilitarEstrategiasCloseBtn) modalHabilitarEstrategiasCloseBtn.addEventListener('click', () => modalHabilitarEstrategiasOverlay.classList.add('hidden'));
    if (modalHabilitarEstrategiasOverlay) modalHabilitarEstrategiasOverlay.addEventListener('click', (e) => { if (e.target === modalHabilitarEstrategiasOverlay) modalHabilitarEstrategiasOverlay.classList.add('hidden'); });

    function addDragAndDropListeners() {
        const items = habilitarEstrategiasContainer.querySelectorAll('.sortable-item');
        items.forEach(item => {
            item.addEventListener('dragstart', handleDragStart, false);
            item.addEventListener('dragover', handleDragOver, false);
            item.addEventListener('dragleave', handleDragLeave, false);
            item.addEventListener('drop', handleDrop, false);
            item.addEventListener('dragend', handleDragEnd, false);
        });
    }
    function handleDragStart(e) {
        draggedItem = this;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
        setTimeout(() => this.classList.add('dragging'), 0);
    }
    function handleDragOver(e) {
        e.preventDefault();
        const container = habilitarEstrategiasContainer;
        const afterElement = getDragAfterElement(container, e.clientY);
        const draggable = document.querySelector('.dragging');
        if (afterElement == null) {
            container.appendChild(draggable);
        } else {
            container.insertBefore(draggable, afterElement);
        }
    }
    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.sortable-item:not(.dragging)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
    function handleDragLeave(e) {}
    function handleDrop(e) {
        e.stopPropagation();
        return false;
    }
    function handleDragEnd() {
        this.classList.remove('dragging');
        strategyOrder = Array.from(habilitarEstrategiasContainer.querySelectorAll('.sortable-item')).map(item => item.dataset.strategyId);
        saveStrategyOrder();
        renderizarRelatorios();
        renderizarControlesEstrategias();
    }

    // --- 9. INICIA A APLICAÇÃO ---
    await carregarNomesCustomizados();
    await loadCustomStrategies();
    loadStrategyOrder();
    loadBankrollState();
    loadBankrollHistory();
    carregarEstadoBancaHabilitada();
    carregarConfigAvisos();
    carregarTimingConfig();
    carregarEstrategiasHabilitadas();
    carregarPreferenciaFixar();
    inicializarTimingPerformance();
    resetarTodosOsEstados();
    criarBotoes();
    setupTabs();
    renderizarTudo();
    atualizarVisibilidadeBanca();
    carregarPreferenciaHeatmap();
});
// SCRIPT COMPLETO - FIM