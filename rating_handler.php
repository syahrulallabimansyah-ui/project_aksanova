<?php
// rating_handler.php — Submit / ambil rating buku (1–5 bintang)
session_start();

if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo json_encode(["ok" => false, "msg" => "Unauthorized"]);
    exit;
}

require_once "db.php";

$user_id = (int)$_SESSION["user_id"];
$method  = $_SERVER["REQUEST_METHOD"];

// ── GET: ambil rating user untuk 1 buku ──────────────────────
if ($method === "GET") {
    $buku_id = (int)($_GET["buku_id"] ?? 0);
    if ($buku_id <= 0) { echo json_encode(["ok" => false]); exit; }

    // Rating user ini
    $r = mysqli_query($conn, "SELECT rating FROM buku_ratings WHERE user_id=$user_id AND buku_id=$buku_id LIMIT 1");
    $user_rating = $r && mysqli_num_rows($r) ? (int)mysqli_fetch_assoc($r)["rating"] : 0;

    // Rata-rata & jumlah
    $r2  = mysqli_query($conn, "SELECT AVG(rating) AS avg_r, COUNT(*) AS total FROM buku_ratings WHERE buku_id=$buku_id");
    $row = mysqli_fetch_assoc($r2);

    echo json_encode([
        "ok"          => true,
        "user_rating" => $user_rating,
        "avg"         => $row["total"] > 0 ? round((float)$row["avg_r"], 1) : 0,
        "total"       => (int)$row["total"],
    ]);
    exit;
}

// ── POST: simpan / update rating ─────────────────────────────
if ($method === "POST") {
    $buku_id = (int)($_POST["buku_id"] ?? 0);
    $rating  = (int)($_POST["rating"]  ?? 0);

    if ($buku_id <= 0 || $rating < 1 || $rating > 5) {
        echo json_encode(["ok" => false, "msg" => "Data tidak valid"]); exit;
    }

    // Upsert
    $r = mysqli_query($conn, "SELECT id FROM buku_ratings WHERE user_id=$user_id AND buku_id=$buku_id LIMIT 1");
    if ($r && mysqli_num_rows($r)) {
        mysqli_query($conn, "UPDATE buku_ratings SET rating=$rating, updated_at=NOW() WHERE user_id=$user_id AND buku_id=$buku_id");
    } else {
        mysqli_query($conn, "INSERT INTO buku_ratings (user_id, buku_id, rating, created_at, updated_at) VALUES ($user_id, $buku_id, $rating, NOW(), NOW())");
    }

    // Kembalikan avg & total terbaru
    $r2  = mysqli_query($conn, "SELECT AVG(rating) AS avg_r, COUNT(*) AS total FROM buku_ratings WHERE buku_id=$buku_id");
    $row = mysqli_fetch_assoc($r2);

    header("Content-Type: application/json");
    echo json_encode([
        "ok"          => true,
        "user_rating" => $rating,
        "avg"         => round((float)$row["avg_r"], 1),
        "total"       => (int)$row["total"],
    ]);
    exit;
}

echo json_encode(["ok" => false, "msg" => "Method not allowed"]);