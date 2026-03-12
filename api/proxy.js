// PROXY V6.8 - TESTE PORTAL BRASIL
export default async function handler(request, response) {
    response.setHeader('Access-Control-Allow-Origin', '*');
    const { tipo, loteria, data } = request.query;

    if (tipo === 'versao') return response.status(200).send("PROXY_V6.8_PORTALBRASIL");

    const mapa = {
        'rj': 'https://bichocerto.com/resultados/rj/para-todos',
        'lk': 'https://portalbrasil.net/jogodobicho/goias/', // Nova fonte para Goiás/Look
        'fd': 'https://bichocerto.com/resultados/fd/loteria-federal',
        'ln': 'https://www.investeloto.com.br/resultados/nacional.php',
        'ba': 'https://bichocerto.com/resultados/ba/para-todos'
    };

    let url_alvo = mapa[loteria] || '';

    try {
        const res = await fetch(url_alvo, {
            headers: { 
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            }
        });

        const html = await res.text();

        if (!html || html.length < 1000) {
             return response.status(200).send("⚠️ Conteúdo insuficiente ou bloqueado.");
        }

        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        return response.status(200).send(html);

    } catch (error) {
        return response.status(500).send("Erro: " + error.message);
    }
}
