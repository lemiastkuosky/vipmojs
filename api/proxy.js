// /api/proxy.js - VERSÃO ATUALIZADA

import fetch from 'node-fetch';
import cheerio from 'cheerio'; // <--- Importa o 'leitor' de HTML

// Mapeamento dos nomes de sorteio (como vêm no HTML)
// para os nomes que seu app já usa (mapaSorteios)
const nomeSorteioMap = {
    'PTM': 'RIO 11:20',
    'PT': 'RIO 14:20',
    'PTV': 'RIO 16:20',
    'PTN': 'RIO 18:20',
    'COR': 'CORUJA 21:30',
    'LOOK': 'LOOK', // Para sorteios da Look
    'NACIONAL': 'NACIONAL', // Para sorteios da Nacional
    'FEDERAL': 'FEDERAL' // Para Federal
};

// Mapeia a sigla da loteria para a URL correta
const mapaUrls = {
    'rj': 'https://bichocerto.com/resultados/rj/para-todos/',
    'lk': 'https://bichocerto.com/resultados/lk/look', 
    'fd': 'https://bichocerto.com/resultados/fd/loteria-federal', 
    'ln': 'https://bichocerto.com/resultados/ln/loteria-nacional',
    'ba': 'https://bichocerto.com/resultados/ba/bahia' // Adicionei a Bahia que estava faltando
};

export default async function handler(request, response) {
    // 1. Permite CORS
    response.setHeader('Access-Control-Allow-Origin', '*');
    response.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (request.method === 'OPTIONS') {
        return response.status(200).end();
    }

    // 2. Pega os parâmetros (ignora 'tipo', vamos focar em 'resultados')
    const { loteria } = request.query;

    if (!loteria) {
        return response.status(400).send('Erro: Parâmetro "loteria" não especificado.');
    }

    const urlAlvo = mapaUrls[loteria];
    if (!urlAlvo) {
        return response.status(400).send(`Erro: URL de resultados não definida para a loteria "${loteria}".`);
    }

    // 3. Faz a busca no site (Fetch)
    try {
        console.log(`Proxy Vercel: Buscando resultados em ${urlAlvo}`);
        const res = await fetch(urlAlvo, {
            method: 'GET',
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            }
        });

        if (!res.ok) {
            throw new Error(`Erro ${res.status} ao acessar o site de origem.`);
        }

        const html = await res.text();

        // 4. *** A MÁGICA ACONTECE AQUI (Parsing com Cheerio) ***
        const $ = cheerio.load(html);
        const resultadosFinais = [];

        // O novo layout usa "result-item" para cada sorteio do dia
        $('div.result-item').each((index, item) => {
            const $item = $(item);

            // Pega o nome do sorteio (ex: "PTM 11:20", "FEDERAL 19:00")
            let nomeSorteioOriginal = $item.find('h4').text().trim();
            if (!nomeSorteioOriginal) return; // Pula se não tiver título

            // Tenta traduzir o nome para o formato que seu app entende
            let nomeSorteioApp = nomeSorteioOriginal; // Padrão
            const nomeCurto = nomeSorteioOriginal.split(' ')[0].toUpperCase(); // Ex: "PTM"

            if (nomeSorteioMap[nomeCurto]) {
               nomeSorteioApp = nomeSorteioMap[nomeCurto];
               // Caso especial da Look, que pode ter vários horários
               if (nomeCurto === 'LOOK' || nomeCurto === 'NACIONAL') {
                   const horaMatch = nomeSorteioOriginal.match(/(\d{2}:\d{2})/);
                   if (horaMatch) {
                       nomeSorteioApp = `${nomeSorteioMap[nomeCurto]} ${horaMatch[0]}`;
                   }
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
                    // Formato Padrão (RJ, LK, etc)
                    itemResultado = {
                        posicao: $(colunas[0]).text().trim(),
                        emoji: $(colunas[1]).text().trim(),
                        milhar: $(colunas[2]).text().trim().replace('.', ''), // Limpa pontos
                        grupo: `${$(colunas[3]).text().trim()} - ${$(colunas[4]).text().trim()}`
                    };
                } else if (colunas.length === 4) {
                    // Formato Federal (4 colunas)
                    itemResultado = {
                        posicao: $(colunas[0]).text().trim(),
                        emoji: '❔', // Federal não tem emoji, vamos adicionar um placeholder
                        milhar: $(colunas[1]).text().trim().replace('.', ''), // Limpa pontos
                        grupo: `${$(colunas[2]).text().trim()} - ${$(colunas[3]).text().trim()}`
                    };
                }

                if (itemResultado && itemResultado.milhar.length >= 4) {
                    resultadosDoSorteio.push(itemResultado);
                }
            });

            // Adiciona o bloco de resultados ao array final
            if (resultadosDoSorteio.length > 0) {
                resultadosFinais.push({
                    nomeSorteio: nomeSorteioApp, // <--- Nome traduzido
                    nomeSorteioOriginal: nomeSorteioOriginal, // <--- Nome do site
                    resultados: resultadosDoSorteio
                });
            }
        });

        // 5. Envia o JSON limpo de volta para o seu app
        response.setHeader('Content-Type', 'application/json');
        return response.status(200).json(resultadosFinais);

    } catch (error) {
        console.error("Erro crítico no proxy Vercel:", error); 
        return response.status(502).send(`Erro no proxy: ${error.message}`);
    }
}