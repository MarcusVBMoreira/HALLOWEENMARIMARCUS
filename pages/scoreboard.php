<?php
include '../includes/header.php';
?>

<section class="inner-page scoreboard-page" id="scoreboard-page" data-api="<?= BASE_URL ?>api/get_scores.php">

    <div class="inner-container">

        <h1>Placar Geral</h1>
        <p class="inner-subtitle">
            Acompanhe a pontuação em tempo real.
        </p>

        <div class="scoreboard-wrapper">

            <!-- TIMES -->
            <div class="scoreboard-teams">

                <div class="team team-purple">
                    <div class="team-header">
                        <h2 id="team-purple-name">Time 1</h2>
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
                        <h2 id="team-orange-name">Time 2</h2>
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

            <!-- RANKING -->
            <div class="scoreboard-full">

                <h2>Ranking Individual</h2>

                <div id="scoreboard-list" class="scoreboard-list">
                    <p>Carregando...</p>
                </div>

            </div>

        </div>

        <a href="<?= BASE_URL ?>index.php" class="back-home">
            ← Voltar para o início
        </a>

    </div>

</section>

<script>
const page = document.getElementById('scoreboard-page');

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function renderPlayers(list) {
    if (!list || list.length === 0) {
        return '<li>Nenhum jogador</li>';
    }

    return list.map(player => `
        <li>${escapeHtml(player.name)}</li>
    `).join('');
}

function renderRanking(ranking) {
    const container = document.getElementById('scoreboard-list');

    if (!ranking || ranking.length === 0) {
        container.innerHTML = '<p>Nenhum jogador</p>';
        return;
    }

    container.innerHTML = ranking.map((player, index) => `
        <div class="score-row ${index === 0 ? 'leader' : ''}">
            <span>${index + 1}º</span>
            <span>${escapeHtml(player.name)}</span>
            <strong>${parseInt(player.total_score)} pts</strong>
        </div>
    `).join('');
}

async function updateScoreboard() {
    try {
        const response = await fetch(page.dataset.api + '?t=' + Date.now());
        const data = await response.json();

        if (!data.success) return;

        const team1 = data.teams[0];
        const team2 = data.teams[1];

        if (team1) {
            document.getElementById('team-purple-name').textContent = team1.name;
            document.getElementById('team-purple-score').textContent = team1.total_score;
            document.getElementById('team-purple-players').innerHTML = renderPlayers(team1.players);
        }

        if (team2) {
            document.getElementById('team-orange-name').textContent = team2.name;
            document.getElementById('team-orange-score').textContent = team2.total_score;
            document.getElementById('team-orange-players').innerHTML = renderPlayers(team2.players);
        }

        renderRanking(data.ranking);

    } catch (e) {
        console.error('Erro:', e);
    }
}

updateScoreboard();
setInterval(updateScoreboard, 3000);
</script>

<?php include '../includes/footer.php'; ?>