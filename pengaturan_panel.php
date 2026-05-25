<?php
// pengaturan_panel.php — Panel pengaturan slide-in
// Sertakan di SEMUA halaman, taruh sebelum </body>
// Tidak butuh session atau database — semua disimpan di localStorage
?>

<!-- ═══════════════════════════════════════════════════════
     PENGATURAN — Tombol trigger di sidebar
     Ganti href="#" pada nav-item Pengaturan menjadi:
     onclick="bukaSettings(); return false;"
 ═══════════════════════════════════════════════════════ -->

<!-- Panel Backdrop -->
<div id="settingsBackdrop" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:800;backdrop-filter:blur(3px);transition:opacity .3s;" onclick="tutupSettings()"></div>

<!-- Panel Utama -->
<aside id="settingsPanel" style="
  position:fixed; top:0; right:-420px; bottom:0; width:400px; max-width:95vw;
  background:var(--card,#fff); z-index:900;
  box-shadow:-8px 0 40px rgba(0,0,0,.18);
  display:flex; flex-direction:column;
  transition:right .35s cubic-bezier(.22,1,.36,1);
  font-family:var(--font-family,'Nunito',sans-serif);
  overflow:hidden;
">

  <!-- Header panel -->
  <div style="padding:20px 22px 16px; border-bottom:1px solid var(--border-color,#e8e9f0); display:flex; align-items:center; gap:12px; flex-shrink:0;">
    <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--accent,#2b4fff),var(--accent2,#ffb800));display:flex;align-items:center;justify-content:center;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
    </div>
    <div style="flex:1;">
      <div style="font-size:.95rem;font-weight:800;color:var(--text,#1a1a2e);">Pengaturan Tampilan</div>
      <div style="font-size:.7rem;color:var(--muted,#7a7a9a);margin-top:1px;">Semua perubahan berlaku di seluruh halaman</div>
    </div>
    <button onclick="tutupSettings()" style="width:32px;height:32px;border:none;background:rgba(0,0,0,.06);border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;" title="Tutup">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>

  <!-- Scroll body -->
  <div style="flex:1;overflow-y:auto;padding:18px 22px;display:flex;flex-direction:column;gap:22px;">

    <!-- ── Seksi 1: Mode Terang/Gelap ── -->
    <div class="sett-section">
      <div class="sett-label">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        Mode Tampilan
      </div>
      <div class="sett-toggle-row">
        <button class="mode-btn" id="modeLight" onclick="setMode('light')">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/></svg>
          Terang
        </button>
        <button class="mode-btn" id="modeDark" onclick="setMode('dark')">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
          Gelap
        </button>
      </div>
    </div>

    <!-- ── Seksi 2: Gaya UI ── -->
    <div class="sett-section">
      <div class="sett-label">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18M9 21V9"/></svg>
        Gaya Antarmuka
      </div>
      <div class="ui-grid">
        <button class="ui-btn" id="ui-default" onclick="setUI('default')">
          <div class="ui-preview" style="background:linear-gradient(135deg,#f4f5f7,#fff);">
            <div style="width:30%;height:100%;background:#fff;border-right:1px solid #e8e9f0;"></div>
            <div style="flex:1;padding:4px;display:flex;flex-direction:column;gap:3px;">
              <div style="height:8px;background:#eef0ff;border-radius:3px;"></div>
              <div style="height:8px;background:#eef0ff;border-radius:3px;width:70%;"></div>
            </div>
          </div>
          <span>Default</span>
        </button>
        <button class="ui-btn" id="ui-minimal" onclick="setUI('minimal')">
          <div class="ui-preview" style="background:#fafafa;">
            <div style="width:30%;height:100%;background:#f5f5f5;border-right:1px solid #e0e0e0;"></div>
            <div style="flex:1;padding:4px;display:flex;flex-direction:column;gap:3px;">
              <div style="height:8px;background:#ebebeb;border-radius:1px;"></div>
              <div style="height:8px;background:#ebebeb;border-radius:1px;width:60%;"></div>
            </div>
          </div>
          <span>Minimalis</span>
        </button>
        <button class="ui-btn" id="ui-modern" onclick="setUI('modern')">
          <div class="ui-preview" style="background:#f0f2ff;">
            <div style="width:30%;height:100%;background:#fff;border-radius:0 10px 10px 0;"></div>
            <div style="flex:1;padding:4px;display:flex;flex-direction:column;gap:3px;">
              <div style="height:8px;background:#e8eaff;border-radius:6px;"></div>
              <div style="height:8px;background:#e8eaff;border-radius:6px;width:75%;"></div>
            </div>
          </div>
          <span>Modern</span>
        </button>
        <button class="ui-btn" id="ui-kuno" onclick="setUI('kuno')">
          <div class="ui-preview" style="background:#f5efe6;">
            <div style="width:30%;height:100%;background:#fdf6eb;border-right:2px solid #d4c09a;"></div>
            <div style="flex:1;padding:4px;display:flex;flex-direction:column;gap:3px;">
              <div style="height:8px;background:#e8dcc0;border-radius:1px;border:1px solid #d4c09a;"></div>
              <div style="height:8px;background:#e8dcc0;border-radius:1px;border:1px solid #d4c09a;width:65%;"></div>
            </div>
          </div>
          <span>Kuno</span>
        </button>
        <button class="ui-btn" id="ui-gradasi" onclick="setUI('gradasi')">
          <div class="ui-preview" style="background:linear-gradient(135deg,#f0f4ff,#fdf0ff);">
            <div style="width:30%;height:100%;background:rgba(255,255,255,.8);backdrop-filter:blur(4px);"></div>
            <div style="flex:1;padding:4px;display:flex;flex-direction:column;gap:3px;">
              <div style="height:8px;background:rgba(255,255,255,.6);border-radius:4px;"></div>
              <div style="height:8px;background:rgba(255,255,255,.6);border-radius:4px;width:80%;"></div>
            </div>
          </div>
          <span>Gradasi</span>
        </button>
      </div>
    </div>

    <!-- ── Seksi 3: Gradasi Warna / Accent ── -->
    <div class="sett-section">
      <div class="sett-label">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r="1.5"/><circle cx="17.5" cy="10.5" r="1.5"/><circle cx="8.5" cy="7.5" r="1.5"/><circle cx="6.5" cy="12.5" r="1.5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
        Warna Aksen
      </div>
      <div class="color-row">
        <button class="color-dot" id="grad-biru"      onclick="setGrad('biru')"      style="background:#2b4fff;" title="Biru"></button>
        <button class="color-dot" id="grad-ungu"      onclick="setGrad('ungu')"      style="background:#7c3aed;" title="Ungu"></button>
        <button class="color-dot" id="grad-hijau"     onclick="setGrad('hijau')"     style="background:#059669;" title="Hijau"></button>
        <button class="color-dot" id="grad-merah"     onclick="setGrad('merah')"     style="background:#dc2626;" title="Merah"></button>
        <button class="color-dot" id="grad-emas"      onclick="setGrad('emas')"      style="background:#b45309;" title="Emas/Coklat"></button>
        <button class="color-dot" id="grad-merah_muda" onclick="setGrad('merah_muda')" style="background:#db2777;" title="Merah Muda"></button>
      </div>
    </div>

    <!-- ── Seksi 4: Font ── -->
    <div class="sett-section">
      <div class="sett-label">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
        Font Teks
      </div>
      <div class="font-list">
        <button class="font-btn" id="font-Nunito"       onclick="setFont('Nunito')"       style="font-family:'Nunito',sans-serif;">Nunito — Modern Rounded</button>
        <button class="font-btn" id="font-Poppins"      onclick="setFont('Poppins')"      style="font-family:'Poppins',sans-serif;">Poppins — Clean Geometric</button>
        <button class="font-btn" id="font-Merriweather" onclick="setFont('Merriweather')" style="font-family:'Merriweather',serif;">Merriweather — Klasik Serif</button>
        <button class="font-btn" id="font-Playfair"     onclick="setFont('Playfair Display')" style="font-family:'Playfair Display',serif;">Playfair — Elegan Editorial</button>
        <button class="font-btn" id="font-Roboto Mono"  onclick="setFont('Roboto Mono')"  style="font-family:'Roboto Mono',monospace;">Roboto Mono — Tech Monospace</button>
      </div>
    </div>

    <!-- ── Seksi 5: Ukuran Teks ── -->
    <div class="sett-section">
      <div class="sett-label">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/></svg>
        Ukuran Teks — <span id="fontSizeLabel" style="color:var(--accent,#2b4fff);font-weight:800;">14px</span>
      </div>
      <div style="display:flex;align-items:center;gap:10px;">
        <span style="font-size:.65rem;color:var(--muted,#7a7a9a);font-weight:700;">A</span>
        <input type="range" id="fontSizeRange" min="11" max="20" step="1" value="14"
               style="flex:1;height:4px;accent-color:var(--accent,#2b4fff);cursor:pointer;"
               oninput="setFontSize(this.value)">
        <span style="font-size:.85rem;color:var(--muted,#7a7a9a);font-weight:800;">A</span>
      </div>
    </div>

    <!-- ── Seksi 6: Ketebalan Teks ── -->
    <div class="sett-section">
      <div class="sett-label">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/></svg>
        Ketebalan Teks
      </div>
      <div class="sett-chip-row">
        <button class="chip-btn" id="fw-light"    onclick="setFontWeight('light')">Light</button>
        <button class="chip-btn" id="fw-normal"   onclick="setFontWeight('normal')">Normal</button>
        <button class="chip-btn" id="fw-bold"     onclick="setFontWeight('bold')">Bold</button>
        <button class="chip-btn" id="fw-extrabold" onclick="setFontWeight('extrabold')">Extra Bold</button>
      </div>
    </div>

    <!-- ── Seksi 7: Border Radius ── -->
    <div class="sett-section">
      <div class="sett-label">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/></svg>
        Sudut Elemen — <span id="radiusLabel" style="color:var(--accent,#2b4fff);font-weight:800;">14px</span>
      </div>
      <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:12px;height:12px;border:2px solid var(--muted,#7a7a9a);border-radius:1px;flex-shrink:0;"></div>
        <input type="range" id="radiusRange" min="0" max="28" step="2" value="14"
               style="flex:1;height:4px;accent-color:var(--accent,#2b4fff);cursor:pointer;"
               oninput="setRadius(this.value)">
        <div style="width:12px;height:12px;border:2px solid var(--muted,#7a7a9a);border-radius:50%;flex-shrink:0;"></div>
      </div>
    </div>

    <!-- ── Seksi 8: Spasi ── -->
    <div class="sett-section">
      <div class="sett-label">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        Kepadatan Spasi
      </div>
      <div class="sett-chip-row">
        <button class="chip-btn" id="sp-compact"  onclick="setSpacing('compact')">Padat</button>
        <button class="chip-btn" id="sp-normal"   onclick="setSpacing('normal')">Normal</button>
        <button class="chip-btn" id="sp-relaxed"  onclick="setSpacing('relaxed')">Lapang</button>
      </div>
    </div>

    <!-- ── Seksi 9: Animasi ── -->
    <div class="sett-section">
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <div class="sett-label" style="margin:0;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 3l14 9-14 9V3z"/></svg>
          Animasi & Transisi
        </div>
        <label class="toggle-switch">
          <input type="checkbox" id="animToggle" checked onchange="setAnimation(this.checked)">
          <span class="toggle-track"><span class="toggle-thumb"></span></span>
        </label>
      </div>
    </div>

    <!-- ── Seksi 10: Sidebar ── -->
    <div class="sett-section">
      <div class="sett-label">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
        Mode Sidebar
      </div>
      <div class="sett-chip-row">
        <button class="chip-btn" id="sb-full" onclick="setSidebar('full')">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
          Penuh
        </button>
        <button class="chip-btn" id="sb-icon" onclick="setSidebar('icon')">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="3" width="12" height="18" rx="2"/></svg>
          Ikon Saja
        </button>
      </div>
    </div>

  </div><!-- /scroll body -->

  <!-- Footer panel -->
  <div style="padding:14px 22px;border-top:1px solid var(--border-color,#e8e9f0);display:flex;gap:10px;flex-shrink:0;">
    <button onclick="resetSettings()" style="flex:1;padding:10px;border-radius:10px;border:1px solid var(--border-color,#e8e9f0);background:transparent;color:var(--muted,#7a7a9a);font-family:var(--font-family,'Nunito',sans-serif);font-size:.78rem;font-weight:700;cursor:pointer;">
      ↺ Reset Default
    </button>
    <button onclick="tutupSettings()" style="flex:1;padding:10px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--accent,#2b4fff),var(--accent2,#ffb800));color:#fff;font-family:var(--font-family,'Nunito',sans-serif);font-size:.78rem;font-weight:800;cursor:pointer;">
      Simpan & Tutup
    </button>
  </div>

</aside>

<!-- ═══════ STYLE PANEL ═══════ -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Playfair+Display:wght@400;700&family=Roboto+Mono:wght@400;700&display=swap');

.sett-section {
  display:flex; flex-direction:column; gap:10px;
}
.sett-label {
  display:flex; align-items:center; gap:7px;
  font-size:.72rem; font-weight:800;
  color:var(--muted,#7a7a9a);
  text-transform:uppercase; letter-spacing:.07em;
}
.sett-toggle-row {
  display:flex; gap:8px;
}
.mode-btn {
  flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
  padding:10px; border-radius:10px;
  border:2px solid var(--border-color,#e8e9f0);
  background:transparent;
  font-family:var(--font-family,'Nunito',sans-serif);
  font-size:.8rem; font-weight:700;
  color:var(--text,#1a1a2e);
  cursor:pointer; transition:all .2s;
}
.mode-btn.aktif {
  border-color:var(--accent,#2b4fff);
  background:rgba(43,79,255,.08);
  color:var(--accent,#2b4fff);
}
.ui-grid {
  display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;
}
.ui-btn {
  display:flex; flex-direction:column; align-items:center; gap:6px;
  padding:8px 4px; border-radius:10px;
  border:2px solid var(--border-color,#e8e9f0);
  background:transparent; cursor:pointer; transition:all .2s;
}
.ui-btn.aktif {
  border-color:var(--accent,#2b4fff);
  box-shadow:0 0 0 3px rgba(var(--accent-rgb,43,79,255),.12);
}
.ui-btn span {
  font-family:var(--font-family,'Nunito',sans-serif);
  font-size:.62rem; font-weight:700;
  color:var(--text,#1a1a2e);
}
.ui-preview {
  width:100%; height:36px; border-radius:6px; overflow:hidden;
  display:flex; border:1px solid rgba(0,0,0,.07);
}
.color-row {
  display:flex; gap:10px; flex-wrap:wrap;
}
.color-dot {
  width:32px; height:32px; border-radius:50%; border:3px solid transparent;
  cursor:pointer; transition:all .2s; box-shadow:0 2px 8px rgba(0,0,0,.18);
}
.color-dot.aktif {
  border-color:var(--text,#1a1a2e);
  transform:scale(1.15);
}
.font-list {
  display:flex; flex-direction:column; gap:6px;
}
.font-btn {
  padding:9px 14px; border-radius:9px;
  border:2px solid var(--border-color,#e8e9f0);
  background:transparent; cursor:pointer;
  font-size:.82rem; font-weight:600;
  color:var(--text,#1a1a2e);
  text-align:left; transition:all .2s;
}
.font-btn.aktif {
  border-color:var(--accent,#2b4fff);
  background:rgba(43,79,255,.06);
  color:var(--accent,#2b4fff);
}
.sett-chip-row {
  display:flex; gap:7px; flex-wrap:wrap;
}
.chip-btn {
  display:flex; align-items:center; gap:5px;
  padding:7px 14px; border-radius:50px;
  border:2px solid var(--border-color,#e8e9f0);
  background:transparent; cursor:pointer;
  font-family:var(--font-family,'Nunito',sans-serif);
  font-size:.76rem; font-weight:700;
  color:var(--text,#1a1a2e); transition:all .2s;
}
.chip-btn.aktif {
  border-color:var(--accent,#2b4fff);
  background:rgba(43,79,255,.08);
  color:var(--accent,#2b4fff);
}
/* Toggle switch */
.toggle-switch { position:relative; display:inline-block; width:44px; height:24px; cursor:pointer; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-track {
  position:absolute; inset:0;
  background:#ccc; border-radius:24px;
  transition:background .25s;
}
.toggle-switch input:checked + .toggle-track { background:var(--accent,#2b4fff); }
.toggle-thumb {
  position:absolute; top:3px; left:3px;
  width:18px; height:18px; border-radius:50%;
  background:#fff; transition:left .25s;
  box-shadow:0 1px 4px rgba(0,0,0,.25);
}
.toggle-switch input:checked + .toggle-track .toggle-thumb { left:23px; }
</style>

<!-- ═══════ SCRIPT PANEL ═══════ -->
<script>
(function(){
  // ── Helpers ──
  function getSett() {
    try { return JSON.parse(localStorage.getItem('aksanova_settings') || '{}'); } catch(e){ return {}; }
  }
  function saveSett(obj) {
    var s = getSett();
    Object.assign(s, obj);
    localStorage.setItem('aksanova_settings', JSON.stringify(s));
    return s;
  }

  // ── Buka / Tutup panel ──
  window.bukaSettings = function() {
    var panel   = document.getElementById('settingsPanel');
    var backdrop= document.getElementById('settingsBackdrop');
    backdrop.style.display = 'block';
    setTimeout(function(){
      backdrop.style.opacity = '1';
      panel.style.right = '0';
    }, 10);
    refreshUI();
  };
  window.tutupSettings = function() {
    var panel   = document.getElementById('settingsPanel');
    var backdrop= document.getElementById('settingsBackdrop');
    panel.style.right = '-420px';
    backdrop.style.opacity = '0';
    setTimeout(function(){ backdrop.style.display='none'; }, 350);
  };

  // ── Terapkan ke DOM real-time ──
  function applyRoot(key, value) {
    document.documentElement.style.setProperty(key, value);
  }

  var palettes = {
    biru:       { accent:'#2b4fff', accent2:'#ffb800', accentRgb:'43,79,255' },
    ungu:       { accent:'#7c3aed', accent2:'#f59e0b', accentRgb:'124,58,237' },
    hijau:      { accent:'#059669', accent2:'#fbbf24', accentRgb:'5,150,105' },
    merah:      { accent:'#dc2626', accent2:'#f59e0b', accentRgb:'220,38,38' },
    emas:       { accent:'#b45309', accent2:'#2b4fff', accentRgb:'180,83,9' },
    merah_muda: { accent:'#db2777', accent2:'#7c3aed', accentRgb:'219,39,119' },
  };

  var uiColors = {
    default: {
      light: { bg:'#f4f5f7',sidebarBg:'#ffffff',card:'#ffffff',text:'#1a1a2e',muted:'#7a7a9a',border:'#e8e9f0',cardBorder:'#eef0fc',bookCard:'#f8f9ff' },
      dark:  { bg:'#0f0f1a',sidebarBg:'#14142a',card:'#1a1a2e',text:'#e8e8f5',muted:'#8888aa',border:'#2a2a40',cardBorder:'#252540',bookCard:'#1e1e30' },
    },
    minimal: {
      light: { bg:'#fafafa',sidebarBg:'#f5f5f5',card:'#ffffff',text:'#111111',muted:'#999999',border:'#e0e0e0',cardBorder:'#ebebeb',bookCard:'#f5f5f5' },
      dark:  { bg:'#111111',sidebarBg:'#191919',card:'#222222',text:'#eeeeee',muted:'#888888',border:'#333333',cardBorder:'#2a2a2a',bookCard:'#1e1e1e' },
    },
    modern: {
      light: { bg:'#f0f2ff',sidebarBg:'#ffffff',card:'#ffffff',text:'#1a1a35',muted:'#6b7280',border:'#dde0ff',cardBorder:'#e8eaff',bookCard:'#f5f6ff' },
      dark:  { bg:'#0d0d1f',sidebarBg:'#12122a',card:'#18183a',text:'#e0e0ff',muted:'#7777aa',border:'#25254a',cardBorder:'#20203e',bookCard:'#1c1c38' },
    },
    kuno: {
      light: { bg:'#f5efe6',sidebarBg:'#fdf6eb',card:'#fffdf5',text:'#2c1810',muted:'#8b7355',border:'#d4c09a',cardBorder:'#e8dcc0',bookCard:'#f9f0dc' },
      dark:  { bg:'#1a1008',sidebarBg:'#221508',card:'#2a1a08',text:'#f0e0c0',muted:'#a08050',border:'#4a3020',cardBorder:'#3a2510',bookCard:'#251508' },
    },
    gradasi: {
      light: { bg:'linear-gradient(135deg,#f0f4ff 0%,#fdf0ff 100%)',sidebarBg:'#ffffff',card:'#ffffff',text:'#1a1a2e',muted:'#7a7a9a',border:'#e8e9f0',cardBorder:'#eef0fc',bookCard:'rgba(255,255,255,.7)' },
      dark:  { bg:'linear-gradient(135deg,#0a0a20 0%,#1a0a2e 100%)',sidebarBg:'#14142a',card:'rgba(26,26,50,.95)',text:'#e8e8f5',muted:'#8888aa',border:'#2a2a45',cardBorder:'#25254a',bookCard:'rgba(30,30,55,.9)' },
    },
  };

  function applyColors(ui, mode) {
    var colors = (uiColors[ui] || uiColors.default)[mode === 'dark' ? 'dark' : 'light'];
    if (colors.bg.startsWith('linear')) {
      document.body.style.background = colors.bg;
      applyRoot('--bg', colors.bg);
    } else {
      document.body.style.background = colors.bg;
      applyRoot('--bg', colors.bg);
    }
    applyRoot('--sidebar-bg', colors.sidebarBg);
    applyRoot('--card', colors.card);
    applyRoot('--text', colors.text);
    applyRoot('--muted', colors.muted);
    applyRoot('--border-color', colors.border);
    applyRoot('--card-border', colors.cardBorder);
    applyRoot('--book-card', colors.bookCard);
  }

  var fontMap = {
    'Nunito':       "'Nunito', sans-serif",
    'Poppins':      "'Poppins', sans-serif",
    'Merriweather': "'Merriweather', serif",
    'Playfair Display': "'Playfair Display', serif",
    'Roboto Mono':  "'Roboto Mono', monospace",
  };
  var weightMap = { light:'300', normal:'400', bold:'600', extrabold:'800' };

  // ── Setters ──
  window.setMode = function(mode) {
    var s = saveSett({ mode: mode });
    var root = document.documentElement;
    root.classList.remove('light','dark');
    root.classList.add(mode);
    applyColors(s.ui || 'default', mode);
    refreshUI();
  };

  window.setUI = function(ui) {
    var s = saveSett({ ui: ui });
    var root = document.documentElement;
    // Hapus kelas ui lama
    root.className = root.className.replace(/ui-\w+/g, '').trim();
    root.classList.add('ui-' + ui);
    applyColors(ui, s.mode || 'light');
    refreshUI();
  };

  window.setGrad = function(grad) {
    saveSett({ gradient: grad });
    var pal = palettes[grad] || palettes.biru;
    applyRoot('--accent', pal.accent);
    applyRoot('--accent2', pal.accent2);
    applyRoot('--accent-rgb', pal.accentRgb);
    refreshUI();
  };

  window.setFont = function(font) {
    saveSett({ font: font });
    applyRoot('--font-family', fontMap[font] || fontMap['Nunito']);
    document.body.style.fontFamily = fontMap[font] || fontMap['Nunito'];
    refreshUI();
  };

  window.setFontSize = function(size) {
    saveSett({ fontSize: parseInt(size) });
    applyRoot('--font-size-base', size + 'px');
    document.body.style.fontSize = size + 'px';
    document.getElementById('fontSizeLabel').textContent = size + 'px';
    document.getElementById('fontSizeRange').value = size;
  };

  window.setFontWeight = function(w) {
    saveSett({ fontWeight: w });
    applyRoot('--font-weight-base', weightMap[w] || '400');
    document.body.style.fontWeight = weightMap[w] || '400';
    refreshUI();
  };

  window.setRadius = function(r) {
    saveSett({ radius: r });
    applyRoot('--radius', r + 'px');
    document.getElementById('radiusLabel').textContent = r + 'px';
    document.getElementById('radiusRange').value = r;
  };

  window.setSpacing = function(sp) {
    saveSett({ spacing: sp });
    var spMap = { compact:'0.6rem', normal:'1rem', relaxed:'1.5rem' };
    applyRoot('--spacing', spMap[sp] || '1rem');
    refreshUI();
  };

  window.setAnimation = function(on) {
    saveSett({ animation: on });
    applyRoot('--trans-speed', on ? '.2s' : '0s');
  };

  window.setSidebar = function(mode) {
    saveSett({ sidebar: mode });
    applyRoot('--sidebar-w', mode === 'icon' ? '64px' : '170px');
    var root = document.documentElement;
    root.className = root.className.replace(/sidebar-\w+/g,'').trim();
    root.classList.add('sidebar-' + mode);
    refreshUI();
  };

  window.resetSettings = function() {
    localStorage.removeItem('aksanova_settings');
    location.reload();
  };

  // ── Refresh tampilan tombol panel ──
  function refreshUI() {
    var s = getSett();
    var mode  = s.mode      || 'light';
    var ui    = s.ui        || 'default';
    var grad  = s.gradient  || 'biru';
    var font  = s.font      || 'Nunito';
    var fw    = s.fontWeight|| 'normal';
    var sp    = s.spacing   || 'normal';
    var sb    = s.sidebar   || 'full';
    var anim  = s.animation !== undefined ? s.animation : true;
    var fs    = parseInt(s.fontSize || 14);
    var rad   = parseInt(s.radius   || 14);

    // Mode
    setActiveBtn(['modeLight','modeDark'], mode === 'dark' ? 'modeDark' : 'modeLight');
    // UI
    ['default','minimal','modern','kuno','gradasi'].forEach(function(k){
      var el = document.getElementById('ui-'+k);
      if(el) el.classList.toggle('aktif', k === ui);
    });
    // Gradient
    ['biru','ungu','hijau','merah','emas','merah_muda'].forEach(function(k){
      var el = document.getElementById('grad-'+k);
      if(el) el.classList.toggle('aktif', k === grad);
    });
    // Font
    ['Nunito','Poppins','Merriweather','Playfair Display','Roboto Mono'].forEach(function(k){
      var el = document.getElementById('font-'+k);
      if(el) el.classList.toggle('aktif', k === font);
    });
    // Font weight
    ['light','normal','bold','extrabold'].forEach(function(k){
      var el = document.getElementById('fw-'+k);
      if(el) el.classList.toggle('aktif', k === fw);
    });
    // Spacing
    ['compact','normal','relaxed'].forEach(function(k){
      var el = document.getElementById('sp-'+k);
      if(el) el.classList.toggle('aktif', k === sp);
    });
    // Sidebar
    ['full','icon'].forEach(function(k){
      var el = document.getElementById('sb-'+k);
      if(el) el.classList.toggle('aktif', k === sb);
    });
    // Anim toggle
    var animEl = document.getElementById('animToggle');
    if(animEl) animEl.checked = !!anim;
    // Sliders
    var fsEl = document.getElementById('fontSizeRange');
    if(fsEl) { fsEl.value = fs; document.getElementById('fontSizeLabel').textContent = fs+'px'; }
    var radEl = document.getElementById('radiusRange');
    if(radEl) { radEl.value = rad; document.getElementById('radiusLabel').textContent = rad+'px'; }
  }

  function setActiveBtn(ids, activeId) {
    ids.forEach(function(id){
      var el = document.getElementById(id);
      if(el) el.classList.toggle('aktif', id === activeId);
    });
  }

  // Init refresh saat load
  document.addEventListener('DOMContentLoaded', refreshUI);
})();
</script>