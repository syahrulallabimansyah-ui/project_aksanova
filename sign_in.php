<?php
// sign_in.php — Halaman masuk pengguna
session_start();
require_once "db.php";

$error = "";

// Proses login saat form dikirim
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($email) || empty($password)) {
        $error = "Email dan kata sandi wajib diisi.";
    } else {
        // Cari user berdasarkan email
        $stmt = mysqli_prepare($conn, "SELECT id, full_name, username, password, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user["password"])) {
            // Login berhasil — simpan data ke session
            $_SESSION["user_id"]   = $user["id"];
            $_SESSION["user_name"] = $user["full_name"];
            $_SESSION["username"]  = $user["username"];
            $_SESSION["role"]      = $user["role"];

            header("Location: beranda.php");
            exit;
        } else {
            $error = "Email atau kata sandi salah.";
        }
    }
}

$page_title = "Sign In – AKSA NOVA";
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
      background: #dcdce4;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Outfit', sans-serif;
      background: linear-gradient(135deg, #d4d4e0 0%, #c2c2cf 50%, #d8d8e4 100%);
      background-size: 300% 300%;
      animation: bgShift 10s ease infinite;
    }

    @keyframes bgShift {
      0%,100% { background-position: 0% 50%; }
      50%      { background-position: 100% 50%; }
    }

    .card {
      width: 880px;
      max-width: calc(100vw - 24px);
      min-height: 560px;
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
      flex: 0 0 54%;
      background: var(--surface);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 52px 56px;
      gap: 0;
      position: relative;
    }

    .left::before {
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
      font-size: 2.4rem;
      font-weight: 700;
      color: var(--ink);
      margin-bottom: 10px;
      letter-spacing: -0.5px;
      animation: fadeSlide .7s .1s both;
    }

    .form-sub {
      font-size: .82rem;
      color: var(--ghost);
      font-weight: 300;
      margin-bottom: 30px;
      animation: fadeSlide .7s .18s both;
    }

    @keyframes fadeSlide {
      from { opacity:0; transform:translateY(12px); }
      to   { opacity:1; transform:translateY(0); }
    }

    /* Pesan error */
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

    .input-group {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-bottom: 12px;
      animation: fadeSlide .7s .26s both;
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
      padding: 14px 18px 14px 44px;
      border: 1.5px solid transparent;
      border-radius: var(--radius-md);
      background: var(--field);
      font-family: 'Outfit', sans-serif;
      font-size: .88rem;
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

    .forgot {
      font-size: .8rem;
      color: var(--dim);
      font-weight: 300;
      margin-bottom: 24px;
      text-decoration: none;
      transition: color var(--trans);
      align-self: flex-end;
      animation: fadeSlide .7s .32s both;
    }
    .forgot:hover { color: var(--accent); text-decoration: underline; }

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
    }

    .btn-primary:hover { background: #1a1a2a; box-shadow: 0 8px 24px rgba(0,0,0,.28); }
    .btn-primary:active { transform: scale(.97); }

    .right {
      flex: 1;
      background: var(--panel-bg);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 52px 44px;
      position: relative;
      overflow: hidden;
    }

    .right::before {
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
    .orb-1 { width:160px;height:160px;background:#fff;top:10%;left:5%; animation-delay:0s; }
    .orb-2 { width:100px;height:100px;background:#ccc;bottom:15%;right:8%; animation-delay:3s; }
    .orb-3 { width:80px;height:80px;background:#888;top:55%;left:20%; animation-delay:5s; }

    @keyframes orbFloat {
      0%,100% { transform: translateY(0) scale(1); }
      50%      { transform: translateY(-18px) scale(1.08); }
    }

    .right-content {
      position: relative;
      text-align: center;
      animation: riseIn .9s .15s cubic-bezier(.22,1,.36,1) both;
    }

    .right-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2.5rem;
      font-weight: 700;
      color: #1a1a26;
      line-height: 1.1;
      margin-bottom: 14px;
    }

    .right-sub {
      font-size: .86rem;
      font-weight: 300;
      color: #2e2e3a;
      line-height: 1.8;
      margin-bottom: 38px;
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

    @media (max-width: 700px) {
      .card { flex-direction: column; min-height: unset; border-radius: 18px; }
      .left { padding: 40px 28px 36px; flex: none; }
      .right { flex: none; padding: 40px 28px; order: -1; min-height: 200px; }
      .right-title { font-size: 1.8rem; }
      .right-sub   { margin-bottom: 22px; font-size: .82rem; }
      .form-title  { font-size: 1.9rem; }
      .left::before { height: 3px; }
    }

    @media (max-width: 420px) {
      .left  { padding: 32px 20px 28px; }
      .right { padding: 28px 20px; }
      .btn-primary { max-width: 100%; }
    }
  </style>
</head>
<body>

<div class="card">

  <!-- LEFT — Sign In Form -->
  <div class="left">
    <h1 class="form-title">Masuk</h1>
    <p class="form-sub">Selamat datang kembali, silakan masuk</p>

    <!-- Tampilkan pesan error jika ada -->
    <?php if ($error): ?>
      <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="sign_in.php" style="width:100%;display:contents;">
      <div class="input-group">
        <!-- Email -->
        <div class="input-wrap">
          <input type="email" name="email" placeholder="Alamat Email" autocomplete="email"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="2" y="4" width="20" height="16" rx="2"/>
            <path d="M22 7l-10 7L2 7"/>
          </svg>
        </div>

        <!-- Password -->
        <div class="input-wrap">
          <input type="password" name="password" placeholder="Kata Sandi" autocomplete="current-password" required/>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
        </div>
      </div>

  

      <button type="submit" class="btn-primary">Sign In</button>
    </form>
  </div>

  <!-- RIGHT — Info Panel -->
  <div class="right">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="right-content">
      <h2 class="right-title">Halo,<br>Teman</h2>
      <p class="right-sub">
        Daftarkan diri Anda dan mulai<br>
        gunakan layanan kami segera
      </p>
      <a href="sign_up.php" class="btn-outline">Daftar Sekarang</a>
    </div>
  </div>

</div>

</body>
</html>