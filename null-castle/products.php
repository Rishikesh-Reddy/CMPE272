<?php
/**
 * NullCastle Systems — products.php
 * Ten services with individual product pages.
 * Cookie-based tracking: last-5-visited + most-visited.
 */

/* ------------------------------------------------------------------ *
 *  PRODUCT CATALOGUE (loaded from shared file)
 * ------------------------------------------------------------------ */
require_once __DIR__ . '/products-catalogue.php';



/* ------------------------------------------------------------------ *
 *  COOKIE HELPERS
 *  nc_recently  = pipe-separated list of up to 5 most recently visited IDs
 *  nc_hitcount  = JSON object { id: count, … }
 * ------------------------------------------------------------------ */
$COOKIE_RECENT  = 'nc_recently';
$COOKIE_HITS    = 'nc_hitcount';
$COOKIE_EXPIRE  = time() + 60 * 60 * 24 * 30; // 30 days

// Read recently visited
$recently = [];
if (!empty($_COOKIE[$COOKIE_RECENT])) {
    $recently = array_filter(
        explode('|', $_COOKIE[$COOKIE_RECENT]),
        fn($id) => isset($PRODUCTS[$id])
    );
    $recently = array_values($recently);
}

// Read hit counts
$hitcounts = [];
if (!empty($_COOKIE[$COOKIE_HITS])) {
    $decoded = json_decode($_COOKIE[$COOKIE_HITS], true);
    if (is_array($decoded)) $hitcounts = $decoded;
}

// Build top-5 most visited (only products that exist)
$validHits = array_filter($hitcounts, fn($id) => isset($PRODUCTS[$id]), ARRAY_FILTER_USE_KEY);
arsort($validHits);
$mostVisited = array_slice(array_keys($validHits), 0, 5);
?>
<!DOCTYPE html>
<html lang="en" class="page-products">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Services — NullCastle Systems</title>
  <meta name="description" content="NullCastle Systems service offerings: threat intelligence, zero-trust, incident response, red team ops, and more." />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Cinzel:wght@400;700;900&family=Raleway:ital,wght@0,300;0,400;0,600;1,300&display=swap" />
  <style>
    /* ---- Card shell ---- */
    .product-card {
      padding: 0; overflow: hidden;
      display: flex; flex-direction: column; position: relative;
    }
    /* ---- Image wrapper: relative context for badge overlay ---- */
    .product-card-img-wrap { position: relative; flex-shrink: 0; overflow: hidden; border-radius: 14px 14px 0 0; }
    .product-card-img {
      width: 100%; height: 200px; object-fit: cover; display: block;
      border-radius: 14px 14px 0 0;
      filter: brightness(0.72) saturate(0.75);
      transition: filter 0.35s ease, transform 0.35s ease;
    }
    .product-card:hover .product-card-img { filter: brightness(0.88) saturate(1.05); transform: scale(1.04); }
    /* gradient fade into card body */
    .product-card-img-wrap::after {
      content: ''; position: absolute; bottom: 0; left: 0; right: 0;
      height: 64px; background: linear-gradient(to bottom, transparent, var(--card));
      pointer-events: none;
    }
    /* ---- "Most Popular" badge — inside image wrapper, always visible ---- */
    .product-badge {
      position: absolute; top: 12px; left: 12px; z-index: 10;
      background: var(--glow-green); color: var(--black);
      font-family: var(--font-mono); font-size: 0.6rem; font-weight: 700;
      letter-spacing: 0.14em; text-transform: uppercase;
      padding: 4px 10px; border-radius: 4px;
      box-shadow: 0 0 14px rgba(0,255,157,0.55);
    }
    /* ---- Card body ---- */
    .product-card-body { padding: 1.4rem 1.6rem 1.5rem; flex: 1; display: flex; flex-direction: column; }
    .product-card .btn { margin-top: auto; }
    .cookie-banner {
      background: rgba(0,255,157,0.04);
      border: 1px solid rgba(0,255,157,0.2);
      border-radius: var(--radius);
      padding: 1.2rem 1.5rem;
      margin-bottom: 2.5rem;
      display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
    }
    .cookie-banner-text { font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-dim); }
    .cookie-banner-text span { color: var(--glow-green); }
    .cookie-links { display: flex; gap: 0.75rem; flex-wrap: wrap; }
    .cookie-links a {
      font-family: var(--font-mono); font-size: 0.72rem; padding: 0.4rem 0.9rem;
      border: 1px solid var(--border); border-radius: var(--radius);
      color: var(--text-dim); text-decoration: none; transition: all 0.2s;
      white-space: nowrap;
    }
    .cookie-links a:hover { border-color: var(--glow-green); color: var(--glow-green); }
    .cookie-links a.has-data { border-color: rgba(0,255,157,0.35); color: var(--glow-green); }
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
      <li><a href="allied-users.php">⬛ Allied Networks</a></li>
      <li><a href="login.php"     data-num="05" style="color:var(--glow-red)">⬛ Admin</a></li>
    </ul>
    <div class="nav-status"><div class="nav-status-dot"></div>SYS:ONLINE</div>
    <div class="nav-hamburger" role="button" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>

<!-- ======================================================
     PRODUCTS HERO
     ====================================================== -->
<section class="products-hero section-sm">
  <div class="container fade-in">
    <p class="pre-title" style="font-family:var(--font-mono); font-size:0.75rem; color:var(--glow-green); letter-spacing:0.2em; text-transform:uppercase; margin-bottom:0.5rem;">
      &gt; ls -la /services/ | sort -h
    </p>
    <h1>Our <span class="glow-text-green">Arsenal</span></h1>
    <div class="divider"></div>
    <p style="color:var(--text-dim); max-width:600px; font-size:1.05rem;">
      Ten battle-tested service lines. Each one exists because a real threat
      demanded a real answer. No upsells. No bloat. Just solutions that work
      at 3am when everything is on fire.
    </p>
  </div>
</section>

<!-- ======================================================
     COOKIE TRACKING BANNER
     ====================================================== -->
<section class="section" style="padding-top:0; padding-bottom:0;">
  <div class="container">
    <div class="cookie-banner">
      <div class="cookie-banner-text">
        <span>&gt; cookie_tracker.sh</span> &nbsp;—&nbsp;
        Your browsing history is tracked locally.
        <?php if (!empty($recently)): ?>
          <?= count($recently) ?> recent visit<?= count($recently) > 1 ? 's' : '' ?> logged.
        <?php else: ?>
          No visits recorded yet — start exploring.
        <?php endif; ?>
      </div>
      <div class="cookie-links">
        <a href="recently-visited.php" <?= !empty($recently) ? 'class="has-data"' : '' ?>>
          &gt; Recently Visited <?= !empty($recently) ? '(' . count($recently) . ')' : '' ?>
        </a>
        <a href="most-visited.php" <?= !empty($mostVisited) ? 'class="has-data"' : '' ?>>
          &gt; Most Visited <?= !empty($mostVisited) ? '(' . count($mostVisited) . ')' : '' ?>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ======================================================
     SERVICE CARDS (10 products)
     ====================================================== -->
<section class="section" style="padding-top:2rem;">
  <div class="container">
    <div class="products-grid">

<?php foreach ($PRODUCTS as $p): ?>
<?php
  $col   = $p['color'];
  $glowVar = match($col) {
    'red'   => 'var(--glow-red)',
    'amber' => 'var(--glow-amber)',
    'cyan'  => 'var(--glow-cyan)',
    default => 'var(--glow-green)',
  };
  $featured = !empty($p['featured']);
?>
      <div class="card product-card<?= $featured ? ' product-featured' : '' ?>">
        <div class="product-card-img-wrap">
          <?php if ($featured): ?>
          <div class="product-badge">⭐ Most Popular</div>
          <?php endif; ?>
          <img src="<?= htmlspecialchars($p['img']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-card-img" loading="lazy" />
        </div>
        <div class="product-card-body">
          <div class="product-num">
            SERVICE // <?= htmlspecialchars($p['num']) ?> &nbsp;
            <span class="tag <?= htmlspecialchars($p['tag'][1]) ?>"><?= htmlspecialchars($p['tag'][0]) ?></span>
          </div>
          <h3 style="color:<?= $glowVar ?>"><?= htmlspecialchars($p['name']) ?></h3>
          <div class="divider" style="background:linear-gradient(90deg,<?= $glowVar ?>,transparent)"></div>
          <p class="product-desc"><?= htmlspecialchars($p['short']) ?></p>
          <div class="product-price" style="margin-top:auto; padding-top:1rem;">
            <span class="price-amount"><?= htmlspecialchars($p['price']) ?></span>
            <span class="price-period"><?= htmlspecialchars($p['period']) ?></span>
          </div>
          <a href="product.php?id=<?= urlencode($p['id']) ?>"
             class="btn<?= $featured ? ' btn-filled' : '' ?>"
             style="margin-top:1rem; color:<?= !$featured ? $glowVar : '' ?>; <?= !$featured ? 'border-color:'.$glowVar.';' : '' ?> font-size:0.75rem;">
            &gt; View Details
          </a>
        </div>
      </div>
<?php endforeach; ?>

    </div>
  </div>
</section>

<!-- ======================================================
     COMPARISON TABLE
     ====================================================== -->
<section class="section" style="background:var(--deep); border-top:1px solid var(--border);">
  <div class="container">
    <div class="section-heading">
      <p class="pre-title">&gt; diff nullcastle_vs_incumbents.txt</p>
      <h2>Why <span class="glow-text-green">NullCastle</span>?</h2>
      <div class="divider divider-center"></div>
    </div>
    <div style="overflow-x:auto;">
      <table class="comparison-table" style="background:var(--card); border:1px solid var(--border); border-radius:var(--radius);">
        <thead>
          <tr>
            <th>Capability</th>
            <th>NullCastle</th>
            <th>Big-4 Consulting</th>
            <th>Generic MSSP</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Dedicated account IR commander</td>
            <td><span class="check">✓ Always</span></td>
            <td><span class="cross">✗ Rotates</span></td>
            <td><span class="cross">✗ Ticket queue</span></td>
          </tr>
          <tr>
            <td>Dark web intelligence feed</td>
            <td><span class="check">✓ Proprietary TIP</span></td>
            <td><span style="color:var(--glow-amber)">◑ Licensed 3rd party</span></td>
            <td><span class="cross">✗ Not included</span></td>
          </tr>
          <tr>
            <td>Physical red team capability</td>
            <td><span class="check">✓ In-house</span></td>
            <td><span style="color:var(--glow-amber)">◑ Subcontracted</span></td>
            <td><span class="cross">✗ Not offered</span></td>
          </tr>
          <tr>
            <td>Post-quantum cryptography</td>
            <td><span class="check">✓ CastleWall OS</span></td>
            <td><span class="cross">✗ Roadmap only</span></td>
            <td><span class="cross">✗ Not offered</span></td>
          </tr>
          <tr>
            <td>Compliance framework support</td>
            <td><span class="check">✓ All major frameworks</span></td>
            <td><span style="color:var(--glow-amber)">◑ Selective</span></td>
            <td><span style="color:var(--glow-amber)">◑ Basic only</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- ======================================================
     CTA
     ====================================================== -->
<section class="section" style="border-top:1px solid var(--border); text-align:center;">
  <div class="container">
    <div class="section-heading">
      <p class="pre-title">&gt; nc_contact --priority=high</p>
      <h2>Ready to <span class="glow-text-green">Fortify</span>?</h2>
      <div class="divider divider-center"></div>
      <p style="color:var(--text-dim); max-width:500px; margin:0 auto 2rem;">
        Every engagement begins with a no-obligation threat briefing. We'll show you
        exactly where you're exposed before you spend a single dollar.
      </p>
      <a href="contact.php" class="btn btn-filled">Request a Free Threat Briefing</a>
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
