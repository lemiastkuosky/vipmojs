// PROXY V6.4 - Novo Alvo: O Jogo do Bicho
const baseHeaders = {
    'User-Agent': 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language': 'pt-BR,pt;q=0.9',
    'Cache-Control': 'no-cache',
    'Pragma': 'no-cache'
};

export default async function handler(request, response) {
    response.setHeader('Access-Control-Allow-Origin', '*');
    response.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (request.method === 'OPTIONS') return response.status(200).end();

    try {
        const { tipo, loteria } = request.query;

        if (tipo === 'versao') return response.status(200).send("PROXY_V6.4_OJOGODOBICHO");

        let url_alvo = '';

        if (tipo === 'atrasados') {
            // Tentando o link direto de estatísticas do site oficial tradicional
            // loteria rj = rj, lk = look, etc.
            url_alvo = `https://www.ojogodobicho.com/estatisticas.asp?loteria=${loteria}`;
        } else {
            // Fallback para resultados se necessário
            url_alvo = `https://www.ojogodobicho.com/resultados.asp`;
        }

        const fetchResponse = await fetch(url_alvo, { 
            method: 'GET', 
            headers: baseHeaders,
            redirect: 'follow'
        });

        const html = await fetchResponse.text();
        
        // Se ainda assim o Cloudflare pegar, vamos avisar no log
        if (html.includes("Just a moment") || html.includes("cloudflare")) {
            console.error("🚫 Bloqueio Cloudflare detectado no novo alvo.");
        }

        response.setHeader('Content-Type', 'text/html; charset=iso-8859-1'); // O site antigo costuma usar esse charset
        response.status(200).send(html);

    } catch (error) {
        response.status(500).send("Erro no Proxy: " + error.message);
    }
}
