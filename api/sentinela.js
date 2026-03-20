import axios from 'axios';
import * as cheerio from 'cheerio';
import admin from 'firebase-admin';

export default async function handler(req, res) {
  try {
    // 1. Validando as chaves antes de conectar
    if (!process.env.FIREBASE_PROJECT_ID || !process.env.FIREBASE_PRIVATE_KEY) {
      throw new Error("As variáveis de ambiente do Firebase não estão configuradas na Vercel.");
    }

    // 2. Conectando no Firebase
    if (!admin.apps.length) {
      admin.initializeApp({
        credential: admin.credential.cert({
          projectId: process.env.FIREBASE_PROJECT_ID,
          clientEmail: process.env.FIREBASE_CLIENT_EMAIL,
          // Garante que as quebras de linha sejam lidas corretamente
          privateKey: process.env.FIREBASE_PRIVATE_KEY.replace(/\\n/g, '\n'),
        }),
      });
    }

    const db = admin.firestore();

    // 3. Testando o Scraping
    const { data } = await axios.get('https://www.investing.com/economic-calendar/', {
      headers: { 
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8'
      }
    });

    return res.status(200).json({ status: "Sucesso!", message: "Conectou no Firebase e leu o site." });

  } catch (error) {
    // 4. Se der erro, joga na tela em formato JSON!
    return res.status(500).json({
      erro_principal: error.message,
      detalhes: error.response ? error.response.statusText : "Sem detalhes do Axios",
      dica: "Copie esse erro e mande para a gente debugar"
    });
  }
}