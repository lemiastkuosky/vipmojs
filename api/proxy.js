// PROXY V7.0 - UNIFICADO PORTAL BRASIL
export default async function handler(request, response) {
    response.setHeader('Access-Control-Allow-Origin', '*');
    const { tipo, loteria, data } = request.query;

    if (tipo === 'versao') return response.status(200).send("PROXY_V7.0_FINAL");

    const mapaBancas = {
        'rj': 'rio-de-janeiro',
        'lk': 'goias',
        'fd': 'federal',
        'ba': 'bahia',
        'ln': 'nacional'
    };

    const bancaSlug = mapaBancas[loteria] || 'rio-de-janeiro';
    let url_alvo = `https://portalbrasil.net/jogodobicho/${bancaSlug}/`;

    // Se houver data, o Portal Brasil usa o formato: .../resultado-do-dia-DD-MM-YYYY/
    if (data && /^\d{4}-\d{2}-\d{2}$/.test(data)) {
        const [ano, mes, dia] = data.split('-');
        url_alvo += `resultado-do-dia-${dia}-${mes}-${ano}/`;
    }

    try {
        const res = await fetch(url_alvo, {
            headers: { 
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Referer': 'https://portalbrasil.net/'
            }
        });

        const html = await res.text();
        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        return response.status(200).send(html);

    } catch (error) {
        return response.status(500).send("Erro: " + error.message);
    }
}
