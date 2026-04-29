<?php
// ── auth_helper.php ────────────────────────────────────────
// Simpan file ini di folder yang sama
// Include di setiap halaman yang butuh auth
// ──────────────────────────────────────────────────────────

function cek_login_pengguna() {
    // Prioritas 1: session
    if (isset($_SESSION["nama"]) && isset($_SESSION["id"])) {
        return true;
    }
    // Prioritas 2: cookie (restore ke session)
    if (isset($_COOKIE["cu_id"]) && isset($_COOKIE["cu_nama"]) && isset($_COOKIE["cu_user"])) {
        $_SESSION["id"]       = $_COOKIE["cu_id"];
        $_SESSION["nama"]     = $_COOKIE["cu_nama"];
        $_SESSION["username"] = $_COOKIE["cu_user"];
        return true;
    }
    return false;
}

function cek_login_admin() {
    if (isset($_SESSION["admin_nama"]) && isset($_SESSION["admin_id"])) {
        return true;
    }
    if (isset($_COOKIE["ca_id"]) && isset($_COOKIE["ca_nama"]) && isset($_COOKIE["ca_user"])) {
        $_SESSION["admin_id"]       = $_COOKIE["ca_id"];
        $_SESSION["admin_nama"]     = $_COOKIE["ca_nama"];
        $_SESSION["admin_username"] = $_COOKIE["ca_user"];
        $_SESSION["admin_role"]     = $_COOKIE["ca_role"] ?? 'konten';
        return true;
    }
    return false;
}