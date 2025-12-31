<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutAnalise | Scoreboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-body: #18181b;
            --bg-card: #27272a;
            --bg-header: #202022;
            --text-main: #f4f4f5;
            --text-muted: #a1a1aa;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --accent-blue: #3b82f6;
            --accent-purple: #8b5cf6;
            --border-color: #3f3f46;
        }

        body { background-color: var(--bg-body); color: var(--text-main); font-family: 'Inter', sans-serif; font-size: 0.9rem; }
        
        .navbar { background-color: rgba(24, 24, 27, 0.95) !important; border-bottom: 1px solid var(--border-color); }
        .section-title { font-size: 0.8rem; font-weight: 800; text-transform: uppercase; margin: 20px 0 10px 0; border-bottom: 1px solid var(--border-color); color: #71717a; padding-bottom: 5px; }

        /* TABS */
        .nav-tabs-custom { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-top: 15px; }
        .btn-tab { background-color: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-muted); padding: 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .btn-tab.active { background-color: rgba(16, 185, 129, 0.1); color: var(--accent-green); border-color: var(--accent-green); }
        
        /* CARD */
        .league-header { background-color: var(--bg-header); padding: 6px 12px; border-radius: 6px; margin-top: 15px; font-weight: 700; font-size: 0.75rem; border-left: 3px solid #71717a; display: flex; align-items: center; }
        .match-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.2); }
        
        /* PLACAR CENTRALIZADO */
        .scoreboard { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 10px; }
        .team-col { flex: 1; text-align: center; overflow: hidden; }
        .team-logo { width: 35px; height: 35px; object-fit: contain; margin-bottom: 4px; display: block; margin: 0 auto 4px auto; }
        .team-name { font-size: 0.75rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
        
        .score-box { 
            background: #111; 
            padding: 5px 12px; 
            border-radius: 6px; 
            border: 1px solid #333; 
            font-size: 1.2rem; 
            font-weight: 800; 
            color: #fff;
            min-width: 70px;
            text-align: center;
            letter-spacing: 2px;
        }
        .score-live { color: var(--accent-green); border-color: var(--accent-green); box-shadow: 0 0 10px rgba(16, 185, 129, 0.1); }
        .match-time { font-size: 0.65rem; color: #71717a; text-align: center; display: block; margin-top: 4px; font-family: monospace; }

        /* GRIDS & DICAS */
        .grid-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; margin-top: 8px; border-top: 1px solid #333; padding-top: 8px; }
        .stat-box { text-align: center; background: rgba(0,0,0,0.2); padding: 4px; border-radius: 4px; }
        .stat-label { font-size: 0.55rem; color: #71717a; display: block; font-weight: 700; }
        .stat-value { font-weight: 700; font-size: 0.75rem; color: #e4e4e7; }
        
        /* Dica */
        .tip-container { margin-top: 8px; }
        .tip-box { 
            padding: 8px; 
            border-radius: 4px; 
            font-size: 0.75rem; 
            font-weight: 600; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            border: 1px solid transparent;
        }
        .tip-green { background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.3); color: #6ee7b7; }
        .tip-blue { background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.3); color: #93c5fd; }
        .tip-red { background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3); color: #fca5a5; }
        
        /* Helpers */
        .text-green { color: var(--accent-green); }
        .text-red { color: var(--accent-red); text-decoration: line-through; opacity: 0.6; }
        .bar-mini { height: 3px; background: #333; margin-top: 3px; border-radius: 2px; }
        .bar-fill { height: 100%; background: #555; border-radius: 2px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark sticky-top">
    <div class="container">
        <span class="navbar-brand mb-0 h1"><i class="fas fa-futbol text-success me-2"></i>FutAnalise</span>
        <button class="btn btn-sm btn-dark border-secondary" onclick="carregarJogos()"><i class="fas fa-sync-alt"></i></button>
    </div>
</nav>

<div class="container mb-5">
    
    <div class="nav-tabs-custom">
        <button class="btn-tab" id="btn-live" onclick="mudarAba('live')">Ao Vivo <span id="c-live"></span></button>
        <button class="btn-tab active" id="btn-pre" onclick="mudarAba('pre')">Próximos <span id="c-pre"></span></button>
        <button class="btn-tab" id="btn-finished" onclick="mudarAba('finished')">Fim <span id="c-finished"></span></button>
    </div>

    <div id="loading" class="text-center py-5">
        <div class="spinner-border text-secondary" role="status"></div>
    </div>

    <div id="content-live" style="display:none"></div>
    <div id="content-pre" style="display:block"></div>
    <div id="content-finished" style="display:none"></div>
    
    <div id="empty-msg" class="text-center text-muted mt-5" style="display:none; font-size:0.8rem">Sem jogos aqui.</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const FIREBASE_URL = "https://futanalise-121f9-default-rtdb.firebaseio.com"; 

    document.addEventListener('DOMContentLoaded', () => { carregarJogos(); });

    function mudarAba(aba) {
        document.querySelectorAll('.btn-tab').forEach(b => b.classList.remove('active'));
        document.getElementById(`btn-${aba}`).classList.add('active');
        ['live', 'pre', 'finished'].forEach(k => document.getElementById(`content-${k}`).style.display = 'none');
        const content = document.getElementById(`content-${aba}`);
        content.style.display = 'block';
        document.getElementById('empty-msg').style.display = content.innerHTML === '' ? 'block' : 'none';
    }

    async function carregarJogos() {
        const loading = document.getElementById('loading');
        const hoje = new Date().toISOString().split('T')[0];
        
        try {
            const res = await fetch(`${FIREBASE_URL}/jogos/${hoje}.json`);
            const data = await res.json();
            loading.style.display = 'none';

            if (!data) return;

            const buckets = { live: {}, pre: {}, finished: {} };
            let counts = { live: 0, pre: 0, finished: 0 };

            Object.values(data).forEach(jogo => {
                const st = jogo.info.status_short;
                const idLiga = jogo.liga.id;
                let type = 'pre';
                
                if (['1H','2H','HT','ET','P'].includes(st)) type = 'live';
                else if (['FT','AET','PEN'].includes(st)) type = 'finished';
                
                if (!buckets[type][idLiga]) buckets[type][idLiga] = { info: jogo.liga, jogos: [] };
                buckets[type][idLiga].jogos.push(jogo);
                counts[type]++;
            });

            document.getElementById('c-live').innerText = `(${counts.live})`;
            document.getElementById('c-pre').innerText = `(${counts.pre})`;
            document.getElementById('c-finished').innerText = `(${counts.finished})`;

            ['live', 'pre', 'finished'].forEach(t => {
                const container = document.getElementById(`content-${t}`);
                container.innerHTML = '';
                if(counts[t] > 0) renderizarSecao(container, buckets[t]);
            });
            
            // Auto switch se tiver ao vivo
            if(counts.live > 0) mudarAba('live');

        } catch (e) { console.error(e); }
    }

    function renderizarSecao(container, dados) {
        for (const [id, liga] of Object.entries(dados)) {
            container.innerHTML += `
                <div class="league-header">
                    <img src="${liga.info.bandeira||''}" style="width:15px; margin-right:8px"> ${liga.info.nome}
                </div>`;
            
            // Ordena por horário
            liga.jogos.sort((a,b) => a.info.timestamp - b.info.timestamp).forEach(j => {
                container.innerHTML += cardJogo(j);
            });
        }
    }

    function cardJogo(jogo) {
        const golsCasa = jogo.placar.casa || 0;
        const golsFora = jogo.placar.fora || 0;
        const total = golsCasa + golsFora;
        const st = jogo.info.status_short;
        
        // Probabilidades (Se não tiver dados, coloca 0)
        const p = {
            '0.5': jogo.analise.prob_over05_ft || 0,
            '1.5': jogo.analise.prob_over15_ft || 0,
            '2.5': jogo.analise.prob_over25_ft || 0,
            '3.5': jogo.analise.prob_over35_ft || 0
        };

        // Configuração Visual Baseada no Status
        let tempoTexto = new Date(jogo.info.timestamp*1000).toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'});
        let scoreClass = "";
        
        if(['1H','2H','HT'].includes(st)) {
            tempoTexto = `<span style="color:#ef4444">AO VIVO ${jogo.info.tempo_decorrido}'</span>`;
            scoreClass = "score-live";
        } else if(['FT','AET'].includes(st)) {
            tempoTexto = "ENCERRADO";
        }

        // --- GERADOR DE DICA (IA) ---
        let dica = null;
        
        // 1. Pré-Jogo
        if(st === 'NS') {
            if(p['0.5'] >= 85) dica = { t: "🛡️ Seguro: Over 0.5 FT", c: "tip-green" };
            else if(p['1.5'] >= 75) dica = { t: "🚀 Alvo: Over 1.5 FT", c: "tip-blue" };
            else if(p['0.5'] >= 70) dica = { t: "👀 Observar Live", c: "tip-blue" };
        }
        // 2. Ao Vivo
        else if(['1H','2H','HT'].includes(st)) {
            if(total > 0 && p['1.5'] > 75) dica = { t: "🔥 Jogo Aberto: Buscar +1", c: "tip-green" };
            else if(total === 0 && st === 'HT' && p['0.5'] > 80) dica = { t: "💎 Valor: Over 0.5 HT/FT", c: "tip-green" };
            else if(total === 0 && st === '2H' && p['0.5'] > 80) dica = { t: "🚨 Pressão: Gol Maduro", c: "tip-red" };
        }
        // 3. Finalizado
        else if(['FT'].includes(st)) {
            // Se bateu a previsão principal
            if(p['0.5'] > 80 && total > 0) dica = { t: "✅ Leitura Correta (Green)", c: "tip-green" };
            else if(p['0.5'] > 80 && total === 0) dica = { t: "❌ Zebra (Red)", c: "tip-red" };
        }

        // Se não caiu em nenhuma regra, força uma dica genérica se tiver stats
        if(!dica && p['0.5'] > 0) {
             dica = { t: `Prob. Gol: ${p['0.5']}%`, c: "tip-blue" };
        }

        const htmlDica = dica ? `<div class="tip-container"><div class="tip-box ${dica.c}">${dica.t}</div></div>` : '';

        // Renderiza Grid de Stats
        let htmlStats = '';
        ['0.5', '1.5', '2.5', '3.5'].forEach(k => {
            let val = p[k];
            let cor = val >= 75 ? '#10b981' : (val >= 50 ? '#f59e0b' : '#333');
            let txtClass = "";
            let check = "";

            // Verifica Green/Red visual
            if(total > parseFloat(k)) { txtClass = "text-green"; check = "✓"; } // Bateu
            else if (st === 'FT') { txtClass = "text-red"; } // Não bateu e acabou

            htmlStats += `
                <div class="stat-box">
                    <span class="stat-label">Over ${k}</span>
                    <div class="stat-value ${txtClass}">${val}% ${check}</div>
                    <div class="bar-mini"><div class="bar-fill" style="width:${val}%; background:${cor}"></div></div>
                </div>`;
        });

        return `
            <div class="card match-card">
                <div class="card-body p-3">
                    
                    <div class="scoreboard">
                        <div class="team-col">
                            <img src="${jogo.times.casa_logo}" class="team-logo" onerror="this.src='https://via.placeholder.com/40'">
                            <span class="team-name">${jogo.times.casa}</span>
                        </div>
                        
                        <div>
                            <div class="score-box ${scoreClass}">
                                ${golsCasa} - ${golsFora}
                            </div>
                            <span class="match-time">${tempoTexto}</span>
                        </div>

                        <div class="team-col">
                            <img src="${jogo.times.fora_logo}" class="team-logo" onerror="this.src='https://via.placeholder.com/40'">
                            <span class="team-name">${jogo.times.fora}</span>
                        </div>
                    </div>

                    <div class="grid-stats">
                        ${htmlStats}
                    </div>

                    ${htmlDica}

                </div>
            </div>`;
    }
</script>

</body>
</html>