<?php
/**
 * NullCastle Systems — most-visited.php
 * Displays the 5 most visited service pages, tracked via the nc_hitcount cookie.
 */

require_once __DIR__ . '/products-catalogue.php'; // shared catalogue

$COOKIE_HITS = 'nc_hitcount';

$hitcounts = [];
if (!empty($_COOKIE[$COOKIE_HITS])) {
    $decoded = json_decode($_COOKIE[$COOKIE_HITS], true);
    if (is_array($decoded)) {
        // Only keep IDs that exist in our catalogue
        $hitcounts = array_filter($decoded, fn($id) => isset($PRODUCTS[$id]), ARRAY_FILTER_USE_KEY);
    }
}

arsort($hitcounts);
$topIds = array_slice(array_keys($hitcounts), 0, 5);
$totalVisits = array_sum($hitcounts);
?>
<!DOCTYPE html>
<html lang="en" class="page-products">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Most Visited Services — NullCastle Systems</title>
  <meta name="robots" content="noindex, nofollow" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Cinzel:wght@400;700;900&family=Raleway:ital,wght@0,300;0,400;0,600;1,300&display=swap" />
  <style>
    .mv-list { display: flex; flex-direction: column; gap: 1.25rem; }
    .mv-card {
      display: flex; gap: 1.5rem; align-items: center;
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 1.25rem 1.5rem;
      transition: border-color 0.25s, box-shadow 0.25s; overflow: hidden;
      position: relative;
    }
    .mv-card:hover { border-color: rgba(0,255,157,0.25); box-shadow: 0 0 20px rgba(0,255,157,0.04); }
    .mv-card-rank {
      font-family: var(--font-mono); font-size: 2.5rem; font-weight: 900;
      min-width: 2.5rem; text-align: center; line-height: 1;
      flex-shrink: 0;
    }
    .mv-card-img { width: 110px; height: 80px; object-fit: cover; border-radius: calc(var(--radius) - 4px); flex-shrink: 0; filter: brightness(0.7) saturate(0.8); }
    .mv-card-info { flex: 1; min-width: 0; }
    .mv-bar-wrap { height: 4px; background: rgba(255,255,255,0.06); border-radius: 2px; margin-top: 0.75rem; overflow: hidden; }
    .mv-bar { height: 100%; border-radius: 2px; transition: width 1s cubic-bezier(0.22,1,0.36,1); }
    .mv-hits { font-family: var(--font-mono); font-size: 0.7rem; color: var(--text-dim); margin-top: 0.35rem; }
    .mv-card-actions { display: flex; flex-direction: column; gap: 0.5rem; flex-shrink: 0; }
    .empty-state { text-align: center; padding: 4rem 2rem; border: 1px dashed var(--border); border-radius: var(--radius); }
    .cookie-meta {
      font-family: var(--font-mono); font-size: 0.72rem; color: var(--text-dim);
      background: rgba(0,0,0,0.3); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 0.75rem 1.25rem;
      margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;
    }
    @media (max-width: 580px) {
      .mv-card { flex-wrap: wrap; }
      .mv-card-img { display: none; }
      .mv-card-actions { flex-direction: row; width: 100%; }
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
    <p class="pre-title" style="font-family:var(--font-mono); font-size:0.75rem; color:var(--glow-amber); letter-spacing:0.2em; text-transform:uppercase; margin-bottom:0.5rem;">
      &gt; sort -rn ~/.nc_hitcount | head -5
    </p>
    <h1>Most <span style="color:var(--glow-amber)">Visited</span></h1>
    <div class="divider" style="background:linear-gradient(90deg,var(--glow-amber),transparent)"></div>
    <p style="color:var(--text-dim); max-width:580px; font-size:1.05rem;">
      Your top <?= min(5, count($topIds)) ?> most-viewed service<?= count($topIds) !== 1 ? 's' : '' ?>,
      ranked by your visit count — tracked locally in your browser cookies.
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
        <span style="color:var(--glow-amber);">cookie</span>: nc_hitcount &nbsp;·&nbsp;
        <?= $totalVisits ?> total visit<?= $totalVisits !== 1 ? 's' : '' ?> across
        <?= count($hitcounts) ?> service<?= count($hitcounts) !== 1 ? 's' : '' ?>
      </span>
      <span style="display:flex; gap:1rem;">
        <a href="recently-visited.php" style="color:var(--glow-cyan);">&gt; Recently Visited →</a>
        <a href="products.php" style="color:var(--text-dim);">← All Services</a>
      </span>
    </div>

    <?php if (empty($topIds)): ?>
    <div class="empty-state">
      <div style="font-size:3rem; margin-bottom:1rem;">📊</div>
      <h3 style="color:var(--text-dim); margin-bottom:0.5rem;">No visit data yet</h3>
      <p style="color:var(--text-dim); font-size:0.9rem; margin-bottom:1.5rem;">
        Visit service detail pages and they'll be ranked here by frequency.
      </p>
      <a href="products.php" class="btn btn-filled">&gt; Explore Services</a>
    </div>
    <?php else:
      $maxHits = $hitcounts[$topIds[0]] ?? 1;
    ?>
    <div class="mv-list">
      <?php foreach ($topIds as $rank => $pid):
        $p = $PRODUCTS[$pid];
        $hits = $hitcounts[$pid];
        $pct  = round(($hits / $maxHits) * 100);
        $glowVar = match($p['color']) {
          'red'   => 'var(--glow-red)',
          'amber' => 'var(--glow-amber)',
          'cyan'  => 'var(--glow-cyan)',
          default => 'var(--glow-green)',
        };
        $rankColors = ['var(--glow-amber)', 'var(--text-bright)', '#cd7f32', 'var(--text-dim)', 'var(--text-dim)'];
        $rankColor  = $rankColors[$rank] ?? 'var(--text-dim)';
      ?>
      <div class="mv-card" style="border-left: 3px solid <?= $glowVar ?>;">
        <div class="mv-card-rank" style="color:<?= $rankColor ?>;">#<?= $rank + 1 ?></div>
        <img src="<?= htmlspecialchars($p['img']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="mv-card-img" loading="lazy" />
        <div class="mv-card-info">
          <div style="font-family:var(--font-mono); font-size:0.65rem; color:var(--text-dim); letter-spacing:0.12em; margin-bottom:0.3rem;">
            SERVICE // <?= htmlspecialchars($p['num']) ?> &nbsp;
            <span class="tag <?= htmlspecialchars($p['tag'][1]) ?>"><?= htmlspecialchars($p['tag'][0]) ?></span>
          </div>
          <h3 style="color:<?= $glowVar ?>; margin:0 0 0.25rem; font-size:1.1rem;"><?= htmlspecialchars($p['name']) ?></h3>
          <p style="font-size:0.8rem; color:var(--text-dim); margin:0;"><?= htmlspecialchars($p['short']) ?></p>
          <div class="mv-bar-wrap">
            <div class="mv-bar" style="width:<?= $pct ?>%; background:<?= $glowVar ?>;"></div>
          </div>
          <div class="mv-hits">
            <?= $hits ?> visit<?= $hits !== 1 ? 's' : '' ?>
            &nbsp;·&nbsp; <?= $pct ?>% of top score
            &nbsp;·&nbsp; <?= htmlspecialchars($p['price']) ?> <?= htmlspecialchars($p['period']) ?>
          </div>
        </div>
        <div class="mv-card-actions">
          <a href="product.php?id=<?= urlencode($p['id']) ?>"
             class="btn btn-filled" style="font-size:0.72rem; white-space:nowrap;">
            &gt; View
          </a>
          <a href="contact.php"
             class="btn" style="font-size:0.7rem; color:<?= $glowVar ?>; border-color:<?= $glowVar ?>; white-space:nowrap; text-align:center;">
            Enquire
          </a>
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
      <a href="recently-visited.php" style="color:var(--glow-cyan)">Recently Visited</a>
    </p>
  </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
