<?php
/**
 * NullCastle Systems — allied-users.php
 * Fetches user lists from allied companies via cURL and displays them
 * alongside our own local users.
 *
 * HOW TO ADD / REMOVE PARTNER COMPANIES:
 *   Edit the $ALLIED_ENDPOINTS array below.
 *   Each entry needs:
 *     'label'   => Display name shown in the UI
 *     'url'     => Full URL to the partner's JSON API endpoint
 *     'color'   => accent colour key: 'cyan' | 'amber' | 'red' | 'green'
 *     'timeout' => cURL timeout in seconds (optional, default 8)
 */


/* =========================================================
 *  ★  CONFIGURE YOUR PARTNER ENDPOINTS HERE  ★
 * ========================================================= */
$ALLIED_ENDPOINTS = [
    [
        'label'   => 'Paradox Systems',
        'id'      => 'paradox',
        'url'     => 'https://paradoxsystems.vikramadithya.me/api/users.php',
        'color'   => 'cyan',
        'timeout' => 8,
    ],
    [
        'label'   => 'Null Castle',
        'id'      => 'nullcastle',
        'url'     => 'https://nullcastle.rishikeshaluguvelli.me/api/users.php',
        'color'   => 'amber',
        'timeout' => 8,
    ]
    // Add more partners here — just copy a block above.
];
/* ========================================================= */


/* ----------------------------------------------------------
 *  cURL helper — fetches one endpoint, returns decoded array
 *  or an error array on failure.
 * ---------------------------------------------------------- */
function fetch_allied_users(array $endpoint): array {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $endpoint['url'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $endpoint['timeout'] ?? 8,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        // Remove the next line in production if you have valid TLS certs:
        // CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['__error' => 'cURL error: ' . $curlErr, '__code' => 0];
    }
    if ($httpCode !== 200) {
        return ['__error' => 'HTTP ' . $httpCode, '__code' => $httpCode];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return ['__error' => 'Invalid JSON response', '__code' => $httpCode];
    }
    if (empty($decoded['users']) || !is_array($decoded['users'])) {
        return ['__error' => 'No "users" key in response', '__code' => $httpCode];
    }

    return $decoded;   // success — has 'company', 'users', 'total', etc.
}


/* ----------------------------------------------------------
 *  Fetch all endpoints (sequential; swap to parallel if needed)
 * ---------------------------------------------------------- */
$alliedData = [];
foreach ($ALLIED_ENDPOINTS as $ep) {
    $result = fetch_allied_users($ep);
    $alliedData[] = [
        'meta'  => $ep,
        'data'  => $result,
        'error' => $result['__error'] ?? null,
    ];
}

$display_name = htmlspecialchars($_SESSION['nc_admin_display'] ?? $_SESSION['nc_admin_user'] ?? 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Allied Networks — NullCastle Systems</title>
  <meta name="robots" content="noindex, nofollow" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Cinzel:wght@400;700;900&family=Raleway:ital,wght@0,300;0,400;0,600;1,300&display=swap" />

  <style>
    body { padding-top: 64px; }

    /* ── Top banner ── */
    .admin-banner {
      background: rgba(0,180,255,0.07);
      border-bottom: 1px solid rgba(0,180,255,0.2);
      padding: 5px 0; text-align: center;
      font-family: var(--font-mono); font-size: 0.6rem;
      color: var(--glow-cyan); letter-spacing: 0.18em; text-transform: uppercase;
      animation: bannerPulse 3s ease-in-out infinite;
    }
    @keyframes bannerPulse { 0%,100%{opacity:1} 50%{opacity:0.5} }

    /* ── Session bar ── */
    .admin-session-bar { background:rgba(0,0,0,0.35); border-bottom:1px solid var(--border); padding:6px 0; }
    .admin-session-inner {
      max-width:1200px; margin:0 auto; padding:0 2rem;
      display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;
    }
    .admin-session-info { font-family:var(--font-mono); font-size:0.65rem; color:var(--text-dim); display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap; }
    .admin-session-info strong { color:var(--glow-cyan); }
    .admin-session-sep { color:var(--border); }

    /* ── Accent colour map ── */
    :root {
      --col-cyan:  var(--glow-cyan,  #00b4ff);
      --col-amber: var(--glow-amber, #ffb800);
      --col-red:   var(--glow-red,   #ff3c5a);
      --col-green: var(--glow-green, #00ff9d);
    }

    /* ── Hero ── */
    .allied-hero { background:var(--deep); border-bottom:1px solid var(--border); padding:2.5rem 0 2rem; }

    /* ── Status pills ── */
    .db-pill {
      display:inline-flex; align-items:center; gap:5px;
      font-family:var(--font-mono); font-size:0.6rem;
      padding:2px 9px; border-radius:2px; border:1px solid; letter-spacing:0.08em;
    }
    .db-pill.live  { color:var(--glow-green); border-color:rgba(0,255,157,0.35); background:rgba(0,255,157,0.06); }
    .db-pill.error { color:var(--glow-red);   border-color:rgba(255,60,90,0.35);  background:rgba(255,60,90,0.06); }
    .db-pill-dot   { width:5px; height:5px; border-radius:50%; flex-shrink:0; }
    .db-pill.live .db-pill-dot  { background:var(--glow-green); box-shadow:0 0 5px var(--glow-green); animation:pulse 2s infinite; }
    .db-pill.error .db-pill-dot { background:var(--glow-red); }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.3} }

    /* ── Company section wrapper ── */
    .company-section { margin-bottom: 3.5rem; }
    .company-header {
      display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;
      margin-bottom:1.2rem; padding-bottom:0.9rem;
      border-bottom:1px solid var(--border);
    }
    .company-title-group { display:flex; align-items:center; gap:0.9rem; }
    .company-badge {
      font-family:var(--font-mono); font-size:0.6rem; letter-spacing:0.14em; text-transform:uppercase;
      padding:3px 10px; border-radius:3px; border:1px solid; background:rgba(0,0,0,0.2);
    }
    .company-badge.cyan  { color:var(--col-cyan);  border-color:rgba(0,180,255,0.35); background:rgba(0,180,255,0.07); }
    .company-badge.amber { color:var(--col-amber); border-color:rgba(255,184,0,0.35); background:rgba(255,184,0,0.07); }
    .company-badge.red   { color:var(--col-red);   border-color:rgba(255,60,90,0.35); background:rgba(255,60,90,0.07); }
    .company-badge.green { color:var(--col-green); border-color:rgba(0,255,157,0.35); background:rgba(0,255,157,0.07); }

    .company-name {
      font-family:var(--font-mono); font-size:1rem; font-weight:700; letter-spacing:0.05em;
    }
    .company-name.cyan  { color:var(--col-cyan); }
    .company-name.amber { color:var(--col-amber); }
    .company-name.red   { color:var(--col-red); }
    .company-name.green { color:var(--col-green); }

    .company-count {
      font-family:var(--font-mono); font-size:0.7rem; color:var(--text-dim); letter-spacing:0.06em;
    }

    /* ── Error block ── */
    .error-block {
      background:rgba(255,60,90,0.05); border:1px solid rgba(255,60,90,0.25);
      border-radius:var(--radius); padding:1.2rem 1.5rem;
      font-family:var(--font-mono); font-size:0.72rem; color:var(--glow-red);
      display:flex; align-items:flex-start; gap:0.75rem;
    }
    .error-block .error-icon { font-size:1.1rem; flex-shrink:0; margin-top:1px; }
    .error-url  { color:var(--text-dim); font-size:0.65rem; margin-top:0.3rem; word-break:break-all; }

    /* ── Table ── */
    .table-wrap { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; overflow-x:auto; }
    table.u-table { width:100%; border-collapse:collapse; min-width:580px; }
    .u-table thead tr { background:rgba(0,0,0,0.35); border-bottom:1px solid var(--border); }
    .u-table th { font-family:var(--font-mono); font-size:0.57rem; color:var(--text-dim); letter-spacing:0.14em; text-transform:uppercase; padding:0.75rem 1rem; text-align:left; white-space:nowrap; }
    .u-table td { padding:0.8rem 1rem; border-bottom:1px solid rgba(30,45,68,0.6); vertical-align:middle; }
    .u-table tbody tr { transition:background 0.12s; }
    .u-table tbody tr:hover { background:rgba(255,255,255,0.02); }
    .u-table tbody tr:last-child td { border-bottom:none; }

    .td-id    { font-family:var(--font-mono); font-size:0.63rem; color:var(--text-dim); white-space:nowrap; }
    .td-name  { font-family:var(--font-mono); font-size:0.8rem; color:var(--text-bright); font-weight:600; }
    .td-email a { color:var(--text-dim); font-family:var(--font-mono); font-size:0.72rem; text-decoration:none; transition:color 0.15s; }
    .td-email a:hover { color:var(--glow-green); }
    .td-date  { font-family:var(--font-mono); font-size:0.68rem; color:var(--text-dim); white-space:nowrap; }

    /* Role badge */
    .role-badge {
      display:inline-block; font-family:var(--font-mono); font-size:0.58rem;
      letter-spacing:0.08em; text-transform:uppercase;
      padding:2px 7px; border-radius:2px; border:1px solid var(--border);
      background:rgba(255,255,255,0.03); color:var(--text-dim);
    }

    /* Status badge */
    .st-badge { display:inline-flex; align-items:center; gap:5px; font-family:var(--font-mono); font-size:0.65rem; letter-spacing:0.08em; white-space:nowrap; }
    .st-dot   { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
    .st-active   .st-dot { background:var(--glow-green); box-shadow:0 0 5px var(--glow-green); }
    .st-active   { color:var(--glow-green); }
    .st-inactive .st-dot { background:var(--text-dim); }
    .st-inactive { color:var(--text-dim); }

    /* ── Toolbar ── */
    .allied-toolbar { display:flex; align-items:center; gap:0.75rem; margin-bottom:2rem; flex-wrap:wrap; }
    .allied-search {
      flex:1; min-width:200px;
      background:rgba(0,0,0,0.35); border:1px solid var(--border);
      border-radius:var(--radius); color:var(--text-bright);
      font-family:var(--font-mono); font-size:0.8rem;
      padding:0.55rem 0.9rem; outline:none; transition:border-color 0.2s;
    }
    .allied-search:focus { border-color:rgba(0,180,255,0.4); box-shadow:0 0 0 2px rgba(0,180,255,0.05); }
    .allied-search::placeholder { color:var(--text-dim); opacity:0.55; }

    .filter-tab {
      font-family:var(--font-mono); font-size:0.63rem; padding:0.5rem 1rem;
      border:1px solid var(--border); border-radius:var(--radius);
      background:transparent; color:var(--text-dim);
      cursor:pointer; letter-spacing:0.08em; transition:all 0.18s; white-space:nowrap;
    }
    .filter-tab:hover  { border-color:rgba(0,180,255,0.4); color:var(--glow-cyan); }
    .filter-tab.active { border-color:rgba(0,180,255,0.5); color:var(--glow-cyan); background:rgba(0,180,255,0.06); }

    /* ── Summary stat strip ── */
    .stat-strip { display:flex; gap:1rem; margin-bottom:2.5rem; flex-wrap:wrap; }
    .stat-chip {
      flex:1; min-width:120px; background:var(--card);
      border:1px solid var(--border); border-radius:var(--radius);
      padding:0.9rem 1.2rem; position:relative; overflow:hidden;
    }
    .stat-chip::after { content:''; position:absolute; top:0; left:0; right:0; height:2px; }
    .stat-chip.s-cyan::after   { background:var(--col-cyan);  box-shadow:0 0 8px var(--col-cyan); }
    .stat-chip.s-amber::after  { background:var(--col-amber); box-shadow:0 0 8px var(--col-amber); }
    .stat-chip.s-green::after  { background:var(--col-green); box-shadow:0 0 8px var(--col-green); }
    .stat-chip.s-red::after    { background:var(--col-red);   box-shadow:0 0 8px var(--col-red); }
    .chip-label { font-family:var(--font-mono); font-size:0.55rem; letter-spacing:0.14em; text-transform:uppercase; color:var(--text-dim); margin-bottom:0.35rem; }
    .chip-value { font-family:var(--font-mono); font-size:1.7rem; font-weight:700; line-height:1; }
    .stat-chip.s-cyan  .chip-value { color:var(--col-cyan); }
    .stat-chip.s-amber .chip-value { color:var(--col-amber); }
    .stat-chip.s-green .chip-value { color:var(--col-green); }
    .stat-chip.s-red   .chip-value { color:var(--col-red); }

    /* ── No-results row ── */
    .no-results-row { text-align:center; color:var(--text-dim); font-family:var(--font-mono); font-size:0.75rem; padding:2rem 1rem !important; }

    /* ── Footer ── */
    .page-footer { border-top:1px solid var(--border); padding:1.5rem 0; }
    .footer-meta { font-family:var(--font-mono); font-size:0.65rem; color:var(--text-dim); letter-spacing:0.07em; }
    .footer-meta a { color:var(--text-dim); }
    .footer-meta a:hover { color:var(--glow-cyan); }

    /* Logout button */
    .admin-logout-btn {
      appearance:none; background:transparent; cursor:pointer;
      font-family:var(--font-mono); font-size:0.62rem; color:var(--glow-red);
      border:1px solid rgba(255,60,90,0.3); padding:3px 10px; border-radius:2px;
      letter-spacing:0.1em; transition:background 0.2s, box-shadow 0.2s; white-space:nowrap;
    }
    .admin-logout-btn:hover { background:rgba(255,60,90,0.08); box-shadow:0 0 8px rgba(255,60,90,0.2); }

    /* Divider */
    .nc-divider { height:1px; background:linear-gradient(90deg,var(--glow-cyan),transparent); border:none; margin:0.5rem 0 1.2rem; }

    /* Section spacing */
    .main-section { padding:2.5rem 0 4rem; }
  </style>
</head>
<body>

<!-- ── Nav ── -->
<nav>
  <div class="nav-inner">
    <a href="index.html" class="nav-logo">
      <div class="nav-logo-icon">NC</div>
      <span class="nav-logo-text">Null<span>Castle</span></span>
    </a>
    <ul class="nav-links">
      <li><a href="index.html"         data-num="00">Home</a></li>
      <li><a href="about.html"         data-num="01">About</a></li>
      <li><a href="products.php"       data-num="02">Services</a></li>
      <li><a href="news.html"          data-num="03">News</a></li>
      <li><a href="contact.php"        data-num="04">Contact</a></li>
      <li><a href="admin.php"          data-num="05" style="color:var(--glow-red)">⬛ Admin</a></li>
    </ul>
    <div class="nav-status"><div class="nav-status-dot"></div>SYS:ONLINE</div>
    <div class="nav-hamburger" role="button" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>

<!-- ── Restricted banner ── -->
<div class="admin-banner">⬛ ALLIED NETWORK INTELLIGENCE // CLEARANCE REQUIRED // RESTRICTED ACCESS ⬛</div>

<!-- ── Session bar ── -->
<div class="admin-session-bar">
  <div class="admin-session-inner">
    <div class="admin-session-info">
      <span>&gt; operator: <strong><?= $display_name ?></strong></span>
      <span class="admin-session-sep">|</span>
      <span>allied_network_fetch.php</span>
      <span class="admin-session-sep">|</span>
      <span>Fetched: <?= date('H:i:s') ?></span>
      <span class="admin-session-sep">|</span>
      <span>
        <?php
          $ok  = count(array_filter($alliedData, fn($d) => !$d['error']));
          $err = count($alliedData) - $ok;
        ?>
        <span class="db-pill <?= $err === 0 ? 'live' : ($ok === 0 ? 'error' : 'live') ?>">
          <span class="db-pill-dot"></span>
          <?= $ok ?>/<?= count($alliedData) ?> NODES LIVE
        </span>
      </span>
    </div>
    <form method="POST" action="admin.php" style="margin:0">
      <button name="logout" class="admin-logout-btn">⬛ LOGOUT</button>
    </form>
  </div>
</div>

<!-- ── Hero ── -->
<section class="allied-hero">
  <div class="container fade-in">
    <p class="pre-title" style="font-family:var(--font-mono); font-size:0.72rem; color:var(--glow-cyan); letter-spacing:0.2em; text-transform:uppercase; margin-bottom:0.5rem;">
      &gt; curl --allied-nodes --output users.json
    </p>
    <h1 style="font-size:2.2rem;">Allied <span style="color:var(--glow-cyan)">Network</span> Roster</h1>
    <hr class="nc-divider" style="max-width:340px;" />
    <p style="color:var(--text-dim); max-width:560px; font-size:0.95rem; margin-top:0.5rem;">
      Live user records pulled from <?= count($ALLIED_ENDPOINTS) ?> allied node<?= count($ALLIED_ENDPOINTS) !== 1 ? 's' : '' ?>
      via cURL. All data is fetched server-side at page load — no client credentials are exposed.
    </p>
  </div>
</section>

<!-- ── Main content ── -->
<section class="main-section">
  <div class="container">

    <!-- ── Global search + company filter tabs ── -->
    <div class="allied-toolbar">
      <input id="global-search" type="search" class="allied-search"
             placeholder="&gt; search across all companies…" autocomplete="off" />
      <button class="filter-tab active" data-company="all">All Companies</button>
      <?php foreach ($alliedData as $entry): ?>
      <button class="filter-tab" data-company="<?= htmlspecialchars($entry['meta']['id']) ?>">
        <?= htmlspecialchars($entry['meta']['label']) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- ── Summary stat strip ── -->
    <?php
      $totalUsers  = 0;
      $totalActive = 0;
      foreach ($alliedData as $entry) {
          if (!$entry['error']) {
              $users = $entry['data']['users'] ?? [];
              $totalUsers += count($users);
              foreach ($users as $u) {
                  if (strtolower($u['status'] ?? '') === 'active') $totalActive++;
              }
          }
      }
    ?>
    <div class="stat-strip">
      <div class="stat-chip s-cyan">
        <div class="chip-label">Allied Nodes</div>
        <div class="chip-value"><?= count($ALLIED_ENDPOINTS) ?></div>
      </div>
      <div class="stat-chip s-green">
        <div class="chip-label">Total Users</div>
        <div class="chip-value" id="stat-total"><?= $totalUsers ?></div>
      </div>
      <div class="stat-chip s-amber">
        <div class="chip-label">Active</div>
        <div class="chip-value" id="stat-active"><?= $totalActive ?></div>
      </div>
      <div class="stat-chip s-red">
        <div class="chip-label">Nodes Down</div>
        <div class="chip-value"><?= $err ?></div>
      </div>
    </div>

    <!-- ── Per-company sections ── -->
    <?php foreach ($alliedData as $entry):
        $meta  = $entry['meta'];
        $color = htmlspecialchars($meta['color']);
        $cid   = htmlspecialchars($meta['id']);
        $label = htmlspecialchars($meta['label']);
        $users = $entry['data']['users'] ?? [];
        $total = $entry['data']['total'] ?? count($users);
    ?>
    <div class="company-section" data-company-section="<?= $cid ?>">

      <div class="company-header">
        <div class="company-title-group">
          <span class="company-badge <?= $color ?>">ALLIED NODE</span>
          <span class="company-name <?= $color ?>"><?= $label ?></span>
        </div>
        <div style="display:flex; align-items:center; gap:0.9rem; flex-wrap:wrap;">
          <?php if ($entry['error']): ?>
            <span class="db-pill error"><span class="db-pill-dot"></span>OFFLINE</span>
          <?php else: ?>
            <span class="db-pill live"><span class="db-pill-dot"></span>LIVE</span>
            <span class="company-count"><?= $total ?> records fetched</span>
            <span class="company-count" style="font-size:0.6rem; opacity:0.55;">
              <?= htmlspecialchars($meta['url']) ?>
            </span>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($entry['error']): ?>
        <div class="error-block">
          <span class="error-icon">⚠</span>
          <div>
            <div>[FETCH_ERROR] <?= htmlspecialchars($entry['error']) ?></div>
            <div class="error-url">&gt; endpoint: <?= htmlspecialchars($meta['url']) ?></div>
            <div style="margin-top:0.4rem; color:var(--text-dim);">
              Check that the remote host is online and the endpoint returns valid JSON.
            </div>
          </div>
        </div>

      <?php else: ?>
        <div class="table-wrap">
          <table class="u-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody class="company-tbody" data-company-body="<?= $cid ?>">
              <?php if (empty($users)): ?>
              <tr><td colspan="6" class="no-results-row">[NULL] No users returned from this node.</td></tr>
              <?php else: foreach ($users as $u):
                  $statusVal  = strtolower($u['status'] ?? 'inactive');
                  $statusCls  = ($statusVal === 'active') ? 'st-active' : 'st-inactive';
                  $statusText = strtoupper($u['status'] ?? 'UNKNOWN');
              ?>
              <tr
                data-name="<?= htmlspecialchars(strtolower($u['name']  ?? '')) ?>"
                data-role="<?= htmlspecialchars(strtolower($u['role']  ?? '')) ?>"
                data-company="<?= $cid ?>"
              >
                <td class="td-id"><?= (int)($u['id'] ?? 0) ?></td>
                <td class="td-name"><?= htmlspecialchars($u['name']  ?? '—') ?></td>
                <td class="td-email">
                  <?php if (!empty($u['email'])): ?>
                    <a href="mailto:<?= htmlspecialchars($u['email']) ?>"><?= htmlspecialchars($u['email']) ?></a>
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td><span class="role-badge"><?= htmlspecialchars($u['role'] ?? '—') ?></span></td>
                <td class="td-date"><?= htmlspecialchars($u['joined'] ?? '—') ?></td>
                <td><span class="st-badge <?= $statusCls ?>"><span class="st-dot"></span><?= htmlspecialchars($statusText) ?></span></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

    </div><!-- /company-section -->
    <?php endforeach; ?>

    <?php if (empty($ALLIED_ENDPOINTS)): ?>
    <div class="error-block">
      <span class="error-icon">ℹ</span>
      <div>
        No allied endpoints configured. Add partner URLs to the <code>$ALLIED_ENDPOINTS</code>
        array at the top of <code>allied-users.php</code>.
      </div>
    </div>
    <?php endif; ?>

  </div><!-- /container -->
</section>

<!-- ── Footer ── -->
<footer class="page-footer">
  <div class="container">
    <p class="footer-meta">
      © <?= date('Y') ?> NullCastle Systems &nbsp;|&nbsp;
      <a href="admin.php">← Admin Dashboard</a> &nbsp;|&nbsp;
      <a href="products.php">Services</a> &nbsp;|&nbsp;
      <span id="footer-timestamp">Page generated: <?= date('Y-m-d H:i:s') ?></span>
    </p>
  </div>
</footer>

<script src="js/main.js"></script>
<script>
/* ================================================================
   Allied Users — client-side search & company tab filter
   ================================================================ */

var currentCompany = 'all';

/* ── Company tab filter ── */
document.querySelectorAll('.filter-tab').forEach(function(btn) {
  btn.addEventListener('click', function() {
    currentCompany = this.dataset.company;
    document.querySelectorAll('.filter-tab').forEach(function(b){ b.classList.remove('active'); });
    this.classList.add('active');

    document.querySelectorAll('[data-company-section]').forEach(function(section) {
      var cid = section.dataset.companySection;
      section.style.display = (currentCompany === 'all' || currentCompany === cid) ? '' : 'none';
    });

    /* Re-apply search after tab switch */
    applySearch(document.getElementById('global-search').value.toLowerCase().trim());
    updateGlobalCount();
  });
});

/* ── Global search ── */
document.getElementById('global-search').addEventListener('input', function() {
  applySearch(this.value.toLowerCase().trim());
  updateGlobalCount();
});

function applySearch(q) {
  document.querySelectorAll('[data-company-section]').forEach(function(section) {
    var cid = section.dataset.companySection;
    /* Skip hidden sections (tab filter) */
    if (currentCompany !== 'all' && currentCompany !== cid) return;

    var tbody = section.querySelector('.company-tbody');
    if (!tbody) return;

    var rows    = tbody.querySelectorAll('tr[data-name]');
    var visible = 0;

    rows.forEach(function(row) {
      var nameMatch = row.dataset.name.includes(q);
      var roleMatch = row.dataset.role.includes(q);
      var show      = !q || nameMatch || roleMatch;
      row.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    /* Show no-results placeholder if needed */
    var placeholder = tbody.querySelector('.no-results-search');
    if (q && visible === 0 && rows.length > 0) {
      if (!placeholder) {
        var tr = document.createElement('tr');
        tr.className = 'no-results-search';
        tr.innerHTML = '<td colspan="6" class="no-results-row">[NULL] No users match "' + escHtml(q) + '" in this node.</td>';
        tbody.appendChild(tr);
      } else {
        placeholder.style.display = '';
      }
    } else if (placeholder) {
      placeholder.style.display = 'none';
    }
  });
}

function updateGlobalCount() {
  var visible = document.querySelectorAll('[data-company-section]:not([style*="display: none"]) .company-tbody tr[data-name]:not([style*="display: none"])').length;
  document.getElementById('stat-total').textContent = visible;
  var active  = document.querySelectorAll('[data-company-section]:not([style*="display: none"]) .company-tbody tr[data-name]:not([style*="display: none"]) .st-active').length;
  document.getElementById('stat-active').textContent = active;
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── Nav scroll ── */
var nav = document.querySelector('nav');
if (nav) window.addEventListener('scroll', function(){ nav.classList.toggle('scrolled', window.scrollY > 30); });

/* ── Hamburger ── */
var hamburger = document.querySelector('.nav-hamburger');
var navLinks  = document.querySelector('.nav-links');
if (hamburger && navLinks) {
  hamburger.addEventListener('click', function() {
    navLinks.classList.toggle('open');
    var spans = hamburger.querySelectorAll('span');
    if (navLinks.classList.contains('open')) {
      spans[0].style.transform='rotate(45deg) translate(5px,5px)';
      spans[1].style.opacity='0';
      spans[2].style.transform='rotate(-45deg) translate(5px,-5px)';
    } else { spans.forEach(function(s){ s.style.transform=''; s.style.opacity=''; }); }
  });
}
</script>
</body>
</html>