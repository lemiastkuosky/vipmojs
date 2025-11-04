//
// CONTEÚDO CORRIGIDO PARA /api/proxy.js
// (Removido o "/para-todos/" das URLs)
//

import fetch from 'node-fetch';
import { URLSearchParams } from 'url';

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
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            },
        };

        if (tipo === 'atrasados') {
            const loterias_permitidas = ['fd', 'rj', 'lk', 'ln', 'ba'];
            if (!loterias_permitidas.includes(loteria)) {
                response.status(400).send('Erro: Loteria inválida para atrasados.');
                return;
            }
            
            url_alvo = 'https://bichocerto.com/estatisticas/atrasados/grupo/load/';
            options.method = 'POST';
            const post_data = new URLSearchParams({
                'l': loteria,
                'p': '1',
                'e': 'all',
                'et': 'Geral'
            });
            options.body = post_data.toString();
            options.headers['Content-Type'] = 'application/x-www-form-urlencoded';

        } else if (tipo === 'resultados') {
            
            // --- INÍCIO DA CORREÇÃO ---
            // As URLs aqui foram atualizadas (removido o /para-todos/)
            const mapa_urls = {
                'rj': 'https://bichocerto.com/resultados/rj/',
                'lk': 'https://bichocerto.com/resultados/look/',
                'fd': 'https://bichocerto.com/resultados/federal/',
                'ln': 'https://bichocerto.com/resultados/nacional/',
                'ba': 'https://bichocerto.com/resultados/bahia/',
            };
            // --- FIM DA CORREÇÃO ---
            
            if (!mapa_urls[loteria]) {
                response.status(400).send(`Erro: URL de resultados não definida para esta loteria: ${loteria}`);
                return;
            }
            
            url_alvo = mapa_urls[loteria];

            if (data) {
                if (/^\d{4}-\d{2}-\d{2}$/.test(data)) {
                    // O site de origem usa /DATA/ no final
                    url_alvo = url_alvo + data + '/';
                } else {
                    response.status(400).send('Erro: Formato de data inválido. Use AAAA-MM-DD.');
                    return;
                }
            }
            options.method = 'GET';
        } else {
            response.status(400).send('Erro: Tipo de busca inválido.');
            return;
        }

        // 3. Faz a busca no site de origem
        const fetchResponse = await fetch(url_alvo, options);

        if (!fetchResponse.ok) {
            console.error(`Erro ao buscar ${url_alvo}. Status: ${fetchResponse.status}`);
            // Retorna o erro 404 para o app saber que a página não existe
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