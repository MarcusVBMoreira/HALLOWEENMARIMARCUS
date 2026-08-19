-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 18/08/2026 às 00:58
-- Versão do servidor: 11.8.8-MariaDB-log
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `HALLOWEEN_2026`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `costume_votes`
--

CREATE TABLE `costume_votes` (
  `id` int(11) NOT NULL,
  `voter_user_id` int(11) NOT NULL,
  `voted_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `event_settings`
--

CREATE TABLE `event_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(100) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `event_settings`
--

INSERT INTO `event_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'costume_voting_enabled', '1', '2026-05-13 02:30:11');

-- --------------------------------------------------------

--
-- Estrutura para tabela `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `how_to_play` text DEFAULT NULL,
  `rules` text DEFAULT NULL,
  `player_points` int(11) DEFAULT 0,
  `team_points` int(11) DEFAULT 0,
  `game_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `games`
--

INSERT INTO `games` (`id`, `name`, `image`, `description`, `how_to_play`, `rules`, `player_points`, `team_points`, `game_order`, `is_active`, `created_at`) VALUES
(7, 'Overcooked', 'assets/uploads/games/game_1786915413_7230.webp', 'Overcooked 2 é um jogo cooperativo de simulação de cozinha lançado em 2018, onde até quatro jogadores precisam preparar pratos em cozinhas caóticas e cheias de obstáculos. É conhecido por sua jogabilidade divertida, desafiadora e caótica, ideal para jogar com amigos localmente ou online.', 'Para jogar Overcooked 2, você precisa trabalhar em equipe para preparar e entregar pratos dentro de um tempo limitado. Cada jogador controla um chef que deve cortar ingredientes, cozinhar, montar os pedidos e lavar pratos, tudo em cozinhas cheias de obstáculos e mudanças inesperadas.\r\n\r\nO segredo é coordenar bem as tarefas: dividir funções, usar o recurso de arremessar ingredientes para ganhar tempo e manter a comunicação constante com os parceiros. Assim, vocês conseguem entregar os pedidos rapidamente e conquistar mais estrelas em cada fase.', 'Durante a partida, cada dupla deve cooperar entre si para preparar e entregar os pratos, mas sem atrapalhar fisicamente os outros jogadores fora do jogo. Isso significa não ficar na frente da tela, não empurrar ou bloquear o campo de visão dos colegas e manter o ambiente confortável para todos.\r\n\r\nDentro do jogo, a regra principal é respeitar o espaço e as funções de cada jogador: evite esbarrar ou bloquear o caminho do parceiro, mantenha a comunicação clara e divida as tarefas de forma justa. Assim, a disputa fica divertida e equilibrada, sem confusão fora ou dentro da cozinha virtual.\r\n\r\nQuem estiver de fora fique a vontade para implantar o caos dando dicas erradas!', 2, 3, 0, 1, '2026-08-16 21:23:33'),
(8, 'Coup', 'assets/uploads/games/game_1786915885_8510.webp', 'Coup é um jogo de cartas rápido e estratégico, ambientado em um universo de intriga política. Cada jogador controla personagens secretos com habilidades especiais e precisa usar blefes, manipulação e dedução para eliminar a influência dos adversários.\r\n\r\nO objetivo é ser o último jogador com influência restante, tomando ações como coletar moedas, assassinar ou bloquear jogadas, enquanto tenta enganar os outros sobre quais cartas possui. É um jogo simples de aprender, mas cheio de tensão e estratégia, ideal para partidas curtas e competitivas entre amigos.', 'Para jogar Coup, cada jogador começa com duas cartas viradas para baixo, representando personagens secretos. No seu turno, você pode realizar ações básicas (como coletar moedas ou pagar para eliminar a influência de outro jogador) ou usar habilidades especiais dos personagens que afirma ter.\r\n\r\nA graça do jogo está no blefe: você pode fingir ter uma carta para usar sua habilidade, e os outros jogadores podem aceitar ou contestar. Se alguém desafiar e você não tiver a carta, perde uma influência; se tiver, o desafiante perde. O objetivo é ser o último jogador com cartas (influência) restantes.', 'Aqui vai uma versão bem simples das regras para jogar Coup:\r\n\r\nCada jogador começa com 2 cartas viradas para baixo (influência).\r\n\r\nNo turno, você pode coletar moedas ou usar a habilidade de um personagem (mesmo que esteja blefando).\r\n\r\nOutros jogadores podem aceitar ou contestar sua ação. Se contestarem, você deve provar que tem a carta; se não tiver, perde uma influência.\r\n\r\nQuando perde todas as cartas, está fora do jogo.\r\n\r\nO objetivo é ser o último jogador com influência restante.\r\n\r\nÉ isso: rápido, direto e cheio de blefes e desafios!', 3, 1, 0, 1, '2026-08-16 21:31:25'),
(9, 'Bang! The Dice Game', 'assets/uploads/games/game_1786916136_8237.jpg', 'Bang! The Dice Game é um jogo de tabuleiro rápido e cheio de ação, inspirado no clássico Bang!. Cada jogador recebe um papel secreto (xerife, vice, fora da lei ou renegado) e usa dados para atacar, defender ou curar, tentando cumprir o objetivo do seu papel sem revelar sua identidade logo de início.\r\n\r\nO jogo gira em torno de rolagens de dados que determinam ações como atirar em outros jogadores, curar pontos de vida ou causar explosões. A partida é marcada por blefes, dedução e sorte, e termina quando o xerife é derrotado ou quando todos os fora da lei são eliminados, dependendo do papel de cada um.', 'Para jogar Bang! The Dice Game, cada jogador recebe um papel secreto (xerife, vice, fora da lei ou renegado) e começa com pontos de vida. O xerife é revelado, mas os outros papéis ficam escondidos.\r\n\r\nNo turno, o jogador rola os cinco dados e pode rerrolar até duas vezes. Os símbolos nos dados indicam ações: tiros (em jogadores próximos), dinamite (que causa dano ao próprio jogador), flechas (que acumulam e podem causar dano coletivo), ou cerveja (que cura pontos de vida). O objetivo varia conforme o papel: o xerife e os vices devem eliminar fora da lei e o renegado; os fora da lei precisam derrotar o xerife; e o renegado deve ser o último sobrevivente.\r\n\r\nÉ um jogo rápido, de blefe e sorte, onde cada rolagem pode mudar completamente o rumo da partida.', 'Para jogar Bang! The Dice Game, cada jogador recebe um papel secreto (xerife, vice, fora da lei ou renegado) e começa com pontos de vida. O xerife é revelado, mas os outros papéis ficam escondidos.\r\n\r\nNo turno, o jogador rola os cinco dados e pode rerrolar até duas vezes. Os símbolos nos dados indicam ações: tiros (em jogadores próximos), dinamite (que causa dano ao próprio jogador), flechas (que acumulam e podem causar dano coletivo), ou cerveja (que cura pontos de vida). O objetivo varia conforme o papel: o xerife e os vices devem eliminar fora da lei e o renegado; os fora da lei precisam derrotar o xerife; e o renegado deve ser o último sobrevivente.\r\n\r\nÉ um jogo rápido, de blefe e sorte, onde cada rolagem pode mudar completamente o rumo da partida.', 3, 1, 0, 1, '2026-08-16 21:35:36'),
(10, 'Mantis', 'assets/uploads/games/game_1786916272_6246.jpg', '**Mantis** é um jogo de cartas simples e colorido, criado pelos mesmos designers de *Exploding Kittens*. Ele mistura sorte e estratégia em partidas rápidas, com cartas ilustradas por mantis multicoloridas.  \r\n\r\nO objetivo é formar conjuntos de três cartas da mesma cor para marcar pontos, enquanto tenta roubar ou proteger cartas dos adversários. É fácil de aprender, divertido de jogar em grupo e cheio de momentos imprevisíveis graças às mecânicas de roubo e coleta.', 'Para jogar **Mantis**, cada jogador tenta formar conjuntos de três cartas da mesma cor para marcar pontos. No seu turno, você compra uma carta e decide se vai colocá-la na sua pilha de pontuação ou tentar roubar da pilha de outro jogador.  \r\n\r\nO jogo segue até que alguém alcance a quantidade de pontos definida no início da partida (geralmente 10). É simples, rápido e cheio de momentos de sorte e estratégia, já que você precisa decidir entre proteger suas cartas ou arriscar roubá-las dos adversários.', 'Aqui estão regras simples para jogar **Mantis**:  \r\n\r\n- Cada jogador começa com cartas viradas para baixo em sua pilha.  \r\n- No turno, você compra uma carta e decide se vai colocá-la na sua pilha de pontuação ou tentar roubar da pilha de outro jogador.  \r\n- O jogo continua até que alguém alcance o número de pontos definido no início (normalmente 10).  \r\n- Vence quem atingir primeiro essa pontuação.  \r\n\r\nÉ direto e rápido: colecionar cores, roubar dos outros e correr para completar os conjuntos antes dos adversários.', 3, 1, 0, 1, '2026-08-16 21:37:52'),
(11, 'Wandering Towers', 'assets/uploads/games/game_1786916468_4624.jpg', '**Wandering Towers** é um jogo de tabuleiro leve e estratégico em que os jogadores controlam magos que precisam chegar ao **Castelo do Corvo**. O diferencial é que as torres do tabuleiro se movem e podem ser empilhadas, aprisionando magos de outros jogadores e criando situações caóticas e divertidas.  \r\n\r\nO objetivo é levar todos os seus magos até o castelo e encher seus frascos mágicos, usando cartas para mover magos ou torres. As torres podem se sobrepor, mudando o caminho e bloqueando adversários. É um jogo rápido, interativo e cheio de surpresas, ideal para grupos que gostam de fantasia com um toque de estratégia.', 'Para jogar **Wandering Towers**, cada jogador controla magos que precisam chegar ao **Castelo do Corvo**. No início, os magos são colocados nas torres espalhadas pelo tabuleiro. Em cada turno, o jogador usa cartas para mover **magos** ou **torres** sempre no sentido horário.  \r\n\r\nAs torres podem ser empilhadas umas sobre as outras, aprisionando magos dentro delas. Quando isso acontece, o jogador que fez o movimento enche seus frascos mágicos. O objetivo é levar todos os seus magos até o castelo e completar seus frascos primeiro. É um jogo de corrida com muita interação, já que o movimento das torres muda constantemente o caminho e cria situações inesperadas.', 'Aqui estão regras simples para jogar **Wandering Towers**:  \r\n\r\n- Cada jogador começa com seus magos posicionados em torres no tabuleiro.  \r\n- No turno, o jogador joga uma carta para **mover magos ou torres** sempre no sentido horário.  \r\n- Quando uma torre se move sobre outra, ela pode ser **empilhada**, aprisionando os magos dentro.  \r\n- Ao aprisionar magos, o jogador enche um de seus **frascos mágicos**.  \r\n- O objetivo é levar todos os seus magos até o **Castelo do Corvo** e completar seus frascos primeiro.  \r\n- O jogo termina quando um jogador consegue cumprir essas condições, tornando-se o vencedor.  \r\n\r\nÉ uma corrida estratégica, com bastante interação e caos divertido, já que as torres mudam constantemente de posição.', 3, 1, 0, 1, '2026-08-16 21:41:08'),
(12, 'When I Dream', 'assets/uploads/games/game_1786917367_2761.jpg', '**When I Dream** é um jogo de cartas leve e criativo, centrado em imaginação e interpretação. Um jogador por vez veste a máscara de \"sonhador\" e deve adivinhar palavras a partir das pistas dadas pelos outros jogadores.  \r\n\r\nOs demais participantes assumem papéis secretos: alguns ajudam o sonhador a acertar, outros tentam confundir, e outros buscam equilíbrio entre os dois. A cada rodada, o sonhador descreve o que imagina com base nas pistas, criando uma narrativa de sonho. No fim, ele tenta lembrar e contar o que sonhou, e os pontos são distribuídos conforme os acertos e os papéis ocultos. É um jogo rápido, divertido e cheio de criatividade.', 'Aqui estão regras simples para jogar **When I Dream**:  \r\n\r\n- O jogo acontece em rodadas, e em cada uma há um **sonhador** que veste a máscara e não vê as cartas.  \r\n- Os outros jogadores recebem papéis secretos:  \r\n  - **Fadas** querem que o sonhador acerte as palavras.  \r\n  - **Bichos-papão** querem que ele erre.  \r\n  - **Tricksters** querem equilíbrio entre acertos e erros.  \r\n- Durante o turno, os jogadores dão pistas sobre a carta revelada, uma palavra por vez.  \r\n- O sonhador tenta adivinhar a palavra com base nas pistas.  \r\n- A rodada dura até o baralho acabar ou o tempo terminar.  \r\n- No fim, o sonhador conta uma história com as palavras que conseguiu lembrar, e os pontos são distribuídos conforme os papéis secretos.  \r\n\r\nÉ um jogo de imaginação, blefe e memória, onde cada rodada cria um “sonho” único e divertido.', 'Aqui estão regras simples para jogar **When I Dream**:  \r\n\r\n- O jogo acontece em rodadas de sonho. Um jogador é o **sonhador** e veste a máscara, sem ver as cartas.  \r\n- Os outros jogadores recebem papéis secretos:  \r\n  - **Fadas**: querem que o sonhador acerte.  \r\n  - **Bichos-papão**: querem que ele erre.  \r\n  - **Tricksters**: querem equilíbrio entre acertos e erros.  \r\n- Durante a rodada, cartas com palavras são reveladas. Os jogadores dão **pistas de uma palavra** cada vez, tentando guiar ou confundir o sonhador.  \r\n- O sonhador deve adivinhar a palavra com base nas pistas.  \r\n- A rodada dura até o tempo acabar (geralmente 2 minutos).  \r\n- No fim, o sonhador conta uma história com as palavras que lembra, criando a narrativa do sonho.  \r\n- Pontos são distribuídos conforme os papéis secretos e os acertos/erros do sonhador.  \r\n\r\nÉ um jogo de criatividade, blefe e memória, onde cada rodada cria um sonho único e divertido.', 3, 1, 0, 1, '2026-08-16 21:56:07'),
(13, 'Código Secreto', 'assets/uploads/games/game_1786917735_7330.webp', '**Código Secreto (Codenames)** é um jogo de palavras em equipes, onde os jogadores competem para descobrir quais palavras no tabuleiro pertencem ao seu time.  \r\n\r\nCada equipe tem um **mestre-espião**, que dá pistas de uma única palavra e um número, tentando conectar várias palavras da sua cor ao mesmo tempo. Os companheiros de equipe discutem e escolhem quais palavras acham que se relacionam com a pista.  \r\n\r\nO objetivo é ser a primeira equipe a revelar todas as suas palavras, evitando escolher as palavras da equipe adversária ou o **assassino**, que encerra a partida imediatamente. É um jogo rápido, divertido e cheio de criatividade e dedução.', 'Aqui vai uma explicação **bem simples** de como jogar **Código Secreto (Codenames):**  \r\n\r\n- Divida em **duas equipes**.  \r\n- Cada equipe tem um **mestre-espião**, que sabe quais palavras no tabuleiro pertencem ao seu time.  \r\n- O mestre-espião dá uma **pista de uma palavra** e um número (ex.: “animal 2”).  \r\n- A equipe tenta adivinhar quais palavras se conectam com a pista.  \r\n- Se acertar, marca a palavra com a cor da equipe. Se errar, pode revelar palavra do adversário ou até o **assassino**, que encerra o jogo.  \r\n- Ganha a equipe que revelar todas as suas palavras primeiro.  \r\n\r\nÉ só isso: pistas inteligentes, dedução e trabalho em equipe. 🎯', 'Aqui estão as **regras simples do jogo Código Secreto (Codenames):**  \r\n\r\n- O tabuleiro é formado por **25 palavras** dispostas em uma grade.  \r\n- Os jogadores se dividem em **duas equipes** (vermelha e azul).  \r\n- Cada equipe escolhe um **mestre-espião**, que vê a chave secreta indicando quais palavras pertencem ao seu time.  \r\n- O mestre-espião dá uma **pista de uma palavra** e um número (ex.: “animal 2”), tentando conectar várias palavras da sua cor.  \r\n- Os companheiros de equipe discutem e apontam as palavras que acham corretas.  \r\n- Se acertarem, a palavra é coberta com a cor da equipe. Se errarem, pode revelar palavra neutra, da outra equipe ou o **assassino** (que encerra o jogo imediatamente).  \r\n- As equipes jogam alternadamente.  \r\n- **Vence a equipe que revelar todas as suas palavras primeiro.**  \r\n\r\nÉ um jogo de **dedução, criatividade e trabalho em equipe**, onde cada pista pode mudar completamente o rumo da partida. 🎯', 0, 3, 0, 1, '2026-08-16 22:02:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `winner_user_id` int(11) DEFAULT NULL,
  `winner_team_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `color` varchar(30) NOT NULL,
  `total_score` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `teams`
--

INSERT INTO `teams` (`id`, `name`, `color`, `total_score`, `created_at`) VALUES
(1, 'TIME 1', 'purple', 0, '2026-05-03 03:52:48'),
(2, 'TIME 2', 'orange', 0, '2026-05-03 03:52:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `team_score_history`
--

CREATE TABLE `team_score_history` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('player','admin') DEFAULT 'player',
  `is_active` tinyint(1) DEFAULT 1,
  `total_score` int(11) DEFAULT 0,
  `costume_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `team_id`, `name`, `username`, `password_hash`, `role`, `is_active`, `total_score`, `costume_photo`, `created_at`) VALUES
(1, NULL, 'ADMIN', 'admin', '$2a$12$uIKFv7rFvou1yPLpBNhhCeCzkBFkzmAHaoxFKearctaiu1QCVl.aW', 'admin', 1, 0, 'assets/uploads/costumes/fantasia_1_1777785474.png', '2026-05-02 21:28:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_score_history`
--

CREATE TABLE `user_score_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `costume_votes`
--
ALTER TABLE `costume_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voter_user_id` (`voter_user_id`),
  ADD KEY `voted_user_id` (`voted_user_id`);

--
-- Índices de tabela `event_settings`
--
ALTER TABLE `event_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Índices de tabela `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `winner_user_id` (`winner_user_id`),
  ADD KEY `winner_team_id` (`winner_team_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Índices de tabela `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `team_score_history`
--
ALTER TABLE `team_score_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `match_id` (`match_id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `team_id` (`team_id`);

--
-- Índices de tabela `user_score_history`
--
ALTER TABLE `user_score_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `match_id` (`match_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `costume_votes`
--
ALTER TABLE `costume_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `event_settings`
--
ALTER TABLE `event_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `team_score_history`
--
ALTER TABLE `team_score_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de tabela `user_score_history`
--
ALTER TABLE `user_score_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `costume_votes`
--
ALTER TABLE `costume_votes`
  ADD CONSTRAINT `costume_votes_ibfk_1` FOREIGN KEY (`voter_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `costume_votes_ibfk_2` FOREIGN KEY (`voted_user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`),
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`winner_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`winner_team_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `team_score_history`
--
ALTER TABLE `team_score_history`
  ADD CONSTRAINT `team_score_history_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `team_score_history_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`);

--
-- Restrições para tabelas `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);

--
-- Restrições para tabelas `user_score_history`
--
ALTER TABLE `user_score_history`
  ADD CONSTRAINT `user_score_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_score_history_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
