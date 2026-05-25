<?php
// daftar_buku.php — Katalog buku (dinamis dari database)
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: sign_in.php");
    exit;
}

require_once "db.php";

$user_name  = $_SESSION["user_name"]  ?? "Pengguna";
$role       = $_SESSION["role"]       ?? "member";
$is_admin   = ($role === "admin");
$admin_role = $is_admin ? "Admin" : "Member";
$page_title = "Daftar Buku – AKSA NOVA";

// ─── Pagination ───
$per_page   = 10;
$page       = max(1, (int)($_GET["page"] ?? 1));
$offset     = ($page - 1) * $per_page;

// ─── Search ───
$search = trim($_GET["q"] ?? "");
$where  = "";
if ($search !== "") {
    $s     = mysqli_real_escape_string($conn, $search);
    $where = "WHERE judul LIKE '%$s%' OR penulis LIKE '%$s%' OR isbn LIKE '%$s%'";
}

// ─── Total & data ───
$total_res  = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku $where");
$total_row  = mysqli_fetch_assoc($total_res);
$total_buku = (int)$total_row["total"];
$total_pages = max(1, ceil($total_buku / $per_page));
$page = min($page, $total_pages);

$buku_list = [];
$res = mysqli_query($conn,
    "SELECT * FROM buku $where ORDER BY id ASC LIMIT $per_page OFFSET $offset"
);
while ($row = mysqli_fetch_assoc($res)) $buku_list[] = $row;

// ─── Likes & Favorites untuk user ini ───
$user_id   = (int)$_SESSION["user_id"];
$liked_ids = [];
$fav_ids   = [];
$like_counts = [];
$fav_counts  = [];

if (!empty($buku_list)) {
    $ids_str = implode(",", array_column($buku_list, "id"));

    // Like count per buku
    $r = mysqli_query($conn, "SELECT buku_id, COUNT(*) AS total FROM buku_likes WHERE buku_id IN ($ids_str) GROUP BY buku_id");
    while ($row = mysqli_fetch_assoc($r)) $like_counts[$row["buku_id"]] = (int)$row["total"];

    // Fav count per buku
    $r = mysqli_query($conn, "SELECT buku_id, COUNT(*) AS total FROM buku_favorites WHERE buku_id IN ($ids_str) GROUP BY buku_id");
    while ($row = mysqli_fetch_assoc($r)) $fav_counts[$row["buku_id"]] = (int)$row["total"];

    if (!$is_admin) {
        // Status like user ini
        $r = mysqli_query($conn, "SELECT buku_id FROM buku_likes WHERE user_id=$user_id AND buku_id IN ($ids_str)");
        while ($row = mysqli_fetch_assoc($r)) $liked_ids[] = $row["buku_id"];

        // Status fav user ini
        $r = mysqli_query($conn, "SELECT buku_id FROM buku_favorites WHERE user_id=$user_id AND buku_id IN ($ids_str)");
        while ($row = mysqli_fetch_assoc($r)) $fav_ids[] = $row["buku_id"];
    }

    // Rating rata-rata per buku
    $rating_counts = [];
    $r = mysqli_query($conn, "SELECT buku_id, ROUND(AVG(rating),1) AS avg_r, COUNT(*) AS total FROM buku_ratings WHERE buku_id IN ($ids_str) GROUP BY buku_id");
    while ($row = mysqli_fetch_assoc($r)) $rating_counts[$row["buku_id"]] = ["avg" => (float)$row["avg_r"], "total" => (int)$row["total"]];
}

// Warna cover berputar
$cover_cls = ["c1","c2","c3","c4","c5","c6","c7","c8"];

// Status stok
function getStatus(int $stok): array {
    if ($stok <= 0) return ["habis",   "Kosong"];
    return ["tersedia","Ada"];
}
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
    .sidebar {
      width:var(--sidebar-w); min-height:100vh; background:var(--sidebar-bg);
      display:flex; flex-direction:column; padding:24px 0 20px;
      border-right:1px solid #e8e9f0;
      position:fixed; top:0; left:0; bottom:0; z-index:100; transition:transform var(--trans);
    }
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
    .nav-item.admin-only { color:#e67e22; }
    .nav-item.admin-only:hover { background:#fff4e6; color:#d35400; }
    .nav-bottom { padding:10px 10px 0; border-top:1px solid #f0f0f5; display:flex; flex-direction:column; gap:2px; }

    /* ── MAIN ── */
    .main { margin-left:var(--sidebar-w); flex:1; padding:24px 24px 32px; min-height:100vh; transition:margin-left var(--trans); }

    /* Topbar */
    .topbar { display:flex; gap:10px; margin-bottom:22px; align-items:center; }
    .search-wrap { display:flex; align-items:center; background:#fff; border-radius:50px; padding:0 16px; gap:10px; height:42px; border:1px solid #e4e5f0; flex:1; max-width:420px; box-shadow:0 2px 8px rgba(0,0,0,.04); }
    .search-wrap input { border:none; outline:none; font-family:'Nunito',sans-serif; font-size:.82rem; color:var(--text); background:transparent; flex:1; }
    .search-wrap input::placeholder { color:var(--muted); }
    .search-wrap svg { width:16px; height:16px; color:var(--muted); }
    .tab-btn { padding:9px 18px; border-radius:50px; border:1px solid #e4e5f0; background:#fff; font-family:'Nunito',sans-serif; font-size:.8rem; font-weight:600; color:var(--muted); cursor:pointer; transition:all var(--trans); text-decoration:none; }
    .tab-btn:hover { border-color:var(--accent); color:var(--accent); }

    /* Page header */
    .page-header { margin-bottom:18px; }
    .page-title { font-family:'Cormorant Garamond',serif; font-size:1.5rem; font-weight:700; color:var(--text); }
    .page-subtitle { font-size:.78rem; color:var(--muted); margin-top:2px; }

    /* Table card */
    .table-card { background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow-sm); overflow:hidden; animation:fadeUp .5s cubic-bezier(.22,1,.36,1) both; }
    @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
    table { width:100%; border-collapse:collapse; }
    thead { background:#f8f9ff; border-bottom:2px solid #eef0fc; }
    thead th { padding:13px 16px; font-size:.72rem; font-weight:800; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; text-align:left; white-space:nowrap; }
    tbody tr { border-bottom:1px solid #f0f1f8; transition:background var(--trans); }
    tbody tr:last-child { border-bottom:none; }
    tbody tr:hover { background:#f8f9ff; }
    td { padding:12px 16px; font-size:.82rem; vertical-align:middle; }

    /* Book identity */
    .book-identity { display:flex; align-items:center; gap:12px; }
    .book-cover-sm {
      width:40px; height:56px; border-radius:6px; flex-shrink:0;
      overflow:hidden; box-shadow:0 3px 10px rgba(0,0,0,.15); position:relative;
    }
    .book-cover-sm img { width:100%; height:100%; object-fit:cover; display:block; }
    .c1 { background:linear-gradient(135deg,#f5a623,#d4820a); }
    .c2 { background:linear-gradient(135deg,#9b59b6,#6c3483); }
    .c3 { background:linear-gradient(135deg,#e74c3c,#922b21); }
    .c4 { background:linear-gradient(135deg,#2ecc71,#1a8a4a); }
    .c5 { background:linear-gradient(135deg,#3498db,#1a5276); }
    .c6 { background:linear-gradient(135deg,#e91e63,#880e4f); }
    .c7 { background:linear-gradient(135deg,#ff5722,#bf360c); }
    .c8 { background:linear-gradient(135deg,#607d8b,#263238); }
    .book-cover-initial { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:.65rem; font-weight:800; color:rgba(255,255,255,.85); text-align:center; padding:2px; line-height:1.2; }

    .td-title  { font-weight:800; color:var(--text); margin-bottom:2px; }
    .td-isbn   { font-size:.68rem; color:var(--muted); }
    .td-author { font-weight:600; color:var(--text); }

    /* Status badge */
    .status-badge { display:inline-flex; align-items:center; gap:5px; font-size:.68rem; font-weight:700; padding:4px 10px; border-radius:50px; }
    .status-badge .dot { width:6px; height:6px; border-radius:50%; }
    .status-tersedia { background:#e8f5e9; color:#1a8a4a; }
    .status-tersedia .dot { background:#2ecc71; }
    .status-habis    { background:#fce4ec; color:#c0392b; }
    .status-habis .dot { background:#e74c3c; }

    .td-stock { font-weight:700; color:var(--text); }

    /* Rating kolom */
    .td-rating { display:flex; align-items:center; gap:4px; white-space:nowrap; }
    .td-rating svg { width:12px; height:12px; }
    .td-rating-num { font-size:.72rem; font-weight:800; color:#d4820a; }
    .td-rating-empty { font-size:.7rem; color:#ccc; letter-spacing:1px; }

    /* Empty state */
    .empty-row td { text-align:center; padding:50px; color:var(--muted); font-weight:600; font-size:.85rem; }

    /* Pagination */
    .pagination-wrap { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-top:1px solid #f0f1f8; background:#fafbff; }
    .pagination-info { font-size:.75rem; color:var(--muted); font-weight:600; }
    .pagination-btns { display:flex; gap:4px; }
    .page-btn { width:30px; height:30px; border-radius:8px; border:1px solid #e4e5f0; background:#fff; font-family:'Nunito',sans-serif; font-size:.78rem; font-weight:700; color:var(--muted); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all var(--trans); text-decoration:none; }
    .page-btn:hover { border-color:var(--accent); color:var(--accent); }
    .page-btn.active { background:var(--accent); color:#fff; border-color:var(--accent); }
    .page-btn svg { width:13px; height:13px; }

    /* Responsive */
    @media (max-width:900px) { .col-hide { display:none; } }
    @media (max-width:700px) { .sidebar { transform:translateX(-100%); } .sidebar.open { transform:translateX(0); } .sidebar-overlay.open { display:block; } .sidebar-toggle { display:flex; } .main { margin-left:0; padding:70px 14px 24px; } .search-wrap { max-width:100%; } }
    @media (max-width:500px) { .table-card { overflow-x:auto; } table { min-width:480px; } }

    /* Like & Simpan */
    .action-cell { display:flex; align-items:center; gap:6px; }
    .btn-like, .btn-save {
      display:inline-flex; align-items:center; gap:4px;
      padding:5px 10px; border-radius:50px; border:1.5px solid #e4e5f0;
      background:#fff; font-family:'Nunito',sans-serif;
      font-size:.7rem; font-weight:700; color:var(--muted);
      cursor:pointer; transition:all .18s; white-space:nowrap;
      user-select:none;
    }
    .btn-like svg, .btn-save svg { width:13px; height:13px; flex-shrink:0; transition:transform .2s; }
    .btn-like:hover  { border-color:#e74c3c; color:#e74c3c; background:#fff5f5; }
    .btn-save:hover  { border-color:#2b4fff; color:#2b4fff; background:#eef0ff; }
    .btn-like.aktif  { border-color:#e74c3c; color:#e74c3c; background:#fff0f0; }
    .btn-save.aktif  { border-color:#2b4fff; color:#2b4fff; background:#eef0ff; }
    .btn-like.aktif svg { fill:#e74c3c; color:#e74c3c; }
    .btn-save.aktif  svg { fill:#2b4fff; color:#2b4fff; }
    .btn-like.pop svg, .btn-save.pop svg { transform:scale(1.4); }

    /* ── MODAL DETAIL BUKU ── */
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
    .detail-stat-row { display:flex; gap:16px; margin-bottom:16px; }
    .detail-stat { display:flex; align-items:center; gap:6px; font-size:.78rem; font-weight:700; }
    .detail-stat svg { width:15px; height:15px; }
    .detail-stat.likes { color:#e74c3c; }
    .detail-stat.favs  { color:#2b4fff; }
    .detail-body .btn-save {
      display:inline-flex; align-items:center; gap:5px;
      padding:6px 14px; border-radius:50px; border:1.5px solid #e4e5f0;
      background:#fff; font-family:'Nunito',sans-serif; font-size:.75rem;
      font-weight:700; color:var(--muted); cursor:pointer; transition:all .18s;
      user-select:none;
    }
    .detail-body .btn-save svg { width:14px; height:14px; flex-shrink:0; transition:transform .2s; }
    .detail-body .btn-save:hover { border-color:#2b4fff; color:#2b4fff; background:#eef0ff; }
    .detail-body .btn-save.aktif { border-color:#2b4fff; color:#2b4fff; background:#eef0ff; }
    .detail-body .btn-save.aktif svg { fill:#2b4fff; color:#2b4fff; }
    .detail-body .btn-save.pop svg { transform:scale(1.4); }
    .detail-meta-row { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
    .detail-meta-chip { display:flex; align-items:center; gap:5px; background:#f8f9ff; border:1px solid #eef0fc; border-radius:8px; padding:6px 11px; font-size:.71rem; font-weight:700; color:var(--muted); }
    .detail-meta-chip svg { width:13px; height:13px; flex-shrink:0; }
    .detail-meta-chip span { color:var(--text); }
    .detail-section-label { font-size:.68rem; font-weight:800; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:7px; }
    .detail-sinopsis { font-size:.83rem; line-height:1.7; color:#3a3a5a; background:#f8f9ff; border-radius:10px; padding:14px 16px; border-left:3px solid var(--accent); }
    .detail-sinopsis-empty { color:var(--muted); font-style:italic; }

    /* Rating bintang */
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
    tbody tr { cursor:pointer; }
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
    <a href="daftar_buku.php" class="nav-item active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
      Daftar Buku
    </a>
    <?php if (!$is_admin): ?>
    <a href="buku_simpan.php" class="nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
      Buku Simpan
    </a>
    <?php endif; ?>
    <?php if ($is_admin): ?>
    <a href="halaman_admin.php" class="nav-item admin-only">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Action Admin
    </a>
    <?php endif; ?>
  </nav>

  <div class="nav-bottom">
    <a href="#" class="nav-item"  onclick="bukaSettings(); return false;" title="Pengaturan Tampilan">
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

  <!-- Search bar -->
  <form method="GET" action="daftar_buku.php">
    <div class="topbar">
      <div class="search-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="q" placeholder="Cari berdasarkan judul, penulis, atau ISBN…" value="<?= htmlspecialchars($search) ?>"/>
      </div>
      <button type="submit" class="tab-btn">Cari</button>
      <?php if ($search): ?><a href="daftar_buku.php" class="tab-btn">✕ Reset</a><?php endif; ?>
    </div>
  </form>

  <div class="page-header">
    <div class="page-title">Daftar Buku</div>
    <div class="page-subtitle">
      <?php if ($search): ?>
        <?= $total_buku ?> buku cocok dengan "<strong><?= htmlspecialchars($search) ?></strong>"
      <?php else: ?>
        <?= $total_buku ?> buku tersedia di katalog perpustakaan
      <?php endif; ?>
    </div>
  </div>

  <div class="table-card">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Buku</th>
          <th class="col-hide">Penulis</th>
          <th>Rating</th>
          <th>Stok</th>
          <th>Status</th>
          <?php if (!$is_admin): ?><th></th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($buku_list)): ?>
        <tr class="empty-row">
          <td colspan="6">
            <?= $search ? "Tidak ada buku yang cocok dengan \"" . htmlspecialchars($search) . "\"." : "Belum ada buku di katalog." ?>
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($buku_list as $i => $buku):
          $num   = $offset + $i + 1;
          $col   = $cover_cls[$i % count($cover_cls)];
          [$status_cls, $status_label] = getStatus((int)$buku["stok"]);
          // Inisial dari judul
          $words   = preg_split('/\s+/', trim($buku["judul"]));
          $initial = mb_strtoupper(mb_substr($words[0], 0, 1)) . (isset($words[1]) ? mb_strtoupper(mb_substr($words[1], 0, 1)) : "");
        ?>
        <tr onclick="bukaDetailBuku(<?= $buku['id'] ?>)" style="cursor:pointer;">
          <td style="font-weight:800;color:var(--muted);font-size:.78rem;"><?= str_pad($num, 2, "0", STR_PAD_LEFT) ?></td>
          <td>
            <div class="book-identity">
              <div class="book-cover-sm <?= $col ?>">
                <?php if ($buku["gambar"] && file_exists($buku["gambar"])): ?>
                  <img src="<?= htmlspecialchars($buku["gambar"]) ?>" alt="<?= htmlspecialchars($buku["judul"]) ?>">
                <?php else: ?>
                  <div class="book-cover-initial"><?= htmlspecialchars($initial) ?></div>
                <?php endif; ?>
              </div>
              <div>
                <div class="td-title"><?= htmlspecialchars($buku["judul"]) ?></div>
                <div class="td-isbn"><?= $buku["isbn"] ? "ISBN " . htmlspecialchars($buku["isbn"]) : "" ?></div>
              </div>
            </div>
          </td>
          <td class="col-hide"><span class="td-author"><?= htmlspecialchars($buku["penulis"] ?: "—") ?></span></td>
          <td onclick="event.stopPropagation()">
            <?php
              $rat = $rating_counts[$buku["id"]] ?? null;
              if ($rat && $rat["total"] > 0):
                $full = floor($rat["avg"]);
            ?>
            <div class="td-rating">
              <?php for ($s=1;$s<=5;$s++): ?>
                <svg viewBox="0 0 24 24" fill="<?= $s<=$full?'#f5a623':'none' ?>" stroke="#f5a623" stroke-width="2" stroke-linejoin="round">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
              <?php endfor; ?>
              <span class="td-rating-num"><?= $rat["avg"] ?></span>
            </div>
            <?php else: ?>
            <span class="td-rating-empty">—</span>
            <?php endif; ?>
          </td>
          <td><span class="td-stock"><?= (int)$buku["stok"] ?></span></td>
          <td><span class="status-badge status-<?= $status_cls ?>"><span class="dot"></span><?= $status_label ?></span></td>
          <?php if (!$is_admin):
            $sudah_like = in_array($buku["id"], $liked_ids);
            $sudah_fav  = in_array($buku["id"], $fav_ids);
            $jml_like   = $like_counts[$buku["id"]] ?? 0;
            $jml_fav    = $fav_counts[$buku["id"]]  ?? 0;
          ?>
          <td onclick="event.stopPropagation()">
            <div class="action-cell">
              <button class="btn-like <?= $sudah_like ? 'aktif' : '' ?>"
                      onclick="toggleAksi(this, <?= $buku['id'] ?>, 'like')"
                      title="Suka">
                <svg viewBox="0 0 24 24" fill="<?= $sudah_like ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <span class="like-count-<?= $buku['id'] ?>"><?= $jml_like ?></span>
              </button>
              <button class="btn-save <?= $sudah_fav ? 'aktif' : '' ?>"
                      onclick="toggleAksi(this, <?= $buku['id'] ?>, 'favorite')"
                      title="Simpan">
                <svg viewBox="0 0 24 24" fill="<?= $sudah_fav ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                  <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                </svg>
                Simpan
              </button>
            </div>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <?php
    $from = $total_buku === 0 ? 0 : $offset + 1;
    $to   = min($offset + $per_page, $total_buku);
    $qs   = $search ? "&q=" . urlencode($search) : "";
    ?>
    <div class="pagination-wrap">
      <div class="pagination-info">Menampilkan <?= $from ?>–<?= $to ?> dari <?= $total_buku ?> buku</div>
      <div class="pagination-btns">
        <!-- Prev -->
        <?php if ($page > 1): ?>
          <a href="?page=<?= $page - 1 . $qs ?>" class="page-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
          </a>
        <?php endif; ?>

        <?php
        // Show a window of pages
        $start = max(1, $page - 2);
        $end   = min($total_pages, $page + 2);
        for ($p = $start; $p <= $end; $p++):
        ?>
          <a href="?page=<?= $p . $qs ?>" class="page-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>

        <!-- Next -->
        <?php if ($page < $total_pages): ?>
          <a href="?page=<?= $page + 1 . $qs ?>" class="page-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

</main>

<!-- ═══════════ MODAL DETAIL BUKU ═══════════ -->
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

<script>
  const toggle  = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  toggle.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('open'); });
  overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); });

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

  function tutupDetailBuku() {
    document.getElementById('detailOverlay').classList.remove('open');
  }

  document.getElementById('detailOverlay').addEventListener('click', function(e) {
    if (e.target === this) tutupDetailBuku();
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') tutupDetailBuku();
  });

  function renderDetail(b) {
    const content = document.getElementById('detailContent');
    const stokLabel = b.stok == 0 ? 'Habis' : (b.stok <= 3 ? 'Terbatas' : 'Tersedia');
    const stokColor = b.stok == 0 ? '#e74c3c' : (b.stok <= 3 ? '#f39c12' : '#27ae60');
    const tglInput = b.created_at
      ? new Date(b.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'})
      : '—';
    const tglUpdate = b.updated_at && b.updated_at !== b.created_at
      ? new Date(b.updated_at).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'})
      : null;

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

  function escHTML(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // ─── Rating helpers ───
  function starSVG(filled) {
    const f = filled ? '#f5a623' : 'none';
    return `<svg viewBox="0 0 24 24" fill="${f}" stroke="#f5a623" stroke-width="2" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`;
  }

  function renderStarsDisplay(avg, total) {
    let stars = '';
    for (let i = 1; i <= 5; i++) stars += starSVG(i <= Math.round(avg));
    const label = total > 0 ? `${avg} (${total} ulasan)` : 'Belum ada rating';
    return `<div class="stars-display">${stars}</div><span class="rating-text">${label}</span>`;
  }

  function renderStarsInput(bukuId, userRating) {
    let html = `<div class="stars-input" id="starsInput_${bukuId}">`;
    for (let i = 1; i <= 5; i++) {
      html += `<svg viewBox="0 0 24 24" fill="${i <= userRating ? '#f5a623' : 'none'}" stroke="#f5a623" stroke-width="2" stroke-linejoin="round"
        class="${i <= userRating ? 'aktif' : ''}"
        onmouseover="hoverStar(${bukuId},${i})"
        onmouseout="resetStarHover(${bukuId})"
        onclick="submitRating(${bukuId},${i})">
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
      </svg>`;
    }
    return html + '</div>';
  }

  function hoverStar(bukuId, n) {
    const stars = document.querySelectorAll(`#starsInput_${bukuId} svg`);
    stars.forEach((s, i) => {
      s.setAttribute('fill', i < n ? '#f5a623' : 'none');
      s.classList.toggle('hover', i < n);
    });
  }

  function resetStarHover(bukuId) {
    const stars = document.querySelectorAll(`#starsInput_${bukuId} svg`);
    stars.forEach(s => {
      s.classList.remove('hover');
      s.setAttribute('fill', s.classList.contains('aktif') ? '#f5a623' : 'none');
    });
  }

  function submitRating(bukuId, rating) {
    const fd = new FormData();
    fd.append('buku_id', bukuId);
    fd.append('rating', rating);
    fetch('rating_handler.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => {
        if (!data.ok) return;
        const stars = document.querySelectorAll(`#starsInput_${bukuId} svg`);
        stars.forEach((s, i) => {
          const on = i < data.user_rating;
          s.setAttribute('fill', on ? '#f5a623' : 'none');
          s.classList.toggle('aktif', on);
        });
        const row = document.getElementById(`ratingRow_${bukuId}`);
        if (row) {
          const disp = row.querySelector('.stars-display');
          const txt  = row.querySelector('.rating-text');
          if (disp && txt) {
            let s = '';
            for (let i = 1; i <= 5; i++) s += starSVG(i <= Math.round(data.avg));
            disp.innerHTML = s;
            txt.textContent = `${data.avg} (${data.total} ulasan)`;
          }
        }
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
        btn.classList.toggle('aktif', data.aktif);
        const svg = btn.querySelector('svg');
        svg.setAttribute('fill', data.aktif ? 'currentColor' : 'none');
        btn.innerHTML = `<svg viewBox="0 0 24 24" fill="${data.aktif ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>${data.aktif ? 'Tersimpan' : 'Simpan'}`;
        btn.classList.toggle('aktif', data.aktif);
        btn.classList.add('pop');
        setTimeout(() => btn.classList.remove('pop'), 200);
        // Sinkronkan juga tombol di tabel jika ada
        const tableBtn = document.querySelector(`.btn-save[onclick*="toggleAksi"][onclick*="${buku_id}"]`);
        if (tableBtn) {
          tableBtn.classList.toggle('aktif', data.aktif);
          const tSvg = tableBtn.querySelector('svg');
          if (tSvg) tSvg.setAttribute('fill', data.aktif ? 'currentColor' : 'none');
        }
      });
  }
  function toggleAksi(btn, buku_id, type) {
    const fd = new FormData();
    fd.append('buku_id', buku_id);
    fd.append('type', type);

    fetch('like_handler.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => {
        if (!data.ok) return;
        btn.classList.toggle('aktif', data.aktif);
        const svg = btn.querySelector('svg');
        svg.setAttribute('fill', data.aktif ? 'currentColor' : 'none');
        btn.classList.add('pop');
        setTimeout(() => btn.classList.remove('pop'), 200);
        const span = btn.querySelector('span');
        if (span) span.textContent = data.total;
      });
  }
</script>
</body>
</html>