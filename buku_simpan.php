<?php
// buku_simpan.php — Halaman buku yang disimpan oleh member
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: sign_in.php");
    exit;
}

require_once "db.php";

$user_name  = $_SESSION["user_name"]  ?? "Pengguna";
$role       = $_SESSION["role"]       ?? "member";
$is_admin   = ($role === "admin");

// Halaman ini hanya untuk member
if ($is_admin) {
    header("Location: beranda.php");
    exit;
}

$user_id    = (int)$_SESSION["user_id"];
$page_title = "Buku Simpan – AKSA NOVA";

// ─── Search ───
$search = trim($_GET["q"] ?? "");
$where_search = "";
if ($search !== "") {
    $s = mysqli_real_escape_string($conn, $search);
    $where_search = "AND (b.judul LIKE '%$s%' OR b.penulis LIKE '%$s%')";
}

// ─── Total ───
$total_res  = mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM buku_favorites f
     INNER JOIN buku b ON b.id = f.buku_id
     WHERE f.user_id = $user_id $where_search"
);
$total_simpan = (int)(mysqli_fetch_assoc($total_res)["total"] ?? 0);

// ─── Ambil buku yang disimpan ───
$saved_books = [];
$res = mysqli_query($conn,
    "SELECT b.*, f.id AS fav_id FROM buku_favorites f
     INNER JOIN buku b ON b.id = f.buku_id
     WHERE f.user_id = $user_id $where_search
     ORDER BY f.id DESC"
);
while ($row = mysqli_fetch_assoc($res)) $saved_books[] = $row;

// ─── Rating rata-rata ───
$rating_avg = [];
if (!empty($saved_books)) {
    $ids_str = implode(",", array_map("intval", array_column($saved_books, "id")));
    $rr = mysqli_query($conn,
        "SELECT buku_id, ROUND(AVG(rating),1) AS avg_r, COUNT(*) AS total
         FROM buku_ratings WHERE buku_id IN ($ids_str) GROUP BY buku_id"
    );
    while ($row = mysqli_fetch_assoc($rr))
        $rating_avg[$row["buku_id"]] = ["avg" => (float)$row["avg_r"], "total" => (int)$row["total"]];
}

$cover_cls = ["c1","c2","c3","c4","c5","c6","c7","c8"];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($page_title) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Cormorant+Garamond:wght@700&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --bg:         #f4f5f7;
      --sidebar-bg: #ffffff;
      --accent:     #2b4fff;
      --text:       #1a1a2e;
      --muted:      #7a7a9a;
      --card:       #ffffff;
      --radius:     14px;
      --sidebar-w:  170px;
      --shadow-sm:  0 2px 12px rgba(0,0,0,.05);
      --shadow-md:  0 4px 20px rgba(0,0,0,.10);
      --trans:      .2s cubic-bezier(.22,1,.36,1);
    }
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:'Nunito',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; display:flex; animation:bodyIn .5s ease both; }
    @keyframes bodyIn { from{opacity:0} to{opacity:1} }

    /* ── SIDEBAR ── */
    .sidebar { width:var(--sidebar-w); min-height:100vh; background:var(--sidebar-bg); display:flex; flex-direction:column; padding:24px 0 20px; border-right:1px solid #e8e9f0; position:fixed; top:0; left:0; bottom:0; z-index:100; transition:transform var(--trans); }
    .sidebar-toggle { display:none; position:fixed; top:14px; left:14px; z-index:200; width:40px; height:40px; border-radius:10px; border:none; background:#fff; box-shadow:0 2px 10px rgba(0,0,0,.12); cursor:pointer; align-items:center; justify-content:center; }
    .sidebar-toggle svg { width:20px; height:20px; color:var(--text); }
    .sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:90; }
    .logo-wrap { display:flex; flex-direction:column; align-items:center; padding:0 18px 24px; border-bottom:1px solid #f0f0f5; }
    .logo-icon { width:52px; height:52px; background:linear-gradient(135deg,#f0f0f8 0%,#fff 100%); border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:8px; box-shadow:0 4px 16px rgba(20,20,20,.15); }
    .logo-icon svg { width:28px; height:28px; color:var(--accent); }
    .logo-name { font-family:'Cormorant Garamond',serif; font-size:1rem; font-weight:700; color:var(--text); letter-spacing:.08em; text-align:center; }
    .logo-sub  { font-size:.58rem; color:var(--muted); letter-spacing:.12em; text-transform:uppercase; text-align:center; margin-top:2px; }
    .nav { flex:1; display:flex; flex-direction:column; gap:2px; padding:16px 10px; }
    .nav-item { display:flex; align-items:center; gap:10px; padding:10px 14px; border-radius:10px; font-size:.82rem; font-weight:600; color:var(--muted); cursor:pointer; text-decoration:none; transition:background var(--trans), color var(--trans); }
    .nav-item:hover  { background:#f0f2ff; color:var(--accent); }
    .nav-item.active { background:#eef0ff; color:var(--accent); }
    .nav-item svg { width:17px; height:17px; flex-shrink:0; }
    .nav-bottom { padding:10px 10px 0; border-top:1px solid #f0f0f5; display:flex; flex-direction:column; gap:2px; }

    /* ── MAIN ── */
    .main { margin-left:var(--sidebar-w); flex:1; padding:24px 24px 32px; min-height:100vh; }

    /* Topbar */
    .topbar { display:flex; gap:10px; margin-bottom:22px; align-items:center; }
    .search-wrap { display:flex; align-items:center; background:#fff; border-radius:50px; padding:0 16px; gap:10px; height:42px; border:1px solid #e4e5f0; flex:1; max-width:420px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
    .search-wrap input { border:none; outline:none; font-family:'Nunito',sans-serif; font-size:.82rem; color:var(--text); background:transparent; flex:1; }
    .search-wrap input::placeholder { color:var(--muted); }
    .search-wrap svg { width:16px; height:16px; color:var(--muted); }
    .tab-btn { padding:9px 18px; border-radius:50px; border:1px solid #e4e5f0; background:#fff; font-family:'Nunito',sans-serif; font-size:.8rem; font-weight:600; color:var(--muted); cursor:pointer; transition:all var(--trans); text-decoration:none; }
    .tab-btn:hover { border-color:var(--accent); color:var(--accent); }

    /* Page header */
    .page-header { margin-bottom:18px; display:flex; align-items:center; gap:12px; }
    .page-header-icon { width:42px; height:42px; background:#eef0ff; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .page-header-icon svg { width:20px; height:20px; color:var(--accent); }
    .page-title { font-family:'Cormorant Garamond',serif; font-size:1.5rem; font-weight:700; color:var(--text); }
    .page-subtitle { font-size:.78rem; color:var(--muted); margin-top:2px; }

    /* Buku Grid */
    .books-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:16px; animation:fadeUp .5s cubic-bezier(.22,1,.36,1) both; }
    @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }

    .book-card {
      background:var(--card); border-radius:var(--radius);
      box-shadow:var(--shadow-sm); overflow:hidden;
      cursor:pointer; transition:box-shadow var(--trans), transform var(--trans);
      display:flex; flex-direction:column;
    }
    .book-card:hover { box-shadow:var(--shadow-md); transform:translateY(-3px); }
    .book-cover-wrap { aspect-ratio:2/3; overflow:hidden; position:relative; flex-shrink:0; }
    .book-cover-wrap img { width:100%; height:100%; object-fit:cover; display:block; }
    .c1 { background:linear-gradient(135deg,#f5a623,#d4820a); }
    .c2 { background:linear-gradient(135deg,#9b59b6,#6c3483); }
    .c3 { background:linear-gradient(135deg,#e74c3c,#922b21); }
    .c4 { background:linear-gradient(135deg,#2ecc71,#1a8a4a); }
    .c5 { background:linear-gradient(135deg,#3498db,#1a5276); }
    .c6 { background:linear-gradient(135deg,#e91e63,#880e4f); }
    .c7 { background:linear-gradient(135deg,#ff5722,#bf360c); }
    .c8 { background:linear-gradient(135deg,#607d8b,#263238); }
    .cover-initial { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:1.4rem; font-weight:800; color:rgba(255,255,255,.8); }
    .cover-rating { position:absolute; bottom:6px; right:6px; background:rgba(0,0,0,.6); color:#ffb800; font-size:.6rem; font-weight:800; padding:2px 7px; border-radius:20px; display:flex; align-items:center; gap:2px; backdrop-filter:blur(3px); }
    .cover-rating svg { width:9px; height:9px; }
    .book-info { padding:10px 12px 12px; flex:1; display:flex; flex-direction:column; gap:4px; }
    .bk-title { font-size:.82rem; font-weight:800; color:var(--text); line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .bk-author { font-size:.7rem; color:var(--muted); font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .bk-actions { display:flex; align-items:center; justify-content:space-between; margin-top:auto; padding-top:8px; }
    .bk-genre { font-size:.6rem; font-weight:700; color:var(--accent); background:#eef0ff; padding:2px 8px; border-radius:50px; }
    .btn-hapus-simpan {
      display:inline-flex; align-items:center; gap:4px;
      padding:4px 10px; border-radius:50px; border:1.5px solid #e4e5f0;
      background:#fff; font-family:'Nunito',sans-serif; font-size:.65rem;
      font-weight:700; color:var(--muted); cursor:pointer; transition:all .18s;
    }
    .btn-hapus-simpan:hover { border-color:#e74c3c; color:#e74c3c; background:#fff5f5; }
    .btn-hapus-simpan svg { width:11px; height:11px; }

    /* Empty */
    .empty-state { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:80px 20px; gap:14px; color:var(--muted); }
    .empty-state svg { width:56px; height:56px; opacity:.3; }
    .empty-state .empty-title { font-size:1rem; font-weight:800; color:var(--text); }
    .empty-state .empty-sub { font-size:.8rem; }
    .empty-state a { color:var(--accent); font-weight:700; text-decoration:none; }

    /* Modal */
    .detail-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:500; align-items:center; justify-content:center; }
    .detail-overlay.open { display:flex; }
    .detail-modal { background:#fff; border-radius:16px; width:100%; max-width:500px; max-height:92vh; overflow-y:auto; box-shadow:0 24px 70px rgba(0,0,0,.28); animation:modalIn .25s cubic-bezier(.22,1,.36,1) both; margin:16px; }
    @keyframes modalIn { from{opacity:0;transform:scale(.94) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
    .detail-cover { width:100%; aspect-ratio:16/9; border-radius:16px 16px 0 0; overflow:hidden; position:relative; background:#1a1a2e; }
    .detail-cover img { width:100%; height:100%; object-fit:cover; display:block; }
    .detail-cover-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; }
    .detail-cover-placeholder svg { width:56px; height:56px; color:rgba(255,255,255,.25); }
    .detail-cover-badge { position:absolute; top:12px; right:12px; background:rgba(0,0,0,.55); color:#fff; font-size:.65rem; font-weight:800; padding:4px 10px; border-radius:20px; letter-spacing:.05em; text-transform:uppercase; backdrop-filter:blur(4px); }
    .detail-close-btn { position:absolute; top:12px; left:12px; width:32px; height:32px; border-radius:50%; background:rgba(0,0,0,.5); border:none; display:flex; align-items:center; justify-content:center; cursor:pointer; backdrop-filter:blur(4px); transition:background .2s; }
    .detail-close-btn:hover { background:rgba(0,0,0,.75); }
    .detail-close-btn svg { width:16px; height:16px; color:#fff; }
    .detail-body { padding:20px 22px 24px; }
    .detail-genre-chip { display:inline-block; background:#eef0ff; color:var(--accent); font-size:.65rem; font-weight:800; padding:3px 10px; border-radius:20px; letter-spacing:.05em; text-transform:uppercase; margin-bottom:8px; }
    .detail-title { font-family:'Cormorant Garamond',serif; font-size:1.45rem; font-weight:700; color:var(--text); line-height:1.2; margin-bottom:4px; }
    .detail-author { font-size:.82rem; color:var(--muted); font-weight:600; margin-bottom:14px; }
    .detail-stat-row { display:flex; gap:16px; margin-bottom:16px; align-items:center; flex-wrap:wrap; }
    .detail-stat { display:flex; align-items:center; gap:6px; font-size:.78rem; font-weight:700; }
    .detail-stat svg { width:15px; height:15px; }
    .detail-stat.likes { color:#e74c3c; }
    .detail-stat.favs  { color:#2b4fff; }
    .detail-meta-row { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
    .detail-meta-chip { display:flex; align-items:center; gap:5px; background:#f8f9ff; border:1px solid #eef0fc; border-radius:8px; padding:6px 11px; font-size:.71rem; font-weight:700; color:var(--muted); }
    .detail-meta-chip svg { width:13px; height:13px; flex-shrink:0; }
    .detail-meta-chip span { color:var(--text); }
    .detail-section-label { font-size:.68rem; font-weight:800; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:7px; }
    .detail-sinopsis { font-size:.83rem; line-height:1.7; color:#3a3a5a; background:#f8f9ff; border-radius:10px; padding:14px 16px; border-left:3px solid var(--accent); }
    .detail-sinopsis-empty { color:var(--muted); font-style:italic; }
    .detail-rating-row { display:flex; align-items:center; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
    .stars-display { display:flex; gap:2px; }
    .stars-display svg { width:15px; height:15px; }
    .rating-text { font-size:.75rem; font-weight:700; color:var(--muted); }
    .stars-input { display:flex; gap:3px; cursor:pointer; }
    .stars-input svg { width:22px; height:22px; color:#e0e0e0; transition:color .15s, transform .15s; cursor:pointer; }
    .stars-input svg.hover { color:#f5a623; transform:scale(1.15); }
    .stars-input svg.aktif { color:#f5a623; }
    .detail-loading { display:flex; align-items:center; justify-content:center; padding:60px; color:var(--muted); font-size:.85rem; flex-direction:column; gap:12px; }
    .spinner { width:32px; height:32px; border:3px solid #eee; border-top-color:var(--accent); border-radius:50%; animation:spin .7s linear infinite; }
    @keyframes spin { to { transform:rotate(360deg); } }

    /* btn-save di modal */
    .detail-body .btn-save {
      display:inline-flex; align-items:center; gap:5px; padding:6px 14px;
      border-radius:50px; border:1.5px solid #e4e5f0; background:#fff;
      font-family:'Nunito',sans-serif; font-size:.75rem; font-weight:700;
      color:var(--muted); cursor:pointer; transition:all .18s;
    }
    .detail-body .btn-save svg { width:14px; height:14px; }
    .detail-body .btn-save:hover { border-color:#2b4fff; color:#2b4fff; background:#eef0ff; }
    .detail-body .btn-save.aktif { border-color:#2b4fff; color:#2b4fff; background:#eef0ff; }
    .detail-body .btn-save.aktif svg { fill:#2b4fff; }

    /* Toast */
    .toast { position:fixed; bottom:24px; right:24px; background:#1a1a2e; color:#fff; font-size:.8rem; font-weight:700; padding:10px 18px; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.25); z-index:999; opacity:0; transform:translateY(10px); transition:all .3s; pointer-events:none; }
    .toast.show { opacity:1; transform:translateY(0); }

    /* Responsive */
    @media (max-width:700px) { .sidebar { transform:translateX(-100%); } .sidebar.open { transform:translateX(0); } .sidebar-overlay.open { display:block; } .sidebar-toggle { display:flex; } .main { margin-left:0; padding:70px 14px 24px; } .search-wrap { max-width:100%; } }
    @media (max-width:480px) { .books-grid { grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:12px; } }
  </style>
  <?php require_once "settings_include.php"; ?>
</head>
<?php require_once "pengaturan_panel.php"; ?>
<body>

<button class="sidebar-toggle" id="sidebarToggle" aria-label="Menu">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <line x1="3" y1="6" x2="21" y2="6"/>
    <line x1="3" y1="12" x2="21" y2="12"/>
    <line x1="3" y1="18" x2="21" y2="18"/>
  </svg>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
  <div class="logo-wrap">
    <div class="logo-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
      </svg>
    </div>
    <div class="logo-name">AKSA NOVA</div>
    <div class="logo-sub">Library Catalog App</div>
  </div>

  <nav class="nav">
    <a href="beranda.php" class="nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Beranda
    </a>
    <a href="daftar_buku.php" class="nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
      Daftar Buku
    </a>
    <a href="buku_simpan.php" class="nav-item active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
      Buku Simpan
    </a>
  </nav>

  <div class="nav-bottom">
    <a href="#" class="nav-item" onclick="bukaSettings(); return false;" title="Pengaturan Tampilan">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
      Pengaturan
    </a>
    <a href="logout.php" class="nav-item" style="color:#e74c3c;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Keluar
    </a>
  </div>
</aside>

<main class="main">

  <!-- Search -->
  <form method="GET" action="buku_simpan.php">
    <div class="topbar">
      <div class="search-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="q" placeholder="Cari di buku simpan…" value="<?= htmlspecialchars($search) ?>"/>
      </div>
      <button type="submit" class="tab-btn">Cari</button>
      <?php if ($search): ?><a href="buku_simpan.php" class="tab-btn">✕ Reset</a><?php endif; ?>
    </div>
  </form>

  <div class="page-header">
    <div class="page-header-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
    </div>
    <div>
      <div class="page-title">Buku Simpan</div>
      <div class="page-subtitle">
        <?php if ($search): ?>
          <?= $total_simpan ?> buku cocok dengan "<strong><?= htmlspecialchars($search) ?></strong>"
        <?php else: ?>
          <?= $total_simpan ?> buku yang kamu simpan
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if (empty($saved_books)): ?>
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
    <div class="empty-title">Belum ada buku yang disimpan</div>
    <div class="empty-sub">
      <?= $search ? "Tidak ada buku yang cocok dengan pencarian." : "Klik tombol <strong>Simpan</strong> pada buku di Daftar Buku." ?>
    </div>
    <a href="daftar_buku.php">Lihat Daftar Buku &rarr;</a>
  </div>
  <?php else: ?>
  <div class="books-grid">
    <?php foreach ($saved_books as $i => $buku):
      $col = $cover_cls[$i % count($cover_cls)];
      $words = preg_split('/\s+/', trim($buku["judul"]));
      $initial = mb_strtoupper(mb_substr($words[0], 0, 1)) . (isset($words[1]) ? mb_strtoupper(mb_substr($words[1], 0, 1)) : "");
      $rat = $rating_avg[$buku["id"]] ?? null;
    ?>
    <div class="book-card" onclick="bukaDetailBuku(<?= $buku['id'] ?>)" id="card_buku_<?= $buku['id'] ?>">
      <div class="book-cover-wrap <?= $col ?>">
        <?php if ($buku["gambar"] && file_exists($buku["gambar"])): ?>
          <img src="<?= htmlspecialchars($buku["gambar"]) ?>" alt="<?= htmlspecialchars($buku["judul"]) ?>">
        <?php else: ?>
          <div class="cover-initial"><?= htmlspecialchars($initial) ?></div>
        <?php endif; ?>
        <?php if ($rat && $rat["total"] > 0): ?>
        <div class="cover-rating">
          <svg viewBox="0 0 24 24" fill="#ffb800" stroke="#ffb800" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <?= $rat["avg"] ?>
        </div>
        <?php endif; ?>
      </div>
      <div class="book-info">
        <div class="bk-title"><?= htmlspecialchars($buku["judul"]) ?></div>
        <div class="bk-author"><?= htmlspecialchars($buku["penulis"] ?: "—") ?></div>
        <div class="bk-actions">
          <?php if ($buku["genre"]): ?>
            <span class="bk-genre"><?= htmlspecialchars($buku["genre"]) ?></span>
          <?php else: ?>
            <span></span>
          <?php endif; ?>
          <button class="btn-hapus-simpan" onclick="event.stopPropagation(); hapusSimpan(this, <?= $buku['id'] ?>)" title="Hapus dari simpan">
            <svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            Hapus
          </button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</main>

<!-- Modal Detail Buku -->
<div class="detail-overlay" id="detailOverlay">
  <div class="detail-modal">
    <div id="detailContent">
      <div class="detail-loading">
        <div class="spinner"></div>
        <span>Memuat detail buku…</span>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
  // Sidebar toggle
  const toggle  = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  toggle.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('open'); });
  overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); });

  // ─── Toast ───
  function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
  }

  // ─── Hapus dari simpan ───
  function hapusSimpan(btn, buku_id) {
    const fd = new FormData();
    fd.append('buku_id', buku_id);
    fd.append('type', 'favorite');
    fetch('like_handler.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => {
        if (!data.ok) return;
        if (!data.aktif) {
          const card = document.getElementById('card_buku_' + buku_id);
          if (card) {
            card.style.transition = 'opacity .3s, transform .3s';
            card.style.opacity = '0';
            card.style.transform = 'scale(.95)';
            setTimeout(() => {
              card.remove();
              const grid = document.querySelector('.books-grid');
              if (grid && grid.children.length === 0) location.reload();
            }, 300);
          }
          showToast('Buku dihapus dari simpan');
        }
      });
  }

  // ─── Modal Detail Buku ───
  function bukaDetailBuku(id) {
    const detailOverlay = document.getElementById('detailOverlay');
    const content = document.getElementById('detailContent');
    content.innerHTML = `<div class="detail-loading"><div class="spinner"></div><span>Memuat detail buku…</span></div>`;
    detailOverlay.classList.add('open');
    fetch('buku_detail.php?id=' + id)
      .then(r => r.json())
      .then(data => {
        if (!data.ok) { content.innerHTML = '<div class="detail-loading">Gagal memuat data.</div>'; return; }
        renderDetail(data.buku);
      })
      .catch(() => { content.innerHTML = '<div class="detail-loading">Gagal memuat data.</div>'; });
  }

  function tutupDetailBuku() { document.getElementById('detailOverlay').classList.remove('open'); }
  document.getElementById('detailOverlay').addEventListener('click', function(e) { if (e.target === this) tutupDetailBuku(); });
  document.addEventListener('keydown', function(e) { if (e.key === 'Escape') tutupDetailBuku(); });

  function escHTML(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function starSVG(filled) {
    return `<svg viewBox="0 0 24 24" fill="${filled?'#f5a623':'none'}" stroke="#f5a623" stroke-width="2" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`;
  }

  function renderStarsDisplay(avg, total) {
    let stars = '';
    for (let i = 1; i <= 5; i++) stars += starSVG(i <= Math.round(avg));
    return `<div class="stars-display">${stars}</div><span class="rating-text">${total > 0 ? avg + ' (' + total + ' ulasan)' : 'Belum ada rating'}</span>`;
  }

  function renderStarsInput(bukuId, userRating) {
    let html = `<div class="stars-input" id="starsInput_${bukuId}">`;
    for (let i = 1; i <= 5; i++) {
      html += `<svg viewBox="0 0 24 24" fill="${i<=userRating?'#f5a623':'none'}" stroke="#f5a623" stroke-width="2" stroke-linejoin="round"
        class="${i<=userRating?'aktif':''}"
        onmouseover="hoverStar(${bukuId},${i})"
        onmouseout="resetStarHover(${bukuId})"
        onclick="submitRating(${bukuId},${i})">
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
      </svg>`;
    }
    return html + '</div>';
  }

  function hoverStar(bukuId, n) {
    document.querySelectorAll(`#starsInput_${bukuId} svg`).forEach((s,i) => { s.setAttribute('fill', i<n?'#f5a623':'none'); s.classList.toggle('hover', i<n); });
  }

  function resetStarHover(bukuId) {
    document.querySelectorAll(`#starsInput_${bukuId} svg`).forEach(s => { s.classList.remove('hover'); s.setAttribute('fill', s.classList.contains('aktif')?'#f5a623':'none'); });
  }

  function submitRating(bukuId, rating) {
    const fd = new FormData();
    fd.append('buku_id', bukuId); fd.append('rating', rating);
    fetch('rating_handler.php', {method:'POST',body:fd}).then(r=>r.json()).then(data => {
      if (!data.ok) return;
      document.querySelectorAll(`#starsInput_${bukuId} svg`).forEach((s,i) => { const on=i<data.user_rating; s.setAttribute('fill',on?'#f5a623':'none'); s.classList.toggle('aktif',on); });
      const row = document.getElementById(`ratingRow_${bukuId}`);
      if (row) { const disp=row.querySelector('.stars-display'), txt=row.querySelector('.rating-text'); if(disp&&txt){ let s=''; for(let i=1;i<=5;i++) s+=starSVG(i<=Math.round(data.avg)); disp.innerHTML=s; txt.textContent=`${data.avg} (${data.total} ulasan)`; } }
    });
  }

  function toggleSimpanModal(btn, buku_id) {
    const fd = new FormData();
    fd.append('buku_id', buku_id);
    fd.append('type', 'favorite');
    fetch('like_handler.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => {
        if (!data.ok) return;
        btn.innerHTML = `<svg viewBox="0 0 24 24" fill="${data.aktif ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>${data.aktif ? 'Tersimpan' : 'Simpan'}`;
        btn.classList.toggle('aktif', data.aktif);
        if (!data.aktif) {
          // Hapus dari grid langsung jika disimpan di-untoggle
          const card = document.getElementById('card_buku_' + buku_id);
          if (card) {
            showToast('Buku dihapus dari simpan');
            card.style.transition = 'opacity .3s, transform .3s';
            card.style.opacity = '0';
            card.style.transform = 'scale(.95)';
            setTimeout(() => { card.remove(); const grid = document.querySelector('.books-grid'); if (grid && grid.children.length === 0) location.reload(); }, 300);
          }
        }
      });
  }

  function renderDetail(b) {
    const content = document.getElementById('detailContent');
    const stokLabel = b.stok == 0 ? 'Habis' : (b.stok <= 3 ? 'Terbatas' : 'Tersedia');
    const stokColor = b.stok == 0 ? '#e74c3c' : (b.stok <= 3 ? '#f39c12' : '#27ae60');
    const tglInput  = b.created_at ? new Date(b.created_at).toLocaleDateString('id-ID', {day:'numeric',month:'long',year:'numeric'}) : '—';
    const tglUpdate = b.updated_at && b.updated_at !== b.created_at ? new Date(b.updated_at).toLocaleDateString('id-ID', {day:'numeric',month:'long',year:'numeric'}) : null;
    const coverHTML = b.gambar
      ? `<img src="${escHTML(b.gambar)}" alt="${escHTML(b.judul)}">`
      : `<div class="detail-cover-placeholder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></div>`;
    content.innerHTML = `
      <div class="detail-cover">
        ${coverHTML}
        <button class="detail-close-btn" onclick="tutupDetailBuku()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        ${b.genre ? `<span class="detail-cover-badge">${escHTML(b.genre)}</span>` : ''}
      </div>
      <div class="detail-body">
        ${b.genre ? `<div class="detail-genre-chip">${escHTML(b.genre)}</div>` : ''}
        <div class="detail-title">${escHTML(b.judul)}</div>
        <div class="detail-author">${b.penulis ? '✍️ ' + escHTML(b.penulis) : 'Penulis tidak diketahui'}</div>
        <div class="detail-stat-row">
          <div class="detail-stat likes">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            ${b.jumlah_like} Suka
          </div>
          <div class="detail-stat favs">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            ${b.jumlah_favorit} Disimpan
          </div>
          <button id="modalSaveBtn_${b.id}" class="btn-save ${b.user_favorit ? 'aktif' : ''}" onclick="toggleSimpanModal(this, ${b.id})" style="margin-left:auto;">
            <svg viewBox="0 0 24 24" fill="${b.user_favorit ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            ${b.user_favorit ? 'Tersimpan' : 'Simpan'}
          </button>
        </div>
        <div class="detail-rating-row" id="ratingRow_${b.id}">
          ${renderStarsDisplay(b.rating_avg, b.rating_total)}
          <div style="border-left:1px solid #e0e0e0;height:16px;"></div>
          <span style="font-size:.72rem;font-weight:700;color:var(--muted);">Nilai kamu:</span>
          ${renderStarsInput(b.id, b.user_rating)}
        </div>
        <div class="detail-meta-row">
          ${b.isbn ? `<div class="detail-meta-chip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>ISBN: <span>${escHTML(b.isbn)}</span></div>` : ''}
          <div class="detail-meta-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            Stok: <span style="color:${stokColor};font-weight:800;">${escHTML(String(b.stok))} — ${stokLabel}</span>
          </div>
          <div class="detail-meta-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Ditambahkan: <span>${tglInput}</span>
          </div>
          ${tglUpdate ? `<div class="detail-meta-chip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Diperbarui: <span>${tglUpdate}</span></div>` : ''}
        </div>
        <div class="detail-section-label">Sinopsis / Ringkasan</div>
        ${b.sinopsis
          ? `<div class="detail-sinopsis">${escHTML(b.sinopsis).replace(/\n/g,'<br>')}</div>`
          : `<div class="detail-sinopsis"><span class="detail-sinopsis-empty">Sinopsis belum tersedia untuk buku ini.</span></div>`}
      </div>`;
  }
</script>
</body>
</html>