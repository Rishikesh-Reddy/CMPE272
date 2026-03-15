<?php
session_start();

if (!empty($_SESSION['nc_admin_authenticated'])) {
    header('Location: admin.php');
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
            [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
              PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC ]
        );
    } catch (PDOException $e) {
        return null;
    }
}

$error   = '';
$attempt = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attempt  = true;
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Session-based rate limiting
    if (!isset($_SESSION['nc_fails']))       $_SESSION['nc_fails']      = 0;
    if (!isset($_SESSION['nc_locked_until'])) $_SESSION['nc_locked_until'] = 0;

    $now = time();

    if ($now < (int)$_SESSION['nc_locked_until']) {
        $wait  = (int)$_SESSION['nc_locked_until'] - $now;
        $error = "TOO_MANY_ATTEMPTS: Access locked. Retry in {$wait}s.";

    } else {
        $pdo = get_pdo();

        if (!$pdo) {
            $error = 'Internal error: Unable to authenticate at this time. Please try again later.';
        } else {
            $stmt = $pdo->prepare(
                "SELECT password_hash, display_name, is_active
                   FROM admin_users
                  WHERE username = :u
                  LIMIT 1"
            );
            $stmt->execute([':u' => $username]);
            $row = $stmt->fetch();

            $hash_ok = $row && password_verify($password, $row['password_hash']);
            $active  = $row && $row['is_active'];

            if ($hash_ok && $active) {
                // ✅ Success
                session_regenerate_id(true);
                $_SESSION['nc_admin_authenticated'] = true;
                $_SESSION['nc_admin_user']          = $username;
                $_SESSION['nc_admin_display']       = $row['display_name'] ?: $username;
                $_SESSION['nc_last_active']         = $now;
                $_SESSION['nc_fails']               = 0;

                // Record last_login timestamp
                $upd = $pdo->prepare(
                    "UPDATE admin_users SET last_login = NOW() WHERE username = :u"
                );
                $upd->execute([':u' => $username]);

                header('Location: admin.php');
                exit;

            } elseif ($row && $hash_ok && !$active) {
                $error = 'AUTH_DENIED: Account is disabled. Contact the system owner.';

            } else {
                $_SESSION['nc_fails']++;
                if ((int)$_SESSION['nc_fails'] >= 5) {
                    $_SESSION['nc_locked_until'] = $now + 30;
                    $_SESSION['nc_fails']        = 0;
                    $error = 'AUTH_LOCKOUT: Too many failed attempts. Locked for 30 seconds.';
                } else {
                    $left  = 5 - (int)$_SESSION['nc_fails'];
                    $error = "AUTH_FAILED: Invalid credentials. {$left} attempt(s) remaining.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="page-login">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Secure Access — NullCastle Systems</title>
  <meta name="robots" content="noindex, nofollow" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Cinzel:wght@400;700;900&family=Raleway:ital,wght@0,300;0,400;0,600;1,300&display=swap" />
  <style>
    .login-wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      position: relative;
      overflow: hidden;
    }
    .login-wrap::before {
      content: '';
      position: fixed; inset: 0;
      background-image:
        linear-gradient(rgba(0,255,157,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,255,157,0.04) 1px, transparent 1px);
      background-size: 40px 40px;
      pointer-events: none;
      animation: gridDrift 20s linear infinite;
    }
    @keyframes gridDrift {
      0%   { background-position: 0 0, 0 0; }
      100% { background-position: 40px 40px, 40px 40px; }
    }
    .login-wrap::after {
      content: '';
      position: fixed; inset: 0;
      background: radial-gradient(ellipse 60% 50% at 50% 50%, rgba(0,255,157,0.06) 0%, transparent 70%);
      pointer-events: none;
    }
    .login-panel {
      position: relative; z-index: 1;
      width: 100%; max-width: 460px;
      background: var(--card);
      border: 1px solid var(--border);
      border-top: 2px solid var(--glow-green);
      border-radius: var(--radius);
      box-shadow: 0 0 60px rgba(0,255,157,0.08), 0 20px 60px rgba(0,0,0,0.6);
      animation: panelIn 0.5s cubic-bezier(0.22,1,0.36,1) both;
    }
    @keyframes panelIn {
      from { opacity:0; transform:translateY(20px) scale(0.98); }
      to   { opacity:1; transform:translateY(0)    scale(1); }
    }
    .login-header {
      background: rgba(0,0,0,0.4);
      border-bottom: 1px solid var(--border);
      padding: 0.85rem 1.5rem;
      display: flex; align-items: center; justify-content: space-between;
      border-radius: var(--radius) var(--radius) 0 0;
    }
    .login-header-dots { display:flex; gap:6px; }
    .login-header-dots span { width:10px; height:10px; border-radius:50%; background:var(--border); }
    .login-header-title { font-family:var(--font-mono); font-size:0.7rem; color:var(--text-dim); letter-spacing:0.12em; }
    .login-header-badge {
      font-family:var(--font-mono); font-size:0.6rem; color:var(--glow-green);
      border:1px solid rgba(0,255,157,0.4); padding:2px 8px; border-radius:2px; letter-spacing:0.1em;
      animation: badgePulse 2.5s ease-in-out infinite;
    }
    @keyframes badgePulse { 0%,100%{opacity:1} 50%{opacity:0.5} }
    .login-body { padding: 2.5rem 2rem 2rem; }
    .login-logo { text-align:center; margin-bottom:2rem; }
    .login-logo-icon {
      width:60px; height:60px; margin:0 auto 0.75rem;
      background:rgba(0,255,157,0.06); border:1px solid rgba(0,255,157,0.25); border-radius:50%;
      display:flex; align-items:center; justify-content:center;
      font-family:var(--font-mono); font-size:1.2rem; font-weight:bold;
      color:var(--glow-green); text-shadow:0 0 20px var(--glow-green);
      box-shadow:0 0 30px rgba(0,255,157,0.1);
    }
    .login-logo-text { font-family:var(--font-serif); font-size:1.3rem; color:var(--text-bright); letter-spacing:0.08em; }
    .login-logo-sub  { font-family:var(--font-mono); font-size:0.65rem; color:var(--text-dim); letter-spacing:0.2em; text-transform:uppercase; margin-top:0.25rem; }
    .login-alert {
      font-family:var(--font-mono); font-size:0.78rem; line-height:1.5;
      padding:0.85rem 1rem; border-radius:var(--radius); margin-bottom:1.5rem;
      border-left-width:3px; border-left-style:solid;
    }
    .login-alert-error  { background:rgba(255,60,90,0.06);  border:1px solid rgba(255,60,90,0.3);  border-left-color:var(--glow-red);  color:var(--glow-red); }
    .login-alert-db     { background:rgba(255,179,0,0.06);  border:1px solid rgba(255,179,0,0.3);  border-left-color:var(--glow-amber); color:var(--glow-amber); }
    .login-alert-info   { background:rgba(0,212,255,0.05);  border:1px solid rgba(0,212,255,0.2);  border-left-color:var(--glow-cyan);  color:var(--text-dim); font-size:0.72rem; }
    .login-form .form-group { margin-bottom:1.2rem; }
    .login-form label {
      display:block; font-family:var(--font-mono); font-size:0.68rem;
      color:var(--text-dim); letter-spacing:0.15em; text-transform:uppercase; margin-bottom:0.4rem;
    }
    .login-form input {
      width:100%; background:rgba(0,0,0,0.4); border:1px solid var(--border);
      border-radius:var(--radius); color:var(--text-bright);
      font-family:var(--font-mono); font-size:0.9rem; padding:0.75rem 1rem;
      transition:border-color 0.25s, box-shadow 0.25s; outline:none;
    }
    .login-form input:focus { border-color:rgba(0,255,157,0.5); box-shadow:0 0 0 2px rgba(0,255,157,0.08); }
    .login-form input::placeholder { color:var(--text-dim); opacity:0.5; }
    .login-btn {
      width:100%; padding:0.85rem;
      background:var(--glow-green); color:var(--black);
      border:none; border-radius:var(--radius);
      font-family:var(--font-mono); font-size:0.82rem; font-weight:bold;
      letter-spacing:0.15em; text-transform:uppercase; cursor:pointer;
      transition:box-shadow 0.25s, transform 0.15s; margin-top:0.5rem;
    }
    .login-btn:hover { box-shadow:0 0 20px rgba(0,255,157,0.4); transform:translateY(-1px); }
    .login-btn:active { transform:translateY(0); }
    .login-back { text-align:center; margin-top:1.5rem; font-family:var(--font-mono); font-size:0.7rem; color:var(--text-dim); }
    .login-back a { color:var(--text-dim); }
    .login-back a:hover { color:var(--glow-cyan); }
    .login-footer-note {
      background:rgba(0,0,0,0.3); border-top:1px solid var(--border);
      padding:0.85rem 1.5rem; font-family:var(--font-mono); font-size:0.62rem;
      color:var(--text-dim); letter-spacing:0.08em; text-align:center;
      border-radius:0 0 var(--radius) var(--radius);
    }
  </style>
</head>
<body>
<div class="login-wrap">
  <div class="login-panel">

    <div class="login-header">
      <div class="login-header-dots"><span></span><span></span><span></span></div>
      <span class="login-header-title">secure_access.sh — bash</span>
      <span class="login-header-badge">AES-256</span>
    </div>

    <div class="login-body">
      <div class="login-logo">
        <div class="login-logo-icon">NC</div>
        <div class="login-logo-text">Null<span style="color:var(--glow-green)">Castle</span></div>
        <div class="login-logo-sub">Secure Administration Portal</div>
      </div>

      <?php if ($error): ?>
      <div class="login-alert <?php echo str_contains($error,'DB_ERROR') ? 'login-alert-db' : 'login-alert-error'; ?>">
        [<?php echo str_contains($error,'DB_ERROR') ? 'DB_ERROR' : 'ACCESS_DENIED'; ?>]
        <?php echo htmlspecialchars($error); ?>
      </div>
      <?php elseif (!$attempt): ?>
      <div class="login-alert login-alert-info">
        &gt; Authentication required. Credentials are verified<br>
        &gt; All access attempts are logged.
      </div>
      <?php endif; ?>

      <form method="POST" action="login.php" class="login-form" autocomplete="off">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username"
            placeholder="admin"
            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
            required autofocus autocomplete="username" />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password"
            placeholder="••••••••••••"
            required autocomplete="current-password" />
        </div>
        <button type="submit" class="login-btn">&gt; Authenticate &amp; Enter</button>
      </form>

      <div class="login-back"><a href="index.html">← Return to Main Site</a></div>
    </div>

    <div class="login-footer-note">
      [ TLS 1.3 ENCRYPTED ] &nbsp;|&nbsp; SESSION TIMEOUT: 30 MIN &nbsp;|&nbsp; ATTEMPTS LOGGED
    </div>
  </div>
</div>
</body>
</html>
