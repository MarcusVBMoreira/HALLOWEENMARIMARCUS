<?php
require_once 'includes/db.php';

$stmt = $pdo->prepare("
    SELECT setting_value 
    FROM event_settings 
    WHERE setting_key = 'costume_voting_enabled'
    LIMIT 1
");
$stmt->execute();

$votingEnabled = $stmt->fetchColumn() === '1';

include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <div class="logo-container">
            <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo do Evento">
        </div>
        <h3>2026</h3>

        <p class="subtitle">Uma noite. Vários jogos. Apenas um vencedor.</p>
        <p class="highlight">RISADAS, BEBIDAS E JOGOS DE TABULEIRO!</p>

        <div class="countdown">
            <div class="time-box"><span id="days" class="flip-number">00</span><small>DIAS</small></div>
            <div class="time-box"><span id="hours" class="flip-number">00</span><small>HORAS</small></div>
            <div class="time-box"><span id="minutes" class="flip-number">00</span><small>MINUTOS</small></div>
            <div class="time-box"><span id="seconds" class="flip-number">00</span><small>SEGUNDOS</small></div>
        </div>

        <div class="buttons">
            <a href="<?= BASE_URL ?>pages/games.php" class="btn btn-primary">
                <span class="img-icon">
                    <img src="<?= BASE_URL ?>assets/images/icons/fantasma.png" class="icon-img" alt="">
                </span>
                ENTRAR NO EVENTO
            </a>
            <a href="<?= BASE_URL ?>pages/rules.php" class="btn btn-secondary">
                <span class="img-icon">
                    <img src="<?= BASE_URL ?>assets/images/icons/regras-roxo.png" class="icon-img" alt="">
                </span>
                VER REGRAS
            </a>
        </div>
        
    </div>
</section>

<!-- COMO FUNCIONA -->
<section class="section">
    <h2 class="section-title title-with-lines">COMO FUNCIONA</h2>

    <div class="cards-row">
        <div class="card card-flex">
            <div class="icon purple">
                <span class="img-icon"><img src="<?= BASE_URL ?>assets/images/icons/user-roxo.png" class="icon-img" alt=""></span>
            </div>
            <div>
                <h3 class="title-purple">JOGOS INDIVIDUAIS</h3>
                <p>Você pontua para você e para o seu time.</p>
            </div>
        </div>

        <div class="card card-flex">
            <div class="icon orange">
                <span class="img-icon"><img src="<?= BASE_URL ?>assets/images/icons/users-laranja.png" class="icon-img" alt=""></span>
            </div>
            <div>
                <h3 class="title-orange">JOGOS EM EQUIPE</h3>
                <p>Apenas o time pontua.</p>
            </div>
        </div>

        <div class="card card-flex">
            <div class="icon purple">
                <span class="img-icon"><img src="<?= BASE_URL ?>assets/images/icons/caveira-roxa.png" class="icon-img" alt=""></span>
            </div>
            <div>
                <h3 class="title-purple">DESAFIOS & BEBIDAS</h3>
                <p>Ganhou? Escolhe o shot. Perdeu? Bebe 2 shots.</p>
            </div>
        </div>
    </div>
</section>

<!-- GRID -->
<section class="section" id="live-area" data-api="<?= BASE_URL ?>api/get_scores.php">

    <div class="top-row">

        <!-- TIMES -->
        <div class="box">
            <h2 class="section-title title-with-lines">TIMES</h2>

            <div class="teams">

                <div class="team team-purple">
                    <div class="team-header">
                        <div class="team-icon">
                            <span class="img-icon"><img src="<?= BASE_URL ?>assets/images/icons/caveira-roxa.png" class="icon-img" alt=""></span>
                        </div>
                        <h3 id="team-purple-name">CAVEIRAS CAÓTICAS</h3>
                    </div>

                    <ul id="team-purple-players">
                        <li>Carregando...</li>
                    </ul>

                    <div class="score">
                        <span>PONTOS</span>
                        <strong id="team-purple-score">0</strong>
                    </div>
                </div>

                <div class="team team-orange">
                    <div class="team-header">
                        <div class="team-icon">
                            <span class="img-icon"><img src="<?= BASE_URL ?>assets/images/icons/abobora-laranja.png" class="icon-img" alt=""></span>
                        </div>
                        <h3 id="team-orange-name">ABÓBORAS ASSASSINAS</h3>
                    </div>

                    <ul id="team-orange-players">
                        <li>Carregando...</li>
                    </ul>

                    <div class="score">
                        <span>PONTOS</span>
                        <strong id="team-orange-score">0</strong>
                    </div>
                </div>

            </div>
        </div>

        <!-- PLACAR -->
        <div class="box">
            <h2 class="section-title">
                <div class="icon">
                    <span class="img-icon"><img src="<?= BASE_URL ?>assets/images/icons/trofeu-roxo.png" class="icon-img" alt=""></span>
                </div>
                PLACAR INDIVIDUAL
            </h2>

            <div class="scoreboard" id="scoreboard-live">
                <p><span>1º</span><span>--</span><span>0 pts</span></p>
                <p><span>2º</span><span>--</span><span>0 pts</span></p>
                <p class="highlight-rank"><span>3º</span><span>--</span><span>0 pts</span></p>
                <p><span>4º</span><span>--</span><span>0 pts</span></p>
                <p><span>5º</span><span>--</span><span>0 pts</span></p>
            </div>

            <button class="btn btn-secondary full" id="toggle-scoreboard">VER PLACAR COMPLETO</button>
        </div>

    </div>

    <!-- LINHA DE BAIXO -->
    <div class="bottom-row">

        <div class="box vote-box">
            <h2 class="section-title">
                <div class="icon">
                    <span class="img-icon"><img src="<?= BASE_URL ?>assets/images/icons/mascara-roxo.png" class="icon-img" alt=""></span>
                </div>
                VOTAÇÃO
            </h2>
            <p>Vote na melhor fantasia da noite!</p>
            <?php if ($votingEnabled): ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= BASE_URL ?>pages/vote.php" class="btn btn-secondary full">
                        🎭 ACESSAR VOTAÇÃO
                    </a>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary full" onclick="showLoginWarning()">
                        🎭 ACESSAR VOTAÇÃO
                    </button>

                    <p id="login-warning" class="vote-warning">Você precisa estar logado para acessar a votação.</p>
                <?php endif; ?>

            <?php else: ?>
                <button class="btn btn-secondary full" disabled>DISPONÍVEL DURANTE O EVENTO</button>
            <?php endif; ?>
        </div>

        <div class="box about-box">
            <h2 class="section-title">
                <div class="icon">
                    <span class="img-icon"><img src="<?= BASE_URL ?>assets/images/icons/calendario-laranja.png" class="icon-img" alt=""></span>
                </div>
                SOBRE O EVENTO
            </h2>

            <div class="about-content">
                <div class="about-item">
                    <div class="icon"><span class="img-icon"><img src="<?= BASE_URL ?>assets/images/icons/calendario-laranja.png" class="icon-img" alt=""></span></div>
                    <div class="cotent-about-item">
                        <span class="label">DATA</span>
                        <p>31/10/2026</p>
                    </div>
                </div>

                <div class="about-item">
                    <div class="icon"><span class="img-icon"><img src="<?= BASE_URL ?>assets/images/icons/relogio-laranja.png" class="icon-img" alt=""></span></div>
                    <div class="cotent-about-item">
                        <span class="label">HORÁRIO</span>
                        <p>20:00</p>
                    </div>
                </div>

                <div class="about-item">
                    <div class="icon"><span class="img-icon"><img src="<?= BASE_URL ?>assets/images/icons/local-laranja.png" class="icon-img" alt=""></span></div>
                    <div class="cotent-about-item">
                        <span class="label">LOCAL</span>
                        <p>Rua dos Bandeirantes, 550, AP 71, Vila Bocaina, Mauá/SP</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="box rules-box">
            <h2 class="section-title">
                <div class="icon">
                    <span class="img-icon"><img src="<?= BASE_URL ?>assets/images/icons/regras-roxo.png" class="icon-img" alt=""></span>
                </div>
                REGRAS
            </h2>

            <ul class="rules">
                <li>✔ Respeite os jogos</li>
                <li>✔ Beba com responsabilidade</li>
                <li>✔ Sem celulares</li>
                <li>✔ Divirta-se</li>
            </ul>

            <div class="rules-image"></div>
        </div>

    </div>

</section>

<script>
const liveArea = document.getElementById('live-area');

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function renderPlayers(list, color) {
    const icon = color === 'orange'
        ? 'assets/images/icons/user-laranja.png'
        : 'assets/images/icons/user-roxo.png';

    if (!list || list.length === 0) {
        return '<li>Nenhum jogador no time</li>';
    }

    return list.map(player => `
        <li>
            <span class="img-icon">
                <img src="${icon}" class="icon-img" alt="">
            </span>
            ${escapeHtml(player.name)}
        </li>
    `).join('');
}

let showFullScoreboard = false;
let currentRanking = [];

function renderScoreboard(ranking) {
    const scoreboard = document.getElementById('scoreboard-live');

    currentRanking = ranking || [];

    if (currentRanking.length === 0) {
        scoreboard.innerHTML = '<p><span>--</span><span>Nenhum jogador</span><span>0 pts</span></p>';
        return;
    }

    const visibleRanking = showFullScoreboard
        ? currentRanking
        : currentRanking.slice(0, 5);

    scoreboard.innerHTML = visibleRanking.map((player, index) => `
        <p class="${index === 2 ? 'highlight-rank' : ''}">
            <span>${index + 1}º</span>
            <span>${escapeHtml(player.name)}</span>
            <span>${parseInt(player.total_score)} pts</span>
        </p>
    `).join('');
}

async function updateLiveData() {
    if (!liveArea) return;

    try {
        const response = await fetch(liveArea.dataset.api + '?t=' + Date.now());
        const data = await response.json();

        if (!data.success) return;

        const teamPurple = data.teams[0];
        const teamOrange = data.teams[1];

        if (teamPurple) {
            document.getElementById('team-purple-name').textContent = teamPurple.name;
            document.getElementById('team-purple-score').textContent = parseInt(teamPurple.total_score);
            document.getElementById('team-purple-players').innerHTML = renderPlayers(teamPurple.players, 'purple');
        }

        if (teamOrange) {
            document.getElementById('team-orange-name').textContent = teamOrange.name;
            document.getElementById('team-orange-score').textContent = parseInt(teamOrange.total_score);
            document.getElementById('team-orange-players').innerHTML = renderPlayers(teamOrange.players, 'orange');
        }

        renderScoreboard(data.ranking);

    } catch (error) {
        console.error('Erro ao atualizar dados:', error);
    }
}

const toggleScoreboardButton = document.getElementById('toggle-scoreboard');

if (toggleScoreboardButton) {
    toggleScoreboardButton.addEventListener('click', () => {
        showFullScoreboard = !showFullScoreboard;

        toggleScoreboardButton.textContent = showFullScoreboard
            ? 'VER MENOS'
            : 'VER PLACAR COMPLETO';

        renderScoreboard(currentRanking);
    });
}

function showLoginWarning() {
    const warning = document.getElementById('login-warning');

    if (!warning) return;

    warning.classList.add('active');

    setTimeout(() => {
        warning.classList.remove('active');
    }, 3000);
}

updateLiveData();
setInterval(updateLiveData, 3000);
</script>

<?php include 'includes/footer.php'; ?>