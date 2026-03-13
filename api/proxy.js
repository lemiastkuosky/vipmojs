// PROXY V7.2 - UNIFICADO PORTAL BRASIL
export default async function handler(request, response) {
    response.setHeader('Access-Control-Allow-Origin', '*');
    const { tipo, loteria, data } = request.query;

    if (tipo === 'versao') return response.status(200).send("PROXY_V7.2_FINAL");

    const mapaBancas = {
        'rj': 'resultado-do-jogo-do-bicho', // Página principal do Rio
        'lk': 'goias',
        'ba': 'bahia',
        'fd': 'resultado-do-jogo-do-bicho', // Federal também fica na principal
        'ln': 'nacional' // Tentativa para Nacional
    };

    const bancaSlug = mapaBancas[loteria] || 'resultado-do-jogo-do-bicho';
    let url_alvo = `https://portalbrasil.net/jogodobicho/${bancaSlug}/`;

    // Filtro de data para o histórico do Portal Brasil
    if (data && /^\d{4}-\d{2}-\d{2}$/.test(data)) {
        const [ano, mes, dia] = data.split('-');
        url_alvo += `resultado-do-dia-${dia}-${mes}-${ano}/`;
    }

    // Se a Nacional der erro no Portal Brasil, usamos o Investeloto como backup
    if (loteria === 'ln' && !data) {
        url_alvo = 'https://www.investeloto.com.br/resultados/nacional.php';
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
