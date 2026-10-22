-- Migration: Tambahkan kolom session_id pada tabel users untuk Single Active Session
-- Menjamin 1 akun kasir/admin hanya bisa aktif di 1 perangkat/browser pada saat yang sama.
ALTER TABLE users ADD COLUMN session_id VARCHAR(128) NULL AFTER last_login_at;
