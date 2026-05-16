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
const eventDate = new Date(2026, 9, 31, 20, 0, 0).getTime();

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