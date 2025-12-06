<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Titanium Score</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* PALETA TITANIUM DARK */
            --bg-body: #121212; 
            --bg-panel: #1e1e1e; 
            --bg-input: #2d2d2d;
            --border: #333333; 
            --text-white: #ffffff; 
            --text-gray: #a3a3a3;
            
            --success: #22c55e; 
            --warning: #eab308;
            --danger: #ef4444;
            
            /* Cores de Identificação */
            --c-blue: #3b82f6; 
            --c-yellow: #eab308; 
            --c-purple: #a855f7;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; outline: none; -webkit-tap-highlight-color: transparent; }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--bg-body); 
            color: var(--text-white); 
            padding: 10px; 
            font-size: 14px; 
        }

        .app-container { max-width: 600px; margin: 0 auto; display: flex; flex-direction: column; gap: 15px; }

        /* --- 0. HEADER / LOGO --- */
        .main-header {
            display: flex; justify-content: center; align-items: center;
            padding-bottom: 10px; margin-bottom: 5px;
            border-bottom: 1px solid var(--border);
        }
        .brand-area { display: flex; align-items: center; gap: 10px; }
        .brand-text { font-size: 1.5rem; font-weight: 800; letter-spacing: -1px; color: var(--text-white); }
        .brand-highlight { color: var(--c-blue); }

        /* --- 1. SCANNER BOX --- */
        .scanner-box { 
            background: var(--bg-panel); 
            border: 1px dashed var(--border); 
            padding: 12px; 
            border-radius: 8px; 
        }
        .scan-controls { display: flex; gap: 8px; margin-bottom: 10px; }
        
        .select-league {
            flex: 1; background: var(--bg-input); border: 1px solid var(--border);
            color: var(--text-white); padding: 12px; border-radius: 6px;
            font-size: 0.9rem; font-weight: 600; cursor: pointer;
        }

        .btn-scan { 
            padding: 0 20px; background: var(--c-blue); color: white; border: none; 
            border-radius: 6px; font-weight: 700; cursor: pointer; transition: 0.2s;
            font-size: 1.2rem;
        }
        .btn-scan:hover { opacity: 0.9; }
        
        .loader { text-align: center; color: var(--text-gray); font-size: 0.8rem; padding: 10px; display: none; }
        
        .games-list { 
            display: flex; flex-direction: column; gap: 10px; margin-top: 10px; 
            max-height: 400px; overflow-y: auto; 
        }
        
        /* CARD DO JOGO (ATUALIZADO COM FLEXBOX PARA NÃO CORTAR) */
        .game-item { 
            display: flex; 
            justify-content: space-between;
            align-items: center;
            background: var(--bg-input); 
            padding: 15px; 
            border-radius: 8px; 
            border: 1px solid var(--border); 
            cursor: pointer; 
            transition: 0.2s; 
            position: relative; 
            min-height: 95px; /* Altura mínima garantida */
        }
        .game-item:hover { border-color: var(--c-blue); background: #2b2b2b; }
        
        .game-item.started { opacity: 0.6; border-color: #444; background: #1a1a1a; }
        .game-item.started::after { 
            content: 'EM ANDAMENTO'; 
            position: absolute; top: 5px; right: 5px; 
            font-size: 0.55rem; background: var(--danger); color: white; 
            padding: 2px 5px; border-radius: 3px; font-weight: 800; 
        }

        .game-time { font-size: 0.7rem; color: var(--text-gray); margin-bottom: 6px; display: flex; align-items: center; gap: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .game-time svg { width: 12px; height: 12px; stroke: currentColor; }
        
        .game-header { 
            font-weight: 700; color: var(--text-white); margin-bottom: 8px; 
            border-bottom: 1px solid #333; padding-bottom: 6px; font-size: 0.95rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .game-odds-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; font-size: 0.75rem; }
        .bk-info { display: flex; flex-direction: column; }
        .bk-name { font-size: 0.65rem; color: var(--text-gray); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70px; }
        
        .roi-tag { padding: 2px 6px; border-radius: 4px; font-weight: 800; font-size: 0.7rem; color: #000; }
        .roi-green { background: var(--success); }
        .roi-yellow { background: var(--warning); }
        .roi-gray { background: #444; color: #ccc; }

        /* --- 2. ABAS --- */
        .tabs { display: flex; gap: 8px; }
        .tab-btn { 
            flex: 1; background: var(--bg-panel); border: 1px solid var(--border); 
            color: var(--text-gray); padding: 12px; border-radius: 8px; 
            font-weight: 700; font-size: 0.9rem; cursor: pointer; transition: 0.2s; 
        }
        .tab-btn.active { 
            background: #2d2d2d; color: var(--text-white); 
            border-color: var(--text-gray); 
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        /* --- 3. STATUS BAR --- */
        .status-bar {
            background: var(--bg-panel); border: 1px solid var(--border); border-radius: 8px;
            padding: 15px; display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 10px; z-index: 100; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            border-left: 5px solid var(--text-gray); transition: border-color 0.3s;
        }
        .status-bar.win { border-left-color: var(--success); }
        .status-bar.lose { border-left-color: var(--danger); }
        
        .lbl-status { font-size: 0.65rem; text-transform: uppercase; color: var(--text-gray); font-weight: 700; margin-bottom: 2px; }
        .val-status { font-size: 1.5rem; font-weight: 800; color: var(--text-white); }
        .win .val-status { color: var(--success); } 
        .lose .val-status { color: var(--danger); }
        
        .roi-box { text-align: right; }
        .roi-val { font-weight: 700; color: var(--text-gray); font-size: 0.9rem; }
        .cost-val { font-size: 0.7rem; color: var(--text-gray); margin-top: 4px; }

        /* --- 4. CONTROLES --- */
        .controls-row { display: flex; gap: 10px; }
        .control-group { flex: 1; background: var(--bg-panel); padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); }
        
        .inp-config { 
            width: 100%; background: transparent; border: none; 
            color: var(--text-white); font-size: 1.1rem; font-weight: 600; 
            text-align: center; margin-top: 2px; 
        }
        select.inp-config { 
            cursor: pointer; background-color: var(--bg-panel); 
            -webkit-appearance: none; text-align-last: center; 
        }
        select.inp-config option { background-color: var(--bg-panel); color: #fff; }

        /* --- 5. GRID DE APOSTAS --- */
        .bet-list { display: flex; flex-direction: column; gap: 8px; }
        .bet-row {
            display: grid; grid-template-columns: 30px 1fr 0.6fr 1.3fr; gap: 10px;
            background: var(--bg-panel); border: 1px solid var(--border); border-radius: 8px; padding: 12px;
            align-items: center; border-left-width: 4px; transition: background 0.2s;
        }
        .bet-row:focus-within { background: #262626; border-color: #555; }
        
        .bet-row.blue { border-left-color: var(--c-blue); }
        .bet-row.yellow { border-left-color: var(--c-yellow); }
        .bet-row.purple { border-left-color: var(--c-purple); }
        
        .row-id { font-weight: 800; font-size: 1.1rem; text-align: center; }
        .blue .row-id { color: var(--c-blue); } 
        .yellow .row-id { color: var(--c-yellow); } 
        .purple .row-id { color: var(--c-purple); }

        .input-box { position: relative; }
        .input-box label { position: absolute; top: -7px; left: 8px; font-size: 0.55rem; background: var(--bg-panel); padding: 0 4px; color: var(--text-gray); font-weight: 700; z-index: 10;}
        .bet-row:focus-within .input-box label { background: #262626; }
        
        .inp-main { width: 100%; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-white); padding: 10px; border-radius: 4px; font-size: 1.1rem; font-weight: 600; text-align: center; }
        .inp-main:focus { border-color: var(--text-white); background: #383838; }
        
        .inp-small { width: 100%; background: transparent; border: 1px solid var(--border); color: var(--text-gray); padding: 10px 2px; border-radius: 4px; font-size: 0.9rem; text-align: center; }
        .inp-small:focus { border-color: var(--text-gray); color: var(--text-white); }
        
        .res-box { text-align: right; }
        .res-lbl { font-size: 0.6rem; color: var(--text-gray); text-transform: uppercase; display: block; margin-bottom: 2px;}
        .res-val { font-size: 1.1rem; font-weight: 700; color: var(--text-white); }
        .res-curr { font-size: 0.8rem; font-weight: 400; color: var(--text-gray); }
        .res-detail { font-size: 0.65rem; margin-top: 2px; font-weight: 600; white-space: nowrap; }

        .btn-clean { width: 100%; padding: 14px; background: transparent; border: 1px solid var(--border); color: var(--text-gray); border-radius: 6px; margin-top: 10px; cursor: pointer; font-weight: 600; transition: 0.2s;}
        .btn-clean:hover { color: var(--text-white); border-color: var(--text-white); }

        /* --- 6. RODAPÉ --- */
        .app-footer {
            margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);
            text-align: center; color: var(--text-gray); font-size: 0.75rem; line-height: 1.6;
        }
        .version-badge {
            background: var(--bg-panel); padding: 2px 8px; border-radius: 12px;
            border: 1px solid var(--border); font-size: 0.65rem; margin-bottom: 8px;
            display: inline-block; text-transform: uppercase; letter-spacing: 1px;
        }
        .footer-credits strong { color: var(--text-white); font-weight: 600; }
    </style>
</head>
<body>

<div class="app-container">

    <div class="main-header">
        <div class="brand-area">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:32px; height:32px; color:var(--c-blue)"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
            <span class="brand-text">TITANIUM <span class="brand-highlight">SCORE</span></span>
        </div>
    </div>

    <div class="scanner-box">
        <div class="scan-controls">
            <select id="leagueSelect" class="select-league">
                <option value="">Carregando ligas...</option>
            </select>
            <button class="btn-scan" onclick="buscarJogos()">🔍</button>
        </div>
        <div id="loader" class="loader">Pronto.</div>
        <div id="gamesList" class="games-list"></div>
    </div>

    <div class="tabs">
        <button class="tab-btn active" onclick="mudarModo('3way', this)">FUTEBOL (3)</button>
        <button class="tab-btn" onclick="mudarModo('2way', this)">2 VIAS</button>
    </div>

    <div id="statusBar" class="status-bar">
        <div><div class="lbl-status">LUCRO LÍQUIDO REAL</div><div class="val-status" id="valProfit">R$ 0,00</div></div>
        <div class="roi-box"><div class="roi-val" id="valRoi">ROI 0.00%</div><div class="cost-val" id="valCost">Custo: R$ 0,00</div></div>
    </div>

    <div class="controls-row">
        <div class="control-group" style="flex:2"><div class="lbl-status">BANCA META (R$)</div><input type="tel" id="investimento" class="inp-config" value="1.000,00" onfocus="this.value=''" oninput="calcular()"></div>
        <div class="control-group" style="flex:1"><div class="lbl-status">ARRED.</div><select id="arredondamento" class="inp-config" onchange="calcular()"><option value="0">Não</option><option value="0.5">0,50</option><option value="1">1,00</option><option value="2">2,00</option><option value="5">5,00</option><option value="10">10,00</option></select></div>
    </div>

    <div id="inputsArea" class="bet-list"></div>
    <button class="btn-clean" onclick="limparTudo()">LIMPAR DADOS</button>

    <div class="app-footer">
        <span class="version-badge">Versão 2.0 Titanium</span>
        <div class="footer-credits">
            Criado por <strong>Le Miastkuosky</strong>
        </div>
        <div style="opacity: 0.5; font-size: 0.65rem; margin-top: 5px;">
            &copy; <?php echo date('Y'); ?> Titanium Score. Todos os direitos reservados.
        </div>
    </div>

</div>

<script>
    let modoAtual = '3way';
    
    window.onload = function() { 
        carregarLigas(); 
        renderizarInputs(); 
        calcular(); 
    };

    function mudarModo(modo, btn) {
        modoAtual = modo;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderizarInputs();
        calcular();
    }

    // --- 1. CARREGAR LIGAS ---
    async function carregarLigas() {
        const select = document.getElementById('leagueSelect');
        select.innerHTML = '<option>Carregando ligas...</option>';
        try {
            const response = await fetch('api.php?action=leagues');
            const data = await response.json();
            if (data.message) throw new Error(data.message);
            select.innerHTML = ''; 
            const soccerLeagues = data.filter(sport => sport.group === 'Soccer');
            soccerLeagues.sort((a, b) => a.title.localeCompare(b.title));
            soccerLeagues.forEach(league => {
                const opt = document.createElement('option');
                opt.value = league.key;
                opt.text = league.title;
                if(league.key === 'soccer_brazil_campeonato') opt.selected = true;
                select.appendChild(opt);
            });
            if(soccerLeagues.length === 0) select.innerHTML = '<option value="">Nenhuma liga encontrada</option>';
        } catch (e) {
            select.innerHTML = '<option value="">Erro ao carregar</option>';
        }
    }

    // --- 2. DATA E HORA ---
    function formatarDataJogo(isoString) {
        const dataJogo = new Date(isoString);
        const agora = new Date();
        const amanha = new Date(agora);
        amanha.setDate(amanha.getDate() + 1);
        const optionsHora = { hour: '2-digit', minute: '2-digit' };
        const hora = dataJogo.toLocaleTimeString('pt-BR', optionsHora);
        const jaPassou = dataJogo < agora;
        const mesmoDia = (d1, d2) => d1.getDate() === d2.getDate() && d1.getMonth() === d2.getMonth();
        let dataStr = "";
        if (mesmoDia(dataJogo, agora)) dataStr = `HOJE às ${hora}`;
        else if (mesmoDia(dataJogo, amanha)) dataStr = `AMANHÃ às ${hora}`;
        else {
            const optionsData = { weekday: 'short', day: '2-digit', month: '2-digit' };
            dataStr = `${dataJogo.toLocaleDateString('pt-BR', optionsData)} às ${hora}`;
        }
        return { texto: dataStr, jaPassou: jaPassou };
    }

    // --- 3. BUSCAR JOGOS (COM CORREÇÃO DE LAYOUT) ---
    async function buscarJogos() {
        const list = document.getElementById('gamesList');
        const loader = document.getElementById('loader');
        const leagueKey = document.getElementById('leagueSelect').value;

        list.innerHTML = ''; 
        loader.innerText = "Buscando jogos e odds...";
        loader.style.display = 'block';

        try {
            const response = await fetch('api.php?action=odds&sport=' + leagueKey);
            if (!response.ok) throw new Error("Erro 404: api.php não encontrado.");
            const text = await response.text();
            let data; try { data = JSON.parse(text); } catch (e) { throw new Error("Erro no JSON da API."); }
            loader.style.display = 'none';
            if(data.message) throw new Error("API: " + data.message);
            if(!data || data.length === 0) { list.innerHTML = '<div style="padding:10px; text-align:center; color:#666">Nenhum jogo disponível.</div>'; return; }

            let oportunidades = [];
            data.forEach(game => {
                let best = { h: 0, d: 0, a: 0, h_bk: '--', d_bk: '--', a_bk: '--' };
                game.bookmakers.forEach(bk => {
                    let m = bk.markets.find(m => m.key === 'h2h');
                    if(m) {
                        m.outcomes.forEach(o => {
                            if(o.name === 'Draw') { if(o.price > best.d) { best.d = o.price; best.d_bk = bk.title; } }
                            else if(o.name === game.home_team) { if(o.price > best.h) { best.h = o.price; best.h_bk = bk.title; } }
                            else { if(o.price > best.a) { best.a = o.price; best.a_bk = bk.title; } }
                        });
                    }
                });
                if(best.h > 0) {
                    let probTotal = (1/best.h) + (1/best.d) + (1/best.a);
                    let roi = (1 / probTotal) - 1;
                    oportunidades.push({ game: game, odds: best, roi: roi });
                }
            });

            oportunidades.sort((a, b) => b.roi - a.roi);

            oportunidades.forEach(item => {
                let roiPerc = (item.roi * 100).toFixed(2) + "%";
                let badgeClass = "roi-gray";
                if(item.roi > 0) badgeClass = "roi-green"; else if(item.roi > -0.02) badgeClass = "roi-yellow";
                
                const infoData = formatarDataJogo(item.game.commence_time);
                const classePassou = infoData.jaPassou ? "started" : "";

                const div = document.createElement('div');
                div.className = `game-item ${classePassou}`;
                
                // LAYOUT CORRIGIDO (FLEX) PARA NÃO CORTAR
                div.innerHTML = `
                    <div style="flex: 1; min-width: 0; padding-right: 10px;">
                        <div class="game-time"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>${infoData.texto}</div>
                        <div class="game-header"><span>${item.game.home_team} x ${item.game.away_team}</span><span class="roi-tag ${badgeClass}">${roiPerc}</span></div>
                        <div class="game-odds-grid">
                            <div class="bk-info" style="color:var(--c-blue)"><b>${item.odds.h.toFixed(2)}</b><span class="bk-name">${item.odds.h_bk}</span></div>
                            <div class="bk-info" style="color:var(--c-yellow)"><b>${item.odds.d.toFixed(2)}</b><span class="bk-name">${item.odds.d_bk}</span></div>
                            <div class="bk-info" style="color:var(--c-purple)"><b>${item.odds.a.toFixed(2)}</b><span class="bk-name">${item.odds.a_bk}</span></div>
                        </div>
                    </div>
                    <div style="font-size:1.5rem; opacity:0.5; margin-left:10px; color:var(--c-blue);">👉</div>
                `;
                div.onclick = () => {
                    if(modoAtual !== '3way') mudarModo('3way', document.querySelector('.tab-btn'));
                    document.getElementById('odd1').value = item.odds.h;
                    document.getElementById('oddX').value = item.odds.d;
                    document.getElementById('odd2').value = item.odds.a;
                    calcular();
                    document.getElementById('statusBar').scrollIntoView({behavior: 'smooth'});
                };
                list.appendChild(div);
            });
        } catch (e) { loader.style.display = 'none'; list.innerHTML = `<div style="color:#ef4444; padding:10px; font-size:0.8rem;">${e.message}</div>`; }
    }

    function renderizarInputs() {
        const area = document.getElementById('inputsArea');
        area.innerHTML = ''; 
        let campos = (modoAtual === '3way') 
            ? [{id:'odd1', s:'1', c:'blue'}, {id:'oddX', s:'X', c:'yellow'}, {id:'odd2', s:'2', c:'purple'}]
            : [{id:'odd1', s:'1', c:'blue'}, {id:'odd2', s:'2', c:'purple'}];

        campos.forEach(cp => {
            const html = `
            <div class="bet-row ${cp.c}">
                <div class="row-id">${cp.s}</div>
                <div class="input-box"><label>ODD</label><input type="tel" id="${cp.id}" class="inp-main" placeholder="0.00" onfocus="this.value=''" oninput="mascaraOdd(this); calcular()"></div>
                <div class="input-box"><label>TAXA</label><input type="tel" id="cambio_${cp.id}" class="inp-small" placeholder="1.00" value="1,00" onfocus="this.value=''" oninput="mascaraOdd(this); calcular()"></div>
                <div class="res-box"><span class="res-lbl">APOSTAR</span><div><span class="res-val" id="res_${cp.id}">0,00</span><span class="res-curr" id="curr_${cp.id}">BRL</span></div><div class="res-detail" id="detail_${cp.id}"></div></div>
            </div>`;
            area.insertAdjacentHTML('beforeend', html);
        });
    }

    function calcular() {
        const invTarget = convFloat(document.getElementById('investimento').value);
        const roundFactor = parseFloat(document.getElementById('arredondamento').value);
        let ids = (modoAtual === '3way') ? ['odd1', 'oddX', 'odd2'] : ['odd1', 'odd2'];
        let odds = [], cambios = [], probs = [], somaProb = 0, valid = true;

        ids.forEach(id => {
            let val = convFloat(document.getElementById(id).value);
            let camb = convFloat(document.getElementById('cambio_'+id).value) || 1;
            if(val <= 1) valid = false;
            odds.push(val); cambios.push(camb);
            let p = val > 0 ? 1/val : 0; probs.push(p); somaProb += p;
        });

        if(!valid || somaProb === 0) {
            atualizarStatus(0, 0, false);
            ids.forEach(id => {
                document.getElementById('res_'+id).innerText = '---';
                document.getElementById('curr_'+id).innerText = '';
                document.getElementById('detail_'+id).innerText = '';
            });
            return;
        }

        let custoRealTotal = 0, stakeList = [], moedaList = [];
        ids.forEach((id, i) => {
            let stakeBRL = (invTarget * probs[i]) / somaProb;
            let stakeLocal = stakeBRL / cambios[i];
            if(roundFactor > 0) {
                let rounded = Math.round(stakeLocal / roundFactor) * roundFactor;
                if(stakeLocal > 0 && rounded === 0) rounded = roundFactor;
                stakeLocal = rounded;
            }
            stakeLocal = parseFloat(stakeLocal.toFixed(2));
            custoRealTotal += stakeLocal * cambios[i];
            stakeList.push(stakeLocal);
            moedaList.push((cambios[i] > 1.5) ? 'USD' : 'BRL');
        });

        let payouts = [];
        ids.forEach((id, i) => {
            let stakeLocal = stakeList[i];
            let retornoBruto = (stakeLocal * odds[i]) * cambios[i];
            let lucroLiq = retornoBruto - custoRealTotal;
            payouts.push(lucroLiq);
            document.getElementById('res_'+id).innerText = formatMoeda(stakeLocal);
            document.getElementById('curr_'+id).innerText = moedaList[i];
            let sinal = lucroLiq >= 0 ? '+' : '';
            let cor = lucroLiq >= 0 ? '#4ade80' : '#ef4444';
            document.getElementById('detail_'+id).innerHTML = `<span style="color:${cor}">Se bater: ${sinal}R$ ${formatMoeda(lucroLiq)}</span>`;
        });

        let lucroMin = Math.min(...payouts);
        let lucroMax = Math.max(...payouts);
        let txtLucro = (Math.abs(lucroMin - lucroMax) > 0.05) ? formatMoeda(lucroMin) + " ~ " + formatMoeda(lucroMax) : formatMoeda(lucroMin);
        atualizarStatus(lucroMin, custoRealTotal, somaProb < 1, txtLucro);
    }

    function atualizarStatus(lucro, custo, isSurebet, txtLucro) {
        const bar = document.getElementById('statusBar');
        const val = document.getElementById('valProfit');
        const roi = document.getElementById('valRoi');
        const cost = document.getElementById('valCost');
        bar.classList.remove('win', 'lose');
        if (custo === 0) { val.innerText = "R$ 0,00"; roi.innerText = "0.00%"; cost.innerText = "Custo: R$ 0,00"; return; }
        val.innerText = (lucro >= 0 ? '+' : '') + "R$ " + (txtLucro || formatMoeda(lucro));
        roi.innerText = "ROI " + ((lucro/custo)*100).toFixed(2) + "%";
        cost.innerText = "Custo Real: R$ " + formatMoeda(custo);
        if (lucro > 0 || isSurebet) bar.classList.add('win'); else bar.classList.add('lose');
    }

    function convFloat(v) { if(!v) return 0; if(v.indexOf(',') > -1) return parseFloat(v.replace(/\./g,'').replace(',','.')); return parseFloat(v); }
    function formatMoeda(v) { return v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function mascaraOdd(i) { let v = i.value.replace(/\D/g,''); v = (v/100).toFixed(2) + ''; v = v.replace(".", ","); i.value = v; }
    function limparTudo() { document.querySelectorAll('.inp-main').forEach(i => i.value = ''); document.querySelectorAll('.inp-small').forEach(i => i.value = '1,00'); calcular(); }
</script>

</body>
</html>