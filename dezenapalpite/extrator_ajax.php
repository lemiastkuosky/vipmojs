import fetch from 'node-fetch';
import cheerio from 'cheerio';

export default async function handler(request, response) {
    // 1. Configuração de CORS (Permite que seu site acesse essa API)
    response.setHeader('Access-Control-Allow-Origin', '*');
    response.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (request.method === 'OPTIONS') {
        return response.status(200).end();
    }

    // 2. Recebe a sigla da loteria (ex: rj, pt, lk)
    const { loteria } = request.query;

    if (!loteria) {
        return response.status(400).json({ erro: 'Parâmetro "loteria" é obrigatório.' });
    }

    // =================================================================================
    // CONFIGURAÇÃO 1: Mapeamento de Loterias e URLs
    // COLOQUE AQUI AS URLS DOS SITES QUE VOCÊ VAI USAR
    // =================================================================================
    const URLS_FONTE = {
        'rj': 'https://www.ojogodobicho.com/deu_no_poste.htm', // Exemplo (muito usado)
        'pt': 'https://www.ojogodobicho.com/deu_no_poste.htm', // Muitas vezes é o mesmo site
        'lk': 'https://site-da-look-aqui.com/resultados',     // Substitua pela URL real da Look
        'federal': 'https://www.ojogodobicho.com/federal.htm'
        // Adicione outras siglas conforme seu frontend envia
    };

    const targetUrl = URLS_FONTE[loteria.toLowerCase()];

    if (!targetUrl) {
        return response.status(404).json({ erro: `Loteria "${loteria}" não configurada no sistema.` });
    }

    try {
        console.log(`[Proxy] Buscando dados de: ${targetUrl}`);

        // 3. Faz a requisição ao site externo (Finge ser um navegador Chrome)
        const fetchResponse = await fetch(targetUrl, {
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'
            }
        });

        if (!fetchResponse.ok) {
            throw new Error(`O site fonte retornou erro: ${fetchResponse.status}`);
        }

        const html = await fetchResponse.text();
        
        // 4. Carrega o HTML no Cheerio para ler os dados
        const $ = cheerio.load(html);
        let dadosExtraidos = [];
        let dataExtracao = '';

        // =================================================================================
        // CONFIGURAÇÃO 2: Lógica de Extração (O CORAÇÃO DO SISTEMA)
        // Isso aqui depende 100% de como o site fonte é feito.
        // O exemplo abaixo funciona para sites que usam TABELAS HTML simples.
        // =================================================================================
        
        // Tenta achar a data (geralmente está num <h3>, <h4> ou <caption>)
        // Ajuste o seletor 'caption' ou 'h3' conforme o site
        dataExtracao = $('caption').first().text().trim() || $('h3').first().text().trim() || new Date().toLocaleDateString('pt-BR');

        // Procura todas as tabelas e tenta ler as linhas
        // DICA: Use "Inspecionar Elemento" no navegador para ver se é 'table', 'div.resultado', etc.
        $('table').each((i, tabela) => {
            // Se já pegamos dados suficientes (ex: 7 prêmios), paramos
            if (dadosExtraidos.length >= 7) return;

            // Percorre as linhas da tabela
            $(tabela).find('tr').each((j, linha) => {
                const colunas = $(linha).find('td');
                
                // Validação básica: Precisa ter pelo menos Prêmio e Número
                if (colunas.length >= 2) {
                    const premioRaw = $(colunas[0]).text().trim().toLowerCase();
                    const numero = $(colunas[1]).text().trim();
                    const bicho = colunas.length > 2 ? $(colunas[2]).text().trim() : ''; // Opcional

                    // Verifica se a primeira coluna parece um prêmio (1º, 2º, etc)
                    if (premioRaw.includes('º') || premioRaw.match(/^\d/)) {
                        dadosExtraidos.push({
                            premio: premioRaw,
                            numero: numero,
                            grupo: bicho // Às vezes o bicho vem junto, às vezes não
                        });
                    }
                }
            });
        });

        // =================================================================================
        // FIM DA LÓGICA DE EXTRAÇÃO
        // =================================================================================

        if (dadosExtraidos.length === 0) {
            console.warn(`[Proxy] Atenção: Nenhum dado encontrado. O layout do site ${targetUrl} pode ter mudado.`);
            return response.status(200).json({ 
                sucesso: false, 
                mensagem: 'Não foi possível ler os resultados. O site fonte pode ter mudado o layout.',
                dados: [] 
            });
        }

        console.log(`[Proxy] Sucesso! ${dadosExtraidos.length} linhas extraídas.`);

        // 5. Retorna o JSON limpo para o seu frontend
        return response.status(200).json({
            sucesso: true,
            loteria: loteria,
            fonte: targetUrl,
            data: dataExtracao,
            resultados: dadosExtraidos
        });

    } catch (error) {
        console.error('[Proxy] Erro fatal:', error);
        return response.status(500).json({ 
            sucesso: false, 
            erro: error.message 
        });
    }
}