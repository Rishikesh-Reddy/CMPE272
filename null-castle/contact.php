<?php
// NullCastle Systems - Contact Page
// PHP used for: (1) reading contacts from text file, (2) form processing

// ----------------------------------------------------------------
// CONTACTS DATA — read from data/contacts.txt
// ----------------------------------------------------------------
function parse_contacts(string $file): array {
    $data = ['sections' => [], 'current' => null];
    if (!file_exists($file)) return $data;

    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $raw) {
        $line = trim($raw);
        if ($line === '' || $line[0] === '#') continue;   // skip comments

        $parts = array_map('trim', explode('|', $line));
        $type  = $parts[0] ?? '';

        if ($type === 'section') {
            $data['current'] = $parts[1] ?? 'Other';
            $data['sections'][$data['current']] = [];

        } elseif ($data['current'] !== null) {
            $data['sections'][$data['current']][] = ['type' => $type, 'parts' => $parts];
        }
    }
    return $data;
}

$contacts_file = __DIR__ . '/data/contacts.txt';
$contacts      = parse_contacts($contacts_file);

// ----------------------------------------------------------------
// FORM PROCESSING
// ----------------------------------------------------------------
$success = false;
$error   = '';
$fields  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $fields = [
        'name'    => htmlspecialchars(trim($_POST['name']    ?? ''), ENT_QUOTES, 'UTF-8'),
        'email'   => htmlspecialchars(trim($_POST['email']   ?? ''), ENT_QUOTES, 'UTF-8'),
        'company' => htmlspecialchars(trim($_POST['company'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'subject' => htmlspecialchars(trim($_POST['subject'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'message' => htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8'),
    ];

    // Basic validation
    if (empty($fields['name']) || empty($fields['email']) || empty($fields['message'])) {
        $error = 'VALIDATION_ERROR: Required fields missing. Aborting transmission.';
    } elseif (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'VALIDATION_ERROR: Invalid email address format detected.';
    } else {
        // Send email (configure your mail settings on InfinityFree)
        $to      = 'contact@nullcastle.example.com'; // Replace with your real email
        $subject = '[NullCastle Contact] ' . $fields['subject'];
        $body    = "Name: {$fields['name']}\n"
                 . "Email: {$fields['email']}\n"
                 . "Company: {$fields['company']}\n"
                 . "Subject: {$fields['subject']}\n\n"
                 . "Message:\n{$fields['message']}\n";
        $headers = "From: noreply@nullcastle.example.com\r\nReply-To: {$fields['email']}";

        if (mail($to, $subject, $body, $headers)) {
            $success = true;
        } else {
            $error = 'TRANSMISSION_ERROR: Message could not be delivered. Try again or use an alternate channel.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="page-contact">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact — NullCastle Systems</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Cinzel:wght@400;700;900&family=Raleway:ital,wght@0,300;0,400;0,600;1,300&display=swap" />
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
      <li><a href="products.html" data-num="02">Services</a></li>
      <li><a href="news.html"     data-num="03">News</a></li>
      <li><a href="contact.php"   data-num="04" class="active">Contact</a></li>
    </ul>
    <div class="nav-status"><div class="nav-status-dot"></div>SYS:ONLINE</div>
    <div class="nav-hamburger" role="button" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="contacts-hero section-sm">
  <div class="container fade-in" style="position:relative; z-index:1;">
    <p style="font-family:var(--font-mono); font-size:0.75rem; color:var(--glow-green); letter-spacing:0.2em; text-transform:uppercase; margin-bottom:0.5rem;">&gt; ssh operative@nullcastle.systems -p 443</p>
    <h1>Open a <span class="glow-text-cyan">Secure</span> Channel</h1>
    <div class="divider"></div>
    <p style="color:var(--text-dim); max-width:580px; font-size:1.05rem;">
      No hold music. No ticketing systems. An actual human being reads every message.
      Usually Marcus. Sometimes Ravi. Definitely not the intern — we don't have one.
    </p>
  </div>
</section>

<!-- CONTACT LAYOUT -->
<section class="section" style="padding-top:0;">
  <div class="container">

    <?php
    $team_items = $contacts['sections']['Meet the Team'] ?? [];

    $clearance = [
        'green' => 'OMEGA',
        'cyan'  => 'ALPHA',
        'amber' => 'SIGMA',
        'red'   => 'DELTA',
        'dim'   => 'GHOST',
    ];

    $status_labels = [
        'THE_MIDDLEWARE_KING' => 'ACTIVE // KERNEL SPACE',
        'DR_COOPER'           => 'ACTIVE // ZERO-TRUST LAB',
        'HOOLI_EXILE'         => 'ACTIVE // VISION BUNKER',
        'WORLD_BEST_BOSS'     => 'ACTIVE // CLIENT FLOOR',
        'THE_WHISPERER'       => 'ACTIVE // DARK WEB NODE',
        'MR_ROBOT'            => 'STATUS: UNKNOWN',
    ];

    $color_vars = [
        'red'   => 'var(--glow-red)',
        'green' => 'var(--glow-green)',
        'cyan'  => 'var(--glow-cyan)',
        'amber' => 'var(--glow-amber)',
        'dim'   => 'var(--text-dim)',
    ];
    ?>

    <!-- ═══ MAIN TWO-COLUMN LAYOUT: Form Left, Info Right ═══ -->
    <div class="contact-layout-v2">

      <!-- LEFT COLUMN: Form (primary) -->
      <div class="contact-form-col">

        <!-- Emergency Banner -->
        <div style="display:flex; align-items:center; gap:1.2rem; background:rgba(255,60,90,0.06); border:1px solid rgba(255,60,90,0.35); border-left:3px solid var(--glow-red); border-radius:var(--radius); padding:1rem 1.5rem; margin-bottom:1.5rem;">
          <div style="width:38px; height:38px; border-radius:50%; background:rgba(255,60,90,0.12); border:1px solid rgba(255,60,90,0.5); display:flex; align-items:center; justify-content:center; font-family:var(--font-mono); font-size:1.1rem; font-weight:bold; color:var(--glow-red); flex-shrink:0; text-shadow:0 0 10px var(--glow-red);">!</div>
          <div>
            <div style="font-family:var(--font-serif); font-size:0.95rem; color:var(--text-bright); margin-bottom:0.15rem;">Active Incident? <span style="color:var(--glow-red);">Skip the form.</span></div>
            <div style="font-size:0.82rem; color:var(--text-dim); margin-bottom:0.3rem;">Do not wait — call our 24/7 IR hotline immediately:</div>
            <a href="tel:+18005550911" style="font-family:var(--font-mono); font-size:1.05rem; color:var(--glow-red); font-weight:bold; text-shadow:0 0 15px rgba(255,60,90,0.5); letter-spacing:0.06em; text-decoration:none;">+1 800 555 0911</a>
          </div>
        </div>

        <!-- Form Card -->
        <div class="card contact-form-card">
          <div class="form-card-header">
            <span class="form-card-label">// ENCRYPT_AND_SEND.sh</span>
            <span class="form-card-badge">TLS 1.3</span>
          </div>

          <?php if ($success): ?>
          <div class="form-success" style="display:block; margin-bottom:2rem;">
            [200 OK] Message received and queued for delivery.<br>
            A NullCastle operative will respond within 24 hours.<br>
            Reference ID: NC-<?php echo strtoupper(substr(md5(time()), 0, 8)); ?>
          </div>
          <?php endif; ?>

          <?php if ($error): ?>
          <div style="background:rgba(255,60,90,0.06); border:1px solid rgba(255,60,90,0.3); border-radius:var(--radius); padding:1rem 1.5rem; font-family:var(--font-mono); font-size:0.8rem; color:var(--glow-red); margin-bottom:1.5rem;">
            <?php echo $error; ?>
          </div>
          <?php endif; ?>

          <form method="POST" action="contact.php" class="contact-form" id="contact-form">

            <div class="form-row">
              <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" placeholder="Elliot Alderson"
                  value="<?php echo $fields['name'] ?? ''; ?>" required />
              </div>
              <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" placeholder="e@corp.example.com"
                  value="<?php echo $fields['email'] ?? ''; ?>" required />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="company">Organisation</label>
                <input type="text" id="company" name="company" placeholder="E Corp (Optional)"
                  value="<?php echo $fields['company'] ?? ''; ?>" />
              </div>
              <div class="form-group">
                <label for="subject">Subject *</label>
                <select id="subject" name="subject">
                  <option value="">-- Select Type --</option>
                  <option value="IR - Active Incident" <?php echo (($fields['subject'] ?? '') === 'IR - Active Incident') ? 'selected' : ''; ?>>IR - Active Incident (URGENT)</option>
                  <option value="Sales - Threat Intelligence" <?php echo (($fields['subject'] ?? '') === 'Sales - Threat Intelligence') ? 'selected' : ''; ?>>Sales - Threat Intelligence</option>
                  <option value="Sales - Zero-Trust" <?php echo (($fields['subject'] ?? '') === 'Sales - Zero-Trust') ? 'selected' : ''; ?>>Sales - Zero-Trust Architecture</option>
                  <option value="Sales - Red Team" <?php echo (($fields['subject'] ?? '') === 'Sales - Red Team') ? 'selected' : ''; ?>>Sales - Red Team Engagement</option>
                  <option value="Sales - CastleWall OS" <?php echo (($fields['subject'] ?? '') === 'Sales - CastleWall OS') ? 'selected' : ''; ?>>Sales - CastleWall OS</option>
                  <option value="Sales - vCISO" <?php echo (($fields['subject'] ?? '') === 'Sales - vCISO') ? 'selected' : ''; ?>>Sales - vCISO Advisory</option>
                  <option value="Partnership" <?php echo (($fields['subject'] ?? '') === 'Partnership') ? 'selected' : ''; ?>>Partnership</option>
                  <option value="Responsible Disclosure" <?php echo (($fields['subject'] ?? '') === 'Responsible Disclosure') ? 'selected' : ''; ?>>Responsible Disclosure</option>
                  <option value="Press" <?php echo (($fields['subject'] ?? '') === 'Press') ? 'selected' : ''; ?>>Press &amp; Media</option>
                  <option value="Other" <?php echo (($fields['subject'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="message">Message *</label>
              <textarea id="message" name="message" placeholder="Describe your situation. The more detail you provide, the faster we can help. For active incidents, include: first symptom time, affected systems, and whether you have containment capability." required><?php echo $fields['message'] ?? ''; ?></textarea>
            </div>

            <div class="form-group" style="display:flex; align-items:flex-start; gap:0.8rem;">
              <input type="checkbox" id="agree" name="agree" required
                style="width:auto; margin-top:3px; flex-shrink:0; accent-color:var(--glow-green);" />
              <label for="agree" style="font-family:var(--font-mono); font-size:0.72rem; color:var(--text-dim); text-transform:none; letter-spacing:0; cursor:pointer;">
                I confirm this message contains no classified information and I accept the
                <a href="#">Privacy Policy</a>.
              </label>
            </div>

            <button type="submit" class="btn btn-filled" style="width:100%; justify-content:center; padding:14px; font-size:0.85rem;">
              Transmit Securely →
            </button>

          </form>
        </div>

      </div><!-- /contact-form-col -->

      <!-- RIGHT COLUMN: Info + Operatives -->
      <div class="contact-info-col">

        <!-- Direct Lines -->
        <?php
        $direct_items = $contacts['sections']['Direct Lines'] ?? [];
        if (!empty($direct_items)):
        ?>
        <div class="contact-info-block">
          <h3>// Direct Lines</h3>
          <?php foreach ($direct_items as $item):
              $p     = $item['parts'];
              $icon  = $p[1] ?? '';
              $label = $p[2] ?? '';
              $href  = $p[3] ?? '#';
              $val   = $p[4] ?? '';
              $note  = $p[5] ?? '';
              $color = $color_vars[$p[6] ?? 'cyan'] ?? 'var(--glow-cyan)';
          ?>
          <div class="contact-detail">
            <span class="contact-detail-icon"><?php echo htmlspecialchars($icon); ?></span>
            <div>
              <div style="color:var(--text-bright); margin-bottom:0.2rem;"><?php echo htmlspecialchars($label); ?></div>
              <a href="<?php echo htmlspecialchars($href); ?>" style="color:<?php echo $color; ?>">
                <?php echo htmlspecialchars($val); ?>
              </a>
              <?php if ($note): ?>
              <div style="font-family:var(--font-mono); font-size:0.72rem; color:<?php echo $color; ?>; margin-top:0.3rem;">
                <?php echo htmlspecialchars($note); ?>
              </div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Response Times -->
        <?php
        $sla_items = $contacts['sections']['Response Times'] ?? [];
        if (!empty($sla_items)):
        ?>
        <div class="contact-info-block">
          <h3>// Response Times</h3>
          <div style="background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:1rem 1.2rem; font-family:var(--font-mono); font-size:0.78rem;">
          <?php
          $last = count($sla_items) - 1;
          foreach ($sla_items as $i => $item):
              $p     = $item['parts'];
              $label = $p[1] ?? '';
              $val   = $p[2] ?? '';
              $color = $color_vars[$p[3] ?? 'cyan'] ?? 'var(--glow-cyan)';
              $border = ($i < $last) ? 'border-bottom:1px solid var(--border);' : '';
          ?>
            <div style="display:flex; justify-content:space-between; padding:0.4rem 0; <?php echo $border; ?>">
              <span style="color:var(--text-dim);"><?php echo htmlspecialchars($label); ?></span>
              <span style="color:<?php echo $color; ?>"><?php echo htmlspecialchars($val); ?></span>
            </div>
          <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Find Us Online -->
        <?php
        $social_items = $contacts['sections']['Find Us Online'] ?? [];
        if (!empty($social_items)):
        ?>
        <div class="contact-info-block">
          <h3>// Find Us Online</h3>
          <div class="social-links">
          <?php foreach ($social_items as $item):
              $p    = $item['parts'];
              $code = $p[1] ?? '';
              $lbl  = $p[2] ?? '';
              $url  = $p[3] ?? '#';
          ?>
            <a href="<?php echo htmlspecialchars($url); ?>" class="social-link">
              [<?php echo htmlspecialchars($code); ?>] <?php echo htmlspecialchars($lbl); ?>
            </a>
          <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Map placeholder -->
        <div class="map-placeholder">
          <div class="map-pin">
            <span style="font-family:var(--font-mono); font-size:0.78rem;">One Null Plaza, San Francisco</span>
          </div>
        </div>

      </div><!-- /contact-info-col -->

    </div><!-- /contact-layout-v2 -->

    <!-- ═══ OPERATIVE ROSTER — full-width below the two columns ═══ -->
    <?php if (!empty($team_items)): ?>
    <div style="padding-top:3rem; border-top:1px solid var(--border);">

      <!-- Section header -->
      <div style="display:flex; align-items:center; gap:1.5rem; margin-bottom:2rem;">
        <div style="width:3px; height:32px; background:var(--glow-green); box-shadow:0 0 10px rgba(0,255,157,0.5); flex-shrink:0;"></div>
        <div>
          <div style="font-family:var(--font-mono); font-size:0.65rem; color:var(--glow-green); letter-spacing:0.25em; text-transform:uppercase; margin-bottom:0.2rem;">// OPERATIVE_ROSTER.txt</div>
          <h2 style="font-family:var(--font-serif); font-size:1.5rem; color:var(--text-bright); margin:0;">Meet the <span style="color:var(--glow-cyan); text-shadow:0 0 20px rgba(0,212,255,0.5);">Team</span></h2>
        </div>
      </div>

      <!-- Operative cards grid -->
      <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:1.2rem;">
      <?php
      $file_counter = 1;
      foreach ($team_items as $item):
          $p          = $item['parts'];
          $initials   = $p[1] ?? '??';
          $name       = $p[2] ?? '';
          $role       = $p[3] ?? '';
          $codename   = $p[4] ?? '';
          $email      = $p[5] ?? '';
          $phone      = $p[6] ?? '';
          $quote      = $p[7] ?? '';
          $accent     = $p[8] ?? 'cyan';
          $avcls      = 'avatar-' . $accent;
          $clr        = $clearance[$accent] ?? 'ALPHA';
          $status     = $status_labels[$codename] ?? 'ACTIVE';
          $file_id    = 'NC-OPS-' . str_pad($file_counter++, 3, '0', STR_PAD_LEFT);
          $show_phone = ($phone && $phone !== '+1 000 000 0000');
          $accent_color = $color_vars[$accent] ?? 'var(--glow-cyan)';

          // Per-accent raw values for glow / border effects
          $glow_map = [
              'green' => ['color' => '#00ff9d', 'shadow' => '0 0 18px rgba(0,255,157,0.18)', 'border_hover' => 'rgba(0,255,157,0.35)', 'bg_hover' => 'rgba(0,255,157,0.04)'],
              'cyan'  => ['color' => '#00d4ff', 'shadow' => '0 0 18px rgba(0,212,255,0.18)', 'border_hover' => 'rgba(0,212,255,0.35)', 'bg_hover' => 'rgba(0,212,255,0.04)'],
              'amber' => ['color' => '#ffb300', 'shadow' => '0 0 18px rgba(255,179,0,0.18)',  'border_hover' => 'rgba(255,179,0,0.35)',  'bg_hover' => 'rgba(255,179,0,0.04)'],
              'red'   => ['color' => '#ff3c5a', 'shadow' => '0 0 18px rgba(255,60,90,0.18)',  'border_hover' => 'rgba(255,60,90,0.35)',  'bg_hover' => 'rgba(255,60,90,0.04)'],
              'dim'   => ['color' => '#607590', 'shadow' => '0 0 18px rgba(96,117,144,0.12)', 'border_hover' => 'rgba(96,117,144,0.3)',  'bg_hover' => 'rgba(96,117,144,0.04)'],
          ];
          $g = $glow_map[$accent] ?? $glow_map['cyan'];
      ?>
      <div
        style="background:#161c2a; border:1px solid #1e2d44; border-radius:6px; overflow:hidden; position:relative; transition:border-color 0.25s, box-shadow 0.25s, background 0.25s;"
        onmouseover="this.style.borderColor='<?php echo $g['border_hover']; ?>'; this.style.boxShadow='<?php echo $g['shadow']; ?>'; this.style.background='<?php echo $g['bg_hover']; ?>';"
        onmouseout="this.style.borderColor='#1e2d44'; this.style.boxShadow='none'; this.style.background='#161c2a';"
      >
        <!-- Top accent line -->
        <div style="height:2px; background:<?php echo $g['color']; ?>; box-shadow:0 0 8px <?php echo $g['color']; ?>; opacity:0.7;"></div>

        <div style="padding:1.4rem 1.5rem;">

          <!-- Header row: file ID + clearance badge -->
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.1rem;">
            <span style="font-family:'Share Tech Mono',monospace; font-size:0.6rem; color:#607590; letter-spacing:0.1em;"><?php echo htmlspecialchars($file_id); ?></span>
            <span style="font-family:'Share Tech Mono',monospace; font-size:0.6rem; color:<?php echo $g['color']; ?>; letter-spacing:0.14em; border:1px solid <?php echo $g['color']; ?>; padding:2px 8px; border-radius:2px; background:rgba(0,0,0,0.3);"><?php echo htmlspecialchars($clr); ?></span>
          </div>

          <!-- Avatar + Name block -->
          <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1rem;">
            <div class="team-avatar <?php echo htmlspecialchars($avcls); ?>" style="width:56px; height:56px; font-size:1.15rem; flex-shrink:0; margin:0;">
              <?php echo htmlspecialchars($initials); ?>
            </div>
            <div style="min-width:0;">
              <div style="font-family:'Cinzel',serif; font-size:1rem; font-weight:700; color:#eef4ff; margin-bottom:0.15rem; letter-spacing:0.03em;"><?php echo htmlspecialchars($name); ?></div>
              <div style="font-family:'Share Tech Mono',monospace; font-size:0.68rem; color:<?php echo $g['color']; ?>; letter-spacing:0.06em; margin-bottom:0.1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($role); ?></div>
              <div style="font-family:'Share Tech Mono',monospace; font-size:0.6rem; color:#607590;">[<?php echo htmlspecialchars($codename); ?>]</div>
            </div>
          </div>

          <!-- Divider -->
          <div style="height:1px; background:#1e2d44; margin-bottom:0.9rem;"></div>

          <!-- Contact links -->
          <div style="display:flex; flex-direction:column; gap:0.4rem; margin-bottom:<?php echo $quote ? '0.9rem' : '0'; ?>;">
            <?php if ($email): ?>
            <a href="mailto:<?php echo htmlspecialchars($email); ?>"
               style="font-family:'Share Tech Mono',monospace; font-size:0.7rem; color:<?php echo $g['color']; ?>; text-decoration:none; display:flex; align-items:center; gap:0.5rem; transition:opacity 0.2s;"
               onmouseover="this.style.opacity='0.65'" onmouseout="this.style.opacity='1'">
              <span style="font-size:0.65rem; opacity:0.6; flex-shrink:0;">▶</span>
              <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($email); ?></span>
            </a>
            <?php endif; ?>
            <?php if ($show_phone): ?>
            <a href="tel:<?php echo htmlspecialchars(str_replace(' ','',$phone)); ?>"
               style="font-family:'Share Tech Mono',monospace; font-size:0.7rem; color:#607590; text-decoration:none; display:flex; align-items:center; gap:0.5rem; transition:color 0.2s;"
               onmouseover="this.style.color='<?php echo $g['color']; ?>'" onmouseout="this.style.color='#607590'">
              <span style="font-size:0.65rem; opacity:0.5; flex-shrink:0;">▶</span>
              <?php echo htmlspecialchars($phone); ?>
            </a>
            <?php endif; ?>
          </div>

          <?php if ($quote): ?>
          <!-- Quote -->
          <div style="padding:0.55rem 0.8rem; border-left:2px solid <?php echo $g['color']; ?>; background:rgba(0,0,0,0.2); border-radius:0 3px 3px 0;">
            <div style="font-family:'Raleway',sans-serif; font-size:0.72rem; font-style:italic; color:#607590; line-height:1.5; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;"><?php echo htmlspecialchars($quote); ?></div>
          </div>
          <?php endif; ?>

        </div>
      </div>
      <?php endforeach; ?>
      </div>

    </div>
    <?php endif; ?>

  </div>
</section>

<!-- FAQ -->
<section class="section" style="background:var(--deep); border-top:1px solid var(--border);">
  <div class="container">
    <div class="section-heading">
      <p class="pre-title">// FREQUENTLY_ASKED.txt</p>
      <h2>Common <span class="glow-text-cyan">Questions</span></h2>
      <div class="divider divider-center"></div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:1.5rem; max-width:900px; margin:0 auto;">
      <div class="card">
        <h3 style="font-size:0.95rem; margin-bottom:0.6rem; color:var(--glow-cyan);">Do you work with small businesses?</h3>
        <p style="font-size:0.85rem; color:var(--text-dim);">Our vCISO and TIP services scale down to teams of 10. If you handle sensitive data, you need us. Company size is not a moat against attackers.</p>
      </div>
      <div class="card">
        <h3 style="font-size:0.95rem; margin-bottom:0.6rem; color:var(--glow-cyan);">How quickly can you respond to an active breach?</h3>
        <p style="font-size:0.85rem; color:var(--text-dim);">IR retainer clients: 15-minute acknowledgment, 60-minute engagement. Non-retainer emergency: best-effort, typically 2-4 hours. We strongly recommend a retainer.</p>
      </div>
      <div class="card">
        <h3 style="font-size:0.95rem; margin-bottom:0.6rem; color:var(--glow-cyan);">Is your bug bounty programme public?</h3>
        <p style="font-size:0.85rem; color:var(--text-dim);">Yes. Report valid vulnerabilities in our infrastructure and earn up to $50,000. Full scope and rules are in our responsible disclosure programme. We pay fast, we pay fairly.</p>
      </div>
      <div class="card">
        <h3 style="font-size:0.95rem; margin-bottom:0.6rem; color:var(--glow-cyan);">Can we schedule a free assessment?</h3>
        <p style="font-size:0.85rem; color:var(--text-dim);">Yes. A 30-minute threat assessment call with a senior engineer costs nothing and tells you a great deal. No pitch decks. Use the form above to book.</p>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="footer-logo">Null<span>Castle</span> Systems</div>
        <p>Cybersecurity for the brave, the paranoid, and the ruthlessly prepared. Protecting the realm since 2019.</p>
        <p style="font-family:var(--font-mono); font-size:0.7rem; color:var(--text-dim); margin-top:1rem;">PGP: <span style="color:var(--glow-green)">0xDEAD BEEF C0DE CAFE</span></p>
      </div>
      <div class="footer-col"><h4>Navigate</h4><ul>
        <li><a href="index.html">Home</a></li><li><a href="about.html">About</a></li>
        <li><a href="products.html">Services</a></li><li><a href="news.html">News</a></li>
        <li><a href="contact.php">Contact</a></li>
      </ul></div>
      <div class="footer-col"><h4>Services</h4><ul>
        <li><a href="products.html">Threat Intelligence</a></li>
        <li><a href="products.html">Zero-Trust Arch.</a></li>
        <li><a href="products.html">Incident Response</a></li>
        <li><a href="products.html">Red Team Ops</a></li>
        <li><a href="products.html">CastleWall OS</a></li>
      </ul></div>
      <div class="footer-col"><h4>Intel</h4><ul>
        <li><a href="news.html">Threat Reports</a></li>
        <li><a href="news.html">White Papers</a></li>
        <li><a href="news.html">CVE Advisories</a></li>
        <li><a href="news.html">Press Releases</a></li>
        <li><a href="contact.php">Bug Bounty</a></li>
      </ul></div>
    </div>
    <div class="footer-bottom">
      <span>&copy; 2025 NullCastle Systems, Inc.</span>
      <span><a href="#">Privacy</a> / <a href="#">Terms</a> / <a href="#">Disclosure</a></span>
      <span style="color:var(--glow-green);">[ ENCRYPTED CONNECTION ]</span>
    </div>
  </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {

  /* --- Scrolled nav --- */
  var nav = document.querySelector('nav');
  if (nav) {
    window.addEventListener('scroll', function() {
      nav.classList.toggle('scrolled', window.scrollY > 30);
    });
  }

  /* --- Mobile hamburger --- */
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
      } else {
        spans.forEach(function(s) { s.style.transform = ''; s.style.opacity = ''; });
      }
    });
    navLinks.querySelectorAll('a').forEach(function(a) {
      a.addEventListener('click', function() {
        navLinks.classList.remove('open');
        hamburger.querySelectorAll('span').forEach(function(s) { s.style.transform = ''; s.style.opacity = ''; });
      });
    });
  }

  /* --- Terminal typewriter (home page only) --- */
  var terminalBody = document.querySelector('.terminal-body');
  if (terminalBody) {
    var lines = [
      { type: 'cmd', text: 'nullcastle --status' },
      { type: 'out', text: 'Initializing NullCastle OS v4.2.0...', cls: 'info' },
      { type: 'out', text: '[ OK ] Firewall: ACTIVE',               cls: 'success' },
      { type: 'out', text: '[ OK ] Encryption: AES-256-GCM',        cls: 'success' },
      { type: 'out', text: '[ OK ] ThreatDB: 2.4M signatures loaded', cls: 'success' },
      { type: 'cmd', text: 'whoami' },
      { type: 'out', text: 'nullcastle\\root - clearance: OMEGA',   cls: 'warn' },
      { type: 'cmd', text: 'ls -la /services/' },
      { type: 'out', text: 'drwx  breach-response/',                cls: '' },
      { type: 'out', text: 'drwx  zero-trust-arch/',                cls: '' },
      { type: 'out', text: 'drwx  dark-web-intel/',                 cls: '' },
      { type: 'cmd', text: 'echo "The castle never sleeps."' },
      { type: 'out', text: 'The castle never sleeps.',              cls: 'success' }
    ];
    var li = 0;
    terminalBody.innerHTML = '';

    function typeText(el, text, cb, speed) {
      speed = speed || 30;
      var i = 0;
      var interval = setInterval(function() {
        el.textContent += text[i++];
        terminalBody.scrollTop = terminalBody.scrollHeight;
        if (i >= text.length) { clearInterval(interval); if (cb) cb(); }
      }, speed);
    }

    function nextLine() {
      if (li >= lines.length) {
        var cur = document.createElement('span');
        cur.className = 'terminal-cursor';
        terminalBody.appendChild(cur);
        return;
      }
      var line = lines[li];
      var row  = document.createElement('div');
      row.className = 'terminal-line';
      if (line.type === 'cmd') {
        row.innerHTML = '<span class="terminal-prompt">nullcastle@sys:~$&nbsp;</span><span class="terminal-cmd"></span>';
        terminalBody.appendChild(row);
        typeText(row.querySelector('.terminal-cmd'), line.text, function() {
          li++; setTimeout(nextLine, 200);
        }, 35);
      } else {
        var cls = line.cls ? 'terminal-output ' + line.cls : 'terminal-output';
        row.innerHTML = '<span class="' + cls + '"></span>';
        terminalBody.appendChild(row);
        typeText(row.querySelector('span'), line.text, function() {
          li++; setTimeout(nextLine, 60);
        }, 14);
      }
    }
    setTimeout(nextLine, 700);
  }

  /* --- Fade-in cards (excludes person-cards and terminal children) --- */
  if ('IntersectionObserver' in window) {
    var fadeObserver = new IntersectionObserver(function(entries) {
      entries.forEach(function(e) {
        if (e.isIntersecting) {
          e.target.style.opacity   = '1';
          e.target.style.transform = 'translateY(0)';
          fadeObserver.unobserve(e.target);
        }
      });
    }, { threshold: 0.08 });

    document.querySelectorAll('.team-card, .news-card, .product-card, .card').forEach(function(el) {
      /* skip person-card wrappers and their children — they manage their own opacity */
      if (el.classList.contains('person-card')) return;
      if (el.classList.contains('person-card-front')) return;
      if (el.classList.contains('person-card-back')) return;
      if (el.closest && el.closest('.person-card')) return;
      if (el.closest && el.closest('.terminal-body')) return;
      el.style.opacity    = '0';
      el.style.transform  = 'translateY(22px)';
      el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
      fadeObserver.observe(el);
    });
  }

  /* --- Counter animation --- */
  if ('IntersectionObserver' in window) {
    document.querySelectorAll('.hero-stat .value').forEach(function(el) {
      var raw     = el.textContent.trim();
      var target  = parseFloat(raw);
      var isFloat = raw.indexOf('.') !== -1;
      var suffix  = raw.replace(/[\d.]/g, '');
      if (isNaN(target)) return;
      var duration  = 2000;
      var startTime = null;
      function update(now) {
        if (!startTime) startTime = now;
        var progress = Math.min((now - startTime) / duration, 1);
        var eased    = 1 - Math.pow(1 - progress, 3);
        el.textContent = (isFloat ? (eased * target).toFixed(1) : Math.floor(eased * target)) + suffix;
        if (progress < 1) requestAnimationFrame(update);
      }
      var statObs = new IntersectionObserver(function(entries) {
        if (entries[0].isIntersecting) { requestAnimationFrame(update); statObs.disconnect(); }
      }, { threshold: 0.5 });
      statObs.observe(el);
    });
  }

  /* --- Hero title glitch on hover --- */
  var glitchEl = document.querySelector('.hero-title');
  if (glitchEl) {
    var glitchTimer;
    glitchEl.addEventListener('mouseenter', function() {
      clearTimeout(glitchTimer);
      glitchEl.style.textShadow = '2px 0 #ff3c5a, -2px 0 #00d4ff';
      glitchTimer = setTimeout(function() { glitchEl.style.textShadow = ''; }, 200);
    });
  }

  /* --- News ticker duplicate --- */
  var tickerInner = document.querySelector('.news-ticker-inner');
  if (tickerInner) {
    tickerInner.innerHTML += tickerInner.innerHTML;
  }

  /* --- Operative cards: crossfade via .oc-active --- */
  document.querySelectorAll('.operative-card').forEach(function(card) {
    card.addEventListener('mouseenter', function() { card.classList.add('oc-active'); });
    card.addEventListener('mouseleave', function() { card.classList.remove('oc-active'); });
    card.addEventListener('touchend', function(e) {
      e.preventDefault();
      card.classList.toggle('oc-active');
    });
    card.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); card.classList.toggle('oc-active'); }
    });
  });

});
</script>
</body>
</html>
