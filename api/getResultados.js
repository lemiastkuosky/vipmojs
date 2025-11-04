// /api/getResultados.js - VERSÃO CommonJS (require)

const admin = require('firebase-admin');

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

module.exports = async (request, response) => {
    response.setHeader('Access-Control-Allow-Origin', '*');
    response.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    if (request.method === 'OPTIONS') return response.status(200).end();
    if (request.method !== 'GET') return response.status(405).send('Método não permitido (apenas GET).');
    if (!admin.apps.length) return response.status(500).send('Erro interno: Firebase Admin não inicializado.');

    try {
        const { tipo, data, loteria, grupos } = request.query;
        const resultadosRef = db.collection('resultados');
        let query;

        if (tipo === 'ia') {
            if (!loteria || !grupos) {
                return response.status(400).send('Para ?tipo=ia, os parâmetros "loteria" e "grupos" (separados por vírgula) são obrigatórios.');
            }
            const gruposArray = grupos.split(',');
            query = resultadosRef
                .where('loteria', '==', loteria)
                .where('bichoGrupo', 'in', gruposArray)
                .orderBy('data', 'desc')
                .limit(5000);
        
        } else if (tipo === 'data') {
            if (!data) {
                return response.status(400).send('Para ?tipo=data, o parâmetro "data" (dd/mm/yyyy) é obrigatório.');
            }
            query = resultadosRef.where('dataExtracao', '==', data);
            if (loteria) {
                query = query.where('loteria', '==', loteria);
            }

        } else if (tipo === 'count') {
             const snapshot = await resultadosRef.get();
             const count = snapshot.size;
             console.log(`Retornando contagem total (via .size): ${count}`);
             return response.status(200).json({ count: count });

        } else {
            return response.status(400).send('Parâmetro "tipo" (ex: "ia", "data", "count") é obrigatório.');
        }

        const snapshot = await query.get();

        if (snapshot.empty) {
            console.log(`Nenhum resultado encontrado para: ${JSON.stringify(request.query)}`);
            return response.status(200).json([]);
        }

        const resultados = snapshot.docs.map(doc => doc.data());
        console.log(`Retornando ${resultados.length} resultado(s) para: ${JSON.stringify(request.query)}`);
        return response.status(200).json(resultados);

    } catch (error) {
        console.error("Erro ao buscar resultados no Firestore:", error);
        if (error.message && error.message.includes('requires an index')) {
             return response.status(500).json({ success: false, message: `Erro de consulta: O Firestore requer um índice. Verifique os logs da Vercel para o link de criação do índice.` });
        }
        return response.status(500).json({ success: false, message: `Erro interno ao buscar resultados: ${error.message}` });
    }
}