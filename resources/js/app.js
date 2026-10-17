import './bootstrap';

// Iconos: Font Awesome Free (sólidos, regulares y marcas) servidos localmente
import '@fortawesome/fontawesome-free/css/fontawesome.css';
import '@fortawesome/fontawesome-free/css/solid.css';
import '@fortawesome/fontawesome-free/css/regular.css';
import '@fortawesome/fontawesome-free/css/brands.css';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import collapse from '@alpinejs/collapse';

Alpine.plugin(intersect);
Alpine.plugin(collapse);

/* Cuenta regresiva hacia una fecha ISO */
Alpine.data('countdown', (target) => ({
    d: '00', h: '00', m: '00', s: '00', done: false,
    init() {
        const end = new Date(target).getTime();
        const pad = (n) => String(n).padStart(2, '0');
        const tick = () => {
            const diff = Math.max(0, end - Date.now());
            this.done = diff === 0;
            this.d = pad(Math.floor(diff / 864e5));
            this.h = pad(Math.floor((diff % 864e5) / 36e5));
            this.m = pad(Math.floor((diff % 36e5) / 6e4));
            this.s = pad(Math.floor((diff % 6e4) / 1e3));
        };
        tick();
        setInterval(tick, 1000);
    },
}));

/* Contador animado: sube al entrar en pantalla y vuelve a 0 al salir, para repetirse en cada scroll */
Alpine.data('counter', (to, duration = 1800) => ({
    value: 0,
    frame: null,
    run() {
        cancelAnimationFrame(this.frame);
        const start = performance.now();
        const step = (now) => {
            const p = Math.min(1, (now - start) / duration);
            this.value = Math.round(to * (1 - Math.pow(1 - p, 3)));
            if (p < 1) this.frame = requestAnimationFrame(step);
        };
        this.frame = requestAnimationFrame(step);
    },
    reset() {
        cancelAnimationFrame(this.frame);
        this.value = 0;
    },
    get formatted() { return this.value.toLocaleString('es-CO'); },
}));

/* Botón de voto con respuesta instantánea */
Alpine.data('voteButton', ({ url, voted, count, loginUrl, blocked }) => ({
    voted, count, loading: false, pulse: false,
    async toggle() {
        if (loginUrl) return (window.location.href = loginUrl);
        if (blocked) return window.toast(blocked, 'warning');
        this.loading = true;
        try {
            const { data } = await window.axios.post(url, {}, { headers: { Accept: 'application/json' } });
            this.voted = data.voted;
            this.count = data.count;
            window.dispatchEvent(new CustomEvent('vote-updated', { detail: data.count }));
            if (data.voted) { this.pulse = true; setTimeout(() => (this.pulse = false), 500); window.confetti?.(); }
            window.toast(data.message + (data.left !== null && data.voted ? ` Te quedan ${data.left} voto(s).` : ''), data.voted ? 'success' : 'info');
        } catch (e) {
            window.toast(e.response?.data?.message || 'No fue posible registrar el voto.', 'warning');
        } finally {
            this.loading = false;
        }
    },
}));

/* Notificaciones tipo toast */
window.toast = (message, type = 'success') => window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } }));

/* Confeti ligero sin dependencias */
window.confetti = () => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const colors = ['#fdc31b', '#8b5cf6', '#22d3ee', '#ec4899', '#ffffff'];
    const wrap = document.createElement('div');
    wrap.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:100;overflow:hidden';
    document.body.appendChild(wrap);
    for (let i = 0; i < 80; i++) {
        const p = document.createElement('i');
        const size = 6 + Math.random() * 6;
        p.style.cssText = `position:absolute;left:50%;top:55%;width:${size}px;height:${size * 0.4}px;background:${colors[i % colors.length]};border-radius:2px;`;
        wrap.appendChild(p);
        const angle = Math.random() * Math.PI * 2;
        const dist = 150 + Math.random() * 350;
        p.animate([
            { transform: 'translate(0,0) rotate(0)', opacity: 1 },
            { transform: `translate(${Math.cos(angle) * dist}px, ${Math.sin(angle) * dist + 300}px) rotate(${Math.random() * 720}deg)`, opacity: 0 },
        ], { duration: 1200 + Math.random() * 800, easing: 'cubic-bezier(.2,.7,.4,1)', fill: 'forwards' });
    }
    setTimeout(() => wrap.remove(), 2200);
};

/* Revelado al hacer scroll para elementos .reveal */
const io = new IntersectionObserver((entries) => entries.forEach((e) => {
    if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); }
}), { threshold: 0.12 });
document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.reveal').forEach((el) => io.observe(el)));

window.Alpine = Alpine;

Alpine.start();
