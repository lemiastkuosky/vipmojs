const axios = require('axios');
const cheerio = require('cheerio');

module.exports = async (req, res) => {
  try {
    // Vamos apenas espionar as variáveis da Vercel, sem ligar o Firebase
    const hasProjectId = !!process.env.FIREBASE_PROJECT_ID;
    const hasEmail = !!process.env.FIREBASE_CLIENT_EMAIL;
    
    // Pega só os primeiros 20 caracteres da chave para ver o que a Vercel leu
    const keyPreview = process.env.FIREBASE_PRIVATE_KEY 
      ? process.env.FIREBASE_PRIVATE_KEY.substring(0, 20) 
      : "CHAVE NAO ENCONTRADA";

    return res.status(200).json({
      status: "Diagnóstico Ativo",
      projeto_ok: hasProjectId,
      email_ok: hasEmail,
      inicio_da_chave: keyPreview,
      mensagem: "Se você está lendo isso, a Vercel e as bibliotecas estão perfeitas."
    });

  } catch (error) {
    return res.status(500).json({ erro_interno: error.message });
  }
};
