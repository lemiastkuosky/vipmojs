// PROXY V6.9 - ULTRA STEALTH (Portal Brasil + Referer)
export default async function handler(request, response) {
    response.setHeader('Access-Control-Allow-Origin', '*');
    const { tipo, loteria } = request.query;

    if (tipo === 'versao') return response.status(200).send("PROXY_V6.9_STEALTH");

    const mapa = {
        'rj': 'https://bichocerto.com/resultados/rj/para-todos',
        'lk': 'https://portalbrasil.net/jogodobicho/goias/',
        'fd': 'https://bichocerto.com/resultados/fd/loteria-federal',
        'ln': 'https://www.investeloto.com.br/resultados/nacional.php'
    };

    const url_alvo = mapa[loteria] || '';

    try {
        const res = await fetch(url_alvo, {
            headers: { 
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language': 'pt-BR,pt;q=0.9',
                'Referer': url_alvo, // Engana o site dizendo que você já está na página
                'Cache-Control': 'no-cache'
            }
        });

        const html = await res.text();

        // Se o HTML for muito curto, o site nos bloqueou
        if (html.length < 500) {
            return response.status(200).send(`❌ BLOQUEIO: O site enviou apenas ${html.length} caracteres. Conteúdo: ${html}`);
        }

        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        return response.status(200).send(html);

    } catch (error) {
        return response.status(500).send("Erro técnico: " + error.message);
    }
}
