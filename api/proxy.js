//
// Este é o conteúdo completo para o seu NOVO arquivo: /api/proxy.js
//

// Funções do Node.js para lidar com requisições
import fetch from 'node-fetch';
import { URLSearchParams } from 'url';

// O handler da Vercel (substitui o arquivo PHP)
export default async function handler(request, response) {
    // 1. Configura o CORS para permitir que seu app acesse
    response.setHeader('Access-Control-Allow-Origin', '*');
    response.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    // Responde a requisições OPTIONS (necessário para o CORS)
    if (request.method === 'OPTIONS') {
        response.status(200).end();
        return;
    }

    try {
        // 2. Obtém os parâmetros da URL (ex: ?loteria=rj&tipo=resultados)
        const { tipo, loteria, data } = request.query;

        let url_alvo = '';
        let options = {
            method: 'GET', // Por padrão é GET
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            },
        };

        if (tipo === 'atrasados') {
            // Lógica para buscar os "Atrasados" (usa POST)
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
            // Lógica para buscar os "Resultados" (usa GET)
            const mapa_urls = {
                'rj': 'https://bichocerto.com/resultados/rj/para-todos/',
                'lk': 'https://bichocerto.com/resultados/look/para-todos/',
                'fd': 'https://bichocerto.com/resultados/federal/para-todos/',
                'ln': 'https://bichocerto.com/resultados/nacional/para-todos/',
                'ba': 'https://bichocerto.com/resultados/bahia/para-todos/',
            };
            
            if (!mapa_urls[loteria]) {
                response.status(400).send(`Erro: URL de resultados não definida para esta loteria: ${loteria}`);
                return;
            }
            
            url_alvo = mapa_urls[loteria];

            // Lógica da data (para buscar dias anteriores)
            if (data) {
                if (/^\d{4}-\d{2}-\d{2}$/.test(data)) {
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

        // 3. Faz a busca no site de origem (bichocerto.com)
        const fetchResponse = await fetch(url_alvo, options);

        if (!fetchResponse.ok) {
            console.error(`Erro ao buscar ${url_alvo}. Status: ${fetchResponse.status}`);
            response.status(502).send(`Erro ao buscar conteudo da URL: ${url_alvo}. Código: ${fetchResponse.status}`);
            return;
        }

        // 4. Retorna o HTML do site de origem para o seu app
        const html = await fetchResponse.text();
        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        response.status(200).send(html);

    } catch (error) {
        console.error('Erro GERAL no proxy Vercel:', error);
        response.status(500).send(`Erro interno no servidor proxy: ${error.message}`);
    }
}