// /api/saveResult.js - VERSÃO CommonJS (require)

const admin = require('firebase-admin');

if (!admin.apps.length) {
  try {
    const serviceAccount = JSON.parse(process.env.FIREBASE_SERVICE_ACCOUNT_JSON);
    admin.initializeApp({
      credential: admin.credential.cert(serviceAccount)
    });
    console.log("Firebase Admin SDK inicializado com sucesso (saveResult).");
  } catch (error) {
    console.error("Erro Crítico: Falha ao inicializar o Firebase Admin SDK (saveResult)!", error);
  }
}

const db = admin.firestore();

module.exports = async (request, response) => {
    response.setHeader('Access-Control-Allow-Origin', '*');
    response.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (request.method === 'OPTIONS') {
        return response.status(200).end();
    }

    if (request.method !== 'POST') {
        return response.status(405).send('Método não permitido (apenas POST).');
    }

    if (!admin.apps.length) {
         return response.status(500).send('Erro interno: Firebase Admin não inicializado.');
    }

    try {
        const { loteria, nomeSorteio, nomeLoteria, dataExtracao, resultados } = request.body;

        if (!loteria || !nomeSorteio || !resultados || !Array.isArray(resultados) || resultados.length === 0) {
            return response.status(400).send('Dados inválidos ou faltando.');
        }

        const dataFormatadaId = dataExtracao.split('/').reverse().join('');
        const documentId = `${dataFormatadaId}-${loteria}-${nomeSorteio.replace(/[:\s]/g, '')}`;

        const collectionRef = db.collection('resultados');

        const dataToSave = {
            loteria: loteria,
            nomeLoteria: nomeLoteria || loteria,
            nomeSorteio: nomeSorteio,
            dataExtracao: dataExtracao,
            resultados: resultados,
            timestampSalvo: admin.firestore.FieldValue.serverTimestamp()
        };

        await collectionRef.doc(documentId).set(dataToSave);

        console.log(`Resultado salvo com sucesso: ${documentId}`);
        return response.status(200).json({ success: true, message: 'Resultado salvo com sucesso!', id: documentId });

    } catch (error) {
        console.error("Erro ao salvar resultado no Firestore:", error);
        return response.status(500).json({ success: false, message: `Erro interno ao salvar resultado: ${error.message}` });
    }
}