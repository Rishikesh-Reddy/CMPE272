<?php
declare(strict_types=1);

require_once __DIR__ . '/products-catalogue.php';

$id = isset($_GET['id']) ? (string) $_GET['id'] : '';
if ($id === '' || !isset($PRODUCTS[$id])) {
    header('Location: products.php');
    exit;
}

$p = $PRODUCTS[$id];
$MP_PRODUCT = $id;

$glowVar = match ($p['color']) {
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
    .pd-hero {
      position: relative; min-height: 42vh; display: flex; align-items: flex-end; padding-bottom: 2.5rem;
      border-bottom: 1px solid var(--border);
    }
    .pd-hero-bg {
      position: absolute; inset: 0;
      background: linear-gradient(to top, rgba(5,8,12,0.95) 0%, transparent 55%), url('<?= htmlspecialchars($p['hero_img']) ?>') center/cover no-repeat;
      z-index: 0;
    }
    .pd-hero .container { position: relative; z-index: 1; }
    .pd-num { font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.15em; color: var(--text-dim); margin-bottom: 0.5rem; }
    .pd-h1 { margin: 0 0 0.75rem; font-size: clamp(1.6rem, 4vw, 2.2rem); color: <?= $glowVar ?>; }
    .pd-short { color: var(--text-dim); max-width: 52rem; font-size: 0.98rem; line-height: 1.55; margin: 0; }
    .pd-price { margin-top: 1rem; font-family: var(--font-mono); font-size: 0.85rem; color: var(--text-bright); }
    .pd-grid { display: grid; grid-template-columns: 1fr; gap: 2rem; margin-top: 2rem; }
    @media (min-width: 900px) { .pd-grid { grid-template-columns: 1.2fr 1fr; } }
    .pd-desc { color: var(--text); line-height: 1.75; font-size: 0.95rem; }
    .pd-features { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 0.65rem; }
    .pd-features li {
      padding-left: 1.1rem; position: relative; font-size: 0.9rem; color: var(--text-dim); line-height: 1.45;
    }
    .pd-features li::before {
      content: '›'; position: absolute; left: 0; color: <?= $glowVar ?>; font-weight: 700;
    }
    .pd-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2rem; }
    .related { margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border); }
    .related h2 { font-size: 0.85rem; letter-spacing: 0.12em; color: var(--text-dim); margin: 0 0 1rem; }
    .related-row { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .related-row a {
      font-family: var(--font-mono); font-size: 0.72rem; padding: 0.35rem 0.65rem;
      border: 1px solid var(--border); border-radius: 6px; color: var(--glow-cyan); text-decoration: none;
    }
    .related-row a:hover { border-color: <?= $glowVar ?>; color: <?= $glowVar ?>; }
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

<div class="pd-hero">
  <div class="pd-hero-bg" aria-hidden="true"></div>
  <div class="container">
    <div class="pd-num">SERVICE // <?= htmlspecialchars($p['num']) ?> · <?= htmlspecialchars($p['tag'][0]) ?></div>
    <h1 class="pd-h1"><?= htmlspecialchars($p['name']) ?></h1>
    <p class="pd-short"><?= htmlspecialchars($p['short']) ?></p>
    <div class="pd-price"><?= htmlspecialchars($p['price']) ?> <?= htmlspecialchars($p['period']) ?></div>
  </div>
</div>

<section class="section" style="padding-top:2rem;">
  <div class="container">
    <div class="pd-grid">
      <div class="pd-desc"><?= nl2br(htmlspecialchars($p['desc'])) ?></div>
      <div>
        <h2 style="font-size:0.8rem;letter-spacing:0.12em;color:var(--text-dim);margin:0 0 1rem;">CAPABILITIES</h2>
        <ul class="pd-features">
          <?php foreach ($p['features'] as $feat): ?>
            <li><?= htmlspecialchars($feat) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="pd-actions">
      <a href="contact.php" class="btn btn-filled"><?= htmlspecialchars($p['cta']) ?></a>
      <a href="products.php" class="btn" style="border-color:var(--border);color:var(--text-dim);">← All services</a>
    </div>

    <div class="related">
      <h2>MORE SERVICES</h2>
      <div class="related-row">
        <?php foreach ($PRODUCTS as $oid => $op): ?>
          <?php if ($oid === $id) {
              continue;
          } ?>
          <a href="product.php?id=<?= htmlspecialchars(rawurlencode($oid), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($op['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<footer style="border-top:1px solid var(--border); padding:2rem 0; text-align:center; margin-top:3rem;">
  <div class="container">
    <p style="font-family:var(--font-mono); font-size:0.7rem; color:var(--text-dim); letter-spacing:0.1em;">
      © <?= date('Y') ?> NullCastle Systems · <a href="recently-visited.php" style="color:var(--glow-cyan);">Recently visited</a>
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
