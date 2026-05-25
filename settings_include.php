<?php
// settings_include.php — Sertakan di <head> semua halaman (sebelum </head>)
// Terapkan preferensi tema dari localStorage via inline script agar tidak flash
?>
<script>
(function(){
  // Baca settings dari localStorage
  var s;
  try { s = JSON.parse(localStorage.getItem('aksanova_settings') || '{}'); } catch(e){ s={}; }

  var root = document.documentElement;

  // ── Tema / Mode ──
  var mode     = s.mode     || 'light';      // light | dark
  var ui       = s.ui       || 'default';    // default | minimal | modern | kuno | gradasi
  var grad     = s.gradient || 'biru';       // biru | ungu | hijau | merah | emas | custom
  var fontFam  = s.font     || 'Nunito';     // Nunito | Merriweather | Poppins | Playfair | Roboto Mono
  var fontSize = parseInt(s.fontSize || 14);
  var fontW    = s.fontWeight || 'normal';   // light | normal | bold | extrabold
  var radius   = s.radius   || '14';        // px number string
  var spacing  = s.spacing  || 'normal';    // compact | normal | relaxed
  var animation= s.animation!==undefined ? s.animation : true;
  var sidebar  = s.sidebar  || 'full';      // full | icon

  // Palettenya
  var palettes = {
    biru:   { accent:'#2b4fff', accent2:'#ffb800', accentRgb:'43,79,255' },
    ungu:   { accent:'#7c3aed', accent2:'#f59e0b', accentRgb:'124,58,237' },
    hijau:  { accent:'#059669', accent2:'#fbbf24', accentRgb:'5,150,105' },
    merah:  { accent:'#dc2626', accent2:'#f59e0b', accentRgb:'220,38,38' },
    emas:   { accent:'#b45309', accent2:'#2b4fff', accentRgb:'180,83,9' },
    merah_muda: { accent:'#db2777', accent2:'#7c3aed', accentRgb:'219,39,119' },
  };
  var pal = palettes[grad] || palettes.biru;

  // ── Mode gelap/terang & UI ──
  var uiVars = {
    default: {
      light: { bg:'#f4f5f7', sidebarBg:'#ffffff', card:'#ffffff', text:'#1a1a2e', muted:'#7a7a9a', border:'#e8e9f0', cardBorder:'#eef0fc', bookCard:'#f8f9ff' },
      dark:  { bg:'#0f0f1a', sidebarBg:'#14142a', card:'#1a1a2e', text:'#e8e8f5', muted:'#8888aa', border:'#2a2a40', cardBorder:'#252540', bookCard:'#1e1e30' },
    },
    minimal: {
      light: { bg:'#fafafa', sidebarBg:'#f5f5f5', card:'#ffffff', text:'#111111', muted:'#999999', border:'#e0e0e0', cardBorder:'#ebebeb', bookCard:'#f5f5f5' },
      dark:  { bg:'#111111', sidebarBg:'#191919', card:'#222222', text:'#eeeeee', muted:'#888888', border:'#333333', cardBorder:'#2a2a2a', bookCard:'#1e1e1e' },
    },
    modern: {
      light: { bg:'#f0f2ff', sidebarBg:'#ffffff', card:'#ffffff', text:'#1a1a35', muted:'#6b7280', border:'#dde0ff', cardBorder:'#e8eaff', bookCard:'#f5f6ff' },
      dark:  { bg:'#0d0d1f', sidebarBg:'#12122a', card:'#18183a', text:'#e0e0ff', muted:'#7777aa', border:'#25254a', cardBorder:'#20203e', bookCard:'#1c1c38' },
    },
    kuno: {
      light: { bg:'#f5efe6', sidebarBg:'#fdf6eb', card:'#fffdf5', text:'#2c1810', muted:'#8b7355', border:'#d4c09a', cardBorder:'#e8dcc0', bookCard:'#f9f0dc' },
      dark:  { bg:'#1a1008', sidebarBg:'#221508', card:'#2a1a08', text:'#f0e0c0', muted:'#a08050', border:'#4a3020', cardBorder:'#3a2510', bookCard:'#251508' },
    },
    gradasi: {
      light: { bg:'linear-gradient(135deg,#f0f4ff 0%,#fdf0ff 100%)', sidebarBg:'#ffffff', card:'#ffffff', text:'#1a1a2e', muted:'#7a7a9a', border:'#e8e9f0', cardBorder:'#eef0fc', bookCard:'rgba(255,255,255,.7)' },
      dark:  { bg:'linear-gradient(135deg,#0a0a20 0%,#1a0a2e 100%)', sidebarBg:'#14142a', card:'rgba(26,26,50,.95)', text:'#e8e8f5', muted:'#8888aa', border:'#2a2a45', cardBorder:'#25254a', bookCard:'rgba(30,30,55,.9)' },
    },
  };

  var uiKey   = uiVars[ui] ? ui : 'default';
  var modeKey = mode === 'dark' ? 'dark' : 'light';
  var colors  = uiVars[uiKey][modeKey];

  // Apply background (gradient atau solid)
  if (colors.bg.startsWith('linear')) {
    root.style.setProperty('--bg', colors.bg);
    document.documentElement.style.background = colors.bg;
  } else {
    root.style.setProperty('--bg', colors.bg);
  }
  root.style.setProperty('--sidebar-bg', colors.sidebarBg);
  root.style.setProperty('--card', colors.card);
  root.style.setProperty('--text', colors.text);
  root.style.setProperty('--muted', colors.muted);
  root.style.setProperty('--border-color', colors.border);
  root.style.setProperty('--card-border', colors.cardBorder);
  root.style.setProperty('--book-card', colors.bookCard);

  // Accent warna
  root.style.setProperty('--accent', pal.accent);
  root.style.setProperty('--accent2', pal.accent2);
  root.style.setProperty('--accent-rgb', pal.accentRgb);

  // Font keluarga
  var fontMap = {
    'Nunito':       "'Nunito', sans-serif",
    'Merriweather': "'Merriweather', serif",
    'Poppins':      "'Poppins', sans-serif",
    'Playfair':     "'Playfair Display', serif",
    'Roboto Mono':  "'Roboto Mono', monospace",
  };
  root.style.setProperty('--font-family', fontMap[fontFam] || fontMap['Nunito']);

  // Font size base
  root.style.setProperty('--font-size-base', fontSize + 'px');

  // Font weight
  var weightMap = { light:'300', normal:'400', bold:'600', extrabold:'800' };
  root.style.setProperty('--font-weight-base', weightMap[fontW] || '400');

  // Border radius
  root.style.setProperty('--radius', radius + 'px');

  // Spacing
  var spMap = { compact:'0.6rem', normal:'1rem', relaxed:'1.5rem' };
  root.style.setProperty('--spacing', spMap[spacing] || '1rem');

  // Animation
  root.style.setProperty('--trans-speed', animation ? '.2s' : '0s');

  // Sidebar lebar
  root.style.setProperty('--sidebar-w', sidebar === 'icon' ? '64px' : '170px');

  // Class pada html
  root.className = [mode, 'ui-' + uiKey, 'sidebar-' + sidebar].join(' ');
})();
</script>
<style>
  /* ── Variabel dasar yang selalu ada (fallback) ── */
  :root {
    --font-family:      'Nunito', sans-serif;
    --font-size-base:   14px;
    --font-weight-base: 400;
    --radius:           14px;
    --spacing:          1rem;
    --trans-speed:      .2s;
    --accent-rgb:       43,79,255;
    --border-color:     #e8e9f0;
    --card-border:      #eef0fc;
    --book-card:        #f8f9ff;
  }
  /* Paksa font & size ke body */
  body {
    font-family: var(--font-family) !important;
    font-size:   var(--font-size-base) !important;
    font-weight: var(--font-weight-base) !important;
  }
  /* Sidebar border */
  .sidebar { border-right: 1px solid var(--border-color) !important; }
  /* Card border */
  .book-card { background: var(--book-card) !important; border-color: var(--card-border) !important; }
  .section-card, .stats-card, .admin-card { background: var(--card) !important; }

  /* ── Mode gelap — override warna utama ── */
  html.dark body         { background: var(--bg) !important; color: var(--text) !important; }
  html.dark .sidebar     { background: var(--sidebar-bg) !important; }
  html.dark .section-card,
  html.dark .stats-card,
  html.dark .admin-card  { background: var(--card) !important; box-shadow: 0 2px 12px rgba(0,0,0,.3) !important; }
  html.dark .search-wrap { background: var(--card) !important; border-color: var(--border-color) !important; }
  html.dark .tab-btn     { background: var(--card) !important; border-color: var(--border-color) !important; color: var(--text) !important; }
  html.dark input        { color: var(--text) !important; background: transparent !important; }
  html.dark .book-card   { background: var(--book-card) !important; border-color: var(--card-border) !important; }
  html.dark .detail-modal{ background: var(--card) !important; }
  html.dark .detail-sinopsis { background: rgba(255,255,255,.05) !important; color: var(--text) !important; }
  html.dark .detail-meta-chip{ background: rgba(255,255,255,.07) !important; border-color: var(--card-border) !important; color: var(--muted) !important; }
  html.dark .stat-box    { background: rgba(255,255,255,.08) !important; }
  html.dark .nav-item    { color: var(--muted) !important; }
  html.dark .nav-item:hover, html.dark .nav-item.active { background: rgba(255,255,255,.08) !important; color: var(--accent) !important; }
  html.dark .logo-name   { color: var(--text) !important; }
  html.dark .section-title{ color: var(--text) !important; }
  html.dark .book-title  { color: var(--text) !important; }
  html.dark .admin-name  { color: var(--text) !important; }
  html.dark .stat-num    { color: var(--text) !important; }
  html.dark .detail-title{ color: var(--text) !important; }

  /* ── UI Minimal ── */
  html.ui-minimal .section-card,
  html.ui-minimal .stats-card,
  html.ui-minimal .admin-card { box-shadow: none !important; border: 1px solid var(--border-color) !important; }
  html.ui-minimal .book-card  { border-radius: 6px !important; }
  html.ui-minimal .book-cover { border-radius: 4px !important; }
  html.ui-minimal .sidebar    { box-shadow: none !important; }
  html.ui-minimal .logo-icon  { box-shadow: none !important; border: 1px solid var(--border-color); }

  /* ── UI Modern ── */
  html.ui-modern .section-card,
  html.ui-modern .stats-card  { border-radius: 20px !important; }
  html.ui-modern .book-card   { border-radius: 16px !important; }
  html.ui-modern .sidebar     { border-radius: 0 24px 24px 0 !important; }
  html.ui-modern .nav-item    { border-radius: 14px !important; }
  html.ui-modern .search-wrap { border-radius: 16px !important; }

  /* ── UI Kuno ── */
  html.ui-kuno body           { background: var(--bg) !important; }
  html.ui-kuno .section-card,
  html.ui-kuno .stats-card,
  html.ui-kuno .admin-card    { border: 2px solid var(--border-color) !important; border-radius: 4px !important; box-shadow: 4px 4px 0 var(--border-color) !important; }
  html.ui-kuno .book-card     { border-radius: 4px !important; border: 1px solid var(--border-color) !important; }
  html.ui-kuno .sidebar       { border-radius: 0 !important; border-right: 2px solid var(--border-color) !important; }
  html.ui-kuno .logo-name     { letter-spacing: .2em; font-size: .85rem !important; }
  html.ui-kuno .book-cover    { border-radius: 2px !important; border: 1px solid var(--border-color) !important; }
  html.ui-kuno .tab-btn       { border-radius: 2px !important; }
  html.ui-kuno .nav-item      { border-radius: 0 !important; border-left: 3px solid transparent; }
  html.ui-kuno .nav-item.active,
  html.ui-kuno .nav-item:hover { border-left-color: var(--accent) !important; background: rgba(0,0,0,.04) !important; }

  /* ── UI Gradasi ── */
  html.ui-gradasi body  { background: var(--bg) !important; min-height: 100vh; }
  html.ui-gradasi .section-card,
  html.ui-gradasi .stats-card  {
    background: rgba(255,255,255,.7) !important;
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,.4) !important;
  }
  html.ui-gradasi.dark .section-card,
  html.ui-gradasi.dark .stats-card {
    background: rgba(30,30,60,.7) !important;
    border-color: rgba(255,255,255,.1) !important;
  }
  html.ui-gradasi .sidebar {
    background: rgba(255,255,255,.8) !important;
    backdrop-filter: blur(16px);
  }
  html.ui-gradasi.dark .sidebar {
    background: rgba(20,20,42,.85) !important;
  }

  /* ── Sidebar icon mode ── */
  html.sidebar-icon .logo-name,
  html.sidebar-icon .logo-sub,
  html.sidebar-icon .nav-item span,
  html.sidebar-icon .nav-bottom .nav-text { display: none !important; }
  html.sidebar-icon .nav-item { justify-content: center; padding: 10px !important; }
  html.sidebar-icon .logo-wrap { padding: 0 8px 20px; }
  html.sidebar-icon .logo-icon { margin: 0 auto; }

  /* ── Spacing compact/relaxed ── */
  html body .section-card, html body .stats-card { padding: calc(var(--spacing) * 1.1) !important; }

  /* Transitions */
  body, .sidebar, .section-card, .stats-card, .admin-card, .book-card, .nav-item {
    transition: background var(--trans-speed) ease, color var(--trans-speed) ease !important;
  }
</style>