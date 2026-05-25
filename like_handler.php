<?php
// like_handler.php — Toggle like / favorite via AJAX (member only)
session_start();
header('Content-Type: application/json');

// Harus login
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["ok" => false, "msg" => "Login dulu"]);
    exit;
}

// Admin tidak boleh like/favorite
if ($_SESSION["role"] === "admin") {
    echo json_encode(["ok" => false, "msg" => "Admin tidak perlu fitur ini"]);
    exit;
}

require_once "db.php";

$user_id = (int)$_SESSION["user_id"];
$buku_id = (int)($_POST["buku_id"] ?? 0);
$type    = $_POST["type"] ?? ""; // "like" atau "favorite"

if ($buku_id <= 0 || !in_array($type, ["like","favorite"])) {
    echo json_encode(["ok" => false, "msg" => "Data tidak valid"]);
    exit;
}

$table = $type === "like" ? "buku_likes" : "buku_favorites";

// Cek apakah sudah ada
$cek = mysqli_query($conn, "SELECT id FROM $table WHERE buku_id=$buku_id AND user_id=$user_id LIMIT 1");

if (mysqli_num_rows($cek) > 0) {
    // Sudah ada → hapus (toggle off)
    mysqli_query($conn, "DELETE FROM $table WHERE buku_id=$buku_id AND user_id=$user_id");
    $aktif = false;
} else {
    // Belum ada → tambah (toggle on)
    mysqli_query($conn, "INSERT INTO $table (buku_id, user_id) VALUES ($buku_id, $user_id)");
    $aktif = true;
}

// Hitung total
$total_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM $table WHERE buku_id=$buku_id");
$total     = (int)mysqli_fetch_assoc($total_res)["total"];

echo json_encode(["ok" => true, "aktif" => $aktif, "total" => $total]);