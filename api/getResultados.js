// /api/getResultados.js

import admin from 'firebase-admin';

// Inicializa o Firebase Admin (APENAS SE NÃO FOI INICIALIZADO AINDA)
if (!admin.apps.length) {
  try {
    const serviceAccount = JSON.parse(process.env.FIREBASE_SERVICE_ACCOUNT_JSON);
    admin.initializeApp({
      credential: admin.credential.cert(serviceAccount)
    });
    console.log("Firebase Admin SDK inicializado (getResultados).");
  } catch (error) {
    console.error("Erro Crítico: Falha ao inicializar o Firebase Admin SDK (getResultados)!", error);
  }
}

const db = admin.firestore();

export default async function handler(request, response) {
    // Permite CORS
    response.setHeader('Access-Control-Allow-Origin', '*'); // Ajuste se necessário
    response.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (request.method === 'OPTIONS') {
        return response.status(200).end();
    }

    // Garante que seja GET
    if (request.method !== 'GET') {
        return response.status(405).send('Método não permitido (apenas GET).');
    }

    // Garante que o SDK foi inicializado
    if (!admin.apps.length) {
         return response.status(500).send('Erro interno: Firebase Admin não inicializado.');
    }

    try {
        // Pega os parâmetros da query string (ex: ?data=25/10/2025&loteria=rj)
        const { data, loteria } = request.query;

        // Validação básica
        if (!data) {
            return response.status(400).send('Parâmetro "data" (formato dd/mm/yyyy) é obrigatório.');
        }

        // --- CONSTRUÇÃO DA CONSULTA NO FIRESTORE ---
        const resultadosRef = db.collection('resultados');
        let query = resultadosRef.where('dataExtracao', '==', data);

        // Adiciona filtro de loteria se fornecido
        if (loteria) {
            query = query.where('loteria', '==', loteria);
            // **IMPORTANTE:** O Firestore pode exigir um índice composto para esta consulta
            // (em 'dataExtracao' e 'loteria'). Se a consulta falhar com um erro
            // sobre índice, o Firebase Console geralmente fornece um link para criá-lo.
        }

        // Ordena opcionalmente (ex: por hora de salvamento, se útil)
        // query = query.orderBy('timestampSalvo', 'asc'); // Ou 'desc'

        // Executa a consulta
        const snapshot = await query.get();

        // --- PROCESSAMENTO DOS RESULTADOS ---
        if (snapshot.empty) {
            console.log(`Nenhum resultado encontrado para data: ${data}, loteria: ${loteria || 'todas'}`);
            return response.status(200).json([]); // Retorna array vazio se não encontrar nada
        }

        // Mapeia os documentos encontrados para um array de objetos
        const resultados = snapshot.docs.map(doc => ({
            id: doc.id, // Inclui o ID do documento
            ...doc.data() // Inclui todos os campos do documento
        }));

        console.log(`Retornando ${resultados.length} resultado(s) para data: ${data}, loteria: ${loteria || 'todas'}`);
        // Retorna os resultados encontrados como JSON
        return response.status(200).json(resultados);

    } catch (error) {
        console.error("Erro ao buscar resultados no Firestore:", error);
        // Verifica se o erro é sobre índice ausente
        if (error.message && error.message.includes('requires an index')) {
             return response.status(500).json({ success: false, message: `Erro de consulta: O Firestore requer um índice para esta combinação de filtros. Verifique o console do Firebase ou os logs da Vercel para o link de criação do índice. Detalhes: ${error.message}` });
        }
        return response.status(500).json({ success: false, message: `Erro interno ao buscar resultados: ${error.message}` });
    }
}