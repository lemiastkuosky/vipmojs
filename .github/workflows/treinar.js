/**
 * VipMojs — Treinamento Automático da I.A.
 * Roda via GitHub Actions todo dia às 03:00 (horário de Brasília)
 * 
 * Requer variáveis de ambiente:
 *   FIREBASE_SERVICE_ACCOUNT  →  JSON completo da Service Account (base64 ou raw)
 *   FIREBASE_PROJECT_ID       →  ID do projeto Firebase
 */

const admin = require('firebase-admin');

// ── CONFIGURAÇÃO ─────────────────────────────────────────────────────────────

const LOTERIAS = ['rj', 'lk', 'ln', 'ba', 'fd'];

const HORARIOS_LOTERIAS = {
    rj: ['RIO 09:20','RIO 11:20','RIO 14:20','RIO 16:20','RIO 18:20','CORUJA 21:30'],
    lk: ['LOOK 07:20','LOOK 09:20','LOOK 11:20','LOOK 14:20','LOOK 16:20','LOOK 18:20','LOOK 21:20','LOOK 23:20'],
    ln: ['NACIONAL 02:00','NACIONAL 08:00','NACIONAL 10:00','NACIONAL 12:00','NACIONAL 15:00','NACIONAL 17:00','NACIONAL 19:00','NACIONAL 21:00','NACIONAL 23:00'],
    ba: ['BAHIA 10:00','BAHIA 12:00','BAHIA 15:00','BAHIA 19:00','BAHIA 21:00'],
    fd: ['FEDERAL 19:00'],
};

// ── INIT FIREBASE ─────────────────────────────────────────────────────────────

function initFirebase() {
    const raw = process.env.FIREBASE_SERVICE_ACCOUNT;
    if (!raw) throw new Error('FIREBASE_SERVICE_ACCOUNT não definida');

    let serviceAccount;
    try {
        // Tenta direto (JSON raw)
        serviceAccount = JSON.parse(raw);
    } catch {
        // Tenta base64
        serviceAccount = JSON.parse(Buffer.from(raw, 'base64').toString('utf-8'));
    }

    admin.initializeApp({
        credential: admin.credential.cert(serviceAccount),
        projectId: process.env.FIREBASE_PROJECT_ID || serviceAccount.project_id,
    });

    return admin.firestore();
}

// ── LÓGICA DE TREINAMENTO ─────────────────────────────────────────────────────

function createContainer() {
    return {
        milharLastDate: {},
        dezenaCounts: {},
        bicho1PremioCount: {},
        bicho1PremioPorDia: {},
        dezenaPorBicho1Premio: {},
        tendenciaRecente: {},
        digitosCarregados: { p0: {}, p1: {}, p2: {}, p3: {} },
        ultimaMilhar1Premio: null,
        datasSorteios: [],
    };
}

function sanitize(obj) {
    if (obj === undefined) return null;
    if (obj === null || typeof obj !== 'object' || obj instanceof Date) return obj;
    const newObj = Array.isArray(obj) ? [] : {};
    for (const k in obj) newObj[k] = sanitize(obj[k]);
    return newObj;
}

async function treinarLoteria(db, loteria) {
    console.log(`\n📊 Treinando: ${loteria.toUpperCase()}...`);

    const dataAtrasada = new Date();
    dataAtrasada.setDate(dataAtrasada.getDate() - 180);

    const dataRecente = new Date();
    dataRecente.setDate(dataRecente.getDate() - 7);

    // Busca até 5000 resultados
    const snapshot = await db.collection('resultados')
        .where('loteria', '==', loteria)
        .orderBy('data', 'desc')
        .limit(5000)
        .get();

    if (snapshot.empty) {
        console.log(`   ⚠️  Sem dados para ${loteria}`);
        return;
    }

    console.log(`   📁 ${snapshot.size} resultados encontrados`);

    // Cria containers: geral + um por horário
    const containers = { geral: createContainer() };
    (HORARIOS_LOTERIAS[loteria] || []).forEach(nome => {
        containers[nome] = createContainer();
    });

    // Processa cada resultado
    snapshot.forEach(doc => {
        const data = doc.data();
        if (!data.milhar || !data.bichoGrupo) return;

        const m = data.milhar;
        const g = data.bichoGrupo;
        const dS = data.data.toDate();
        const p = data.posicao;
        const drawName = data.nomeSorteio;

        const targets = [containers.geral];
        if (drawName && containers[drawName]) targets.push(containers[drawName]);

        targets.forEach(c => {
            c.datasSorteios.push(dS.getTime());

            // Milhar atrasada
            if (!c.milharLastDate[m] || dS > c.milharLastDate[m]) {
                c.milharLastDate[m] = dS;
            }

            // Dezena geral
            const dz = m.slice(-2);
            c.dezenaCounts[dz] = (c.dezenaCounts[dz] || 0) + 1;

            if (p === 1) {
                // Bicho do 1º prêmio
                c.bicho1PremioCount[g] = (c.bicho1PremioCount[g] || 0) + 1;

                // Por dia da semana
                const diaSem = dS.getDay();
                if (!c.bicho1PremioPorDia[diaSem]) c.bicho1PremioPorDia[diaSem] = {};
                c.bicho1PremioPorDia[diaSem][g] = (c.bicho1PremioPorDia[diaSem][g] || 0) + 1;

                // Tendência recente (últimos 7 dias)
                if (dS >= dataRecente) {
                    c.tendenciaRecente[g] = (c.tendenciaRecente[g] || 0) + 1;
                }

                // Dezena por bicho no 1º prêmio
                if (!c.dezenaPorBicho1Premio[g]) c.dezenaPorBicho1Premio[g] = {};
                c.dezenaPorBicho1Premio[g][dz] = (c.dezenaPorBicho1Premio[g][dz] || 0) + 1;

                // Dígitos carregados
                if (c.ultimaMilhar1Premio) {
                    const prev = c.ultimaMilhar1Premio;
                    [0, 1, 2, 3].forEach(pos => {
                        if (m[pos] === prev[pos]) {
                            const pk = 'p' + pos;
                            c.digitosCarregados[pk][m[pos]] = (c.digitosCarregados[pk][m[pos]] || 0) + 1;
                        }
                    });
                }
                c.ultimaMilhar1Premio = m;
            }
        });
    });

    // Compila dados finais
    const compiledData = {};

    for (const [key, c] of Object.entries(containers)) {
        const finalObject = {
            milharesAtrasadas: Object.keys(c.milharLastDate)
                .filter(m => c.milharLastDate[m] < dataAtrasada)
                .slice(0, 50),

            dezenasQuentes: Object.keys(c.dezenaCounts)
                .sort((a, b) => c.dezenaCounts[b] - c.dezenaCounts[a])
                .slice(0, 50),

            tendenciaRecente: c.tendenciaRecente || {},
            bicho1PremioPorDia: c.bicho1PremioPorDia || {},

            // ✅ NOVO: dezenas quentes do 1º prêmio por bicho
            dezenaPorBicho1Premio: Object.fromEntries(
                Object.entries(c.dezenaPorBicho1Premio || {}).map(([grp, counts]) => [
                    grp,
                    Object.entries(counts)
                        .sort((a, b) => b[1] - a[1])
                        .slice(0, 5)
                        .map(x => x[0])
                ])
            ),

            digitosCarregados: {
                p0: Object.entries(c.digitosCarregados?.p0 || {}).sort((a,b) => b[1]-a[1]).slice(0,3).map(x=>x[0]),
                p1: Object.entries(c.digitosCarregados?.p1 || {}).sort((a,b) => b[1]-a[1]).slice(0,3).map(x=>x[0]),
                p2: Object.entries(c.digitosCarregados?.p2 || {}).sort((a,b) => b[1]-a[1]).slice(0,3).map(x=>x[0]),
                p3: Object.entries(c.digitosCarregados?.p3 || {}).sort((a,b) => b[1]-a[1]).slice(0,3).map(x=>x[0]),
            },

            ultimaMilhar1Premio: c.ultimaMilhar1Premio || null,
        };

        compiledData[key] = sanitize(finalObject);
    }

    compiledData.lastUpdated = admin.firestore.FieldValue.serverTimestamp();

    await db.collection('analises_ia').doc(loteria).set(compiledData);
    console.log(`   ✅ ${loteria.toUpperCase()} treinado com sucesso!`);
}

// ── MAIN ──────────────────────────────────────────────────────────────────────

async function main() {
    console.log('🤖 VipMojs — Treinamento Automático da I.A.');
    console.log(`⏰ ${new Date().toLocaleString('pt-BR', { timeZone: 'America/Sao_Paulo' })}`);
    console.log('─'.repeat(50));

    const db = initFirebase();
    console.log('🔥 Firebase conectado');

    let erros = 0;

    for (const loteria of LOTERIAS) {
        try {
            await treinarLoteria(db, loteria);
        } catch (e) {
            console.error(`   ❌ Erro ao treinar ${loteria}:`, e.message);
            erros++;
        }
    }

    console.log('\n' + '─'.repeat(50));
    if (erros === 0) {
        console.log('🎉 Treinamento concluído! Todas as loterias atualizadas.');
    } else {
        console.log(`⚠️  Treinamento concluído com ${erros} erro(s).`);
        process.exit(1);
    }
}

main().catch(e => {
    console.error('💥 Erro fatal:', e);
    process.exit(1);
});
