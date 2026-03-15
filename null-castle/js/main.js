/* ============================================================
   NullCastle Systems — main.js
   ============================================================ */

/* --- Scrolled nav --- */
const nav = document.querySelector('nav');
window.addEventListener('scroll', () => {
  nav.classList.toggle('scrolled', window.scrollY > 30);
});

/* --- Mobile hamburger --- */
const hamburger = document.querySelector('.nav-hamburger');
const navLinks  = document.querySelector('.nav-links');
if (hamburger) {
  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('open');
    const spans = hamburger.querySelectorAll('span');
    if (navLinks.classList.contains('open')) {
      spans[0].style.transform = 'rotate(45deg) translate(5px,5px)';
      spans[1].style.opacity = '0';
      spans[2].style.transform = 'rotate(-45deg) translate(5px,-5px)';
    } else {
      spans.forEach(s => { s.style.transform = ''; s.style.opacity = ''; });
    }
  });
  navLinks.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => {
      navLinks.classList.remove('open');
      hamburger.querySelectorAll('span').forEach(s => { s.style.transform = ''; s.style.opacity = ''; });
    });
  });
}

/* --- Terminal typewriter (home page only) --- */
const terminalBody = document.querySelector('.terminal-body');
if (terminalBody) {
  const lines = [
    { type: 'cmd', text: 'nullcastle --status' },
    { type: 'out', text: 'Initializing NullCastle OS v4.2.0...', cls: 'info' },
    { type: 'out', text: '[ OK ] Firewall: ACTIVE',               cls: 'success' },
    { type: 'out', text: '[ OK ] Encryption: AES-256-GCM',        cls: 'success' },
    { type: 'out', text: '[ OK ] ThreatDB: 2.4M signatures loaded', cls: 'success' },
    { type: 'cmd', text: 'whoami' },
    { type: 'out', text: 'nullcastle\\root — clearance: OMEGA',   cls: 'warn' },
    { type: 'cmd', text: 'ls -la /services/' },
    { type: 'out', text: 'drwx  breach-response/',                cls: '' },
    { type: 'out', text: 'drwx  zero-trust-arch/',                cls: '' },
    { type: 'out', text: 'drwx  dark-web-intel/',                 cls: '' },
    { type: 'cmd', text: 'echo "The castle never sleeps."' },
    { type: 'out', text: 'The castle never sleeps.',              cls: 'success' },
  ];

  let li = 0;
  terminalBody.innerHTML = '';

  function typeText(el, text, cb, speed = 30) {
    let i = 0;
    const interval = setInterval(() => {
      el.textContent += text[i++];
      terminalBody.scrollTop = terminalBody.scrollHeight;
      if (i >= text.length) { clearInterval(interval); if (cb) cb(); }
    }, speed);
  }

  function nextLine() {
    if (li >= lines.length) {
      const cur = document.createElement('span');
      cur.className = 'terminal-cursor';
      terminalBody.appendChild(cur);
      return;
    }
    const line = lines[li];
    const row  = document.createElement('div');
    row.className = 'terminal-line';

    if (line.type === 'cmd') {
      row.innerHTML = '<span class="terminal-prompt">nullcastle@sys:~$&nbsp;</span><span class="terminal-cmd"></span>';
      terminalBody.appendChild(row);
      typeText(row.querySelector('.terminal-cmd'), line.text, () => {
        li++; setTimeout(nextLine, 200);
      }, 35);
    } else {
      const cls = line.cls ? 'terminal-output ' + line.cls : 'terminal-output';
      row.innerHTML = '<span class="' + cls + '"></span>';
      terminalBody.appendChild(row);
      typeText(row.querySelector('span'), line.text, () => {
        li++; setTimeout(nextLine, 60);
      }, 14);
    }
  }

  setTimeout(nextLine, 700);
}

/* --- Intersection observer for fade-in cards ---
     IMPORTANT: exclude .person-card (flip cards) and anything
     inside .terminal-body — both break if opacity/transform
     are overridden by inline styles.
---------------------------------------------------------------- */
const fadeObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.style.opacity  = '1';
      e.target.style.transform = 'translateY(0)';
      fadeObserver.unobserve(e.target); // fire once, then stop
    }
  });
}, { threshold: 0.08 });

document.querySelectorAll(
  '.team-card, .news-card, .product-card, ' +
  '.card:not(.person-card):not(.person-card-front):not(.person-card-back)'
).forEach(el => {
  // Skip anything inside the terminal or inside a person-card
  if (el.closest('.terminal-body') || el.closest('.person-card')) return;
  el.style.opacity    = '0';
  el.style.transform  = 'translateY(22px)';
  el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
  fadeObserver.observe(el);
});

/* --- Counter animation (home stats) --- */
document.querySelectorAll('.hero-stat .value').forEach(el => {
  const raw     = el.textContent.trim();
  const target  = parseFloat(raw);
  const isFloat = raw.includes('.');
  const suffix  = raw.replace(/[\d.]/g, '');
  if (isNaN(target)) return;

  const duration = 2000;
  let startTime  = null;

  function update(now) {
    if (!startTime) startTime = now;
    const progress = Math.min((now - startTime) / duration, 1);
    const eased    = 1 - Math.pow(1 - progress, 3);
    el.textContent = (isFloat ? (eased * target).toFixed(1) : Math.floor(eased * target)) + suffix;
    if (progress < 1) requestAnimationFrame(update);
  }

  const statObs = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) { requestAnimationFrame(update); statObs.disconnect(); }
  }, { threshold: 0.5 });
  statObs.observe(el);
});

/* --- Glitch effect on hero title hover --- */
const glitchEl = document.querySelector('.hero-title');
if (glitchEl) {
  let glitchTimer;
  glitchEl.addEventListener('mouseenter', () => {
    clearTimeout(glitchTimer);
    glitchEl.style.textShadow = '2px 0 #ff3c5a, -2px 0 #00d4ff';
    glitchTimer = setTimeout(() => { glitchEl.style.textShadow = ''; }, 200);
  });
}

/* --- News ticker: duplicate for seamless infinite scroll --- */
const tickerInner = document.querySelector('.news-ticker-inner');
if (tickerInner) {
  tickerInner.innerHTML += tickerInner.innerHTML;
}

/* --- Person / dossier card: tap-to-flip on touch devices ---
     CSS :hover doesn't fire reliably on mobile, so we toggle
     a .flipped class on tap and use that in the CSS instead.
---------------------------------------------------------------- */
document.querySelectorAll('.person-card').forEach(card => {
  // Keyboard: flip on Enter / Space
  card.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      card.classList.toggle('flipped');
    }
  });

  // Touch: first tap flips, second tap flips back
  card.addEventListener('click', e => {
    // Only intercept on touch-capable devices; on desktop CSS :hover handles it
    if (window.matchMedia('(hover: none)').matches) {
      e.preventDefault();
      card.classList.toggle('flipped');
    }
  });
});
