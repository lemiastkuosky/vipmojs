import axios from 'axios';
import * as cheerio from 'cheerio';
// Se for usar Firebase, importe aqui também

export default async function handler(req, res) {
  // 1. Coloque aqui toda a lógica de scraping que criamos
  // 2. No final, em vez de apenas console.log, você responde ao request:
  
  try {
    const data = await seuMetodoDeScraping(); 
    res.status(200).json({ success: true, news: data });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
}

async function checkHighImpactNews() {
    console.log("🔍 Monitorando o calendário econômico...");

    try {
        // Usamos um User-Agent para não sermos bloqueados pelo site
        const { data } = await axios.get('https://www.investing.com/economic-calendar/', {
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            }
        });

        const $ = cheerio.load(data);
        const newsFound = [];

        // No Investing, as linhas de notícias ficam em #economicCalendarData
        $('#economicCalendarData tbody tr').each((i, el) => {
            const time = $(el).find('.time').text().trim();
            const currency = $(el).find('.left.flagCur').text().trim();
            const impactStars = $(el).find('.sentiment > i.grayFullBullishIcon').length;
            const event = $(el).find('.event').text().trim();

            // Filtramos apenas notícias de ALTO IMPACTO (3 estrelas/touros)
            if (impactStars === 3) {
                newsFound.push({ time, currency, event });
            }
        });

        if (newsFound.length > 0) {
            console.table(newsFound);
            console.log("⚠️ ATENÇÃO: Evite operar próximo a esses horários!");
        } else {
            console.log("✅ Nenhuma notícia de alto impacto para agora.");
        }

    } catch (error) {
        console.error("❌ Erro ao buscar notícias:", error.message);
    }
}

// Executa a cada 1 minuto (ou o tempo que você preferir)
checkHighImpactNews();
setInterval(checkHighImpactNews, 60000);