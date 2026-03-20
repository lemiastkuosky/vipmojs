const axios = require('axios');
const cheerio = require('cheerio');
const admin = require('firebase-admin');

module.exports = async (req, res) => {
  try {
    // 1. Inicializa o Firebase
    if (!admin.apps.length) {
      if (!process.env.FIREBASE_PRIVATE_KEY) {
        throw new Error("Chave do Firebase não encontrada.");
      }
      
      admin.initializeApp({
        credential: admin.credential.cert({
          projectId: process.env.FIREBASE_PROJECT_ID,
          clientEmail: process.env.FIREBASE_CLIENT_EMAIL,
          // Garante a leitura das quebras de linha da private key
          privateKey: process.env.FIREBASE_PRIVATE_KEY.replace(/\\n/g, '\n'),
        }),
      });
    }

    const db = admin.firestore();

    // 2. Scraping da Investing
    const { data } = await axios.get('https://www.investing.com/economic-calendar/', {
      headers: { 'User-Agent': 'Mozilla/5.0' }
    });
    
    const $ = cheerio.load(data);
    const newsFound = [];

    $('#economicCalendarData tbody tr').each((i, el) => {
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

    // 4. Retorno
    return res.status(200).json({ 
      status: "Sucesso!", 
      noticias_salvas: newsFound.length 
    });

  } catch (error) {
    return res.status(500).json({ 
      erro: "Falha na execução", 
      detalhe: error.message 
    });
  }
};
