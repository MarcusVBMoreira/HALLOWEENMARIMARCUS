<?php
include '../includes/header.php';
?>

<section class="inner-page rules-page">

    <div class="inner-container">

        <h1>Regras do Evento</h1>
        <p class="inner-subtitle">
            Leia com atenção: o desconhecimento das regras não livra ninguém do shot. 🎃
        </p>

        <div class="rules-page-grid">

            <div class="rule-card rule-card-highlight">
                <h2>🍻 Bebidas</h2>
                <ul class="rule-list">
                    <li><strong>Vencedor(es)</strong> do jogo bebem <strong>1 shot</strong>.</li>
                    <li><strong>Perdedor(es)</strong> do jogo bebem <strong>2 shots</strong>.</li>
                    <li>Vale para todos os jogos da noite, individuais ou em equipe.</li>
                    <li>Respeite seus limites: quem não bebe álcool (ou já passou do ponto) troca o shot por uma dose sem álcool, sem julgamento.</li>
                    <li>Ninguém é obrigado a beber, e quem beber não dirige.</li>
                </ul>
            </div>

            <div class="rule-card rule-card-highlight">
                <h2>🏆 Pontuação</h2>
                <ul class="rule-list">
                    <li><strong>Jogos Individual + Equipe:</strong> o vencedor ganha <strong>3 pontos</strong> para si e o time dele ganha <strong>1 ponto</strong>.</li>
                    <li><strong>Jogos só de Equipe:</strong> apenas o time vencedor pontua, com <strong>3 pontos</strong>.</li>
                    <li>Os resultados são lançados pelo organizador e o placar aparece no site em tempo real.</li>
                    <li>Na reta final o placar pode ficar oculto até a grande revelação.</li>
                </ul>
            </div>

            <div class="rule-card">
                <h2>🥇 Troféus da Noite</h2>
                <ul class="rule-list">
                    <li><strong>Melhor Jogador da Noite:</strong> quem somar mais pontos individuais.</li>
                    <li><strong>Melhor Fantasia:</strong> a fantasia mais votada pelos participantes.</li>
                    <li>A equipe com mais pontos leva a glória (e o direito de zoar a outra até o próximo Halloween).</li>
                </ul>
            </div>

            <div class="rule-card">
                <h2>🎭 Fantasias</h2>
                <ul class="rule-list">
                    <li>Fantasia é obrigatória! Capriche e seja criativo: originalidade vale mais que dinheiro gasto.</li>
                    <li>Cada participante entra no site com seu login e envia a foto da <strong>própria</strong> fantasia.</li>
                    <li>A votação abre automaticamente quando <strong>todos</strong> enviarem a foto, então não seja a pessoa que atrasa todo mundo.</li>
                    <li>Cada pessoa vota <strong>uma vez</strong> e não pode votar em si mesma.</li>
                    <li>Depois que todos enviarem, as fotos não podem mais ser trocadas.</li>
                </ul>
            </div>

            <div class="rule-card">
                <h2>🎲 Durante os Jogos</h2>
                <ul class="rule-list">
                    <li>Respeite as regras de cada jogo: elas estão na página <a href="<?= BASE_URL ?>pages/games.php">Jogos</a>.</li>
                    <li>Blefar faz parte, roubar não. Trapaça descoberta = 2 shots.</li>
                    <li>Em caso de dúvida ou empate, a decisão do organizador é final.</li>
                    <li>Celular só para usar o site do evento (fantasia e placar). Nada de pesquisar respostas no meio do jogo!</li>
                </ul>
            </div>

            <div class="rule-card">
                <h2>💀 Convivência</h2>
                <ul class="rule-list">
                    <li>Zoeira liberada, ofensa não. Todo mundo está aqui para se divertir.</li>
                    <li>Cuide da casa e dos jogos: nada de bebida em cima dos tabuleiros e cartas.</li>
                    <li>Ajude quem passou do limite: água e comida são aliadas.</li>
                    <li>Divirta-se! Essa é a regra mais importante. 🎃</li>
                </ul>
            </div>

        </div>

        <a href="<?= BASE_URL ?>index.php" class="back-home">
            ← Voltar para o início
        </a>

    </div>

</section>

<?php include '../includes/footer.php'; ?>
