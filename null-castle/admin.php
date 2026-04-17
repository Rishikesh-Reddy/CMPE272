<?php
/**
 * NullCastle Systems — admin.php
 * Admin dashboard shell. Auth/session handled here in PHP;
 * user data is loaded client-side via GET /api/users.php.
 */
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_unset(); session_destroy();
    header('Location: login.php?reason=logout');
    exit;
}

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
      max-width: 1200px; margin: 0 auto; padding: 0 2rem;
      display: flex; align-items: center; justify-content: space-between;
      gap: 1rem; flex-wrap: wrap;
    }
    .admin-session-info {
      font-family: var(--font-mono); font-size: 0.65rem; color: var(--text-dim);
      display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;
    }
    .admin-session-info strong { color: var(--glow-green); }
    .admin-session-sep { color: var(--border); }

    .db-pill {
      display: inline-flex; align-items: center; gap: 5px;
      font-family: var(--font-mono); font-size: 0.6rem;
      padding: 2px 9px; border-radius: 2px; border: 1px solid; letter-spacing: 0.08em;
    }
    .db-pill.live  { color:var(--glow-green); border-color:rgba(0,255,157,0.35); background:rgba(0,255,157,0.06); }
    .db-pill.error { color:var(--glow-red);   border-color:rgba(255,60,90,0.35);  background:rgba(255,60,90,0.06); }
    .db-pill-dot { width:5px; height:5px; border-radius:50%; flex-shrink:0; }
    .db-pill.live  .db-pill-dot { background:var(--glow-green); box-shadow:0 0 5px var(--glow-green); animation:pulse 2s infinite; }
    .db-pill.error .db-pill-dot { background:var(--glow-red); }

    .admin-logout-btn {
      appearance: none;
      background: transparent;
      cursor: pointer;
      font-family: var(--font-mono); font-size: 0.62rem; color: var(--glow-red);
      border: 1px solid rgba(255,60,90,0.3); padding: 3px 10px; border-radius: 2px;
      letter-spacing: 0.1em; transition: background 0.2s, box-shadow 0.2s; white-space: nowrap;
    }
    .admin-logout-btn:hover { background: rgba(255,60,90,0.08); box-shadow: 0 0 8px rgba(255,60,90,0.2); color: var(--glow-red); }
    .admin-logout-btn:focus-visible {
      outline: 2px solid rgba(255,60,90,0.55);
      outline-offset: 2px;
    }

    .admin-logout-form {
      margin: 0;
    }

    .footer-logout-btn {
      appearance: none;
      background: transparent;
      border: 1px solid rgba(255,60,90,0.3);
      border-radius: 2px;
      color: var(--glow-red);
      cursor: pointer;
      font-family: var(--font-mono);
      font-size: 0.68rem;
      letter-spacing: 0.08em;
      padding: 0.35rem 0.65rem;
      transition: background 0.2s, box-shadow 0.2s;
    }
    .footer-logout-btn:hover { background: rgba(255,60,90,0.08); box-shadow: 0 0 8px rgba(255,60,90,0.2); }
    .footer-logout-btn:focus-visible {
      outline: 2px solid rgba(255,60,90,0.55);
      outline-offset: 2px;
    }

    .admin-hero { background: var(--deep); border-bottom: 1px solid var(--border); padding: 2.5rem 0 2rem; }

    .admin-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; }
    @media (max-width: 900px) { .admin-stats { grid-template-columns: repeat(2,1fr); } }

    .stat-card {
      background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
      padding: 1.2rem 1.4rem; position: relative; overflow: hidden;
    }
    .stat-card::after { content:''; position:absolute; top:0; left:0; right:0; height:2px; }
    .stat-card.s-green::after { background:var(--glow-green); box-shadow:0 0 8px var(--glow-green); }
    .stat-card.s-cyan::after  { background:var(--glow-cyan);  box-shadow:0 0 8px var(--glow-cyan); }
    .stat-card.s-red::after   { background:var(--glow-red);   box-shadow:0 0 8px var(--glow-red); }
    .stat-card.s-amber::after { background:var(--glow-amber); box-shadow:0 0 8px var(--glow-amber); }

    .stat-label { font-family:var(--font-mono); font-size:0.6rem; letter-spacing:0.14em; text-transform:uppercase; color:var(--text-dim); margin-bottom:0.5rem; }
    .stat-value { font-family:var(--font-mono); font-size:2.2rem; font-weight:700; line-height:1; }
    .stat-card.s-green .stat-value { color:var(--glow-green); }
    .stat-card.s-cyan  .stat-value { color:var(--glow-cyan); }
    .stat-card.s-red   .stat-value { color:var(--glow-red); }
    .stat-card.s-amber .stat-value { color:var(--glow-amber); }

    /* Toolbar */
    .admin-toolbar { display:flex; align-items:center; gap:0.75rem; margin-bottom:1.2rem; flex-wrap:wrap; }
    .admin-search {
      flex:1; min-width:180px; background:rgba(0,0,0,0.35); border:1px solid var(--border);
      border-radius:var(--radius); color:var(--text-bright); font-family:var(--font-mono);
      font-size:0.8rem; padding:0.55rem 0.9rem; outline:none; transition:border-color 0.2s;
    }
    .admin-search:focus { border-color:rgba(0,255,157,0.4); box-shadow:0 0 0 2px rgba(0,255,157,0.05); }
    .admin-search::placeholder { color:var(--text-dim); opacity:0.55; }

    .filter-btn {
      font-family:var(--font-mono); font-size:0.65rem; padding:0.55rem 0.9rem;
      border:1px solid var(--border); border-radius:var(--radius); background:transparent;
      color:var(--text-dim); cursor:pointer; letter-spacing:0.08em; transition:all 0.18s;
    }
    .filter-btn:hover, .filter-btn.active { border-color:rgba(0,255,157,0.4); color:var(--glow-green); background:rgba(0,255,157,0.04); }
    .filter-btn.f-suspended:hover, .filter-btn.f-suspended.active { border-color:rgba(255,60,90,0.4); color:var(--glow-red); background:rgba(255,60,90,0.04); }

    /* Table */
    .table-wrap { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; overflow-x:auto; }
    table.u-table { width:100%; border-collapse:collapse; min-width:680px; }
    .u-table thead tr { background:rgba(0,0,0,0.35); border-bottom:1px solid var(--border); }
    .u-table th { font-family:var(--font-mono); font-size:0.58rem; color:var(--text-dim); letter-spacing:0.14em; text-transform:uppercase; padding:0.8rem 1.1rem; text-align:left; white-space:nowrap; }
    .u-table td { padding:0.85rem 1.1rem; border-bottom:1px solid rgba(30,45,68,0.6); vertical-align:middle; }
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

    .clr-badge { display:inline-block; font-family:var(--font-mono); font-size:0.6rem; letter-spacing:0.1em; padding:2px 8px; border-radius:2px; border:1px solid; white-space:nowrap; }
    .st-badge { display:inline-flex; align-items:center; gap:5px; font-family:var(--font-mono); font-size:0.6rem; letter-spacing:0.08em; white-space:nowrap; }
    .st-dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
    .st-active   .st-dot { background:var(--glow-green); box-shadow:0 0 5px var(--glow-green); animation:pulse 2s infinite; }
    .st-suspended .st-dot { background:var(--glow-red); }
    .st-active    { color:var(--glow-green); }
    .st-suspended { color:var(--glow-red); }

    .no-results-row td { text-align:center; padding:3rem !important; font-family:var(--font-mono); font-size:0.75rem; color:var(--text-dim); }

    /* DB/API error block */
    .db-error-block {
      background:rgba(255,60,90,0.05); border:1px solid rgba(255,60,90,0.25);
      border-left:3px solid var(--glow-red); border-radius:var(--radius);
      padding:1.2rem 1.5rem; font-family:var(--font-mono); font-size:0.78rem;
      color:var(--glow-red); line-height:1.7; margin-bottom:2rem;
    }
    .db-error-block span { color:var(--text-dim); }

    /* Loading skeleton */
    .skeleton-row td { padding: 0.85rem 1.1rem; border-bottom: 1px solid rgba(30,45,68,0.6); }
    .skel {
      display: inline-block; height: 0.75rem; border-radius: 3px;
      background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%);
      background-size: 200% 100%;
      animation: shimmer 1.5s infinite;
    }
    @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

    /* Refresh button */
    .btn-refresh {
      font-family:var(--font-mono); font-size:0.65rem; padding:0.55rem 0.9rem;
      border:1px solid rgba(0,255,157,0.3); border-radius:var(--radius);
      background:transparent; color:var(--glow-green); cursor:pointer; letter-spacing:0.08em; transition:all 0.18s;
    }
    .btn-refresh:hover { background:rgba(0,255,157,0.06); box-shadow:0 0 8px rgba(0,255,157,0.15); }
    .btn-refresh:disabled { opacity:0.4; cursor:not-allowed; }
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
      <li><a href="allied-users.php">⬛ Allied Networks</a></li>
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
      <span id="db-status-pill" class="db-pill live">
        <span class="db-pill-dot"></span><span id="db-status-text">CONNECTING</span>
      </span>
    </div>
    <form method="post" action="admin.php" class="admin-logout-form">
      <button type="submit" name="logout" value="1" class="admin-logout-btn" aria-label="Log out of admin session">[LOGOUT]</button>
    </form>
  </div>
</div>

<!-- Hero -->
<section class="admin-hero">
  <div class="container">
    <p style="font-family:var(--font-mono);font-size:0.7rem;color:var(--glow-red);letter-spacing:0.18em;text-transform:uppercase;margin-bottom:0.5rem;">
      &gt; GET /api/users.php
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

    <!-- API error block (hidden by default, shown on fetch failure) -->
    <div id="api-error-block" class="db-error-block" style="display:none;"></div>

    <!-- Stat cards — values populated by JS -->
    <div class="admin-stats">
      <div class="stat-card s-green">
        <div class="stat-label">Total Users</div>
        <div class="stat-value" id="stat-total">—</div>
      </div>
      <div class="stat-card s-cyan">
        <div class="stat-label">Active</div>
        <div class="stat-value" id="stat-active">—</div>
      </div>
      <div class="stat-card s-red">
        <div class="stat-label">Suspended</div>
        <div class="stat-value" id="stat-suspended">—</div>
      </div>
      <div class="stat-card s-amber">
        <div class="stat-label">Clearance Levels</div>
        <div class="stat-value" id="stat-clearances">—</div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="admin-toolbar">
      <input type="text" id="user-search" class="admin-search" placeholder="> search name, role, department..." autocomplete="off" />
      <button class="filter-btn active"      onclick="setFilter('all', this)">All</button>
      <button class="filter-btn"             onclick="setFilter('active', this)">Active</button>
      <button class="filter-btn f-suspended" onclick="setFilter('suspended', this)">Suspended</button>
      <button class="btn-refresh" id="btn-refresh" onclick="loadUsers()" title="Reload from API">↻ Refresh</button>
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
          <!-- Skeleton rows shown while the API fetch is in flight -->
          <tr class="skeleton-row"><td><span class="skel" style="width:44px"></span></td><td><span class="skel" style="width:110px"></span></td><td><span class="skel" style="width:160px"></span></td><td><span class="skel" style="width:100px"></span></td><td><span class="skel" style="width:56px"></span></td><td><span class="skel" style="width:70px"></span></td><td><span class="skel" style="width:80px"></span></td><td><span class="skel" style="width:80px"></span></td></tr>
          <tr class="skeleton-row"><td><span class="skel" style="width:44px"></span></td><td><span class="skel" style="width:130px"></span></td><td><span class="skel" style="width:140px"></span></td><td><span class="skel" style="width:90px"></span></td><td><span class="skel" style="width:56px"></span></td><td><span class="skel" style="width:70px"></span></td><td><span class="skel" style="width:80px"></span></td><td><span class="skel" style="width:80px"></span></td></tr>
          <tr class="skeleton-row"><td><span class="skel" style="width:44px"></span></td><td><span class="skel" style="width:95px"></span></td><td><span class="skel" style="width:155px"></span></td><td><span class="skel" style="width:115px"></span></td><td><span class="skel" style="width:56px"></span></td><td><span class="skel" style="width:70px"></span></td><td><span class="skel" style="width:80px"></span></td><td><span class="skel" style="width:80px"></span></td></tr>
        </tbody>
      </table>
    </div>

    <p style="font-family:var(--font-mono);font-size:0.62rem;color:var(--text-dim);margin-top:0.9rem;text-align:right;" id="footer-meta">
      Loading… &nbsp;•&nbsp; via /api/users.php
    </p>

  </div>
</section>

<footer>
  <div class="container">
    <div class="footer-bottom" style="justify-content:space-between;flex-wrap:wrap;">
      <span>&copy; 2025 NullCastle Systems, Inc.</span>
      <span style="color:var(--glow-red);font-family:var(--font-mono);font-size:0.65rem;letter-spacing:0.14em;">[ADMIN SESSION ACTIVE]</span>
      <form method="post" action="admin.php" class="admin-logout-form">
        <button type="submit" name="logout" value="1" class="footer-logout-btn" aria-label="Log out">Logout -&gt;</button>
      </form>
    </div>
  </div>
</footer>

<script>
// ─────────────────────────────────────────────────────────────────────────────
//  Clearance colour maps (mirrors original PHP)
// ─────────────────────────────────────────────────────────────────────────────
var CLR_COLOR = {
  OMEGA: 'var(--glow-green)',
  ALPHA: 'var(--glow-cyan)',
  SIGMA: 'var(--glow-amber)',
  DELTA: 'var(--glow-red)',
  GHOST: 'var(--text-dim)',
};
var CLR_BG = {
  OMEGA: 'rgba(0,255,157,0.08)',
  ALPHA: 'rgba(0,212,255,0.08)',
  SIGMA: 'rgba(255,179,0,0.08)',
  DELTA: 'rgba(255,60,90,0.08)',
  GHOST: 'rgba(96,117,144,0.08)',
};

// ─────────────────────────────────────────────────────────────────────────────
//  State
// ─────────────────────────────────────────────────────────────────────────────
var allUsers      = [];   // full list from API
var currentFilter = 'all';

// ─────────────────────────────────────────────────────────────────────────────
//  Fetch users from the API
// ─────────────────────────────────────────────────────────────────────────────
function loadUsers() {
  var btn = document.getElementById('btn-refresh');
  if (btn) btn.disabled = true;

  // Show skeleton while loading
  setDbPill('connecting');
  document.getElementById('api-error-block').style.display = 'none';
  renderSkeleton();

  fetch('api/users.php', { credentials: 'same-origin' })
    .then(function(res) {
      if (res.status === 401) { window.location.href = 'login.php?reason=session'; throw null; }
      if (!res.ok) return res.json().then(function(d){ throw new Error(d.error || 'HTTP ' + res.status); });
      return res.json();
    })
    .then(function(data) {
      allUsers = data.users || [];
      updateStats(allUsers, data.meta);
      setDbPill('live');
      applyFilters(document.getElementById('user-search').value.toLowerCase().trim());
      document.getElementById('footer-meta').textContent =
        data.meta.total + ' records\u00a0\u2022\u00a0Fetched: ' + new Date().toLocaleTimeString() + ' \u2022 /api/users.php';
    })
    .catch(function(err) {
      if (!err) return; // redirect already triggered
      setDbPill('error');
      var el = document.getElementById('api-error-block');
      el.innerHTML = '[API_ERROR] ' + escHtml(err.message) +
        '<br><span>Check that the API endpoint is reachable and the database is online.</span>';
      el.style.display = '';
      renderEmpty('[API_ERROR] Cannot load users — fetch from /api/users.php failed.');
      document.getElementById('footer-meta').textContent = 'DB unavailable \u2022 /api/users.php';
    })
    .finally(function() {
      if (btn) btn.disabled = false;
    });
}

// ─────────────────────────────────────────────────────────────────────────────
//  Stats cards
// ─────────────────────────────────────────────────────────────────────────────
function updateStats(users, meta) {
  var active    = users.filter(function(u){ return u.status === 'ACTIVE'; }).length;
  var suspended = users.length - active;
  var clearances = new Set(users.map(function(u){ return u.clearance; })).size;

  document.getElementById('stat-total').textContent      = meta ? meta.total : users.length;
  document.getElementById('stat-active').textContent     = active;
  document.getElementById('stat-suspended').textContent  = suspended;
  document.getElementById('stat-clearances').textContent = clearances;
}

// ─────────────────────────────────────────────────────────────────────────────
//  Render helpers
// ─────────────────────────────────────────────────────────────────────────────
function setDbPill(state) {
  var pill = document.getElementById('db-status-pill');
  var text = document.getElementById('db-status-text');
  pill.className = 'db-pill ' + (state === 'live' ? 'live' : 'error');
  text.textContent = state === 'live' ? 'LIVE DB' : state === 'connecting' ? 'CONNECTING' : 'DB ERROR';
}

function renderSkeleton() {
  var tbody = document.getElementById('user-tbody');
  var html = '';
  for (var i = 0; i < 4; i++) {
    html += '<tr class="skeleton-row">' +
      '<td><span class="skel" style="width:44px"></span></td>' +
      '<td><span class="skel" style="width:' + (90 + i * 20) + 'px"></span></td>' +
      '<td><span class="skel" style="width:150px"></span></td>' +
      '<td><span class="skel" style="width:100px"></span></td>' +
      '<td><span class="skel" style="width:56px"></span></td>' +
      '<td><span class="skel" style="width:70px"></span></td>' +
      '<td><span class="skel" style="width:80px"></span></td>' +
      '<td><span class="skel" style="width:80px"></span></td>' +
      '</tr>';
  }
  tbody.innerHTML = html;
}

function renderEmpty(msg) {
  document.getElementById('user-tbody').innerHTML =
    '<tr><td colspan="8" class="no-results-row" style="color:var(--glow-red)">' + escHtml(msg) + '</td></tr>';
}

function renderRows(users) {
  var tbody = document.getElementById('user-tbody');
  if (!users.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="no-results-row">[NULL] No users match that query.</td></tr>';
    return;
  }
  var html = '';
  users.forEach(function(u) {
    var status  = u.status;
    var clr     = u.clearance;
    var color   = CLR_COLOR[clr]  || 'var(--text-dim)';
    var bg      = CLR_BG[clr]     || 'rgba(96,117,144,0.08)';
    var sCls    = status === 'ACTIVE' ? 'st-active' : 'st-suspended';
    var idStr   = 'NC-' + String(u.id).padStart(3, '0');

    html += '<tr' +
      ' data-name="'   + escAttr((u.name       || '').toLowerCase()) + '"' +
      ' data-status="' + escAttr(status.toLowerCase()) + '"' +
      ' data-role="'   + escAttr((u.role       || '').toLowerCase()) + '"' +
      ' data-dept="'   + escAttr((u.department || '').toLowerCase()) + '">' +
      '<td class="td-id">' + escHtml(idStr) + '</td>' +
      '<td class="td-name">' + escHtml(u.name || '') + '</td>' +
      '<td class="td-email"><a href="mailto:' + escAttr(u.email || '') + '">' + escHtml(u.email || '') + '</a></td>' +
      '<td><div class="td-role">' + escHtml(u.role || '') + '</div>' +
           '<div class="td-dept">' + escHtml(u.department || '') + '</div></td>' +
      '<td><span class="clr-badge" style="color:' + color + ';border-color:' + color + ';background:' + bg + '">' +
           escHtml(clr) + '</span></td>' +
      '<td><span class="st-badge ' + sCls + '"><span class="st-dot"></span>' + escHtml(status) + '</span></td>' +
      '<td class="td-date">' + escHtml(u.joined     || '—') + '</td>' +
      '<td class="td-date">' + escHtml(u.last_login || '—') + '</td>' +
      '</tr>';
  });
  tbody.innerHTML = html;
}

// ─────────────────────────────────────────────────────────────────────────────
//  Filtering (client-side, no extra round-trip)
// ─────────────────────────────────────────────────────────────────────────────
function setFilter(f, btn) {
  currentFilter = f;
  document.querySelectorAll('.filter-btn').forEach(function(b){ b.classList.remove('active'); });
  btn.classList.add('active');
  applyFilters(document.getElementById('user-search').value.toLowerCase().trim());
}

function applyFilters(q) {
  var filtered = allUsers.filter(function(u) {
    var statusOk = currentFilter === 'all' || u.status.toLowerCase() === currentFilter;
    var searchOk = !q ||
      (u.name       || '').toLowerCase().includes(q) ||
      (u.role       || '').toLowerCase().includes(q) ||
      (u.department || '').toLowerCase().includes(q);
    return statusOk && searchOk;
  });
  renderRows(filtered);
}

// ─────────────────────────────────────────────────────────────────────────────
//  XSS helpers
// ─────────────────────────────────────────────────────────────────────────────
function escHtml(s) {
  return String(s)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function escAttr(s) { return escHtml(s); }

// ─────────────────────────────────────────────────────────────────────────────
//  Event listeners
// ─────────────────────────────────────────────────────────────────────────────
document.getElementById('user-search').addEventListener('input', function() {
  applyFilters(this.value.toLowerCase().trim());
});

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

// ─────────────────────────────────────────────────────────────────────────────
//  Boot — fetch users immediately on page load
// ─────────────────────────────────────────────────────────────────────────────
loadUsers();
</script>
</body>
</html>