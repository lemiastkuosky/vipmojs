// Arquivo: api/proxy.js
import axios from 'axios';
import * as cheerio from 'cheerio';

export default async function handler(req, res) {
    res.setHeader('Access-Control-Allow-Credentials', true);
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET,OPTIONS');

    if (req.method === 'OPTIONS') {
        res.status(200).end();
        return;
    }

    // Pega o parâmetro que seu site está enviando (loteria=rj, loteria=pt, etc)
    const { loteria } = req.query;

    // Lógica simples para definir a URL baseada no parâmetro
    let urlAlvo = '';
    
    // AQUI VOCÊ COLOCA O SITE REAL DE ONDE VAI TIRAR OS DADOS
    // Exemplo fictício, substitua pelos links reais que você quer ler:
    if (loteria === 'rj') urlAlvo = 'https://site-da-loteria.com/rj';
    if (loteria === 'pt') urlAlvo = 'https://site-da-loteria.com/pt';
    if (loteria === 'federal') urlAlvo = 'https://site-da-loteria.com/federal';

    if (!urlAlvo) {
        // Se não mandou 'loteria', tenta ver se mandou uma 'url' direta
        if (req.query.url) {
            urlAlvo = req.query.url;
        } else {
            return res.status(400).json({ error: 'Parâmetro loteria ou url não informado' });
        }
    }

    try {
        const response = await axios.get(urlAlvo);
        const html = response.data;
        
        // Retorna o HTML para seu site processar
        return res.status(200).json({ html: html });

    } catch (error) {
        return res.status(500).json({ error: 'Erro ao buscar dados', details: error.message });
    }
}