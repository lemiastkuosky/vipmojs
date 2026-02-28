const admin = require('firebase-admin');

// Conecta no seu Firebase usando a Chave Secreta do GitHub
const serviceAccount = JSON.parse(process.env.FIREBASE_CREDENTIALS);
admin.initializeApp({
    credential: admin.credential.cert(serviceAccount)
});
const db = admin.firestore();

// Tabela de Bichos
const tabelaDeGrupos = [
    { grupo: '01', animal: 'Avestruz', dezenas: ['01', '02', '03', '04'] }, { grupo: '02', animal: 'Aguia', dezenas: ['05', '06', '07', '08'] }, { grupo: '03', animal: 'Burro', dezenas: ['09', '10', '11', '12'] }, { grupo: '04', animal: 'Borboleta', dezenas: ['13', '14', '15', '16'] }, { grupo: '05', animal: 'Cachorro', dezenas: ['17', '18', '19', '20'] }, { grupo: '06', animal: 'Cabra', dezenas: ['21', '22', '23', '24'] }, { grupo: '07', animal: 'Carneiro', dezenas: ['25', '26', '27', '28'] }, { grupo: '08', animal: 'Camelo', dezenas: ['29', '30', '31', '32'] }, { grupo: '09', animal: 'Cobra', dezenas: ['33', '34', '35', '36'] }, { grupo: '10', animal: 'Coelho', dezenas: ['37', '38', '39', '40'] }, { grupo: '11', animal: 'Cavalo', dezenas: ['41', '42', '43', '44'] }, { grupo: '12', animal: 'Elefante', dezenas: ['45', '46', '47', '48'] }, { grupo: '13', animal: 'Galo', dezenas: ['49', '50', '51', '52'] }, { grupo: '14', animal: 'Gato', dezenas: ['53', '54', '55', '56'] }, { grupo: '15', animal: 'Jacare', dezenas: ['57', '58', '59', '60'] }, { grupo: '16', animal: 'Leao', dezenas: ['61', '62', '63', '64'] }, { grupo: '17', animal: 'Macaco', dezenas: ['65', '66', '67', '68'] }, { grupo: '18', animal: 'Porco', dezenas: ['69', '70', '71', '72'] }, { grupo: '19', animal: 'Pavao', dezenas: ['73', '74', '75', '76'] }, { grupo: '20', animal: 'Peru', dezenas: ['77', '78', '79', '80'] }, { grupo: '21', animal: 'Touro', dezenas: ['81', '82', '83', '84'] }, { grupo: '22', animal: 'Tigre', dezenas: ['85', '86', '87', '88'] }, { grupo: '23', animal: 'Urso', dezenas: ['89', '90', '91', '92'] }, { grupo: '24', animal: 'Veado', dezenas: ['93', '94', '95', '96'] }, { grupo: '25', animal: 'Vaca', dezenas: ['97', '98', '99', '00'] }
];

async function runTreinamento() {
    console.log("🤖 INICIANDO TREINAMENTO AUTOMÁTICO DA I.A...");
    const loterias = ['rj', 'lk', 'ln', 'fd'];

    for (const loteria of loterias) {
        console.log(`Processando: ${loteria.toUpperCase()}`);
        const snapshot = await db.collection('resultados').where('loteria', '==', loteria).orderBy('data', 'desc').limit(3000).get();
        if (snapshot.empty) continue;

        const c = { 
            bicho1PremioCount: {}, ultimosVistos1Premio: new Map(), bichoFrequencia: new Map(), datasSorteios: [] 
        };
        const dataFrequente = new Date(); dataFrequente.setDate(dataFrequente.getDate() - 7);

        snapshot.forEach(doc => {
            const data = doc.data();
            if (!data.bichoGrupo) return;
            const g = data.bichoGrupo, dS = data.data.toDate(), p = data.posicao;

            c.datasSorteios.push(dS.getTime());
            if (p === 1) {
                c.bicho1PremioCount[g] = (c.bicho1PremioCount[g] || 0) + 1;
                if (!c.ultimosVistos1Premio.has(g)) c.ultimosVistos1Premio.set(g, dS);
            }
            if (dS >= dataFrequente) c.bichoFrequencia.set(g, (c.bichoFrequencia.get(g) || 0) + 1);
        });

        const dataAntiga = new Date('2000-01-01');
        const ciclos = {};
        let maxTime = c.datasSorteios.length > 0 ? Math.max(...c.datasSorteios) : new Date().getTime();
        let minTime = c.datasSorteios.length > 0 ? Math.min(...c.datasSorteios) : new Date().getTime();
        let totalDias = Math.max(1, (maxTime - minTime) / (1000 * 60 * 60 * 24)); 

        for (let i = 1; i <= 25; i++) {
            const g = String(i).padStart(2, '0');
            const count = c.bicho1PremioCount[g] || 0.1; 
            const cicloMedioDias = totalDias / count; 
            const lastSeen = c.ultimosVistos1Premio.get(g) || dataAntiga;
            const atrasoDias = (maxTime - lastSeen.getTime()) / (1000 * 60 * 60 * 24);
            
            ciclos[g] = { medio: Number(cicloMedioDias.toFixed(1)), atual: Number(atrasoDias.toFixed(1)), estourado: atrasoDias >= cicloMedioDias };
        }

        const compiledData = {
            geral: {
                bichosAtrasados1Premio: tabelaDeGrupos.map(b => ({ ...b, data: c.ultimosVistos1Premio.get(b.grupo) || dataAntiga })).sort((a, b) => a.data.getTime() - b.data.getTime()).slice(0, 5),
                bichosFrequentes: Array.from(c.bichoFrequencia.entries()).map(([g, count]) => ({ ...tabelaDeGrupos.find(x => x.grupo === g), count })).sort((a, b) => b.count - a.count).slice(0, 5),
                ciclos: ciclos
            },
            lastUpdated: admin.firestore.FieldValue.serverTimestamp()
        };
        
        await db.collection('analises_ia').doc(loteria).set(compiledData, { merge: true });
        console.log(`✅ ${loteria.toUpperCase()} Treinada!`);
    }

    console.log("🤖 Calculando Placar de Marketing...");
    const tresDiasAtras = new Date(); tresDiasAtras.setDate(tresDiasAtras.getDate() - 3);
    const snapRecentes = await db.collection('resultados').where('data', '>=', admin.firestore.Timestamp.fromDate(tresDiasAtras)).where('posicao', '==', 1).get();

    let acertos = 0; let loteriasHit = new Set();
    for (const loteria of loterias) {
        const docIA = await db.collection('analises_ia').doc(loteria).get();
        if(!docIA.exists) continue;
        const analise = docIA.data().geral;

        let bichosScore = tabelaDeGrupos.map(b => {
            let score = 25;
            if(analise.bichosAtrasados1Premio.find(x => x.grupo === b.grupo)) score += 30;
            if(analise.bichosFrequentes.find(x => x.grupo === b.grupo)) score += 20;
            if(analise.ciclos[b.grupo] && analise.ciclos[b.grupo].atual > analise.ciclos[b.grupo].medio) score += 20;
            return { grupo: b.grupo, score: score };
        });
        const top5 = bichosScore.sort((a,b) => b.score - a.score).slice(0, 5).map(b => b.grupo);

        snapRecentes.forEach(doc => {
            const data = doc.data();
            if (data.loteria === loteria && top5.includes(data.bichoGrupo)) { acertos++; loteriasHit.add(loteria.toUpperCase()); }
        });
    }

    if (acertos > 0) {
        const autoText = `🔥 A I.A. cravou <strong>${acertos} Grupos na Cabeça</strong> nos últimos 3 dias nas loterias ${Array.from(loteriasHit).join(', ')}!`;
        await db.collection('config').doc('placar_ia').set({ texto_automatico: autoText, ultimaAtualizacaoAuto: admin.firestore.FieldValue.serverTimestamp() }, { merge: true });
    }
    console.log("🚀 ROTINA FINALIZADA COM SUCESSO!");
}

runTreinamento().then(() => process.exit(0)).catch(e => { console.error(e); process.exit(1); });