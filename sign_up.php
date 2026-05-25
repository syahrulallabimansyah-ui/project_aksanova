<?php
// sign_up.php — Halaman registrasi pengguna baru
session_start();
require_once "db.php";

$error   = "";
$success = "";

// Proses registrasi saat form dikirim
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name        = trim($_POST["full_name"] ?? "");
    $username         = trim($_POST["username"] ?? "");
    $email            = trim($_POST["email"] ?? "");
    $password         = trim($_POST["password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");

    // Validasi sederhana
    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = "Semua kolom wajib diisi.";
    } elseif ($password !== $confirm_password) {
        $error = "Kata sandi dan konfirmasi tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error = "Kata sandi minimal 6 karakter.";
    } else {
        // Cek apakah username atau email sudah dipakai
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Username atau email sudah digunakan.";
        } else {
            mysqli_stmt_close($stmt);

            // Hash password lalu simpan ke database (role default: member)
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt2  = mysqli_prepare($conn, "INSERT INTO users (full_name, username, email, password, role) VALUES (?, ?, ?, ?, 'member')");
            mysqli_stmt_bind_param($stmt2, "ssss", $full_name, $username, $email, $hashed);

            if (mysqli_stmt_execute($stmt2)) {
                $success = "Akun berhasil dibuat! Silakan masuk.";
            } else {
                $error = "Terjadi kesalahan, coba lagi.";
            }
            mysqli_stmt_close($stmt2);
        }
    }
}

$page_title = "Sign Up – AKSA NOVA";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($page_title) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --ink:       #0f0f14;
      --dim:       #6b6b80;
      --ghost:     #a8a8b8;
      --surface:   #ffffff;
      --field:     #f3f3f6;
      --field-foc: #eaeaef;
      --panel-bg:  linear-gradient(148deg, #c8c8d4 0%, #8a8a9a 50%, #3e3e50 100%);
      --accent:    #3e3e50;
      --ring:      rgba(62,62,80,.28);
      --radius-lg: 26px;
      --radius-md: 10px;
      --shadow:    0 32px 80px rgba(0,0,0,.45);
      --trans:     .25s cubic-bezier(.22,1,.36,1);
    }

    html, body {
      min-height: 100vh;
      font-family: 'Outfit', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #d4d4e0 0%, #c2c2cf 50%, #d8d8e4 100%);
      background-size: 300% 300%;
      animation: bgShift 10s ease infinite;
      padding: 16px;
    }

    @keyframes bgShift {
      0%,100% { background-position: 0% 50%; }
      50%      { background-position: 100% 50%; }
    }

    .card {
      width: 920px;
      max-width: calc(100vw - 24px);
      border-radius: var(--radius-lg);
      overflow: hidden;
      display: flex;
      box-shadow: var(--shadow);
      animation: riseIn .9s cubic-bezier(.22,1,.36,1) both;
    }

    @keyframes riseIn {
      from { opacity:0; transform:translateY(36px) scale(.97); }
      to   { opacity:1; transform:translateY(0) scale(1); }
    }

    .left {
      flex: 0 0 38%;
      background: var(--panel-bg);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 52px 40px;
      position: relative;
      overflow: hidden;
    }

    .left::before {
      content: '';
      position: absolute; inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='0.08'/%3E%3C/svg%3E");
      opacity: .22;
      pointer-events: none;
    }

    .orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(40px);
      opacity: .18;
      animation: orbFloat 8s ease-in-out infinite;
    }
    .orb-1 { width:140px;height:140px;background:#fff;top:8%;right:10%; animation-delay:0s; }
    .orb-2 { width:90px;height:90px;background:#aaa;bottom:12%;left:6%; animation-delay:4s; }

    @keyframes orbFloat {
      0%,100% { transform: translateY(0); }
      50%      { transform: translateY(-16px); }
    }

    .left-content {
      position: relative;
      text-align: center;
      animation: riseIn .9s .15s cubic-bezier(.22,1,.36,1) both;
    }

    .greeting {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2.4rem;
      font-weight: 700;
      color: #1a1a26;
      line-height: 1.1;
      margin-bottom: 14px;
    }

    .sub {
      font-size: .86rem;
      font-weight: 300;
      color: #2e2e3a;
      line-height: 1.8;
      margin-bottom: 36px;
    }

    .btn-outline {
      display: inline-block;
      padding: 11px 38px;
      border: 1.5px solid rgba(30,30,40,.45);
      border-radius: 50px;
      background: transparent;
      color: #1a1a26;
      font-family: 'Outfit', sans-serif;
      font-size: .76rem;
      font-weight: 500;
      letter-spacing: .16em;
      text-transform: uppercase;
      text-decoration: none;
      transition: background var(--trans), border-color var(--trans), box-shadow var(--trans), transform .15s;
    }
    .btn-outline:hover {
      background: rgba(20,20,30,.12);
      border-color: rgba(20,20,30,.7);
      box-shadow: 0 6px 20px rgba(0,0,0,.15);
      transform: translateY(-1px);
    }
    .btn-outline:active { transform: scale(.97); }

    .right {
      flex: 1;
      background: var(--surface);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 44px 52px;
      position: relative;
    }

    .right::after {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 4px;
      background: linear-gradient(90deg, #3e3e50, #8a8a9a, #3e3e50);
      background-size: 200% 100%;
      animation: shimmer 3s linear infinite;
    }

    @keyframes shimmer {
      0%   { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    .form-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2.3rem;
      font-weight: 700;
      color: var(--ink);
      margin-bottom: 8px;
      letter-spacing: -0.5px;
      animation: fadeSlide .7s .1s both;
    }

    .form-sub {
      font-size: .82rem;
      color: var(--ghost);
      font-weight: 300;
      margin-bottom: 26px;
      animation: fadeSlide .7s .18s both;
    }

    @keyframes fadeSlide {
      from { opacity:0; transform:translateY(12px); }
      to   { opacity:1; transform:translateY(0); }
    }

    /* Pesan error & sukses */
    .error-msg {
      width: 100%;
      background: #fff0f0;
      border: 1px solid #f5c6cb;
      color: #c0392b;
      font-size: .8rem;
      padding: 10px 14px;
      border-radius: var(--radius-md);
      margin-bottom: 14px;
      animation: fadeSlide .4s both;
    }

    .success-msg {
      width: 100%;
      background: #f0fff4;
      border: 1px solid #b2dfdb;
      color: #27ae60;
      font-size: .8rem;
      padding: 10px 14px;
      border-radius: var(--radius-md);
      margin-bottom: 14px;
      animation: fadeSlide .4s both;
    }

    .input-grid {
      width: 100%;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 0;
      animation: fadeSlide .7s .22s both;
    }

    .input-group {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-bottom: 12px;
      animation: fadeSlide .7s .28s both;
    }

    .input-wrap {
      position: relative;
      display: flex;
      align-items: center;
      width: 100%;
    }

    .input-wrap svg {
      position: absolute;
      left: 16px;
      width: 17px;
      height: 17px;
      color: var(--ghost);
      pointer-events: none;
      transition: color var(--trans);
    }

    .input-wrap input {
      width: 100%;
      padding: 13px 18px 13px 44px;
      border: 1.5px solid transparent;
      border-radius: var(--radius-md);
      background: var(--field);
      font-family: 'Outfit', sans-serif;
      font-size: .86rem;
      color: var(--ink);
      outline: none;
      transition: background var(--trans), border-color var(--trans), box-shadow var(--trans);
    }

    .input-wrap input::placeholder { color: var(--ghost); }

    .input-wrap input:focus {
      background: var(--field-foc);
      border-color: var(--accent);
      box-shadow: 0 0 0 3px var(--ring);
    }

    .input-wrap:focus-within svg { color: var(--accent); }

    .btn-primary {
      width: 100%;
      max-width: 200px;
      padding: 13px 0;
      border: none;
      border-radius: 50px;
      background: var(--ink);
      color: #fff;
      font-family: 'Outfit', sans-serif;
      font-size: .88rem;
      font-weight: 600;
      letter-spacing: .04em;
      cursor: pointer;
      transition: background var(--trans), transform .15s, box-shadow var(--trans);
      animation: fadeSlide .7s .38s both;
      margin-top: 6px;
    }

    .btn-primary:hover { background: #1a1a2a; box-shadow: 0 8px 24px rgba(0,0,0,.28); }
    .btn-primary:active { transform: scale(.97); }

    @media (max-width: 760px) {
      .card { flex-direction: column; border-radius: 18px; }
      .left { flex: none; padding: 36px 28px; order: -1; min-height: 190px; }
      .right { flex: none; padding: 36px 24px 40px; }
      .input-grid { grid-template-columns: 1fr; }
      .greeting { font-size: 1.9rem; }
    }

    @media (max-width: 420px) {
      .right  { padding: 28px 18px 36px; }
      .left   { padding: 28px 18px; }
      .btn-primary { max-width: 100%; }
    }
  </style>
</head>
<body>

<div class="card">

  <!-- LEFT — Info Panel -->
  <div class="left">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="left-content">
      <h1 class="greeting">Halo,<br>Sahabat</h1>
      <p class="sub">
        Kami merindukanmu<br>
        Masuk dan lanjutkan<br>
        dari tempat terakhir
      </p>
      <a href="sign_in.php" class="btn-outline">Sign In</a>
    </div>
  </div>

  <!-- RIGHT — Sign Up Form -->
  <div class="right">
    <h2 class="form-title">Buat Akun</h2>
    <p class="form-sub">Isi data diri Anda untuk mendaftar</p>

    <!-- Pesan error atau sukses -->
    <?php if ($error): ?>
      <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="success-msg"><?= htmlspecialchars($success) ?> <a href="sign_in.php">Masuk sekarang &rarr;</a></div>
    <?php endif; ?>

    <form method="POST" action="sign_up.php" style="width:100%;display:contents;">

      <!-- Nama Lengkap -->
      <div class="input-group" style="animation-delay:.18s">
        <div class="input-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <input type="text" name="full_name" placeholder="Nama Lengkap" autocomplete="name"
                 value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required/>
        </div>
      </div>

      <!-- Username & Email -->
      <div class="input-grid" style="margin-bottom:12px;">
        <div class="input-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <input type="text" name="username" placeholder="Username" autocomplete="username"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required/>
        </div>
        <div class="input-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="2" y="4" width="20" height="16" rx="2"/>
            <path d="M22 7l-10 7L2 7"/>
          </svg>
          <input type="email" name="email" placeholder="Alamat Email" autocomplete="email"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
        </div>
      </div>

      <!-- Password & Konfirmasi -->
      <div class="input-group" style="animation-delay:.34s">
        <div class="input-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <input type="password" name="password" placeholder="Kata Sandi" autocomplete="new-password" required/>
        </div>
        <div class="input-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            <path d="M12 16v-2" stroke-linecap="round"/>
          </svg>
          <input type="password" name="confirm_password" placeholder="Ulangi Kata Sandi" autocomplete="new-password" required/>
        </div>
      </div>

      <button type="submit" class="btn-primary">Daftar</button>
    </form>
  </div>

</div>

</body>
</html>