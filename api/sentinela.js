import axios from 'axios';
import * as cheerio from 'cheerio';
import admin from 'firebase-admin';

export default async function handler(req, res) {
  try {
    // 1. Inicializa o Firebase DENTRO do try/catch para não derrubar o servidor
    if (!admin.apps.length) {
      if (!process.env.FIREBASE_PRIVATE_KEY) {
        throw new Error("Chave do Firebase não encontrada nas variáveis da Vercel.");
      }
      
      admin.initializeApp({
        credential: admin.credential.cert({
          projectId: process.env.FIREBASE_PROJECT_ID,
          clientEmail: process.env.FIREBASE_CLIENT_EMAIL,
          // O replace garante que as quebras de linha funcionem
          privateKey: process.env.FIREBASE_PRIVATE_KEY.replace(/\\n/g, '\n'),
        }),
      });
    }

    const db = admin.firestore();

    // 2. Faz o Scraping da Investing
    const { data } = await axios.get('https://www.investing.com/economic-calendar/', {
      headers: { 'User-Agent': 'Mozilla/5.0' }
    });
    
    const $ = cheerio.load(data);
    const newsFound = [];

    $('#economicCalendarData tbody tr').each((i, el) => {
      // Pega só os 3 touros (alto impacto)
      const impactStars = $(el).find('.sentiment > i.grayFullBullishIcon').length;
      if (impactStars === 3) {
        newsFound.push({
          time: $(el).find('.time').text().trim(),
          currency: $(el).find('.left.flagCur').text().trim(),
          event: $(el).find('.event').text().trim(),
          updatedAt: new Date().toISOString()
        });
      }
    });

    // 3. Salva no Firebase
    if (newsFound.length > 0) {
      const batch = db.batch();
      newsFound.forEach(news => {
        const docId = `${news.currency}_${news.time}_${news.event.replace(/\s+/g, '_')}`;
        batch.set(db.collection('noticias_impacto').doc(docId), news);
      });
      await batch.commit();
    }

    // 4. Retorna o sucesso!
    return res.status(200).json({ 
      status: "Sucesso Absoluto!", 
      noticias_salvas: newsFound.length,
      dados: newsFound 
    });

  } catch (error) {
    // Se algo der errado, agora ele te conta o que foi:
    return res.status(500).json({ 
      erro: "Deu ruim em alguma etapa", 
      detalhe: error.message 
    });
  }
}