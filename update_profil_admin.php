<?php
// update_profil_admin.php — Simpan nama & foto profil admin
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: sign_in.php");
    exit;
}

require_once "db.php";

$redirect = $_POST["redirect"] ?? "halaman_admin.php";

// ── Ambil nama baru ──
$display_name = trim(mysqli_real_escape_string($conn, $_POST["display_name"] ?? ""));
if ($display_name === "") $display_name = "Admin";

// ── Upload foto baru (opsional) ──
$foto_baru = "";
if (isset($_FILES["foto_admin"]) && $_FILES["foto_admin"]["error"] === UPLOAD_ERR_OK) {
    $dir = "uploads/profil/";
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $ext     = strtolower(pathinfo($_FILES["foto_admin"]["name"], PATHINFO_EXTENSION));
    $allowed = ["jpg","jpeg","png","webp","gif"];
    if (in_array($ext, $allowed) && $_FILES["foto_admin"]["size"] <= 5 * 1024 * 1024) {
        // Hapus foto lama
        $r = mysqli_query($conn, "SELECT foto FROM admin_profile LIMIT 1");
        if ($r && $row = mysqli_fetch_assoc($r)) {
            if ($row["foto"] && file_exists($row["foto"])) @unlink($row["foto"]);
        }
        $filename = "admin_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES["foto_admin"]["tmp_name"], $dir . $filename);
        $foto_baru = $dir . $filename;
    }
}

// ── Upsert ke tabel admin_profile ──
$exists = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM admin_profile LIMIT 1"));

if ($exists) {
    if ($foto_baru !== "") {
        mysqli_query($conn, "UPDATE admin_profile SET display_name='$display_name', foto='$foto_baru' WHERE id={$exists['id']}");
    } else {
        mysqli_query($conn, "UPDATE admin_profile SET display_name='$display_name' WHERE id={$exists['id']}");
    }
} else {
    mysqli_query($conn, "INSERT INTO admin_profile (display_name, foto) VALUES ('$display_name', '$foto_baru')");
}

// Update nama di session juga
$_SESSION["user_name"] = $display_name;

header("Location: $redirect?profil_saved=1");
exit;