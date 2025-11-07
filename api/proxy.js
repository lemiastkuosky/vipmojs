//
// CONTEÚDO CORRIGIDO (TENTATIVA 5 / VERSÃO 5.0)
// Lógica final: Datas passadas só funcionam para a Loteria Federal.
// INCLUI UM VERIFICADOR DE VERSÃO: ?tipo=versao
//

import fetch from 'node-fetch';
import { URLSearchParams } from 'url';

// --- ADICIONE ESTA LINHA ---
const PROXY_VERSION = "V5.0";

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

    // Adiciona a versão aos logs do servidor
    console.log(`[Proxy ${PROXY_VERSION}] Recebida requisição...`);

    try {
        const { tipo, loteria, data } = request.query;

        // --- ADICIONE ESTE BLOCO ---
        // 2. Verificador de Versão
        // Se a URL for /api/proxy?tipo=versao, ele para aqui.
        if (tipo === 'versao') {
            console.log(`[Proxy ${PROXY_VERSION}] Verificação de versão solicitada.`);
            // Retorna a versão como texto simples
            response.status(200).send(`PROXY_VERSION_${PROXY_VERSION}`);
            return;
        }
        // --- FIM DO BLOCO ADICIONADO ---

        let url_alvo = '';
        let options = {
            method: 'GET',
            headers: baseHeaders,
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
            
            // [CAMINHOS CORRIGIDOS]
            const mapa_urls = {
                'rj': 'https://bichocerto.com/resultados/rj/para-todos',
                'lk': 'https://bichocerto.com/resultados/lk/look',
                'fd': 'https://bichocerto.com/resultados/fd/loteria-federal',
                'ln': 'https://bichocerto.com/resultados/ln/loteria-nacional',
                'ba': 'https://bichocerto.com/resultados/ba/para-todos',
            };
            
            if (!mapa_urls[loteria]) {
                response.status(400).send(`Erro: URL de resultados não definida para esta loteria: ${loteria}`);
                return;
            }
            
            url_alvo = mapa_urls[loteria];

            // [LÓGICA DA DATA CORRIGIDA (SÓ FUNCIONA PARA FD)]
            if (data && /^\d{4}-\d{2}-\d{2}$/.test(data)) {
                
                if (loteria === 'fd') {
                    // Federal é a ÚNICA que aceita data no caminho
                    url_alvo = url_alvo + '/' + data + '/';
                } else {
                    // Para RJ, LK, LN, BA: Ignora a data e busca a página principal (de hoje)
                    url_alvo = url_alvo + '/';
                }

            } else {
                // Se não houver data (busca de hoje)
                if (loteria === 'fd') {
                    url_alvo = url_alvo + '/de-hoje/';
                } else {
                    // Adiciona a barra final para RJ, LK, LN, BA
                    url_alvo = url_alvo + '/';
                }
            }
            
            options.method = 'GET';
            options.headers = {
                ...baseHeaders,
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Referer': url_alvo, 
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
            console.error(`[Proxy ${PROXY_VERSION}] Erro ao buscar ${url_alvo}. Status: ${fetchResponse.status}`);
            response.status(502).send(`[Proxy ${PROXY_VERSION}] Erro ao buscar conteudo da URL: ${url_alvo}. Código: ${fetchResponse.status}`);
            return;
        }

        // 4. Retorna o HTML do site de origem
        const html = await fetchResponse.text();
        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        response.status(200).send(html);

    } catch (error) {
        console.error(`[Proxy ${PROXY_VERSION}] Erro GERAL no proxy Vercel:`, error);
        response.status(500).send(`[Proxy ${PROXY_VERSION}] Erro interno no servidor proxy: ${error.message}`);
    }
}