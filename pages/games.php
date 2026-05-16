<?php
require_once '../includes/db.php';
include '../includes/header.php';

$games = $pdo->query("
    SELECT *
    FROM games
    WHERE is_active = 1
    ORDER BY game_order ASC, id ASC
")->fetchAll();

function getGameType($game) {
    $playerPoints = (int)$game['player_points'];
    $teamPoints = (int)$game['team_points'];

    if ($playerPoints > 0 && $teamPoints > 0) {
        return 'Individual + Equipe';
    }

    if ($playerPoints > 0) {
        return 'Individual';
    }

    if ($teamPoints > 0) {
        return 'Coletivo';
    }

    return 'Sem pontuação';
}
?>

<section class="inner-page games-page">

    <div class="inner-container">

        <h1>Jogos da Noite</h1>
        <p class="inner-subtitle">
            Conheça os jogos que farão parte da competição.
        </p>

        <div class="games-grid">

            <?php if (count($games) === 0): ?>
                <div class="empty-state">
                    Nenhum jogo cadastrado ainda.
                </div>
            <?php endif; ?>

            <?php foreach ($games as $game): ?>
                <?php
                    $image = !empty($game['image'])
                        ? BASE_URL . htmlspecialchars($game['image'])
                        : BASE_URL . 'assets/images/game-placeholder.png';

                    $gameType = getGameType($game);
                ?>

                <div class="game-card">
                    <img src="<?= $image ?>" alt="<?= htmlspecialchars($game['name']) ?>">

                    <div class="game-card-content">
                        <h2><?= htmlspecialchars($game['name']) ?></h2>
                        <span class="game-type"><?= htmlspecialchars($gameType) ?></span>

                        <button 
                            class="btn btn-primary full open-game-modal"
                            data-name="<?= htmlspecialchars($game['name']) ?>"
                            data-type="<?= htmlspecialchars($gameType) ?>"
                            data-image="<?= $image ?>"
                            data-description="<?= htmlspecialchars($game['description'] ?? '') ?>"
                            data-how="<?= htmlspecialchars($game['how_to_play'] ?? '') ?>"
                            data-rules="<?= htmlspecialchars($game['rules'] ?? '') ?>"
                            data-player-points="<?= (int)$game['player_points'] ?>"
                            data-team-points="<?= (int)$game['team_points'] ?>"
                        >
                            Visualizar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>

    </div>

</section>

<div class="game-modal" id="gameModal">
    <div class="game-modal-overlay" id="closeGameModal"></div>

    <div class="game-modal-content">
        <button class="game-modal-close" id="closeGameModalBtn">×</button>

        <img id="modalGameImage" src="" alt="">

        <h2 id="modalGameName"></h2>
        <span id="modalGameType" class="game-type"></span>

        <div class="game-modal-info">
            <p><strong>Pontos jogador:</strong> <span id="modalPlayerPoints"></span></p>
            <p><strong>Pontos equipe:</strong> <span id="modalTeamPoints"></span></p>
        </div>

        <div class="game-modal-section">
            <h3>Descrição</h3>
            <p id="modalDescription"></p>
        </div>

        <div class="game-modal-section">
            <h3>Como jogar</h3>
            <p id="modalHow"></p>
        </div>

        <div class="game-modal-section">
            <h3>Regras</h3>
            <p id="modalRules"></p>
        </div>
    </div>
</div>

<script>
const modal = document.getElementById('gameModal');
const buttons = document.querySelectorAll('.open-game-modal');

const modalImage = document.getElementById('modalGameImage');
const modalName = document.getElementById('modalGameName');
const modalType = document.getElementById('modalGameType');
const modalDescription = document.getElementById('modalDescription');
const modalHow = document.getElementById('modalHow');
const modalRules = document.getElementById('modalRules');
const modalPlayerPoints = document.getElementById('modalPlayerPoints');
const modalTeamPoints = document.getElementById('modalTeamPoints');

buttons.forEach(button => {
    button.addEventListener('click', () => {
        modalImage.src = button.dataset.image;
        modalName.textContent = button.dataset.name;
        modalType.textContent = button.dataset.type;

        modalDescription.textContent = button.dataset.description || 'Sem descrição cadastrada.';
        modalHow.textContent = button.dataset.how || 'Como jogar ainda não cadastrado.';
        modalRules.textContent = button.dataset.rules || 'Regras ainda não cadastradas.';

        modalPlayerPoints.textContent = button.dataset.playerPoints + ' pts';
        modalTeamPoints.textContent = button.dataset.teamPoints + ' pts';

        modal.classList.add('active');
    });
});

function closeModal() {
    modal.classList.remove('active');
}

document.getElementById('closeGameModal').addEventListener('click', closeModal);
document.getElementById('closeGameModalBtn').addEventListener('click', closeModal);

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>