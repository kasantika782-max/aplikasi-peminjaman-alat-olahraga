<?php
class m_peminjaman {
    private $db;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    public function log_aktivitas($id_user, $aksi) {
        $id_user = (int)$id_user;
        $aksi = trim((string)$aksi);
        if ($id_user <= 0 || $aksi === '') return false;

        $stmt = $this->db->prepare(
            "INSERT INTO log_aktivitas (id_user, aksi, waktu) VALUES (?, ?, NOW())"
        );
        if (!$stmt) return false;
        $stmt->bind_param('is', $id_user, $aksi);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function tampil_log_aktivitas() {
        $sql = "SELECT l.*, u.username
                FROM log_aktivitas l
                JOIN user u ON l.id_user = u.id_user
                ORDER BY l.id_log DESC";
        return mysqli_query($this->db, $sql);
    }

    public function tampil_data() {
        $sql = "SELECT p.*, u.username AS nama_peminjam, u.no_hp, a.nama_alat
                FROM peminjaman p
                JOIN user u ON p.id_user = u.id_user
                JOIN alat a ON p.id_alat = a.id_alat
                ORDER BY p.id_peminjaman DESC";
        return mysqli_query($this->db, $sql);
    }

    public function tampil_data_admin($tipe = 'pinjam') {
        $where = ($tipe === 'kembali')
            ? "WHERE p.status = 'kembali'"
            : "WHERE p.status IN ('pending', 'dipinjam')";

        $sql = "SELECT p.*, u.username AS nama_peminjam, u.no_hp,
                       a.nama_alat, k.nama_kategori
                FROM peminjaman p
                JOIN user u ON p.id_user = u.id_user
                JOIN alat a ON p.id_alat = a.id_alat
                LEFT JOIN kategori k ON a.id_kategori = k.id_kategori
                $where
                ORDER BY p.id_peminjaman DESC";
        return mysqli_query($this->db, $sql);
    }

    public function tampil_data_user($id_user) {
        $id_user = (int)$id_user;
        if ($id_user <= 0) return false;

        $stmt = $this->db->prepare(
            "SELECT p.*, a.nama_alat
             FROM peminjaman p
             JOIN alat a ON p.id_alat = a.id_alat
             WHERE p.id_user = ?
             ORDER BY p.id_peminjaman DESC"
        );
        if (!$stmt) return false;
        $stmt->bind_param('i', $id_user);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Peminjam biasa: membuat pengajuan pending. Stok baru berkurang saat disetujui.
    public function tambah_pinjam($id_user, $id_alat, $jumlah, $kondisi) {
        $id_user = (int)$id_user;
        $id_alat = (int)$id_alat;
        $jumlah = (int)$jumlah;
        $kondisi = trim((string)$kondisi);

        if ($id_user <= 0 || $id_alat <= 0 || $jumlah <= 0 || $kondisi === '') {
            return false;
        }

        $stmt = $this->db->prepare(
            "SELECT id_user FROM user
             WHERE id_user = ? AND role = 'peminjam' LIMIT 1"
        );
        if (!$stmt) return false;
        $stmt->bind_param('i', $id_user);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) return false;

        $stmt = $this->db->prepare(
            "SELECT stok FROM alat WHERE id_alat = ? LIMIT 1"
        );
        if (!$stmt) return false;
        $stmt->bind_param('i', $id_alat);
        $stmt->execute();
        $alat = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$alat || (int)$alat['stok'] < $jumlah) return false;

        $tgl = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare(
            "INSERT INTO peminjaman
             (id_user, id_alat, jumlah_pinjam, tgl_pinjam, kondisi_keluar, status)
             VALUES (?, ?, ?, ?, ?, 'pending')"
        );
        if (!$stmt) return false;
        $stmt->bind_param('iiiss', $id_user, $id_alat, $jumlah, $tgl, $kondisi);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // Admin meminjam untuk dirinya sendiri.
    public function tambah_pinjam_admin($id_user, $id_alat, $jumlah,
                                        $kondisi = 'Baik', $jaminan = 'KTP',
                                        $tgl_pinjam = '') {
        $id_user = (int)$id_user;
        $id_alat = (int)$id_alat;
        $jumlah = (int)$jumlah;
        $kondisi = trim((string)$kondisi);
        if ($id_user <= 0 || $id_alat <= 0 || $jumlah <= 0 || $kondisi === '') {
            return false;
        }

        // Pastikan ID session memang akun admin.
        $stmtUser = $this->db->prepare(
            "SELECT id_user FROM user WHERE id_user = ? AND role = 'admin' LIMIT 1"
        );
        if (!$stmtUser) return false;
        $stmtUser->bind_param('i', $id_user);
        $stmtUser->execute();
        $user = $stmtUser->get_result()->fetch_assoc();
        $stmtUser->close();
        if (!$user) return 'user_tidak_ditemukan';

        if ($tgl_pinjam === '') {
            $tgl_pinjam = date('Y-m-d H:i:s');
        } else {
            $timestamp = strtotime($tgl_pinjam);
            if ($timestamp === false) return 'tanggal_tidak_valid';
            $tgl_pinjam = date('Y-m-d H:i:s', $timestamp);
        }

        $jaminan = 'KTP';

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT stok FROM alat WHERE id_alat = ? FOR UPDATE"
            );
            if (!$stmt) {
                $this->db->rollback();
                return false;
            }
            $stmt->bind_param('i', $id_alat);
            $stmt->execute();
            $alat = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$alat) {
                $this->db->rollback();
                return 'alat_tidak_ditemukan';
            }

            $stok = (int)$alat['stok'];
            if ($stok < $jumlah) {
                $this->db->rollback();
                return 'stok_kurang';
            }

            $stmt = $this->db->prepare(
                "INSERT INTO peminjaman
                 (id_user, id_alat, jumlah_pinjam, tgl_pinjam,
                  kondisi_keluar, jaminan, status)
                 VALUES (?, ?, ?, ?, ?, ?, 'dipinjam')"
            );
            if (!$stmt) {
                $this->db->rollback();
                return false;
            }
            $stmt->bind_param(
                'iiisss', $id_user, $id_alat, $jumlah,
                $tgl_pinjam, $kondisi, $jaminan
            );
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                $this->db->rollback();
                return false;
            }

            $stmt = $this->db->prepare(
                "UPDATE alat SET stok = stok - ? WHERE id_alat = ?"
            );
            if (!$stmt) {
                $this->db->rollback();
                return false;
            }
            $stmt->bind_param('ii', $jumlah, $id_alat);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                $this->db->rollback();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    // Petugas menyetujui pengajuan pending dan mengurangi stok secara atomik.
    public function verifikasi_pinjam($id) {
        $id = (int)$id;
        if ($id <= 0) return false;

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT id_alat, jumlah_pinjam, status
                 FROM peminjaman WHERE id_peminjaman = ? FOR UPDATE"
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$data || $data['status'] !== 'pending') {
                $this->db->rollback();
                return false;
            }

            $id_alat = (int)$data['id_alat'];
            $jumlah = (int)$data['jumlah_pinjam'];

            $stmt = $this->db->prepare(
                "SELECT stok FROM alat WHERE id_alat = ? FOR UPDATE"
            );
            $stmt->bind_param('i', $id_alat);
            $stmt->execute();
            $alat = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$alat || (int)$alat['stok'] < $jumlah) {
                $this->db->rollback();
                return false;
            }

            $stmt = $this->db->prepare(
                "UPDATE peminjaman SET status='dipinjam'
                 WHERE id_peminjaman=? AND status='pending'"
            );
            $stmt->bind_param('i', $id);
            $ok1 = $stmt->execute();
            $stmt->close();

            $stmt = $this->db->prepare(
                "UPDATE alat SET stok = stok - ? WHERE id_alat = ?"
            );
            $stmt->bind_param('ii', $jumlah, $id_alat);
            $ok2 = $stmt->execute();
            $stmt->close();

            if (!$ok1 || !$ok2) {
                $this->db->rollback();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    // Admin/petugas mengonfirmasi pengembalian.
    public function konfirmasi_kembali($id) {
        $id = (int)$id;
        if ($id <= 0) return false;

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT id_alat, jumlah_pinjam, status
                 FROM peminjaman WHERE id_peminjaman = ? FOR UPDATE"
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$data || $data['status'] !== 'dipinjam') {
                $this->db->rollback();
                return false;
            }

            $tgl = date('Y-m-d H:i:s');
            $id_alat = (int)$data['id_alat'];
            $jumlah = (int)$data['jumlah_pinjam'];

            $stmt = $this->db->prepare(
                "UPDATE peminjaman
                 SET status='kembali', tgl_kembali_asli=?, kondisi_masuk='Baik'
                 WHERE id_peminjaman=? AND status='dipinjam'"
            );
            $stmt->bind_param('si', $tgl, $id);
            $ok1 = $stmt->execute();
            $stmt->close();

            $stmt = $this->db->prepare(
                "UPDATE alat SET stok = stok + ? WHERE id_alat = ?"
            );
            $stmt->bind_param('ii', $jumlah, $id_alat);
            $ok2 = $stmt->execute();
            $stmt->close();

            if (!$ok1 || !$ok2) {
                $this->db->rollback();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function update_pinjam($id_peminjaman, $jumlah_baru, $status_baru) {
        $id_peminjaman = (int)$id_peminjaman;
        $jumlah_baru = (int)$jumlah_baru;
        $status_baru = strtolower(trim((string)$status_baru));

        if ($id_peminjaman <= 0 || $jumlah_baru <= 0 ||
            !in_array($status_baru, ['pending', 'dipinjam'], true)) {
            return false;
        }

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT id_alat, jumlah_pinjam, status
                 FROM peminjaman WHERE id_peminjaman=? FOR UPDATE"
            );
            $stmt->bind_param('i', $id_peminjaman);
            $stmt->execute();
            $lama = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$lama || !in_array($lama['status'], ['pending','dipinjam'], true)) {
                $this->db->rollback();
                return false;
            }

            $id_alat = (int)$lama['id_alat'];
            $jumlah_lama = (int)$lama['jumlah_pinjam'];
            $status_lama = $lama['status'];

            $stmt = $this->db->prepare(
                "SELECT stok FROM alat WHERE id_alat=? FOR UPDATE"
            );
            $stmt->bind_param('i', $id_alat);
            $stmt->execute();
            $alat = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$alat) {
                $this->db->rollback();
                return false;
            }

            $stok = (int)$alat['stok'];
            $stok_akhir = $stok;

            if ($status_lama === 'pending' && $status_baru === 'dipinjam') {
                if ($stok < $jumlah_baru) {
                    $this->db->rollback();
                    return 'stok_kurang';
                }
                $stok_akhir = $stok - $jumlah_baru;
            } elseif ($status_lama === 'dipinjam' && $status_baru === 'pending') {
                $stok_akhir = $stok + $jumlah_lama;
            } elseif ($status_lama === 'dipinjam' && $status_baru === 'dipinjam') {
                $selisih = $jumlah_baru - $jumlah_lama;
                if ($selisih > 0) {
                    if ($stok < $selisih) {
                        $this->db->rollback();
                        return 'stok_kurang';
                    }
                    $stok_akhir = $stok - $selisih;
                } elseif ($selisih < 0) {
                    $stok_akhir = $stok + abs($selisih);
                }
            }

            $stmt = $this->db->prepare(
                "UPDATE alat SET stok=? WHERE id_alat=?"
            );
            $stmt->bind_param('ii', $stok_akhir, $id_alat);
            if (!$stmt->execute()) {
                $stmt->close();
                $this->db->rollback();
                return false;
            }
            $stmt->close();

            $stmt = $this->db->prepare(
                "UPDATE peminjaman SET jumlah_pinjam=?, status=?
                 WHERE id_peminjaman=?"
            );
            $stmt->bind_param('isi', $jumlah_baru, $status_baru, $id_peminjaman);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                $this->db->rollback();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function update_kembali($id, $kondisi, $tgl) {
        $id = (int)$id;
        $kondisi = trim((string)$kondisi);
        if ($id <= 0 || $kondisi === '' || trim((string)$tgl) === '') return false;

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT id_alat, jumlah_pinjam, status
                 FROM peminjaman WHERE id_peminjaman=? FOR UPDATE"
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$data || $data['status'] === 'pending') {
                $this->db->rollback();
                return false;
            }

            $id_alat = (int)$data['id_alat'];
            $jumlah = (int)$data['jumlah_pinjam'];
            $status_lama = $data['status'];

            if ($status_lama === 'dipinjam') {
                $stmt = $this->db->prepare(
                    "UPDATE alat SET stok=stok+? WHERE id_alat=?"
                );
                $stmt->bind_param('ii', $jumlah, $id_alat);
                if (!$stmt->execute()) {
                    $stmt->close();
                    $this->db->rollback();
                    return false;
                }
                $stmt->close();
            }

            $timestamp = strtotime($tgl);
            if ($timestamp === false) {
                $this->db->rollback();
                return false;
            }
            $tgl = date('Y-m-d H:i:s', $timestamp);

            $stmt = $this->db->prepare(
                "UPDATE peminjaman
                 SET kondisi_masuk=?, tgl_kembali_asli=?, status='kembali'
                 WHERE id_peminjaman=?"
            );
            $stmt->bind_param('ssi', $kondisi, $tgl, $id);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                $this->db->rollback();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function hapus_data($id) {
        $id = (int)$id;
        if ($id <= 0) return false;

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT id_alat, jumlah_pinjam, status
                 FROM peminjaman WHERE id_peminjaman=? FOR UPDATE"
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$data) {
                $this->db->rollback();
                return false;
            }

            if ($data['status'] === 'dipinjam') {
                $stmt = $this->db->prepare(
                    "UPDATE alat SET stok=stok+? WHERE id_alat=?"
                );
                $jumlah = (int)$data['jumlah_pinjam'];
                $id_alat = (int)$data['id_alat'];
                $stmt->bind_param('ii', $jumlah, $id_alat);
                if (!$stmt->execute()) {
                    $stmt->close();
                    $this->db->rollback();
                    return false;
                }
                $stmt->close();
            }

            $stmt = $this->db->prepare(
                "DELETE FROM peminjaman WHERE id_peminjaman=?"
            );
            $stmt->bind_param('i', $id);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                $this->db->rollback();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function get_all_peminjam() {
        return mysqli_query(
            $this->db,
            "SELECT id_user, username FROM user
             WHERE role='peminjam' ORDER BY username ASC"
        );
    }

    public function get_all_alat() {
        return mysqli_query(
            $this->db,
            "SELECT id_alat, nama_alat, stok FROM alat
             WHERE stok > 0 ORDER BY nama_alat ASC"
        );
    }
}
?>
