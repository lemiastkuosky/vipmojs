// PROXY V6.3 - Stealth (Anti-Cloudflare)
// Sem dependências e com disfarce avançado de navegador
const baseHeaders = {
    'User-Agent': 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'X-Forwarded-For': '66.249.66.1' // Finge ser um IP do Google
};


export default async function handler(request, response) {
    response.setHeader('Access-Control-Allow-Origin', '*');
    response.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (request.method === 'OPTIONS') return response.status(200).end();

    try {
        const { tipo, loteria, data } = request.query;

        if (tipo === 'versao') return response.status(200).send("PROXY_V6.3_STEALTH");

        let url_alvo = '';
        let options = { method: 'GET', headers: baseHeaders };

        if (tipo === 'atrasados') {
            url_alvo = 'https://bichocerto.com/estatisticas/atrasados/grupo/load/';
            options.method = 'POST';
            options.headers = {
                ...baseHeaders,
                'Content-Type': 'application/x-www-form-urlencoded',
                'Referer': 'https://bichocerto.com/estatisticas/atrasados/grupo/',
                'Origin': 'https://bichocerto.com',
                'Sec-Fetch-Site': 'same-origin'
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

            if (data && /^\d{4}-\d{2}-\d{2}$/.test(data)) {
                if (loteria === 'fd') {
                    const [y, m, d] = data.split('-').map(Number);
                    const diaSemana = new Date(y, m - 1, d).getDay(); 
                    if (diaSemana !== 3 && diaSemana !== 6) {
                        return response.status(200).send('<html><body>Sem sorteio programado.</body></html>');
                    }
                    url_alvo += `/${data}/`;
                } else {
                    url_alvo += '/'; 
                }
            } else {
                url_alvo += loteria === 'fd' ? '/de-hoje/' : '/';
            }
        }

        const fetchResponse = await fetch(url_alvo, options);
        const html = await fetchResponse.text();
        
        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        response.status(200).send(html);

    } catch (error) {
        console.error("Erro no Proxy:", error);
        response.status(500).send("Erro interno no proxy: " + error.message);
    }
}

