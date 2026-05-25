<?php
// login.php — Halaman sambutan / landing sebelum masuk
$page_title = "Selamat Datang – AKSA NOVA";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($page_title) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --ink:    #0f0f14;
      --light:  #f0f0f4;
      --accent: #3e3e50;
      --trans:  .28s cubic-bezier(.22,1,.36,1);
    }

    html, body {
      min-height: 100vh;
      background: #d0d0da;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Outfit', sans-serif;
      padding: 16px;
      background: linear-gradient(135deg, #d4d4e0 0%, #c0c0ce 50%, #d6d6e2 100%);
      background-size: 300% 300%;
      animation: bgShift 12s ease infinite;
    }

    @keyframes bgShift {
      0%,100% { background-position: 0% 50%; }
      50%      { background-position: 100% 50%; }
    }

    .card {
      width: 880px;
      max-width: calc(100vw - 24px);
      height: 560px;
      border-radius: 26px;
      overflow: hidden;
      display: flex;
      box-shadow: 0 32px 80px rgba(0,0,0,.5);
      animation: riseIn .9s cubic-bezier(.22,1,.36,1) both;
    }

    @keyframes riseIn {
      from { opacity:0; transform:translateY(36px) scale(.97); }
      to   { opacity:1; transform:translateY(0) scale(1); }
    }

    /* ── LEFT — Info Panel ── */
    .left {
      flex: 0 0 46%;
      background: linear-gradient(148deg, #c6c6d2 0%, #888898 48%, #3c3c4e 100%);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 52px 52px;
      position: relative;
      overflow: hidden;
    }

    /* grain texture */
    .left::before {
      content: '';
      position: absolute; inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='0.08'/%3E%3C/svg%3E");
      opacity: .22;
      pointer-events: none;
    }

    /* floating orbs */
    .orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(44px);
      opacity: .15;
      animation: orbFloat 9s ease-in-out infinite;
    }
    .orb-1 { width:180px;height:180px;background:#fff;top:-20px;right:-30px; animation-delay:0s; }
    .orb-2 { width:120px;height:120px;background:#bbb;bottom:20px;left:-20px; animation-delay:4s; }

    @keyframes orbFloat {
      0%,100% { transform: translateY(0) scale(1); }
      50%      { transform: translateY(-20px) scale(1.06); }
    }

    /* Book stack decorative icon */
    .deco-icon {
      position: relative;
      margin-bottom: 28px;
    }
    .deco-icon svg {
      width: 52px;
      height: 52px;
      color: rgba(255,255,255,.7);
      animation: pulse 3s ease-in-out infinite;
    }
    @keyframes pulse {
      0%,100% { transform: scale(1); opacity:.7; }
      50%      { transform: scale(1.08); opacity:1; }
    }

    .left-content {
      position: relative;
      text-align: center;
      animation: riseIn .9s .15s cubic-bezier(.22,1,.36,1) both;
    }

    .brand {
      font-family: 'Cormorant Garamond', serif;
      font-size: .78rem;
      font-weight: 600;
      letter-spacing: .3em;
      text-transform: uppercase;
      color: rgba(255,255,255,.6);
      margin-bottom: 16px;
    }

    .greeting {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2.8rem;
      font-weight: 700;
      color: #1a1a26;
      line-height: 1.1;
      margin-bottom: 14px;
      letter-spacing: -0.5px;
    }

    .sub {
      font-size: .86rem;
      font-weight: 300;
      color: #2e2e3c;
      line-height: 1.8;
      margin-bottom: 40px;
    }

    .btn-masuk {
      display: inline-block;
      padding: 12px 42px;
      border: 1.5px solid rgba(30,30,40,.45);
      border-radius: 50px;
      background: transparent;
      color: #1a1a26;
      font-family: 'Outfit', sans-serif;
      font-size: .78rem;
      font-weight: 500;
      letter-spacing: .18em;
      text-transform: uppercase;
      text-decoration: none;
      transition: background var(--trans), border-color var(--trans), box-shadow var(--trans), transform .15s;
    }
    .btn-masuk:hover {
      background: rgba(20,20,30,.12);
      border-color: rgba(20,20,30,.75);
      box-shadow: 0 6px 22px rgba(0,0,0,.18);
      transform: translateY(-1px);
    }
    .btn-masuk:active { transform: scale(.97); }

    /* ── RIGHT — Image ── */
    .right {
      flex: 1;
      position: relative;
      overflow: hidden;
    }

    .right img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      display: block;
      transition: transform 10s ease;
      animation: zoomIn 10s ease forwards;
    }

    @keyframes zoomIn {
      from { transform: scale(1.08); }
      to   { transform: scale(1); }
    }

    .card:hover .right img {
      transform: scale(1.04);
      transition: transform 6s ease;
    }

    /* gradient overlay on left edge */
    .right::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 80px; height: 100%;
      background: linear-gradient(to right, #888898, transparent);
      z-index: 1;
      pointer-events: none;
    }

    /* bottom caption */
    .img-caption {
      position: absolute;
      bottom: 16px;
      right: 16px;
      font-size: .68rem;
      color: rgba(255,255,255,.55);
      letter-spacing: .1em;
      font-weight: 300;
      z-index: 2;
    }

    /* ══════════════════
       RESPONSIVE
    ══════════════════ */
    @media (max-width: 680px) {
      .card {
        flex-direction: column;
        height: auto;
        border-radius: 18px;
      }

      .left {
        flex: none;
        padding: 40px 28px;
      }

      .right {
        flex: none;
        height: 220px;
        order: -1;
      }

      .right::before {
        display: none;
      }

      .greeting { font-size: 2.2rem; }
    }

    @media (max-width: 400px) {
      .left { padding: 32px 20px; }
      .greeting { font-size: 1.9rem; }
    }
  </style>
</head>
<body>

<div class="card">

  <!-- LEFT — Welcome Panel -->
  <div class="left">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="left-content">
      <div class="deco-icon">
        <img src="" alt="">
      </div>
      <h1 class="greeting">Halo,<br>Teman</h1>
      <p class="sub">
        Kami merindukanmu<br>
        Masuk dan lanjutkan dari tempat terakhir
      </p>
      <a href="sign_in.php" class="btn-masuk">Masuk</a>
    </div>
  </div>

  <!-- RIGHT — Library Image -->
  <div class="right">
    <img
      src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=900&q=80"
      alt="Interior perpustakaan"
      onerror="this.src='https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=900&q=80'"
    />
    <span class="img-caption">Perpustakaan AKSA NOVA</span>
  </div>

</div>

</body>
</html>
