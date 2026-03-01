// PROXY V6.2 - Nativo (Sem dependências e compatível com seu package.json)
const baseHeaders = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
    'Accept-Language': 'pt-BR,pt;q=0.9',
};

export default async function handler(request, response) {
    // Configuração de CORS para permitir que o index.html acesse o proxy
    response.setHeader('Access-Control-Allow-Origin', '*');
    response.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (request.method === 'OPTIONS') return response.status(200).end();

    try {
        const { tipo, loteria, data } = request.query;

        if (tipo === 'versao') return response.status(200).send("PROXY_V6.2_NATIVO");

        let url_alvo = '';
        let options = { method: 'GET', headers: baseHeaders };

        if (tipo === 'atrasados') {
            url_alvo = 'https://bichocerto.com/estatisticas/atrasados/grupo/load/';
            options.method = 'POST';
            options.headers = {
                ...baseHeaders,
                'Content-Type': 'application/x-www-form-urlencoded',
                'Referer': 'https://bichocerto.com/estatisticas/atrasados/grupo/'
            };
            options.body = new URLSearchParams({ 'l': loteria, 'p': '1', 'e': 'all', 'et': 'Geral' }).toString();

        } else if (tipo === 'resultados') {
            const mapa = {
                'rj': 'https://bichocerto.com/resultados/rj/para-todos',
                'lk': 'https://bichocerto.com/resultados/lk/look',
                'fd': 'https://bichocerto.com/resultados/fd/loteria-federal',
                'ln': 'https://bichocerto.com/resultados/ln/loteria-nacional',
                'ba': 'https://bichocerto.com/resultados/ba/para-todos'
            };

            if (!mapa[loteria]) return response.status(400).send('Loteria inválida');
            url_alvo = mapa[loteria];

            // Lógica inteligente para data da Federal
            if (data && /^\d{4}-\d{2}-\d{2}$/.test(data)) {
                if (loteria === 'fd') {
                    const [y, m, d] = data.split('-').map(Number);
                    const diaSemana = new Date(y, m - 1, d).getDay(); // 3=Quarta, 6=Sábado
                    if (diaSemana !== 3 && diaSemana !== 6) {
                        return response.status(200).send('<html><body>Sem sorteio programado.</body></html>');
                    }
                    url_alvo += `/${data}/`;
                } else {
                    url_alvo += '/'; // Outras loterias pegam sempre o dia atual
                }
            } else {
                url_alvo += loteria === 'fd' ? '/de-hoje/' : '/';
            }
        }

        // Fetch nativo do Node.js 18+ (Padrão Vercel)
        const fetchResponse = await fetch(url_alvo, options);
        const html = await fetchResponse.text();
        
        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        response.status(200).send(html);

    } catch (error) {
        console.error("Erro no Proxy:", error);
        response.status(500).send("Erro interno no proxy: " + error.message);
    }
}
