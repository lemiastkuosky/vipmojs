import axios from 'axios';
import * as cheerio from 'cheerio';
import admin from 'firebase-admin';

// Inicializa Firebase com as variáveis que você colocou na Vercel
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
        newsFound.push({
          time: $(el).find('.time').text().trim(),
          currency: $(el).find('.left.flagCur').text().trim(),
          event: $(el).find('.event').text().trim(),
          updatedAt: new Date().toISOString()
        });
      }
    });

    // Grava no Firestore (Batch para performance)
    const batch = db.batch();
    newsFound.forEach(news => {
      const docId = `${news.currency}_${news.time}_${news.event.replace(/\s+/g, '_')}`;
      batch.set(db.collection('noticias_impacto').doc(docId), news);
    });
    await batch.commit();

    return res.status(200).json({ status: "Operacional", news_count: newsFound.length });
  } catch (error) {
    return res.status(500).json({ error: error.message });
  }
}