// api/proxy.js - V7.3 FINAL MULTI-SITE
export default async function handler(request, response) {
    response.setHeader('Access-Control-Allow-Origin', '*');
    const { tipo, loteria, data } = request.query;
    if (tipo === 'versao') return response.status(200).send("PROXY_V7.3_FINAL");

    const mapaPortalBrasil = {
        'rj': 'resultado-do-jogo-do-bicho',
        'lk': 'goias',
        'ba': 'bahia',
        'fd': 'federal'
    };

    let url_alvo = '';

    // LÓGICA PARA NACIONAL (RESULTADO FÁCIL)
    if (loteria === 'ln') {
        url_alvo = 'https://www.resultadofacil.com.br/resultados-da-banca-loteria-nacional';
        if (data && /^\d{4}-\d{2}-\d{2}$/.test(data)) {
            url_alvo = `https://www.resultadofacil.com.br/resultado-do-jogo-do-bicho/nacional/do-dia/${data}`;
        }
    } 
    // LÓGICA PARA AS OUTRAS (PORTAL BRASIL)
    else {
        const slug = mapaPortalBrasil[loteria] || 'resultado-do-jogo-do-bicho';
        url_alvo = `https://portalbrasil.net/jogodobicho/${slug}/`;
        if (data && /^\d{4}-\d{2}-\d{2}$/.test(data)) {
            const [ano, mes, dia] = data.split('-');
            url_alvo += `resultado-do-dia-${dia}-${mes}-${ano}/`;
        }
    }

    try {
        const res = await fetch(url_alvo, {
            headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'Referer': 'https://portalbrasil.net/' }
        });
        const html = await res.text();
        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        return response.status(200).send(html);
    } catch (error) {
        return response.status(500).send("Erro: " + error.message);
    }
}
