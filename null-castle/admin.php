<?php
session_start();


if (empty($_SESSION['nc_admin_authenticated'])) {
    header('Location: login.php');
    exit;
}

$timeout = 30 * 60;
if (isset($_SESSION['nc_last_active']) && (time() - $_SESSION['nc_last_active']) > $timeout) {
    session_unset(); session_destroy();
    header('Location: login.php?reason=timeout');
    exit;
}
$_SESSION['nc_last_active'] = time();

if (isset($_GET['logout'])) {
    session_unset(); session_destroy();
    header('Location: login.php?reason=logout');
    exit;
}

function get_pdo(): ?PDO {
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '5432';
    $name = getenv('DB_NAME') ?: 'nullcastle';
    $user = getenv('DB_USER') ?: 'postgres';
    $pass = getenv('DB_PASS') ?: 'iamgrooooooooot';
    if (!$host || !$name || !$user) return null;
    try {
        return new PDO(
            "pgsql:host={$host};port={$port};dbname={$name};sslmode=require",
            $user, $pass,
            [ PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
              PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC ]
        );
    } catch (PDOException $e) {
        return null;
    }
}


$users     = [];
$db_error  = '';
$db_ok     = false;

$pdo = get_pdo();

if (!$pdo) {
    $db_error = 'Cannot connect to PostgreSQL. '
              . 'Verify DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS are set in Render Environment Variables.';
} else {
    try {
        $stmt  = $pdo->query("SELECT * FROM site_users ORDER BY id ASC");
        $users = $stmt->fetchAll();
        $db_ok = true;

    } catch (PDOException $e) {
        $db_error = 'Query failed: ' . $e->getMessage();
    }
}

$total     = count($users);
$active    = count(array_filter($users, fn($u) => strtoupper($u['status']) === 'ACTIVE'));
$suspended = $total - $active;
$clr_set   = array_unique(array_column($users, 'clearance'));

$clr_color = [
    'OMEGA' => 'var(--glow-green)',
    'ALPHA' => 'var(--glow-cyan)',
    'SIGMA' => 'var(--glow-amber)',
    'DELTA' => 'var(--glow-red)',
    'GHOST' => 'var(--text-dim)',
];
$clr_bg = [
    'OMEGA' => 'rgba(0,255,157,0.08)',
    'ALPHA' => 'rgba(0,212,255,0.08)',
    'SIGMA' => 'rgba(255,179,0,0.08)',
    'DELTA' => 'rgba(255,60,90,0.08)',
    'GHOST' => 'rgba(96,117,144,0.08)',
];

$display_name = htmlspecialchars($_SESSION['nc_admin_display'] ?? $_SESSION['nc_admin_user'] ?? 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Portal — NullCastle Systems</title>
  <meta name="robots" content="noindex, nofollow" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Cinzel:wght@400;700;900&family=Raleway:ital,wght@0,300;0,400;0,600;1,300&display=swap" />
  <style>
    body { padding-top: 64px; }

    .admin-banner {
      background: rgba(255,60,90,0.07);
      border-bottom: 1px solid rgba(255,60,90,0.2);
      padding: 5px 0;
      text-align: center;
      font-family: var(--font-mono);
      font-size: 0.6rem;
      color: var(--glow-red);
      letter-spacing: 0.18em;
      text-transform: uppercase;
      animation: bannerPulse 3s ease-in-out infinite;
    }
    @keyframes bannerPulse { 0%,100%{opacity:1} 50%{opacity:0.55} }

    .admin-session-bar {
      background: rgba(0,0,0,0.35);
      border-bottom: 1px solid var(--border);
      padding: 6px 0;
    }
    .admin-session-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .admin-session-info {
      font-family: var(--font-mono);
      font-size: 0.65rem;
      color: var(--text-dim);
      display: flex;
      align-items: center;
      gap: 0.75rem;
      flex-wrap: wrap;
    }
    .admin-session-info strong { color: var(--glow-green); }
    .admin-session-sep { color: var(--border); }

    .db-pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-family: var(--font-mono);
      font-size: 0.6rem;
      padding: 2px 9px;
      border-radius: 2px;
      border: 1px solid;
      letter-spacing: 0.08em;
    }
    .db-pill.live  { color:var(--glow-green); border-color:rgba(0,255,157,0.35); background:rgba(0,255,157,0.06); }
    .db-pill.error { color:var(--glow-red);   border-color:rgba(255,60,90,0.35);  background:rgba(255,60,90,0.06); }
    .db-pill-dot {
      width:5px; height:5px; border-radius:50%; flex-shrink:0;
    }
    .db-pill.live  .db-pill-dot { background:var(--glow-green); box-shadow:0 0 5px var(--glow-green); animation:pulse 2s infinite; }
    .db-pill.error .db-pill-dot { background:var(--glow-red); }

    .admin-logout-btn {
      font-family: var(--font-mono);
      font-size: 0.62rem;
      color: var(--glow-red);
      border: 1px solid rgba(255,60,90,0.3);
      padding: 3px 10px;
      border-radius: 2px;
      text-decoration: none;
      letter-spacing: 0.1em;
      transition: background 0.2s, box-shadow 0.2s;
      white-space: nowrap;
    }
    .admin-logout-btn:hover {
      background: rgba(255,60,90,0.08);
      box-shadow: 0 0 8px rgba(255,60,90,0.2);
      color: var(--glow-red);
    }

    .admin-hero {
      background: var(--deep);
      border-bottom: 1px solid var(--border);
      padding: 2.5rem 0 2rem;
    }

    .admin-stats {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
      margin-bottom: 2rem;
    }
    @media (max-width: 900px) { .admin-stats { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 480px) { .admin-stats { grid-template-columns: repeat(2,1fr); } }

    .stat-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.2rem 1.4rem;
      position: relative;
      overflow: hidden;
    }
    .stat-card::after {
      content:'';
      position:absolute;
      top:0; left:0; right:0;
      height:2px;
    }
    .stat-card.s-green::after { background:var(--glow-green); box-shadow:0 0 8px var(--glow-green); }
    .stat-card.s-cyan::after  { background:var(--glow-cyan);  box-shadow:0 0 8px var(--glow-cyan); }
    .stat-card.s-red::after   { background:var(--glow-red);   box-shadow:0 0 8px var(--glow-red); }
    .stat-card.s-amber::after { background:var(--glow-amber); box-shadow:0 0 8px var(--glow-amber); }

    .stat-label {
      font-family: var(--font-mono);
      font-size: 0.6rem;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: var(--text-dim);
      margin-bottom: 0.5rem;
    }
    .stat-value {
      font-family: var(--font-mono);
      font-size: 2.2rem;
      font-weight: 700;
      line-height: 1;
    }
    .stat-card.s-green .stat-value { color:var(--glow-green); }
    .stat-card.s-cyan  .stat-value { color:var(--glow-cyan); }
    .stat-card.s-red   .stat-value { color:var(--glow-red); }
    .stat-card.s-amber .stat-value { color:var(--glow-amber); }

    /* ── Toolbar ───────────────────────────────────────────── */
    .admin-toolbar {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 1.2rem;
      flex-wrap: wrap;
    }
    .admin-search {
      flex: 1;
      min-width: 180px;
      background: rgba(0,0,0,0.35);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      color: var(--text-bright);
      font-family: var(--font-mono);
      font-size: 0.8rem;
      padding: 0.55rem 0.9rem;
      outline: none;
      transition: border-color 0.2s;
    }
    .admin-search:focus { border-color:rgba(0,255,157,0.4); box-shadow:0 0 0 2px rgba(0,255,157,0.05); }
    .admin-search::placeholder { color:var(--text-dim); opacity:0.55; }

    .filter-btn {
      font-family: var(--font-mono);
      font-size: 0.65rem;
      padding: 0.55rem 0.9rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      background: transparent;
      color: var(--text-dim);
      cursor: pointer;
      letter-spacing: 0.08em;
      transition: all 0.18s;
    }
    .filter-btn:hover, .filter-btn.active {
      border-color: rgba(0,255,157,0.4);
      color: var(--glow-green);
      background: rgba(0,255,157,0.04);
    }
    .filter-btn.f-suspended:hover, .filter-btn.f-suspended.active {
      border-color: rgba(255,60,90,0.4);
      color: var(--glow-red);
      background: rgba(255,60,90,0.04);
    }

    /* ── Table ─────────────────────────────────────────────── */
    .table-wrap {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      overflow: hidden;
      overflow-x: auto;
    }
    table.u-table { width:100%; border-collapse:collapse; min-width:680px; }
    .u-table thead tr { background:rgba(0,0,0,0.35); border-bottom:1px solid var(--border); }
    .u-table th {
      font-family:var(--font-mono); font-size:0.58rem; color:var(--text-dim);
      letter-spacing:0.14em; text-transform:uppercase;
      padding:0.8rem 1.1rem; text-align:left; white-space:nowrap;
    }
    .u-table td {
      padding: 0.85rem 1.1rem;
      border-bottom: 1px solid rgba(30,45,68,0.6);
      vertical-align: middle;
    }
    .u-table tbody tr { transition:background 0.12s; }
    .u-table tbody tr:hover { background:rgba(0,255,157,0.022); }
    .u-table tbody tr:last-child td { border-bottom:none; }
    .u-table tbody tr.row-hidden { display:none; }

    .td-id   { font-family:var(--font-mono); font-size:0.65rem; color:var(--text-dim); white-space:nowrap; }
    .td-name { font-family:var(--font-serif); font-size:0.86rem; color:var(--text-bright); font-weight:700; white-space:nowrap; }
    .td-email a { font-family:var(--font-mono); font-size:0.68rem; color:var(--glow-cyan); text-decoration:none; }
    .td-email a:hover { color:var(--glow-green); }
    .td-role { font-size:0.8rem; color:var(--text); }
    .td-dept { font-family:var(--font-mono); font-size:0.6rem; color:var(--text-dim); margin-top:2px; }
    .td-date { font-family:var(--font-mono); font-size:0.65rem; color:var(--text-dim); white-space:nowrap; }

    .clr-badge {
      display:inline-block; font-family:var(--font-mono); font-size:0.6rem;
      letter-spacing:0.1em; padding:2px 8px; border-radius:2px; border:1px solid;
      white-space:nowrap;
    }
    .st-badge {
      display:inline-flex; align-items:center; gap:5px;
      font-family:var(--font-mono); font-size:0.6rem;
      letter-spacing:0.08em; white-space:nowrap;
    }
    .st-dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
    .st-active  .st-dot { background:var(--glow-green); box-shadow:0 0 5px var(--glow-green); animation:pulse 2s infinite; }
    .st-suspended .st-dot { background:var(--glow-red); }
    .st-active   { color:var(--glow-green); }
    .st-suspended { color:var(--glow-red); }

    .no-results-row td {
      text-align:center; padding:3rem !important;
      font-family:var(--font-mono); font-size:0.75rem; color:var(--text-dim);
    }

    /* ── DB error block ────────────────────────────────────── */
    .db-error-block {
      background:rgba(255,60,90,0.05);
      border:1px solid rgba(255,60,90,0.25);
      border-left:3px solid var(--glow-red);
      border-radius:var(--radius);
      padding:1.2rem 1.5rem;
      font-family:var(--font-mono);
      font-size:0.78rem;
      color:var(--glow-red);
      line-height:1.7;
      margin-bottom:2rem;
    }
    .db-error-block span { color:var(--text-dim); }
  </style>
</head>
<body>

<!-- Restricted banner -->
<div class="admin-banner">⚠ &nbsp; RESTRICTED AREA — AUTHORISED PERSONNEL ONLY — ALL ACTIVITY IS MONITORED &nbsp; ⚠</div>

<!-- Nav -->
<nav>
  <div class="nav-inner">
    <a href="index.html" class="nav-logo">
      <div class="nav-logo-icon">NC</div>
      <span class="nav-logo-text">Null<span>Castle</span></span>
    </a>
    <ul class="nav-links">
      <li><a href="index.html"    data-num="00">Home</a></li>
      <li><a href="about.html"    data-num="01">About</a></li>
      <li><a href="products.html" data-num="02">Services</a></li>
      <li><a href="news.html"     data-num="03">News</a></li>
      <li><a href="contact.php"   data-num="04">Contact</a></li>
      <li><a href="admin.php"     data-num="05" class="active" style="color:var(--glow-red)">Admin</a></li>
    </ul>
    <div class="nav-status" style="color:var(--glow-red); border-color:rgba(255,60,90,0.35);">
      <div class="nav-status-dot" style="background:var(--glow-red);"></div>
      ADMIN:ACTIVE
    </div>
    <div class="nav-hamburger" role="button" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>

<!-- Session bar -->
<div class="admin-session-bar">
  <div class="admin-session-inner">
    <div class="admin-session-info">
      <span>Session: <strong><?php echo $display_name; ?></strong></span>
      <span class="admin-session-sep">|</span>
      <span>Logged in: <strong><?php echo date('H:i:s'); ?></strong></span>
      <span class="admin-session-sep">|</span>
      <span>Timeout: <strong>30 min</strong></span>
      <span class="admin-session-sep">|</span>
      <?php if ($db_ok): ?>
        <span class="db-pill live"><span class="db-pill-dot"></span>LIVE DB</span>
      <?php else: ?>
        <span class="db-pill error"><span class="db-pill-dot"></span>DB ERROR</span>
      <?php endif; ?>
    </div>
    <a href="admin.php?logout=1" class="admin-logout-btn">[LOGOUT]</a>
  </div>
</div>

<!-- Hero -->
<section class="admin-hero">
  <div class="container">
    <p style="font-family:var(--font-mono);font-size:0.7rem;color:var(--glow-red);letter-spacing:0.18em;text-transform:uppercase;margin-bottom:0.5rem;">
      &gt; sudo cat /var/nullcastle/users/registry.db
    </p>
    <h1>User <span class="glow-text-green">Registry</span></h1>
    <div class="divider" style="background:linear-gradient(90deg,var(--glow-red),transparent);margin:0.9rem 0 0.7rem;"></div>
    <p style="color:var(--text-dim);font-size:0.88rem;">
      Registered site users — clearance levels, roles &amp; access status.
      Viewing as: <span style="color:var(--glow-green);font-family:var(--font-mono);"><?php echo $display_name; ?></span>
    </p>
  </div>
</section>

<!-- Content -->
<section class="section" style="padding-top:2.5rem;">
  <div class="container">

    <?php if ($db_error): ?>
    <div class="db-error-block">
      [DB_ERROR] <?php echo htmlspecialchars($db_error); ?><br>
      <span>Check that DB_HOST, DB_PORT, DB_NAME, DB_USER and DB_PASS are set in your Render service's Environment Variables, and that the schema has been run.</span>
    </div>
    <?php endif; ?>

    <!-- Stat cards -->
    <div class="admin-stats">
      <div class="stat-card s-green">
        <div class="stat-label">Total Users</div>
        <div class="stat-value"><?php echo $db_ok ? $total : '—'; ?></div>
      </div>
      <div class="stat-card s-cyan">
        <div class="stat-label">Active</div>
        <div class="stat-value"><?php echo $db_ok ? $active : '—'; ?></div>
      </div>
      <div class="stat-card s-red">
        <div class="stat-label">Suspended</div>
        <div class="stat-value"><?php echo $db_ok ? $suspended : '—'; ?></div>
      </div>
      <div class="stat-card s-amber">
        <div class="stat-label">Clearance Levels</div>
        <div class="stat-value"><?php echo $db_ok ? count($clr_set) : '—'; ?></div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="admin-toolbar">
      <input type="text" id="user-search" class="admin-search" placeholder="> search name, role, department..." autocomplete="off" />
      <button class="filter-btn active"     onclick="setFilter('all', this)">All</button>
      <button class="filter-btn"            onclick="setFilter('active', this)">Active</button>
      <button class="filter-btn f-suspended" onclick="setFilter('suspended', this)">Suspended</button>
    </div>

    <!-- Table -->
    <div class="table-wrap">
      <table class="u-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role / Dept</th>
            <th>Clearance</th>
            <th>Status</th>
            <th>Joined</th>
            <th>Last Login</th>
          </tr>
        </thead>
        <tbody id="user-tbody">
          <?php if (!$db_ok): ?>
          <tr><td colspan="8" class="no-results-row" style="color:var(--glow-red)">
            [DB_ERROR] Cannot load users — database connection failed.
          </td></tr>
          <?php elseif (empty($users)): ?>
          <tr><td colspan="8" class="no-results-row">
            [EMPTY] No users in the database yet. Run schema.sql to seed records.
          </td></tr>
          <?php else: ?>
            <?php foreach ($users as $u):
              $status  = strtoupper($u['status']);
              $clr     = strtoupper($u['clearance']);
              $color   = $clr_color[$clr]  ?? 'var(--text-dim)';
              $bg      = $clr_bg[$clr]     ?? 'rgba(96,117,144,0.08)';
              $s_cls   = ($status === 'ACTIVE') ? 'st-active' : 'st-suspended';
            ?>
            <tr data-name="<?php echo htmlspecialchars(strtolower($u['name'])); ?>"
                data-status="<?php echo htmlspecialchars(strtolower($status)); ?>"
                data-role="<?php echo htmlspecialchars(strtolower($u['role'])); ?>"
                data-dept="<?php echo htmlspecialchars(strtolower($u['department'])); ?>">
              <td class="td-id">NC-<?php echo str_pad((int)$u['id'], 3, '0', STR_PAD_LEFT); ?></td>
              <td class="td-name"><?php echo htmlspecialchars($u['name']); ?></td>
              <td class="td-email"><a href="mailto:<?php echo htmlspecialchars($u['email']); ?>"><?php echo htmlspecialchars($u['email']); ?></a></td>
              <td>
                <div class="td-role"><?php echo htmlspecialchars($u['role']); ?></div>
                <div class="td-dept"><?php echo htmlspecialchars($u['department']); ?></div>
              </td>
              <td>
                <span class="clr-badge" style="color:<?php echo $color; ?>;border-color:<?php echo $color; ?>;background:<?php echo $bg; ?>">
                  <?php echo htmlspecialchars($clr); ?>
                </span>
              </td>
              <td>
                <span class="st-badge <?php echo $s_cls; ?>">
                  <span class="st-dot"></span><?php echo htmlspecialchars($status); ?>
                </span>
              </td>
              <td class="td-date"><?php echo htmlspecialchars($u['joined'] ?? '—'); ?></td>
              <td class="td-date"><?php echo htmlspecialchars($u['last_login'] ?? '—'); ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          <tr id="no-results-row" class="no-results-row" style="display:none;">
            <td colspan="8">[NULL] No users match that query.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <p style="font-family:var(--font-mono);font-size:0.62rem;color:var(--text-dim);margin-top:0.9rem;text-align:right;">
      <?php echo $db_ok ? "{$total} records" : 'DB unavailable'; ?> &nbsp;•&nbsp;
      Refreshed: <?php echo date('Y-m-d H:i:s'); ?> UTC
    </p>

  </div>
</section>

<footer>
  <div class="container">
    <div class="footer-bottom" style="justify-content:space-between;flex-wrap:wrap;">
      <span>&copy; 2025 NullCastle Systems, Inc.</span>
      <span style="color:var(--glow-red);font-family:var(--font-mono);font-size:0.65rem;letter-spacing:0.14em;">[ADMIN SESSION ACTIVE]</span>
      <a href="admin.php?logout=1" style="font-family:var(--font-mono);font-size:0.68rem;color:var(--glow-red);">Logout →</a>
    </div>
  </div>
</footer>

<script>
var currentFilter = 'all';

document.getElementById('user-search').addEventListener('input', function() {
  applyFilters(this.value.toLowerCase().trim());
});

function setFilter(f, btn) {
  currentFilter = f;
  document.querySelectorAll('.filter-btn').forEach(function(b){ b.classList.remove('active'); });
  btn.classList.add('active');
  applyFilters(document.getElementById('user-search').value.toLowerCase().trim());
}

function applyFilters(q) {
  var rows = document.querySelectorAll('#user-tbody tr[data-name]');
  var vis  = 0;
  rows.forEach(function(r) {
    var match  = !q || r.dataset.name.includes(q) || r.dataset.role.includes(q) || r.dataset.dept.includes(q);
    var statOk = currentFilter === 'all' || r.dataset.status === currentFilter;
    if (match && statOk) { r.classList.remove('row-hidden'); vis++; }
    else                 { r.classList.add('row-hidden'); }
  });
  document.getElementById('no-results-row').style.display = (vis === 0 && rows.length > 0) ? '' : 'none';
}

/* Nav scroll */
var nav = document.querySelector('nav');
if (nav) window.addEventListener('scroll', function(){ nav.classList.toggle('scrolled', window.scrollY > 30); });

/* Hamburger */
var hamburger = document.querySelector('.nav-hamburger');
var navLinks  = document.querySelector('.nav-links');
if (hamburger && navLinks) {
  hamburger.addEventListener('click', function() {
    navLinks.classList.toggle('open');
    var spans = hamburger.querySelectorAll('span');
    if (navLinks.classList.contains('open')) {
      spans[0].style.transform = 'rotate(45deg) translate(5px,5px)';
      spans[1].style.opacity   = '0';
      spans[2].style.transform = 'rotate(-45deg) translate(5px,-5px)';
    } else { spans.forEach(function(s){ s.style.transform=''; s.style.opacity=''; }); }
  });
}
</script>
</body>
</html>
