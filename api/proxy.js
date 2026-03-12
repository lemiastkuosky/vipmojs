// PROXY V6.4 - Multi-Source Stealth
// Configurado para: Bicho Certo (RJ, LOOK, FD, BA) e Resultado Fácil (LN)

const baseHeaders = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'Accept-Language': 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
    'Cache-Control': 'max-age=0',
    'Connection': 'keep-alive'
};

export default async function handler(request, response) {
    // Configuração de CORS
    response.setHeader('Access-Control-Allow-Origin', '*');
    response.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (request.method === 'OPTIONS') {
        return response.status(200).end();
    }

    try {
        const { tipo, loteria, data } = request.query;

        // Teste de vida do Proxy
        if (tipo === 'versao') {
            return response.status(200).send("PROXY_V6.4_MULTI_SOURCE");
        }

        let url_alvo = '';
        let options = { 
            method: 'GET', 
            headers: baseHeaders 
        };

        // --- LÓGICA PARA ESTATÍSTICAS (ATRASADOS) ---
        if (tipo === 'atrasados') {
            url_alvo = 'https://bichocerto.com/estatisticas/atrasados/grupo/load/';
            options.method = 'POST';
            options.headers = {
                ...baseHeaders,
                'Content-Type': 'application/x-www-form-urlencoded',
                'Referer': 'https://bichocerto.com/estatisticas/atrasados/grupo/',
                'Origin': 'https://bichocerto.com'
            };
            options.body = new URLSearchParams({ 
                'l': loteria, 
                'p': '1', 
                'e': 'all', 
                'et': 'Geral' 
            }).toString();

        // --- LÓGICA PARA RESULTADOS ---
        } else if (tipo === 'resultados') {
            const mapa = {
                'rj': 'https://bichocerto.com/resultados/rj/para-todos',
                'lk': 'https://bichocerto.com/resultados/lk/look',
                'fd': 'https://bichocerto.com/resultados/fd/loteria-federal',
                'ba': 'https://bichocerto.com/resultados/ba/para-todos',
                'ln': 'https://www.resultadofacil.com.br/resultados-da-banca-loteria-nacional' // Fonte Alternativa
            };

            if (!mapa[loteria]) {
                return response.status(400).send('Loteria inválida');
            }

            url_alvo = mapa[loteria];

            // Tratamento de Data/Filtro
            if (data && /^\d{4}-\d{2}-\d{2}$/.test(data)) {
                if (loteria === 'ln') {
                    // Formato Resultado Fácil: .../loteria-nacional/do-dia/YYYY-MM-DD
                    url_alvo += `/do-dia/${data}`;
                } else if (loteria === 'fd') {
                    url_alvo += `/${data}/`;
                } else {
                    // Para as outras, o Bicho Certo geralmente não usa data na URL da mesma forma
                    url_alvo += '/';
                }
            } else {
                // URLs padrão sem data
                if (loteria === 'fd') url_alvo += '/de-hoje/';
                else url_alvo += '/';
            }
        }

        // Executa a busca
        const fetchResponse = await fetch(url_alvo, options);
        const html = await fetchResponse.text();
        
        // Retorna o HTML para o seu site
        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        response.status(200).send(html);

    } catch (error) {
        console.error("Erro no Proxy:", error);
        response.status(500).send("Erro interno no proxy: " + error.message);
    }
}
