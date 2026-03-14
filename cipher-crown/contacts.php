<?php
$contactsFile = __DIR__ . '/data/contacts.json';
$contacts = [];

if (file_exists($contactsFile)) {
    $json     = file_get_contents($contactsFile);
    $contacts = json_decode($json, true);
} else {
    $textFile = __DIR__ . '/data/contacts.txt';
    $rawText  = file_exists($textFile) ? file_get_contents($textFile) : '';
}

$hq   = $contacts['headquarters'] ?? [];
$team = $contacts['team'] ?? [];
$ph   = $contacts['phone'] ?? [];
$em   = $contacts['email'] ?? [];
$avatars = ['🧙‍♂️','🕵️','🤖','👩‍💻','🦁','⚡'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact — Cipher &amp; Crown Technologies</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body data-page="contacts">
<div id="nav-placeholder"></div>
<main>
  <section class="page-hero">
    <div class="hex-bg"></div>
    <span class="eyebrow">// OPEN CHANNELS</span>
    <h1>Send a <span style="color:var(--gold)">Raven</span></h1>
    <p>"The ravens fly day and night. So does our support team."</p>
  </section>

  <section class="section container">
    <div class="contacts-layout">
      <div class="contact-info-panel">
        <h2>Company Contacts</h2>
        <p>Our ravens are always airborne. Reach us through any of these channels.</p>

        <?php if (!empty($hq)): ?>
        <div class="contact-item">
          <div class="contact-icon-box">🏰</div>
          <div class="contact-detail">
            <label>Headquarters</label>
            <span><?= htmlspecialchars($hq['name']) ?></span><br>
            <span><?= htmlspecialchars($hq['address']) ?></span><br>
            <span><?= htmlspecialchars($hq['city'] . ', ' . $hq['state'] . ' ' . $hq['zip']) ?></span>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($ph['main'])): ?>
        <div class="contact-item">
          <div class="contact-icon-box">📞</div>
          <div class="contact-detail">
            <label>Phone</label>
            <a href="tel:<?= htmlspecialchars($ph['main']) ?>"><?= htmlspecialchars($ph['main']) ?></a>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($em['general'])): ?>
        <div class="contact-item">
          <div class="contact-icon-box">✉️</div>
          <div class="contact-detail">
            <label>Email</label>
            <a href="mailto:<?= htmlspecialchars($em['general']) ?>"><?= htmlspecialchars($em['general']) ?></a>
          </div>
        </div>
        <?php endif; ?>

        <!-- Team Contacts (PHP loop) -->
        <h3 style="margin:2rem 0 1rem;font-family:var(--font-heading);color:var(--gold);font-size:0.9rem;letter-spacing:0.15em;text-transform:uppercase;">
          Team Directory
        </h3>

        <?php foreach ($team as $i => $member): ?>
        <div class="team-contact-card">
          <div class="tcc-avatar"><?= $avatars[$i % count($avatars)] ?></div>
          <div class="tcc-info">
            <span class="tcc-name"><?= htmlspecialchars($member['name']) ?></span>
            <span class="tcc-role"><?= htmlspecialchars($member['role']) ?></span>
            <a class="tcc-email" href="mailto:<?= htmlspecialchars($member['email']) ?>">
              <?= htmlspecialchars($member['email']) ?>
            </a>
          </div>
          <div style="font-family:var(--font-prose);font-style:italic;font-size:0.78rem;color:var(--gray-light);max-width:220px;line-height:1.4;">
            "<?= htmlspecialchars($member['quote']) ?>"
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Contact Form -->
      <div class="contact-form-panel">
        <h2>Transmit a Message</h2>
        <form id="contact-form" method="post" action="send.php">
          <div class="form-row">
            <div class="form-group">
              <label for="name">Your Name</label>
              <input type="text" id="name" name="name" placeholder="Tyrion Lannister" required>
            </div>
            <div class="form-group">
              <label for="email">Email Address</label>
              <input type="email" id="email" name="email" placeholder="tyrion@casterly.rock" required>
            </div>
          </div>
          <div class="form-group">
            <label for="subject">Subject</label>
            <select id="subject" name="subject">
              <option value="">— Select a category —</option>
              <option>Product Enquiry</option>
              <option>Security Incident</option>
              <option>Partnership</option>
              <option>Careers</option>
              <option>Press / Media</option>
              <option>Other</option>
            </select>
          </div>
          <div class="form-group">
            <label for="message">Your Message</label>
            <textarea id="message" name="message" placeholder="// Begin your transmission...&#10;// We will respond within one raven's flight." rows="6" required></textarea>
          </div>
          <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;">
            ⚡ Send Transmission
          </button>
        </form>
        <div class="form-success" id="form-success">
          <h3>✦ Raven Dispatched</h3>
          <p>Your message has been received. We will respond before the next watch.</p>
        </div>
      </div>
    </div>
  </section>
</main>
<div id="footer-placeholder"></div>
<script src="js/main.js"></script>
</body>
</html>
