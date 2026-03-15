<?php
/**
 * NullCastle Systems — product.php
 * Individual product / service detail page.
 * Records this visit in nc_recently (last 5) and nc_hitcount (most visited).
 */

/* ------------------------------------------------------------------ *
 *  PRODUCT CATALOGUE (loaded from shared file)
 * ------------------------------------------------------------------ */
require_once __DIR__ . '/products-catalogue.php';

/* ------------------------------------------------------------------ *
 *  ROUTE: validate ?id= param
 * ------------------------------------------------------------------ */
$id = $_GET['id'] ?? '';
if (!isset($PRODUCTS[$id])) {
    header('Location: products.php');
    exit;
}
$p = $PRODUCTS[$id];

/* ------------------------------------------------------------------ *
 *  COOKIE: update recently visited (last 5, most recent first)
 * ------------------------------------------------------------------ */
$COOKIE_RECENT  = 'nc_recently';
$COOKIE_HITS    = 'nc_hitcount';
$COOKIE_EXPIRE  = time() + 60 * 60 * 24 * 30; // 30 days

// Recently visited
$recently = [];
if (!empty($_COOKIE[$COOKIE_RECENT])) {
    $recently = array_filter(
        explode('|', $_COOKIE[$COOKIE_RECENT]),
        fn($x) => isset($PRODUCTS[$x])
    );
    $recently = array_values($recently);
}
// Remove current from list, prepend, cap at 5
$recently = array_filter($recently, fn($x) => $x !== $id);
array_unshift($recently, $id);
$recently = array_slice($recently, 0, 5);
setcookie($COOKIE_RECENT, implode('|', $recently), $COOKIE_EXPIRE, '/', '', false, true);

// Hit counts
$hitcounts = [];
if (!empty($_COOKIE[$COOKIE_HITS])) {
    $decoded = json_decode($_COOKIE[$COOKIE_HITS], true);
    if (is_array($decoded)) $hitcounts = $decoded;
}
$hitcounts[$id] = ($hitcounts[$id] ?? 0) + 1;
setcookie($COOKIE_HITS, json_encode($hitcounts), $COOKIE_EXPIRE, '/', '', false, true);

/* ------------------------------------------------------------------ *
 *  Related products (3 random others)
 * ------------------------------------------------------------------ */
$others = array_filter(array_keys($PRODUCTS), fn($x) => $x !== $id);
shuffle($others);
$related = array_slice($others, 0, 3);

/* ------------------------------------------------------------------ *
 *  Color helper
 * ------------------------------------------------------------------ */
$col = $p['color'];
$glowVar = match($col) {
    'red'   => 'var(--glow-red)',
    'amber' => 'var(--glow-amber)',
    'cyan'  => 'var(--glow-cyan)',
    default => 'var(--glow-green)',
};
?>
<!DOCTYPE html>
<html lang="en" class="page-products">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($p['name']) ?> — NullCastle Systems</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Cinzel:wght@400;700;900&family=Raleway:ital,wght@0,300;0,400;0,600;1,300&display=swap" />
  <style>
    .product-detail-hero {
      position: relative; overflow: hidden;
      border-bottom: 1px solid var(--border);
    }
    .product-detail-hero-img {
      width: 100%; height: 380px; object-fit: cover;
      display: block; filter: brightness(0.35) saturate(0.6);
    }
    .product-detail-hero-overlay {
      position: absolute; inset: 0;
      display: flex; align-items: flex-end;
      padding: 3rem 0;
    }
    .product-detail-meta { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
    .product-detail-price-box {
      background: rgba(0,0,0,0.6); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 1.5rem 2rem;
      display: flex; flex-direction: column; gap: 0.5rem;
      min-width: 220px;
    }
    .product-detail-features {
      list-style: none; padding: 0; margin: 1.5rem 0;
    }
    .product-detail-features li {
      padding: 0.6rem 0;
      border-bottom: 1px solid var(--border);
      font-family: var(--font-mono); font-size: 0.82rem; color: var(--text-dim);
      display: flex; align-items: flex-start; gap: 0.75rem;
    }
    .product-detail-features li::before {
      content: '✓'; color: <?= $glowVar ?>; flex-shrink: 0; margin-top: 0.05rem;
    }
    .related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
    .related-card { padding: 0; overflow: hidden; }
    .related-card-img { width: 100%; height: 140px; object-fit: cover; display: block; filter: brightness(0.65) saturate(0.7); transition: filter 0.3s; }
    .related-card:hover .related-card-img { filter: brightness(0.85) saturate(1); }
    .related-card-body { padding: 1.2rem; }
    .breadcrumb {
      font-family: var(--font-mono); font-size: 0.7rem; color: var(--text-dim);
      margin-bottom: 2rem; display: flex; align-items: center; gap: 0.5rem;
    }
    .breadcrumb a { color: var(--text-dim); text-decoration: none; }
    .breadcrumb a:hover { color: var(--glow-green); }
    .breadcrumb span { color: var(--glow-green); }
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
     HERO IMAGE
     ====================================================== -->
<div class="product-detail-hero">
  <img src="<?= htmlspecialchars($p['hero_img']) ?>"
       alt="<?= htmlspecialchars($p['name']) ?>"
       class="product-detail-hero-img" />
  <div class="product-detail-hero-overlay">
    <div class="container">
      <div class="product-detail-meta">
        <span class="tag <?= htmlspecialchars($p['tag'][1]) ?>"><?= htmlspecialchars($p['tag'][0]) ?></span>
        <span style="font-family:var(--font-mono); font-size:0.7rem; color:var(--text-dim);">
          SERVICE // <?= htmlspecialchars($p['num']) ?>
        </span>
      </div>
      <h1 style="font-size:clamp(1.8rem,4vw,3rem); color:<?= $glowVar ?>; margin-bottom:0.5rem;">
        <?= htmlspecialchars($p['name']) ?>
      </h1>
      <p style="color:var(--text-dim); font-size:1.1rem; max-width:600px; margin:0;">
        <?= htmlspecialchars($p['short']) ?>
      </p>
    </div>
  </div>
</div>

<!-- ======================================================
     DETAIL BODY
     ====================================================== -->
<section class="section">
  <div class="container">

    <div class="breadcrumb">
      <a href="products.php">&gt; /services</a>
      <span>/</span>
      <span><?= htmlspecialchars($p['name']) ?></span>
      <span style="margin-left:auto;">
        <a href="recently-visited.php" style="color:var(--glow-green)">Recently Visited</a>
        &nbsp;|&nbsp;
        <a href="most-visited.php" style="color:var(--glow-green)">Most Visited</a>
      </span>
    </div>

    <div style="display:grid; grid-template-columns:1fr auto; gap:3rem; align-items:start; flex-wrap:wrap;">

      <!-- LEFT: description + features -->
      <div>
        <h2 style="font-size:1.3rem; color:var(--text-bright); margin-bottom:1rem;">Overview</h2>
        <div class="divider" style="background:linear-gradient(90deg,<?= $glowVar ?>,transparent); margin-bottom:1.5rem;"></div>
        <p style="color:var(--text-dim); line-height:1.8; font-size:1rem;">
          <?= htmlspecialchars($p['desc']) ?>
        </p>

        <h3 style="font-family:var(--font-mono); font-size:0.85rem; color:<?= $glowVar ?>; letter-spacing:0.15em; text-transform:uppercase; margin-top:2rem;">
          &gt; What's Included
        </h3>
        <ul class="product-detail-features">
          <?php foreach ($p['features'] as $f): ?>
          <li><?= htmlspecialchars($f) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- RIGHT: price + CTA -->
      <div class="product-detail-price-box">
        <div style="font-family:var(--font-mono); font-size:0.65rem; color:var(--text-dim); letter-spacing:0.15em; text-transform:uppercase;">
          Starting from
        </div>
        <div>
          <span class="price-amount" style="font-size:2rem; color:<?= $glowVar ?>;"><?= htmlspecialchars($p['price']) ?></span>
          <span class="price-period" style="font-size:0.85rem;"><?= htmlspecialchars($p['period']) ?></span>
        </div>
        <div class="divider" style="background:var(--border); margin:0.5rem 0;"></div>
        <a href="contact.php" class="btn btn-filled" style="text-align:center; font-size:0.78rem; display:block;">
          &gt; <?= htmlspecialchars($p['cta']) ?>
        </a>
        <a href="products.php" class="btn" style="text-align:center; font-size:0.72rem; display:block; margin-top:0.5rem; color:var(--text-dim); border-color:var(--border);">
          ← All Services
        </a>
      </div>

    </div>
  </div>
</section>

<!-- ======================================================
     RELATED SERVICES
     ====================================================== -->
<section class="section" style="background:var(--deep); border-top:1px solid var(--border);">
  <div class="container">
    <div class="section-heading" style="margin-bottom:2rem;">
      <p class="pre-title">&gt; ls /services/ | grep -v <?= htmlspecialchars($id) ?></p>
      <h2>Related <span class="glow-text-green">Services</span></h2>
      <div class="divider divider-center"></div>
    </div>
    <div class="related-grid">
      <?php foreach ($related as $rid):
        $r = $PRODUCTS[$rid];
        $rc = match($r['color']) {
          'red'   => 'var(--glow-red)',
          'amber' => 'var(--glow-amber)',
          'cyan'  => 'var(--glow-cyan)',
          default => 'var(--glow-green)',
        };
      ?>
      <div class="card related-card">
        <img src="<?= htmlspecialchars($r['img']) ?>" alt="<?= htmlspecialchars($r['name']) ?>" class="related-card-img" loading="lazy" />
        <div class="related-card-body">
          <div class="product-num" style="font-size:0.65rem;">
            SERVICE // <?= htmlspecialchars($r['num']) ?> &nbsp;
            <span class="tag <?= htmlspecialchars($r['tag'][1]) ?>"><?= htmlspecialchars($r['tag'][0]) ?></span>
          </div>
          <h4 style="color:<?= $rc ?>; margin:0.5rem 0;"><?= htmlspecialchars($r['name']) ?></h4>
          <p style="font-size:0.8rem; color:var(--text-dim); margin-bottom:1rem;"><?= htmlspecialchars($r['short']) ?></p>
          <a href="product.php?id=<?= urlencode($r['id']) ?>" class="btn" style="font-size:0.72rem; color:<?= $rc ?>; border-color:<?= $rc ?>;">&gt; View Details</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<footer style="border-top:1px solid var(--border); padding:2rem 0; text-align:center;">
  <div class="container">
    <p style="font-family:var(--font-mono); font-size:0.7rem; color:var(--text-dim); letter-spacing:0.1em;">
      © <?= date('Y') ?> NullCastle Systems &nbsp;|&nbsp;
      <a href="products.php" style="color:var(--text-dim)">Services</a> &nbsp;|&nbsp;
      <a href="recently-visited.php" style="color:var(--glow-green)">Recently Visited</a> &nbsp;|&nbsp;
      <a href="most-visited.php" style="color:var(--glow-green)">Most Visited</a>
    </p>
  </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
