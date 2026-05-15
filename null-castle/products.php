<?php
declare(strict_types=1);

require_once __DIR__ . '/products-catalogue.php';

$MP_PRODUCT = '__site__';
?>
<!DOCTYPE html>
<html lang="en" class="page-products">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Services — NullCastle Systems</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Cinzel:wght@400;700;900&family=Raleway:ital,wght@0,300;0,400;0,600;1,300&display=swap" />
  <style>
    .cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem; margin-top: 2rem; }
    .cat-card {
      display: block; text-decoration: none; color: inherit;
      border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden;
      background: rgba(0,0,0,0.35); transition: border-color 0.2s, transform 0.15s;
    }
    .cat-card:hover { border-color: rgba(0, 255, 65, 0.35); transform: translateY(-2px); }
    .cat-card img { width: 100%; height: 140px; object-fit: cover; display: block; filter: brightness(0.75); }
    .cat-card-body { padding: 1rem 1.1rem 1.25rem; }
    .cat-card-body h3 { margin: 0 0 0.35rem; font-size: 1rem; color: var(--text-bright); }
    .cat-card-body p { margin: 0; font-size: 0.82rem; color: var(--text-dim); line-height: 1.45; }
    .cat-meta { font-family: var(--font-mono); font-size: 0.62rem; letter-spacing: 0.12em; color: var(--text-dim); margin-bottom: 0.35rem; }
    .page-intro { color: var(--text-dim); max-width: 640px; font-size: 0.95rem; line-height: 1.55; }
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
      <li><a href="index.html" data-num="00">Home</a></li>
      <li><a href="about.html" data-num="01">About</a></li>
      <li><a href="products.php" data-num="02" class="active">Services</a></li>
      <li><a href="news.html" data-num="03">News</a></li>
      <li><a href="contact.php" data-num="04">Contact</a></li>
      <li><a href="allied-users.php">⬛ Allied Networks</a></li>
      <li><a href="login.php" data-num="05" style="color:var(--glow-red)">⬛ Admin</a></li>
    </ul>
    <div class="nav-status"><div class="nav-status-dot"></div>SYS:ONLINE</div>
    <div class="nav-hamburger" role="button" aria-label="Toggle menu"><span></span><span></span><span></span></div>
  </div>
</nav>

<section class="page-hero section" style="padding-bottom:2rem;">
  <div class="container">
    <p style="font-family:var(--font-mono);font-size:0.72rem;color:var(--glow-cyan);letter-spacing:0.15em;margin-bottom:0.75rem;">
      &gt; catalogue --all
    </p>
    <h1>Services</h1>
    <div class="divider" style="background:linear-gradient(90deg,var(--glow-green),transparent)"></div>
    <p class="page-intro">
      Deep links from the enterprise marketplace land here with your session token in the URL hash.
      Each service page reports the visit back to the hub so your cross-store history stays accurate.
    </p>

    <div class="cat-grid">
      <?php foreach ($PRODUCTS as $pid => $p): ?>
        <a class="cat-card" href="product.php?id=<?= htmlspecialchars(rawurlencode($pid), ENT_QUOTES, 'UTF-8') ?>">
          <img src="<?= htmlspecialchars($p['img']) ?>" alt="" loading="lazy" />
          <div class="cat-card-body">
            <div class="cat-meta"><?= htmlspecialchars($p['num']) ?> · <?= htmlspecialchars($p['tag'][0]) ?></div>
            <h3><?= htmlspecialchars($p['name']) ?></h3>
            <p><?= htmlspecialchars($p['short']) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <p style="margin-top:2.5rem;font-family:var(--font-mono);font-size:0.72rem;color:var(--text-dim);">
      <a href="recently-visited.php" style="color:var(--glow-cyan);">&gt; Recently visited</a>
      &nbsp;·&nbsp;
      <a href="most-visited.php" style="color:var(--glow-amber);">&gt; Most visited</a>
    </p>
  </div>
</section>

<footer style="border-top:1px solid var(--border); padding:2rem 0; text-align:center;">
  <div class="container">
    <p style="font-family:var(--font-mono); font-size:0.7rem; color:var(--text-dim); letter-spacing:0.1em;">
      © <?= date('Y') ?> NullCastle Systems
    </p>
  </div>
</footer>

<script src="js/main.js"></script>
<?php
require_once __DIR__ . '/marketplace-config.php';
echo mp_script_tag(MP_SITE_ID, $MP_PRODUCT);
?>
</body>
</html>
