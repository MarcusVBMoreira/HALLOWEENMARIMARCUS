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

/**
 * Converte a URL de vídeo cadastrada (YouTube, Vimeo ou arquivo direto)
 * em algo que dá pra embedar no modal.
 */
function getVideoEmbed($url) {
    if (empty($url)) return null;

    // YouTube (watch?v= ou youtu.be/)
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $m)) {
        return [
            'type' => 'iframe',
            'src'  => 'https://www.youtube.com/embed/' . $m[1]
        ];
    }

    // Vimeo
    if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
        return [
            'type' => 'iframe',
            'src'  => 'https://player.vimeo.com/video/' . $m[1]
        ];
    }

    // Arquivo direto (.mp4, .webm, .ogg)
    if (preg_match('/\.(mp4|webm|ogg)$/i', $url)) {
        return [
            'type' => 'video',
            'src'  => $url
        ];
    }

    return null;
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
                    $video = getVideoEmbed($game['video'] ?? '');
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
                            data-video-type="<?= $video['type'] ?? '' ?>"
                            data-video-src="<?= htmlspecialchars($video['src'] ?? '') ?>"
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

        <div class="game-modal-section" id="modalVideoSection">
            <h3>Vídeo</h3>
            <div class="game-modal-video" id="modalVideoWrapper"></div>
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

const modalVideoSection = document.getElementById('modalVideoSection');
const modalVideoWrapper = document.getElementById('modalVideoWrapper');

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

        // Vídeo
        const videoType = button.dataset.videoType;
        const videoSrc = button.dataset.videoSrc;

        modalVideoWrapper.innerHTML = '';

        if (videoType === 'iframe' && videoSrc) {
            modalVideoSection.style.display = 'block';
            modalVideoWrapper.innerHTML = `
                <iframe
                    src="${videoSrc}"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>`;
        } else if (videoType === 'video' && videoSrc) {
            modalVideoSection.style.display = 'block';
            modalVideoWrapper.innerHTML = `
                <video controls>
                    <source src="${videoSrc}">
                    Seu navegador não suporta vídeo.
                </video>`;
        } else {
            modalVideoSection.style.display = 'none';
        }

        modal.classList.add('active');
    });
});

function closeModal() {
    modal.classList.remove('active');
    modalVideoWrapper.innerHTML = ''; // para o vídeo/iframe ao fechar
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