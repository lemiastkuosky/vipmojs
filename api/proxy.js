// /api/proxy.js - VERSÃO FINAL COM AXIOS (usa require)

const axios = require('axios'); // <-- MUDANÇA
const cheerio = require('cheerio');

// Mapeamentos (sem mudança)
const nomeSorteioMap = { 'PTM': 'RIO 11:20', 'PT': 'RIO 14:20', 'PTV': 'RIO 16:20', 'PTN': 'RIO 18:20', 'COR': 'CORUJA 21:30', 'LOOK': 'LOOK', 'NACIONAL': 'NACIONAL', 'FEDERAL': 'FEDERAL' };
const mapaUrls = { 'rj': 'https://bichocerto.com/resultados/rj/para-todos/', 'lk': 'https://bichocerto.com/resultados/lk/look', 'fd': 'https://bichocerto.com/resultados/fd/loteria-federal', 'ln': 'https://bichocerto.com/resultados/ln/loteria-nacional', 'ba': 'https://bichocerto.com/resultados/ba/bahia' };

module.exports = async (request, response) => {
    // CORS (sem mudança)
    response.setHeader('Access-Control-Allow-Origin', '*');
    response.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    if (request.method === 'OPTIONS') return response.status(200).end();

    // Validação de Parâmetros (sem mudança)
    const { loteria } = request.query;
    if (!loteria) return response.status(400).send('Erro: Parâmetro "loteria" não especificado.');
    const urlAlvo = mapaUrls[loteria];
    if (!urlAlvo) return response.status(400).send(`Erro: URL de resultados não definida para a loteria "${loteria}".`);

    try {
        console.log(`Proxy Vercel (axios): Buscando resultados em ${urlAlvo}`);
        
        // --- MUDANÇA: Usando AXIOS ---
        const res = await axios.get(urlAlvo, {
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            }
        });
        
        if (res.status !== 200) {
            throw new Error(`Erro ${res.status} ao acessar o site de origem.`);
        }
        
        const html = res.data; // <--- No axios, os dados vêm em .data
        // --- FIM DA MUDANÇA ---
        
        const $ = cheerio.load(html);
        const resultadosFinais = [];

        // Lógica do Cheerio (sem mudança)
        $('div.result-item').each((index, item) => {
            const $item = $(item);
            let nomeSorteioOriginal = $item.find('h4').text().trim();
            if (!nomeSorteioOriginal) return;
            let nomeSorteioApp = nomeSorteioOriginal;
            const nomeCurto = nomeSorteioOriginal.split(' ')[0].toUpperCase();
            if (nomeSorteioMap[nomeCurto]) {
               nomeSorteioApp = nomeSorteioMap[nomeCurto];
               if (nomeCurto === 'LOOK' || nomeCurto === 'NACIONAL') {
                   const horaMatch = nomeSorteioOriginal.match(/(\d{2}:\d{2})/);
                   if (horaMatch) nomeSorteioApp = `${nomeSorteioMap[nomeCurto]} ${horaMatch[0]}`;
               }
            } else if (nomeCurto.includes('FEDERAL')) {
                nomeSorteioApp = nomeSorteioMap['FEDERAL'];
            }
            const resultadosDoSorteio = [];
            const linhas = $item.find('table tbody tr');
            linhas.each((i, linha) => {
                const colunas = $(linha).find('td');
                let itemResultado = null;
                if (colunas.length === 5) {
                    itemResultado = { posicao: $(colunas[0]).text().trim(), emoji: $(colunas[1]).text().trim(), milhar: $(colunas[2]).text().trim().replace('.', ''), grupo: `${$(colunas[3]).text().trim()} - ${$(colunas[4]).text().trim()}` };
                } else if (colunas.length === 4) {
                    itemResultado = { posicao: $(colunas[0]).text().trim(), emoji: '❔', milhar: $(colunas[1]).text().trim().replace('.', ''), grupo: `${$(colunas[2]).text().trim()} - ${$(colunas[3]).text().trim()}` };
                }
                if (itemResultado && itemResultado.milhar.length >= 4) {
                    resultadosDoSorteio.push(itemResultado);
                }
            });
            if (resultadosDoSorteio.length > 0) {
                resultadosFinais.push({ nomeSorteio: nomeSorteioApp, nomeSorteioOriginal: nomeSorteioOriginal, resultados: resultadosDoSorteio });
            }
        });

        response.setHeader('Content-Type', 'application/json');
        return response.status(200).json(resultadosFinais);

    } catch (error) {
        console.error("Erro crítico no proxy Vercel (axios):", error); 
        return response.status(502).json({ error: `Erro no proxy: ${error.message}` });
    }
}