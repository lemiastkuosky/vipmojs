// /api/proxy.js - Versão Final Unificada

export default async function handler(request, response) {
    // 1. Permite que qualquer site acesse (CORS) - Essencial no início
    response.setHeader('Access-Control-Allow-Origin', '*');
    response.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS'); // Permite GET e POST
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    // Responde a requisições OPTIONS (pré-verificação CORS)
    if (request.method === 'OPTIONS') {
        return response.status(200).end();
    }

    // 2. Pega os parâmetros da URL
    const { loteria, tipo } = request.query;

    // *** VALIDAÇÃO DE LOTERIA (essencial para ambos os tipos) ***
    if (!loteria) {
        return response.status(400).send('Erro: Parâmetro "loteria" não especificado.');
    }

    // *** DETERMINA O TIPO DE BUSCA (padrão é 'atrasados' se 'tipo' não for enviado) ***
    const tipoBusca = tipo || 'atrasados'; // <--- COMPATIBILIDADE

    let urlAlvo = '';
    let fetchOptions = {};

    // 3. Lógica baseada no TIPO de busca
    if (tipoBusca === 'atrasados') {
        // --- LÓGICA PARA ATRASADOS (EQUIVALENTE AO SEU PROXY ANTIGO) ---
        const loteriasPermitidas = ['fd', 'rj', 'lk', 'ln', 'ba']; // Verifique se estas são as corretas
        if (!loteriasPermitidas.includes(loteria)) {
            return response.status(400).send(`Erro: Loteria "${loteria}" inválida para busca de atrasados.`);
        }
        
        urlAlvo = 'https://bichocerto.com/estatisticas/atrasados/grupo/load/';
        
        // Constrói o corpo da requisição POST
        const postData = new URLSearchParams();
        postData.append('l', loteria);
        postData.append('p', '1'); // Parâmetros fixos como no seu proxy antigo
        postData.append('e', 'all');
        postData.append('et', 'Geral');

        fetchOptions = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                 // Adicione outros headers se necessário (referer, etc.)
            },
            body: postData.toString(),
        };

    } else if (tipoBusca === 'resultados') {
        // --- LÓGICA PARA RESULTADOS ---
        const mapaUrls = {
            'rj': 'https://bichocerto.com/resultados/rj/para-todos/',
            'lk': 'https://bichocerto.com/resultados/lk/look', 
            'fd': 'https://bichocerto.com/resultados/fd/loteria-federal', 
            'ln': 'https://bichocerto.com/resultados/ln/loteria-nacional' 
            // Adicione 'ba' aqui se tiver o URL
        };

        if (!mapaUrls[loteria]) {
            return response.status(400).send(`Erro: URL de resultados não definida para a loteria "${loteria}".`);
        }
        
        urlAlvo = mapaUrls[loteria];
        fetchOptions = {
            method: 'GET', // Resultados são buscados via GET
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                 // Adicione outros headers se necessário
            }
        };
        
    } else {
        // Se 'tipo' for enviado mas for inválido
        return response.status(400).send(`Erro: Tipo de busca "${tipoBusca}" inválido.`);
    }

    // 4. Faz a busca no site do bicho certo
    try {
        console.log(`Proxy Vercel: Buscando ${fetchOptions.method} ${urlAlvo}`); // Log para debug na Vercel
        const res = await fetch(urlAlvo, fetchOptions);
        
        if (!res.ok) {
            // Tenta ler a mensagem de erro do site de origem, se houver
            const errorBody = await res.text().catch(() => 'Não foi possível ler o corpo do erro.'); 
            console.error(`Erro ${res.status} ao buscar ${urlAlvo}: ${res.statusText}. Corpo: ${errorBody}`);
            throw new Error(`Erro ${res.status} ao acessar o site de origem (${res.statusText}).`);
        }
        
        const html = await res.text();
        
        // 5. Envia o HTML de volta para o frontend
        // O header 'Access-Control-Allow-Origin' já foi definido no início
        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        return response.status(200).send(html);

    } catch (error) {
        console.error("Erro crítico no proxy Vercel:", error); 
        // Envia a mensagem de erro capturada (que inclui o status HTTP se veio do fetch)
        return response.status(502).send(`Erro no proxy: ${error.message}`);
    }
}