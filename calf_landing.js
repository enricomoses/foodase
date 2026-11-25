// Landing v2: Motion (versi JS dari Framer Motion) + Lenis (smooth scroll).
// Alur: Hero (langsung ada tombol tur) → Menu unggulan → Cerita layanan → Outlet → Ajakan akhir.
// Semua animasi terikat ke kontainer #lp-scroll, bukan window, karena tur 3D memakai layar yang sama.
import { animate, scroll, inView, stagger } from 'https://cdn.jsdelivr.net/npm/motion@11.11.17/+esm';
import Lenis from 'https://cdn.jsdelivr.net/npm/lenis@1.1.13/+esm';
import { MENU, OUTLETS, SERVICE_STEPS } from './data.js';
import { t, L, onLang } from './i18n.js';

const $ = s => document.querySelector(s);
const $$ = s => [...document.querySelectorAll(s)];
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const rupiah = n => 'Rp ' + n.toLocaleString('id-ID');
const wrap = $('#lp-scroll');
const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
const EASE = [0.32, 0.72, 0, 1]; // sama dengan --ease brand

// ---------- Smooth scroll ----------
let lenis = null;
if (!reduce) {
  lenis = new Lenis({ wrapper: wrap, content: $('.lp-content'), lerp: 0.09, wheelMultiplier: 0.9, smoothWheel: true });
  const raf = time => { lenis.raf(time); requestAnimationFrame(raf); };
  requestAnimationFrame(raf);
}
const goTo = el => lenis ? lenis.scrollTo(el, { offset: -72, duration: 1.2 }) : el.scrollIntoView({ behavior: 'smooth' });

// Kembali ke atas tiap kali landing dibuka lagi
new MutationObserver(() => {
  if (document.body.dataset.view === 'landing') { lenis ? lenis.scrollTo(0, { immediate: true }) : (wrap.scrollTop = 0); lenis?.start(); }
  else lenis?.stop();
}).observe(document.body, { attributes: true, attributeFilter: ['data-view'] });

// Tautan nav & tombol sekunder: scroll halus di dalam kontainer
$$('#landing a[href^="#"]').forEach(a => a.addEventListener('click', e => {
  const el = $(a.getAttribute('href')); if (!el) return;
  e.preventDefault(); goTo(el);
}));
// Semua tombol "Mulai Tur" memakai alur yang sama dengan tombol hero
$$('.go-explore').forEach(b => (b.onclick = () => $('#btn-enter').click()));

// Progres scroll → panorama di belakang (dibaca main.js) + nav jadi solid + tautan aktif
const nav = $('#lp-nav');
const sections = ['#lp-menu', '#lp-steps', '#lp-outlets'].map(s => [s, $(s)]);
scroll(p => {
  window.kcLandingP = p;
  const y = wrap.scrollTop;
  nav.classList.toggle('solid', y > 40);
  let cur = null;
  for (const [id, el] of sections) if (el.offsetTop - innerHeight * 0.4 <= y) cur = id;
  $$('.lp-nav-links a').forEach(a => a.classList.toggle('on', a.getAttribute('href') === cur));
}, { container: wrap });

// ---------- Data ----------
const SIGNATURE = (() => {
  const seen = new Set();
  return [...MENU].filter(m => m.img).sort((a, b) => (b.sold || 0) - (a.sold || 0))
    .filter(m => !seen.has(m.img) && seen.add(m.img)).slice(0, 8);
})();
const cityCount = OUTLETS.reduce((m, o) => (o.city && m.set(o.city, (m.get(o.city) || 0) + 1), m), new Map());
const TOP_CITIES = [...cityCount].sort((a, b) => b[1] - a[1]).slice(0, 10).map(([c]) => c);
const NUMS = () => [[OUTLETS.length, t('about.outlets')], [cityCount.size, t('about.cities')], [MENU.length, t('about.menus')]];

let first = true;
function render() {
  $('#lp-title').innerHTML = t('lp.title').split('|').map(l => {
    const em = /^\*(.*)\*$/.exec(l);
    return `<span class="ln"><span>${em ? `<em>${esc(em[1])}</em>` : esc(l)}</span></span>`;
  }).join('');
  const nums = NUMS().map(([n, l]) => `<div><b data-n="${n}">${first && !reduce ? 0 : n}</b><span>${esc(l)}</span></div>`).join('');
  $('#lp-stats').innerHTML = nums;
  $('#lp-cities').innerHTML = TOP_CITIES.map(c => `<span>${esc(c)}</span>`).join('') + `<span>+${Math.max(0, cityCount.size - TOP_CITIES.length)}</span>`;
  $('#lp-track').innerHTML = SIGNATURE.map((m, i) => `<article class="lp-card">
      <div class="lp-card-ph"><span class="lp-rank">#${i + 1}</span><img src="${m.img}" alt="${esc(m.name)}" loading="lazy" decoding="async"></div>
      <div class="lp-card-body"><small>${esc(m.group)}</small><b>${esc(m.name)}</b><span>${rupiah(m.price)}</span></div>
    </article>`).join('');
  const tl = $('#lp-tl');
  tl.querySelectorAll('li').forEach(li => li.remove());
  tl.insertAdjacentHTML('beforeend', SERVICE_STEPS.map(s => `<li>
      <span class="n">${s.step}</span>
      <small>${esc(L(s, 'kicker'))}</small><b>${esc(L(s, 'title'))}</b><p>${esc(L(s, 'body'))}</p>
    </li>`).join(''));
  $('#lp-year').textContent = new Date().getFullYear();
  if (!first) $$('.lp-title .ln > span').forEach(s => (s.style.transform = 'none'));
}

render();
onLang(render);

// ---------- Animasi ----------
const countUp = root => root.querySelectorAll('[data-n]').forEach(b =>
  animate(0, +b.dataset.n, { duration: 1.4, ease: EASE, onUpdate: v => { b.textContent = Math.round(v); } }));

if (!reduce) {
  // Intro hero: judul naik per baris, lalu elemen lain
  animate('.lp-title .ln > span', { y: ['110%', '0%'] }, { duration: 1.1, delay: stagger(0.09, { startDelay: 0.15 }), ease: EASE });
  animate('.lp-hero [data-rv]', { opacity: [0, 1], y: [16, 0] }, { duration: 0.9, delay: stagger(0.1, { startDelay: 0.45 }), ease: EASE });
  animate('.lp-nav-in', { opacity: [0, 1], y: [-12, 0] }, { duration: 0.8, delay: 0.2, ease: EASE });

  // Hero memudar & menyusut saat di-scroll
  scroll(animate('.lp-hero-in', { y: [0, -80], opacity: [1, 0] }, { ease: 'linear' }),
    { container: wrap, target: $('.lp-hero'), offset: ['start start', 'end start'] });

  // Reveal umum untuk bagian di bawah hero
  inView('.lp-steps [data-rv], .lp-outlets [data-rv], .lp-cta [data-rv]', ({ target: el }) => {
    animate(el, { opacity: [0, 1], y: [28, 0] }, { duration: 0.9, ease: EASE });
  }, { root: wrap, amount: 0.3 });
  inView('#lp-stats', () => countUp($('#lp-stats')), { root: wrap, amount: 0.4 });
} else {
  $$('[data-rv]').forEach(el => (el.style.opacity = 1));
}

// Kartu menu: geser horizontal selama bagian "menempel" + indikator progres
const menuSec = $('#lp-menu'), track = $('#lp-track'), mprog = $('#lp-mprog');
scroll(p => {
  const dist = Math.max(0, track.scrollWidth - innerWidth);
  track.style.transform = `translate3d(${-dist * p}px,0,0)`;
  mprog.style.transform = `scaleX(${p})`;
}, { container: wrap, target: menuSec, offset: ['start start', 'end end'] });

// Garis waktu layanan: garis terisi & langkah menyala mengikuti scroll
const tl = $('#lp-tl'), line = $('#lp-line');
scroll(p => {
  line.style.transform = `scaleY(${p})`;
  const items = tl.querySelectorAll('li');
  items.forEach((li, i) => li.classList.toggle('on', reduce || p >= i / items.length));
}, { container: wrap, target: tl, offset: ['start 65%', 'end 55%'] });
