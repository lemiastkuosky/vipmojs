export default async function handler(request, response) {
    response.setHeader('Access-Control-Allow-Origin', '*');
    const { tipo, loteria, data } = request.query;

    if (tipo === 'versao') return response.status(200).send("PROXY_V6.5_DEBUG");

    // Mapa de URLs Corrigido para o formato do Resultado Fácil
    const mapa = {
        'rj': 'https://bichocerto.com/resultados/rj/para-todos',
        'lk': 'https://bichocerto.com/resultados/lk/look',
        'fd': 'https://bichocerto.com/resultados/fd/loteria-federal',
        'ba': 'https://bichocerto.com/resultados/ba/para-todos',
        'ln': 'https://www.resultadofacil.com.br/resultados-loteria-nacional-de-hoje' // URL principal
    };

    let url_alvo = mapa[loteria] || '';

    // Se tiver data e for Nacional, tentamos o link do histórico
    if (data && loteria === 'ln') {
        url_alvo = `https://www.resultadofacil.com.br/resultado-do-jogo-do-bicho/nacional/do-dia/${data}`;
    }

    try {
        console.log("Tentando acessar:", url_alvo);
        
        const res = await fetch(url_alvo, {
            headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36' }
        });

        const texto = await res.text();

        if (!texto || texto.length < 100) {
            return response.status(200).send(`⚠️ ALERTA: O site respondeu, mas o conteúdo veio vazio. Status: ${res.status}`);
        }

        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        return response.status(200).send(texto);

    } catch (error) {
        return response.status(200).send(`❌ ERRO DE CONEXÃO: Não foi possível alcançar o site. Detalhe: ${error.message}`);
    }
}
