<?php
// =========================================================
// 🔮 ORÁCULO V6.1 - DESIGN V6 + I.A. NARRATIVA REAL
// =========================================================
set_time_limit(300); 
date_default_timezone_set('America/Sao_Paulo');

$dbHost = 'localhost'; $dbName = 'fut_analises'; $dbUser = 'root'; $dbPass = '';
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
} catch (PDOException $e) { die("ERRO SQL."); }

$stmt = $pdo->query("SELECT liga as c, data_jogo as d, time_casa as h, time_fora as a, gols_casa as gh, gols_fora as ga FROM historico_jogos ORDER BY data_jogo DESC");
$dbGames = $stmt->fetchAll(PDO::FETCH_ASSOC);

$upcomingGames = [];
if (file_exists('upcoming_odds.json')) {
    $upcomingGames = json_decode(file_get_contents('upcoming_odds.json'), true);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>ORÁCULO MASTER</title>
<link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
<style>
    /* --- DESIGN ORIGINAL V6.0 MANTIDO --- */
    :root { --bg: #000000; --term-green: #00ff41; --term-dim: #008F11; --term-glow: 0 0 10px rgba(0, 255, 65, 0.5); --highlight: #ffee00; --home: #00ffff; --away: #ff00ff; --card-bg: #050505; }
    * { box-sizing: border-box; }
    body { background: var(--bg); color: var(--term-green); font-family: 'VT323', monospace; margin: 0; padding: 15px; font-size: 20px; overflow-x: hidden; }
    .container { max-width: 900px; margin: 0 auto; padding-bottom: 80px; }
    
    .search-wrapper { text-align: center; margin: 20px 0; border-bottom: 2px dashed var(--term-dim); padding-bottom: 20px; }
    h1 { font-size: 48px; margin: 0; letter-spacing: 4px; line-height: 1; }
    .data-info { font-size: 16px; color: var(--highlight); margin: 10px 0; text-shadow: 0 0 10px rgba(255,238,0,0.4); }

    .input-group { margin-top: 20px; position: relative; max-width: 600px; margin: 20px auto; height: 55px; }
    input { width: 100%; height: 100%; padding: 15px 90px 15px 15px; background: #000; border: 2px solid var(--term-green); color: var(--term-green); font-family: 'VT323'; font-size: 22px; text-align: center; outline: none; text-transform: uppercase; }
    .btn-clear { position: absolute; right: 55px; top: 0; height: 100%; width: 40px; background: transparent; border: none; color: #333; font-size: 20px; cursor: pointer; }
    .btn-clear:hover { color: #f00; }
    .btn-go { position: absolute; right: 0; top: 0; height: 100%; width: 55px; background: rgba(0,255,65,0.1); color: var(--term-green); border: none; border-left: 1px solid var(--term-dim); font-size: 24px; cursor: pointer; font-family: 'VT323'; }

    .card { background: var(--card-bg); border: 1px solid var(--term-green); margin-bottom: 20px; padding: 15px; box-shadow: inset 0 0 20px rgba(0,255,65,0.05); }
    .versus-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
    .team-col { width: 45%; display: flex; flex-direction: column; align-items: center; }
    .team-name { font-size: 26px; text-align: center; border-bottom: 1px solid var(--term-dim); width: 100%; line-height: 1.1; word-wrap: break-word; }
    .league-tag { font-size: 14px; color: #888; margin-top: 4px; text-transform: uppercase; }
    .vs { font-size: 24px; color: var(--term-dim); margin-top: 20px; }

    .sequence-row { display: flex; gap: 4px; margin-top: 8px; justify-content: center; }
    .ball { width: 10px; height: 10px; border-radius: 50%; }
    .ball-w { background: #00ff41; box-shadow: 0 0 5px #00ff41; }
    .ball-d { background: #ffff00; box-shadow: 0 0 5px #ffff00; }
    .ball-l { background: #ff0000; box-shadow: 0 0 5px #ff0000; }
    .seq-label { font-size: 10px; color: #888; text-transform: uppercase; margin-top: 2px; }

    .comp-row { display: flex; align-items: center; margin-bottom: 12px; }
    .comp-val { width: 15%; text-align: center; font-weight: bold; }
    .comp-bar-area { flex: 1; height: 10px; background: #222; border: 1px solid #444; position: relative; margin: 0 10px; }
    .comp-bar-fill { height: 100%; position: absolute; top: 0; }
    .fill-l { left: 0; background: var(--home); } .fill-r { right: 0; background: var(--away); }
    .comp-label { width: 100%; text-align: center; font-size: 14px; color: var(--term-dim); margin-bottom: 3px; }
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; text-align: center; border: 1px solid var(--term-dim); padding: 10px; margin-top: 15px; }
    .stat-val { font-size: 28px; display: block; line-height: 1; } .stat-lbl { font-size: 14px; color: var(--term-dim); }

    .final-verdict-box { margin-top: 20px; border: 2px dashed var(--highlight); background: rgba(255,238,0,0.05); padding: 15px; text-align: center; box-shadow: 0 0 15px rgba(255, 238, 0, 0.2); }
    .final-verdict-title { font-size: 22px; color: var(--highlight); font-weight: bold; margin-bottom: 10px; }
    .final-verdict-text { font-size: 20px; color: #fff; font-style: italic; white-space: pre-wrap; margin-bottom: 15px; }
    .suggestion-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .sugg-box { border: 1px solid #333; padding: 8px; background: #000; }
    .sugg-label { font-size: 14px; color: #888; } .sugg-val { font-size: 20px; color: var(--term-green); font-weight: bold; }
    .ai-block { border-left: 4px solid var(--term-green); padding-left: 15px; margin-top: 15px; font-size: 18px; background: rgba(0,50,0,0.3); white-space: pre-wrap; }

    .loading { margin-top: 50px; text-align: center; display: none; font-size: 22px; }
    .blink { animation: blinker 1s linear infinite; }
    @keyframes blinker { 50% { opacity: 0; } }
    @media (max-width: 600px) { h1 { font-size: 36px; } .team-name { font-size: 20px; } }
</style>
</head>
<body>

<div class="container">
    <div class="search-wrapper">
        <h1>ORÁCULO <span class="blink">_</span></h1>
        <div class="data-info">[SISTEMA]: BASE HÍBRIDA (HISTÓRICO + FUTURO)</div>
        <div class="input-group">
            <input type="text" id="searchInput" placeholder="BUSCAR..." onkeyup="handleEnter(event)" autocomplete="off">
            <button class="btn-clear" onclick="clearSearch()" title="Limpar">✕</button>
            <button class="btn-go" onclick="processQuery()">></button>
        </div>
        <div style="font-size:12px; color:var(--term-dim); margin-top:8px;">[ENTER] PARA PROCESSAR | "DICAS" PARA OPORTUNIDADES</div>
    </div>
    <div id="loading" class="loading">[NORMALIZANDO]...<br>ANALISANDO NARRATIVA...<br><span class="blink">█</span></div>
    <div id="reportArea" class="report-section"></div>
</div>

<script>
const allGames = <?php echo json_encode($dbGames); ?>;
const upcomingGames = <?php echo json_encode($upcomingGames); ?>;
let uniqueTeamNames = [];

// BANCO DE FRASES DA I.A. (NOVO)
const phrases = {
    homeStrong: [
        "O mandante transforma seu estádio em um caldeirão.",
        "Jogar em casa tem sido sinônimo de lucro para esta equipe.",
        "A superioridade técnica local é gritante neste cenário."
    ],
    awayStrong: [
        "O visitante é um hóspede indigesto e costuma roubar pontos.",
        "Surpreendentemente, este time joga melhor fora do que em casa.",
        "A equipe visitante tem um contra-ataque letal."
    ],
    balanced: [
        "Um confronto de xadrez: duas defesas sólidas e pouco espaço.",
        "Equilíbrio total. Este jogo será decidido nos detalhes.",
        "As estatísticas mostram um espelho: forças muito parecidas."
    ],
    goalsHigh: [
        "As defesas são peneiras. Espere gols.",
        "Dois ataques que funcionam e duas zagas que dormem.",
        "A tendência é um tiroteio. O placar não deve ficar zerado."
    ],
    goalsLow: [
        "Jogo travado, de muita marcação e pouca criatividade.",
        "Não espere um espetáculo. A tendência é Under.",
        "O medo de perder será maior que a vontade de ganhar."
    ]
};

window.onload = function() {
    const teamSet = new Set();
    allGames.forEach(g => { teamSet.add(g.h); teamSet.add(g.a); });
    if(upcomingGames) upcomingGames.forEach(g => { teamSet.add(g.home_team); teamSet.add(g.away_team); });
    uniqueTeamNames = Array.from(teamSet);
};

function handleEnter(e) { if(e.key === 'Enter') processQuery(); }
function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('searchInput').focus();
    document.getElementById('reportArea').style.display = 'none';
}
function normalizeText(text) { return text.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase(); }
function getRandom(arr) { return arr[Math.floor(Math.random() * arr.length)]; }

function generateNarrative(tA, tB, diff, goals, sA, sB) {
    let text = "";
    // Analise de Força
    if(diff > 25) text += getRandom(phrases.homeStrong);
    else if(diff < -15) text += getRandom(phrases.awayStrong);
    else text += getRandom(phrases.balanced);

    text += " ";
    // Analise de Gols
    if(goals > 2.8) text += getRandom(phrases.goalsHigh);
    else if(goals < 1.9) text += getRandom(phrases.goalsLow);

    // Momento (Check de sequencia recente)
    let homeForm = sA.sequence.slice(-3).filter(x=>x==='W').length;
    let awayForm = sB.sequence.slice(-3).filter(x=>x==='W').length;
    
    if(homeForm === 3) text += ` O ${tA} vem embalado com vitórias recentes.`;
    else if(awayForm === 3) text += ` Atenção ao momento iluminado do ${tB}.`;
    
    return text;
}

function processQuery() {
    let rawQuery = document.getElementById('searchInput').value;
    let query = normalizeText(rawQuery).trim();
    if(query.length < 2) return;
    document.getElementById('searchInput').blur();
    document.getElementById('reportArea').innerHTML = '';
    document.getElementById('loading').style.display = 'block';
    document.getElementById('reportArea').style.display = 'none';

    setTimeout(() => {
        if (["dicas","dica","aposta","jogos"].some(k => query.includes(k))) {
            document.getElementById('reportArea').innerHTML = buildBestTipsReport();
        } else {
            let count = 20;
            const numberMatch = query.match(/(\d+)/);
            if (numberMatch) { count = parseInt(numberMatch[0]); if(count<5)count=5; if(count>100)count=100; }
            
            const foundTeams = extractTeamsFromText(rawQuery);
            let html = "";
            
            if (foundTeams.length >= 2) html = buildComparisonReport(foundTeams[0], foundTeams[1], count);
            else if (foundTeams.length === 1) html = buildTeamReport(foundTeams[0], count);
            else html = `<div class='card' style='text-align:center; color:red;'>[ERRO]: TIME NÃO ENCONTRADO.<br><small style="color:#aaa">Tente o nome simples (ex: "Flamengo").</small></div>`;
            
            document.getElementById('reportArea').innerHTML = html;
        }
        document.getElementById('loading').style.display = 'none';
        document.getElementById('reportArea').style.display = 'block';
    }, 800);
}

function renderSequence(seqArray) {
    if(!seqArray || seqArray.length === 0) return '<div class="seq-label">SEM DADOS</div>';
    let recent = seqArray.slice(0, 10).reverse();
    let html = '<div class="sequence-row">';
    recent.forEach(r => { let cls = r==='W'?'ball-w':(r==='D'?'ball-d':'ball-l'); html += `<div class="ball ${cls}"></div>`; });
    return html + '</div><div class="seq-label">Últimos 10</div>';
}

function buildComparisonReport(tA, tB, count) {
    const sA = getAdvancedStats(tA, count);
    const sB = getAdvancedStats(tB, count);
    if(!sA || !sB) return `<div class='card' style='text-align:center;'><div class="card-header"><span class="card-title">${tA} x ${tB}</span></div><div class="ai-block">Times na base, mas sem histórico suficiente.</div></div>`;

    const lA = getLeagueInfo(tA)||"DESCONHECIDO"; const lB = getLeagueInfo(tB)||"";
    let p = predictScore(tA, tB, sA.league);
    let hWin=parseFloat(sA.home.winRate), aWin=parseFloat(sB.away.winRate);
    let diff=hWin-aWin; let goals=parseFloat(sA.home.avgGF)+parseFloat(sB.away.avgGF);
    let btts=(parseFloat(sA.home.avgGF)>0.8 && parseFloat(sB.away.avgGF)>0.8 && parseFloat(sA.home.avgGA)>0.8 && parseFloat(sB.away.avgGA)>0.8);

    // PALPITES
    let pick="", safe="";
    if(diff>25) { pick=`VITÓRIA ${tA}`; safe="CASA OU EMPATE"; }
    else if(diff<-15) { pick=`CHANCE DUPLA ${tB}`; safe=`HANDICAP +1 ${tB}`; }
    else if(goals>2.8) { pick="OVER 2.5 GOLS"; safe="OVER 1.5"; }
    else if(goals<1.8) { pick="UNDER 2.5 GOLS"; safe="UNDER 3.5"; }
    else { pick=btts?"AMBAS MARCAM":"EMPATE"; safe=btts?"OVER 1.5":"UNDER 3.5"; }

    // CHAMA O NOVO MOTOR DE NARRATIVA
    let opinion = generateNarrative(tA, tB, diff, goals, sA, sB);

    return `
    <div class="card">
        <div class="versus-header">
            <div class="team-col"><div class="team-name" style="color:var(--home)">${tA}<div class="league-tag">${lA}</div></div>${renderSequence(sA.sequence)}</div>
            <div class="vs">X</div>
            <div class="team-col"><div class="team-name" style="color:var(--away)">${tB}<div class="league-tag">${lB}</div></div>${renderSequence(sB.sequence)}</div>
        </div>
        <div style="text-align:center;font-size:14px;color:var(--term-dim); margin-top:15px;">PROBABILIDADES</div>
        <div style="display:flex;justify-content:center;gap:20px;font-size:32px;">
            <div style="color:${p.probHome>40?'#fff':'inherit'}">${p.probHome.toFixed(0)}%</div><div style="color:yellow;">${p.probDraw.toFixed(0)}%</div><div style="color:${p.probAway>40?'#fff':'inherit'}">${p.probAway.toFixed(0)}%</div>
        </div>
    </div>
    <div class="card">${renderCompBar("ATAQUE",sA.home.avgGF,sB.away.avgGF)}${renderCompBar("DEFESA",sA.home.avgGA,sB.away.avgGA)}${renderCompBar("WIN RATE",sA.home.winRate,sB.away.winRate)}</div>
    <div class="final-verdict-box">
        <div class="final-verdict-title">⚡ VEREDITO ⚡</div><div class="final-verdict-text">"${opinion}"</div>
        <div style="margin-bottom:15px;"><div style="font-size:14px;color:#888;">PALPITE</div><div style="font-size:28px;font-weight:bold;color:var(--highlight);">${pick}</div></div>
        <div class="suggestion-grid">
            <div class="sugg-box"><div class="sugg-label">PLACAR</div><div class="sugg-val" style="color:#fff;">${p.mostLikelyScore}</div></div>
            <div class="sugg-box"><div class="sugg-label">AMBAS?</div><div class="sugg-val">${btts?"SIM":"NÃO"}</div></div>
            <div class="sugg-box"><div class="sugg-label">SEGURANÇA</div><div class="sugg-val" style="color:var(--term-green);font-size:18px;">${safe}</div></div>
            <div class="sugg-box"><div class="sugg-label">EXP GOLS</div><div class="sugg-val" style="color:#fff;">${goals.toFixed(2)}</div></div>
        </div>
    </div>`;
}

function buildTeamReport(teamName, count) {
    const t = findOfficialName(teamName); if(!t) return `<div class='card'>[ERRO] TIME NÃO ACHADO.</div>`;
    const s = getAdvancedStats(t, count);
    if(!s) return `<div class='card' style='text-align:center;'><div class="card-header"><span class="card-title">${t}</span></div><div class="ai-block">Time na agenda futura, mas sem histórico.</div></div>`;
    
    const l = getLeagueInfo(t)||"Liga Desconhecida";
    let op = parseFloat(s.winRate)>60 ? "Fase sólida e consistente." : "Momento de instabilidade.";

    return `
    <div class="card">
        <div class="card-header"><span class="card-title">${t}</span><span style="font-size:14px; color:#888;">${l}</span></div>
        <div style="text-align:center; margin-bottom:15px;">${renderSequence(s.sequence)}</div>
        <div class="stats-grid">
            <div class="stat-box"><span class="stat-val">${s.winRate}%</span><span class="stat-lbl">WIN RATE</span></div>
            <div class="stat-box"><span class="stat-val">${s.avgGF}</span><span class="stat-lbl">GOLS PRÓ</span></div>
            <div class="stat-box"><span class="stat-val">${s.avgGA}</span><span class="stat-lbl">GOLS SOFR</span></div>
        </div>
    </div>
    <div class="final-verdict-box"><div class="final-verdict-title">⚡ ANÁLISE ⚡</div><div class="final-verdict-text">"${op}"</div></div>`;
}

function buildBestTipsReport() {
    if(!upcomingGames || upcomingGames.length===0) return `<div class='card' style='text-align:center;'>SEM JOGOS FUTUROS.</div>`;
    let tips=[];
    upcomingGames.forEach(g=>{
        let tA=findOfficialName(g.home_team), tB=findOfficialName(g.away_team);
        if(tA && tB) {
            let sA=getAdvancedStats(tA,10), sB=getAdvancedStats(tB,10);
            if(sA && sB) {
                let hR=parseFloat(sA.home.winRate), goals=parseFloat(sA.home.avgGF)+parseFloat(sB.away.avgGF);
                if(parseFloat(sA.home.avgGF)>1.2 && parseFloat(sB.away.avgGF)>1.2) tips.push({m:`${tA} x ${tB}`,t:"AMBAS MARCAM",r:"Ataques fortes",c:80});
                if(hR>65) tips.push({m:`${tA} x ${tB}`,t:"CASA VENCE",r:`Mandante ${hR}%`,c:hR});
                else if(goals>3) tips.push({m:`${tA} x ${tB}`,t:"OVER 2.5",r:`Gols Esp: ${goals.toFixed(1)}`,c:goals*20});
            }
        }
    });
    tips.sort((a,b)=>b.c-a.c);
    let html=`<div class="card"><div class="card-header"><span class="card-title">TOP OPORTUNIDADES</span></div>`;
    tips.slice(0,5).forEach(tp=>{ html+=`<div style="border-bottom:1px solid #222;padding:10px 0;display:flex;justify-content:space-between;"><div><div style="font-size:18px;">${tp.m}</div><small style="color:#888;">${tp.r}</small></div><div style="background:var(--highlight);color:#000;padding:2px 8px;font-weight:bold;height:fit-content;">${tp.t}</div></div>`; });
    return html+`</div>`;
}

function findOfficialName(input) {
    input = normalizeText(input);
    let f = allGames.find(g => normalizeText(g.h).includes(input) || normalizeText(g.a).includes(input));
    if(f) return normalizeText(f.h).includes(input) ? f.h : f.a;
    if(upcomingGames) {
        let u = upcomingGames.find(g => normalizeText(g.home_team).includes(input) || normalizeText(g.away_team).includes(input));
        if(u) return normalizeText(u.home_team).includes(input) ? u.home_team : u.away_team;
    }
    return null;
}

function extractTeamsFromText(text) {
    let foundMatches=[]; let clean=normalizeText(text); let temp=clean;
    uniqueTeamNames.sort((a,b)=>b.length-a.length);
    uniqueTeamNames.forEach(team=>{
        let tLow=normalizeText(team); let idx=temp.indexOf(tLow);
        if(idx!==-1) { foundMatches.push({name:team,index:idx}); temp=temp.substring(0,idx)+" ".repeat(tLow.length)+temp.substring(idx+tLow.length); }
    });
    foundMatches.sort((a,b)=>a.index-b.index);
    return foundMatches.map(i=>i.name).slice(0,2);
}

function getAdvancedStats(team, count) {
    let games = allGames.filter(g=>g.h===team || g.a===team).slice(0,count);
    if(games.length===0) return null;
    let s={total:games.length,wins:0,draws:0,losses:0,gf:0,ga:0,home:{games:0,wins:0,draws:0,losses:0,gf:0,ga:0,winRate:0,avgGF:0,avgGA:0},away:{games:0,wins:0,draws:0,losses:0,gf:0,ga:0,winRate:0,avgGF:0,avgGA:0},league:games[0].c,sequence:[]};
    games.forEach(g=>{
        let isHome=(g.h===team); let sc=isHome?g.gh:g.ga; let co=isHome?g.ga:g.gh; s.gf+=sc; s.ga+=co;
        let r=''; if(sc>co){s.wins++;r='W';}else if(sc==co){s.draws++;r='D';}else{s.losses++;r='L';} s.sequence.push(r);
        if(isHome){ s.home.games++; s.home.gf+=sc; s.home.ga+=co; if(sc>co)s.home.wins++;else if(sc==co)s.home.draws++;else s.home.losses++; }
        else{ s.away.games++; s.away.gf+=sc; s.away.ga+=co; if(sc>co)s.away.wins++;else if(sc==co)s.away.draws++;else s.away.losses++; }
    });
    const avg=(v,t)=>t>0?(v/t).toFixed(2):"0.00"; const rate=(v,t)=>t>0?((v/t)*100).toFixed(0):0;
    s.home.avgGF=avg(s.home.gf,s.home.games); s.home.avgGA=avg(s.home.ga,s.home.games); s.home.winRate=rate(s.home.wins,s.home.games);
    s.away.avgGF=avg(s.away.gf,s.away.games); s.away.avgGA=avg(s.away.ga,s.away.games); s.away.winRate=rate(s.away.wins,s.away.games);
    s.winRate=rate(s.wins,s.total); s.avgGF=avg(s.gf,s.total); s.avgGA=avg(s.ga,s.total);
    return s;
}

function renderCompBar(l,vA,vB) {
    let a=parseFloat(vA), b=parseFloat(vB), t=a+b||1; let pA=(a/t)*100, pB=(b/t)*100;
    return `<div style="margin-bottom:15px;"><div class="comp-label">${l}</div><div class="comp-row"><div class="comp-val" style="color:var(--home)">${vA}</div><div class="comp-bar-area"><div class="comp-bar-fill fill-l" style="width:${pA}%"></div><div class="comp-bar-fill fill-r" style="width:${pB}%"></div></div><div class="comp-val" style="color:var(--away)">${vB}</div></div></div>`;
}

function factorial(n){if(n==0||n==1)return 1;let r=1;for(let i=2;i<=n;i++)r*=i;return r;}
function poisson(k,l){return(Math.pow(Math.E,-l)*Math.pow(l,k))/factorial(k);}
function predictScore(h,a,lg) {
    let all=allGames.filter(g=>g.c===lg); if(all.length<20) all=allGames;
    let tH=0,tA=0; all.forEach(g=>{tH+=g.gh;tA+=g.ga;});
    let avgH=tH/all.length, avgA=tA/all.length;
    let hG=all.filter(g=>g.h===h), aG=all.filter(g=>g.a===a);
    if(hG.length<3||aG.length<3) return {probHome:33,probDraw:33,probAway:33,mostLikelyScore:"?-?"};
    let hs=0,hc=0; hG.forEach(g=>{hs+=g.gh;hc+=g.ga;}); let as=0,ac=0; aG.forEach(g=>{as+=g.ga;ac+=g.gh;});
    let attH=(hs/hG.length)/avgH, defH=(hc/hG.length)/avgA, attA=(as/aG.length)/avgA, defA=(ac/aG.length)/avgH;
    let lamH=attH*defA*avgH, lamA=attA*defH*avgA;
    let w=0,d=0,l=0,ov=0,maxP=0,sc="0-0";
    for(let x=0;x<6;x++){ for(let y=0;y<6;y++){ let p=poisson(x,lamH)*poisson(y,lamA); if(x>y)w+=p; else if(x==y)d+=p; else l+=p; if(p>maxP){maxP=p;sc=`${x}-${y}`;} }}
    return {probHome:w*100,probDraw:d*100,probAway:l*100,mostLikelyScore:sc};
}
function getLeagueInfo(t) { let g=allGames.find(g=>g.h===t||g.a===t); return g?g.c:""; }
</script>
</body>
</html>