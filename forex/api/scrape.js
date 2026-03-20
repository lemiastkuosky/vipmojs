import { db } from '../lib/firebase'; // Sua config do firebase
import axios from 'axios';
import * as cheerio from 'cheerio';

export default async function handler(req, res) {
  // 1. Faz o Scraping (mesma lógica que criamos antes)
  const { data } = await axios.get('https://www.investing.com/economic-calendar/');
  const $ = cheerio.load(data);
  
  const news = [];
  // ... lógica do cheerio para filtrar 3 touros ...

  // 2. Salva no Firestore
  // Usamos o Firebase para que o dado fique disponível para o MT5
  const batch = db.batch();
  news.forEach(item => {
    const ref = db.collection('noticias').doc(`${item.currency}_${item.time}`);
    batch.set(ref, item);
  });
  await batch.commit();

  return res.status(200).json({ success: true, count: news.length });
}