<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

/* Publicar o placar no fim da revelação */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'publish_scoreboard') {
    setSetting($pdo, 'scoreboard_hidden', '0');
    header("Location: " . BASE_URL . "index.php");
    exit;
}

/* Pega todos os empatados no topo de uma lista já ordenada */
function topTied(array $rows, string $field): array {
    if (count($rows) === 0) return [];
    $best = (int) $rows[0][$field];
    return array_values(array_filter($rows, fn($r) => (int)$r[$field] === $best));
}

/* EQUIPES */
$teams = $pdo->query("
    SELECT id, name, color, total_score
    FROM teams
    ORDER BY total_score DESC, id ASC
    LIMIT 2
")->fetchAll();
$teamWinners = topTied($teams, 'total_score');

/* JOGADOR */
$players = $pdo->query("
    SELECT users.id, users.name, users.total_score, teams.name AS team_name, teams.color AS team_color
    FROM users
    LEFT JOIN teams ON teams.id = users.team_id
    WHERE users.role = 'player' AND users.is_active = 1
    ORDER BY users.total_score DESC, users.name ASC
")->fetchAll();
$playerWinners = (count($players) && (int)$players[0]['total_score'] > 0) ? topTied($players, 'total_score') : [];

/* FANTASIA */
$costumes = $pdo->query("
    SELECT users.id, users.name, users.costume_photo, COUNT(costume_votes.id) AS total_votes
    FROM users
    LEFT JOIN costume_votes ON costume_votes.voted_user_id = users.id
    WHERE users.role = 'player'
    AND users.is_active = 1
    AND users.costume_photo IS NOT NULL AND users.costume_photo <> ''
    GROUP BY users.id, users.name, users.costume_photo
    ORDER BY total_votes DESC, users.name ASC
")->fetchAll();
$costumeWinners = (count($costumes) && (int)$costumes[0]['total_votes'] > 0) ? topTied($costumes, 'total_votes') : [];

$data = [
    'teams' => array_map(fn($t) => [
        'name' => $t['name'],
        'color' => $t['color'],
        'score' => (int)$t['total_score'],
    ], $teams),
    'teamWinners' => array_map(fn($t) => ['name' => $t['name'], 'color' => $t['color'], 'score' => (int)$t['total_score']], $teamWinners),
    'playerWinners' => array_map(fn($p) => [
        'name' => $p['name'],
        'team' => $p['team_name'],
        'color' => $p['team_color'],
        'score' => (int)$p['total_score'],
    ], $playerWinners),
    'podium' => array_map(fn($p) => ['name' => $p['name'], 'score' => (int)$p['total_score']], array_slice($players, 0, 5)),
    'costumeWinners' => array_map(fn($c) => [
        'name' => $c['name'],
        'photo' => BASE_URL . $c['costume_photo'],
        'votes' => (int)$c['total_votes'],
    ], $costumeWinners),
];

$scoreboardHidden = isScoreboardHidden($pdo);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>A Grande Revelação | HALLOWEEN 2026</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Creepster&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/images/favicon.png">

    <style>
        :root {
            --orange: #ff7518;
            --purple: #9333ea;
            --bg: #050505;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body { height: 100%; }

        body {
            font-family: 'Poppins', sans-serif;
            color: #fff;
            background: var(--bg);
            overflow: hidden;
            user-select: none;
        }

        /* fundo */
        .bg {
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse at center, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.92) 75%),
                url('../assets/images/bg-halloween.jpg') center/cover no-repeat;
            filter: saturate(1.1);
            z-index: 0;
            transition: filter 0.6s;
        }
        body.suspense .bg { filter: brightness(0.35) saturate(0.6); animation: flicker 1.2s infinite; }

        @keyframes flicker {
            0%, 100% { opacity: 1; }
            8% { opacity: 0.55; }
            10% { opacity: 1; }
            47% { opacity: 0.85; }
            50% { opacity: 0.4; }
            53% { opacity: 1; }
        }

        /* névoa */
        .fog {
            position: fixed; left: -50%; bottom: -10%;
            width: 200%; height: 45%;
            background: radial-gradient(ellipse at center, rgba(180,160,220,0.18), transparent 65%);
            filter: blur(20px);
            animation: fog 18s linear infinite alternate;
            z-index: 1; pointer-events: none;
        }
        .fog.f2 { animation-duration: 26s; bottom: -18%; opacity: 0.7; animation-direction: alternate-reverse; }
        @keyframes fog { from { transform: translateX(-10%); } to { transform: translateX(10%); } }

        /* morcegos voando */
        .bat {
            position: fixed; font-size: 34px; z-index: 2; pointer-events: none;
            animation: batFly linear infinite;
            filter: drop-shadow(0 0 6px rgba(0,0,0,0.9));
        }
        @keyframes batFly {
            0% { transform: translate(-10vw, 0) scaleX(1); }
            25% { transform: translate(25vw, -6vh); }
            50% { transform: translate(55vw, 4vh); }
            75% { transform: translate(85vw, -8vh); }
            100% { transform: translate(115vw, 0); }
        }

        /* palco */
        .stage {
            position: relative; z-index: 5;
            height: 100%;
            display: flex; flex-direction: column;
            align-items: center; justify-content: safe center;
            text-align: center;
            padding: 64px 20px 90px;
            overflow-y: auto;
        }

        .step { display: none; flex-direction: column; align-items: center; gap: min(18px, 2vh); max-width: 1100px; width: 100%; margin: auto 0; }
        .step.active { display: flex; animation: fadeIn 0.8s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }

        .logo { width: min(340px, 60vw); filter: drop-shadow(0 0 25px rgba(255,117,24,0.5)); animation: float 4s ease-in-out infinite; }
        @keyframes float { 50% { transform: translateY(-12px); } }

        .title {
            font-family: 'Creepster', cursive;
            font-size: clamp(48px, 9vw, 120px);
            color: var(--orange);
            letter-spacing: 3px;
            text-shadow: 0 0 20px rgba(255,117,24,0.8), 0 0 60px rgba(255,117,24,0.4);
            line-height: 1;
        }

        .category {
            font-family: 'Creepster', cursive;
            font-size: clamp(32px, 5.5vw, 72px);
            color: #c084fc;
            letter-spacing: 2px;
            text-shadow: 0 0 18px rgba(147,51,234,0.9);
        }

        .hint { color: #bfbfbf; font-size: 16px; opacity: 0.8; }

        /* suspense */
        .suspense-box { display: flex; flex-direction: column; align-items: center; gap: 10px; min-height: 30vh; justify-content: center; }
        .pumpkin { font-size: clamp(70px, min(14vw, 20vh), 170px); animation: shake 0.25s infinite; filter: drop-shadow(0 0 30px rgba(255,117,24,0.9)); }
        @keyframes shake {
            0% { transform: rotate(-6deg) scale(1); }
            50% { transform: rotate(6deg) scale(1.06); }
            100% { transform: rotate(-6deg) scale(1); }
        }
        .count {
            font-family: 'Creepster', cursive;
            font-size: clamp(60px, min(12vw, 16vh), 150px);
            color: #fff;
            text-shadow: 0 0 25px var(--purple);
            animation: pop 1s ease-out;
        }
        @keyframes pop { from { transform: scale(2.2); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        /* resultado */
        .result { display: none; flex-direction: column; align-items: center; gap: 14px; }
        .result.show { display: flex; animation: reveal 1.1s cubic-bezier(.2,1.4,.4,1); }
        @keyframes reveal {
            0% { transform: scale(0.2) rotate(-8deg); opacity: 0; filter: blur(12px); }
            100% { transform: scale(1) rotate(0); opacity: 1; filter: blur(0); }
        }

        .winner-name {
            font-family: 'Creepster', cursive;
            font-size: clamp(44px, min(10vw, 13vh), 140px);
            line-height: 1.05;
            animation: glow 2s ease-in-out infinite alternate;
        }
        .winner-name.purple { color: #c084fc; --glow: rgba(147,51,234,0.95); }
        .winner-name.orange, .winner-name.neutral { color: var(--orange); --glow: rgba(255,117,24,0.95); }
        @keyframes glow {
            from { text-shadow: 0 0 15px var(--glow), 0 0 40px var(--glow); }
            to   { text-shadow: 0 0 30px var(--glow), 0 0 90px var(--glow); }
        }

        .tie { font-family: 'Creepster', cursive; font-size: 40px; color: #fde047; text-shadow: 0 0 15px rgba(253,224,71,0.8); }

        .winner-meta { font-size: clamp(18px, 2.4vw, 28px); color: #e5e5e5; }
        .winner-meta strong { color: var(--orange); }

        .vs { display: flex; gap: 28px; justify-content: center; flex-wrap: wrap; margin-top: 8px; }
        .vs div {
            padding: 12px 22px; border-radius: 14px;
            background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15);
            font-size: 18px;
        }
        .vs .w { border-color: var(--orange); box-shadow: 0 0 18px rgba(255,117,24,0.5); }

        .costume-photos { display: flex; gap: 24px; flex-wrap: wrap; justify-content: center; }
        .costume-photo {
            height: min(48vh, 460px); max-width: 80vw; aspect-ratio: 3/4; object-fit: cover;
            border-radius: 18px;
            border: 3px solid var(--orange);
            box-shadow: 0 0 40px rgba(255,117,24,0.7), 0 0 100px rgba(147,51,234,0.4);
        }
        .costume-photos.many .costume-photo { height: min(36vh, 340px); max-width: 40vw; }

        .podium { display: flex; flex-direction: column; gap: 6px; min-width: min(460px, 90vw); }
        .podium div { display: flex; justify-content: space-between; padding: min(10px, 1.2vh) 16px; border-radius: 10px; background: rgba(0,0,0,0.6); border: 1px solid rgba(147,51,234,0.3); font-size: 18px; }
        .podium div:first-child { border-color: var(--orange); box-shadow: 0 0 15px rgba(255,117,24,0.4); }

        .final-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; width: 100%; }
        .final-card {
            background: rgba(0,0,0,0.65); border: 1px solid rgba(147,51,234,0.4);
            border-radius: 18px; padding: 22px 16px; display: flex; flex-direction: column; gap: 8px; align-items: center;
        }
        .final-card span { color: #c084fc; font-size: 14px; letter-spacing: 2px; }
        .final-card strong { font-family: 'Creepster', cursive; font-size: 38px; color: var(--orange); line-height: 1.1; }
        .final-card img { width: 120px; height: 150px; object-fit: cover; border-radius: 12px; border: 2px solid var(--orange); }

        /* botões */
        .controls {
            position: fixed; bottom: 18px; left: 50%; transform: translateX(-50%);
            display: flex; gap: 12px; z-index: 20;
        }
        .btn {
            font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px;
            padding: 12px 26px; border-radius: 10px; cursor: pointer; border: none; color: #fff;
            text-decoration: none;
        }
        .btn-primary { background: linear-gradient(90deg, #ff7518, #ff8c42); box-shadow: 0 0 15px rgba(255,117,24,0.5); }
        .btn-secondary { background: rgba(0,0,0,0.6); border: 1px solid var(--purple); }
        .btn:disabled { opacity: 0.4; cursor: default; }
        .exit { position: fixed; top: 14px; left: 14px; z-index: 20; font-size: 14px; padding: 8px 14px; }
        .fs { position: fixed; top: 14px; right: 14px; z-index: 20; font-size: 14px; padding: 8px 14px; }
        .warn { position: fixed; top: 60px; left: 50%; transform: translateX(-50%); z-index: 20; background: rgba(239,68,68,0.18); border: 1px solid rgba(239,68,68,0.5); color: #fca5a5; padding: 8px 14px; border-radius: 10px; font-size: 14px; }

        /* chuva de confete temático */
        .confetti {
            position: fixed; top: -60px; z-index: 15; pointer-events: none;
            animation: fall linear forwards;
        }
        @keyframes fall {
            to { transform: translateY(115vh) rotate(var(--rot)); opacity: 0.9; }
        }

        @media (max-width: 800px) {
            .final-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="bg"></div>
<div class="fog"></div>
<div class="fog f2"></div>

<a href="<?= BASE_URL ?>admin/index.php" class="btn btn-secondary exit">← Painel</a>
<button class="btn btn-secondary fs" id="fullscreenBtn">⛶ Tela cheia</button>

<?php if (!$scoreboardHidden): ?>
    <div class="warn">⚠ O placar ainda está visível no site. Oculte no painel para não estragar a surpresa.</div>
<?php endif; ?>

<main class="stage">

    <!-- 0: abertura -->
    <section class="step active" data-step="intro">
        <img src="<?= BASE_URL ?>assets/images/logo.png" class="logo" alt="Halloween 2026">
        <h1 class="title">A Grande Revelação</h1>
        <p class="hint">Pressione <strong>ESPAÇO</strong> ou clique em “Próximo” para começar</p>
    </section>

    <!-- 1: equipe -->
    <section class="step" data-step="team">
        <h2 class="category">🏆 Equipe Vencedora</h2>
        <div class="suspense-box"></div>
        <div class="result" id="result-team"></div>
    </section>

    <!-- 2: jogador -->
    <section class="step" data-step="player">
        <h2 class="category">💀 Melhor Jogador da Noite</h2>
        <div class="suspense-box"></div>
        <div class="result" id="result-player"></div>
    </section>

    <!-- 3: fantasia -->
    <section class="step" data-step="costume">
        <h2 class="category">🎭 Fantasia Mais Votada</h2>
        <div class="suspense-box"></div>
        <div class="result" id="result-costume"></div>
    </section>

    <!-- 4: final -->
    <section class="step" data-step="final">
        <h1 class="title">Os Campeões</h1>
        <div class="final-grid" id="final-grid"></div>

        <form method="POST" onsubmit="return confirm('Publicar o placar completo no site para todo mundo?');">
            <input type="hidden" name="action" value="publish_scoreboard">
            <button type="submit" class="btn btn-primary">👀 Publicar placar no site</button>
        </form>
    </section>

</main>

<div class="controls">
    <button class="btn btn-secondary" id="prevBtn">← Voltar</button>
    <button class="btn btn-primary" id="nextBtn">Próximo →</button>
</div>

<script>
const DATA = <?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

const steps = [...document.querySelectorAll('.step')];
let current = 0;
let busy = false;
const revealed = {};

function esc(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function names(list) {
    return list.map(i => esc(i.name)).join(' <br>&amp;<br> ');
}

/* ---------- conteúdo de cada revelação ---------- */
function buildTeam() {
    const w = DATA.teamWinners;
    if (!w.length) return '<p class="winner-meta">Nenhuma equipe cadastrada.</p>';

    const tie = w.length > 1;
    const color = tie ? 'neutral' : (w[0].color || 'neutral');
    const scores = DATA.teams.map(t => `
        <div class="${w.some(x => x.name === t.name) ? 'w' : ''}">${esc(t.name)}: <strong>${t.score} pts</strong></div>
    `).join('');

    return `
        ${tie ? '<div class="tie">EMPATE!</div>' : ''}
        <div class="winner-name ${color}">${names(w)}</div>
        <div class="vs">${scores}</div>`;
}

function buildPlayer() {
    const w = DATA.playerWinners;
    if (!w.length) return '<p class="winner-meta">Ninguém pontuou individualmente.</p>';

    const tie = w.length > 1;
    const color = tie ? 'neutral' : (w[0].color || 'neutral');
    const podium = DATA.podium.map((p, i) => `<div><span>${i + 1}º ${esc(p.name)}</span><strong>${p.score} pts</strong></div>`).join('');

    return `
        ${tie ? '<div class="tie">EMPATE!</div>' : ''}
        <div class="winner-name ${color}">${names(w)}</div>
        <p class="winner-meta"><strong>${w[0].score} pontos</strong>${!tie && w[0].team ? ' · ' + esc(w[0].team) : ''}</p>
        <div class="podium">${podium}</div>`;
}

function buildCostume() {
    const w = DATA.costumeWinners;
    if (!w.length) return '<p class="winner-meta">Nenhum voto de fantasia registrado.</p>';

    const tie = w.length > 1;
    const photos = w.map(c => `<img class="costume-photo" src="${esc(c.photo)}" alt="Fantasia de ${esc(c.name)}">`).join('');

    return `
        ${tie ? '<div class="tie">EMPATE!</div>' : ''}
        <div class="costume-photos ${tie ? 'many' : ''}">${photos}</div>
        <div class="winner-name neutral">${names(w)}</div>
        <p class="winner-meta"><strong>${w[0].votes} voto${w[0].votes === 1 ? '' : 's'}</strong></p>`;
}

function buildFinal() {
    const t = DATA.teamWinners.map(x => esc(x.name)).join(' & ') || '-';
    const p = DATA.playerWinners.map(x => esc(x.name)).join(' & ') || '-';
    const c = DATA.costumeWinners.map(x => esc(x.name)).join(' & ') || '-';
    const img = DATA.costumeWinners[0] ? `<img src="${esc(DATA.costumeWinners[0].photo)}" alt="">` : '';

    return `
        <div class="final-card"><span>EQUIPE</span><div style="font-size:42px">🏆</div><strong>${t}</strong></div>
        <div class="final-card"><span>JOGADOR</span><div style="font-size:42px">💀</div><strong>${p}</strong></div>
        <div class="final-card"><span>FANTASIA</span>${img}<strong>${c}</strong></div>`;
}

const builders = { team: buildTeam, player: buildPlayer, costume: buildCostume };

/* ---------- efeitos ---------- */
function confetti(amount = 70) {
    const icons = ['🎃', '🦇', '👻', '🕷️', '💀', '🍬', '🕸️', '✨'];
    for (let i = 0; i < amount; i++) {
        const el = document.createElement('div');
        el.className = 'confetti';
        el.textContent = icons[Math.floor(Math.random() * icons.length)];
        el.style.left = Math.random() * 100 + 'vw';
        el.style.fontSize = (22 + Math.random() * 30) + 'px';
        el.style.setProperty('--rot', (Math.random() * 720 - 360) + 'deg');
        el.style.animationDuration = (2.8 + Math.random() * 3) + 's';
        el.style.animationDelay = (Math.random() * 1.2) + 's';
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 7500);
    }
}

let audioCtx = null;
function sound(type) {
    try {
        audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
        const o = audioCtx.createOscillator();
        const g = audioCtx.createGain();
        o.connect(g); g.connect(audioCtx.destination);
        const t = audioCtx.currentTime;

        if (type === 'tick') {
            o.type = 'square'; o.frequency.setValueAtTime(110, t);
            g.gain.setValueAtTime(0.12, t); g.gain.exponentialRampToValueAtTime(0.001, t + 0.25);
            o.start(t); o.stop(t + 0.25);
        } else {
            o.type = 'sawtooth';
            o.frequency.setValueAtTime(220, t);
            o.frequency.exponentialRampToValueAtTime(880, t + 0.6);
            g.gain.setValueAtTime(0.15, t); g.gain.exponentialRampToValueAtTime(0.001, t + 1.2);
            o.start(t); o.stop(t + 1.2);
        }
    } catch (e) {}
}

const wait = ms => new Promise(r => setTimeout(r, ms));

async function runReveal(stepEl) {
    const key = stepEl.dataset.step;
    const box = stepEl.querySelector('.suspense-box');
    const result = stepEl.querySelector('.result');

    if (revealed[key]) {
        box.style.display = 'none';
        result.classList.add('show');
        return;
    }

    busy = true;
    result.classList.remove('show');
    box.style.display = 'flex';
    document.body.classList.add('suspense');

    for (const n of [3, 2, 1]) {
        box.innerHTML = `<div class="pumpkin">🎃</div><div class="count">${n}</div>`;
        sound('tick');
        await wait(1000);
    }

    document.body.classList.remove('suspense');
    box.style.display = 'none';
    result.innerHTML = builders[key]();
    result.classList.add('show');
    sound('reveal');
    confetti();

    revealed[key] = true;
    busy = false;
}

/* ---------- navegação ---------- */
function show(index) {
    if (busy || index < 0 || index >= steps.length) return;

    steps[current].classList.remove('active');
    current = index;
    const el = steps[current];
    el.classList.add('active');

    if (builders[el.dataset.step]) runReveal(el);
    if (el.dataset.step === 'final') {
        document.getElementById('final-grid').innerHTML = buildFinal();
        confetti(120);
    }

    document.getElementById('prevBtn').disabled = current === 0;
    document.getElementById('nextBtn').disabled = current === steps.length - 1;
}

document.getElementById('nextBtn').addEventListener('click', () => show(current + 1));
document.getElementById('prevBtn').addEventListener('click', () => show(current - 1));

document.addEventListener('keydown', e => {
    if (e.key === ' ' || e.key === 'ArrowRight' || e.key === 'Enter') { e.preventDefault(); show(current + 1); }
    if (e.key === 'ArrowLeft') show(current - 1);
});

document.getElementById('fullscreenBtn').addEventListener('click', () => {
    if (!document.fullscreenElement) document.documentElement.requestFullscreen?.();
    else document.exitFullscreen?.();
});

document.getElementById('prevBtn').disabled = true;

/* morcegos */
for (let i = 0; i < 6; i++) {
    const b = document.createElement('div');
    b.className = 'bat';
    b.textContent = '🦇';
    b.style.top = (5 + Math.random() * 35) + 'vh';
    b.style.animationDuration = (12 + Math.random() * 12) + 's';
    b.style.animationDelay = (-Math.random() * 20) + 's';
    b.style.fontSize = (20 + Math.random() * 26) + 'px';
    document.body.appendChild(b);
}
</script>

</body>
</html>
