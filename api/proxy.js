//
// CONTEÚDO CORRIGIDO PARA /api/proxy.js
// (Disfarce de navegador completo e URLs corretas)
//

import fetch from 'node-fetch';
import { URLSearchParams } from 'url';

// --- CABEÇALHOS PADRÃO (DISFARCE) ---
const baseHeaders = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
    'Accept-Language': 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
};

export default async function handler(request, response) {
    // 1. Configura o CORS
    response.setHeader('Access-Control-Allow-Origin', '*');
    response.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (request.method === 'OPTIONS') {
        response.status(200).end();
        return;
    }

    try {
        const { tipo, loteria, data } = request.query;

        let url_alvo = '';
        let options = {
            method: 'GET',
            headers: baseHeaders, // Começa com o disfarce base
        };

        if (tipo === 'atrasados') {
            // --- OPÇÕES PARA REQUISIÇÃO 'ATRASADOS' (POST) ---
            const loterias_permitidas = ['fd', 'rj', 'lk', 'ln', 'ba'];
            if (!loterias_permitidas.includes(loteria)) {
                response.status(400).send('Erro: Loteria inválida para atrasados.');
                return;
            }
            
            url_alvo = 'https://bichocerto.com/estatisticas/atrasados/grupo/load/';
            options.method = 'POST';
            
            // Cabeçalhos específicos para uma requisição POST (fetch)
            options.headers = {
                ...baseHeaders,
                'Accept': '*/*',
                'Content-Type': 'application/x-www-form-urlencoded',
                'Referer': 'https://bichocerto.com/estatisticas/atrasados/grupo/',
                'Origin': 'https://bichocerto.com',
                'Sec-Fetch-Dest': 'empty',
                'Sec-Fetch-Mode': 'cors',
                'Sec-Fetch-Site': 'same-origin'
            };
            
            const post_data = new URLSearchParams({
                'l': loteria, 'p': '1', 'e': 'all', 'et': 'Geral'
            });
            options.body = post_data.toString();

        } else if (tipo === 'resultados') {
            // --- OPÇÕES PARA REQUISIÇÃO 'RESULTADOS' (GET) ---
            
            // --- URLs CORRIGIDAS (FINAL) ---
            const mapa_urls = {
                'rj': 'https://bichocerto.com/resultados/rj/',
                'lk': 'https://bichocerto.com/resultados/go/', // Correto é /go/
                'fd': 'https://bichocerto.com/resultados/federal/',
                'ln': 'https://bichocerto.com/resultados/ln/', // Correto é /ln/
                'ba': 'https://bichocerto.com/resultados/bahia/',
            };
            
            if (!mapa_urls[loteria]) {
                response.status(400).send(`Erro: URL de resultados não definida para esta loteria: ${loteria}`);
                return;
            }
            
            url_alvo = mapa_urls[loteria];

            if (data) {
                if (/^\d{4}-\d{2}-\d{2}$/.test(data)) {
                    url_alvo = url_alvo + data + '/';
                } else {
                    response.status(400).send('Erro: Formato de data inválido. Use AAAA-MM-DD.');
                    return;
                }
            } else {
                if (loteria === 'fd') {
                    url_alvo = url_alvo + 'de-hoje/';
                }
            }
            
            options.method = 'GET';
            // Cabeçalhos específicos para uma navegação (GET)
            options.headers = {
                ...baseHeaders,
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Referer': url_alvo, // O Referer é a própria URL
                'Sec-Fetch-Dest': 'document',
                'Sec-Fetch-Mode': 'navigate',
                'Sec-Fetch-Site': 'same-origin',
                'Sec-Fetch-User': '?1',
                'Upgrade-Insecure-Requests': '1'
            };

        } else {
            response.status(400).send('Erro: Tipo de busca inválido.');
            return;
        }

        // 3. Faz a busca no site de origem
        const fetchResponse = await fetch(url_alvo, options);

        if (!fetchResponse.ok) {
            console.error(`Erro ao buscar ${url_alvo}. Status: ${fetchResponse.status}`);
            response.status(502).send(`Erro ao buscar conteudo da URL: ${url_alvo}. Código: ${fetchResponse.status}`);
            return;
        }

        // 4. Retorna o HTML do site de origem
        const html = await fetchResponse.text();
        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        response.status(200).send(html);

    } catch (error) {
        console.error('Erro GERAL no proxy Vercel:', error);
        response.status(500).send(`Erro interno no servidor proxy: ${error.message}`);
    }
}