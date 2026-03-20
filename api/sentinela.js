import axios from 'axios';
import * as cheerio from 'cheerio';
import admin from 'firebase-admin';

// Inicialização do Firebase (evita inicializar múltiplas vezes)
if (!admin.apps.length) {
  admin.initializeApp({
    credential: admin.credential.cert({
      projectId: process.env.FIREBASE_PROJECT_ID,
      clientEmail: process.env.FIREBASE_CLIENT_EMAIL,
      privateKey: process.env.FIREBASE_PRIVATE_KEY.replace(/\\n/g, '\n'),
    }),
  });
}

const db = admin.firestore();

export default async function handler(req, res) {
  try {
    const { data } = await axios.get('https://www.investing.com/economic-calendar/', {
      headers: { 'User-Agent': 'Mozilla/5.0' }
    });
    
    const $ = cheerio.load(data);
    const newsFound = [];

    $('#economicCalendarData tbody tr').each((i, el) => {
      const impactStars = $(el).find('.sentiment > i.grayFullBullishIcon').length;
      
      if (impactStars === 3) {
        const item = {
          time: $(el).find('.time').text().trim(),
          currency: $(el).find('.left.flagCur').text().trim(),
          event: $(el).find('.event').text().trim(),
          updatedAt: new Date().toISOString()
        };
        newsFound.push(item);
      }
    });

    // Gravação no Firestore
    const batch = db.batch();
    newsFound.forEach(news => {
      const docId = `${news.currency}_${news.time}_${news.event.replace(/\s+/g, '_')}`;
      const docRef = db.collection('noticias_impacto').doc(docId);
      batch.set(docRef, news);
    });
    await batch.commit();

    return res.status(200).json({ success: true, count: newsFound.length });
  } catch (error) {
    return res.status(500).json({ error: error.message });
  }
}