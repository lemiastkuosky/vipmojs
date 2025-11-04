// /api/saveResult.js

// Importa o SDK Admin do Firebase
import admin from 'firebase-admin';

// Inicializa o Firebase Admin (APENAS SE NÃO FOI INICIALIZADO AINDA)
// Isso garante que a inicialização ocorra apenas uma vez, mesmo com múltiplas chamadas da função
if (!admin.apps.length) {
  try {
    // Pega as credenciais da variável de ambiente (o JSON que você colou na Vercel)
    const serviceAccount = JSON.parse(process.env.FIREBASE_SERVICE_ACCOUNT_JSON);

    admin.initializeApp({
      credential: admin.credential.cert(serviceAccount)
    });
    console.log("Firebase Admin SDK inicializado com sucesso.");
  } catch (error) {
    console.error("Erro Crítico: Falha ao inicializar o Firebase Admin SDK!", error);
    // Se a inicialização falhar, não podemos continuar.
    // É importante verificar se a variável de ambiente está correta na Vercel.
  }
}

// Acessa o serviço do Firestore
const db = admin.firestore();

// Função principal que a Vercel executará
export default async function handler(request, response) {
    // Permite CORS (importante para o seu frontend acessar esta API)
    response.setHeader('Access-Control-Allow-Origin', '*'); // Ou especifique o domínio da sua Vercel
    response.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
    response.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    // Responde a requisições OPTIONS
    if (request.method === 'OPTIONS') {
        return response.status(200).end();
    }

    // --- VALIDAÇÃO INICIAL ---
    // Garante que a requisição seja POST
    if (request.method !== 'POST') {
        return response.status(405).send('Método não permitido (apenas POST).');
    }

    // Garante que o SDK foi inicializado
    if (!admin.apps.length) {
         return response.status(500).send('Erro interno: Firebase Admin não inicializado.');
    }

    // --- PROCESSAMENTO DOS DADOS ---
    try {
        // Pega os dados enviados pelo frontend (espera um JSON no corpo)
        const { loteria, nomeSorteio, nomeLoteria, dataExtracao, resultados } = request.body;

        // Validação simples dos dados recebidos
        if (!loteria || !nomeSorteio || !resultados || !Array.isArray(resultados) || resultados.length === 0) {
            return response.status(400).send('Dados inválidos ou faltando.');
        }

        // --- ESTRUTURA DOS DADOS NO FIRESTORE ---
        // Vamos criar um ID de documento único combinando data, loteria e sorteio
        // Ex: 20251025-rj-PTM
        const dataFormatadaId = dataExtracao.split('/').reverse().join(''); // dd/mm/yyyy -> yyyymmdd
        const documentId = `${dataFormatadaId}-${loteria}-${nomeSorteio.replace(/[:\s]/g, '')}`; // Remove espaços e ':'

        // Coleção onde os resultados serão salvos (ex: "resultados")
        const collectionRef = db.collection('resultados');

        // Dados a serem salvos
        const dataToSave = {
            loteria: loteria,
            nomeLoteria: nomeLoteria || loteria, // Usa nomeLoteria se disponível
            nomeSorteio: nomeSorteio,
            dataExtracao: dataExtracao,
            resultados: resultados, // Array com {posicao, emoji, milhar, grupo}
            timestampSalvo: admin.firestore.FieldValue.serverTimestamp() // Adiciona data/hora do salvamento
        };

        // --- SALVANDO NO FIRESTORE ---
        // Usamos .set() com o ID que criamos. Isso sobrescreve se já existir um resultado
        // para o mesmo dia/loteria/sorteio, garantindo que não haja duplicatas.
        // Se preferir adicionar sempre (mesmo que duplicado), use .add().
        await collectionRef.doc(documentId).set(dataToSave);

        console.log(`Resultado salvo com sucesso: ${documentId}`);
        // Envia resposta de sucesso para o frontend
        return response.status(200).json({ success: true, message: 'Resultado salvo com sucesso!', id: documentId });

    } catch (error) {
        console.error("Erro ao salvar resultado no Firestore:", error);
        // Envia resposta de erro para o frontend
        return response.status(500).json({ success: false, message: `Erro interno ao salvar resultado: ${error.message}` });
    }
}