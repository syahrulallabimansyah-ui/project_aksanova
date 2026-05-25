<?php
// buku_detail.php — API endpoint: ambil detail 1 buku (JSON)
session_start();

if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo json_encode(["ok" => false, "msg" => "Unauthorized"]);
    exit;
}

require_once "db.php";

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
    echo json_encode(["ok" => false, "msg" => "ID tidak valid"]);
    exit;
}

$res = mysqli_query($conn, "SELECT * FROM buku WHERE id = $id LIMIT 1");
if (!$res || mysqli_num_rows($res) === 0) {
    echo json_encode(["ok" => false, "msg" => "Buku tidak ditemukan"]);
    exit;
}

$buku = mysqli_fetch_assoc($res);

// Hitung likes & favorites
$like_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku_likes WHERE buku_id = $id");
$buku["jumlah_like"] = (int)(mysqli_fetch_assoc($like_res)["total"] ?? 0);

$fav_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku_favorites WHERE buku_id = $id");
$buku["jumlah_favorit"] = (int)(mysqli_fetch_assoc($fav_res)["total"] ?? 0);

// Hitung rating
$rat_res = mysqli_query($conn, "SELECT AVG(rating) AS avg_r, COUNT(*) AS total FROM buku_ratings WHERE buku_id = $id");
$rat_row = mysqli_fetch_assoc($rat_res);
$buku["rating_avg"]   = $rat_row["total"] > 0 ? round((float)$rat_row["avg_r"], 1) : 0;
$buku["rating_total"] = (int)$rat_row["total"];

// Rating user yang sedang login
$uid     = (int)$_SESSION["user_id"];
$ur_res  = mysqli_query($conn, "SELECT rating FROM buku_ratings WHERE user_id=$uid AND buku_id=$id LIMIT 1");
$buku["user_rating"] = ($ur_res && mysqli_num_rows($ur_res)) ? (int)mysqli_fetch_assoc($ur_res)["rating"] : 0;

// Status favorit/simpan user ini
$uf_res  = mysqli_query($conn, "SELECT id FROM buku_favorites WHERE user_id=$uid AND buku_id=$id LIMIT 1");
$buku["user_favorit"] = ($uf_res && mysqli_num_rows($uf_res) > 0) ? true : false;

header("Content-Type: application/json");
echo json_encode(["ok" => true, "buku" => $buku]);