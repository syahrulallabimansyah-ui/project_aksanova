<?php
// logout.php — Hapus session lalu redirect ke halaman sign in
session_start();
session_destroy();
header("Location: sign_in.php");
exit;
?>