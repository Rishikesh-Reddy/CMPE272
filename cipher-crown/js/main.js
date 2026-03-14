/* ═══════════════════════════════════════════════
   CIPHER & CROWN TECHNOLOGIES — main.js
   ═══════════════════════════════════════════════ */

// ── Navigation injection ────────────────────────
const NAV_HTML = `
<nav id="site-nav">
  <a href="index.html" class="nav-logo" aria-label="Cipher &amp; Crown Home">
    <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <path d="M16 2L3 9v14l13 7 13-7V9L16 2z" stroke="#c9a84c" stroke-width="1.2" fill="none"/>
      <path d="M16 2v28M3 9l13 7 13-7" stroke="#c9a84c" stroke-width="0.8" opacity="0.4"/>
      <text x="10" y="20" fill="#c9a84c" font-size="10" font-family="serif" font-weight="bold">C</text>
    </svg>
    <span>CIPHER <span class="amp">&amp;</span> CROWN</span>
  </a>

  <ul class="nav-links" id="nav-links">
    <li><a href="index.html"    data-page="home">Home</a></li>
    <li><a href="about.html"    data-page="about">About</a></li>
    <li><a href="products.html" data-page="products">Products</a></li>
    <li><a href="news.html"     data-page="news">News</a></li>
    <li><a href="contacts.html" data-page="contacts">Contact</a></li>
  </ul>

  <div class="nav-terminal" aria-hidden="true">
    <span id="nav-clock">00:00:00</span> <span class="cursor-blink"></span>
  </div>

  <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
    <span></span><span></span><span></span>
  </button>
</nav>`;

const FOOTER_HTML = `
<footer id="site-footer">
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="nav-logo" style="margin-bottom:1rem;">
        <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
          <path d="M16 2L3 9v14l13 7 13-7V9L16 2z" stroke="#c9a84c" stroke-width="1.2" fill="none"/>
          <path d="M16 2v28M3 9l13 7 13-7" stroke="#c9a84c" stroke-width="0.8" opacity="0.4"/>
          <text x="10" y="20" fill="#c9a84c" font-size="10" font-family="serif" font-weight="bold">C</text>
        </svg>
        <span style="font-size:0.75rem;">CIPHER <span class="amp">&amp;</span> CROWN</span>
      </div>
      <p>Elite cybersecurity &amp; software engineering. We protect kingdoms in the digital realm — one firewall at a time.</p>
    </div>
    <div class="footer-col">
      <h4>Navigate</h4>
      <ul>
        <li><a href="index.html">Home</a></li>
        <li><a href="about.html">About Us</a></li>
        <li><a href="products.html">Products</a></li>
        <li><a href="news.html">News</a></li>
        <li><a href="contacts.html">Contact</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Products</h4>
      <ul>
        <li><a href="products.html">Shadowfire Suite</a></li>
        <li><a href="products.html">Iron Ledger</a></li>
        <li><a href="products.html">The Raven</a></li>
        <li><a href="products.html">Quantum Forge</a></li>
        <li><a href="products.html">The Wall</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Connect</h4>
      <ul>
        <li><a href="mailto:ravens@cipherandcrown.io">ravens@cipherandcrown.io</a></li>
        <li><a href="contacts.html">+1 (555) 000-1337</a></li>
        <li><a href="#">GitHub</a></li>
        <li><a href="#">LinkedIn</a></li>
        <li><a href="#">Twitter/X</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>&copy; <span id="footer-year"></span> Cipher &amp; Crown Technologies, Inc. All rights reserved.</span>
    <span class="house-words">"In the game of code, you win or you debug."</span>
    <span style="font-size:0.68rem;color:var(--gray);">
      <span style="color:var(--green);">◉</span> All systems operational
    </span>
  </div>
</footer>`;

// ── Inject nav + footer ─────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  // Inject nav
  const navPlaceholder = document.getElementById('nav-placeholder');
  if (navPlaceholder) navPlaceholder.outerHTML = NAV_HTML;

  // Inject footer
  const footerPlaceholder = document.getElementById('footer-placeholder');
  if (footerPlaceholder) footerPlaceholder.outerHTML = FOOTER_HTML;

  // Footer year
  const fy = document.getElementById('footer-year');
  if (fy) fy.textContent = new Date().getFullYear();

  // ── Active nav link ───────────────────────────
  const currentPage = document.body.dataset.page || 'home';
  document.querySelectorAll('.nav-links a').forEach(link => {
    if (link.dataset.page === currentPage) link.classList.add('active');
  });

  // ── Nav scroll shadow ─────────────────────────
  const nav = document.getElementById('site-nav');
  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });
  }

  // ── Hamburger ─────────────────────────────────
  const toggle = document.getElementById('nav-toggle');
  const links  = document.getElementById('nav-links');
  if (toggle && links) {
    toggle.addEventListener('click', () => {
      links.classList.toggle('open');
      const spans = toggle.querySelectorAll('span');
      const open = links.classList.contains('open');
      spans[0].style.transform = open ? 'rotate(45deg) translate(5px,5px)' : '';
      spans[1].style.opacity   = open ? '0' : '1';
      spans[2].style.transform = open ? 'rotate(-45deg) translate(5px,-5px)' : '';
    });
  }

  // ── Live clock ────────────────────────────────
  const clock = document.getElementById('nav-clock');
  if (clock) {
    const tick = () => {
      const now = new Date();
      clock.textContent = [now.getHours(), now.getMinutes(), now.getSeconds()]
        .map(n => String(n).padStart(2, '0')).join(':');
    };
    tick();
    setInterval(tick, 1000);
  }

  // ── Scroll reveal ─────────────────────────────
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

  // ── Animate stat counters ─────────────────────
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count, 10);
    const suffix = el.dataset.suffix || '';
    const prefix = el.dataset.prefix || '';
    let current = 0;
    const increment = target / 60;
    const timer = setInterval(() => {
      current += increment;
      if (current >= target) { current = target; clearInterval(timer); }
      el.textContent = prefix + Math.floor(current).toLocaleString() + suffix;
    }, 25);
  });

  // ── Glitch elements: set data-text ───────────
  document.querySelectorAll('.glitch').forEach(el => {
    if (!el.dataset.text) el.dataset.text = el.textContent;
  });
});

// ── Canvas Particle Background (Homepage) ──────
function initCanvas() {
  const canvas = document.getElementById('canvas-bg');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  let W, H, particles = [], runes = [];

  const RUNE_CHARS = '⚔🛡⚙🔐⚡🌑★◆▲▼◉⊕⊗∞≠≡✦';
  const RUNE_POOL  = Array.from(RUNE_CHARS);

  const resize = () => {
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
  };

  class Particle {
    constructor() { this.reset(true); }
    reset(initial = false) {
      this.x  = Math.random() * W;
      this.y  = initial ? Math.random() * H : -10;
      this.vy = 0.3 + Math.random() * 0.8;
      this.vx = (Math.random() - 0.5) * 0.3;
      this.size = 1 + Math.random() * 2;
      this.alpha = 0.1 + Math.random() * 0.5;
      this.color = Math.random() > 0.6
        ? `rgba(201,168,76,${this.alpha})`
        : `rgba(0,255,65,${this.alpha * 0.4})`;
    }
    update() {
      this.x += this.vx;
      this.y += this.vy;
      if (this.y > H + 10) this.reset();
    }
    draw() {
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
      ctx.fillStyle = this.color;
      ctx.fill();
    }
  }

  class FloatingRune {
    constructor() { this.reset(true); }
    reset(initial = false) {
      this.x     = Math.random() * W;
      this.y     = initial ? Math.random() * H : H + 20;
      this.char  = RUNE_POOL[Math.floor(Math.random() * RUNE_POOL.length)];
      this.size  = 10 + Math.random() * 14;
      this.vy    = -(0.2 + Math.random() * 0.5);
      this.alpha = 0.03 + Math.random() * 0.1;
      this.life  = 0;
      this.maxLife = 300 + Math.random() * 200;
    }
    update() {
      this.y += this.vy;
      this.life++;
      if (this.life > this.maxLife) this.reset();
    }
    draw() {
      const fade = Math.sin((this.life / this.maxLife) * Math.PI);
      ctx.globalAlpha = this.alpha * fade;
      ctx.fillStyle = '#c9a84c';
      ctx.font = `${this.size}px serif`;
      ctx.fillText(this.char, this.x, this.y);
      ctx.globalAlpha = 1;
    }
  }

  const init = () => {
    resize();
    particles = Array.from({ length: 120 }, () => new Particle());
    runes     = Array.from({ length: 25 },  () => new FloatingRune());
  };

  let animId;
  const animate = () => {
    ctx.clearRect(0, 0, W, H);
    // Draw subtle grid
    ctx.strokeStyle = 'rgba(201,168,76,0.025)';
    ctx.lineWidth   = 0.5;
    const gridSize = 50;
    for (let x = 0; x < W; x += gridSize) {
      ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, H); ctx.stroke();
    }
    for (let y = 0; y < H; y += gridSize) {
      ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(W, y); ctx.stroke();
    }
    particles.forEach(p => { p.update(); p.draw(); });
    runes.forEach(r => { r.update(); r.draw(); });
    animId = requestAnimationFrame(animate);
  };

  window.addEventListener('resize', () => { resize(); });
  init();
  animate();
}

// ── Typing animation ───────────────────────────
function initTyping() {
  const el = document.getElementById('typing-text');
  if (!el) return;
  const phrases = [
    'We do not sow — we code.',
    'Every system has a vulnerability.',
    'The night is dark and full of bugs.',
    'Power is power. So is root access.',
    'When you play the game of code...',
  ];
  let pi = 0, ci = 0, deleting = false;
  const type = () => {
    const phrase = phrases[pi];
    if (!deleting) {
      el.textContent = phrase.substring(0, ci + 1);
      ci++;
      if (ci === phrase.length) {
        deleting = true;
        setTimeout(type, 2200);
        return;
      }
    } else {
      el.textContent = phrase.substring(0, ci - 1);
      ci--;
      if (ci === 0) {
        deleting = false;
        pi = (pi + 1) % phrases.length;
      }
    }
    setTimeout(type, deleting ? 40 : 65);
  };
  type();
}

// ── Contact Form (no backend, visual only) ─────
function initContactForm() {
  const form = document.getElementById('contact-form');
  if (!form) return;
  form.addEventListener('submit', e => {
    e.preventDefault();
    const btn = form.querySelector('[type=submit]');
    btn.textContent = '[ TRANSMITTING... ]';
    btn.disabled = true;
    setTimeout(() => {
      form.style.display = 'none';
      const success = document.getElementById('form-success');
      if (success) success.style.display = 'block';
    }, 1800);
  });
}

// ── Load contacts from JSON ────────────────────
async function loadContacts() {
  const container = document.getElementById('team-contacts-container');
  if (!container) return;

  const infoContainer = document.getElementById('company-contacts-info');

  try {
    const res  = await fetch('data/contacts.json');
    if (!res.ok) throw new Error('Network response not ok');
    const data = await res.json();

    // Populate company info
    if (infoContainer && data.headquarters) {
      const hq = data.headquarters;
      document.getElementById('ci-address').textContent =
        `${hq.address}, ${hq.city}, ${hq.state} ${hq.zip}`;
      document.getElementById('ci-phone').textContent   = data.phone?.main || '';
      document.getElementById('ci-support').textContent = data.phone?.support || '';
      document.getElementById('ci-email').textContent   = data.email?.general || '';
      document.getElementById('ci-support-email').textContent = data.email?.support || '';
    }

    // Populate team cards
    container.innerHTML = '';
    const avatars = ['🧙‍♂️','🕵️','🤖','👩‍💻','🦁','⚡'];
    (data.team || []).forEach((member, i) => {
      const card = document.createElement('div');
      card.className = 'team-contact-card reveal';
      card.innerHTML = `
        <div class="tcc-avatar">${avatars[i % avatars.length]}</div>
        <div class="tcc-info">
          <span class="tcc-name">${member.name}</span>
          <span class="tcc-role">${member.role}</span>
          <a class="tcc-email" href="mailto:${member.email}">${member.email}</a>
        </div>
        <div style="font-family:var(--font-prose);font-style:italic;font-size:0.78rem;
                    color:var(--gray-light);max-width:220px;line-height:1.4;">
          "${member.quote}"
        </div>`;
      container.appendChild(card);
    });

    // Re-trigger scroll observer on new elements
    const obs = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); } });
    }, { threshold: 0.1 });
    container.querySelectorAll('.reveal').forEach(el => obs.observe(el));

  } catch (err) {
    container.innerHTML = `
      <div style="padding:1.5rem;border:1px solid var(--border);border-radius:4px;text-align:center;">
        <p style="color:var(--gray-light);font-size:0.85rem;">
          📡 Raven transmission failed — could not load contact data.<br>
          <small style="color:var(--gray);font-size:0.75rem;margin-top:0.5rem;display:block;">
            (When deployed on a web server, contacts load from data/contacts.json)
          </small>
        </p>
      </div>`;
    console.warn('Contacts fetch error:', err);
  }
}

// ── Initialise ─────────────────────────────────
window.addEventListener('DOMContentLoaded', () => {
  initCanvas();
  initTyping();
  initContactForm();
  loadContacts();
});
