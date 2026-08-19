document.addEventListener("DOMContentLoaded", () => {

    // MENU MOBILE
    const toggle = document.getElementById("menu-toggle");
    const nav = document.querySelector(".nav-links");
    const overlay = document.querySelector(".menu-overlay");

    if (toggle && nav && overlay) {

        toggle.addEventListener("click", () => {
            nav.classList.toggle("active");
            overlay.classList.toggle("active");
            toggle.classList.toggle("open");
        });

        overlay.addEventListener("click", () => {
            nav.classList.remove("active");
            overlay.classList.remove("active");
            toggle.classList.remove("open");
        });

    }

    // REVEAL ANIMATION
    const reveals = document.querySelectorAll(".reveal");

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = "1";
                entry.target.style.transform = "translateY(0)";
            }
        });
    });

    reveals.forEach(el => {
        observer.observe(el);
    });

    // NAVBAR SCROLL
    const navbar = document.querySelector(".navbar");

    if (navbar) {
        window.addEventListener("scroll", () => {
            if (window.scrollY > 250) {
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });
    }

});

// CONTADOR REGRESSIVO COM FLIP SIMPLES
const eventDate = new Date(2026, 9, 31, 18, 0, 0).getTime();

const countdown = document.querySelector(".countdown");

const daysEl = document.getElementById("days");
const hoursEl = document.getElementById("hours");
const minutesEl = document.getElementById("minutes");
const secondsEl = document.getElementById("seconds");

function updateFlipNumber(element, value) {
    const newValue = String(value).padStart(2, "0");

    if (element.innerText !== newValue) {
        element.classList.remove("flip");
        void element.offsetWidth;

        element.innerText = newValue;
        element.classList.add("flip");
    }
}

function updateCountdown() {
    const now = Date.now();
    const distance = eventDate - now;

    if (distance <= 0) {
        clearInterval(interval);

        countdown.innerHTML = `
            <h3 class="event-started">O EVENTO COMEÇOU 🎃</h3>
        `;

        return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance / (1000 * 60 * 60)) % 24);
    const minutes = Math.floor((distance / (1000 * 60)) % 60);
    const seconds = Math.floor((distance / 1000) % 60);

    updateFlipNumber(daysEl, days);
    updateFlipNumber(hoursEl, hours);
    updateFlipNumber(minutesEl, minutes);
    updateFlipNumber(secondsEl, seconds);
}

updateCountdown();

const interval = setInterval(updateCountdown, 1000);

const modal = document.getElementById('gameModal');

if (modal) {
    /* =========================
    GAME MODAL (com vídeo)
    ========================= */
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
}