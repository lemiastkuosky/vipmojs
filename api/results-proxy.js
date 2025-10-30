// Importa as bibliotecas necessárias
import fetch from 'node-fetch'; // Para buscar o HTML da página
import cheerio from 'cheerio'; // Para "ler" e extrair dados do HTML

// A função principal que a Vercel executará
export default async function handler(request, response) {
    // 1. Pega o parâmetro 'loteria' da URL (?loteria=rj)
    const { loteria } = request.query;

    if (!loteria) {
        return response.status(400).send('Erro: Parâmetro "loteria" não fornecido.');
    }

    // 2. Mapeia a sigla da loteria para a URL do site de resultados
    // !!! IMPORTANTE: SUBSTITUA PELAS URLs REAIS DO SITE QUE VOCÊ VAI USAR !!!
    const urls = {
        'rj': 'URL_DO_SITE_DE_RESULTADOS_PARA_RJ',
        'lk': 'URL_DO_SITE_DE_RESULTADOS_PARA_LK',
        'fd': 'URL_DO_SITE_DE_RESULTADOS_PARA_FD',
        'ln': 'URL_DO_SITE_DE_RESULTADOS_PARA_LN',
        'ba': 'URL_DO_SITE_DE_RESULTADOS_PARA_BA',
        // Adicione outras loterias conforme necessário
    };

    const targetUrl = urls[loteria.toLowerCase()];

    if (!targetUrl) {
        return response.status(400).send(`Erro: Loteria "${loteria}" não configurada no proxy.`);
    }

    try {
        // 3. Busca o HTML do site de resultados
        console.log(`Buscando resultados para ${loteria} em: ${targetUrl}`);
        const fetchResponse = await fetch(targetUrl, {
            // Alguns sites podem exigir um User-Agent para parecer um navegador real
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            }
        });

        if (!fetchResponse.ok) {
            throw new Error(`Erro ao buscar a página: ${fetchResponse.status} ${fetchResponse.statusText}`);
        }

        const html = await fetchResponse.text();
        console.log(`HTML recebido para ${loteria}. Tamanho: ${html.length}`);

        // 4. Usa o Cheerio para carregar o HTML
        const $ = cheerio.load(html);

        // 5. Extrai os dados (ESTA É A PARTE QUE MAIS PRECISA DE ADAPTAÇÃO)
        //    Você precisa inspecionar o HTML do site alvo e encontrar os seletores corretos.
        //    Este é um EXEMPLO HIPOTÉTICO.
        let resultadosTexto = '';
        let dataHoraExtracao = ''; // Tente encontrar a data/hora também

        // Exemplo: Tentando encontrar uma tabela e extrair linhas (AJUSTE CONFORME O SITE REAL)
        // Suponha que os resultados estejam em uma tabela com a classe 'result-table'
        const linhasTabela = $('table.result-table tbody tr'); // Exemplo de seletor CSS

        if (linhasTabela.length > 0) {
            console.log(`Encontradas ${linhasTabela.length} linhas na tabela.`);
            linhasTabela.each((index, element) => {
                const colunas = $(element).find('td');
                if (colunas.length >= 3) { // Supondo 3 colunas: Prêmio, Número, Grupo/Bicho
                    const premio = $(colunas[0]).text().trim(); // Ex: 1º Prêmio
                    const numero = $(colunas[1]).text().trim(); // Ex: 1234
                    const grupoBicho = $(colunas[2]).text().trim(); // Ex: (Grupo 09 - Cobra)

                    // Formata a linha como a função `extrairResultadosDoTexto` espera
                    resultadosTexto += `${premio}: ${numero} ${grupoBicho}\n`;
                }
            });
             // Tentar pegar data/hora de algum lugar (ex: um <h4> antes da tabela)
             dataHoraExtracao = $('h4.data-extracao').first().text().trim(); // Exemplo
        } else {
             console.warn("Nenhuma linha encontrada com o seletor 'table.result-table tbody tr'. A estrutura do site pode ser diferente.");
             // Tente outros seletores se a tabela não funcionar
             // Ex: $('.resultado-item').each(...)
             // Se nada for encontrado, retorne uma mensagem indicando isso
             if(!resultadosTexto) {
                 resultadosTexto = "Erro: Não foi possível encontrar os dados dos resultados na página. A estrutura do site pode ter mudado.";
             }
        }


        console.log(`Dados extraídos (antes de enviar):\n${resultadosTexto}`);

        // 6. Retorna os dados como texto simples
        // Define o cabeçalho para indicar que é texto
        response.setHeader('Content-Type', 'text/plain; charset=utf-8');
        // Adiciona a data/hora no início, se encontrada
        const respostaFinal = dataHoraExtracao ? `Data/Hora: ${dataHoraExtracao}\n\n${resultadosTexto.trim()}` : resultadosTexto.trim();
        return response.status(200).send(respostaFinal);

    } catch (error) {
        console.error(`Erro no proxy para ${loteria}:`, error);
        // Retorna uma mensagem de erro clara para o frontend
        return response.status(500).send(`Erro no servidor proxy: ${error.message}`);
    }
}