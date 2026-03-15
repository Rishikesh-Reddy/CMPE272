<?php
/**
 * NullCastle Systems — recently-visited.php
 * Displays the last 5 product pages the user visited, read from the nc_recently cookie.
 */

require_once __DIR__ . '/products-catalogue.php'; // shared catalogue

$COOKIE_RECENT = 'nc_recently';

$recently = [];
if (!empty($_COOKIE[$COOKIE_RECENT])) {
    $recently = array_filter(
        explode('|', $_COOKIE[$COOKIE_RECENT]),
        fn($id) => isset($PRODUCTS[$id])
    );
    $recently = array_values(array_slice($recently, 0, 5));
}
?>
<!DOCTYPE html>
<html lang="en" class="page-products">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Recently Visited — NullCastle Systems</title>
  <meta name="robots" content="noindex, nofollow" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Cinzel:wght@400;700;900&family=Raleway:ital,wght@0,300;0,400;0,600;1,300&display=swap" />
  <style>
    .history-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
    .history-card { padding: 0; overflow: hidden; }
    .history-card-img { width: 100%; height: 160px; object-fit: cover; display: block; filter: brightness(0.65) saturate(0.7); transition: filter 0.3s; }
    .history-card:hover .history-card-img { filter: brightness(0.9) saturate(1); }
    .history-card-body { padding: 1.4rem; }
    .history-rank {
      font-family: var(--font-mono); font-size: 0.65rem; color: var(--text-dim);
      letter-spacing: 0.15em; margin-bottom: 0.5rem;
    }
    .empty-state {
      text-align: center; padding: 4rem 2rem;
      border: 1px dashed var(--border); border-radius: var(--radius);
    }
    .empty-state-icon { font-size: 3rem; margin-bottom: 1rem; }
    .cookie-meta {
      font-family: var(--font-mono); font-size: 0.72rem; color: var(--text-dim);
      background: rgba(0,0,0,0.3); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 0.75rem 1.25rem;
      margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;
    }
  </style>
</head>
<body>

<nav>
  <div class="nav-inner">
    <a href="index.html" class="nav-logo">
      <div class="nav-logo-icon">NC</div>
      <span class="nav-logo-text">Null<span>Castle</span></span>
    </a>
    <ul class="nav-links">
      <li><a href="index.html"    data-num="00">Home</a></li>
      <li><a href="about.html"    data-num="01">About</a></li>
      <li><a href="products.php"  data-num="02" class="active">Services</a></li>
      <li><a href="news.html"     data-num="03">News</a></li>
      <li><a href="contact.php"   data-num="04">Contact</a></li>
      <li><a href="login.php"     data-num="05" style="color:var(--glow-red)">⬛ Admin</a></li>
    </ul>
    <div class="nav-status"><div class="nav-status-dot"></div>SYS:ONLINE</div>
    <div class="nav-hamburger" role="button" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>

<!-- ======================================================
     HERO
     ====================================================== -->
<section class="products-hero section-sm">
  <div class="container fade-in">
    <p class="pre-title" style="font-family:var(--font-mono); font-size:0.75rem; color:var(--glow-cyan); letter-spacing:0.2em; text-transform:uppercase; margin-bottom:0.5rem;">
      &gt; cat ~/.nc_recently_visited
    </p>
    <h1>Recently <span class="glow-text-cyan">Visited</span></h1>
    <div class="divider" style="background:linear-gradient(90deg,var(--glow-cyan),transparent)"></div>
    <p style="color:var(--text-dim); max-width:580px; font-size:1.05rem;">
      The last <?= min(5, count($recently)) ?> service page<?= count($recently) !== 1 ? 's' : '' ?>
      you explored, pulled from your browser cookies.
    </p>
  </div>
</section>

<!-- ======================================================
     CONTENT
     ====================================================== -->
<section class="section" style="padding-top:0;">
  <div class="container">

    <div class="cookie-meta">
      <span>
        <span style="color:var(--glow-cyan);">cookie</span>: nc_recently &nbsp;·&nbsp;
        <?= count($recently) ?>/5 entries recorded
      </span>
      <span style="display:flex; gap:1rem;">
        <a href="most-visited.php" style="color:var(--glow-green);">&gt; Most Visited →</a>
        <a href="products.php" style="color:var(--text-dim);">← All Services</a>
      </span>
    </div>

    <?php if (empty($recently)): ?>
    <div class="empty-state">
      <div class="empty-state-icon">📭</div>
      <h3 style="color:var(--text-dim); margin-bottom:0.5rem;">No visit history yet</h3>
      <p style="color:var(--text-dim); font-size:0.9rem; margin-bottom:1.5rem;">
        Browse our service pages and they'll appear here automatically.
      </p>
      <a href="products.php" class="btn btn-filled">&gt; Explore Services</a>
    </div>
    <?php else: ?>
    <div class="history-grid">
      <?php foreach ($recently as $i => $pid):
        $p = $PRODUCTS[$pid];
        $glowVar = match($p['color']) {
          'red'   => 'var(--glow-red)',
          'amber' => 'var(--glow-amber)',
          'cyan'  => 'var(--glow-cyan)',
          default => 'var(--glow-green)',
        };
      ?>
      <div class="card history-card">
        <img src="<?= htmlspecialchars($p['img']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="history-card-img" loading="lazy" />
        <div class="history-card-body">
          <div class="history-rank">
            VISIT #<?= $i + 1 ?> (most recent<?= $i === 0 ? ' — just now' : '' ?>) &nbsp;·&nbsp;
            <span class="tag <?= htmlspecialchars($p['tag'][1]) ?>"><?= htmlspecialchars($p['tag'][0]) ?></span>
          </div>
          <h3 style="color:<?= $glowVar ?>; margin:0.4rem 0 0.5rem;"><?= htmlspecialchars($p['name']) ?></h3>
          <p style="font-size:0.82rem; color:var(--text-dim); margin-bottom:1.2rem;">
            <?= htmlspecialchars($p['short']) ?>
          </p>
          <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
            <span>
              <span class="price-amount" style="color:<?= $glowVar ?>; font-size:1.1rem;"><?= htmlspecialchars($p['price']) ?></span>
              <span class="price-period"><?= htmlspecialchars($p['period']) ?></span>
            </span>
            <a href="product.php?id=<?= urlencode($p['id']) ?>"
               class="btn" style="font-size:0.72rem; color:<?= $glowVar ?>; border-color:<?= $glowVar ?>;">
              &gt; View Again
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</section>

<footer style="border-top:1px solid var(--border); padding:2rem 0; text-align:center;">
  <div class="container">
    <p style="font-family:var(--font-mono); font-size:0.7rem; color:var(--text-dim); letter-spacing:0.1em;">
      © <?= date('Y') ?> NullCastle Systems &nbsp;|&nbsp;
      <a href="products.php" style="color:var(--text-dim)">All Services</a> &nbsp;|&nbsp;
      <a href="most-visited.php" style="color:var(--glow-green)">Most Visited</a>
    </p>
  </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
